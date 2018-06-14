<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/14
	 * Time: 14:06
	 */
class Tools
{
	public static function outPut($outData, $callback=false)
	{
		if($callback) {

		}else{
			return json_encode($outData);
		}
	}
}