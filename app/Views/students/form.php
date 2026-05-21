<?php

use Core\Session;
use Core\View\Engine as View;

// Defaults para same form magamit sa create ug edit pages.
$title = $title ?? 'Student Form';
$student = $student ?? [];
$errors = $errors ?? [];
$action = $action ?? '/students';
$mode = $mode ?? 'create';

// Helpers para mas short ang form fields ug error display below.
$value = fn (string $key): string => (string) ($student[$key] ?? '');
$fieldError = fn (string $key): string => isset($errors[$key]) ? '<div>' . View::e($errors[$key]) . '</div>' : '';
?>
<div>
    <div>
        <h1><?= View::e($title) ?></h1>
        <p><?= $mode === 'create' ? 'Create a new student record.' : 'Update student information.' ?></p>
    </div>
    <a href="/students">Back</a>
</div>

<section>
    <form method="post" action="<?= View::e($action) ?>">
        <!-- CSRF token i-check sa controller before saving. -->
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

        <div>
            <div>
                <label for="student_number">Student Number</label>
                <input id="student_number" name="student_number" value="<?= View::e($value('student_number')) ?>" required>
                <?= $fieldError('student_number') ?>
            </div>

            <div>
                <label for="course">Course</label>
                <input id="course" name="course" value="<?= View::e($value('course')) ?>" placeholder="BSIT" required>
                <?= $fieldError('course') ?>
            </div>

            <div>
                <label for="first_name">First Name</label>
                <input id="first_name" name="first_name" value="<?= View::e($value('first_name')) ?>" required>
                <?= $fieldError('first_name') ?>
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input id="last_name" name="last_name" value="<?= View::e($value('last_name')) ?>" required>
                <?= $fieldError('last_name') ?>
            </div>

            <div>
                <label for="year_level">Year Level</label>
                <select id="year_level" name="year_level" required>
                    <!-- Mag-build ug year choices 1 to 5 ug i-select ang current value kung editing. -->
                    <?php for ($year = 1; $year <= 5; $year++): ?>
                        <option value="<?= $year ?>" <?= (int) $value('year_level') === $year ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endfor; ?>
                </select>
                <?= $fieldError('year_level') ?>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <!-- Kini nga statuses dapat match sa allowed values sa Validator::student(). -->
                    <?php foreach (['Active', 'Inactive', 'Graduated'] as $status): ?>
                        <option value="<?= $status ?>" <?= $value('status') === $status ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $fieldError('status') ?>
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= View::e($value('email')) ?>" required>
                <?= $fieldError('email') ?>
            </div>

            <div>
                <label for="phone">Phone</label>
                <input id="phone" name="phone" value="<?= View::e($value('phone')) ?>">
            </div>

            <div>
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"><?= View::e($value('address')) ?></textarea>
            </div>
        </div>

        <div>
            <button type="submit"><?= $mode === 'create' ? 'Create Student' : 'Save Changes' ?></button>
            <a href="/students">Cancel</a>
        </div>
    </form>
</section>
