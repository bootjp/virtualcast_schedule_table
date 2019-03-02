<?php

use Medoo\Medoo;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response, array $args) {
    /** @var $renderer \Slim\Views\PhpRenderer $renderer */
    $renderer = $this->get('renderer');
    /** @var $db Medoo $renderer */
    $db = $this->get('db');

    // todo queryを隠蔽する
    $reservedLive = $db->query('SELECT * FROM live WHERE start > NOW() ORDER BY start ASC')->fetchAll();
    // todo 完全に推測なのできちんとスクレイピングして生きている枠か判定する
    $onAirLive = $db->query('SELECT * FROM live WHERE start < NOW() AND start > DATE_SUB(NOW(),INTERVAL 30 MINUTE) ORDER BY start ASC')->fetchAll();

    return $renderer->render($response, 'index.phtml', [
        'current' => $onAirLive,
        'reserved' => $reservedLive,
    ]);
});

$app->get('/__health__', function (Request $request, Response $response, array $args) {
    /** @var $db Medoo $renderer */
    $db = $this->get('db');
    try {
        $db->exec('SELECT TRUE')->fetch();
        return $response->write('OK');
    } catch (Exception $e) {
        return $response->write('NG')->withStatus(500);
    }
});
