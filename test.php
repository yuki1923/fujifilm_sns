<?php
// require('function.php');
// if(!empty($_GET)){
//     debug('入ってる：'.print_r($_GET,true));
// }else{
//     debug('入ってない：'.print_r($_GET,true));
// }


// $currentPage = (isset($_GET))? $_GET['page'] : 1;
// debug('カレント'.print_r($currentPage,true));

// $test = 'あいうえお';
// echo mb_substr($test,0,-3,"UTF-8");



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css" integrity="sha384-vp86vTRFVJgpjF9jiIGPEEqYqlDwgyBgEF109VFjmqGmIY/Y4HV4d3Gp2irVfcrp" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">
  <title>Document</title>
</head>

<body>
  <i class="fas fa-heart"><span class="heart-text">いいね</span></i>


  <!-- <h1><?php echo '現在は' . $currentPage . 'ページです。'; ?></h1> -->
  <ul>
    <?php
    // $totalPage = 5;
    // if($currentPage == 1){
    //     $minPage = $currentPage;
    //     $maxPage = $currentPage+4;
    // }elseif($currentPage == 2){
    //     $minPage = $currentPage-1;
    //     $maxPage = $currentPage+3;
    // }elseif($currentPage ==3){
    //     $minPage = $currentPage-2;
    //     $maxPage = $currentPage+2;
    // }elseif($currentPage ==4){
    //     $minPage = $currentPage-3;
    //     $maxPage = $currentPage+1;
    // }elseif($currentPage == 5){
    //     $minPage = $currentPage-4;
    //     $maxPage = $currentPage;
    // }
    ?>
    <!-- <?php for ($i = $minPage; $i <= $maxPage; $i++) : ?>
   <li class="<?php if ($currentPage == $i) {
                echo 'active';
              } ?>"><a href="?page=<?php echo $i; ?>"><?php echo $i ?></a></li>
<?php endfor; ?> -->
  </ul>

</body>

</html>
