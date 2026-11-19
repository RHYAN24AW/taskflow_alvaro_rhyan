# Testes da Semana 5 – CRUD de tarefas

Execução: collection `postman/taskflow-semana05.postman_collection.json` (51 requisições, 111 verificações, 0 falhas),
PHP 8.3 + PostgreSQL 16, `base_url = http://localhost:8000`. Contas fictícias A (usuário 1, projeto 1) e B (usuário 2, projeto 2).

| # | Cenário | Esperado | Obtido | OK |
|---|---------|----------|--------|----|
| 1 | Criar com A, projeto de A, dados válidos | 201; status `pendente`; dono pela sessão | 201 | ✔ |
| 2 | Título vazio / array, prioridade `urgente`, `2026-02-31`, prazo < início, JSON inválido, `project_id=abc` | 400; nenhuma inserção | 400 (banco continuou com 2 linhas) | ✔ |
| 3 | Criar com B no projeto de A / projeto inexistente | 404; nenhuma inserção | 404 | ✔ |
| 4 | Listar com A | 200; só tarefas de A com `projeto_nome` | 200 | ✔ |
| 5 | Filtros válidos; `status=feito`, `limite=0`, `limite=101`, `project_id=abc` | 200 / 400 | 200 / 400 | ✔ |
| 6 | Listar com B, inclusive `project_id` de A | 200; só de B ou `[]` | 200; `[]` | ✔ |
| 7 | Requisições sem sessão | 401 | 401 | ✔ |
| 8 | Buscar: A (própria), B (de A), ID inexistente, `abc`, `0`, sem id | 200; 404; 404; 400 | idem | ✔ |
| 9 | PATCH válido / inválido / de B | 200; 400; 404 (dados intactos) | idem; `updated_at` mudou | ✔ |
| 10 | PUT válido / sem campo obrigatório / prazo < início / de B | 200; 400; 400; 404 (intactos) | idem | ✔ |
| 11 | DELETE de B; DELETE próprio; buscar de novo; repetir | 404 (preservada); 200; 404; 404 | idem | ✔ |
| 12 | POST em tasks-detail / DELETE em tasks | 405 + `Allow` | `Allow: GET, PUT, PATCH, DELETE` / `Allow: GET, POST` | ✔ |

## Exclusão de projeto com tarefas
`DELETE /api/projects-detail/index.php?id=N` em projeto que ainda tem tarefas: o banco recusa (SQLSTATE 23503),
a API responde **409** "O projeto possui tarefas" e as tarefas ficam preservadas. O schema não foi alterado;
a política definitiva fica a cargo do professor.
