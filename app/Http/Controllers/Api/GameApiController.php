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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class GameApiController extends ApiController
{
	public function getGameinfo(Request $request){
		$this->validate($request, [
			'openid' => 'required|max:64',
		]);
		$openId = $request->openid;
		//获取game信息
		$gameInfo = Game::where("openid", $openId)->orderBy('id','desc')->first();
		Tools::ensureNotEmpty($gameInfo, ErrorMsg::$gameEmpty);
		$res = [
			"in_total_price" => Game::where("openid", $openId)->sum("in_price"),
			"out_total_price" => Game::where("openid", $openId)->sum("out_price"),
		];
		$res["total_price"] =  $res["in_total_price"] - $res["out_total_price"];
		$keys = [
			"game_status", "in_price", "out_price", "game_num"
		];
		$res = Tools::getMyArr($res, $keys, $gameInfo);
		//获取当前对局中，对厉害的，最菜的
		if($gameInfo->game_status>=2){
			$res["winner"] = Game::select(DB::raw("max(in_price-out_price) as hismoney, openid"))->where('game_num', $gameInfo->game_num)->groupBy("openid")->first();
			$res["loser"] = Game::select(DB::raw("min(in_price-out_price) as hismoney, openid"))->where('game_num', $gameInfo->game_num)->groupBy("openid")->first();
		}
		$succOut = ErrorMsg::$succ;
		$succOut["data"] = $res;
		Tools::outPut($succOut);
	}
}