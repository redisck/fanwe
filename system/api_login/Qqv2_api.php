<?php
// +----------------------------------------------------------------------
// | Fanwe 方维订餐小秘书商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

$api_lang = array(
	'name'	=>	'QQv2登录插件',
	'app_key'	=>	'QQAPI应用appid',
	'app_secret'	=>	'QQAPI应用appkey',
);

$config = array(
	'app_key'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //腾讯API应用的KEY值
	'app_secret'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //腾讯API应用的密码值
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
	if(ACTION_NAME=='install')
	{
		//更新字段
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `qqv2_id`  varchar(255) NOT NULL",'SILENT');
		$GLOBALS['db']->query("ALTER TABLE `".DB_PREFIX."user`  ADD COLUMN `qq_token`  varchar(255) NOT NULL",'SILENT');
	}
    $module['class_name']    = 'Qqv2';

    /* 名称 */
    $module['name']    = $api_lang['name'];

	$module['config'] = $config;
	
	$module['lang'] = $api_lang;
    
    return $module;
}

// QQ的api登录接口
require_once(APP_ROOT_PATH.'system/libs/api_login.php');
class Qqv2_api implements api_login {
	
	private $api;
	
	public function __construct($api)
	{
		$api['config'] = unserialize($api['config']);
		$this->api = $api;
	}
	
	public function get_api_url()
	{
		es_session::start();
		$inc=array();
		$callback = SITE_DOMAIN.APP_ROOT."/callback/api_login/qqv2_callback.php";
		$scope="get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr";
		$inc['appid']=$this->api['config']['app_key'];
		$inc['appkey']=$this->api['config']['app_secret'];
		$inc['callback']=$callback;
		$inc['scope']=$scope;
		$inc['errorReport']=1;
		$inc['storageType']="file";
		$inc['host']=SITE_DOMAIN;
		$setting = json_encode($inc);
		@file_put_contents(APP_ROOT_PATH."/public/qqv2_inc.php",$setting);
		@chmod(APP_ROOT_PATH."/public/qqv2_inc.php",0777);
		$url = SITE_DOMAIN.APP_ROOT."/system/api_login/qqv2/qq_login.php";	
		$str = "<a href='".$url."' title='".$this->api['name']."'><img src='".$this->api['icon']."' alt='".$this->api['name']."' /></a>&nbsp;";
		return $str;
	}
    
	/**
	 * 返回腾讯绑定数组信息
	 * @return array("class","name","bicon",url);
	 */
	public function get_bind_api_url_arr()
	{
	    es_session::start();
		$inc=array();
		
		$callback = SITE_DOMAIN.APP_ROOT."/callback/api_login/qqv2_callback.php";
		$scope="get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr";
		$inc['appid']=$this->api['config']['app_key'];
		$inc['appkey']=$this->api['config']['app_secret'];
		$inc['callback']=$callback;
		$inc['scope']=$scope;
		$inc['errorReport']=1;
		$inc['storageType']="file";
		$inc['host']=SITE_DOMAIN;
		$setting = json_encode($inc);
		@file_put_contents(APP_ROOT_PATH."/public/qqv2_inc.php",$setting);
		@chmod(APP_ROOT_PATH."/public/qqv2_inc.php",0777);
		$url = SITE_DOMAIN.APP_ROOT."/system/api_login/qqv2/qq_login.php";	
	    es_session::set("is_bind",1);
	
	    $data['class'] = 'qqv2';
	    $data['name'] =  $this->api['name'];
	    $data['bicon'] = $this->api['bicon'];
	    $data['url'] = $url;
	    return $data;
	}
		
	public function callback()
	{
	    global_run();
	    require_once(APP_ROOT_PATH."system/api_login/qqv2/qqConnectAPI.php");
		$qc = new QC();
		$access_token =$qc->qq_callback();
		$openid = $qc->get_openid();
		$use_info_keysArr = array(
            "access_token" => $access_token,
			"openid" => $openid,
		 	"oauth_consumer_key" => $this->api['config']['app_key']
        );
		$use_info_url="https://graph.qq.com/user/get_user_info";
        $graph_use_info_url = $qc->urlUtils->combineURL($use_info_url, $use_info_keysArr);
        $response = $qc->urlUtils->get_contents($graph_use_info_url);

        if($response['ret']!=0){
            showErr("授权失败,错误信息：".$response['msg']);
            die();
        }
        $response = json_decode($response,1);
		$msg['field'] = 'qqv2_id';
		$msg['id'] = $openid;
		$msg['name'] = $response["nickname"];
		es_session::set("api_user_info",$msg);

		$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where qqv2_id = '".$openid."' and qqv2_id <> '' and is_effect=1 and is_delete=0");
		$is_bind = intval(es_session::get("is_bind"));
		if($user_data)
		{
			    $GLOBALS['db']->query("update ".DB_PREFIX."user set qq_token = '".$access_token."',login_ip = '".CLIENT_IP."',login_time= ".NOW_TIME." where id =".$user_data['id']);				
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
			$GLOBALS['db']->query("update ".DB_PREFIX."user set qqv2_id= '".$msg['id']."', qq_token ='".$access_token."' where id =".$GLOBALS['user_info']['id']);						
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
		return 'QQv2登录接口，需要php_curl扩展的支持';
	}
	public function create_user()
	{
	    require_once(APP_ROOT_PATH."system/model/user.php");
		$s_api_user_info = es_session::get("api_user_info");
		$user_data['user_name'] = $s_api_user_info['name'];
		$user_data['qqv2_id'] = $s_api_user_info['id'];
		
		$result = auto_create($user_data, 0);
		if($result['status']){
		    $user_info = $result['user_data'];
		}else{
		    showErr("注册失败");
		}
		
		es_session::delete("api_user_info");
		return $user_info;
	}	
	
	//解除API 绑定
	public function unset_api(){
	    if($GLOBALS['user_info']){
	       $GLOBALS['db']->query("update ".DB_PREFIX."user set qqv2_id= '', qq_token ='' where id =".$GLOBALS['user_info']['id']);
	    }
	}    
	
	//同步微博信息
	public function send_message($data){
	    
	}
}
?>