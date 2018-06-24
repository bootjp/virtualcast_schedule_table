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
  .live {
    background-color: aliceblue;
    margin: 10px;
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
  <h1>これから始まるバーチャルキャストの番組表</h1>
  <div class="row align-items-center">
    <?php foreach ($lives as $live) :?>
      <div class="col-xs54 col-md-5 live">
        <p><a href="https://nico.ms/<?php echo $live['live_id'];?>"><?php echo htmlentities($live['title']);?></a></p>
        <p><?php echo $live['start'];?>~</p>
        <img class="thumbnail" src="<?php echo $live['image'];?>" />
        <div><p class="descriptions"><?php echo htmlentities($live['description']);?></p></div>
        <div class="clear"></div>
        <p><?php echo htmlentities($live['owner']);?></p>
      </div>
    <?php endforeach;?>
  </div>
</div>
@bootjp <a href="https://github.com/bootjp/virtualcast_schedule_table">バーチャルキャスト予約番組表</a>
</body>
</html>


