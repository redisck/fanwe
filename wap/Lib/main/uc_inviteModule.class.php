<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_inviteModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		if(!$GLOBALS['user_info']){
			app_redirect(wap_url("index","user#login"));
		}
		$user_id = $GLOBALS['user_info']['id'];	
		$data=array();
		$data['share_url'] = get_domain().url("index","index",array("r"=>base64_encode(intval($user_id))));
		$data['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$data['page_title'].="邀请链接";
		$data['city_name'] = $GLOBALS['city']['name'];

		//print_r($data);exit;

		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("uc_invite.html");
	}
	

	
}
?>