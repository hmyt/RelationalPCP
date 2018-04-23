<?php
namespace KosodeModules\DB;

class KosodeDB{
	private $con;
	function __construct(){
		$accessFile = mb_convert_encoding(__DIR__."\..\..\..\kosode\小袖屏風データベース_ver3.5.6_hamasaki.accdb", "Shift_JIS", "UTF-8");
		$DSN = "Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$accessFile";
		$DBUSER = "";
		$DBPASSWORD = "";

		$this->con = odbc_connect($DSN, $DBUSER, $DBPASSWORD);
		if(!$this->con){
			die ("接続できませんでした.");
		}
	}

	public function fetchArray($query, $array_key){
		$query = mb_convert_encoding($query, "Shift_JIS", "UTF-8");
		$result = odbc_exec($this->con, $query)
								or die("odbc_exec() Failed: " . mb_convert_encoding($query, "UTF-8", "Shift_JIS"));
		$json = array();
		while($fetch = $this->fetch_json($result)){
			$json[$array_key][] = $fetch;
		}

		return $json;
	}

	/**
	 * [odbc結果IDから連想配列を取得し、UTF-8に変換する関数]
	 * @param  [resource] $result [ODBC結果ID]
	 * @return [array]         [取得した連想配列をUTF-8で返す]
	 */
	private function fetch_json( $result ) {
		$ret = odbc_fetch_array( $result );
		$ret2 = array();
		while (list($_key, $_value) = @each($ret)) {
			$_key = mb_convert_encoding($_key, "UTF-8", "Shift_JIS");
			$encoded_value = mb_convert_encoding( $_value, "UTF-8", "Shift_JIS" );
			// $ret2[$_key] = $encoded_value == "" ? NULL : $encoded_value;
			$ret2[$_key] = $encoded_value;
		}
		return $ret2;
	}

	public function closeConnection(){
		odbc_close($this->con);
	}
}
?>
