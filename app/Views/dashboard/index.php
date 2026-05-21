<?php

use Core\View\Engine as View;
?>
<h1>Dashboard</h1>
<p>Overview of student records and deleted records.</p>

<!-- <p><a href="/students/create">Add Student</a></p> -->

<!-- Counts gikan sa Student::stats() sa dashboard controller. -->
<section>
    <p><?= (int) $stats['total'] ?><br>Total Students</p>
    <p><?= (int) $stats['active'] ?><br>Active Students</p>
    <p><?= (int) $stats['graduated'] ?><br>Graduated Students</p>
    <p><?= (int) $stats['deleted'] ?><br>Deleted Students</p>
</section>

<section>
    <h2>List of Students</h2>
    <p><a href="/students">View All</a></p>

    <!-- Ipakita ang friendly message kung wala pay student records. -->
    <?php if (!$recentStudents): ?>
        <p>No student records yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Student No.</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- I-escape ang text from database before ipakita sa HTML. -->
                <?php foreach ($recentStudents as $student): ?>
                    <tr>
                        <td><?= View::e($student['student_number']) ?></td>
                        <td><a href="/students/<?= (int) $student['id'] ?>"><?= View::e($student['first_name'] . ' ' . $student['last_name']) ?></a></td>
                        <td><?= View::e($student['course']) ?></td>
                        <td><?= View::e($student['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

