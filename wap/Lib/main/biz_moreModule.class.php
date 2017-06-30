<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_moreModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		
		$data = request_api("biz_more","index");

		if ($data['biz_user_status']==0){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}

		$data['account_name'] = $GLOBALS['account_info']['account_name'];
		//设定页面类型为验证部分
		$data['page_type'] = "r";
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","more"));
		$GLOBALS['tmpl']->display("biz_more.html");
	}
	
	
}
?>