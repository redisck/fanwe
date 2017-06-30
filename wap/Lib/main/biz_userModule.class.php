<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_userModule extends MainBaseModule
{
	public function login()
	{
		global_run();		
		init_app_page();		
		
		if($GLOBALS['account_info']){ //用户已经登录
		    app_redirect(wap_url("biz","dealv#index"));
		}
		
		$data['page_title'] = "商户登入";
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("biz_user_login.html");
	}
	
	public function dologin(){
	    global_run();		

		$param['account_name'] = strim($_REQUEST['account_name']);
		$param['account_password'] = strim($_REQUEST['account_password']);

		//获取品牌
		$data = request_api("biz_user","dologin",$param);
        if ($data['status']){
            //写入COOKIE
//             es_cookie::set("biz_uname",$data['account_info']['account_name'],604800);
//             es_cookie::set("biz_upwd",$data['account_info']['account_password'],604800);
            $data['jump'] = wap_url("biz","dealv#index");
        }
        ajax_return($data);
	   
	}
	
	public function loginout(){
	    $data = request_api("biz_user","loginout");
		
		es_cookie::delete("biz_uname");
		es_cookie::delete("biz_upwd");
		app_redirect(wap_url("biz","user#login"));
	}
	
	
	
}
?>