<?php

class DividedImageCatenator{
  private $img_info;
	function __construct($img_info){
    $this->img_info = $img_info;
  }
  // debug用
  if($debug){
  //	var_dump($this->img_info['items']);
  	var_dump($hqimage_title);
  	var_dump($motif);
  	var_dump($id);
  	var_dump($size);
  }

  // 変数宣言
	$image_title = $this->img_info['items'][$id]['縮小画像ファイル名'];
	$hqimage_width = $this->img_info['items'][$id]['高精細横幅'];
	$hqimage_height = $this->img_info['items'][$id]['高精細縦幅'];
	$image_width = $this->img_info['items'][$id]['縮小横幅'];;
	$image_height = $this->img_info['items'][$id]['縮小縦幅'];
	$image_motif_coordinates = explode(',', $this->img_info['items'][$id]['頂点リスト']);
	$zoomlevel = array(256,512,1024,2048,4096,8192,16384,32768);

  // 最大、最小座標値の取得
  $buff = getMaxMinCoordinates($image_motif_coordinates, $hqimage_width / $image_width, $hqimage_height / $image_height);
	$max_x = $buff[0];
	$max_y = $buff[1];
	$min_x = $buff[2];
	$min_y = $buff[3];

	// *debug
	if($debug){
	  print "max(x,y): ($max_x, $max_y) <br>min(x,y): ($min_x, $min_y<br>";
    print "width: ".($max_x-$min_x)."<br>height: ".($max_y-$min_y)."<br>";
  }

	// zoomlevelの決定
	$max_size = max( array( $max_x-$min_x, $max_y-$min_y ) );
	$rev_level = 0;
	while($max_size > $size*2 && $rev_level < count($zoomlevel)){
	  $max_size /= 2.0;
	  $rev_level++;
	}
	// *debug
  if($debug){
	  print "size: $max_size level: $rev_level<br>";
  }
	// zoomlevelに合わせた座標へ変換
	$cal_max_x = $rev_level == 0 ? $max_x : ceil($max_x / pow( 2, $rev_level));
	$cal_max_y = $rev_level == 0 ? $max_y : ceil($max_y / pow( 2, $rev_level));
	$cal_min_x = $rev_level == 0 ? $min_x : floor($min_x / pow( 2, $rev_level));
	$cal_min_y = $rev_level == 0 ? $min_y : floor($min_y / pow( 2, $rev_level));

	// *debug
	if($debug){
    print "max(x,y): ($cal_max_x, $cal_max_y) <br>min(x,y): ($cal_min_x, $cal_min_y)<br>";
  }
	//$i = 0;

	// フェッチするタイル番号を決める
  $tile_no_max_x = ceil($cal_max_x/($zoomlevel[0]-1));
  $tile_no_max_y = ceil($cal_max_y/($zoomlevel[0]-1));
  $tile_no_min_x = floor($cal_min_x/($zoomlevel[0]-1));
  $tile_no_min_y = floor($cal_min_y/($zoomlevel[0]-1));

	// *debug
  if($debug){
	   print "posmax(x,y): ($tile_no_max_x, $tile_no_max_y) <br>posmin(x,y): ($tile_no_min_x, $tile_no_min_y)<br>";
  }

	// フェッチしたタイルを連結する
	$width = $zoomlevel[0] * ($tile_no_max_x - $tile_no_min_x + 1);
	$height = $zoomlevel[0] * ($tile_no_max_y - $tile_no_min_y + 1);
	$create_image = imagecreatetruecolor($width, $height);
	$level = count($zoomlevel)-$rev_level-1;
	for($y = $tile_no_min_y; $y <= $tile_no_max_y; $y++){
	  for($x = $tile_no_min_x; $x <= $tile_no_max_x; $x++){
	  $path = "./images/高精細画像201404/$hqimage_title/zoomlevel$level/zoomlevel".$level."_x".$x."_y".$y."_$hqimage_title.png";
	  $path = mb_convert_encoding($path ,"Shift_JIS", "UTF-8");
	    $get_image = imagecreatefrompng($path);

	    imagecopy($create_image, $get_image, $zoomlevel[0] * ($x - $tile_no_min_x), $zoomlevel[0] * ($y - $tile_no_min_y), 0, 0, $zoomlevel[0] ,$zoomlevel[0]);
	  }
	}

