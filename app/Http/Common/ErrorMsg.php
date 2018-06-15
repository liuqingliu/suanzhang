<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/14
	 * Time: 14:03
	 */
namespace App\Http\Common;

class ErrorMsg{
	//系统级别，通用信息 0-100
	public static $succ = ["errno" => 0,"errmsg" => "ok"];
	public static $sysErr = ["errno" => 1,"errmsg" => "系统错误"];
	public static $paramsErr = ["errno" => 2,"errmsg" => "参数校验失败"];
	//用戶报错信息
	//100-200
	public static $userOpenidEmpty =  ["errno" => 100,"errmsg" => "用户openid为空"];

	//game 200-2020
	public static $gameEmpty =  ["errno" => 200,"errmsg" => "游戏信息为空"];
	public static $gameCalculateErr =  ["errno" => 201,"errmsg" => "游戏计算出错"];
}
