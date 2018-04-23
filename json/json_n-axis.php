<?php
//
// ini_set("display_errors", On);
// error_reporting(E_ALL);
// include '../ChromePhp.php';
require __DIR__ . "/../vendor/autoload.php";
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

use KosodeModules\DB\KosodeDB as KosodeDB;

// 検索可能属性
$query_selection = array(
  "屏風番号_index",
  "資料番号",
  "屏風番号",
  "地色",
  "年代",
  "材質",
  "技法",
  "モティーフ",
  "モード",
  // "絞の種類",
  "章",
  "分類",
  "近世きもの万華鏡",
  "解説文_技法",
);

// 結合不可属性
$disabledAxis['disabledAxis'] = array(
  "資料番号",
  "屏風番号"
);

// 数値属性
$numericAxises['numericAxises'] = array(
  "屏風番号_index",
  "年代",
  "近世きもの万華鏡"
);

// 外部結合可能属性
$outerjoinable['outerjoinable']= array(
  "モード",
  "解説文_技法"
);

if(isset($_GET['exclude']) ){
  $exclude = $_GET['exclude'];
  $exclude = array_diff($exclude, array("屏風番号_index"));
  $exclude = array_values($exclude);
  $query_selection = array_diff($query_selection, $exclude);
  $query_selection = array_values($query_selection);
}

if(isset($_GET['outerjoin'])){
  $innerjoin = array_diff($query_selection, $_GET['outerjoin']);
  foreach ($innerjoin as $val) {
    $jointype[$val] = 'innerjoin';
  }
  foreach ($_GET['outerjoin'] as $val){
    $jointype[$val] = 'outerjoin';
  }
} else {
  foreach($query_selection as $val) {
    $jointype[$val] = 'innerjoin';
  }
}

if(isset($_GET['param']) ){
  $param = $_GET['param'];
}


