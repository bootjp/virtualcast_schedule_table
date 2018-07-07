<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="format-detection" content="telephone=no">
  <meta name="viewport" content="width=device-width">
  <title>バーチャルキャスト番組表</title>
</head>
<style>
  img {
    background-color: aliceblue;
    width: 100px; height: 100px;
    object-fit: contain;

  }
  .day {
    position: relative;
    width: 100%;
    margin-left: 20px;
  }
  .live {
    background-color: aliceblue;
    margin: 10px;
    word-break: break-all;
  }
  .thumbnail {
    float: left;
  }
  .clear {
    clear: both;
  }
  .descriptions {
    padding-left: 10px;
    font-size: 90%;
  }
  .col-xs54.col-md-5.live {
    width: 400px;
    min-height: 250px;
  }
</style>
<body>

<div class="container-fluid">
  <p>今の所「バーチャルキャスト」のタグがついているニコ生のものだけを対象としています．</p>
  <p><a href="https://twitter.com/notify_vcas">新着番組通知TwitterBot</a></p>
  <?php if (count($current) > 0) :?>
  <h2>今やっているもの</h2>
  <p>予約時刻から30分以内のもの</p>
  <div class="row align-items-center">
      <?php foreach ($current as $live) :?>
        <div class="col-xs54 col-md-5 live">
          <p><a href="https://nico.ms/<?php echo $live['live_id'];?>"><?php echo htmlentities($live['title']);?></a></p>
          <p><?php echo DateTime::createFromFormat('Y-m-d H:i:s', $live['start'])->format('Y年m月d日 H時i分');?>〜</p>
          <img class="thumbnail" src="<?php echo $live['image'];?>" />
          <div><p class="descriptions"><?php echo htmlentities($live['description']);?></p></div>
          <div class="clear"></div>
          <p><?php echo htmlentities($live['owner']);?></p>
        </div>
      <?php endforeach;?>
  </div>
  <?php endif;?>
  <h2>これから始まるバーチャルキャストの番組表</h2>
  <p>今後予定されている放送枠を直近順で表示中</p>
  <div class="row align-items-center">
  <?php
  $lastDate = null;
  $printDate = [];
  $week = [ "日", "月", "火", "水", "木", "金", "土"];
  foreach ($reserved as $live) :
      $currentDate = DateTime::createFromFormat('Y-m-d H:i:s', $live['start']);
      $day = $currentDate->format('m月d日');
      if (is_null($lastDate) || !array_key_exists($day, $printDate)):?>
        <p class="day"><?php echo $day;?>&nbsp;(<?php echo $week[$currentDate->format('w')]?>)</p>
      <?php
      $printDate[$day] = null;
      endif;
      if (!is_null($lastDate) && $lastDate->format('j') !== $currentDate->format('j')): ?>
    </div>
    <div class="row align-items-center">
    <?php endif;?>
      <div class="col-xs54 col-md-5 live">
        <p><a href="https://nico.ms/<?php echo $live['live_id'];?>"><?php echo htmlentities($live['title']);?></a></p>
        <p><?php echo $currentDate->format('H時i分');?>〜</p>
        <img class="thumbnail" src="<?php echo $live['image'];?>" />
        <div><p class="descriptions"><?php echo htmlentities($live['description']);?></p></div>
        <div class="clear"></div>
        <p><?php echo htmlentities($live['owner']);?></p>
      </div>
    <?php $lastDate = $currentDate;?>
    <?php endforeach;?>
  </div>

  <div class="row align-items-center" style="margin: 50px">
    <a href="https://twitter.com/bootjp">@bootjp</a>&nbsp;<a href="https://github.com/bootjp/virtualcast_schedule_table">バーチャルキャスト予約番組表</a>
  </div>
</div>
</body>
</html>


