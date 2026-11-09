<?php

class Project
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function criar(string $nome, ?string $descricao, int $userId): array
    {
        // Os valores entram por parâmetros, nunca por concatenação.
        $sql = 'INSERT INTO projects (nome, descricao, user_id)
                VALUES (:nome, :descricao, :user_id)
                RETURNING id, nome, descricao, status,
                          user_id, created_at, updated_at';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'descricao' => $descricao,
            'user_id' => $userId,
        ]);

        // RETURNING devolve a linha que acabou de ser inserida.
        $projeto = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($projeto === false) {
            throw new RuntimeException('Projeto não retornado pelo INSERT.');
        }

        return $projeto;
    }

    public function listar(int $userId): array
    {
        // O filtro restringe o resultado ao dono autenticado.
        $sql = 'SELECT id, nome, descricao, status,
                       user_id, created_at, updated_at
                FROM projects
                WHERE user_id = :user_id
                ORDER BY created_at DESC, id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id, int $userId): ?array
    {
        $sql = 'SELECT id, nome, descricao, status,
                       user_id, created_at, updated_at
                FROM projects
                WHERE id = :id AND user_id = :user_id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

        return $projeto === false ? null : $projeto;
    }

    public function atualizar(
        int $id,
        int $userId,
        string $nome,
        ?string $descricao,
        string $status
    ): ?array {
        $sql = 'UPDATE projects
                SET nome = :nome, descricao = :descricao,
                    status = :status, updated_at = NOW()
                WHERE id = :id AND user_id = :user_id
                RETURNING id, nome, descricao, status,
                          user_id, created_at, updated_at';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'descricao' => $descricao,
            'status' => $status,
            'id' => $id,
            'user_id' => $userId,
        ]);

        $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

        return $projeto === false ? null : $projeto;
    }

    public function deletar(int $id, int $userId): bool
    {
        $sql = 'DELETE FROM projects
                WHERE id = :id AND user_id = :user_id
                RETURNING id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}
