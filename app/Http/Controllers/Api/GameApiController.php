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

		Tools::ensureFalse($validator->fails(), ErrorMsg::$paramsErr);
		$openId = $request->openid;
		//获取game信息
		$gameInfo = Game::where("openid", $openId)->orderBy('id','desc')->first();
		Tools::ensureNotEmpty($gameInfo, ErrorMsg::$gameEmpty);
		$res = [
			"yu_times" => $gameInfo->yu_times,
			"hu_times" => $gameInfo->hu_times,
			"in_total_price" => Game::where("openid", $openId)->sum("in_price"),
			"out_total_price" => Game::where("openid", $openId)->sum("out_price"),
		];
		$res["total_price"] =  $res["in_total_price"] - $res["out_total_price"];
		$keys = [
			"game_status", "in_price", "out_price", "game_num"
		];
		$res = Tools::getMyArr($res, $keys, $gameInfo);
		//获取当前对局中，对厉害的，最菜的
		$gameUserList = Game::select(DB::raw("(in_price-out_price) as hismoney, openid"))->where('game_num', $gameInfo->game_num)->orderBy("hismoney")->get();
		$otherUserHuList = [];
		$otherUserYuList = [];
		$yuList = explode(",", $gameInfo->yu_people_list);
		$huList = explode(",", $gameInfo->hu_people_list);

		foreach ($gameUserList as $gameUser) {
			if($gameUser->openid != $openId){
				$otherUserHuList[] = ["value" => $gameUser->user->nickName, "name" => $gameUser->openid, "checks" => in_array($gameUser->openid, $huList) ? true :false];
				$otherUserYuList[] = ["value" => $gameUser->user->nickName, "name" => $gameUser->openid, "checks" => in_array($gameUser->openid, $yuList) ? true :false];
			}
		}

		if($gameInfo->game_status>=2){
			$res["winner"] = $gameUserList[3];
			$res["loser"] = $gameUserList[0];
		}
		//查看当前对局有哪几个人
		$res["other_user_hu_list"] = $otherUserHuList;
		$res["other_user_yu_list"] = $otherUserYuList;
		$succOut = ErrorMsg::$succ;
		$succOut["data"] = $res;
		Tools::outPut($succOut);
	}

	public function calculateMoney(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'openid' => 'required|max:64',
			'game_num' => 'required',
		]);
		Tools::ensureFalse($validator->fails(), ErrorMsg::$paramsErr);
		//更新game表
		//状态置为1
		$gameOrm = Game::where("openid", $request->openid)->where("game_num", $request->game_num)->where("game_status", NormalParams::gameStatusDefault)->first();
		Tools::ensureNotEmpty($gameOrm, ErrorMsg::$gameEmpty);
		$gameOrm->hu_people_list = isset($request->hu_people_list) ? $request->hu_people_list : '';
		$gameOrm->hu_status = empty($gameOrm->hu_people_list) ? NormalParams::huStatusNot : NormalParams::huStatusYes;
		$gameOrm->hu_times = $request->hu_times;
		$gameOrm->yu_times = $request->yu_times;
		$gameOrm->yu_people_list = isset($request->yu_people_list) ? $request->yu_people_list : '';
		$gameOrm->yu_status = empty($gameOrm->yu_people_list) ? NormalParams::yuStatusNot : NormalParams::yuStatusYes;
		$gameOrm->game_status = NormalParams::gameStatusAlready;
		$upRes = $gameOrm->save();
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
				$game->game_status = NormalParams::gameStatusCaculateOver;
				$saveRes = $game->save();//循环保存
				Tools::ensureNotFalse($saveRes, ErrorMsg::$gameReadyCalcuErr);//如果某个失败了。跳错。
			}
		}
		Tools::ensureNotFalse($upRes, ErrorMsg::$gameReadyCalcuErr);
		Tools::outPut(ErrorMsg::$succ);
	}

	public function cancelCalculateMoney(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'openid' => 'required|max:64',
			'game_num' => 'required',
		]);
		Tools::ensureFalse($validator->fails(), ErrorMsg::$paramsErr);
		$gameInfo = Game::where("openid", $request->openid)->where("game_num",$request->game_num)->first();
		//判断游戏状态是否有误
		Tools::ensureNotFalse($gameInfo->game_status==NormalParams::gameStatusAlready, ErrorMsg::$gameCancelForCalculateErr);
		$gameInfo->game_status = NormalParams::gameStatusDefault;
		$res = $gameInfo->save();
		Tools::ensureNotFalse($res, ErrorMsg::$netErr);
		Tools::outPut(ErrorMsg::$succ);
	}

	public function readyForNextGame(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'openid' => 'required|max:64',
			'game_num' => 'required',
		]);
		Tools::ensureFalse($validator->fails(), ErrorMsg::$paramsErr);
		$gameInfo = Game::where("openid", $request->openid)->where("game_num",$request->game_num)->first();
		//判断游戏状态是否有误
		Tools::ensureNotFalse($gameInfo->game_status==NormalParams::gameStatusCaculateOver, ErrorMsg::$gameReadyForNextErr);
		$gameInfo->game_status = NormalParams::gameStatusCanNext;
		$res = $gameInfo->save();
		Tools::ensureNotFalse($res, ErrorMsg::$netErr);
		//查看是否全部人都已经准备
		$gameList = Game::where("game_num",$request->game_num)->where("game_status", NormalParams::gameStatusCanNext)->get();
		$succ = ErrorMsg::$succ;
		if(count($gameList->toArray()) == 4){ //4个人都准备好了，那么给他们新开一局
			foreach ($gameList as $gameOne) {
				$newGameOne = new Game();
				$newGameOne->openid = $gameOne->openid;
				$newGameOne->room_num = $gameOne->room_num;
				$newGameOne->game_num = $gameOne->game_num+1;
				$res = $newGameOne->save();
				//请联系管理员
				Tools::ensureNotFalse($res, ErrorMsg::$callMe);
			}
			$succ["gameInfo"] = Game::where("openid", $request->openid)->where("game_num",$request->game_num + 1)->first();
		}
		Tools::outPut($succ);
	}

	public function cancelForNextGame(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'openid' => 'required|max:64',
			'game_num' => 'required',
		]);
		Tools::ensureFalse($validator->fails(), ErrorMsg::$paramsErr);
		$gameInfo = Game::where("openid", $request->openid)->where("game_num",$request->game_num)->first();
		//判断游戏状态是否有误
		Tools::ensureNotFalse($gameInfo->game_status==NormalParams::gameStatusCanNext, ErrorMsg::$gameReadyForNextErr);
		$gameInfo->game_status = NormalParams::gameStatusCaculateOver;
		$res = $gameInfo->save();
		Tools::ensureNotFalse($res, ErrorMsg::$netErr);
		Tools::outPut(ErrorMsg::$succ);
	}
}