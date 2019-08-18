<?php

namespace app\admin\controller;
use think\facade\Config;
use think\facade\Request;
use app\admin\model\AdminLog as LogModel;
use think\facade\View;
class AdminLog extends Base{

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * @return array|string
     * @throws \think\db\exception\DbException
     */
    public function index(){

        if(Request::isPost()){
            $keys=Request::post('keys','','trim');
            $page = Request::post('page')?Request::post('page'):1;
            $list = LogModel::where('status',1)
               ->where('username|log_title','like','%'.$keys.'%')
               ->order('id desc')
               ->paginate(array('list_rows'=> $this->pageSize,'page'=>$page))
               ->toArray();
            if(!empty($list['data'])){
                foreach ($list['data'] as $k => $v) {
                    $useragent = explode('(', $v['log_agent']);
                    $list['data'][$k]['log_agent'] = $useragent[0];
                }
            }
            $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list['data'],'count'=>$list['total']];
            return $result;
        }

        return View::fetch();
    }

    /**
     * @throws \Exception
     * 删除日志
     */
    public function delete(){

        if(LogModel::destroy(Request::post('id'))){
             $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }

    }

}
