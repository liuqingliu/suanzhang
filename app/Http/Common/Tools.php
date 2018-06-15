<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/14
	 * Time: 14:06
	 */

namespace App\Http\Common;

class Tools
{
	public static function outPut($outData, $callback=false)
	{
		if($callback) {
			echo $callback.'('.json_encode($outData).')';
		}else{
			echo json_encode($outData);
		}
		exit;
	}

	public static function ensureNotEmpty($v, $outData)
	{
		if(empty($v)) {
			self::outPut($outData);
		}
	}

	public static function ensureEmpty($v, $outData)
	{
		if(!empty($v)) {
			self::outPut($outData);
		}
	}

	public static function ensureNotFalse($v, $outData)
	{
		if($v===false) {
			self::outPut($outData);
		}
	}

	public static function ensureFalse($v, $outData)
	{
		if($v!==false) {
			self::outPut($outData);
		}
	}

	public static function getMyArr($res = [], $keys, $data)
	{
		foreach ($keys as $v) {
			$res["$v"] = isset($data["$v"]) ? $data["$v"] : "";
		}
		return $res;
	}
}