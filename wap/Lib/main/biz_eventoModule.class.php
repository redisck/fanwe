<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_eventoModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		$param['page'] = intval($_REQUEST['page']);
		$data = request_api("biz_evento","index",$param);

		if ($data['biz_user_status']==0){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}
		//设定页面类型为验证部分
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","evento"));
		$GLOBALS['tmpl']->display("biz_evento.html");
	}
	
	
	public function events()
	{
	    global_run();
	    init_app_page();
	    $param['page'] = intval($_REQUEST['page']);
	    $param['data_id'] = intval($_REQUEST['data_id']);
	    
	    $data = request_api("biz_evento","events",$param);
	

	    if ($data['biz_user_status']==0){ //用户未登录
	        app_redirect(wap_url("biz","user#login"));
	    }
	    //设定页面类型为验证部分
	    $GLOBALS['tmpl']->assign("data",$data);
	    $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","evento"));
	    $GLOBALS['tmpl']->display("biz_evento_events.html");
	}
	
	public function approval()	{
	
	    global_run();		
		init_app_page();		
		$param['data_id'] = intval($_REQUEST['data_id']);
		$data = request_api("biz_evento","approval",$param);

		if ($data['biz_user_status']==0){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}
		ajax_return($data);
	
	}
	
	public function refuse()	{
	
	    global_run();		
		init_app_page();		
		$param['data_id'] = intval($_REQUEST['data_id']);
		$data = request_api("biz_evento","refuse",$param);

		if ($data['biz_user_status']==0){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}
		ajax_return($data);
	
	}
	
	
	
}
?>