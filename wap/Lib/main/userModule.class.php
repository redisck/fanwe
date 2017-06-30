<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class userModule extends MainBaseModule
{

	/**
	 * 
	 * 登录页跳转进入条件
	 * 1.未登录状态 login_status 0
	 * 2.需要验证安全登录状态的临时登录 login_status:2
	 * 3.下单时账户无手机号（进入绑定）
	 * 4.下单时有账户余额并且为临时登录状态
	 * 
	 * 登录页的展示：
	 * 1.无登录时，显示账号登录与手机短信登录（无需验证唯一）
	 * 2.临时登录时，显示验证登录，账号名锁死，如有手机号，手机号锁死
	 * 3.会员为临时会员，并且无手机号时，显示绑定页
	 */
	public function login()
	{
		global_run();		
		init_app_page();
		
		
		$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		$data['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$data['page_title'].="登录";
		$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());

		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("user_login.html");
	}
	
	
	public function dologin()
	{
		global_run();
		$user_name = strim($_REQUEST['user_key']);
		$password = strim($_REQUEST['user_pwd']);
		
		$data = request_api("user","dologin",array("user_key"=>$user_name,"user_pwd"=>$password));
		if($data['status'])
		{
			$data['jump'] = get_gopreview();
			//保存cookie
			es_cookie::set("user_name",$data['user_name'],3600*24*30);
			es_cookie::set("user_pwd",md5($data['user_pwd']."_EASE_COOKIE"),3600*24*30);
		}
		ajax_return($data);
	}
	
	
	public function dophlogin()
	{
		global_run();
		$mobile = strim($_REQUEST['mobile']);
		$sms_verify = strim($_REQUEST['sms_verify']);
	
		$data = request_api("user","dophlogin",array("mobile"=>$mobile,"sms_verify"=>$sms_verify));
		if($data['status'])
		{
			$data['jump'] = get_gopreview();
			
			//保存cookie
			es_cookie::set("fanwe_mobile", $mobile,3600*24*7);
			es_cookie::set("user_name",$data['user_name'],3600*24*30);
			es_cookie::set("user_pwd",md5($data['user_pwd']."_EASE_COOKIE"),3600*24*30);
		}
		ajax_return($data);
	}
	
	public function dophbind()
	{
		global_run();
		$mobile = strim($_REQUEST['mobile']);
		$sms_verify = strim($_REQUEST['sms_verify']);
	
		$data = request_api("user","dophbind",array("mobile"=>$mobile,"sms_verify"=>$sms_verify));
		if($data['status'])
		{
			$data['jump'] = get_gopreview();
				
			//保存cookie
			es_cookie::set("fanwe_mobile", $mobile,3600*24*7);
			es_cookie::set("user_name",$data['user_name'],3600*24*30);
			es_cookie::set("user_pwd",md5($data['user_pwd']."_EASE_COOKIE"),3600*24*30);
		}
		ajax_return($data);
	}
	
	
	public function loginout()
	{
		$data = request_api("user","loginout");		
		es_cookie::delete("user_name");
		es_cookie::delete("user_pwd");
		es_session::delete("wx_info");	
		es_cookie::set("deny_weixin_".intval($GLOBALS['supplier_info']['id']), 1); //人工退出禁止微信登录
		$url = get_gopreview();
		$url = preg_replace("/[&|?]redirect=[^&]*/i", "", $url);
		$url = preg_replace("/[&|?]weixin_login=[^&]*/i", "", $url);

		app_redirect($url);
	}
	
	
	public function getpassword()
	{
		global_run();
		init_app_page();
		
		$data['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		
		$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
		
		if($GLOBALS['user_info']&&$GLOBALS['user_info']['mobile']=='')
		{
			$data['page_title'].="绑定手机号";
			$GLOBALS['tmpl']->assign("data",$data);
			$user_info = $GLOBALS['user_info'];
			$user_info['is_tmp'] = 1;
			$GLOBALS['tmpl']->assign("user_info",$user_info);
			$GLOBALS['tmpl']->display("user_login.html");
		}
		else
		{		
			$data['page_title'].="重置密码";
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->display("user_getpassword.html");
		}
	}
	
	public function phmodifypassword()
	{
		global_run();
		$mobile = strim($_REQUEST['mobile']);
		$sms_verify = strim($_REQUEST['sms_verify']);
		$new_pwd = strim($_REQUEST['new_pwd']);
		
		$data = request_api("user","phmodifypassword",array("mobile"=>$mobile,"sms_verify"=>$sms_verify,"new_pwd"=>$new_pwd));
		if($data['status'])
		{
			$data['jump'] = get_gopreview();
				
			//保存cookie
			es_cookie::set("fanwe_mobile", $mobile,3600*24*7);
			es_cookie::set("user_name",$data['user_name'],3600*24*30);
			es_cookie::set("user_pwd",md5($data['user_pwd']."_EASE_COOKIE"),3600*24*30);
		}
		ajax_return($data);
	}

	
	public function register()
	{
		global_run();
		init_app_page();
	
		$data['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$data['page_title'].="注册";
	
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("user_register.html");
	}
	
	public function doregister()
	{
		global_run();
		$user_name = strim($_REQUEST['user_name']);
		$email = strim($_REQUEST['email']);
		$user_pwd = strim($_REQUEST['user_pwd']);
		
		$data = request_api("user","doregister",array("user_name"=>$user_name,"user_email"=>$email,"user_pwd"=>$user_pwd));
		if($data['status'])
		{
			$data['jump'] = get_gopreview();
		
			//保存cookie
			es_cookie::set("user_name",$data['user_name'],3600*24*30);
			es_cookie::set("user_pwd",md5($data['user_pwd']."_EASE_COOKIE"),3600*24*30);
		}
		ajax_return($data);
	}
	
}
?>