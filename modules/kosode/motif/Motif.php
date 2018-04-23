<?php
namespace KosodeModules\Motif;

class Motif {
  public $image_title;
	public $hqimage_width;
	public $hqimage_height;
	public $image_width;
	public $image_height;
	public $image_motif_coordinates;
	public $zoomlevel;
  public $size;

  function __construct($param, $img_info) {
    $this->image_title = $img_info['items'][$param->id]['縮小画像ファイル名'];
    $this->hqimage_title = $img_info['items'][$param->id]['高精細画像ファイル名'];
  	$this->hqimage_width = $img_info['items'][$param->id]['高精細横幅'];
  	$this->hqimage_height = $img_info['items'][$param->id]['高精細縦幅'];
  	$this->image_width = $img_info['items'][$param->id]['縮小横幅'];;
  	$this->image_height = $img_info['items'][$param->id]['縮小縦幅'];
  	$this->image_motif_coordinates = explode(',', $img_info['items'][$param->id]['頂点リスト']);
  	$this->zoomlevel = array(256,512,1024,2048,4096,8192,16384,32768);
    $this->size = $param->size;
  }
}
