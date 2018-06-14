<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/11
	 * Time: 18:19
	 */
namespace App\Http\Controllers\Api;


use App\Http\Common\ErrorMsg;
use App\Http\Common\Tools;
use App\Models\Game;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Input;


class UserApiController extends ApiController
{
	public function getUserinfo(Request $request){
		$loginInfo = $this->getLoginInfo($request->code);
		$userInfo = json_decode($request->userinfo);
		$userInfoDetail = $userInfo->userInfo;
		//获取game信息
		$gameInfo = Game::where("openid", $loginInfo->openid)->orderBy('id','desc')->first();
		if(empty($gameInfo)) {
			//todo:这里要改!
			User::updateOrCreate(['openid' => $loginInfo->openid], [
				'nickName' => $userInfoDetail->nickName,
				'gender' => $userInfoDetail->gender,
				'city' => $userInfoDetail->city,
				'province' => $userInfoDetail->province,
				'country' => $userInfoDetail->country,
				'avatarUrl' => $userInfoDetail->avatarUrl,
				'ip' => $request->getClientIp(),
			]);
		}
		$succOut = ErrorMsg::$succ;
		$succOut["data"] = $gameInfo;
		Tools::outPut($succOut);
	}

	private function getLoginInfo($code){
		$client = new Client();
# 获取一个外部 API 接口：
		$appId = env("APPID");
		$secret = env("APPSECRET");
		$res = $client->request('GET', 'https://api.weixin.qq.com/sns/jscode2session', [
			'query' => [
				'appid' => "{$appId}",
				'secret' => "{$secret}",
				'js_code' => "{$code}",
				'grant_type' => 'authorization_code'
			]
		]);
		return json_decode($res->getBody()->getContents());
	}
}