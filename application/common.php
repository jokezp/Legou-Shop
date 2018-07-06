<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use app\admin\model\Cats;
use app\index\model\Shopcar;
use think\Cookie;

global $count_page;//总页数

/**
 * 栏目列表数据呈树形输出
 *
 */

function getTree($lv = 0, $num = 0){

	$t = array();
	$list = Cats::all();

	foreach ($list as $k => $v) {
		
		if($v['parent_id'] == $lv){

			$v['num'] = $num;
			$t[] = $v;

			$t = array_merge($t , getTree($v['cat_id'], $num+1));
		}
	}
	return $t;
}

/** 
 * 数组分页函数  核心函数  array_slice 
 * $count   每页多少条数据 
 * $page    当前第几页 
 * $array   查询出来的所有数组 
 */   
function getPage($count , $data) {

	$page = isset($_GET['page']) ? $_GET['page'] : 1;

	$start = ($page-1) * $count;
	$GLOBALS['count_page'] = ceil(count($data)/$count);

	$data = array_slice($data, $start, $count, true);
	return $data;
}

/** 
 * 分页及显示函数 
 */  
function showPage(){  

     $page = isset($_GET['page']) ? $_GET['page'] : 1;

     if($page > 1){  
        $up_page=$page-1;  	  
     }else{  
        $up_page=1;  
     }  
  
     if($page < $GLOBALS['count_page']){  
        $next_page=$page+1;  	  
     }else{  
        $next_page=$GLOBALS['count_page'];
     }  
         
    $str = '';
    $str .= "<li><a href='?page={$up_page}'> &laquo;  </a></li>"; 
    for($i=1;$i<=$GLOBALS['count_page'];$i++){
		$str .= "<li id='num_$i'><a href='?page={$i}'> $i </a></li>";
    } 
    $str .= "<li><a href='?page={$next_page}'>&raquo;  </a></li>";  

    return $str;  
}  

//COOIE加密验证
function check(){

	return Cookie::get('legou_key') == md5(Cookie::get('legou_user').'legouweb');
}

//获取购物车物品数量
function getShopcarNum(){

	$shopcar = new Shopcar;
    $username = Cookie::get('user','legou_');
    $shopcar_goodsnum = $shopcar->where('username',$username)->count();
    return $shopcar_goodsnum;

}


function zhifuApi($username , $amount){
    $codepay_id="43789";//这里改成码支付ID
    $codepay_key="gHa0om5bb2tHatFvMxX8dq0VY6To1hEE"; //这是您的通讯密钥

    $data = array(
        "id" => $codepay_id,//你的码支付ID
        "pay_id" => $username, //唯一标识 可以是用户ID,用户名,session_id(),订单ID,ip 付款后返回
        "type" => 1,//1支付宝支付 3微信支付 2QQ钱包
        "price" => $amount,//金额100元
        "param" => "",//自定义参数
        "notify_url"=>"",//通知地址
        "return_url"=>"http://codepay.fateqq.com/",//跳转地址
    ); //构造需要传递的参数

    ksort($data); //重新排序$data数组
    reset($data); //内部指针指向数组中的第一个元素

    $sign = ''; //初始化需要签名的字符为空
    $urls = ''; //初始化URL参数为空

    foreach ($data AS $key => $val) { //遍历需要传递的参数
        if ($val == ''||$key == 'sign') continue; //跳过这些不参数签名
        if ($sign != '') { //后面追加&拼接URL
            $sign .= "&";
            $urls .= "&";
        }
        $sign .= "$key=$val"; //拼接为url参数形式
        $urls .= "$key=" . urlencode($val); //拼接为url参数形式并URL编码参数值

    }
    $query = $urls . '&sign=' . md5($sign .$codepay_key); //创建订单所需的参数
    $url = "http://api2.fateqq.com:52888/creat_order/?{$query}"; //支付页面    

    return $url;
}