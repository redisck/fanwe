<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class index_moreModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		
		init_app_page();
		
		$data = request_api("index","more");

		foreach($data['indexs'] as $k=>$v)
		{
			$data['indexs'][$k]['url'] =  getWebAdsUrl($v);
		}
		
		$GLOBALS['tmpl']->assign("data",$data);
		
		$GLOBALS['tmpl']->display("index_more.html");
	}
	
	
}
?>