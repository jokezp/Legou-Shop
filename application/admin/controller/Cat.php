<?php
namespace app\admin\controller;

use think\Controller;
use app\admin\model\Cats;
use think\Request;

class Cat extends Controller
{

	public function catAdd(){

		if(Request::instance()->isPost()) {

			$cat = new Cats;
			$cat->cat_name = $_POST['cat_name'];
			$cat->intro = $_POST['intro'];
			$cat->parent_id = $_POST['parent_id'];
			$res = $cat->save();

			if($res){

				$this->redirect('catlist');
			}else {

				$this->error('添加栏目出错，请稍后重试！');
			}
		}

		$list = getTree();

		$this->assign('list',$list);

		return $this->fetch();
	}

	public function catList(){

		$list = getTree();
		$list = getPage(8, $list);
		$page = showPage();

		$this->assign('list',$list);
		$this->assign('page',$page);

		return $this->fetch();
		
	}

	public function catEdit(){
		
		$id = Request::instance()->param('cat_id');

		if(Request::instance()->isPost()) {

			$cat = Cats::get($id);
			$cat->cat_name  = $_POST['cat_name'];
			$cat->intro     = $_POST['intro'];
			$cat->parent_id = $_POST['parent_id'];
			$res = $cat->save();

			if($res){

				$this->redirect('catlist');
			}else {

				$this->error('编辑栏目出错，请稍后重试！');
			}
		}
		
		$list = getTree();
		$old_data = Cats::get($id);

		$this->assign('list',$list);
		$this->assign('old', $old_data);

		return $this->fetch();
	}


	public function catDel(){

		$id = Request::instance()->param('cat_id');

		$res = Cats::destroy($id);

		if($res){

			$this->redirect('catlist');
		}else {

			$this->error('删除栏目出错，请稍后重试！');
		}
	}

}