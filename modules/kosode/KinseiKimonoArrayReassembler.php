<?php

namespace KosodeModules;

class KinseiKimonoArrayReassembler implements ReassembleArray{
  /**
   * [getReassembledArray description]
   * @param  array $array[][][] 再構築対象配列
   * @param  array $key[][]   再構築するため際のキーとなる配列
   * @return array        再構築した配列
   */
  public function getReassembledArray($array, $key){
    $result = array();
    $str = '';
    foreach($key as $k){
      if($k[0] == 'items'){
        for ($i = 0; $i < count($array[$k[0]]); $i++) {
          $chapter_head = $array[$k[0]][$i][$k[1]];
          $result[$k[0]][$chapter_head][] = $array[$k[0]][$i];
        }
      }else{
        for ($i = 0; $i < count($array[$k[0]]); $i++) {
          $byobu_no = $array[$k[0]][$i][$k[1]];
          $result[$k[0]][$byobu_no][] = $array[$k[0]][$i];
        }
      }
    }
    return $result;
  }
}
?>
