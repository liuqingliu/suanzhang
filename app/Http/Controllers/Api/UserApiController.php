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

class UserApiController extends ApiController
{
	public function getUserinfo(Request $request){
		var_dump($request->name);exit;
		return UserCollection::collection(User::paginate(Input::get('limit') ?: 20));

	}
}