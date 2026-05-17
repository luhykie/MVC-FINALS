<?php

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
    <title><?= View::e($title ?? 'Login') ?></title>
</head>
<body>
    <main>
        <section>
            <h1><?= View::e($appConfig['app']['name']) ?></h1>

            <?php if ($success): ?>
                <div><?= View::e($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div><?= View::e($error) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </section>
    </main>
</body>
</html>
