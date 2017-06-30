<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

$api_lang = array(
	'name'	=>	'腾讯微博登录插件',
	'app_key'	=>	'腾讯API应用APP_KEY',
	'app_secret'	=>	'腾讯API应用APP_SECRET',
	'app_url'	=>	'回调地址',
);

$config = array(
	'app_key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //腾讯API应用的KEY值
	'app_secret'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //腾讯API应用的密码值
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
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `tencent_id`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `t_access_token`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `t_openkey`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `t_name`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `is_syn_tencent`  tinyint(1) NOT NULL",'SILENT');
	}
    $module['class_name']    = 'Tencent';

    /* 名称 */
    $module['name']    = $api_lang['name'];

	$module['config'] = $config;
	$module['is_weibo'] = 1;  //可以同步发送微博
	
	$module['lang'] = $api_lang;
    
    return $module;
}

// 腾讯的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class Tencent_api implements api_login {
	
	private $api;
	
	public function __construct($api)
	{		
		$api['config'] = unserialize($api['config']);
		$this->api = $api;		
	}
	
	public function get_api_url()
	{
		es_session::start();
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';

		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		if($this->api['config']['app_url']=="")
		{
			$app_url = SITE_DOMAIN.APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = OAuth::getAuthorizeURL($app_url);
		es_session::set("is_bind",0);
		
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['icon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}

	
	public function get_bind_api_url()
	{
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';

		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		if($this->api['config']['app_url']=="")
		{
			$app_url = SITE_DOMAIN.APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = OAuth::getAuthorizeURL($app_url);
		es_session::set("is_bind",1);
		
		$str = "<a href='".$aurl."' title='".$this->api['name']."'><img src='".$this->api['bicon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}		
	
	/**
	 * 返回腾讯绑定数组信息
	 * @return array("class","name","bicon",url);
	 */
	public function get_bind_api_url_arr()
	{
	    require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';

		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		if($this->api['config']['app_url']=="")
		{
			$app_url = SITE_DOMAIN.APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		$aurl = OAuth::getAuthorizeURL($app_url);
		es_session::set("is_bind",1);
		
	    $data['class'] = 'tencent';
	    $data['name'] =  $this->api['name'];
	    $data['bicon'] = $this->api['bicon'];
	    $data['url'] = $aurl;
	    return $data;
	}
	public function callback()
	{
	    global_run();
		es_session::start();		
		require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';
		OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);
		
		$code = strim($_REQUEST['code']);
		$openid = strim($_REQUEST['openid']);
		$openkey = strim($_REQUEST['openkey']);
		
		if($this->api['config']['app_url']=="")
		{
			$app_url = SITE_DOMAIN.APP_ROOT."/api_callback.php?c=Tencent";
		}
		else
		{
			$app_url = $this->api['config']['app_url'];
		}
		
		$token_url = OAuth::getAccessToken($code,$app_url);
		$result = Http::request($token_url);
		$result = preg_replace('/[^\x20-\xff]*/', "", $result); //清除不可见字符
        $result = iconv("utf-8", "utf-8//ignore", $result); //UTF-8转码
        //过滤返回数据
        parse_str($result,$result_arr);
        
		$is_bind = intval(es_session::get("is_bind"));

		if(intval($result_arr['errorCode'])!=0){
		    showErr("授权失败,错误代码:".$result_arr['errorMsg']);
		    die();
		}
	    if(!$result_arr['name'])
		{
		   app_redirect(url("index"));
		   exit();
		}
		$msg['field'] = 'tencent_id';
		$msg['id'] = $openid;
		$msg['t_openid'] = $result_arr['openid'];
		$msg['t_openkey'] = $openkey;
		$msg['t_access_token'] = $result_arr['access_token'];
		$msg['refresh_token'] = $result_arr['refresh_token'];
		$msg['t_name'] = $result_arr['name'];
		
		//没有登录用户无绑定情况下，创建用户时候使用到
		es_session::set("api_user_info",$msg);
		
		$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where tencent_id = '".$msg['id']."' and tencent_id <> 0");
		$is_bind = intval(es_session::get("is_bind"));

		//存在用户直接登录
		if($user_data){
		    $GLOBALS['db']->query("update ".DB_PREFIX."user set t_access_token = '".$msg['t_access_token']."',login_ip = '".CLIENT_IP."',login_time= ".NOW_TIME." where id =".$user_data['id']);
		    
		    es_session::delete("api_user_info");
		    if($is_bind)  //是否来自绑定用户
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
		    else  //直接登录
		    {
		        require_once APP_ROOT_PATH."system/model/user.php";
		        auto_do_login_user($user_data['user_name'],$user_data['user_pwd'],$from_cookie = false);
		        app_redirect(url("index","index"));
		    }
		}elseif($is_bind==1&&$GLOBALS['user_info']){
		    //登录了站内用户，用户又不存在如果来自绑定就进行绑定
		    $GLOBALS['db']->query("update ".DB_PREFIX."user set t_access_token ='".$msg['t_access_token']."',t_openkey = '".$msg['t_openkey']."',tencent_id = '".$msg['id']."',t_name='".$msg['t_name']."' where id =".$GLOBALS['user_info']['id']);
		    require_once APP_ROOT_PATH."system/model/user.php";
		    load_user($GLOBALS['user_info']['id'],true);
		    app_redirect(url("index","uc_account"));
		}else{//没有登录站内用户，直接进行创建临时用户
		    $user_info = $this->create_user();
		    require_once APP_ROOT_PATH."system/model/user.php";
		    auto_do_login_user($user_info['user_name'],$user_info['user_pwd'],$from_cookie = false);
		    app_redirect(url("index","index"));
		}
	}
	
	public function get_title()
	{
		return '腾讯api登录接口，需要php_curl扩展的支持';
	}
	public function create_user()
	{
		$s_api_user_info = es_session::get("api_user_info");
		$user_data['user_name'] = $s_api_user_info['t_name'];
		
		$user_data['tencent_id'] = $s_api_user_info['id'];
		$user_data['t_access_token'] = $s_api_user_info['t_access_token'];
		$user_data['t_openkey'] = $s_api_user_info['t_openkey'];
		$user_data['t_name'] = $s_api_user_info['t_name'];
		
	    $result = auto_create($user_data, 0);
		if($result['status']){
		    $user_info = $result['user_data'];
		}else{
		    showErr("注册失败");
		}
		es_session::delete("api_user_info");
		return $user_info;
	}

	
	public function send_message($data)
	{
		
			require_once APP_ROOT_PATH.'system/api_login/Tencent/Tencent.php';
			OAuth::init($this->api['config']['app_key'], $this->api['config']['app_secret']);		
			
			$uid = intval($GLOBALS['user_info']['id']);
			$udata = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$uid);
			
			es_session::set("t_access_token",$udata['t_access_token']);
			es_session::set("t_openid",$udata['tencent_id']);
			es_session::set("t_openkey",$udata['t_openkey']);
			if (es_session::get("t_access_token")|| (es_session::get("t_openid")&&es_session::get("t_openkey"))) 
			{		
				if(!empty($data['img']))
				{
					 $params = array(
			        	'content' => $data['content'],
					 	'clientip'	=>	CLIENT_IP,
					 	'format'	=>	'json'
				    );
				    $multi = array('pic' => $data['img']);
				    $r = Tencent::api('t/add_pic', $params, 'POST', $multi);
				}
				else
				{
					 $params = array(
			        	'content' => $data['content'],
					 	'clientip'	=>	CLIENT_IP,
					 	'format'	=>	'json'
				    );
				    $r = Tencent::api('t/add', $params, 'POST');
				}
				
				
				$msg = json_decode($r,true);
				
	
				
				if(intval($msg['errcode'])==0)
				{
					$result['status'] = true;
					$result['msg'] = "success";

				}
				else
				{
					$result['status'] = false;
					$result['msg'] = "腾讯微博".$msg['msg'];

				}
								
			}
			return $result;

	
	}
	
	//解除API 绑定
	public function unset_api(){
	    if($GLOBALS['user_info']){
	        //解除绑定
	        $GLOBALS['db']->query("update ".DB_PREFIX."user set tencent_id='',t_access_token ='',t_openkey = '',t_name='',is_syn_tencent=0 where id =".$GLOBALS['user_info']['id']);				
	    }
	}
	
	/**
	 * 设置微博同步
	 * @return string
	 */
	public function set_syn_weibo(){
	    if($GLOBALS['user_info']){
	        if($GLOBALS['user_info']['tencent_id']>0){
	            $set_v = $GLOBALS['user_info']['is_syn_tencent']==1?0:1;
	
	            $GLOBALS['db']->query("update ".DB_PREFIX."user set is_syn_tencent=".$set_v." where id =".$GLOBALS['user_info']['id']);
	            $result['status'] = 1;
	            $result['info'] = $GLOBALS['user_info']['is_syn_tencent']==1?"取消同步成功":"设置同步成功";
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