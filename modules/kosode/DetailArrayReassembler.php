<?php

namespace KosodeModules;

/**
 * 詳細表示画面用配列の再構築
 */
class DetailArrayReassembler implements ReassembleArray{
  /**
   * [getReassembledArray description]
   * @param  array $array[][][] 再構築対象配列
   * @param  array $key[][]   再構築するため際のキーとなる配列およびそれに関連するキーの配列
   * @return array        再構築した配列
   */
  public function getReassembledArray($array, $key){
    $result[$key[0][2]] = $array[$key[0][2]];
    foreach($key as $k){
      for($i = 0; $i < count($array[$k[0]]); $i++){
        // buff[motif/material][資料番号]に対してモティーフ/技法の配列を格納する
        $material_no = $array[$k[0]][$i]['資料番号'];
        $buff[$k[0]][$material_no][] = $array[$k[0]][$i][$k[1]];
      }
    }

    for($i = 0; $i < count($array[$key[0][2]]); $i++){
      foreach($key as $k){
        // 先に作ったbuff配列を、$result['items']の適切な位置に格納する
        $material_no = $result[$key[0][2]][$i]['資料番号'];
			  $result[$key[0][2]][$i][$k[1]] = $buff[$k[0]][$material_no];
      }
		}
    return $result;
  }
}

?>
