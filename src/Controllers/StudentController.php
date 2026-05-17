<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Student;
use PDOException;

class StudentController extends Controller
{
    private Student $students;

    public function __construct()
    {
        $this->students = new Student();
    }

    public function index(): string
    {
        $this->requireAuth();

        $search = trim((string) ($_GET['search'] ?? ''));
        $page = (int) ($_GET['page'] ?? 1);

        return $this->render('students/index', [
            'title' => 'Students',
            'result' => $this->students->paginate($search, $page),
            'search' => $search,
        ]);
    }

    public function create(): string
    {
        $this->requireAuth();

        return $this->render('students/form', [
            'title' => 'Add Student',
            'student' => [],
            'errors' => [],
            'action' => '/students',
            'mode' => 'create',
        ]);
    }

    public function history(): string
    {
        $this->requireAuth();

        return $this->render('students/history', [
            'title' => 'Deleted Student History',
            'students' => $this->students->deletedHistory(),
        ]);
    }

    public function store(): string
    {
        $this->requireAuth();
        $this->validateCsrf();

        $errors = Validator::student($_POST);

        if ($errors) {
            return $this->render('students/form', [
                'title' => 'Add Student',
                'student' => $_POST,
                'errors' => $errors,
                'action' => '/students',
                'mode' => 'create',
            ]);
        }

        try {
            $id = $this->students->create($_POST);
            Session::flash('success', 'Student record created.');
            $this->redirect('/students/' . $id);
        } catch (PDOException $exception) {
            return $this->render('students/form', [
                'title' => 'Add Student',
                'student' => $_POST,
                'errors' => ['student_number' => 'Student number or email may already exist.'],
                'action' => '/students',
                'mode' => 'create',
            ]);
        }
    }

    public function show(string $id): string
    {
        $this->requireAuth();
        $student = $this->students->find((int) $id);

        if (!$student) {
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        return $this->render('students/show', [
            'title' => $student['first_name'] . ' ' . $student['last_name'],
            'student' => $student,
        ]);
    }

    public function edit(string $id): string
    {
        $this->requireAuth();
        $student = $this->students->find((int) $id);

        if (!$student) {
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        return $this->render('students/form', [
            'title' => 'Edit Student',
            'student' => $student,
            'errors' => [],
            'action' => '/students/' . $id . '/update',
            'mode' => 'edit',
        ]);
    }

    public function update(string $id): string
    {
        $this->requireAuth();
        $this->validateCsrf();

        $student = $this->students->find((int) $id);
        if (!$student) {
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        $errors = Validator::student($_POST);

        if ($errors) {
            return $this->render('students/form', [
                'title' => 'Edit Student',
                'student' => array_merge($student, $_POST),
                'errors' => $errors,
                'action' => '/students/' . $id . '/update',
                'mode' => 'edit',
            ]);
        }

        try {
            $this->students->update((int) $id, $_POST);
            Session::flash('success', 'Student record updated.');
            $this->redirect('/students/' . $id);
        } catch (PDOException $exception) {
            return $this->render('students/form', [
                'title' => 'Edit Student',
                'student' => array_merge($student, $_POST),
                'errors' => ['student_number' => 'Student number or email may already exist.'],
                'action' => '/students/' . $id . '/update',
                'mode' => 'edit',
            ]);
        }
    }

    public function destroy(string $id): never
    {
        $this->requireAuth();
        $this->validateCsrf();
        $this->students->delete((int) $id);
        Session::flash('success', 'Student record moved to deleted history.');
        $this->redirect('/students');
    }
}
