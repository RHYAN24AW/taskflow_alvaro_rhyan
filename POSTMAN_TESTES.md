
## 1. Cadastro válido
- Método: `POST`
- URL: `{{base_url}}/api/auth/register.php`
- JSON:
```json
{
  "nome": "Pessoa Teste",
  "email": "pessoa.teste@example.com",
  "senha": "SenhaFicticia123"
}
```
- Esperado: HTTP `201`. A resposta não deve conter senha nem senha_hash.

## 2. E-mail repetido
Repita o cadastro anterior.
- Esperado: HTTP `409`; não deve criar outro usuário.

## 3. Cadastro inválido
- Método: `POST`
- URL: `{{base_url}}/api/auth/register.php`
- JSON:
```json
{
  "nome": "",
  "email": "email-invalido",
  "senha": "123"
}
```
- Esperado: HTTP `422`; nenhuma linha inserida.

## 4. Login válido
- Método: `POST`
- URL: `{{base_url}}/api/auth/login.php`
- JSON:
```json
{
  "email": "pessoa.teste@example.com",
  "senha": "SenhaFicticia123"
}
```
- Esperado: HTTP `200`. O Postman deve manter o cookie `PHPSESSID` para as próximas chamadas.

## 5. Login inválido
Use o mesmo e-mail com uma senha errada.
- Esperado: HTTP `401` e mensagem genérica `Credenciais inválidas.`

## 6. Endpoint protegido sem login
Antes de autenticar (ou depois do logout), envie:
- Método: `GET`
- URL: `{{base_url}}/api/users/index.php`
- Esperado: HTTP `401`.

## 7. Endpoint protegido após login
Faça novamente o login válido e, sem apagar os cookies do domínio, envie:
- Método: `GET`
- URL: `{{base_url}}/api/users/index.php`
- Esperado: HTTP `200`, lista de usuários sem a coluna `senha_hash`.

## 8. Logout e nova tentativa
- Método: `POST`
- URL: `{{base_url}}/api/auth/logout.php`
- Body: pode ser vazio.
- Esperado: HTTP `200`.
Depois repita o GET protegido.
- Esperado: HTTP `401`.

## Variável da Collection
Em **Variables**, crie:
- `base_url` = `http://localhost/TaskFlow`

Use somente dados fictícios. Não exporte nem compartilhe `config/database.php` com credenciais locais. Não inclua hashes ou senhas nos prints/relatórios.
