<?php
// +----------------------------------------------------------------------
// | Fanwe 方维商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'system/wechat/platform_wechat.class.php';
class wxconfModule extends BizBaseModule
{
	function __construct()
	{
		parent::__construct();
		global_run();
		$this->check_auth();
	}
	
	public function index()
	{
		init_app_page();
		$weixin_conf = load_auto_cache("weixin_conf");
		$s_account_info = $GLOBALS['account_info'];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		if($weixin_conf['platform_status']==0||$s_account_info['platform_status']==0)
		{
			showBizErr("公众平台功能已关闭");
		}
		
		
		
		$weixin_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = '".$supplier_id."' and type = 0");
		//$weixin_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = '0'");
		$GLOBALS['tmpl']->assign("weixin_account",$weixin_account);
		if($weixin_account)
		{
			$GLOBALS['tmpl']->assign("unbind_url",url("biz","wxconf#unbind"));
			$verify_type_array=array(-1=>'未认证',0=>'微信认证',1=>'新浪微博认证',2=>'腾讯微博认证',3=>'已资质认证通过但还未通过名称认证',4=>'已资质认证通过、还未通过名称认证，但通过了新浪微博认证',5=>'已资质认证通过、还未通过名称认证，但通过了腾讯微博认证');
			$service_type_array=array(0=>'订阅号',1=>'由历史老帐号升级后的订阅号',2=>'服务号');
			$GLOBALS['tmpl']->assign("verify_type",$verify_type_array[$weixin_account['verify_type_info']]);
			$GLOBALS['tmpl']->assign("service_type",$service_type_array[$weixin_account['service_type_info']]);
			$GLOBALS['tmpl']->assign("head_title","公众号信息");
			
			
			//行业与模板操作
			$industry_list = require_once APP_ROOT_PATH."system/wechat/wx_industry_cfg.php";
			$GLOBALS['tmpl']->assign("industry_list",$industry_list);
			$GLOBALS['tmpl']->assign("syn_industry_url",url("biz","wxconf#syn_industry"));
			
			
			$template_list = require_once APP_ROOT_PATH."system/wechat/wx_template_cfg.php";
			foreach($template_list as $k=>$v)
			{
				$template_list[$k]['data'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_tmpl where account_id=".$supplier_id." and template_id_short='".$k."'");
			}
			$GLOBALS['tmpl']->assign("template_list",$template_list);
			$GLOBALS['tmpl']->assign("syn_template_url",url("biz","wxconf#syn_template"));
			$GLOBALS['tmpl']->assign("del_template_url",url("biz","wxconf#del_template"));
			
		}
		else
		{		
			$platform = new PlatformWechat();
			$platform_pre_auth_code=$platform->check_platform_get_pre_auth_code();
			$return_url=get_domain().url("biz","weixin#platform_get_auth_code");
			$return_url=urlencode($return_url);
			$sq_url='https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.$weixin_conf['platform_appid'].'&pre_auth_code='.$platform_pre_auth_code.'&redirect_uri='.$return_url;
			$GLOBALS['tmpl']->assign("sq_url",$sq_url);		
			$GLOBALS['tmpl']->assign("head_title","公众号授权接入");
		}
		$GLOBALS['tmpl']->display("pages/wxconf/index.html");
		
	}
	
	public function unbind()
	{
		$s_account_info = $GLOBALS['account_info'];
		$supplier_id = intval($s_account_info['supplier_id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."weixin_account where user_id = '".$supplier_id."' and type = 0");
		app_redirect(url("biz","wxconf"));
	}
	
	
	
	
	
	public function syn_industry()
	{
		$s_account_info = $GLOBALS['account_info'];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$supplier_id." and type = 0");
		$industry_list = require_once APP_ROOT_PATH."system/wechat/wx_industry_cfg.php";
		$k=1;
		foreach($industry_list as $key => $v)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."weixin_account set industry_".$k."='".$key."' where user_id = ".$supplier_id." and type = 0");
			$wx_account['industry_'.$k] = $key;
			$k++;
		}
	
		//开始获取微信的token
		$industry_1 = intval($wx_account['industry_1']);
		$industry_2 = intval($wx_account['industry_2']);
	
		$weixin_app_id =$wx_account['authorizer_appid'];
		$weixin_app_key =$wx_account['authorizer_access_token'];
		if($weixin_app_id=="" || $weixin_app_key==""){
			//$this->showFrmErr("请先设置授权",1,"",JKU("nav/auth"));
			showBizErr("请先设置授权");
		}
		
		
		
		$option = array('authorizer_access_token'=>$wx_account['authorizer_access_token'],
 				'authorizer_access_token_expire'=>$wx_account['expires_in'],
 				'authorizer_appid'=>$wx_account['authorizer_appid'],
 				'authorizer_refresh_token'=>$wx_account['authorizer_refresh_token']
		);
		$platform= new PlatformWechat($option);
		$platform_authorizer_token=$platform->check_platform_authorizer_token();
		if($platform_authorizer_token){
			$result=$platform->setTMIndustry($industry_1,$industry_2);
			if($result){
				if(!isset($result['errcode']) || intval($result['errcode'])==0){
					//$this->sdb->table('weixin_nav')->where(array('seller_id'=>$this->seller_id))->setField('status',1);
					$data=array('industry_1_status'=>1,'industry_2_status'=>1);
					$GLOBALS['db']->autoExecute(DB_PREFIX."weixin_account",$data,'UPDATE',' id='.$wx_account['id']);
					showBizSuccess("同步成功");
				}else{
					if($result['errcode']==43100){
						showBizErr("同步频率太高,一个月只可修改一次");
					}else{
						showBizErr("同步出错，错误代码".$result['errcode'].":".$result['errmsg']);
					}
				}
			}else{
				showBizErr("通讯出错，请重试");
			}
		}else{
			showBizErr("通讯出错，请重试");
		}
	}
	
	
	public function syn_template()
	{
		$s_account_info = $GLOBALS['account_info'];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$supplier_id." and type = 0");
		$option = array('authorizer_access_token'=>$wx_account['authorizer_access_token'],
				'authorizer_access_token_expire'=>$wx_account['expires_in'],
				'authorizer_appid'=>$wx_account['authorizer_appid'],
				'authorizer_refresh_token'=>$wx_account['authorizer_refresh_token']
		);
		
