<?php
namespace app\admin\controller;

use think\Controller;
use app\admin\model\Goods;
use app\admin\model\Cats;
use think\Request;
use think\Paginator;

class Good extends Controller
{

	public function goodAdd(){

		if(Request::instance()->isPost()){

			$file_info = request()->file('goods_img');

			if($file_info){

				$file = $file_info->validate(['size'=>200000,'ext'=>'jpg,jpeg,png,gif'])->move(ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.'goods_img');

				if($file) {

					$goods_path = '/static/images/goods_img/'.date('Ymd',time()).'/'.$file->getFilename();
					$thumb_path = '/static/images/goods_img/'.date('Ymd',time()).'/tb_'.$file->getFilename();
					$detail_path = '/static/images/goods_img/'.date('Ymd',time()).'/de_'.$file->getFilename();

					$image = \think\Image::open($file_info);
					$image->thumb(400,400)->save('.'.$detail_path);
					$image->thumb(100,100)->save('.'.$thumb_path);

					$_POST['goods_img'] = $goods_path;
					$_POST['thumb_img'] = $thumb_path;
					$_POST['detail_img'] = $detail_path;

				}else {

					return $this->error($file_info->getError());
				}
			}

			if(!isset($_POST['is_new'])){

				$_POST['is_new'] = 0;
			}

			if(!isset($_POST['is_hot'])){

				$_POST['is_hot'] = 0;
			}

			$good = new Goods($_POST);
			$res = $good->allowField(true)->save();

			if($res) {

				$this->redirect('goodlist');
			} else {

				$this->error('添加商品出错，请稍后重试！');
			}

		}

		$list = getTree();

		$this->assign('list',$list);

		return $this->fetch();
	}

	public function goodList(){

		$goodsmodel = new Goods;

    	$goods = $goodsmodel->alias('g')
    	->join('cats c','g.cat_id = c.cat_id')
    	->order('goods_id')
    	->select();

		$goods = getPage(8, $goods);
		$page = showPage();

		$this->assign('goods',$goods);
		$this->assign('page', $page);

		return $this->fetch();
	}


	public function goodEdit(){

		$id = Request::instance()->param('goods_id');

		if(Request::instance()->isPost()) {

			$file_info = request()->file('goods_img');
			if($file_info) {

				$file = $file_info->validate(['size'=>200000,'ext'=>'jpg,jpeg,png,gif'])->move(ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.'goods_img');
				
				if($file) {

					$goods_path = '/static/images/goods_img/'.date('Ymd',time()).'/'.$file->getFilename();
					$thumb_path = '/static/images/goods_img/'.date('Ymd',time()).'/tb_'.$file->getFilename();
					$detail_path = '/static/images/goods_img/'.date('Ymd',time()).'/de_'.$file->getFilename();

					$image = \think\Image::open($file_info);
					$image->thumb(400,400)->save('.'.$detail_path);
					$image->thumb(100,100)->save('.'.$thumb_path);

					$_POST['goods_img'] = $goods_path;
					$_POST['thumb_img'] = $thumb_path;
					$_POST['detail_img'] = $detail_path;

					$good_img = Goods::get($id);
					if(is_file('.'.$good_img->goods_img)) {

						unlink('.'.$good_img->goods_img);
					}

					if(is_file('.'.$good_img->thumb_img)) {

						unlink('.'.$good_img->thumb_img);
					}
					
					if(is_file('.'.$good_img->detail_img)) {

						unlink('.'.$good_img->detail_img);
					}

				}else {

					return $this->error($file_info->getError());
				}

			}

			if(!isset($_POST['is_new'])){

				$_POST['is_new'] = 0;
			}

			if(!isset($_POST['is_hot'])){

				$_POST['is_hot'] = 0;
			}

			$good = new Goods;
			$res = $good->allowField(true)->save($_POST , ['goods_id'=>$id]);
			// $res = $good->where('goods_id',$id)->update($_POST);

			if($res){

				$this->redirect('goodlist');
			}else {

				$this->error('编辑商品出错，请稍后重试！');
			}

		}

		$list = getTree();
		$old_data = Goods::get($id);

		$this->assign('list',$list);
		$this->assign('old',$old_data);

		return $this->fetch();
	}


	public function goodDel(){

		$id = Request::instance()->param('goods_id');

		$res = Goods::destroy($id);

		if($res) {

			$this->redirect('goodlist');
		} else {

			$this->error('删除商品出错，请稍后重试！');
		}
	}
}