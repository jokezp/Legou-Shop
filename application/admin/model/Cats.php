<?php
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Cats extends Model
{
	use SoftDelete;
	protected $deleteTime = 'del_time';
	
}