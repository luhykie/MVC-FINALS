<?php

use App\Core\Session;
use App\Core\View;

$items = $result['items'];
$page = $result['page'];
$pages = $result['pages'];
?>
<div>
    <h1>Student Records</h1>
    <p><?= (int) $result['total'] ?> record(s)</p>
    <a href="/students/create">Add Student</a>
    <!-- <a href="/students/history">Deleted History</a> -->
</div>

<section>
    <form method="get" action="/students">
        <label for="search">Search</label>
        <input id="search" name="search" value="<?= View::e($search) ?>" placeholder="Name, student no., course, or email">
        <button type="submit">Search</button>
        <?php if ($search !== ''): ?>
            <a href="/students">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (!$items): ?>
        <p>No students matched your search.</p>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $student): ?>
                    <tr>
                        <td><?= View::e($student['student_number']) ?></td>
                        <td><?= View::e($student['first_name'] . ' ' . $student['last_name']) ?></td>
                        <td><?= View::e($student['course']) ?></td>
                        <td><?= (int) $student['year_level'] ?></td>
                        <td><?= View::e($student['email']) ?></td>
                        <td><?= View::e($student['status']) ?></td>
                        <td>
                            <a href="/students/<?= (int) $student['id'] ?>">View</a>
                            <a href="/students/<?= (int) $student['id'] ?>/edit">Edit</a>
                            <form method="post" action="/students/<?= (int) $student['id'] ?>/delete" onsubmit="return confirm('Delete this student?');">
                                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div>
            <?php if ($page > 1): ?>
                <a href="/students?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $pages ?></span>
            <?php if ($page < $pages): ?>
                <a href="/students?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
