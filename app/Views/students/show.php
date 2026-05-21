<?php

use Core\Session;
use Core\View\Engine as View;
?>
<div>
    <div>
        <!-- Student data gi-load by StudentController::show(). -->
        <h1><?= View::e($student['first_name'] . ' ' . $student['last_name']) ?></h1>
        <p><?= View::e($student['student_number']) ?></p>
    </div>
    <div>
        <a href="/students">Back</a>
        <a href="/students/<?= (int) $student['id'] ?>/edit">Edit</a>
    </div>
</div>

<section>
    <div>
        <div>
            <h3>Course</h3>
            <p><?= View::e($student['course']) ?></p>
        </div>
        <div>
            <h3>Year Level</h3>
            <p><?= (int) $student['year_level'] ?></p>
        </div>
        <div>
            <h3>Email</h3>
            <p><?= View::e($student['email']) ?></p>
        </div>
        <div>
            <h3>Phone</h3>
            <p><?= View::e($student['phone'] ?: 'N/A') ?></p>
        </div>
        <div>
            <h3>Status</h3>
            <p><span><?= View::e($student['status']) ?></span></p>
        </div>
        <div>
            <h3>Address</h3>
            <p><?= View::e($student['address'] ?: 'N/A') ?></p>
        </div>
    </div>

    <!-- Delete form mo-move sa student into deleted history before tangtangon. -->
    <form method="post" action="/students/<?= (int) $student['id'] ?>/delete" onsubmit="return confirm('Delete this student?');">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
        <button type="submit">Delete Student</button>
    </form>
</section>
