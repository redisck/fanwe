<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class uc_eventModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
	
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
	
		$did = intval($_REQUEST['did']);
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page_size = app_conf("PAGE_SIZE");
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$user_id = $GLOBALS['user_info']['id'];
		
		$sql = "select * from ".DB_PREFIX."event_submit  where  ".
			" user_id = ".$user_id." order by  create_time desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."event_submit  where  ".
				" user_id = ".$user_id;
	
		$list = $GLOBALS['db']->getAll($sql);

		foreach($list as $k=>$v)
		{
			$list[$k]['event'] = load_auto_cache("event",array("id"=>$v['event_id']));
		}
		$count = $GLOBALS['db']->getOne($sql_count);
	
		$page = new Page($count,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
		$GLOBALS['tmpl']->assign("page_title","我的活动报名");
		assign_uc_nav_list();
		$GLOBALS['tmpl']->display("uc/uc_event_index.html");
	}
	
	
	public function view_submit()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$GLOBALS['tmpl']->assign("is_logined",false);
		}
		else
		{
			$GLOBALS['tmpl']->assign("is_logined",true);
			$id = intval($_REQUEST['id']);
			$user_id = intval($GLOBALS['user_info']['id']);
			$user_submit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where user_id = ".$user_id." and id = ".$id);
			if($user_submit)
			{					
				$event_fields = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$user_submit['event_id']." order by sort asc");
				foreach($event_fields as $k=>$v)
				{
					$event_fields[$k]['result'] = $GLOBALS['db']->getOne("select result from ".DB_PREFIX."event_submit_field where submit_id = ".$user_submit['id']." and field_id = ".$v['id']." and event_id = ".$user_submit['event_id']);
					$event_fields[$k]['value_scope'] = explode(" ",$v['value_scope']);
				}
				$GLOBALS['tmpl']->assign("event_fields",$event_fields);					
			}
			else
			{
				$data['status'] = 0;
				ajax_return($data);
			}
		}
		
		$data['status'] = 1;
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/view_event_submit.html");
		ajax_return($data);
	}
	
	
	public function send()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		else
		{
			$t = strim($_REQUEST['t']);
			$id = intval($_REQUEST['id']);
			$event_submit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where id = ".$id." and user_id = ".$GLOBALS['user_info']['id']);
			$event_info = load_auto_cache("event",array("id"=>$event_submit['event_id']));
			if($event_submit)
			{
				if($event_submit['is_verify']==0)
				{
					$data['status'] = 0;
					$data['info'] = "报名未审核";
					ajax_return($data);
				}
				if($event_submit['is_verify']==2)
				{
					$data['status'] = 0;
					$data['info'] = "报名审核不通过";
					ajax_return($data);
				}
				elseif($event_submit['confirm_time']>0)
				{
					$data['status'] = 0;
					$data['info'] = "活动已参加";
					ajax_return($data);
				}
				elseif($event_submit['event_end_time']>0&&$event_submit['event_end_time']<NOW_TIME)
				{
					$data['status'] = 0;
					$data['info'] = "活动已过期";
					ajax_return($data);
				}
				else
				{
					if($t=="sms")
					{
						if(app_conf("SMS_ON")==0)
						{
							$data['status'] = 0;
							$data['info'] = "不支持短信发送";
							ajax_return($data);
						}
						elseif($event_submit['sms_count']>=app_conf("SMS_COUPON_LIMIT"))
						{
							$data['status'] = 0;
							$data['info'] = "短信发送已超过".app_conf("SMS_COUPON_LIMIT")."次";
							ajax_return($data);
						}
						elseif($GLOBALS['user_info']['mobile']=="")
						{
							$data['status'] = 0;
							$data['info'] = "请先设置手机号";
							$data['jump'] = url("index","uc_account");
							ajax_return($data);
						}
						else
						{
							send_event_sn_sms($id);
							$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set sms_count = sms_count + 1 where id = ".$id);
							$data['status'] = 1;
							$data['info'] = "短信成功发送到".$GLOBALS['user_info']['mobile']."，请注意查收。";
							ajax_return($data);
						}		
					}
					elseif($t=="mail")
					{
						if(app_conf("MAIL_ON")==0)
						{
							$data['status'] = 0;
							$data['info'] = "不支持邮件发送";
							ajax_return($data);
						}
						elseif($event_submit['mail_count']>=app_conf("MAIL_COUPON_LIMIT"))
						{
							$data['status'] = 0;
							$data['info'] = "邮件发送已超过".app_conf("MAIL_COUPON_LIMIT")."次";
							ajax_return($data);
						}
						elseif($GLOBALS['user_info']['email']=="")
						{
							$data['status'] = 0;
							$data['info'] = "请先设置邮箱";
							$data['jump'] = url("index","uc_account");
							ajax_return($data);
						}
						else
						{
							send_event_sn_mail($id);
							$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set mail_count = mail_count + 1 where id = ".$id);
							$data['status'] = 1;
							$data['info'] = "邮件成功发送到".$GLOBALS['user_info']['email']."，请注意查收。";
							ajax_return($data);
						}		
					}						
					else
					{
						$data['status'] = 0;
						$data['info'] = "非法操作";
						ajax_return($data);
					}
				}
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = "报名数据不存在";
				ajax_return($data);
			}
		}
	}
}
?>