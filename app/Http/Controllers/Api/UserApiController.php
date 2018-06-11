<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/11
	 * Time: 18:19
	 */
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\User as UserCollection;
use Illuminate\Support\Facades\Input;
use WXBizDataCrypt;

class UserApiController extends ApiController
{
	public function getUserinfo(Request $request){
		var_dump($request->userinfo);exit;
		$pc = new WXBizDataCrypt(env("APPID"), $request->sessionKey);
		$errCode = $pc->decryptData($request->encryptedData, $request->iv, $data );

		if ($errCode == 0) {
			print($data . "\n");
		} else {
			print($errCode . "\n");
		}
		var_dump(env("APPID"),$request->sessionKey,$request->encryptedData,$request->iv,$data);exit;
		return UserCollection::collection(User::paginate(Input::get('limit') ?: 20));

	}
}