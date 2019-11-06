<?php

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

$dom = new PHPHtmlParser\Dom();
$dom->load($res);
$html = $dom->find('.searchPage-ProgramList')[0];
var_dump($html);exit;
$reserved = $dom->find('.searchPage\-ProgramList_Item');
var_dump($res, $reserved);exit;
/* @var $live PHPHtmlParser\Dom() */

$result = [];

foreach ($reserved as $index => $live) {
    $data = parse($live->innerHtml);
    $result[$index]['title'] = $data['title'];
    $result[$index]['live_id'] = $data['id'];

    $str = $live->find('.elapsed-time')[0]->innerHtml;
    $time = trim(strip_tags($str));
    // 曜日を消す
    $time = preg_replace('#\((.+?)\)#', ' ', $time);
    $time = str_replace('開始', '', $time);
    $result[$index]['start'] = DateTime::createFromFormat('Y/m/d H:i', $time, new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s');


    var_dump(result);exit;
    $provider = $live->find('.provider-label')[0]->getAttribute('data-provider-type');
    switch ($provider) {
    case 'official':
        $style = $live->find('.official-thumbnail')[0]->getAttribute('style'). "\n";;;
        $matches = [];
        preg_match('|background-image:url\((?<bg_url>.+?)\)|', $style, $matches);
        $result[$index]['owner'] = '公式';
        $result[$index]['image'] = $matches['bg_url'];
        break;
    case 'channel':
        $result[$index]['owner'] = 'チャンネル';
        $result[$index]['image'] = $live->find('.alt-thumbnail-provider-icon')[0]->getAttribute('src');
        break;

    case 'user':
        $result[$index]['owner'] = $live->find('.provider-name')[0]->innerHtml;
        $result[$index]['image'] = $live->find('.alt-thumbnail-provider-icon')[0]->getAttribute('src');
        break;
    }

    $result[$index]['description'] = $live->find('.description-text')[0]->innerHtml;
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

function parse($item) {
    $matches = [];
    preg_match('#<a class="title" href=".+v=(?<id>.+?)&pp.+?">(?<title>.+?)</a>#', $item, $matches);
    return $matches;
}