		$template_list = require_once APP_ROOT_PATH."system/wechat/wx_template_cfg.php";
		$success_count = 0;
		foreach($template_list as $k=>$v)
		{
			$name = strim($v['name']);
			$template_id_short = strim($k);
				
			$row = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_tmpl where template_id_short='".$template_id_short."' and account_id=".$supplier_id);
			if(!$row)
			{
				$platform= new PlatformWechat($option);
				$platform->check_platform_authorizer_token();
				$result=$platform->addTemplateMessage($template_id_short);
				if($result)
				{
					if(intval($result['errcode'])==0)
					{
						$data = array('first'=>$name,'remark'=>array('value'=>$v['remark'],'color'=>'#173177'));
						$msg = serialize($data);
						$GLOBALS['db']->autoExecute(DB_PREFIX."weixin_tmpl",array('name'=>$name,'template_id'=>$result,'template_id_short'=>$template_id_short,'account_id'=>$supplier_id,'msg'=>$msg));
						if(!$GLOBALS['db']->error())
						{
							$success_count++;
						}
						else
						{
							$err[$template_id_short] = "DB:".$GLOBALS['db']->error();
						}
					}
					else
					{
						$err[$template_id_short] = $result['errmsg'];
					}
				}
			}
			else
			{
				$success_count++;
			}//end install
		}//end foreach
	
		if($success_count==count($template_list))
		{
			showBizSuccess("同步成功");
		}
		else
		{
			$msg = "";
			foreach($err as $kk=>$vv)
			{
				$msg.="模板".$kk."同步失败：".$vv."<br />";
			}
			showBizErr($msg);
		}
	}
	
	
	public function del_template()
	{
		$s_account_info = $GLOBALS['account_info'];
		$supplier_id = intval($s_account_info['supplier_id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."weixin_tmpl where account_id = ".$supplier_id);
		showBizSuccess("删除成功");
	}
}
?>