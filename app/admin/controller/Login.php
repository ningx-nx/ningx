<?php
namespace app\admin\controller;
use app\admin\model\Admin;
use app\admin\model\AuthGroup;
use app\BaseController;
use think\facade\Session;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
class Login extends BaseController {
    /*
     * 登录
     */
    public function index( ){
        if (!Request::isPost()) {
            $admin= Session::get('admin');
            $admin_sign= Session::get('admin_sign') == auth_sign($admin) ? $admin['id'] : 0;
            // 签名验证
            if ($admin && $admin_sign) {
                return redirect('index/index');
            }
            $token = $this->request->buildToken(config('admin.token'), 'sha1');
            View::assign('token', $token);
            return View::fetch();

        } else {

            $username = Request::post('username', '', 'util\Filter::filterWords');
            $password = Request::post('password', '', 'util\Filter::filterWords');
            $captcha = Request::post('captcha', '', 'util\Filter::filterWords');
            $rememberMe = Request::post('rememberMe');
            // 用户信息验证
            try {
                if(!captcha_check($captcha)){
                    throw new \Exception('验证码错误');
                }
                $res = self::checkLogin($username, $password,$rememberMe);
            } catch (\Exception $e) {
                $this->error("登陆失败：{$e->getMessage()}");
            }
            $this->success('登录成功，正在进入系统...', url('@admin'));
        }
    }

    /*
     * 验证码
     *
     */
    public function captcha()
    {
        return captcha();
    }



    /**
     * 根据用户名密码，验证用户是否能成功登陆
     * @param string $user
     * @param string $pwd
     * @throws \Exception
     * @return mixed
     */
    public static function checkLogin($user, $password,$rememberMe) {

        try{
            $where['username'] = strip_tags(trim($user));
            $password = strip_tags(trim($password));
            $where['password']  = md5($password);
            $info = Admin::where($where)->find();
            if($info['status']==0){
                throw new \Exception("账号已经被禁用");
            }
            if(!$info){
                throw new \Exception("请检查用户名或者密码");
            }

            if(!$info['group_id']){
                $info['group_id'] = 1;

            }
            $rules = AuthGroup::where('id',$info['group_id'])
                ->value('rules');
            $info['rules'] = $rules  ;
            if(!$info['username']){
                $info['username'] = $info['username'];
            }
            if($rememberMe){
                Session::set('admin', $info,5*24*3600);
                Session::set('admin_sign', auth_sign($info),5*24*3600);
            }else{
                Session::set('admin', $info);
                Session::set('admin_sign', auth_sign($info));
            }

        }catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return true;
    }
}