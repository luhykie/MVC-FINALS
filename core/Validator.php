<?php

declare(strict_types=1);

namespace Core;

class Validator
{
    public static function login(array $data): array
    {
        // Tigumon ang login validation messages.
        $errors = [];

        $emailIsEmpty = trim((string) ($data['email'] ?? '')) === '';
        $passwordIsEmpty = (string) ($data['password'] ?? '') === '';

        // Email ug password dili dapat empty.
        if ($emailIsEmpty && $passwordIsEmpty) {
            $errors[] = 'fields are empty';
        } elseif ($emailIsEmpty) {
            $errors[] = 'email is required';
        } elseif ($passwordIsEmpty) {
            $errors[] = 'password is required';
        }

        // I-check ang email format kung naay gi-type nga email.
        if (!$errors && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }

        return $errors;
    }

    public static function student(array $data): array
    {
        // Tigumon ang validation messages by field name.
        $errors = [];

        // Kini nga fields dili dapat empty.
        $requiredFields = [
            'student_number' => 'fields are empty',
            'first_name' => 'fields are empty',
            'last_name' => 'fields are empty',
            'course' => 'fields are empty',
            'year_level' => 'fields are empty',
            'email' => 'fields are empty',
            'status' => 'fields are empty',
        ];

        foreach ($requiredFields as $field => $message) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                $errors[$field] = $message;
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
