SET SEARCH_PATH TO ignored_schema;
INSERT INTO especialidades (nome) VALUES
('Especialidade temporária');
INSERT INTO medicos (nome, crm, especialidade_id) VALUES
('Médico inválido', 'TEST-1', 999999);
