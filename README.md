# virtualcast-schedule_table

* コントリビュート歓迎
* 不明点はissuesから

## how to develop 

```bash 
$ git clone https://github.com/bootjp/virtualcast_schedule_table.git
$ cd virtualcast_schedule_table
$ curl -s http://getcomposer.org/installer | php
$ cd app
$ php composer.phar install
or
$ composer install
$ docker-compose up 
```

batches 
```bash
docker run bootjp/virtualcast_schedule_table php /app/backend/fetch.php
```

* DB構成は ./docker_init/create_table.sqlにあります．  
* アプリケーション構成は Dockerfile及びdocker-compose.ymlを確認ください
* 使用している docker image は[こちら](https://hub.docker.com/r/bootjp/virtualcast_schedule_table)です． 

## 環境について
 * backend/fetch.phpを15分おきに実行してください．
 * backend/check.phpを毎事10分に実行してください．
 * 現段階では負荷対策は一切行っておらず，また予約番組数が増えた際の対応も行っておりません．
 * 現段階においては「バーチャルキャスト」タグが付与された予約番組のみを対象としています．
 
## 今後の予定（未定） 
 * CIでのテストを導入する
 * リファクタリングを行う
 * 負荷対策を行う
 * デザインなんとかする
