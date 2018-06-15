<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/15
	 * Time: 16:54
	 */
namespace App\Http\Common;

class NormalParams
{
	//--------------------------------------game-----------------------------
	//hu status
	const huStatusNot = 0;
	const huStatusYes = 1;
	//yu status
	const yuStatusNot = 0;
	const yuStatusYes = 1;
	//game status
	const gameStatusDefault = 0;//初始
	const gameStatusAlready = 1;//已经准备好了
	const gameStatusCaculateOver = 2;//计算完成
	const gameStatusCanNext = 3;//准备下一局
	const gameStatusFail = 4;//失败（一般不会有）
}