<?php
// ini_set("display_errors", On);
// error_reporting(E_ALL);
include '../ChromePhp.php';
require __DIR__ . "/../vendor/autoload.php";
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

use KosodeModules\DB\KosodeDB as KosodeDB;
use KosodeModules\ArrayReassembler as ArrayReassembler;
use KosodeModules\ReassembleArray as ReassembleArray;
use KosodeModules\KinseiKimonoArrayReassembler as KinseiKimonoArrayReassembler;
use KosodeModules\DetailArrayReassembler as DetailArrayReassembler;

	if(isset($_GET['param'])){
		$param = $_GET['param'];
	}else{
		die("パラメータ'param'が指定されていません.");
	}

	if(!isset($_GET['extend'])){
		$extend = 'off';
	} else{
		$extend = $_GET['extend'];
	}

	if($param == 'list'){
		$msg = "SELECT 屏風.解題_屏風番号, 高精細画像201404_屏風画像.*
						FROM 高精細画像201404_屏風画像,屏風
						WHERE 高精細画像201404_屏風画像.屏風番号 = 屏風.屏風番号
						ORDER BY 解題_屏風番号 ASC";
	}
	else if($param == 'list_kinsei_kimono' && $extend == 'off'){
		$msg = "SELECT
						    *
						FROM
						    (
						        SELECT
						            DISTINCT 高精細画像201404_屏風画像.屏風番号,
						            高精細画像201404_屏風画像.画像ファイル名,
						            章 as 小題,
												モティーフ,
						            開始ページ
						        FROM
						            高精細画像201404_屏風画像,
						            近世きもの万華鏡,
						            小袖,
						            館蔵コレ_小袖屏風
						        WHERE
						            高精細画像201404_屏風画像.屏風番号 = 館蔵コレ_小袖屏風.屏風番号
						        AND 小袖.館蔵コレ_小袖番号 = 館蔵コレ_小袖屏風.資料番号
						        AND 小袖.図版番号 = 近世きもの万華鏡.図版番号
						    )
						ORDER BY
						    開始ページ ASC
							";
		$msg2 = "SELECT DISTINCT 館蔵コレ_小袖屏風.屏風番号, 近世きもの万華鏡.モティーフ
						FROM 近世きもの万華鏡,縮小画像201509_領域_モティーフ,縮小画像201509_領域_小袖,小袖,館蔵コレ_小袖屏風
						WHERE 縮小画像201509_領域_モティーフ.領域番号 = 縮小画像201509_領域_小袖.領域番号
						AND  縮小画像201509_領域_小袖.小袖番号 = 小袖.館蔵コレ_小袖番号
						AND 近世きもの万華鏡.モティーフ = 縮小画像201509_領域_モティーフ.モティーフ
						AND 小袖.館蔵コレ_小袖番号 = 館蔵コレ_小袖屏風.資料番号
						AND 小袖.図版番号 = 近世きもの万華鏡.図版番号
							";
	}
	else if($param == 'list_kinsei_kimono' && $extend == 'on'){
		$msg = "SELECT
						    X.*,
						    Y.index
						FROM
						    (
						        SELECT
						            屏風番号,
						            CLng(Mid(A.屏風番号, InStrRev(A.屏風番号, '-') + 1)) as byobu,
						            画像ファイル名,
						            IIF(
						                近世きもの万華鏡_章 = '',
						                章,
						                近世きもの万華鏡_章
						            ) as 小題,
						            モティーフ,
						            IIF(
						                開始ページ = '',
						                NULL,
						                CLng(開始ページ)
						            ) as beginPage,
						            subindex
						        FROM
						            (
						                SELECT
						                    *
						                FROM
						                    (
						                        SELECT
						                            屏風番号,
						                            画像ファイル名,
						                            章,
						                            '' as 近世きもの万華鏡_章,
						                            モティーフ,
						                            開始ページ,
						                            '' as subindex
						                        FROM
						                            (
						                                SELECT
						                                    A.*,
						                                    文様.きもの文様図鑑_分類
						                                FROM
						                                    (
						                                        SELECT
						                                            DISTINCT 高精細画像201404_屏風画像.屏風番号,
						                                            高精細画像201404_屏風画像.画像ファイル名,
						                                            章,
						                                            モティーフ,
						                                            開始ページ
						                                        FROM
						                                            高精細画像201404_屏風画像,
						                                            近世きもの万華鏡,
						                                            小袖,
						                                            館蔵コレ_小袖屏風
						                                        WHERE
						                                            高精細画像201404_屏風画像.屏風番号 = 館蔵コレ_小袖屏風.屏風番号
						                                        AND 小袖.館蔵コレ_小袖番号 = 館蔵コレ_小袖屏風.資料番号
						                                        AND 小袖.図版番号 = 近世きもの万華鏡.図版番号
						                                    ) AS A
						                                    LEFT OUTER JOIN
						                                        文様
						                                    ON  文様.近世きもの万華鏡_章 = A.章
						                            )
						                        WHERE
						                            IsNULL(きもの文様図鑑_分類)
						                    )
						                UNION
						                (
						                    SELECT
						                        屏風番号,
						                        画像ファイル名,
						                        章,
						                        近世きもの万華鏡_章,
						                        B.モティーフ,
						                        '' as 開始ページ,
						                        subindex
						                    FROM
						                        (
						                            SELECT
						                                A.*,
						                                B.モティーフ,
						                                B.subindex
						                            FROM
						                                (
						                                    SELECT
						                                        DISTINCT 館蔵コレ_小袖屏風.屏風番号,
						                                        高精細画像201404_屏風画像.画像ファイル名,
						                                        きもの文様図鑑_分類クエリ.分類 as 章
						                                    FROM
						                                        きもの文様図鑑_分類クエリ,
						                                        きもの文様図鑑_分類,
						                                        館蔵コレ_小袖屏風,
						                                        高精細画像201404_屏風画像
						                                    WHERE
						                                        館蔵コレ_小袖屏風.資料番号 = きもの文様図鑑_分類クエリ.資料番号
						                                    AND 高精細画像201404_屏風画像.屏風番号 = 館蔵コレ_小袖屏風.屏風番号
						                                    AND きもの文様図鑑_分類.分類 = きもの文様図鑑_分類クエリ.分類
						                                ) as A
						                                LEFT OUTER JOIN
						                                    (
						                                        SELECT
						                                            DISTINCT 館蔵コレ_小袖屏風.屏風番号,
						                                            きもの文様図鑑_意匠クエリ.種類 as モティーフ,
						                                            きもの文様図鑑_種類.分類 as 章,
						                                            きもの文様図鑑_種類.開始ページ数 as subindex
						                                        FROM
						                                            きもの文様図鑑_意匠クエリ,
						                                            館蔵コレ_小袖屏風,
						                                            きもの文様図鑑_種類
						                                        WHERE
						                                            館蔵コレ_小袖屏風.資料番号 = きもの文様図鑑_意匠クエリ.資料番号
						                                        AND きもの文様図鑑_種類.種類 = きもの文様図鑑_意匠クエリ.種類
						                                    ) as B
						                                ON  A.屏風番号 = B.屏風番号
						                                AND A.章 = B.章
						                        ),
						                        文様
						                    WHERE
						                        文様.きもの文様図鑑_分類 = 章
						                )
						            )
						    ) AS X,
						    (
						        SELECT
						            MIN(開始ページ) as index,
						            章
						        FROM
						            近世きもの万華鏡
						        GROUP BY
						            章
						        ORDER BY
						            MIN(開始ページ)
						    ) AS Y
						WHERE
						    小題 = Y.章
						ORDER BY
						    index,
						    beginPage,
						    subindex,
						    byobu
					 ";
	}
	else if($param == 'list_kimono_monyo'){
		$msg ="SELECT 屏風番号, index, 画像ファイル名, 章 as 小題, B.モティーフ
						FROM(
						SELECT
						            CLng(Mid(A.屏風番号, InStrRev(A.屏風番号, '-')+1)) as index, A.*, B.モティーフ
						        FROM
						            (
						                SELECT
						                    DISTINCT 館蔵コレ_小袖屏風.屏風番号,
						                    高精細画像201404_屏風画像.画像ファイル名,
						                    きもの文様図鑑_分類クエリ.分類 as 章,
																きもの文様図鑑_分類.開始ページ数
						                FROM
						                    きもの文様図鑑_分類クエリ,
																きもの文様図鑑_分類,
						                    館蔵コレ_小袖屏風,
						                    高精細画像201404_屏風画像
						                WHERE
						                    館蔵コレ_小袖屏風.資料番号 = きもの文様図鑑_分類クエリ.資料番号
						                AND 高精細画像201404_屏風画像.屏風番号 = 館蔵コレ_小袖屏風.屏風番号
														AND きもの文様図鑑_分類.分類 = きもの文様図鑑_分類クエリ.分類
						            ) as A
						            LEFT OUTER JOIN
						                (
						                    SELECT
						                        DISTINCT 館蔵コレ_小袖屏風.屏風番号,
						                        きもの文様図鑑_意匠クエリ.種類 as モティーフ,
						                        きもの文様図鑑_種類.分類 as 章
						                    FROM
						                        きもの文様図鑑_意匠クエリ,
						                        館蔵コレ_小袖屏風,
						                        きもの文様図鑑_種類
						                    WHERE
						                        館蔵コレ_小袖屏風.資料番号 = きもの文様図鑑_意匠クエリ.資料番号
						                    AND きもの文様図鑑_種類.種類 = きもの文様図鑑_意匠クエリ.種類
						                ) as B
						            ON  A.屏風番号 = B.屏風番号
						            AND A.章 = B.章
						)
						ORDER BY 開始ページ数, B.モティーフ, index
						";
	} else if ($param == 'list_motif_combination'){
		// $msg = "SELECT *
		// 				FROM (
		// 				SELECT DISTINCT
		// 				    A.組合せ as 小題,
		// 				    A.資料番号,
		// 				    館蔵コレ_小袖屏風.屏風番号,
		// 				    高精細画像201404_屏風画像.画像ファイル名
		//
		// 				FROM
		// 				    (
		// 				        SELECT
		// 				            DISTINCT 組合せ,
		// 				            資料番号,
		// 				            開始ページ数
		// 				        FROM
		// 				            きもの文様図鑑_意匠_モティーフ組合せ
		// 				        UNION
		// 				        SELECT
		// 				            DISTINCT 組合せ,
		// 				            資料番号,
		// 				            開始ページ数
		// 				        FROM
		// 				            きもの文様図鑑_文様_モティーフ組合せ
		// 				    ) as A,
		// 				    高精細画像201404_屏風画像,
		// 				    館蔵コレ_小袖屏風
		// 				WHERE
		// 				    高精細画像201404_屏風画像.屏風番号 = 館蔵コレ_小袖屏風.屏風番号
		// 				AND 館蔵コレ_小袖屏風.資料番号 = A.資料番号
		// 				)
		// 				ORDER BY
		// 				    小題,
		// 				    CLng(Mid(屏風番号, InStrRev(屏風番号, '-') + 1))
		// 			 ";
		$msg = "SELECT *
						FROM (
						select DISTINCT
						    資料番号,
						    モティーフ_小袖_組合せ.組合せ as 小題,
						    屏風番号,
						    高精細画像201404_屏風画像.画像ファイル名
						from
						    (
						        SELECT
						            資料番号,
						            組合せ
						        from
						            (
						                SELECT
						                    *
						                FROM
						                    (
						                        SELECT
						                            文様_モティーフ要素数.文様 as 組合せ,
						                            きもの文様図鑑_文様_小袖モティーフ要素数.資料番号,
						                            モティーフ要素,
						                            文様_モティーフ要素数.モティーフ要素数
						                        FROM
						                            (
						                                SELECT
						                                    文様,
						                                    Count([モティーフ要素]) AS モティーフ要素数
						                                FROM
						                                    (
						                                        SELECT DISTINCT
						                                            [きもの文様図鑑_文様_モティーフ要素].文様,
						                                            [きもの文様図鑑_文様_モティーフ要素].[モティーフ要素]
						                                        FROM
						                                            きもの文様図鑑_文様,
						                                            きもの文様図鑑_文様_モティーフ要素
						                                        WHERE
						                                            [きもの文様図鑑_文様].文様=[きもの文様図鑑_文様_モティーフ要素].文様
						                                    ) AS 文様_モティーフ要素
						                                GROUP BY
						                                    文様
						                            ) AS 文様_モティーフ要素数,
						                            (
						                                SELECT
						                                    資料番号,
						                                    文様,
						                                    Count(小袖_モティーフ要素.[モティーフ要素]) AS モティーフ要素数
						                                FROM
						                                    モティーフ,
						                                    (
						                                        SELECT DISTINCT
						                                            資料番号,
						                                            館蔵コレ_モティーフ.[モティーフ] as モティーフ,
						                                            [モティーフ要素]
						                                        FROM
						                                            館蔵コレ_モティーフ,
						                                            館蔵コレ_モティーフ_モティーフ要素
						                                        WHERE
						                                            館蔵コレ_モティーフ.[モティーフ]=館蔵コレ_モティーフ_モティーフ要素.[モティーフ]
						                                    ) as 小袖_モティーフ要素,
						                                    きもの文様図鑑_文様_モティーフ要素
						                                WHERE
						                                    [モティーフ].館蔵コレ_モティーフ=小袖_モティーフ要素.[モティーフ要素] And
						                                [モティーフ].[きもの文様図鑑_モティーフ]=[きもの文様図鑑_文様_モティーフ要素].[モティーフ要素] And
						                                文様 <> 小袖_モティーフ要素.モティーフ
						                                GROUP BY
						                                    資料番号,
						                                    文様
						                            ) as きもの文様図鑑_文様_小袖モティーフ要素数,
						                            きもの文様図鑑_文様_モティーフ要素
						                        WHERE
						                            文様_モティーフ要素数.文様=きもの文様図鑑_文様_小袖モティーフ要素数.文様 And
						                        文様_モティーフ要素数.[モティーフ要素数]=きもの文様図鑑_文様_小袖モティーフ要素数.[モティーフ要素数] And
						                        文様_モティーフ要素数.[モティーフ要素数] > 1 And
						                        文様_モティーフ要素数.文様=きもの文様図鑑_文様_モティーフ要素.文様
						                        UNION
						                        SELECT
						                            意匠_モティーフ要素数.意匠,
						                            きもの文様図鑑_意匠_小袖モティーフ要素数.資料番号,
						                            モティーフ要素,
						                            意匠_モティーフ要素数.モティーフ要素数
						                        FROM
						                            (
						                                SELECT
						                                    意匠,
						                                    Count([モティーフ要素]) AS モティーフ要素数
						                                FROM
						                                    (
						                                        SELECT DISTINCT
						                                            [きもの文様図鑑_意匠_モティーフ要素].意匠,
						                                            [きもの文様図鑑_意匠_モティーフ要素].[モティーフ要素]
						                                        FROM
						                                            きもの文様図鑑_文様,
						                                            きもの文様図鑑_意匠,
						                                            きもの文様図鑑_文様_モティーフ要素,
						                                            きもの文様図鑑_意匠_モティーフ要素
						                                        WHERE
						                                            [きもの文様図鑑_文様].文様=[きもの文様図鑑_意匠].文様 And
						                                        [きもの文様図鑑_意匠].意匠=[きもの文様図鑑_意匠_モティーフ要素].意匠
						                                    ) AS 意匠_モティーフ要素
						                                GROUP BY
						                                    意匠
						                            ) AS 意匠_モティーフ要素数,
						                            (
						                                SELECT
						                                    資料番号,
						                                    意匠,
						                                    Count(小袖_モティーフ要素.[モティーフ要素]) AS モティーフ要素数
						                                FROM
						                                    モティーフ,
						                                    (
						                                        SELECT DISTINCT
						                                            資料番号,
						                                            [モティーフ要素]
						                                        FROM
						                                            館蔵コレ_モティーフ,
						                                            館蔵コレ_モティーフ_モティーフ要素
						                                        WHERE
						                                            館蔵コレ_モティーフ.[モティーフ]=館蔵コレ_モティーフ_モティーフ要素.[モティーフ]
						                                    ) as 小袖_モティーフ要素,
						                                    きもの文様図鑑_意匠_モティーフ要素
						                                WHERE
						                                    [モティーフ].館蔵コレ_モティーフ=小袖_モティーフ要素.[モティーフ要素] And
						                                [モティーフ].[きもの文様図鑑_意匠]=[きもの文様図鑑_意匠_モティーフ要素].[モティーフ要素]
						                                GROUP BY
						                                    資料番号,
						                                    意匠
						                            ) as きもの文様図鑑_意匠_小袖モティーフ要素数,
						                            きもの文様図鑑_意匠_モティーフ要素
						                        WHERE
						                            意匠_モティーフ要素数.意匠=きもの文様図鑑_意匠_小袖モティーフ要素数.意匠 And
						                        意匠_モティーフ要素数.[モティーフ要素数]=きもの文様図鑑_意匠_小袖モティーフ要素数.[モティーフ要素数] And
						                        意匠_モティーフ要素数.[モティーフ要素数] > 1 And
						                        意匠_モティーフ要素数.意匠=きもの文様図鑑_意匠_モティーフ要素.意匠
						                    ) as モティーフ_組合せ,
						                    (
						                        SELECT
						                            小袖番号,
						                            [モティーフ]
						                        FROM
						                            (
						                                SELECT
						                                    小袖番号,
						                                    [モティーフ]
						                                FROM
						                                    縮小画像201509_領域_モティーフ,
						                                    縮小画像201509_領域_小袖
						                                WHERE
						                                    縮小画像201509_領域_モティーフ.領域番号 = 縮小画像201509_領域_小袖.領域番号
						                            ) AS 領域_小袖_モティーフ_要素
						                        GROUP BY
						                            小袖番号,
						                            [モティーフ]
						                        ORDER BY
						                            小袖番号
						                    ) as 領域_小袖_モティーフ要素
						                where
						                    [モティーフ_組合せ].資料番号=領域_小袖_モティーフ要素.小袖番号 And
						                [モティーフ_組合せ].[モティーフ要素]=領域_小袖_モティーフ要素.[モティーフ]
						            ) AS モティーフ_組合せ_モティーフ要素
						        GROUP BY
						            [モティーフ_組合せ_モティーフ要素].資料番号,
						            [モティーフ_組合せ_モティーフ要素].組合せ,
						            [モティーフ_組合せ_モティーフ要素].[モティーフ要素数]
						        HAVING
						            ((([モティーフ_組合せ_モティーフ要素].[モティーフ要素数])=Count(*)))
						    ) as モティーフ_小袖_組合せ,
						    (
						        SELECT DISTINCT
						            [きもの文様図鑑_意匠_モティーフ要素].意匠 as 組合せ,
						            [きもの文様図鑑_意匠_モティーフ要素].[モティーフ要素]
						        FROM
						            きもの文様図鑑_文様,
						            きもの文様図鑑_意匠,
						            きもの文様図鑑_文様_モティーフ要素,
						            きもの文様図鑑_意匠_モティーフ要素
						        WHERE
						            [きもの文様図鑑_文様].文様=[きもの文様図鑑_意匠].文様 And
						        [きもの文様図鑑_意匠].意匠=[きもの文様図鑑_意匠_モティーフ要素].意匠
						        union
						        SELECT DISTINCT
						            [きもの文様図鑑_文様_モティーフ要素].文様 as 組合せ,
						            [きもの文様図鑑_文様_モティーフ要素].[モティーフ要素]
						        FROM
						            きもの文様図鑑_文様,
						            きもの文様図鑑_文様_モティーフ要素
						        WHERE
						            [きもの文様図鑑_文様].文様=[きもの文様図鑑_文様_モティーフ要素].文様
						    ) as きもの文様図鑑_モティーフ_組合せ,
						    縮小画像201509_領域_小袖,
						    縮小画像201509_領域_モティーフ,
						    縮小画像201509_領域,
						    縮小画像201509_屏風画像,
						    高精細画像201404_屏風画像
						where
						    モティーフ_小袖_組合せ.組合せ = きもの文様図鑑_モティーフ_組合せ.組合せ And
						モティーフ_小袖_組合せ.資料番号=縮小画像201509_領域_小袖.小袖番号 And
						きもの文様図鑑_モティーフ_組合せ.[モティーフ要素]=縮小画像201509_領域_モティーフ.[モティーフ] And
						縮小画像201509_領域_小袖.領域番号=縮小画像201509_領域_モティーフ.領域番号 And
						縮小画像201509_領域_モティーフ.領域番号=縮小画像201509_領域.領域番号 and
						縮小画像201509_屏風画像.画像ファイル名=縮小画像201509_領域.画像ファイル名 and
						縮小画像201509_屏風画像.高精細画像ファイル名=高精細画像201404_屏風画像.画像ファイル名
						)
						order by 小題,
						CLng(Mid(屏風番号, InStrRev(屏風番号, '-') + 1))

		";
		$msg2 = "SELECT DISTINCT 資料番号,モティーフ要素,組合せ
							FROM (
									SELECT
						        *
									FROM
						        きもの文様図鑑_意匠_モティーフ組合せ
						      UNION
						      SELECT
						        *
						      FROM
						        きもの文様図鑑_文様_モティーフ組合せ
							)
					";
	}	else if($param == 'detail'){
		$id = $_GET['id'];
		$msg = "SELECT 館蔵コレ_小袖屏風.資料番号,[資料名称(漢字)],[資料名称(かな)],種類,[時代(和暦)],[時代(西暦)],[法量(縦)],[法量(横)],地色,材質,画像ファイル名
					FROM 館蔵コレ_小袖,館蔵コレ_小袖屏風,高精細画像201404_屏風画像
					WHERE 館蔵コレ_小袖.資料番号=館蔵コレ_小袖屏風.資料番号
					AND 館蔵コレ_小袖屏風.屏風番号=高精細画像201404_屏風画像.屏風番号
					AND 館蔵コレ_小袖屏風.屏風番号='".$id."'";
		$msg2 = "SELECT 館蔵コレ_小袖屏風.資料番号,モティーフ
					FROM 館蔵コレ_小袖屏風,館蔵コレ_モティーフ
					WHERE 館蔵コレ_小袖屏風.資料番号=館蔵コレ_モティーフ.資料番号
					AND 館蔵コレ_小袖屏風.屏風番号='".$id."'";
		$msg3 = "SELECT 館蔵コレ_小袖屏風.資料番号,技法
					FROM 館蔵コレ_小袖屏風,館蔵コレ_技法
					WHERE 館蔵コレ_小袖屏風.資料番号=館蔵コレ_技法.資料番号
					AND 館蔵コレ_小袖屏風.屏風番号='".$id."'";

		$msg4 = "SELECT 解題_小袖.資料番号, 解題
					FROM 解題_小袖,小袖,館蔵コレ_小袖屏風
					WHERE 解題_小袖.資料番号=小袖.解題_小袖番号
					AND 小袖.館蔵コレ_小袖番号=館蔵コレ_小袖屏風.資料番号
					AND 館蔵コレ_小袖屏風.屏風番号='".$id."'";
		$msg4_1 = "SELECT 解題_技法.資料番号, 技法
					FROM 解題_技法,小袖,館蔵コレ_小袖屏風
					WHERE 解題_技法.資料番号=小袖.解題_小袖番号
					AND 小袖.館蔵コレ_小袖番号=館蔵コレ_小袖屏風.資料番号
					AND 館蔵コレ_小袖屏風.屏風番号='".$id."'";

		$msg5 = "SELECT LoW_小袖.*
					FROM LoW_小袖,小袖,館蔵コレ_小袖屏風
					WHERE LoW_小袖.資料番号=小袖.LoW_小袖番号
					AND 小袖.館蔵コレ_小袖番号=館蔵コレ_小袖屏風.資料番号
					AND 館蔵コレ_小袖屏風.屏風番号='".$id."'";
		$msg5_1 = "SELECT LoW_技法.*
					FROM LoW_技法,小袖,館蔵コレ_小袖屏風
					WHERE LoW_技法.資料番号=小袖.LoW_小袖番号
					AND 小袖.館蔵コレ_小袖番号=館蔵コレ_小袖屏風.資料番号
					AND 館蔵コレ_小袖屏風.屏風番号='".$id."'";

		$msg6 = "SELECT 雛形_小袖.*
					FROM 雛形_小袖,小袖,館蔵コレ_小袖屏風
					WHERE 雛形_小袖.資料番号=小袖.雛形_小袖番号
					AND 小袖.館蔵コレ_小袖番号=館蔵コレ_小袖屏風.資料番号
					AND 館蔵コレ_小袖屏風.屏風番号='".$id."'";
	}

	$db = new KosodeDB();
	$result = $db->fetchArray($msg, 'items');

	/**
	 * 詳細表示画面処理
	 */
	if($param == 'detail'){
		$keyname = array( array('motif', 'モティーフ', 'items'), array('technique', '技法', 'items'));

		// items, motif, technique配列結合
		$dummy = $result;
		$dummy = array_merge($dummy, $db->fetchArray($msg2, $keyname[0][0]));
		$dummy = array_merge($dummy, $db->fetchArray($msg3, $keyname[1][0]));


		// items配列に 'モティーフ', '技法' 配列を組み込む
		$ar = new ArrayReassembler();
		$result = $ar->getReassembledArray(new DetailArrayReassembler, $dummy,  $keyname);

		$keyname = array( array('kaidai_g', '技法', 'kaidai'));
		$dummy =  $db->fetchArray($msg4, 'kaidai');
		$dummy = array_merge($dummy, $db->fetchArray($msg4_1, 'kaidai_g'));
		$result = array_merge($result, $ar->getReassembledArray(new DetailArrayReassembler, $dummy,  $keyname));

		$keyname = array( array('low_g', '技法', 'low'));
		$dummy =  $db->fetchArray($msg5, 'low');
		$dummy = array_merge($dummy, $db->fetchArray($msg5_1, 'low_g'));
		$result = array_merge($result, $ar->getReassembledArray(new DetailArrayReassembler, $dummy,  $keyname));

		$result = array_merge($result, $db->fetchArray($msg6, 'hinagata'));
		ChromePhp::log($result);
	}

	/**
	 * 近世きもの万華鏡画面処理
	 */
	else if($param == 'list_kinsei_kimono'){
		if($extend == 'off')
			$result = array_merge($result, $db->fetchArray($msg2, 'motif'));

		if($extend == 'off')
			$keyname = array( array('items', '小題'), array('motif', '屏風番号'));
		else
			$keyname = array( array('items', '小題'));
		$ar = new ArrayReassembler();
		$result = $ar->getReassembledArray(new KinseiKimonoArrayReassembler, $result,  $keyname);
		ChromePhp::log($result);
	}

  else if($param == 'list_kimono_monyo'){
		ChromePhp::log($result);
		$keyname = array( array('items', '小題'));
		$ar = new ArrayReassembler();
		$result = $ar->getReassembledArray(new KinseiKimonoArrayReassembler, $result,  $keyname);
		ChromePhp::log($result);
	}

	else if($param == 'list_motif_combination'){
		ChromePhp::log($result);
		$result = array_merge($result, $db->fetchArray($msg2, 'motif'));
		$keyname = array( array('items', '小題'), array('motif', '資料番号'));
		$ar = new ArrayReassembler();
		$result = $ar->getReassembledArray(new KinseiKimonoArrayReassembler, $result,  $keyname);
		ChromePhp::log($result);
	}


	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($result, JSON_UNESCAPED_UNICODE);

	$db->closeConnection();

?>
