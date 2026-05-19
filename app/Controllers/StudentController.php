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
        // Controller uses the Student model for all student database actions.
        $this->students = new Student();
    }

    public function index(): string
    {
        // Only logged-in users can view student records.
        $this->requireAuth();

        // Read optional search and page values from the query string.
        $search = trim((string) ($_GET['search'] ?? ''));
        $page = (int) ($_GET['page'] ?? 1);

        // Ask the model for paginated data, then pass it to the view.
        return $this->render('students/index', [
            'title' => 'Students',
            'result' => $this->students->paginate($search, $page),
            'search' => $search,
        ]);
    }

    public function create(): string
    {
        $this->requireAuth();

        // Show an empty form for creating a new student.
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

        // Show records that were archived before deletion.
        return $this->render('students/history', [
            'title' => 'Deleted Student History',
            'students' => $this->students->deletedHistory(),
        ]);
    }

    public function store(): string
    {
        $this->requireAuth();
        $this->validateCsrf();

        // Validate form input before saving it.
        $errors = Validator::student($_POST);

        if ($errors) {
            // Send the user back to the form with their input and validation errors.
            return $this->render('students/form', [
                'title' => 'Add Student',
                'student' => $_POST,
                'errors' => $errors,
                'action' => '/students',
                'mode' => 'create',
            ]);
        }

        try {
            // Create the student, then redirect to its profile page.
            $id = $this->students->create($_POST);
            Session::flash('success', 'Student record created.');
            $this->redirect('/students/' . $id);
        } catch (PDOException $exception) {
            // Duplicate student number or email usually causes a database constraint error.
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

        // Route parameter {id} arrives as a string, so cast it to int for the model.
        $student = $this->students->find((int) $id);

        if (!$student) {
            // Missing student id should show a 404 page.
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        // Show one student's full information.
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
            // Cannot edit a record that does not exist.
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        // Reuse the student form, but set it to edit mode.
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

        // Load the existing record first so invalid ids can return 404.
        $student = $this->students->find((int) $id);
        if (!$student) {
            http_response_code(404);
            return $this->render('errors/404', ['title' => 'Student not found']);
        }

        // Validate the submitted changes.
        $errors = Validator::student($_POST);

        if ($errors) {
            // Merge old data with submitted data so the form keeps what the user typed.
            return $this->render('students/form', [
                'title' => 'Edit Student',
                'student' => array_merge($student, $_POST),
                'errors' => $errors,
                'action' => '/students/' . $id . '/update',
                'mode' => 'edit',
            ]);
        }

        try {
            // Save changes, then redirect back to the student's profile.
            $this->students->update((int) $id, $_POST);
            Session::flash('success', 'Student record updated.');
            $this->redirect('/students/' . $id);
        } catch (PDOException $exception) {
            // Handle duplicate student number or email while keeping the edit form open.
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

        // The model archives the student to deleted_students before deleting it.
        $this->students->delete((int) $id);
        Session::flash('success', 'Student record moved to deleted history.');
        $this->redirect('/students');
    }
}
