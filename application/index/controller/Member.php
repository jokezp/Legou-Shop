<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\User;
use think\Request;
use think\Validate;
use think\Cookie;
use think\Session;

class Member extends Controller{

	protected $err_msg = 1;

	public function yzuser(){

		if(Request::instance()->isGet()){

			$un = Request::instance()->param('un');
			
			$user = User::all();
			$username = [];
			foreach ($user as $v) {
				$username[] = $v->username;
			}

			echo in_array($un, $username)||mb_strlen($un)<2||mb_strlen($un)>20?1:0;
		}
	}

	public function userReg(){

		if(Request::instance()->isPost()){

			$validate = new Validate([			
				['username','require|length:2,20|chsDash|unique:user','账号不能为空|账号长度在2-20之间|账号只能由汉字、字母、数字、下划线和破折号组成|账号已存在'],
				['email','require|email','邮箱不能为空|邮箱格式错误'],
				['pwd','require|min:6|confirm','密码不能为空|密码长度不能小于6|两次密码输入不一致'],
				['verify','require|captcha','验证码不能为空|验证码错误'],
				['accept','require','请同意《最终用户协议》'],
			]);

			if(!$validate->check($_POST)){			
				
				$this->err_msg = $validate->getError();
			} else {

				$user = new User;
				$user->username = $_POST['username'];
				$user->email = $_POST['email'];
				$user->salt = $user->randStr(8);
				$user->password = md5($_POST['pwd'].$user->salt);
				$user->state = 1;//0:冻结，1：正常

				$res = $user->save();
				if(!$res){

					$this->err_msg = '注册失败，请稍后重试！';
				}else {	

					$this->err_msg = '2';//注册成功
					$_POST = [];
				}

			}
		}

		$this->assign('odata',$_POST);
		$this->assign('msg',$this->err_msg);

		return $this->fetch();
	}

	public function userLogin(){

		$old_data = '';
		if(Request::instance()->isPost()){

			$user = new User;
			$res = $user->where('username',$_POST['user'])->find();
			if(!$res){

				$this->err_msg = '用户名不存在！';
			}elseif($res['state'] == 0){

				$this->err_msg = '该账号已冻结！';
			}else {

				if($res['password'] == md5($_POST['password'].$res['salt'])){

					$res->uplogin_time = time();
					if($res->save()){
						//Cookie设置
						Cookie::init(['prefix'=>'legou_','expire'=>0,'path'=>'/']);
						Cookie::set('user',$_POST['user']);
						Cookie::set('key',md5($_POST['user'].'legouweb'));

						// Session设置
						if(isset($_POST['re_me']) && $_POST['re_me']=='on'){
							Session::set('user', $_POST['user']);
							Session::set('password', $_POST['password']);
						}else {
							Session::clear('think');
						}

						$this->redirect('index/index/index');
					}else {
						$old_data = $_POST['user'];
						$this->err_msg = '登录失败，请稍后重试！';
					}
				}else {

					$old_data = $_POST['user'];
					$this->err_msg = '用户名或密码错误！';
				}
			}
		}

		$this->assign('odata',$old_data);
		$this->assign('msg',$this->err_msg);

		return $this->fetch();
	}

	public function userLogout(){

		Cookie::clear('legou_');
		$this->redirect('index/member/userlogin');
	}
}