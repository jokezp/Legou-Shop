<?php
namespace app\index\model;

use think\Model;

class User extends Model{

	protected $autoWriteTimestamp = true;
	protected $createTime = 'userreg_time';
	protected $updateTime = false;

	public function randStr($length){

		$str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ.+-*/[]{}<>()=';
		return substr(str_shuffle($str), 0, $length);
	}
}