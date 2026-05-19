<?php

use Core\Auth;
use Core\Session;
use Core\View\Engine as View;

// Layout data used by every logged-in page.
$appConfig = require dirname(__DIR__, 3) . '/config/app.php';

// Flash messages are read once, then removed from the session.
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
            <!-- Main navigation links for protected pages. -->
            <a href="/dashboard">Dashboard</a>
            <a href="/students">Students</a>
            <a href="/students/history">History</a>
            <?php if (Auth::check()): ?>
                <!-- Show the logged-in user's name from the session. -->
                <span><?= View::e(Auth::user()['name'] ?? '') ?></span>
            <?php endif; ?>
            <div>
                <?php if (Auth::check()): ?>
                    <!-- Logout uses POST plus CSRF protection. -->
                    <form method="post" action="/logout">
                        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                        <button type="submit">Logout</button>
                    </form>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <!-- One-time success and error messages from controllers. -->
        <?php if ($success): ?>
            <div><?= View::e($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div><?= View::e($error) ?></div>
        <?php endif; ?>

        <!-- Page-specific view content is inserted here by the view engine. -->
        <?= $content ?>

    </main>
</body>
</html>
