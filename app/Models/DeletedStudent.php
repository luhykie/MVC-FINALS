<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Model;

class DeletedStudent extends Model
{
    // I-connect ni nga model sa table nga mo-store sa archived deleted students.
    protected string $table = 'deleted_students';

    public static function history(): array
    {
        // Ipakita tanan deleted students, newest deletion ang una.
        return self::allOrdered('deleted_at', 'DESC');
    }

    public static function recent(int $limit = 5): array
    {
        // Kuhaon ang small list sa most recently deleted students.
        return self::latestRecords($limit, 'deleted_at');
    }

    public static function archive(array $student): int
    {
        // I-copy ang student data sa deleted_students before i-delete ang original row.
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
