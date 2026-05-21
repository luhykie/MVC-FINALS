<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index(): string
    {
        // Protected ang dashboard, so guests kay i-redirect sa login.
        $this->requireAuth();

        // Student model mohatag ug counts ug recent records para sa dashboard.
        $students = new Student();

        return $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $students->stats(),
            'recentStudents' => $students->allRecent(),
            'recentDeleted' => $students->recentDeleted(),
        ]);
    }
}
