<?php
namespace app\index\model;

use think\Model;

class Ordinfo extends Model{
	
	protected $autoWriteTimestamp = true;
	protected $createTime = 'ordtime';
	protected $updateTime = false;
}