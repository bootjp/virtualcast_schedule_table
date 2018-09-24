<?php

require_once __DIR__. '/vendor/autoload.php';

$client = new \GuzzleHttp\Client([
    'cookies' => true,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
        'Accept' =>  'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Encoding' =>  'gzip, deflate',
        'Accept-Language' => 'ja,en-US;q=0.9,en;q=0.8',
        'Cache-Control' => 'no-cache',
    ]
]);

$db = new Medoo\Medoo([
    'database_type' => 'mariadb',
    'database_name' => getenv('MYSQL_DATABASE'),
    'server' => getenv('MYSQL_HOST'),
    'username' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'charset' => 'utf8mb4',
]);

$liveIds = $db->query('SELECT * FROM live WHERE start > NOW() ORDER BY start ASC')->fetchAll();

foreach ($liveIds as $live) {
    $uri = "http://live.nicovideo.jp/watch/{$live['live_id']}";
    echo "$uri\n";
    $body = $client->get($uri)->getBody()->getContents();

    if (mb_strpos($body, 'この番組は放送者により削除されました。') !== false) {
        try {
            $db->pdo->beginTransaction();
            $db->delete('live', ['live_id' => $live['live_id']]);
            $db->pdo->commit();

        } catch (Exception $e) {
            $db->pdo->rollBack();
        }
    }
    sleep(1);
}


