<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_indexModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		app_redirect(wap_url("biz","user#login"));
	}
}
?>