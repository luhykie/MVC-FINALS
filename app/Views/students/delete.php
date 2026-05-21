<?php

use Core\Session;
use Core\View\Engine as View;
?>
<section>
    <h1>Delete Student</h1>
    <p>Delete this student?</p>
    <p><?= View::e($student['first_name'] . ' ' . $student['last_name']) ?></p>

    <form method="post" action="/students/<?= (int) $student['id'] ?>/delete">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
        <button type="submit">OK</button>
        <a href="/students">Cancel</a>
    </form>
</section>
