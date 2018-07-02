<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/6/14
 * Time: 15:32
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Room extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'rooms';

    protected $fillable = [
        'name', 'price', 'status',
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
    public function game()
    {
        return $this->belongsTo(Game::class, 'id','room_num');
    }
}
