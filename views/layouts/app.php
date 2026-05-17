<?php

use App\Core\Auth;
use App\Core\Session;
use App\Core\View;

$appConfig = require dirname(__DIR__, 2) . '/config/config.php';
$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($title ?? $appConfig['app']['name']) ?></title>
</head>
<body>
    <header>
        <nav>
            <a href="/"><?= View::e($appConfig['app']['name']) ?></a>
            <br>
            <a href="/dashboard">Dashboard</a>
            <a href="/students">Students</a>
            <a href="/students/history">History</a>
            <?php if (Auth::check()): ?>
                <span><?= View::e(Auth::user()['name'] ?? '') ?></span>
            <?php endif; ?>
            <div>
                <?php if (Auth::check()): ?>
                    <form method="post" action="/logout">
                        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                        <button type="submit">Logout</button>
                    </form>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <?php if ($success): ?>
            <div><?= View::e($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div><?= View::e($error) ?></div>
        <?php endif; ?>

        <?= $content ?>

        <footer>PHP MVC Final Examination Project</footer>
    </main>
</body>
</html>
