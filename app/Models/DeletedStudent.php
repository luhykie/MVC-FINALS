<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Model;

class DeletedStudent extends Model
{
    // Connects this model to the table that stores archived deleted students.
    protected string $table = 'deleted_students';

    public static function history(): array
    {
        // Show all deleted students with the newest deletion first.
        return self::allOrdered('deleted_at', 'DESC');
    }

    public static function recent(int $limit = 5): array
    {
        // Get a small list of the most recently deleted students.
        return self::latestRecords($limit, 'deleted_at');
    }

    public static function archive(array $student): int
    {
        // Copy the student data into deleted_students before the original row is deleted.
        return self::insertRecord([
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
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
