<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Connection as Database;
use Core\Database\Model;
use Throwable;

class Student extends Model
{
    // I-connect ni nga model sa "students" table.
    protected string $table = 'students';

    public function paginate(string $search = '', int $page = 1, int $perPage = 8): array
    {
        // Mangita ug students by common fields ug mo-return ug paginated results para sa index page.
        return self::searchPage(
            ['student_number', 'first_name', 'last_name', 'course', 'email'],
            $search,
            $page,
            $perPage
        );
    }

    public function allRecent(int $limit = 5): array
    {
        // Kuhaon ang newest student records para sa dashboard lists.
        return self::latestRecords($limit);
    }

    public function stats(): array
    {
        // I-count ang students by status. Deleted records kay counted from another table.
        return [
            'total' => self::countRecords(),
            'active' => self::countRecords(['status' => 'Active']),
            'graduated' => self::countRecords(['status' => 'Graduated']),
            'deleted' => DeletedStudent::countRecords(),
        ];
    }

    public function deletedHistory(): array
    {
        // Deleted students kay gi-store sa deleted_students table, dili permanently lost.
        return DeletedStudent::history();
    }

    public function recentDeleted(int $limit = 5): array
    {
        // Kuhaon ang latest archived/deleted student records.
        return DeletedStudent::recent($limit);
    }

    public function find(int $id): ?array
    {
        // Gamiton ang base ORM method para mangita ug usa ka student by primary key.
        $student = self::findRecord($id);

        return $student?->toArray();
    }

    public function create(array $data): int
    {
        // Limpyohan ang form data, add timestamps, then i-insert sa students table.
        return self::insertRecord(array_merge($this->payload($data), [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function update(int $id, array $data): void
    {
        // Limpyohan ang form data ug i-refresh ang updated_at timestamp before saving.
        self::updateRecord($id, array_merge($this->payload($data), [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function delete(int $id): void
    {
        // Gamiton ang raw PDO connection diri kay ang archive ug delete dapat sabay.
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $student = $this->find($id);

            if ($student) {
                // I-copy ang student sa deleted_students before tangtangon from students.
                DeletedStudent::archive($student);
            }

            self::deleteRecord($id);

            // I-commit lang kung success ang archive ug delete.
            $pdo->commit();
        } catch (Throwable $exception) {
            // Kung naay fail, i-undo ang archive/delete para consistent ang database.
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function payload(array $data): array
    {
        // Kini mo-keep ra sa columns nga allowed i-save ug mo-normalize sa values.
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
