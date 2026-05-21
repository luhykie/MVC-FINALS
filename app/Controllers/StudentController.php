<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Student;
use PDOException;

class StudentController extends Controller
{
    private Student $students;

    public function __construct()
    {
        // Gamiton ang Student model para sa tanan student database actions.
        $this->students = new Student();
    }

    public function index(): string
    {
        // Logged-in users ra ang maka-view sa student records.
        $this->requireAuth();

        // Kuhaon ang search ug page values gikan sa query string.
        $search = trim((string) ($_GET['search'] ?? ''));
        $page = (int) ($_GET['page'] ?? 1);

        // Pangayo sa model ang paginated data, then ipasa sa view.
        return $this->render('students/index', [
            'title' => 'Students',
            'result' => $this->students->paginate($search, $page),
            'search' => $search,
        ]);
    }

    public function create(): string
    {
        $this->requireAuth();

        // Ipakita ang empty form para mag-add ug new student.
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

        // Ipakita ang records nga gi-archive before gi-delete.
        return $this->render('students/history', [
            'title' => 'Deleted Student History',
            'students' => $this->students->deletedHistory(),
        ]);
    }

    public function store(): string
    {
        $this->requireAuth();
        $this->validateCsrf();

        // I-check una ang form input before i-save.
        $errors = Validator::student($_POST);

        if ($errors) {
            // Ibalik ang user sa form with input ug validation errors.
            return $this->render('students/form', [
                'title' => 'Add Student',
                'student' => $_POST,
                'errors' => $errors,
                'action' => '/students',
                'mode' => 'create',
            ]);
        }

        try {
            // I-create ang student, then adto sa profile page.
            $id = $this->students->create($_POST);
            Session::flash('success', 'Student record created.');
            $this->redirect('/students/' . $id);
        } catch (PDOException $exception) {
            // Duplicate student number or email usually ang cause sa database error.
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

        // Ang route parameter {id} kay string, so himuon ug int para sa model.
        $student = $this->students->find((int) $id);

        if (!$student) {
            // Kung wala ang student id, ipakita ang 404 page.
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        // Ipakita ang full info sa usa ka student.
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
            // Dili ma-edit ang record nga wala nag-exist.
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        // Gamiton balik ang student form, pero edit mode.
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

        // I-load una ang existing record para ang invalid id kay mo-return ug 404.
        $student = $this->students->find((int) $id);
        if (!$student) {
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        // I-check ang gi-submit nga changes.
        $errors = Validator::student($_POST);

        if ($errors) {
            // I-merge ang old data ug submitted data para dili mawala ang gi-type sa user.
            return $this->render('students/form', [
                'title' => 'Edit Student',
                'student' => array_merge($student, $_POST),
                'errors' => $errors,
                'action' => '/students/' . $id . '/update',
                'mode' => 'edit',
            ]);
        }

        try {
            // I-save ang changes, then balik sa student profile.
            $this->students->update((int) $id, $_POST);
            Session::flash('success', 'Student record updated.');
            $this->redirect('/students/' . $id);
        } catch (PDOException $exception) {
            // I-handle ang duplicate student number or email while open gihapon ang edit form.
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

        // I-archive sa model ang student sa deleted_students before i-delete.
        $this->students->delete((int) $id);
        Session::flash('success', 'Student record moved to deleted history.');
        $this->redirect('/students');
    }
}
