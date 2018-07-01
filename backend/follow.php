<?php

$conn = new \mpyw\Cowitter\Client([getenv('CK'), getenv('CS'), getenv('AT'), getenv('ATS')]);

$doNotRemoveIds = [
    90569072 => '教授',
    296765763 => 'IL社',
    978095933358424064 => 'vcas公式',
];
$unFollow = function ($user_id) use ($conn, $doNotRemoveIds) {
    if (array_key_exists($user_id, $doNotRemoveIds)) {
        return;
    }
    try {
        echo "unfollow ${$user_id}\n";
        $conn->post('friendships/destroy', compact('user_id'));
    } catch (Exception $e) {
        echo "ID{$user_id}: {$e->getMessage()}\n";
    }
};

$follow = function ($user_id) use ($conn) {
    try {
        echo "follow ${$user_id}\n";
        $conn->post('friendships/create', compact('user_id'));
    } catch (Exception $e) {
        echo "ID{$user_id}: {$e->getMessage()}\n";
    }
};
$get_all_ids = function ($endpoint) use ($conn) {
    $ids = [];
    $params = [
        'stringify_ids' => '1',
        'cursor'        => '-1',
    ];
    $params['cursor'] = '-1';
    do {
        $result = $conn->get($endpoint, $params);
        $ids += array_flip($result->ids);
    } while ($params['cursor'] = $result->next_cursor_str);
    return $ids;
};
try {
    // フレンドとフォロワーそれぞれを取得
    $friends   = $get_all_ids('friends/ids');
    $followers = $get_all_ids('followers/ids');
    // 差分を取得
    $friends_only   = array_diff_key($friends, $followers);
    $followers_only = array_diff_key($followers, $friends);
    // 1件ずつそれぞれ試行
    foreach ($friends_only as $user_id => $_) {
        $unFollow($user_id);
    };
    foreach ($followers_only as $user_id => $_) {
        $follow($user_id);
    };
} catch (Exception $e) {
    echo "Fatal Error: {$e->getMessage()}\n";
}
