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
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'invalid email format';
        } elseif ($passwordIsEmpty) {
            $errors[] = 'password is required';
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

        $studentNumber = trim((string) ($data['student_number'] ?? ''));
        if ($studentNumber !== '' && !preg_match('/^\d{4}-\d{4}$/', $studentNumber)) {
            $errors['student_number'] = 'Student number must use the format YYYY-0000.';
        }

        foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
            $name = trim((string) ($data[$field] ?? ''));
            if ($name !== '' && !preg_match("/^[A-Za-z][A-Za-z .'-]*$/", $name)) {
                $errors[$field] = "{$label} may only contain letters, spaces, periods, apostrophes, and hyphens.";
            }
        }

        $course = trim((string) ($data['course'] ?? ''));
        if ($course !== '' && !preg_match('/^[A-Za-z0-9 .-]+$/', $course)) {
            $errors['course'] = 'Course may only contain letters, numbers, spaces, periods, and hyphens.';
        }

        // I-validate ang email format kung naay submitted email.
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'invalid email format';
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

        $phone = trim((string) ($data['phone'] ?? ''));
        if ($phone !== '' && !preg_match('/^(09|\+639)\d{9}$/', $phone)) {
            $errors['phone'] = 'Phone must be a valid Philippine mobile number.';
        }

        return $errors;
    }
}
