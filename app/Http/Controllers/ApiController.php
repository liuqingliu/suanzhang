<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/11
	 * Time: 16:40
	 */
namespace App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
	public function getUserinfo(Request $request)
	{
		var_dump($request);exit;
		return $request;
	}

}