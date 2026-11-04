# Sistema Hospitalar

Projeto das disciplinas de Banco de Dados II e Back-end, desenvolvido com Laravel, Blade e PostgreSQL no Neon. A aplicação gerencia consultas e demonstra a integração entre o backend e índices, functions, stored procedures e triggers do banco.

## Funcionalidades

- Cadastro, listagem, consulta, edição e exclusão de consultas pela interface web e API REST.
- Busca por paciente, CPF ou médico, filtros e paginação na agenda.
- Cancelamento e realização de consultas por stored procedures; a realização também registra um prontuário.
- Histórico de alterações de status gerado por trigger e exibido na consulta.
- Relatórios de faturamento por médico e ocupação dos quartos por functions PostgreSQL.
- Importação da carga inicial e procedimento de backup com restauração e evidências.

O CRUD disponível é o de consultas. Pacientes, médicos e demais entidades relacionadas são fornecidos pela carga inicial.

## Configuração e execução

Execute os comandos na raiz do projeto. Os exemplos de terminal usam PowerShell.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Se já tiver um `.env` configurado, mantenha-o. Edite a conexão do banco com os dados fornecidos pelo Neon:

```dotenv
DB_CONNECTION=pgsql
DB_URL="ep-tiny-moon-b6et5n7j.c-2.sa-east-1.aws.neon.tech"
DB_SSLMODE=require
```

Substitua os valores de exemplo. O banco deve existir no Neon. A conexão utiliza o schema `public`. Não publique o `.env` ou credenciais.

Depois de configurar a conexão:

```powershell
php artisan config:clear
php artisan hospital:prepare
php artisan serve
```

O comando `hospital:prepare` executa as migrations e o seeder. As migrations criam as tabelas e instalam os objetos de `database/sql/PA-Scriptsql.sql` antes da carga dos dados. Não é necessário executar esse script manualmente após as migrations.

O importador executa `database/sql/inserts_sh_bd.sql` em uma transação, desativando os triggers de usuário de consultas durante a carga e reativando-os ao concluir. As chaves estrangeiras continuam sendo verificadas. Uma carga já importada é reconhecida pelo checksum e não é repetida; tabelas hospitalares já preenchidas sem o registro dessa carga impedem o import para preservar os dados.

Se o banco já está preparado e você precisa apenas aplicar novas migrations, use `php artisan migrate`. Não use `migrate:fresh` no banco de trabalho: ele apaga as tabelas.

Abra no navegador o endereço informado por `php artisan serve`. A rota `/` redireciona para `/consultas`.

## Rotas web

|   Método  |          Rota          |                      Finalidade                          |
|   GET     | `/consultas`           | Agenda, busca e filtros.                                 |
|   GET     | `/consultas/create`    | Formulário de cadastro.                                  |
|   POST    | `/consultas`           | Cadastrar consulta.                                      |
|   GET     | `/consultas/{id}`      | Detalhes, prontuários, prescrições e histórico de status.|
|   GET     | `/consultas/{id}/edit` | Formulário de edição.                                    |
| PUT/PATCH | `/consultas/{id}`      | Atualizar consulta.                                      |
|  DELETE   | `/consultas/{id}`      | Excluir consulta sem prontuários ou prescrições.         |
|   POST    | `/consultas/{id}/cancelar` | Cancelar uma consulta agendada via procedure.        |
|POST| `/consultas/{id}/realizar`    | Realizar uma consulta agendada e registrar prontuário via procedure |
|    GET    | `/relatorios`          | Faturamento e ocupação, com filtros.                     |

## API REST e API Resource

As rotas estão em [routes/api.php](routes/api.php). O [ConsultaApiController](app/Http/Controllers/Api/ConsultaApiController.php) utiliza o [ConsultaService](app/Services/ConsultaService.php), e o [ConsultaResource](app/Http/Resources/ConsultaResource.php) define a representação JSON das consultas, incluindo paciente e médico.

|   Método  |             Rota               |                      Resposta                      |
|    GET    | `/api/consultas`               | `200`: JSON paginado com `data`, `links` e `meta`. |
|    GET    | `/api/consultas/{id}`          | `200`: consulta em `data`.                         |
|    POST   | `/api/consultas`               | `201`: consulta criada em `data`.                  |
| PUT/PATCH | `/api/consultas/{id}`          | `200`: consulta atualizada em `data`.              |
|   DELETE  | `/api/consultas/{id}`          | `204`: exclusão concluída, sem corpo.              |
|    POST   | `/api/consultas/{id}/cancelar` | `200`: consulta cancelada em `data`.               |
|    POST   | `/api/consultas/{id}/realizar` | `200`: consulta realizada em `data`.               |
|    GET    | `/api/relatorios/faturamento`  | `200`: faturamento por médico em `data`.           |
|    GET    | `/api/relatorios/ocupacao`     | `200`: ocupação dos quartos em `data`.             |

Registros inexistentes retornam `404`; erros de validação retornam `422`. A tentativa de excluir consulta vinculada a prontuário ou prescrição retorna `409` com mensagem JSON. O código `204` não contém JSON porque significa sucesso sem conteúdo.

As rotas hospitalares acima não exigem autenticação na implementação atual. A rota adicional `/api/user` exige token Sanctum e não faz parte da demonstração do CRUD de consultas.

### Demonstração no Postman do VS Code

1. Inicie o servidor com `php artisan serve` e crie uma coleção **Sistema Hospitalar** na extensão do Postman.
2. Crie a variável de coleção `base_url` com o endereço informado pelo servidor, sem barra final.
3. Configure **Authorization → No Auth** e o header `Accept: application/json`. Nos pedidos com corpo, selecione **Body → raw → JSON**, com `Content-Type: application/json`.
4. Envie `GET {{base_url}}/api/consultas`. Anote um `paciente.id` e um `medico.id` existentes. A listagem aceita `busca`, `status` e `page`, por exemplo: `/api/consultas?status=Agendada&page=1`.
5. Envie `POST {{base_url}}/api/consultas` com o JSON abaixo, substituindo os IDs e escolhendo um horário livre para o médico:

