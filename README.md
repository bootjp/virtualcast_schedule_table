# virtualcast_-schedule_table

## how to develop 

```bash 
$ git clone https://github.com/bootjp/virtualcast_schedule_table.git
$ cd virtualcast_schedule_table
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
or
$ composer install
$ docker-compose up 
```

## 環境について
 * backenc/fetch.phpを15分おきに実行してください．
 * 現段階では負荷対策は一切行っておらず，また予約番組数が増えた際の対応も行っておりません．
 * 現段階においては「バーチャルキャスト」タグが付与された予約番組のみを対象としています．
 
## 今後の予定（未定） 
 * リファクタリングを行う
 * 負荷対策を行う
 * 予約番組数が増えても問題なくする
 * 複数のタグに対応させる
 * 現在配信中の番組も網羅的に表示可能にする（バーチャルキャストの公式ページをスクレイピング）
