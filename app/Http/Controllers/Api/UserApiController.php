<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/11
	 * Time: 18:19
	 */
namespace App\Http\Controllers\Api;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\User as UserCollection;
use Illuminate\Support\Facades\Input;
use WXBizDataCrypt;

class UserApiController extends ApiController
{
	public function getUserinfo(Request $request){
		$loginInfo = $this->getLoginInfo($request->code);
		$pc = new WXBizDataCrypt(env("APPID"), $loginInfo->session_key);
		$userInfo = $request->userinfo;
		$errCode = $pc->decryptData($userInfo['encryptedData'], $userInfo['iv'], $data );

		if ($errCode == 0) {
			print($data . "\n");
		} else {
			print($errCode . "\n");
		}
		var_dump($errCode,$data);exit;
		return UserCollection::collection(User::paginate(Input::get('limit') ?: 20));
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