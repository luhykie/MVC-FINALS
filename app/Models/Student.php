<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Connection as Database;
use Core\Database\Model;
use Throwable;

class Student extends Model
{
    protected string $table = 'students';

    public function paginate(string $search = '', int $page = 1, int $perPage = 8): array
    {
        return self::searchPage(
            ['student_number', 'first_name', 'last_name', 'course', 'email'],
            $search,
            $page,
            $perPage
        );
    }

    public function allRecent(int $limit = 5): array
    {
        return self::latestRecords($limit);
    }

    public function stats(): array
    {
        return [
            'total' => self::countRecords(),
            'active' => self::countRecords(['status' => 'Active']),
            'graduated' => self::countRecords(['status' => 'Graduated']),
            'deleted' => DeletedStudent::countRecords(),
        ];
    }

    public function deletedHistory(): array
    {
        return DeletedStudent::history();
    }

    public function recentDeleted(int $limit = 5): array
    {
        return DeletedStudent::recent($limit);
    }

    public function find(int $id): ?array
    {
        $student = self::findRecord($id);

        return $student?->toArray();
    }

    public function create(array $data): int
    {
        return self::insertRecord(array_merge($this->payload($data), [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function update(int $id, array $data): void
    {
        self::updateRecord($id, array_merge($this->payload($data), [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function delete(int $id): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $student = $this->find($id);

            if ($student) {
                DeletedStudent::archive($student);
            }

            self::deleteRecord($id);
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
