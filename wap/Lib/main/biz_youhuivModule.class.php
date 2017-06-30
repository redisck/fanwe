<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_youhuivModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		
		$data = request_api("biz_youhuiv","index");

		if ($data['biz_user_status']==0){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}
		//设定页面类型为验证部分
		$data['page_type'] = "v";
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","youhuiv"));
		$GLOBALS['tmpl']->display("biz_youhuiv.html");
	}
	
	public function do_submit(){
	    $param=array();
	    $param['location_id'] = intval($_REQUEST['location_id']);
	    $param['youhui_sn'] = strim($_REQUEST['youhui_sn']);
	    $data = request_api("biz_youhuiv","check_youhui",$param);
	    ajax_return($data);
	}
	
	public function use_youhui(){
	    $param = array();
	    $param['location_id'] = intval($_REQUEST['location_id']);
	    $param['youhui_sn'] = strim($_REQUEST['youhui_sn']);
	    $data = request_api("biz_youhuiv","use_youhui",$param);
	    ajax_return($data);
	}
	
	
	
	
}
?>