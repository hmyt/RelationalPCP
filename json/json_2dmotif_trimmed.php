<?php

// ini_set("display_errors", On);
// error_reporting(E_ALL);
// include '../ChromePhp.php';
require __DIR__ . "/../vendor/autoload.php";
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

use KosodeModules\DB\KosodeDB as KosodeDB;
use KosodeModules\ArrayReassembler as ArrayReassembler;
use KosodeModules\ReassembleArray as ReassembleArray;
use KosodeModules\KinseiKimonoArrayReassembler as KinseiKimonoArrayReassembler;

$json = file_get_contents('param_list.json');
$title_list = json_decode($json, true);

	if(!isset($_GET['row']) || !isset($_GET['col'])){
		die("パラメータ'row'および'col'が指定されていません.");
	}else{
		$row = $_GET['row'];
		$col = $_GET['col'];
	}

	$sql = "SELECT 館蔵コレ_モティーフ.資料番号,館蔵コレ_小袖屏風.屏風番号,館蔵コレ_モティーフ.モティーフ,モティーフ分類.分類,館蔵コレ_小袖.地色,ベース.年代,高精細画像201404_屏風画像.画像ファイル名
				FROM 館蔵コレ_小袖,館蔵コレ_モティーフ,モティーフ分類,館蔵コレ_小袖屏風,高精細画像201404_屏風画像,縮小画像201509_領域,縮小画像201509_屏風画像,縮小画像201509_領域_モティーフ,(
					SELECT 年表.*,(年表.開始年+年表.終了年)/2 AS 年代
					FROM (
						SELECT 館蔵コレ_小袖.資料番号,MAX(時代.開始年) AS 開始年,MIN(時代.終了年) AS 終了年
						FROM 館蔵コレ_小袖,時代
						WHERE (館蔵コレ_小袖.[時代(和暦)] IN (時代.時代,時代.[館蔵コレ_時代(和暦)]) OR 館蔵コレ_小袖.[時代(西暦)] IN (時代.時代,時代.[館蔵コレ_時代(西暦)]))
						GROUP BY 館蔵コレ_小袖.資料番号
					) AS 年表
				) AS ベース
				WHERE 館蔵コレ_モティーフ.資料番号 = 館蔵コレ_小袖屏風.資料番号
				AND 館蔵コレ_小袖.資料番号 = 館蔵コレ_小袖屏風.資料番号
				AND ベース.資料番号 = 館蔵コレ_小袖屏風.資料番号
				AND 高精細画像201404_屏風画像.屏風番号 = 館蔵コレ_小袖屏風.屏風番号
				AND モティーフ分類.資料番号 = 館蔵コレ_小袖屏風.資料番号
				AND 縮小画像201509_領域.画像ファイル名 = 縮小画像201509_屏風画像.画像ファイル名
				AND 縮小画像201509_屏風画像.高精細画像ファイル名 = 高精細画像201404_屏風画像.画像ファイル名
				AND 縮小画像201509_領域.領域番号 = 縮小画像201509_領域_モティーフ.領域番号
				AND 館蔵コレ_モティーフ.モティーフ = 縮小画像201509_領域_モティーフ.モティーフ
				";

	$sql = 'SELECT DISTINCT 資料番号,屏風番号,'.$title_list[$row].','.$title_list[$col].',画像ファイル名 FROM ('.$sql.')';
	if($col == 'motif'){
		$sql_list ='SELECT DISTINCT モティーフ FROM 館蔵コレ_モティーフ,館蔵コレ_小袖屏風 WHERE 館蔵コレ_モティーフ.資料番号 = 館蔵コレ_小袖屏風.資料番号';
	}
	else if($col == 'color') {
		$sql_list ='SELECT DISTINCT 地色 FROM 館蔵コレ_小袖';
	}
	else if($col == 'class') {
		$sql_list ='SELECT DISTINCT 分類 FROM モティーフ分類';
	}
	else if($col == 'year_trimmed') {
		$sql_list ='SELECT DISTINCT (年表.開始年+年表.終了年)/2 AS 年代
								FROM (
									SELECT 館蔵コレ_小袖.資料番号,MAX(時代.開始年) AS 開始年,MIN(時代.終了年) AS 終了年
									FROM 館蔵コレ_小袖,時代
									WHERE (館蔵コレ_小袖.[時代(和暦)] IN (時代.時代,時代.[館蔵コレ_時代(和暦)]) OR 館蔵コレ_小袖.[時代(西暦)] IN (時代.時代,時代.[館蔵コレ_時代(西暦)]))
									GROUP BY 館蔵コレ_小袖.資料番号
								) AS 年表
								';
	}

	$sql_tech = "SELECT 館蔵コレ_小袖屏風.資料番号,技法
				FROM 館蔵コレ_小袖屏風,館蔵コレ_技法
				WHERE 館蔵コレ_小袖屏風.資料番号=館蔵コレ_技法.資料番号";

	$db = new KosodeDB();
		$json = $db->fetchArray($sql, 'items');
		$jsonTech = $db->fetchArray($sql_tech, 'tech');
		$ar = new ArrayReassembler();
		$jsonTech = $ar->getReassembledArray(new KinseiKimonoArrayReassembler, $jsonTech,  array( array('tech', '資料番号') ));
		$json = array_merge($json, $jsonTech);
	if(isset($sql_list)){
		$json = array_merge($json, $db->fetchArray($sql_list, 'list'));
	}

	$db->closeConnection();

	$jsoncodes = json_encode($json, JSON_UNESCAPED_UNICODE);

	header('Content-Type: application/json; charset=UTF-8');
	echo $jsoncodes;

?>
