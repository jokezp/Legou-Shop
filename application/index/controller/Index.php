<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Cats;
use app\index\model\Goods;
use app\index\model\Comment;
use think\Request;


class Index extends Controller
{

    public function search(){
        dump($_POST);
    }

    public function comment(){
        
        $comment = new Comment;
        $comment->goods_id = $_POST['goods_id'];
        $comment->username = $_POST['username'];
        $comment->content = htmlspecialchars($_POST['content']);
        if(isset($_POST['wuming'])){
            $comment->nm = 1;//匿名
        }else {
            $comment->nm = 0;
        }
        $comment->com_time = time();
        $res = $comment->save();
        if(!$res){
            $this->error('评论失败');
        }

        $this->redirect('index/index/getgoodsdetail',['goods_id'=>$_POST['goods_id']]);
    }

    public function index()
    {   
    	$goodsmodel = new Goods;

    	$cats = getTree();

    	$goods_hot = $goodsmodel->where('is_hot',1)->limit(5)->order('click_count','desc')->select();
    	$goods_new = $goodsmodel->where('is_new',1)->limit(5)->order('add_time','desc')->select();
    	$goods_eday = $goodsmodel->limit(date('w'),5)->select();

        $shopcar_goodsnum = getShopcarNum();
        
    	$this->assign('cats',$cats);
    	$this->assign('hot',$goods_hot);
    	$this->assign('new',$goods_new);
    	$this->assign('eday',$goods_eday);
        $this->assign('scnum', $shopcar_goodsnum);

        return $this->fetch();
    }

    public function getGoodsAll(){

    	if(Request::instance()->isGet()) {

    		$cat_id = Request::instance()->param('cat_id');

    		$goodsmodel = new Goods;

    		$cat = Cats::get($cat_id);
    		$catsAll = Cats::all();

    		// 栏目下（包括栏目下的其他栏目）的所有商品
	    	$goods = $goodsmodel->where('cat_id',$cat_id)->select();	
	    	foreach ($catsAll as $v) {
				if($v['parent_id'] == $cat_id){

					$goods = array_merge($goods, $goodsmodel->where('cat_id',$v['cat_id'])->select());
				}
			}

            if(Request::instance()->param('pri') == 'asc'){
                array_multisort(array_column($goods,'shop_price'),SORT_ASC,$goods);
            }else if(Request::instance()->param('pri') == 'desc'){
                array_multisort(array_column($goods,'shop_price'),SORT_DESC,$goods);
            }

			$goods = getPage(9, $goods);
			$page = showPage();

    		// 面包屑导航
    		$fm = array();

    		while ($cat_id > 0) {			
    			foreach($catsAll as $c){
    				if($c['cat_id'] == $cat_id){
    					$fm[] = $c;
    					$cat_id = $c['parent_id'];
    					break;
    				}
    			}
    		}

    		$fm = array_reverse($fm);

    	}

    	$cats = getTree();

    	$brand = [
    		['logo'=>'taobao','name'=>'淘宝'],
    		['logo'=>'huawei','name'=>'华为'],
    		['logo'=>'meizu','name'=>'魅族'],
    		['logo'=>'oppo','name'=>'OPPO'],
    		['logo'=>'apple','name'=>'苹果'],
    		['logo'=>'xiaomi','name'=>'小米'],
    		['logo'=>'vivo','name'=>'vivo'],
    		['logo'=>'sanxing','name'=>'三星'],
    		['logo'=>'nokia','name'=>'诺基亚'],
    		['logo'=>'yidong','name'=>'移动'],
    		['logo'=>'dianxin','name'=>'电信'],
    		['logo'=>'liantong','name'=>'联通'],
    	];

        $shopcar_goodsnum = getShopcarNum();

    	$this->assign('cats' , $cats);
    	$this->assign('cat' , $cat);
    	$this->assign('goods' , $goods);
    	$this->assign('fm' , $fm);
    	$this->assign('page', $page);
    	$this->assign('brand' ,$brand);
        $this->assign('scnum', $shopcar_goodsnum);

    	return $this->fetch();
    }


    public function getGoodsDetail(){

    	if(Request::instance()->isGet()){

    		$good_id = Request::instance()->param('goods_id');

    		$good = Goods::get($good_id);

            $goodsmodel = new Goods;
            $goods = $goodsmodel->where('cat_id', $good['cat_id'])
            ->where('goods_id','neq',$good_id)->limit(0,3)->select();

            //评论
            $commentmodel = new Comment;
            $comment = $commentmodel->where('goods_id',$good_id)->order('com_id desc')->select();

    		// 面包屑导航
    		$fm = array();
    		$cat_id = $good['cat_id'];
    		$catsAll = Cats::all();

    		while ($cat_id > 0) {			
    			foreach($catsAll as $c){
    				if($c['cat_id'] == $cat_id){
    					$fm[] = $c;
    					$cat_id = $c['parent_id'];
    					break;
    				}
    			}
    		}

    		$fm = array_reverse($fm);
    	}

        $shopcar_goodsnum = getShopcarNum();

    	$this->assign('good', $good);
        $this->assign('rel_goods', $goods);
    	$this->assign('fm', $fm);
        $this->assign('scnum', $shopcar_goodsnum);

        $this->assign('com',$comment);

    	return $this->fetch();
    }
}