$msg = "SELECT *
        FROM (
          SELECT DISTINCT
        ";

foreach($query_selection as $q){
  $msg .= $q . ",";
}
$msg = mb_substr($msg, 0, -1);

if(isset($param) && $param == 'kissyo'){
  $msg = "
    SELECT CLng(Mid(小袖番号, InStrRev(小袖番号, '-') + 1)) as index, 小袖番号 as 屏風番号, 特徴語, 属性
    FROM きっしょう
    ORDER BY CLng(Mid(小袖番号, InStrRev(小袖番号, '-') + 1))
  ";
}
else {
$msg .=" FROM (
                      SELECT
                            DISTINCT CLng(Mid(館蔵コレ_小袖屏風.屏風番号, InStrRev(館蔵コレ_小袖屏風.屏風番号, '-') + 1)) as 屏風番号_index,
                              館蔵コレ_小袖屏風.屏風番号,
                              館蔵コレ_小袖屏風.資料番号,";

if (in_array("分類", $query_selection)) {
$msg .=                      "きもの文様図鑑_分類クエリ.分類,";
}
if (in_array("モード", $query_selection)) {
$msg .=                      "解題_抽出_モード.モード as モード,";
}
// if (in_array("絞の種類", $query_selection)) {
// $msg .=                      "特徴語_技法_nagai.特徴語 as 絞の種類,";
// }
if (in_array("解説文_技法", $query_selection)) {
$msg .=                      "身分単語たち_技.単語 as 解説文_技法,";
}
if (in_array("解説文_形式", $query_selection)) {
$msg .=                      "身分単語たち_形.単語 as 解説文_形式,";
}
if (in_array("モティーフ", $query_selection)) {
$msg .=                      "館蔵コレ_モティーフ.モティーフ as モティーフ,";
}
$msg .="
                              近世きもの万華鏡.章,
                              館蔵コレ_小袖.材質,
                              館蔵コレ_小袖.地色,
                              館蔵コレ_技法.技法,
                              年表.年代,
                              近世きもの万華鏡.開始ページ as 近世きもの万華鏡
                          FROM
                              小袖,
                              近世きもの万華鏡,
                              館蔵コレ_小袖屏風,
                              館蔵コレ_モティーフ,";

if (in_array("分類", $query_selection)) {
$msg .=                      "きもの文様図鑑_分類クエリ,";
}
// if (in_array("絞の種類", $query_selection)) {
// $msg .=                      "特徴語_技法_nagai,
//                               近世_特徴語_nagai,";
// }
if (in_array("モード", $query_selection)) {
  if($jointype["モード"] == "innerjoin") {
    $msg .=                 "解題_抽出_モード,";
  } else {
    $msg .=                 "(
                             SELECT 小袖.解題_小袖番号 as 資料番号, 解題_抽出_モード.モード
                             FROM 小袖
                             LEFT OUTER JOIN 解題_抽出_モード ON 解題_抽出_モード.資料番号 = 小袖.解題_小袖番号
                             ) as 解題_抽出_モード,";
  }
}
if (in_array("解説文_技法", $query_selection)) {
  if($jointype["解説文_技法"] == "innerjoin") {
    $msg .=                 "(
                              select distinct 身分単語_nagai.小袖番号, 身分単語_nagai.単語
                              from 身分単語_nagai, 抽出単語_nagai
                              where 身分単語_nagai.単語 = 抽出単語_nagai.抽出単語
                              and 抽出単語_nagai.特徴 = '技法'
                              ) as 身分単語たち_技,";
  } else {
    $msg .=                 "(
                              SELECT 小袖.小袖番号, 身分.単語
                              FROM 小袖
                              LEFT OUTER JOIN (select distinct 身分単語_nagai.小袖番号, 身分単語_nagai.単語
                                                            from 身分単語_nagai, 抽出単語_nagai
                                                            where 身分単語_nagai.単語 = 抽出単語_nagai.抽出単語
                                                            and 抽出単語_nagai.特徴 = '技法'
                              				) as 身分 ON 身分.小袖番号 = 小袖.小袖番号
                              ) as 身分単語たち_技,
                            ";
  }
}
if (in_array("解説文_形式", $query_selection)) {
$msg .=                      "(
                              select distinct 身分単語_nagai.小袖番号, 身分単語_nagai.単語
                              from 身分単語_nagai, 抽出単語_nagai
                              where 身分単語_nagai.単語 = 抽出単語_nagai.抽出単語
                              and 抽出単語_nagai.特徴 = '形式'
                              ) as 身分単語たち_形,";
}
$msg .="                      館蔵コレ_技法,
                              館蔵コレ_小袖,
                              (
                                  SELECT
                                      ベース.*,
                                      (ベース.開始年 + ベース.終了年) / 2 AS 年代
                                  FROM
                                      (
                                          SELECT
                                              館蔵コレ_小袖.資料番号,
                                              MAX(時代.開始年) AS 開始年,
                                              MIN(時代.終了年) AS 終了年
                                          FROM
                                              館蔵コレ_小袖,
                                              時代
                                          WHERE
                                              (
                                                  館蔵コレ_小袖.[時代(和暦) ] IN(時代.時代, 時代.[館蔵コレ_時代(和暦) ])
                                              OR  館蔵コレ_小袖.[時代(西暦) ] IN(時代.時代, 時代.[館蔵コレ_時代(西暦) ])
                                              )
                                          GROUP BY
                                              館蔵コレ_小袖.資料番号
                                      ) AS ベース
                              ) AS 年表

                          WHERE
                              小袖.館蔵コレ_小袖番号 = 館蔵コレ_小袖屏風.資料番号
                          AND 近世きもの万華鏡.図版番号 = 小袖.図版番号
                          AND 館蔵コレ_モティーフ.資料番号 = 館蔵コレ_小袖屏風.資料番号
                          AND 館蔵コレ_技法.資料番号 = 館蔵コレ_小袖屏風.資料番号
                          AND 館蔵コレ_小袖.資料番号 = 館蔵コレ_小袖屏風.資料番号
                          AND 年表.資料番号 = 館蔵コレ_小袖屏風.資料番号";

if (in_array("分類", $query_selection)) {
$msg .=                  " AND きもの文様図鑑_分類クエリ.資料番号 = 館蔵コレ_小袖屏風.資料番号";
}
// if (in_array("絞の種類", $query_selection)) {
// $msg .=                  " AND 近世_特徴語_nagai.小袖番号 = 館蔵コレ_小袖屏風.資料番号
//                            AND 特徴語_技法_nagai.特徴語 = 近世_特徴語_nagai.特徴語
//                            AND 特徴語_技法_nagai.技法 = '絞'";
// }
if (in_array("モード", $query_selection)) {
$msg .=                  " AND 解題_抽出_モード.資料番号 = 小袖.解題_小袖番号";
}
if (in_array("解説文_技法", $query_selection)) {
$msg .=                  " AND 身分単語たち_技.小袖番号 = 館蔵コレ_小袖屏風.資料番号";
}
if (in_array("解説文_形式", $query_selection)) {
$msg .=                  " AND 身分単語たち_形.小袖番号 = 館蔵コレ_小袖屏風.資料番号";
}
if (in_array("モティーフ", $query_selection)) {
$msg .=                  " AND 館蔵コレ_モティーフ.資料番号 = 館蔵コレ_小袖屏風.資料番号";
}
$msg .="
          )
      )

ORDER BY
  屏風番号_index, 資料番号
";

}

$db = new KosodeDB();
$assoc_data = $db->fetchArray($msg, 'items');
$db->closeConnection();

$assoc_data = array_merge($assoc_data, $outerjoinable);
$assoc_data = array_merge($assoc_data, $disabledAxis);
$assoc_data = array_merge($assoc_data, $numericAxises);

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($assoc_data, JSON_UNESCAPED_UNICODE);
?>
