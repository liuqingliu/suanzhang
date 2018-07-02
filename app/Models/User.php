<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

	protected $table = 'users';

	protected $fillable = [
		'nickName', 'email', 'password', 'open_id', 'gender', 'country', 'province', 'city', 'ip','openid','avatarUrl'
	];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at',
    ];

	public function game()
	{
		return $this->hasMany(Game::class, 'openid', 'openid');
		// 第一个参数为关联的模型名字，第二个参数为外键，第三个参数为主键
	}
}
