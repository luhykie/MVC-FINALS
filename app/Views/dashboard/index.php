<?php

use Core\View\Engine as View;
?>
<h1>Dashboard</h1>
<p>Overview of student records and deleted records.</p>

<!-- <p><a href="/students/create">Add Student</a></p> -->

<!-- Counts come from Student::stats() in the dashboard controller. -->
<section>
    <p><?= (int) $stats['total'] ?><br>Total Students</p>
    <p><?= (int) $stats['active'] ?><br>Active Students</p>
    <p><?= (int) $stats['graduated'] ?><br>Graduated Students</p>
    <p><?= (int) $stats['deleted'] ?><br>Deleted Students</p>
</section>

<section>
    <h2>List of Students</h2>
    <p><a href="/students">View All</a></p>

    <!-- Show a friendly message when there are no student records yet. -->
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
                <!-- Escape text from the database before showing it in HTML. -->
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

<section>
    <h2>Deleted History</h2>
    <!-- <p><a href="/students/history">View History</a></p> -->

    <!-- recentDeleted comes from the deleted_students table. -->
    <?php if (!$recentDeleted): ?>
        <p>No deleted student records.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Student No.</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Deleted At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentDeleted as $student): ?>
                    <tr>
                        <td><?= View::e($student['student_number']) ?></td>
                        <td><?= View::e($student['first_name'] . ' ' . $student['last_name']) ?></td>
                        <td><?= View::e($student['course']) ?></td>
                        <td><?= View::e($student['deleted_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
