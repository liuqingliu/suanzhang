<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/11
	 * Time: 18:19
	 */
	namespace App\Http\Controllers\Api;
	use App\Models\User;
	use App\Http\Resources\User as UserCollection;
	use Illuminate\Support\Facades\Input;

	class IndexController extends ApiController
	{
		public function index(){

			return UserCollection::collection(User::paginate(Input::get('limit') ?: 20));

		}
	}