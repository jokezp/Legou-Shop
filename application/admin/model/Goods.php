<?php
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Goods extends Model
{
	use SoftDelete;
	protected $deleteTime = 'del_time';

	protected $autoWriteTimestamp = true;
	protected $createTime = 'add_time';
	protected $updateTime = 'update_time';

}