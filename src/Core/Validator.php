<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    public static function student(array $data): array
    {
        $errors = [];

        foreach (['student_number', 'first_name', 'last_name', 'course', 'year_level', 'email', 'status'] as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                $errors[$field] = 'This field is required.';
            }
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        $year = (int) ($data['year_level'] ?? 0);
        if ($year < 1 || $year > 5) {
            $errors['year_level'] = 'Year level must be between 1 and 5.';
        }

        if (!in_array($data['status'] ?? '', ['Active', 'Inactive', 'Graduated'], true)) {
            $errors['status'] = 'Choose a valid status.';
        }

        return $errors;
    }
}
