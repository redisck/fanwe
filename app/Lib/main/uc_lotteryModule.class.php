<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class uc_lotteryModule extends MainBaseModule
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
		
		$sql = "select * from ".DB_PREFIX."lottery  where  ".
			" user_id = ".$user_id." order by  create_time desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."lottery  where  ".
				" user_id = ".$user_id;
	
		$list = $GLOBALS['db']->getAll($sql);

		foreach($list as $k=>$v)
		{
			$list[$k]['deal'] = load_auto_cache("deal",array("id"=>$v['deal_id']));
		}
		$count = $GLOBALS['db']->getOne($sql_count);
	
		$page = new Page($count,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
		$GLOBALS['tmpl']->assign("page_title","我的抽奖号");
		assign_uc_nav_list();
		$GLOBALS['tmpl']->display("uc/uc_lottery_index.html");
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
			$lottery = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."lottery where id = ".$id." and user_id = ".$GLOBALS['user_info']['id']);
			$deal = load_auto_cache("deal",array("id"=>$lottery['deal_id']));
			if($lottery)
			{
				
					if($t=="sms")
					{
						if(app_conf("SMS_ON")==0||app_conf("LOTTERY_SN_SMS")==0)
						{
							$data['status'] = 0;
							$data['info'] = "不支持短信发送";
							ajax_return($data);
						}
						elseif($lottery['sms_count']>=app_conf("SMS_COUPON_LIMIT"))
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
							send_lottery_sms($id);
							$GLOBALS['db']->query("update ".DB_PREFIX."lottery set sms_count = sms_count + 1 where id = ".$id);
							$data['status'] = 1;
							$data['info'] = "短信成功发送到".$GLOBALS['user_info']['mobile']."，请注意查收。";
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
			else
			{
				$data['status'] = 0;
				$data['info'] = "抽奖号不存在";
				ajax_return($data);
			}
		}
	}
}
?>