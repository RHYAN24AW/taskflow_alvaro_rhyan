# TaskFlow

Sistema simples de tarefas em PHP + PostgreSQL.

## Como rodar
1. Crie o banco e rode `sql/Schema.sql`.
2. Copie `config/database.example.php` para `config/database.php` e ajuste as credenciais (não vai para o Git).
3. Inicie o Apache (XAMPP) e use `base_url = http://localhost` no Postman.

## Endpoints
- `POST /api/auth/register.php`, `POST /api/auth/login.php`, `POST /api/auth/logout.php`
- `GET /api/users/index.php` (autenticado)
- `GET|POST /api/projects/index.php` (listar / criar)
- `GET|PUT|DELETE /api/projects-detail/index.php?id=N` (buscar / atualizar / excluir)

- `GET|POST /api/tasks/index.php` (listar com filtros `project_id`, `status`, `prioridade`, `limite` / criar)
- `GET|PUT|PATCH|DELETE /api/tasks-detail/index.php?id=N` (buscar / atualizar tudo / atualizar só o status / excluir)

Collections do Postman em `postman/` (Semana 5: `taskflow-semana05.postman_collection.json`, com `base_url = http://localhost:8000`).
