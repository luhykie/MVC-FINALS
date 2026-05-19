<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Connection as Database;
use Core\Database\Model;
use Throwable;

class Student extends Model
{
    // Connects this model to the "students" table.
    protected string $table = 'students';

    public function paginate(string $search = '', int $page = 1, int $perPage = 8): array
    {
        // Search students by common fields and return paginated results for the index page.
        return self::searchPage(
            ['student_number', 'first_name', 'last_name', 'course', 'email'],
            $search,
            $page,
            $perPage
        );
    }

    public function allRecent(int $limit = 5): array
    {
        // Gets the newest student records for dashboard-style lists.
        return self::latestRecords($limit);
    }

    public function stats(): array
    {
        // Counts students by status. Deleted records are counted from another table.
        return [
            'total' => self::countRecords(),
            'active' => self::countRecords(['status' => 'Active']),
            'graduated' => self::countRecords(['status' => 'Graduated']),
            'deleted' => DeletedStudent::countRecords(),
        ];
    }

    public function deletedHistory(): array
    {
        // Deleted students are stored in the deleted_students table, not permanently lost.
        return DeletedStudent::history();
    }

    public function recentDeleted(int $limit = 5): array
    {
        // Gets the latest archived/deleted student records.
        return DeletedStudent::recent($limit);
    }

    public function find(int $id): ?array
    {
        // Uses the base ORM method to find one student by primary key.
        $student = self::findRecord($id);

        return $student?->toArray();
    }

    public function create(array $data): int
    {
        // Clean the form data, add timestamps, then insert it into the students table.
        return self::insertRecord(array_merge($this->payload($data), [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function update(int $id, array $data): void
    {
        // Clean the form data and refresh the updated_at timestamp before saving.
        self::updateRecord($id, array_merge($this->payload($data), [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function delete(int $id): void
    {
        // Use the raw PDO connection here because archive + delete must happen together.
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $student = $this->find($id);

            if ($student) {
                // Copy the student to deleted_students before removing it from students.
                DeletedStudent::archive($student);
            }

            self::deleteRecord($id);

            // Commit only after both archive and delete succeed.
            $pdo->commit();
        } catch (Throwable $exception) {
            // If anything fails, undo the archive/delete so the database stays consistent.
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function payload(array $data): array
    {
        // This keeps only the columns allowed to be saved and normalizes their values.
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
