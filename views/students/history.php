<?php

use App\Core\View;
?>
<h1>Deleted Student History</h1>
<p><a href="/dashboard">Back to Dashboard</a></p>

<?php if (!$students): ?>
    <p>No deleted student records.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Student No.</th>
                <th>Name</th>
                <th>Course</th>
                <th>Year</th>
                <th>Email</th>
                <th>Status</th>
                <th>Deleted At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= View::e($student['student_number']) ?></td>
                    <td><?= View::e($student['first_name'] . ' ' . $student['last_name']) ?></td>
                    <td><?= View::e($student['course']) ?></td>
                    <td><?= (int) $student['year_level'] ?></td>
                    <td><?= View::e($student['email']) ?></td>
                    <td><?= View::e($student['status']) ?></td>
                    <td><?= View::e($student['deleted_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
