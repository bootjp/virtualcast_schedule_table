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
        'sort' => 'recent',
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

//var_dump($reserved);exit;

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
        $matches = [];
        $style = $live->find('.screenshot-thumbnail')[0]->getAttribute('style');
        preg_match('|background-image:url\((?<bg_url>.+?)\)|', $style, $matches);
        $result[$index]['image'] =  $matches['bg_url'];
        break;
    }

    $result[$index]['description'] = $live->find('.description-text')[0]->innerHtml;

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
    if ($db->query('SELECT * FROM notify_bot WHERE live_id = :live_id AND send = true ', [':live_id' =>  $live['live_id']])->fetch())  {
        echo 'broken';
        continue;
    }

    $conn = null;
    function ($endpoint) use ($conn, $live, $db) {
        $conn->post($endpoint, ['status' => "{$live['owner']}で{$live['title']}が始まったよ．https://nico.ms/{$live['live_id']}"]);
        $db->insert('notify_bot', [
            'live_id' => $live['live_id'],
            'send' => true,
        ]);
    };
}

function parse($item) {
    $matches = [];
    preg_match('#<a class="title" href=".+v=(?<id>.+?)&pp.+?">(?<title>.+?)</a>#', $item, $matches);
    return $matches;
}



