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

$res = $client->get('http://live.nicovideo.jp/search', [
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
    $result[$index]['title'] = html_entity_decode($data['title']);
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


        $idMatch = [];
        $provider = html_entity_decode($live->find('.provider-area')[0]->innerHtml);
        preg_match('/src=".+?(?<com_id>co[0-9]+).+?"/m', $provider, $idMatch);
        if ($idMatch['com_id'] == 'co1918179') {
            // SPAM TAG LOCK IGNORE.
            unset ($result[$index]);
            continue 2;
        }
        $rowOwner = html_entity_decode($live->find('.provider-name')[0]->innerHtml);
        $matches = [];
        preg_match('#(?<com_name>.+?) \((?<user_name>.+?)\)#', $rowOwner, $matches);
        $result[$index]['owner'] = ($matches['user_name'] . ' さん');
        break;
    }

    $result[$index]['description'] = html_entity_decode($live->find('.description-text')[0]->innerHtml);
}

foreach ($result as $live) {
    $tweeted = liveIDTweeted($live['live_id']);

    if ($tweeted)  {
        continue;
    }

    try {
        $db = getDB();
        $db->pdo->beginTransaction();
        $conn = new \mpyw\Cowitter\Client([
            getenv('CK'),
            getenv('CS'),
            getenv('AT'),
            getenv('ATS')
        ]);
        $post = function ($endpoint) use ($conn, $live, $db) {
            $conn->post($endpoint, [
                'status' => "{$live['owner']} が {$live['title']} というタイトルで放送を始めたよ． https://nico.ms/{$live['live_id']}"
            ]);
            $db->insert('notify_bot', [
                'live_id' => $live['live_id'],
                'send' => true,
            ]);
        };
        $post('statuses/update');
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

/**
 * @param $url
 * @return string
 */
function getLiveIDByURL(string $url) : string {
    return mb_substr($url, mb_strpos($url, 'lv'));
}

/**
 * @param string $liveID
 * @return bool
 */
function liveIDTweeted(string $liveID) : bool {
    $db = getDB();

    $count = $db->query('SELECT * FROM notify_bot WHERE live_id = :live_id AND send = true ', [
        ':live_id' =>  $liveID
    ])->rowCount();

    return $count > 0;
}

function getDB() :Medoo\Medoo {
    return (new Medoo\Medoo([
        'database_type' => 'mariadb',
        'database_name' => getenv('MYSQL_DATABASE'),
        'server' => getenv('MYSQL_HOST'),
        'username' => getenv('MYSQL_USER'),
        'password' => getenv('MYSQL_PASSWORD'),
        'charset' => 'utf8mb4',
    ]));
}
