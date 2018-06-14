<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/14
	 * Time: 14:03
	 */
namespace App\Http\Common;

class ErrorMsg{
	public static $succ = ["errno" => 0,"errmsg" => "ok"];
	public static $sysErr = ["errno" => 1,"errmsg" => "系统错误"];
}
