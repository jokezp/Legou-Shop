<?php
namespace app\admin\controller;

use think\Controller;
use app\admin\model\Goods;
use app\admin\model\Cats;
use app\admin\model\User;
use app\admin\model\Ordinfo;
use think\Request;
use think\Cookie;

class Index extends Controller
{
    public function adminOut(){

        Cookie::delete('adpd');
        $this->redirect('index/index/index');
    }


    public function index()
    {
        if(Request::instance()->isPost()){
            Cookie::set('adpd', md5($_POST['admin_pwd']), 3600);
        }

        if(Cookie::get('adpd') == md5('shop6788')){

            $catsmodel = new Cats;
            $cats = $catsmodel->limit(0,7)->select();
            $lists = $catsmodel->all();

            $goodsmodel = new Goods;
            $goods = $goodsmodel->alias('g')
            ->join('cats c','g.cat_id = c.cat_id')
            ->order('add_time desc')
            ->limit(0,7)
            ->select();

            $order = Ordinfo::all();

            $usermodel = new User;
            $user = $usermodel->order('user_id desc')->select();

            //cat
            $this->assign('cats',$cats);
            $this->assign('lists',$lists);

            //good
            $this->assign('goods',$goods);

            //order
            $this->assign('order',$order);

            //user
            $this->assign('user',$user);

            return $this->fetch();   
        }else {
            $this->redirect('index/index/index');
        }
    }


    public function freeze(){

        if(Request::instance()->isGet()){

            $id = Request::instance()->param('user_id');
            $user = User::get($id);
            $user->state = 0;
            $user->save();
        }

        $this->redirect('admin/index/index');
    }

    public function recover(){

        if(Request::instance()->isGet()){

            $id = Request::instance()->param('user_id');
            $user = User::get($id);
            $user->state = 1;
            $user->save();
        }

        $this->redirect('admin/index/index');
    }

}
