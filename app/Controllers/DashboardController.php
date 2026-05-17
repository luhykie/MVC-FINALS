<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index(): string
    {
        $this->requireAuth();

        $students = new Student();

        return $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $students->stats(),
            'recentStudents' => $students->allRecent(),
            'recentDeleted' => $students->recentDeleted(),
        ]);
    }
}
