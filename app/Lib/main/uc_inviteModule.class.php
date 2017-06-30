<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


require APP_ROOT_PATH.'app/Lib/page.php';
class uc_inviteModule extends MainBaseModule
{
	public function index()
	{
		require APP_ROOT_PATH."system/model/uc_center_service.php";
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}	
		
		$page = intval($_REQUEST['p']);
		if($page<=0)	$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$user_id = intval($GLOBALS['user_info']['id']);
		$result = get_invite_list($limit,$user_id);	
		$GLOBALS['tmpl']->assign("list",$result['list']);

		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);		

		$total_referral_money = $GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."referrals where user_id = ".$GLOBALS['user_info']['id']." and pay_time > 0");
		$total_referral_score = $GLOBALS['db']->getOne("select sum(score) from ".DB_PREFIX."referrals where user_id = ".$GLOBALS['user_info']['id']." and pay_time > 0");		
		$GLOBALS['tmpl']->assign("total_referral_money",$total_referral_money);
		$GLOBALS['tmpl']->assign("total_referral_score",$total_referral_score);

		$share_url = get_domain().APP_ROOT."/";
		if($GLOBALS['user_info'])
		$share_url .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
		$GLOBALS['tmpl']->assign("share_url",$share_url);				
		
		$GLOBALS['tmpl']->assign("page_title","我的邀请");
		assign_uc_nav_list();//左侧导航菜单	
		$GLOBALS['tmpl']->display("uc/uc_invite.html");
		
	}
	
}
?>