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
use App\Http\Common\NormalParams;
use Validator;

class GameApiController extends ApiController
{
	public function getGameinfo(Request $request){
		$validator = Validator::make($request->all(), [
			'openid' => 'required|max:64',
		]);

		Tools::ensureNotFalse($validator->fails(), ErrorMsg::$paramsErr);
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

	public function calculateMoney(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'openid' => 'required|max:64',
			'game_num' => 'required|int',
			'hu_status' => 'required|int',
			'hu_people_list' => 'required',
			'hu_times' => 'required|int',
			'yu_status' => 'required|int',
			'yu_times' => 'required|int',
			'yu_people_list' => 'required',
		]);
		Tools::ensureNotFalse($validator->fails(), ErrorMsg::$paramsErr);
		//更新game表
		//状态置为1
		$gameOrm = Game::where("openid", $request->openid)->where("game_num", $request->game_num)->where("game_status", NormalParams::gameStatusDefault)->first();
		Tools::ensureNotEmpty($gameOrm, ErrorMsg::$gameEmpty);
		$gameOrm->hu_status = $request->hu_status;
		$gameOrm->hu_people_list = $request->hu_people_list;
		$gameOrm->hu_times = $request->hu_times;
		$gameOrm->yu_status = $request->yu_status;
		$gameOrm->yu_times = $request->yu_times;
		$gameOrm->yu_people_list = $request->yu_people_list;
		$gameOrm->game_status = NormalParams::gameStatusAlready;
		$gameOrm->save();
		//查看当前是否可以进行运算（4个人的状态都是1）
		$gameList = Game::where("game_num", $request->game_num)->where("game_status", NormalParams::gameStatusAlready)->get()->keyBy("openid");
		if(count($gameList) == 4) { //4个人准备好了。开始计算
			//怎么才算是否计算正确呢。
			//输的人。没法选择输给了谁
			//赢的人。没法选择赢了谁
			//胡了的人。也可能会输
			//没胡的人。也可能会赢
			//所以。我们假设知道谁赢了哪些人。谁输了哪些人
			$finalRes = [];
			foreach ($gameList as $game) {
				//如果胡了
				$inPirce = 0;
				if($game["game_status"] == NormalParams::huStatusYes) {
					$huOpenIdList = explode(",", $game["hu_people_list"]);
					$inPirce += count($huOpenIdList) * $game["hu_times"];
					foreach ($huOpenIdList as $loserOpenId) {
						if(isset($finalRes["$loserOpenId"]["out_price"])) {
							$finalRes["$loserOpenId"]["out_price"] += $game["hu_times"];
						}else {
							$finalRes["$loserOpenId"]["out_price"] = $game["hu_times"];
						}
					}
				}
				if($game["yu_status"] == NormalParams::yuStatusYes) {
					$yuOpenIdList = explode(",", $game["yu_people_list"]);
					$inPirce += count($yuOpenIdList) * $game["yu_times"];
					foreach ($yuOpenIdList as $loserOpenId) {
						if(isset($finalRes["$loserOpenId"]["out_price"])) {
							$finalRes["$loserOpenId"]["out_price"] += $game["yu_times"];
						}else {
							$finalRes["$loserOpenId"]["out_price"] = $game["yu_times"];
						}
					}
				}
				$finalRes["{$game['openid']}"]["in_price"] = $inPirce;
			}
			foreach ($gameList as &$game) {
				$game->in_price = isset($finalRes["{$game['openid']}"]["in_price"]) ? $finalRes["{$game['openid']}"]["in_price"] : 0;
				$game->out_price = isset($finalRes["{$game['openid']}"]["out_price"]) ? $finalRes["{$game['openid']}"]["out_price"] : 0;
				$game->save();//循环保存
			}
			Tools::outPut(ErrorMsg::$succ);
		}else{
			Tools::outPut(ErrorMsg::$gameCalculateErr);
		}
	}
}