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

Collections do Postman em `postman/`.
