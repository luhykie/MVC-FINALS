<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

class Student
{
    public function paginate(string $search = '', int $page = 1, int $perPage = 8): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];

        if ($search !== '') {
            $where = 'WHERE student_number LIKE :search OR first_name LIKE :search OR last_name LIKE :search OR course LIKE :search OR email LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $pdo = Database::connection();
        $count = $pdo->prepare("SELECT COUNT(*) FROM students {$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $statement = $pdo->prepare("SELECT * FROM students {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function allRecent(int $limit = 5): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM students ORDER BY created_at DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function stats(): array
    {
        $pdo = Database::connection();

        return [
            'total' => (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
            'active' => (int) $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn(),
            'graduated' => (int) $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Graduated'")->fetchColumn(),
            'deleted' => (int) $pdo->query('SELECT COUNT(*) FROM deleted_students')->fetchColumn(),
        ];
    }

    public function deletedHistory(): array
    {
        return Database::connection()
            ->query('SELECT * FROM deleted_students ORDER BY deleted_at DESC')
            ->fetchAll();
    }

    public function recentDeleted(int $limit = 5): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM deleted_students ORDER BY deleted_at DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $student = $statement->fetch();

        return $student ?: null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO students (student_number, first_name, last_name, course, year_level, email, phone, address, status, created_at, updated_at)
             VALUES (:student_number, :first_name, :last_name, :course, :year_level, :email, :phone, :address, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );
        $statement->execute($this->payload($data));

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $payload = $this->payload($data);
        $payload['id'] = $id;

        $statement = Database::connection()->prepare(
            'UPDATE students
             SET student_number = :student_number, first_name = :first_name, last_name = :last_name, course = :course,
                 year_level = :year_level, email = :email, phone = :phone, address = :address, status = :status,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute($payload);
    }

    public function delete(int $id): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $student = $this->find($id);

            if ($student) {
                $archive = $pdo->prepare(
                    'INSERT INTO deleted_students
                     (original_student_id, student_number, first_name, last_name, course, year_level, email, phone, address, status, created_at, updated_at, deleted_at)
                     VALUES
                     (:original_student_id, :student_number, :first_name, :last_name, :course, :year_level, :email, :phone, :address, :status, :created_at, :updated_at, CURRENT_TIMESTAMP)'
                );
                $archive->execute([
                    'original_student_id' => $student['id'],
                    'student_number' => $student['student_number'],
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                    'course' => $student['course'],
                    'year_level' => $student['year_level'],
                    'email' => $student['email'],
                    'phone' => $student['phone'],
                    'address' => $student['address'],
                    'status' => $student['status'],
                    'created_at' => $student['created_at'],
                    'updated_at' => $student['updated_at'],
                ]);
            }

            $statement = $pdo->prepare('DELETE FROM students WHERE id = :id');
            $statement->execute(['id' => $id]);
            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function payload(array $data): array
    {
        return [
            'student_number' => trim((string) $data['student_number']),
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'course' => trim((string) $data['course']),
            'year_level' => (int) $data['year_level'],
            'email' => trim((string) $data['email']),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'address' => trim((string) ($data['address'] ?? '')),
            'status' => trim((string) $data['status']),
        ];
    }
}
