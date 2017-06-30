<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_moreModule extends MainBaseModule
{

    /**
     * 	更多页面接口
     *
     * 	 输入:
     *  无
     *
     *  输出:
     *  [biz_user_status] => 1 [int]用户登录状态 0未登录 1已登录
        [shop_tel] => 400-800-8885 [string]商城电话
        
     */
	public function index(){
	    /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "更多";
	    
        /*获取参数*/
        //客服端手机类型dev_type=android
        $dev_type = $GLOBALS['request']['dev_type'];
        $version = $GLOBALS['request']['version'];
        
        
	    /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        $root['shop_tel'] = app_conf("SHOP_TEL");
        
        output($root);
    }
    
    
   
}
?>

