<?php
namespace KosodeModules\Motif;

class SquareMotifGenerator implements MotifGenerator {
  private $motifContainer;

  public function __construct($motifContainer){
    $this->motifContainer = $motifContainer;
  }

  /**
   * 連結画像を生成する関数
   * @return 画像ID タイルを連結した画像
   */
  public function generateMotifImage() {
    // 最大値・最小値座標取得
    $co_x_transform = $this->motifContainer->hqimage_width / $this->motifContainer->image_width;
    $co_y_transform = $this->motifContainer->hqimage_height / $this->motifContainer->image_height;
    $coordiantes = getMaxMinCoordinates($this->motifContainer->image_motif_coordinates, $co_x_transform, $co_y_transform);

    // zoomlevel（倍率）の決定
    $level = defineZoomLevel($coordiantes);

    // 座標の変換
    $coordiantes = convertCoordinates($coordiantes, $level);

    // タイル座標の決定
    $tileCoordinates = defineTileNumber($coordiantes);

    // タイルを連結する
  	$image = concatenateImages($tileCoordinates);

    // 画像をトリミングする
    $image = trimmingImage($image, $coordiantes, $tileCoordinates);

    $returnImage = imagecreatetruecolor($this->motifContainer->size, $this->motifContainer->size);
    $isCreated = resampleImage($returnImage, $image, $this->motifContainer->size);

    // 出力
    if($debug){
      ob_start();
      imagepng( $returnImage, NULL);
      $img2 = ob_get_clean();
      echo "<img src='data:image/jpeg;base64,".base64_encode( $img2 )."'>";
    } else {
      if(!$isCreated){
        $returnImage  = imagecreatetruecolor(150, 30);
        $bgc = imagecolorallocate($returnImage, 255, 255, 255);
        $tc  = imagecolorallocate($returnImage, 0, 0, 0);

        imagefilledrectangle($returnImage, 0, 0, 150, 30, $bgc);

        /* エラーメッセージを出力します */
        imagestring($returnImage, 1, 5, 5, 'Error loading image.', $tc);
      } else {
        imagepng($returnImage, $imgpath);
      }
      header('Content-Type: image/png');
      imagepng($returnImage);
      imagedestroy($returnImage);
    }

    return $image;
  }

  /**
   * モティーフ領域座標の最左上座標および最右下座標を取得する関数
   * @param  array $image_motif_coordinates [モティーフ座標集合]
   * @param  double $co_x_transform          [x座標変換係数]
   * @param  double $co_y_transform          [y座標変換係数]
   * @return array                          [最大最小座標配列]
   */
  private function getMaxMinCoordinates($image_motif_coordinates, $co_x_transform, $co_y_transform){
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
    $image_motif_coordinates_x = array_map('current', array_chunk($this->motifContainer->image_motif_coordinates, 2));
    // 奇数番目(y座標)を取り出す
    $image_motif_coordinates_y = array_map('current', array_chunk(array_slice($this->motifContainer->image_motif_coordinates, 1), 2));

    // 最大値、最小値の取り出し
    $max_x = max($image_motif_coordinates_x);
    $max_y = max($image_motif_coordinates_y);
    $min_x = min($image_motif_coordinates_x);
    $min_y = min($image_motif_coordinates_y);

    // *debug
    if($GLOBALS['debug']){
      print "max(x,y): ($max_x, $max_y) <br>min(x,y): ($min_x, $min_y)<br>";
    }

    // 座標位置を等倍画像のものへ変換
    $max_x = $max_x * $co_x_transform;
    $max_y = $max_y * $co_y_transform;
    $min_x = $min_x * $co_x_transform;
    $min_y = $min_y * $co_y_transform;

    // *debug
    if($debug){
      var_dump($img_info['items']);
      print "max(x,y): ($max_x, $max_y) <br>min(x,y): ($min_x, $min_y<br>";
      print "width: ".($max_x-$min_x)."<br>height: ".($max_y-$min_y)."<br>";
    }

    return array(
      'max' => array(
        'x' => $max_x,
        'y' => $max_y
      ),
      'min' => array(
        'x' => $min_x,
        'y' => $min_y
      )
    );
  }

  /**
   * zoomlevel(倍率)を決定する関数
   * @param  hash $coordiantes [モティーフ領域の最大・最小座標]
   * @return Integer              [zoomlevel]
   */
  private function defineZoomLevel($coordiantes) {
  	$max_size = max( array( $coordiantes['max']['x'] - $coordiantes['min']['x'] ,
                            $coordiantes['max']['y'] - $coordiantes['min']['y']
                          ));
  	$rev_level = 0;
  	while($max_size > $this->motifContainer->size * 2 && $rev_level < count($this->motifContainer->zoomlevel)){
  	  $max_size /= 2.0;
  	  $rev_level++;
  	}
  	// *debug
    if($GLOBALS['debug']){
  	  print "size: $max_size rev_level: $rev_level<br>";
    }

    return count($this->motifContainer->zoomlevel) - $rev_level - 1;
  }

