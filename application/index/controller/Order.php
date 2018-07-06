<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Cookie;
use app\index\model\Shopcar;
use app\index\model\Ordinfo;
use app\index\model\Ordgoods;
use think\Db;

class Order extends Controller{

	public function shopcarNum(){

		return getShopcarNum();
	}

	// 加入购物车
	public function addCart(){

		if(Request::instance()->isPost()){

			$shopcar = new Shopcar;
			$data = $shopcar->where(['username'=>Cookie::get('user','legou_') , 'good_id'=>$_POST['gid']])->find();
			if($data){
				$data->good_number += $_POST['shop_number'];
				$res = $data->save();
			} else {
				$shopcar->username = Cookie::get('user','legou_');
				$shopcar->good_id = $_POST['gid'];
				$shopcar->good_number = $_POST['shop_number'];
				$res = $shopcar->save();
			}
			if(!$res){
				echo 0;
			}else {
				echo 1;
			}
		}else {
			$this->redirect('index/index/index');
		}

	}

	//立即购买
	public function upOrder(){

		if(Request::instance()->isPost()){

			$id = $_POST['gid'];
			$shop_number = $_POST['shop_number'];
			$good = Db::table('goods')->where('goods_id',$id)->find();

			$shopcar_goodsnum = getShopcarNum();
			
		}else {
			$this->redirect('index/index/index');
		}

		$this->assign('good', $good);
		$this->assign('num', $shop_number);
		$this->assign('scnum', $shopcar_goodsnum);
		return $this->fetch();
	}

	//结算购买
	public function jsOrder(){

		if(!check()){
			//未登录
			$this->redirect('index/member/userlogin');
		}else {
			//已登录
			if(Request::instance()->isPost()){

				$goods = array();
				foreach ($_POST as $k => $v) {

					$goods[] = Db::table('goods')
					->alias('g')
					->join('shopcar s','g.goods_id = s.good_id')
					->where('s.id',$k)
					->find();
				}

				$shopcar_goodsnum = getShopcarNum();

			}else {
				$this->redirect('index/index/index');
			}

			$this->assign('goods', $goods);
			$this->assign('zongji', 0);
			$this->assign('jiesheng', 0);
			$this->assign('scnum', $shopcar_goodsnum);
			return $this->fetch();
		}
	}

	//购物车
	public function shopCart(){

		if(!check()){
			//未登录
			$this->redirect('index/member/userlogin');
		}else {
			$username = Cookie::get('user','legou_');

			$goods = Db::table('goods')
			->alias('g')
			->join('shopcar s','g.goods_id = s.good_id')
			->where('s.username',$username)
			->order('s.id','desc')
			->select();		

			$shopcar_goodsnum = getShopcarNum();

		}		

		$this->assign('goods',$goods);
		$this->assign('scnum', $shopcar_goodsnum);
		$this->assign('zongjia', 0);
		return $this->fetch();
	}

	//移除购物车商品
	public function delShopCart(){

		if(!check()){
			//未登录
			$this->redirect('index/index/index');
		}else {
			if(Request::instance()->isGet()){

				$del_id = Request::instance()->param('delid');
				Shopcar::where('id', $del_id)->delete();				
			}
		}
		$this->redirect('index/order/shopcart');
	}

	//清空购物车
	public function delAll(){

		if(!check()){
			//未登录
			$this->redirect('index/index/index');
		}else {
			$username = Cookie::get('user','legou_');
			Shopcar::where('username',$username)->delete();
		}
		$this->redirect('index/order/shopcart');
	}

	public function zhifu(){

		if(!check()){
			//未登录
			$this->redirect('index/member/userlogin');
		}else {
			if(Request::instance()->isPost()){
				dump($_POST);exit;
				$ordinfo = new Ordinfo;
				$ordgoods = new Ordgoods;

				$username = Cookie::get('user','legou_');
				$amount = $_POST['money'];

				//新增订单
				$ordinfo->username = $_POST['username'];
				$ordinfo->xm = $_POST['xm'];
				$ordinfo->mobile = $_POST['mobile'];
				$ordinfo->address = $_POST['address'];
				$ordinfo->paystatus = 0;
				$ordinfo->money = $_POST['money'];
				$ordinfo->note = $_POST['note'];
				$res = $ordinfo->save();

				//新增订单中商品信息
				$auto_id = $ordinfo->ordinfo_id;
				if(!isset($_POST['key'])){
					// echo '单个商品';exit;
					$ordgoods->ordinfo_id = $auto_id;
					$ordgoods->goods_id = $_POST['goods_id'];
					$ordgoods->goods_name = $_POST['goods_name'];
					$ordgoods->shop_price = $_POST['shop_price'];
					$ordgoods->goods_num = $_POST['goods_num'];
					$goodres = $ordgoods->save();
				}else {
					// echo '多个商品';exit;
					$k = $_POST['key'];
					$list = [];
					for ($i=0; $i <= $k ; $i++) {
						$list[$i] = [
							'ordinfo_id'	=>	$auto_id,
							'goods_id'		=>	json_decode($_POST['goods_id'])[$i],
							'goods_name'	=>	json_decode($_POST['goods_name'])[$i],
							'shop_price'	=>	json_decode($_POST['shop_price'])[$i],
							'goods_num'		=>	json_decode($_POST['goods_num'])[$i],
						];
					}

					$goodres = $ordgoods->saveAll($list);
				}

				if(!$res || !$goodres){
					echo "<script> alert('提交失败，请稍后重试'); history.go(-1); </script>"; 
				}else{

					$url = zhifuApi($username , $amount);
				    $this->redirect("{$url}"); //跳转到支付页面
				}
			}else{
				$this->redirect('index/index/index');
			}
		}
		
	}
}