<?php

use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

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

$app->get('/', function () use ($db) {
    $reservedLive = $db->query('SELECT * FROM live WHERE start > NOW() ORDER BY start ASC')->fetchAll();

    // todo 完全に推測なのできちんとスクレイピングして生きている枠か判定する
    $onAirLive = $db->query('SELECT * FROM live WHERE start < NOW() AND start > DATE_SUB(NOW(),INTERVAL 30 MINUTE) ORDER BY start ASC')->fetchAll();
    $templates = new League\Plates\Engine(__DIR__ . '/../templates');

    // http:// をhttps://に変換している
    // DBでやらないのはniconico側が正式に対応するか不明なため

    $ssl = function ($live) {
        $live['image'] = str_replace('http://', 'https://', $live['image']);
        return $live;
    };
    $reservedLive = array_map($ssl, $reservedLive);
    $onAirLive = array_map($ssl, $onAirLive);

    return $templates->render('index', [
        'current' => $onAirLive,
        'reserved' => $reservedLive,
    ]);
});


$app->get('/__health__', function () use ($db) {
    try {
        $db->exec('SELECT TRUE')->fetch();
        return 'OK';
    } catch (Exception $e) {
        return new Response('NG', 500);
    }
});


$app->run();
