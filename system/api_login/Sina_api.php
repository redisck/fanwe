<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

$api_lang = array(
	'name'	=>	'新浪微博api登录接口',
	'app_key'	=>	'新浪API应用APP_KEY',
	'app_secret'	=>	'新浪API应用APP_SECRET',
	'app_url'	=>	'回调地址',
);

$config = array(
	'app_key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //新浪API应用的KEY值
	'app_secret'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //新浪API应用的密码值
	'app_url'	=>	array(
		'INPUT_TYPE'	=>	'0'
	),
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
	if(ACTION_NAME=='install')
	{
		//更新字段
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `sina_id`  varchar(255) NOT NULL",'SILENT');
		//$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `sina_app_key`  varchar(255) NOT NULL",'SILENT');
		//$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `sina_app_secret`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `sina_token`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `is_syn_sina`  tinyint(1) NOT NULL",'SILENT');
	}
    $module['class_name']    = 'Sina';

    /* 名称 */
    $module['name']    = $api_lang['name'];

	$module['config'] = $config;
	$module['is_weibo'] = 1;  //可以同步发送微博
	
	$module['lang'] = $api_lang;
    
    return $module;
}

// 新浪的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class Sina_api implements api_login {
	
	private $api;
	
	public function __construct($api)
	{
		$api['config'] = unserialize($api['config']);
		$this->api = $api;
	}
	
	public function get_api_url()
	{
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
		$o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
		es_session::start();
		//$keys = $o->getRequestToken();
		if($this->api['config']['app_url']=="")
		{
			$app_url = SITE_DOMAIN.APP_ROOT."/api_callback.php?c=Sina";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = $o->getAuthorizeURL($app_url);

		es_session::set("is_bind",0);
		
		
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['icon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}
	
	
	public function get_bind_api_url()
	{
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
		$o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
		//$keys = $o->getRequestToken();
		if($this->api['config']['app_url']=="")
		{
			$app_url = SITE_DOMAIN.APP_ROOT."/api_callback.php?c=Sina";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = $o->getAuthorizeURL($app_url);	
		es_session::set("is_bind",1);
		
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['bicon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}	
	
	/**
	 * 返回新浪绑定数组信息
	 * @return array("class","name","bicon",url);
	 */
	public function get_bind_api_url_arr()
	{
	    require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
	    
	    $o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
	    es_session::start();
	    //$keys = $o->getRequestToken();
	    if($this->api['config']['app_url']=="")
	    {
	        $app_url = get_domain().APP_ROOT."/api_callback.php?c=Sina";
	    }
	    else
	    {
	        $app_url = $this->api['config']['app_url'];
	    }
	    $aurl = $o->getAuthorizeURL($app_url);
	    es_session::set("is_bind",1);

	    $data['class'] = 'sina';
	    $data['name'] =  $this->api['name'];
	    $data['bicon'] = $this->api['bicon'];
	    $data['url'] = $aurl;
	    return $data;
	}
	
	public function callback()
	{
	    global_run();
		require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
		//$sina_keys = es_session::get("sina_keys");
		$o = new SaeTOAuthV2($this->api['config']['app_key'],$this->api['config']['app_secret']);
		if (isset($_REQUEST['code'])) {
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			if($this->api['config']['app_url']=="")
			{
				$app_url = SITE_DOMAIN.APP_ROOT."/api_callback.php?c=Sina";
			}
			else
			{
				$app_url = $this->api['config']['app_url'];
			}
			$keys['redirect_uri'] = $app_url;
			try {
				$token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
				//print_r($e);exit;
				showErr("授权失败,错误信息：".$e->getMessage());
				die();
			}
		}
		
		
		$c = new SaeTClientV2($this->api['config']['app_key'],$this->api['config']['app_secret'] ,$token['access_token'] );
		$ms  = $c->home_timeline(); // done
		$uid_get = $c->get_uid();
		$uid = $uid_get['uid'];
		$msg = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
		
		if(intval($msg['error_code'])!=0){
			showErr("授权失败,错误代码:".$msg['error_code']);
			die();
		}
		
		$msg['field'] = 'sina_id';
		$msg['sina_token'] = $token['access_token'];
 		es_session::set("api_user_info",$msg);
		
		if(!$msg['name'])
		{
		   app_redirect(url("index"));
		   exit();
		}
		
		//print_r($msg);die();
		$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where sina_id = '".$msg['id']."' and sina_id <> ''  and is_delete=0");	
		$is_bind = intval(es_session::get("is_bind"));
		if($user_data)
		{
		
				$GLOBALS['db']->query("update ".DB_PREFIX."user set sina_token = '".$token['access_token']."',login_ip = '".CLIENT_IP."',login_time= ".NOW_TIME." where id =".$user_data['id']);				
				es_session::delete("api_user_info");
						
				if($is_bind)
				{
					if(intval($user_data['id'])!=intval($GLOBALS['user_info']['id']))
					{
						showErr("该帐号已经被别的会员绑定过，请直接用帐号登录",0,url("index","uc_account"));
					}
					else
					{
						require_once APP_ROOT_PATH."system/model/user.php";
						load_user($user_data['id'],true);
						es_session::set("user_info",$user_data);
						app_redirect(url("index","uc_account"));
					}
				}
				else
				{
					require_once APP_ROOT_PATH."system/model/user.php";
					auto_do_login_user($user_data['user_name'],$user_data['user_pwd'],$from_cookie = false);
					app_redirect(url("index","index"));
				}
		}
		elseif($is_bind==1&&$GLOBALS['user_info'])
		{
			//当有用户身份且要求绑定时
			$GLOBALS['db']->query("update ".DB_PREFIX."user set sina_id= '".$msg['id']."', sina_token ='".$token['access_token']."' where id =".$GLOBALS['user_info']['id']);						
			require_once APP_ROOT_PATH."system/model/user.php";
			load_user($GLOBALS['user_info']['id'],true);
			app_redirect(url("index","uc_account"));
		}
		else{
			$user_info = $this->create_user();
			require_once APP_ROOT_PATH."system/model/user.php";
			auto_do_login_user($user_info['user_name'],$user_info['user_pwd'],$from_cookie = false);
			app_redirect(url("index","index"));
		}
		
		
	}
	
	public function get_title()
	{
		return '新浪api登录接口，需要php_curl扩展的支持(V2)';
	}
	
	public function create_user()
	{
		$s_api_user_info = es_session::get("api_user_info");
		$user_data['user_name'] = $s_api_user_info['screen_name'];
	
		$user_data['sina_id'] = $s_api_user_info['id'];
		$user_data['sina_token'] = $s_api_user_info['sina_token'];
		
	    $result = auto_create($user_data, 0);
		if($result['status']){
		    $user_info = $result['user_data'];
		}else{
		    showErr("注册失败");
		}
		es_session::delete("api_user_info");
		return $user_info;
	}
	
	
	//同步发表到新浪微博
	public function send_message($data)
	{
		static $client = NULL;
		if($client === NULL)
		{
			require_once APP_ROOT_PATH.'system/api_login/sina/saetv2.ex.class.php';
			$uid = intval($GLOBALS['user_info']['id']);
			$udata = $GLOBALS['db']->getRow("select sina_token from ".DB_PREFIX."user where id = ".$uid);
			$client = new SaeTClientV2($this->api['config']['app_key'],$this->api['config']['app_secret'],$udata['sina_token']);
		}
		try
		{
			if(empty($data['img']))
				$msg = $client->update($data['content']);
			else
				$msg = $client->upload($data['content'],$data['img']);

			if($msg['error'])
			{
				$result['status'] = false;
				$result['msg'] = "新浪微博同步失败，请偿试重新通过腾讯微博登录或得新授权。";
			}
			else
			{
				$result['status'] = true;
				$result['msg'] = "success";
			}

		}
		catch(Exception $e)
		{

		}
		return $result;
	}
	//解除API 绑定
	public function unset_api(){
	    if($GLOBALS['user_info']){
	        //解除绑定
	        $GLOBALS['db']->query("update ".DB_PREFIX."user set sina_id= '', sina_token ='',is_syn_sina=0 where id =".$GLOBALS['user_info']['id']);
	    }
	}
	/**
	 * 设置微博同步
	 * @return string
	 */
	public function set_syn_weibo(){
	    if($GLOBALS['user_info']){
	        if($GLOBALS['user_info']['sina_id']>0){
	            $set_v = $GLOBALS['user_info']['is_syn_sina']==1?0:1;
	           
	            $GLOBALS['db']->query("update ".DB_PREFIX."user set is_syn_sina=".$set_v." where id =".$GLOBALS['user_info']['id']);
	            $result['status'] = 1;
	            $result['info'] = $GLOBALS['user_info']['is_syn_sina']==1?"取消同步成功":"设置同步成功";
	        }else{
	            $result['status'] = 0;
	            $result['info'] = '还未绑定新浪微博';
	        }
	    }else{
	        $result['status'] = -1;
	        $result['info'] = '请先登录执行该后操作';
	    }
	    return $result;
	}
	
}
?>