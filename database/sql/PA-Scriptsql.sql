-- Adendo: Após um migrate:fresh, execute o script de novo.

SET search_path TO public;

CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- IDX 1, 2 e 3: busca textual (trigram)
CREATE INDEX IF NOT EXISTS idx_pacientes_nome_trgm
    ON pacientes USING gin (nome gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_pacientes_cpf_trgm
    ON pacientes USING gin (cpf gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_medicos_nome_trgm
    ON medicos USING gin (nome gin_trgm_ops);


-- IDX 4: histórico de consultas do paciente
CREATE INDEX IF NOT EXISTS idx_consultas_paciente_data
    ON consultas (paciente_id, data_hora DESC);


-- IDX 5: internações ativas por quarto
CREATE INDEX IF NOT EXISTS idx_internacoes_quarto_ativa
    ON internacoes (quarto_id)
    WHERE data_saida IS NULL;



-- 2. TABELA DE LOG (usada pelo trigger de auditoria)
CREATE TABLE IF NOT EXISTS consultas_status_log (
    id BIGSERIAL PRIMARY KEY,
    consulta_id BIGINT NOT NULL,
    status_anterior VARCHAR(20),
    status_novo VARCHAR(20)  NOT NULL,
    alterado_em TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_banco VARCHAR(100) NOT NULL DEFAULT current_user
);

CREATE INDEX IF NOT EXISTS idx_consultas_status_log_consulta
    ON consultas_status_log (consulta_id, alterado_em DESC);



-- 3. FUNCTIONS



-- FUNCTION 1: fn_faturamento_por_medico
CREATE OR REPLACE FUNCTION fn_faturamento_por_medico(
    p_inicio DATE DEFAULT (date_trunc('month', CURRENT_DATE))::date,
    p_fim DATE DEFAULT CURRENT_DATE
)
RETURNS TABLE (
    medico_id BIGINT,
    medico_nome VARCHAR,
    especialidade VARCHAR,
    total_consultas BIGINT,
    faturamento NUMERIC
)
LANGUAGE sql
STABLE
AS $$
    SELECT m.id AS medico_id,
           m.nome AS medico_nome,
           e.nome AS especialidade,
           COUNT(c.id) AS total_consultas,
           COALESCE(SUM(c.valor), 0) AS faturamento
      FROM medicos m
      JOIN especialidades e ON e.id = m.especialidade_id
      LEFT JOIN consultas c
             ON c.medico_id = m.id
            AND c.status = 'Realizada'
            AND c.data_hora >= p_inicio
            AND c.data_hora <  (p_fim + 1)
     GROUP BY m.id, m.nome, e.nome
     ORDER BY 5 DESC, 2;
$$;


-- FUNCTION 2: fn_ocupacao_quartos
CREATE OR REPLACE FUNCTION fn_ocupacao_quartos(
    p_departamento_id BIGINT DEFAULT NULL
)
RETURNS TABLE (
    quarto_id BIGINT,
    numero VARCHAR,
    departamento VARCHAR,
    tipo VARCHAR,
    capacidade INTEGER,
    ocupados BIGINT,
    vagas BIGINT
)
LANGUAGE sql
STABLE
AS $$
    SELECT q.id AS quarto_id,
           q.numero AS numero,
           d.nome AS departamento,
           q.tipo AS tipo,
           q.capacidade AS capacidade,
           COUNT(i.id) AS ocupados,
           GREATEST(q.capacidade - COUNT(i.id), 0) AS vagas
      FROM quartos q
      JOIN departamentos d ON d.id = q.departamento_id
      LEFT JOIN internacoes i
             ON i.quarto_id = q.id
            AND i.data_saida IS NULL
     WHERE p_departamento_id IS NULL
        OR q.departamento_id = p_departamento_id
     GROUP BY q.id, q.numero, d.nome, q.tipo, q.capacidade
     ORDER BY d.nome, q.numero;
$$;



-- 4. TRIGGERS 

-- TRIGGER 1: trg_consultas_conflito_horario 
CREATE OR REPLACE FUNCTION fn_valida_conflito_consulta()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.status = 'Cancelada' THEN
        RETURN NEW;
    END IF;

    IF TG_OP = 'UPDATE'
       AND NEW.medico_id = OLD.medico_id
       AND NEW.data_hora = OLD.data_hora
       AND OLD.status   <> 'Cancelada' THEN
        RETURN NEW;   
    END IF;

    IF EXISTS (
        SELECT 1
          FROM consultas c
         WHERE c.medico_id = NEW.medico_id
           AND c.data_hora = NEW.data_hora
           AND c.status <> 'Cancelada'
           AND c.id <> COALESCE(NEW.id, 0)
    ) THEN
        RAISE EXCEPTION 'Conflito de horário: o médico % já possui consulta em %.',
            NEW.medico_id, to_char(NEW.data_hora, 'DD/MM/YYYY HH24:MI')
            USING HINT = 'Escolha outro horário ou cancele a consulta existente.';
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_consultas_conflito_horario ON consultas;

CREATE TRIGGER trg_consultas_conflito_horario
    BEFORE INSERT OR UPDATE OF medico_id, data_hora, status
    ON consultas
    FOR EACH ROW
    EXECUTE FUNCTION fn_valida_conflito_consulta();

-- TRIGGER 2: trg_consultas_log_status  
CREATE OR REPLACE FUNCTION fn_log_status_consulta()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        INSERT INTO consultas_status_log (consulta_id, status_anterior, status_novo)
        VALUES (NEW.id, NULL, NEW.status);

    ELSIF OLD.status IS DISTINCT FROM NEW.status THEN
        INSERT INTO consultas_status_log (consulta_id, status_anterior, status_novo)
        VALUES (NEW.id, OLD.status, NEW.status);
    END IF;

    RETURN NULL; 
END;
$$;

DROP TRIGGER IF EXISTS trg_consultas_log_status ON consultas;

CREATE TRIGGER trg_consultas_log_status
    AFTER INSERT OR UPDATE OF status
    ON consultas
    FOR EACH ROW
    EXECUTE FUNCTION fn_log_status_consulta();

-- 5. STORED PROCEDURES

-- PROCEDURE 1: sp_cancelar_consulta
CREATE OR REPLACE PROCEDURE sp_cancelar_consulta(
    p_consulta_id BIGINT,
    p_motivo TEXT DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status VARCHAR(20);
BEGIN
    SELECT status INTO v_status
      FROM consultas
     WHERE id = p_consulta_id
       FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Consulta % não encontrada.', p_consulta_id;
    END IF;

    IF v_status <> 'Agendada' THEN
        RAISE EXCEPTION 'Somente consultas agendadas podem ser canceladas (status atual: %).', v_status;
    END IF;

    UPDATE consultas
       SET status = 'Cancelada',
           motivo  = LEFT(
                            COALESCE(motivo, '')
                            || CASE WHEN p_motivo IS NOT NULL
                                    THEN ' [Cancelamento: ' || p_motivo || ']'
                                    ELSE '' END,
                            200),
           updated_at = CURRENT_TIMESTAMP
     WHERE id = p_consulta_id;
END;
$$;

-- PROCEDURE 2: sp_realizar_consulta

CREATE OR REPLACE PROCEDURE sp_realizar_consulta(
    p_consulta_id  BIGINT,
    p_descricao TEXT,
    p_diagnostico VARCHAR DEFAULT NULL
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_consulta consultas%ROWTYPE;
BEGIN
    SELECT * INTO v_consulta
      FROM consultas
     WHERE id = p_consulta_id
       FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Consulta % não encontrada.', p_consulta_id;
    END IF;

    IF v_consulta.status <> 'Agendada' THEN
        RAISE EXCEPTION 'Somente consultas agendadas podem ser realizadas (status atual: %).', v_consulta.status;
    END IF;

    UPDATE consultas
       SET status = 'Realizada',
           updated_at = CURRENT_TIMESTAMP
     WHERE id = p_consulta_id;

    INSERT INTO prontuarios
        (paciente_id, consulta_id, data_registro, descricao, diagnostico, created_at, updated_at)
    VALUES
        (v_consulta.paciente_id, p_consulta_id, CURRENT_TIMESTAMP, p_descricao, p_diagnostico,
         CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
END;
$$;



-- 6. TESTES 

-- 6.1 Índices existentes
-- SELECT indexname, indexdef
--   FROM pg_indexes
--  WHERE schemaname = 'public'
--    AND indexname IN ('idx_pacientes_nome_trgm', 'idx_pacientes_cpf_trgm',
--                      'idx_medicos_nome_trgm', 'idx_consultas_paciente_data',
--                      'idx_internacoes_quarto_ativa');

-- 6.2 Uso do índice trigram (força o uso para demonstrar)
-- SET enable_seqscan = off;
-- EXPLAIN ANALYZE SELECT * FROM pacientes WHERE nome ILIKE '%silva%';
-- RESET enable_seqscan;

-- 6.3 Functions
-- SELECT * FROM fn_faturamento_por_medico('2026-01-01', CURRENT_DATE);
-- SELECT * FROM fn_faturamento_por_medico();          -- mês atual
-- SELECT * FROM fn_ocupacao_quartos();
-- SELECT * FROM fn_ocupacao_quartos(1);

-- 6.4 Procedures (troque o id por uma consulta 'Agendada' real)
-- SELECT id FROM consultas WHERE status = 'Agendada' ORDER BY id LIMIT 2;
-- CALL sp_cancelar_consulta(<id1>, 'Paciente desistiu');
-- CALL sp_realizar_consulta(<id2>, 'Paciente apresentou melhora.', 'Hipertensão arterial');
-- SELECT * FROM prontuarios ORDER BY id DESC LIMIT 1;
-- CALL sp_cancelar_consulta(<id1>);   -- consulta já cancelada

-- 6.5 Trigger de log (consulte após os CALLs acima)
-- SELECT * FROM consultas_status_log ORDER BY id DESC LIMIT 10;

-- 6.6 Trigger de conflito: copia uma consulta agendada (mesmo médico e horário)
--     e deve FALHAR com "Conflito de horário". Um UPDATE de status em consulta
--     legada (sem mudar médico/horário) deve FUNCIONAR.
-- INSERT INTO consultas (paciente_id, medico_id, data_hora, status)
-- SELECT paciente_id, medico_id, data_hora, 'Agendada'
--   FROM consultas
--  WHERE status = 'Agendada'
--  LIMIT 1;