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
		$sessionCode = $this->getSessionKeys($request->code);
		var_dump($sessionCode);exit;
		$pc = new WXBizDataCrypt(env("APPID"), $sessionCode);
		$errCode = $pc->decryptData($request->encryptedData, $request->iv, $data );

		if ($errCode == 0) {
			print($data . "\n");
		} else {
			print($errCode . "\n");
		}
		var_dump(env("APPID"),$request->sessionKey,$request->encryptedData,$request->iv,$data);exit;
		return UserCollection::collection(User::paginate(Input::get('limit') ?: 20));
	}

	private function getSessionKeys($code){
		$client = new Client();
# 获取一个外部 API 接口：
		$res = $client->request('GET', 'https://api.weixin.qq.com/sns/jscode2session', [
			'appid' => env("APPID"),
			'secret' => env("APPSERCRET"),
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		]);
//		$response = $client->get('https://api.weixin.qq.com/sns/jscode2session?appid='.env("APPID")
//			.'&secret='.env("APPSERCRET").'&js_code='.$code.'&grant_type=authorization_code');
# echo 结果
		return $res->getBody();
	}
}