```json
{
  "paciente_id": 1,
  "medico_id": 1,
  "data_hora": "2026-12-10 14:30:00",
  "motivo": "Demonstração do CRUD pelo Postman",
  "valor": 250
}
```

6. Confira o status `201` e anote `data.id`. Envie `GET {{base_url}}/api/consultas/ID_CRIADO` e confira `200`.
7. Envie `PUT {{base_url}}/api/consultas/ID_CRIADO` com os mesmos campos obrigatórios (`paciente_id`, `medico_id`, `data_hora`), alterando `motivo` e `valor`. Confira `200` e os novos valores no JSON. A validação atual também exige esses campos nas requisições PATCH.
8. Envie `DELETE {{base_url}}/api/consultas/ID_CRIADO`. Confira `204`. Repita o GET desse ID e confira `404`. Use o registro criado na demonstração, sem prontuário ou prescrição.
9. Para demonstrar validação, envie um POST com `{}` e confira `422` com os erros em JSON.

Salve as requisições na coleção.
Para demonstrar as procedures, crie outras consultas agendadas e execute as ações abaixo em registros distintos:
```t
POST /api/consultas/{id}/cancelar
{"motivo": "Cancelamento solicitado pelo paciente"}

POST /api/consultas/{id}/realizar
{"descricao": "Atendimento realizado, paciente estável.", "diagnostico": "Retorno"}
```

A descrição é obrigatória para realizar. O motivo do cancelamento e o diagnóstico são opcionais. Confira depois o histórico na página web da consulta. Uma consulta já cancelada ou realizada não pode receber novamente essas ações.

Exemplos de relatórios:

```t
GET /api/relatorios/faturamento?data_inicio=2026-01-01&data_fim=2026-12-31
GET /api/relatorios/ocupacao?departamento_id=1
```

O faturamento considera apenas consultas realizadas e inclui o último dia do período. Sem datas, usa o início do mês até a data atual do banco. A ocupação considera internações sem data de saída; sem departamento, retorna todos os quartos.

## Recursos PostgreSQL

O script [PA-Scriptsql.sql](database/sql/PA-Scriptsql.sql) contém os objetos e exemplos de uso SQL:

|             Recurso         |                            Aplicação                                 |
| Índices trigram em nome/CPF | Apoiar buscas textuais de pacientes e médicos.                       |
| Índices de consultas e internações | Apoiar histórico de consultas e internações ativas por quarto.|
| `fn_faturamento_por_medico` | Relatório de consultas realizadas e faturamento por médico.          |
| `fn_ocupacao_quartos`       | Relatório de capacidade, ocupação e vagas por quarto.                | 
| `sp_cancelar_consulta`      | Cancelamento com motivo opcional, por `CALL`.                        |
| `sp_realizar_consulta`      | Realização e criação de prontuário, por `CALL`.                      |
| `trg_consultas_conflito_horario` | Impedir consultas ativas no mesmo horário para o mesmo médico.  |
| `trg_consultas_log_status`  | Registrar cadastro e mudanças de status em `consultas_status_log`.   |

A carga inicial não gera histórico de status. Novos cadastros e alterações posteriores são auditados. As procedures bloqueiam a linha da consulta durante a operação, e a gravação de status e prontuário ocorre na mesma transação.

## Testes automatizados

Os testes usam PHPUnit e PostgreSQL em um **banco separado**, pois as migrations e os testes exercitam recursos específicos do PostgreSQL. Crie um banco exclusivo, por exemplo `hospital_teste`, e um `.env.testing` com sua própria chave de aplicação e a conexão desse banco:

```dotenv
APP_ENV=testing
APP_KEY=Chave gerada
DB_CONNECTION=pgsql
DB_URL=
DB_HOST=Link da Neon
DB_PORT=5432
DB_DATABASE=hospital_teste
DB_USERNAME=""
DB_PASSWORD=""
DB_SSLMODE=require
```

Para preencher a chave e executar:

```powershell
php artisan key:generate --env=testing
php artisan config:clear
php artisan test --compact
```

O `phpunit.xml` força PostgreSQL e limpa `DB_URL`, portanto preencha todos os campos individuas de conexão. O nome do banco precisa conter `test` para passar pela trava em `tests/TestCase.php`. Os testes usam `RefreshDatabase` e podem recriar tabelas: nunca aponte essa configuração para o banco de trabalho ou uma cópia que deseja preservar.

Há testes das rotas web e API, validação e exclusão, filtros, importação da carga, auditoria, conflitos de horário, procedures, rollback e relatórios. Para executar apenas os testes da API:

```powershell
php artisan test --compact tests/Feature/ConsultaApiTest.php
```


## Organização
|         Caminho         |                       Responsabilidade                    |
| `app/Models/`           | Entidades e relacionamentos Eloquent.                     |
| `app/Http/Controllers/` | Controllers web e API.                                    |
| `app/Http/Requests/`    | Validação das requisições.                                |
| `app/Http/Resources/`   | Representação JSON das consultas.                         |
| `app/Services/`         | Regras de consultas e importação da carga SQL.            |
| `resources/views/`      | Telas Blade.                                              |
| `database/migrations/`  | Estrutura e instalação dos objetos PostgreSQL.            |
| `database/sql/`         | Carga inicial, objetos do banco e procedimento de backup. |
| `tests/Feature/`        | Testes de integração web, API e banco.                    |
