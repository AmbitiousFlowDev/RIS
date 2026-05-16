<?php

final class User extends Model
{
    protected string $table = 'User';
    protected string $primaryKey = 'user_id';

    public function allWithEmployees()
    {
        $sql = "
            SELECT 
                u.user_id,
                u.username,
                u.user_category,
                u.employee_id,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM {$this->table} u
            LEFT JOIN Employee e ON u.employee_id = e.employee_id
            WHERE u.deleted_at IS NULL
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function all()
    {
        $sql = "
            SELECT user_id, username, user_category, employee_id
            FROM {$this->table}
            WHERE deleted_at IS NULL
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function createUser(array $data)
    {
        $sql = "INSERT INTO {$this->table} (username, password_hash, user_category, employee_id) 
                VALUES (:username, :password_hash, :user_category, :employee_id)";
        return $this->db->prepare($sql)->execute($data);
    }

    /**
     * Find user by credentials and return UserInterface (Factory creates concrete type).
     * Joins Employee and Role so Factory can use role_code and user_category.
     */
    public function findByCredentials(string $username, string $password): ?UserInterface
    {
        $sql = "
            SELECT u.user_id, u.username, u.password_hash, u.user_category, u.employee_id, r.role_code
            FROM {$this->table} u
            INNER JOIN Employee e ON e.employee_id = u.employee_id AND e.deleted_at IS NULL
            INNER JOIN Role r ON r.role_id = e.role_id AND r.deleted_at IS NULL
            WHERE u.username = ? AND u.deleted_at IS NULL
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $data = $stmt->fetch();

        if (!$data || !$this->verifyPassword($password, (string) $data['password_hash'])) {
            return null;
        }

        if (password_needs_rehash((string) $data['password_hash'], PASSWORD_DEFAULT)) {
            $this->updatePasswordHash((int) $data['user_id'], password_hash($password, PASSWORD_DEFAULT));
        }

        unset($data['password_hash']);
        return UserFactory::create($data);
    }

    private function verifyPassword(string $password, string $storedHash): bool
    {
        if (password_verify($password, $storedHash)) {
            return true;
        }

        // Backward compatibility with existing seed data using MD5.
        return hash_equals($storedHash, md5($password));
    }

    private function updatePasswordHash(int $userId, string $hash): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password_hash = :hash WHERE {$this->primaryKey} = :id");
        $stmt->execute(['hash' => $hash, 'id' => $userId]);
    }
}