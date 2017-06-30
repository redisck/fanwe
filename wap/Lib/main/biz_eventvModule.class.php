<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_eventvModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		
		$data = request_api("biz_eventv","index");

		if ($data['biz_user_status']==0){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}
		//设定页面类型为验证部分
		$data['page_type'] = "v";
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","eventv"));
		$GLOBALS['tmpl']->display("biz_eventv.html");
	}
	
	public function do_submit(){
	    $param=array();
	    $param['location_id'] = intval($_REQUEST['location_id']);
	    $param['event_sn'] = strim($_REQUEST['event_sn']);
	    $data = request_api("biz_eventv","check_event",$param);
	    ajax_return($data);
	}
	
	public function use_evnet(){
	    $param = array();
	    $param['location_id'] = intval($_REQUEST['location_id']);
	    $param['event_sn'] = strim($_REQUEST['event_sn']);
	    $data = request_api("biz_eventv","use_event",$param);
	    ajax_return($data);
	}
	
	
	
	
}
?>