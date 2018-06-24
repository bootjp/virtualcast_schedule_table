<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$db = new Medoo\Medoo([
    'database_type' => 'mariadb',
    'database_name' => getenv('MYSQL_DATABASE'),
    'server' => getenv('MYSQL_HOST'),
    'username' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'charset' => 'utf8mb4',
]);

$app->get('/', function () use($db) {
    $db->exec('SET SESSION time_zone = Asia/Tokyo');
    $lives = $db->query('SELECT * FROM live WHERE start > NOW() ORDER BY start ASC')->fetchAll();
    $templates = new League\Plates\Engine(__DIR__ . '/../templates');

    // http:// をhttps://に変換している
    // DBでやらないのはniconico側が正式に対応するか不明なため
    $lives = array_map(function ($live) {
        $live['image'] = str_replace('http://', 'https://', $live['image']);
        return $live;
    }, $lives);

    return $templates->render('index', [
        'lives' => $lives
    ]);
});

$app->run();
