<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Database;

$pdo = Database::connection();
$config = require dirname(__DIR__) . '/config/config.php';
$schemaFile = $config['database']['driver'] === 'mysql' ? 'mysql_schema.sql' : 'schema.sql';
$schema = file_get_contents(dirname(__DIR__) . '/database/' . $schemaFile);
$seed = file_get_contents(dirname(__DIR__) . '/database/seed.sql');

$pdo->exec($schema);

$hasUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$hasStudents = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();

if ($hasUsers === 0 && $hasStudents === 0) {
    $pdo->exec($seed);
    echo "Database created and seeded.\n";
} else {
    echo "Database already has data. Schema checked only.\n";
}
