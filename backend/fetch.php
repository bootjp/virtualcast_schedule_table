<?php

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

require_once __DIR__. '/vendor/autoload.php';

$client = new \GuzzleHttp\Client([
    'cookies' => true,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36',
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

$rowExistsIds = $db->query('SELECT live_id FROM live ORDER BY live_id')->fetchAll();
$exitsIds = [];
// 探索コストを減らすためにkeyに入れる
foreach ($rowExistsIds as $id) {
    $exitsIds[$id['live_id']] = null;
}

$baseUri = 'https://live.nicovideo.jp/search';
$offSet = 0;
// https://live.nicovideo.jp/search?keyword=%E3%83%90%E3%83%BC%E3%83%81%E3%83%A3%E3%83%AB%E3%82%AD%E3%83%A3%E3%82%B9%E3%83%88&status=reserved&sortOrder=recentDesc&isTagSearch=true
$res = $client->get($baseUri, [
    'query' => [
        'keyword' => 'バーチャルキャスト',
        'status' => 'reserved',
        'sortOrder' => 'recentDesc',
        'isTagSearch' => 'true'
    ]
])->getBody()->getContents();

$crawler = new DomCrawler($res);
/** @var []DomCrawler $res */
$res = $crawler->filter('.searchPage-Layout_Section')->first()->each(function (DomCrawler $node, $i) {
    return $node->filter('.searchPage-ProgramList_Item');
});
$result = [];
foreach ($res as  $index => $row) {
    /** @var DomCrawler $row */
    $row->each(function (DomCrawler $node, $i) use (&$result, $index) {
        $result[$index + $i]['title'] = trim($node->filter('.searchPage-ProgramList_TitleLink')->text());
        $result[$index + $i]['live_id'] = str_replace(
            'watch/',
            '',
            $node->filter('.searchPage-ProgramList_TitleLink')->attr('href')
        );
        $time = $node->filter('.searchPage-ProgramList_DataText')->text();
        $time = str_replace(' 開始 ', '', $time);
        $result[$index + $i]['start'] = DateTime::createFromFormat('Y/m/d H:i', $time, new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s');
        $result[$index + $i]['owner'] = trim($node->filter('.searchPage-ProgramList_UserName')->text());
        $result[$index + $i]['image'] = $node->filter('.searchPage-ProgramList_UserImage')->attr('src');
    });
}

foreach ($result as $live) {
    try {
        $db->pdo->beginTransaction();
        if (!array_key_exists($live['live_id'], $exitsIds)) {
            $db->insert('live', $live);
        } else {
            $db->update('live', $live, ['live_id' => $live['live_id']]);
        }
        $db->pdo->commit();
    } catch (Exception $e) {
        $db->pdo->rollBack();
    }
}


