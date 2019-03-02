<?php

require_once __DIR__. '/vendor/autoload.php';

$client = new \GuzzleHttp\Client();

$res = $client->get('https://api.virtualcast.jp/channels/ja/broadcast/list')->getBody()->getContents();

foreach (json_decode($res, true)['list'] as $live) {
    $liveID =  getLiveIDByURL($live['url']);
    $tweeted = liveIDTweeted($liveID);

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
        $post = function ($endpoint) use ($conn, $live, $db, $liveID) {
            $conn->post($endpoint, [
                'status' => "{$live['nickname']} が {$live['title']} というタイトルで放送を始めたよ． {$live['url']}"
            ]);
            $db->insert('notify_bot', [
                'live_id' => $liveID,
                'send' => true,
            ]);
        };
        $post('statuses/update');
        $db->pdo->commit();
    } catch (Exception $e) {
        $db->pdo->rollBack();
        var_export($e);
    }
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

function getDB() : Medoo\Medoo {
    return (new Medoo\Medoo([
        'database_type' => 'mariadb',
        'database_name' => getenv('MYSQL_DATABASE'),
        'server' => getenv('MYSQL_HOST'),
        'username' => getenv('MYSQL_USER'),
        'password' => getenv('MYSQL_PASSWORD'),
        'charset' => 'utf8mb4',
    ]));
}