	// 連結した画像をトリミングする
	$trimmed_max_x = $cal_max_x - $tile_no_min_x * ($zoomlevel[0]-1);
	$trimmed_max_y = $cal_max_y - $tile_no_min_y * ($zoomlevel[0]-1);
	$trimmed_min_x = $cal_min_x - $tile_no_min_x * ($zoomlevel[0]-1);
	$trimmed_min_y = $cal_min_y - $tile_no_min_y * ($zoomlevel[0]-1);
	$trimmed_image = imagecreatetruecolor($trimmed_max_x-$trimmed_min_x + 1, $trimmed_max_y-$trimmed_min_y + 1);
	imagecopy($trimmed_image, $create_image, 0, 0, $trimmed_min_x, $trimmed_min_y, $trimmed_max_x-$trimmed_min_x + 1, $trimmed_max_y-$trimmed_min_y + 1);
	imagedestroy($create_image);


	$resample = array('width' => $trimmed_max_x - $trimmed_min_x + 1, 'height' => $trimmed_max_y - $trimmed_min_y + 1);
	if($resample['width'] >= $resample['height']){
	  $maxkey = "width";
	}else{
	  $maxkey = "height";
	}

	$resampled_image = imagecreatetruecolor($size, $size);
  imagealphablending($resampled_image, false);
  imagesavealpha($resampled_image, true);
  $fillcolor = imagecolorallocatealpha($resampled_image, 0, 0, 0, 127);
  imagefill($resampled_image, 0, 0, $fillcolor);
	imagecopyresampled($resampled_image, $trimmed_image, ($size- $resample['width'] * $size / $resample[$maxkey])/2, ($size-$resample['height'] * $size / $resample[$maxkey])/2, 0, 0, $resample['width'] * $size / $resample[$maxkey], $resample['height'] * $size / $resample[$maxkey], $resample['width'], $resample['height']);
	imagedestroy($trimmed_image);

	// 出力
  if($debug){
    ob_start();
    imagepng( $resampled_image, NULL);
    $img2 = ob_get_clean();
    echo "<img src='data:image/jpeg;base64,".base64_encode( $img2 )."'>";
  }else{
    imagepng($resampled_image, $imgpath);
    header('Content-Type: image/png');
    imagepng($resampled_image);
    imagedestroy($resampled_image);
  }


function calcDiff( $time1 ,$time2 ){
	$time1_a = date('Y-m-d H:i:s', $time1);
	return intval(strtotime($time2)-strtotime($time1_a));

}

/**
 * モティーフ領域座標の最右下座標or最左上座標を取得する関数
 * @param  array $image_motif_coordinates [モティーフ座標集合]
 * @param  double $co_x_transform          [x座標変換係数]
 * @param  double $co_y_transform          [y座標変換係数]
 * @return array                          [座標配列]
 */
function fetchCoordinate($image_motif_coordinates, $co_x_transform, $co_y_transform){
  /**
   * $image_motif_coordinates=[
   * 														x座標,y座標,
   * 														x座標,y座標,
   * 														x座標,y座標,
   * 														.....
   * 														]
   * 	したがって、配列の偶数番目はx座標、奇数番目はy座標となる
   */
	// 偶数番目(x座標)を取り出す
	$image_motif_coordinates_x = array_map('current', array_chunk($image_motif_coordinates, 2));
	// 奇数番目(y座標)を取り出す
	$image_motif_coordinates_y = array_map('current', array_chunk(array_slice($image_motif_coordinates, 1), 2));

	// 最大値、最小値の取り出し
	$max_x = max($image_motif_coordinates_x);
	$max_y = max($image_motif_coordinates_y);

	// *debug
	if($GLOBALS['debug']){
	  print "max(x,y): ($max_x, $max_y) <br>min(x,y): ($min_x, $min_y)<br>";
  }

	// 座標位置を等倍画像のものへ変換
	$max_x = $max_x * $co_x_transform;
	$max_y = $max_y * $co_y_transform;

  return array('x': $x, 'y': $y);
}
}
?>
