<?php

use Core\Auth;
use Core\Session;
use Core\View\Alert;
use Core\View\Engine as View;

// Layout data nga gamiton sa every logged-in page.
$appConfig = require dirname(__DIR__, 3) . '/config/app.php';

// Flash messages basahon once, then tangtangon from session.
$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($title ?? $appConfig['name']) ?></title>
</head>
<body>
    <header>
        <nav>
            <!-- <a href="/"><?= View::e($appConfig['name']) ?></a>
            <br> -->
            <!-- Main navigation links para sa protected pages. -->
            <a href="/dashboard">Dashboard</a>
            <a href="/students">Students</a>
            <a href="/students/history">History</a>
            <?php if (Auth::check()): ?>
                <!-- Ipakita ang logged-in user's name from session. -->
                <span><?= View::e(Auth::user()['name'] ?? '') ?></span>
            <?php endif; ?>
            <div>
                <?php if (Auth::check()): ?>
                    <!-- Logout gamit ug POST plus CSRF protection. -->
                    <form method="post" action="/logout">
                        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                        <button type="submit">Logout</button>
                    </form>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <!-- One-time success ug error messages from controllers. -->
        <?= Alert::render($success, 'success') ?>
        <?= Alert::render($error, 'error') ?>

        <!-- Diri i-insert sa view engine ang page-specific view content. -->
        <?= $content ?>

    </main>
</body>
</html>