  /**
   * 画像の縮小に合わせて座標の変換を行う関数
   * @param  hash $coordiantes [モティーフ領域の最大・最小座標]
   * @return hash              [変換後のモティーフ領域の最大・最小座標]]
   */
  private function convertCoordinates($coordiantes, $level) {
    $rev_level = count($this->motifContainer->zoomlevel) - $level - 1;

  	$coordiantes['max']['x'] = $rev_level == 0 ? $coordiantes['max']['x'] : ceil($coordiantes['max']['x'] / pow( 2, $rev_level));
  	$coordiantes['max']['y'] = $rev_level == 0 ? $coordiantes['max']['y'] : ceil($coordiantes['max']['y'] / pow( 2, $rev_level));
  	$coordiantes['min']['x'] = $rev_level == 0 ? $coordiantes['min']['x'] : floor($coordiantes['min']['x'] / pow( 2, $rev_level));
  	$coordiantes['min']['y'] = $rev_level == 0 ? $coordiantes['min']['y'] : floor($coordiantes['min']['y'] / pow( 2, $rev_level));

    // *debug
    if($GLOBALS['debug']){
      print "max(x,y): (" . $coordiantes['max']['x'] . ", " . $coordiantes['max']['y'] . ") <br>min(x,y): (" . $coordiantes['min']['x'] . ", " . $coordiantes['min']['y'] . "<br>";
    }

    return $coordiantes;
  }

  /**
   * フェッチするタイル番号を決める関数
   * @param  hash $coordiantes [モティーフ領域の最大・最小座標]
   * @return hash              [最大・最小座標が含まれるタイル座標]
   */
  private function defineTileNumber($coordiantes) {
    $coordiantes['max']['x'] = ceil($coordiantes['max']['x']/($this->motifContainer->zoomlevel[0]-1));
    $coordiantes['max']['y'] = ceil($coordiantes['max']['y']/($this->motifContainer->zoomlevel[0]-1));
    $coordiantes['min']['x'] = floor($coordiantes['min']['x']/($this->motifContainer->zoomlevel[0]-1));
    $coordiantes['min']['y'] = floor($coordiantes['min']['y']/($this->motifContainer->zoomlevel[0]-1));

  	// *debug
    if($debug){
  	   print "posmax(x,y): (".$coordiantes['max']['x'].", ".$coordiantes['max']['y'].") <br>posmin(x,y): (".$coordiantes['min']['x'].", ".$coordiantes['min']['y'].")<br>";
    }

    return $coordiantes;
  }


  private function concatenateImages($tileCoordinates) {
    // タイルを連結する
    $tileSize = $this->motifContainer->zoomlevel[0];
  	$width = $tileSize * ($tileCoordinates['max']['x'] - $tileCoordinates['min']['x'] + 1);
  	$height = $tileSize * ($tileCoordinates['max']['y'] - $tileCoordinates['min']['x'] + 1);
  	$image = imagecreatetruecolor($width, $height);

  	for($y = $tileCoordinates['min']['y']; $y <= $tileCoordinates['max']['y']; $y++){
  	  for($x = $tileCoordinates['min']['x']; $x <= $tileCoordinates['max']['x']; $x++){
    	  $path = "./images/高精細画像201404/".$this->motifContainer->hqimage_title."/zoomlevel$level/zoomlevel".$level."_x".$x."_y".$y."_".$this->motifContainer->hqimage_title.".png";
        $path = mb_convert_encoding($path ,"Shift_JIS", "UTF-8");
        if(is_readable($path)){
    	    $tileImage = imagecreatefrompng($path);
    	    imagecopy($image, $tileImage, $tileSize * ($x - $tileCoordinates['min']['x']), $tileSize * ($y - $tileCoordinates['min']['y']), 0, 0, $tileSize ,$tileSize);
        }
  	  }
  	}
    if($GLOBALS['debug']){
      ob_start();
      imagepng( $image, NULL);
      $img2 = ob_get_clean();
      echo "<img src='data:image/jpeg;base64,".base64_encode( $img2 )."'>";
    }

    return $image;
  }

  private function trimmingImage($originalImage, $coordiantes, $tileCoordinates) {
    // 連結した画像をトリミングする
    $coordiantes['max']['x'] = $coordiantes['max']['x'] - $tileCoordinates['min']['x'] * ($this->motifContainer->zoomlevel[0]-1);
    $coordiantes['max']['y'] = $coordiantes['max']['y'] - $tileCoordinates['min']['y'] * ($this->motifContainer->zoomlevel[0]-1);
    $coordiantes['min']['x'] = $coordiantes['min']['x'] - $tileCoordinates['min']['x'] * ($this->motifContainer->zoomlevel[0]-1);
    $coordiantes['min']['y'] = $coordiantes['min']['y'] - $tileCoordinates['min']['y'] * ($this->motifContainer->zoomlevel[0]-1);

    $image = imagecreatetruecolor($coordiantes['max']['x']-$coordiantes['min']['x'] + 1, $coordiantes['max']['y']-$coordiantes['min']['y'] + 1);
    imagecopy($image, $originalImage, 0, 0, $coordiantes['min']['x'], $coordiantes['min']['y'], $coordiantes['max']['x']-$coordiantes['min']['x'] + 1, $coordiantes['max']['y']-$coordiantes['min']['y'] + 1);
    imagedestroy($originalImage);

    return $image;
  }

  private function resampleImage($image, $originalImage, $size) {
    $resample = array('width' => $coordiantes['max']['x']-$coordiantes['min']['x'] + 1, 'height' => $coordiantes['max']['y']-$coordiantes['min']['y'] + 1);
    if($resample['width'] >= $resample['height']){
      $maxkey = "width";
    }else{
      $maxkey = "height";
    }

    imagealphablending($image, false);
    imagesavealpha($image, true);
    $fillcolor = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $fillcolor);
    $doneCreate = imagecopyresampled($image, $originalImage, ($size- $resample['width'] * $size / $resample[$maxkey])/2, ($size-$resample['height'] * $size / $resample[$maxkey])/2, 0, 0, $resample['width'] * $size / $resample[$maxkey], $resample['height'] * $size / $resample[$maxkey], $resample['width'], $resample['height']);
    imagedestroy($originalImage);

    return $doneCreate;
  }

}
