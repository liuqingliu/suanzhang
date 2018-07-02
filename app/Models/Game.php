<?php
	/**
	 * Created by PhpStorm.
	 * User: dell
	 * Date: 2018/6/14
	 * Time: 15:32
	 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Game extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'games';

	protected $fillable = [
		'openid', 'room_num', 'game_num', 'in_price', 'out_price', 'hu_status', 'hu_people_list', 'hu_times',
		'yu_status', 'yu_people_list', 'yu_times', 'game_status'
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'created_at', 'updated_at',
	];

	/**
	 * 获取game对应的用户
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'openid','openid');
	}

    /**
     * 获取game对应的room
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_num','id');
    }
}
