<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_manageModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		
        
		if(!$GLOBALS['account_info']){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}
		
		$data['page_title'] = "经营管理";
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("biz_manage.html");
	}
	
	
	
	
}
?>