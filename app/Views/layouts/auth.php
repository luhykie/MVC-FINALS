<?php

use Core\Session;
use Core\View\Engine as View;

// Auth layout gamiton sa login-related pages.
$appConfig = require dirname(__DIR__, 3) . '/config/app.php';

// Flash messages mo-show sa login/logout errors or success notices once.
$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($title ?? 'Login') ?></title>
</head>
<body>
    <main>
        <section>
            <h1><?= View::e($appConfig['name']) ?></h1>

            <!-- One-time messages from AuthController. -->
            <?php if ($success): ?>
                <div><?= View::e($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div><?= View::e($error) ?></div>
            <?php endif; ?>

            <!-- Diri i-insert ang login form content. -->
            <?= $content ?>
        </section>
    </main>
</body>
</html>
