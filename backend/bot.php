<?php

require_once __DIR__. '/../vendor/autoload.php';

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



$baseUri = 'http://live.nicovideo.jp/search';
$offSet = 0;
$res = $client->get($baseUri, [
    'query' => [
        'track' => '',
        'sort' => 'recent_r',
        'date' => '',
        'keyword' => 'バーチャルキャスト',
        'filter' => ':hidecomonly:',
        'kind' => 'tags',
    ]
])->getBody()->getContents();

if (mb_strpos($res, '<strong>バーチャルキャスト</strong> を含む 放送中の番組はございません') !== false) {
    echo 'live is does not exits' . "\n";
    exit;
}


$dom = new PHPHtmlParser\Dom();
$dom->load($res);

/* @var $html PHPHtmlParser\Dom() */
$html = $dom->find('.result-list')[0];

$reserved = $html->find('.result-item');
/* @var $live PHPHtmlParser\Dom() */

$result = [];

foreach ($reserved as $index => $live) {
    $data = parse($live->innerHtml);
    $result[$index]['title'] = $data['title'];
    $result[$index]['live_id'] = $data['id'];

    $str = $live->find('.elapsed-time')[0]->innerHtml;
    $time = trim(strip_tags($str));

    $provider = $live->find('.provider-label')[0]->getAttribute('data-provider-type');
    switch ($provider) {
    case 'official':
        $style = $live->find('.official-thumbnail')[0]->getAttribute('style'). "\n";;;
        $result[$index]['owner'] = '公式';
        break;
    case 'channel':
        $result[$index]['owner'] = 'チャンネル';
        break;

    case 'user':
        $result[$index]['owner'] = html_entity_decode($live->find('.provider-name')[0]->innerHtml);
        break;
    }

    $result[$index]['description'] = html_entity_decode($live->find('.description-text')[0]->innerHtml);
}

foreach ($result as $live) {
    $db = new Medoo\Medoo([
        'database_type' => 'mariadb',
        'database_name' => getenv('MYSQL_DATABASE'),
        'server' => getenv('MYSQL_HOST'),
        'username' => getenv('MYSQL_USER'),
        'password' => getenv('MYSQL_PASSWORD'),
        'charset' => 'utf8mb4',
    ]);

    $tweeted = $db->query('SELECT * FROM notify_bot WHERE live_id = :live_id AND send = true ',[
        ':live_id' =>  $live['live_id']
    ])->rowCount() > 0;

    if ($tweeted)  {
        continue;
    }

    try {
        $db->pdo->beginTransaction();
        $conn = new \mpyw\Cowitter\Client([getenv('CK'), getenv('CS'), getenv('AT'), getenv('ATS')]);
        $post = function ($endpoint) use ($conn, $live, $db) {
            $conn->post($endpoint, ['status' => "{$live['owner']}で{$live['title']}が始まったよ．https://nico.ms/{$live['live_id']}"]);
            $db->insert('notify_bot', [
                'live_id' => $live['live_id'],
                'send' => true,
            ]);
        };
        $db->pdo->commit();
    } catch (Exception $e) {
        $db->pdo->rollBack();
    }

    $post('statuses/update');
}

function parse($item) {
    $matches = [];
    preg_match('#<a class="title" href=".+v=(?<id>.+?)&pp.+?">(?<title>.+?)</a>#', $item, $matches);
    return $matches;
}



