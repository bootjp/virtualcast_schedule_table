# virtualcast-schedule_table

* コントリビュート歓迎
* 不明点はissuesから

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
 * backend/fetch.phpを15分おきに実行してください．
 * 現段階では負荷対策は一切行っておらず，また予約番組数が増えた際の対応も行っておりません．
 * 現段階においては「バーチャルキャスト」タグが付与された予約番組のみを対象としています．
 
## 今後の予定（未定） 
 * リファクタリングを行う
 * 枠確保後に削除された枠を非表示にする  
   - 定期的に枠のレスポンスコードを監視する
 * 今放送中の予約枠も表示する(今は放送時間になると消える)
   - 枠のレスポンスコードを見ることでできる?
 * 負荷対策を行う
 * 予約番組数が増えても問題なくする
 * 複数のタグに対応させる
 * 現在配信中の番組も網羅的に表示可能にする（バーチャルキャストの公式ページをスクレイピング）
