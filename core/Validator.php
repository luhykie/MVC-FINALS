<?php

declare(strict_types=1);

namespace Core;

class Validator
{
    public static function student(array $data): array
    {
        // Tigumon ang validation messages by field name.
        $errors = [];

        // Kini nga fields dili dapat empty.
        foreach (['student_number', 'first_name', 'last_name', 'course', 'year_level', 'email', 'status'] as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                $errors[$field] = 'This field is required.';
            }
        }

        // I-validate ang email format kung naay submitted email.
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        // Ang form mo-allow ra ug year levels 1 to 5.
        $year = (int) ($data['year_level'] ?? 0);
        if ($year < 1 || $year > 5) {
            $errors['year_level'] = 'Year level must be between 1 and 5.';
        }

        // Ang status dapat match sa allowed options.
        if (!in_array($data['status'] ?? '', ['Active', 'Inactive', 'Graduated'], true)) {
            $errors['status'] = 'Choose a valid status.';
        }

        return $errors;
    }
}
