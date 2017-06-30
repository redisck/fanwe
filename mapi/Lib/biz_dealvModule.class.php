<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_dealvModule extends MainBaseModule
{

    /**
     * 	 团购券验证接口
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
        $root['page_title'] = "团购券验证";
	    
	    /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
  
	    //返回商户权限
	    if(!check_module_auth("dealv")){
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
     * 	 团购券提交验证接口
     *
     * 	 输入:
     *  location_id:string 门店ID
     *  coupon_pwd:string 
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
            [coupon_pwd] => 62376366    [string] 验证密码
            [count] => 4 [int] 剩余有效的张数
        )
    
     */
    public function check_coupon(){
        /*初始化*/
        $root = array();
        require_once  APP_ROOT_PATH."system/model/biz_verify.php";
        $s_account_info = $GLOBALS['account_info'];
        
        /*获取参数*/
        $location_id = intval($GLOBALS['request']['location_id']);
        $pwd = strim($GLOBALS['request']['coupon_pwd']);
        
        /*业务逻辑*/
        $root['biz_user_status'] = $s_account_info?1:0;
        if (empty($s_account_info)){
            output($root,0,"商户未登录");
        }
        //返回商户权限
        if(!check_module_auth("dealv")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作验证权限");
        }else{
            $root['is_auth'] = 1;
        }

        $result = biz_super_check_coupon($s_account_info,$pwd,$location_id);
        
        if ($result['status']==1){
            $data['location_id'] = $result['location_id'];
            $data['coupon_pwd'] = $result['coupon_pwd'];
            $data['count'] = $result['count'];
            $root['data'] = $data;
        }
        if($result['sub_msg'])
            $result['sub_msg'].="\n 一共：".$result['count']."张有效";
        $info = $result['sub_msg']?$result['sub_msg']:$result['msg'];
        output($root,$result['status'],$info);
        
    }
    
    
    /**
     * 	 团购券提交接口
     *
     * 	 输入:
     *  location_id:string 门店ID
     *  coupon_pwd:string 验证码
     *  coupon_use_count:int 验证的张数
     *  
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *  以下仅在biz_user_status为1时会返回
     *
     *  如果status 为 1 的情况下
     *  [data] => Array
     (
        [location_id] => 34 ['int'] 门店编号
        [coupon_pwd] => 62376366    [string] 验证密码
        [coupon_use_count]=>11  [int] 验证的张数
     )
    
     */
    public function use_coupon()
    {
        /*初始化*/
        $root = array();
        require_once  APP_ROOT_PATH."system/model/biz_verify.php";
        $s_account_info = $GLOBALS['account_info'];
        
        /*获取参数*/
        $location_id = intval($GLOBALS['request']['location_id']);
        $pwd = strim($GLOBALS['request']['coupon_pwd']);
        $coupon_use_count = intval($GLOBALS['request']['coupon_use_count']);
        
        /*业务逻辑*/
        $root['biz_user_status'] = $s_account_info?1:0;
        if (empty($s_account_info)){
            output($root,0,"商户未登录");
        }
        //返回商户权限
        if(!check_module_auth("dealv")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作验证权限");
        }else{
            $root['is_auth'] = 1;
        }

        $result = biz_super_use_coupon($s_account_info,$location_id,$pwd,$coupon_use_count);
        if ($result['status']==1){
            $data['location_id'] = $result['location_id'];
            $data['coupon_pwd'] = $result['coupon_pwd'];
            $data['coupon_use_count'] = $result['coupon_use_count'];
            $root['data'] = $data;
        }
        $info = $result['msg'];
        output($root,$result['status'],$info);
    }
}
?>

