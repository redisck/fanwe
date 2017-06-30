<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_eventvModule extends MainBaseModule
{

    /**
     * 	活动验证接口
     *
     * 	 输入:
     *  无
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *  
     *  有权限的情况下返回以下内容
        [location_list] => Array [array] 支持的门店数据
        (
            [0] => Array
                (
                    [id] => 21  [int] 门店编号
                    [name] => 桥亭活鱼小镇（万象城店） [string]门店名称
                )
        )

     */
	public function index(){
	    /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "活动报名验证";
	    
	    /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
  
	    //返回商户权限
	    if(!check_module_auth("eventv")){
	        $root['is_auth'] = 0;
	        output($root,0,"没有操作验证权限");
	    }else{
	        $root['is_auth'] = 1;
	    }
	    
	    
	    //获取支持的门店
	    $location_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $account_info['location_ids']) . ")");
        $root['location_list'] = $location_list?$location_list:array();
        
        
        output($root);
    }
    
    
    /**
     * 	活动验证提交验证接口
     *
     * 	 输入:
     *  location_id:string 门店ID
     *  event_sn:string  活动验证码
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *  如果status 为 1 的情况下
     *  [data] => Array 
        (
            [location_id] => 34 ['int'] 门店编号
            [event_sn] => 62376366    [string] 验证密码
        )
    
     */
    public function check_event(){
        /*初始化*/
        $root = array();
        require_once  APP_ROOT_PATH."system/model/biz_verify.php";
        $s_account_info = $GLOBALS['account_info'];
        
        /*获取参数*/
        $location_id = intval($GLOBALS['request']['location_id']);
        $event_sn = strim($GLOBALS['request']['event_sn']);
        
        /*业务逻辑*/
        $root['biz_user_status'] = $s_account_info?1:0;
        if (empty($s_account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("eventv")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作验证权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        $result = biz_check_event($s_account_info,$event_sn,$location_id);
        if ($result['status']==1){
            $data['location_id'] = $result['location_id'];
            $data['event_sn'] = $result['event_sn'];
            $root['data'] = $data;
        }
        
        output($root,$result['status'],$result['msg']);
        
    }
    
    
    /**
     * 	 优惠券提交接口
     *
     * 	 输入:
     *  location_id:string 门店ID
     *  youhui_sn:string
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     * 
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *  如果status 为 1 的情况下
     *  [data] => Array
     (
        [location_id] => 34 ['int'] 门店编号
        [event_sn] => 62376366    [string] 验证密码
     )
    
     */
    public function use_event()
    {
        /*初始化*/
        $root = array();
        require_once  APP_ROOT_PATH."system/model/biz_verify.php";
        $s_account_info = $GLOBALS['account_info'];
        
        /*获取参数*/
        $location_id = intval($GLOBALS['request']['location_id']);
        $event_sn = strim($GLOBALS['request']['event_sn']);
        
        /*业务逻辑*/
        $root['biz_user_status'] = $s_account_info?1:0;
        if (empty($s_account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("eventv")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作验证权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        $result = biz_use_event($s_account_info,$event_sn,$location_id);
        if ($result['status']==1){
            $data['location_id'] = $result['location_id'];
            $data['event_sn'] = $result['event_sn'];
            $root['data'] = $data;
        }

        output($root,$result['status'],$result['msg']);
    }
}
?>

