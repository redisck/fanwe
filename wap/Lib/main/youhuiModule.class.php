<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class youhuiModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();		

		$param['data_id'] = intval($_REQUEST['data_id']); //分类ID

		//获取品牌
		$data = request_api("youhui","index",$param);
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
		    $is_login = 0;
		}
		if(intval($data['id'])==0)
		{
		    app_redirect(wap_url("index"));
		}

		$GLOBALS['tmpl']->assign("youhui",$data['youhui_info']);
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("index","youhui"));
		$GLOBALS['tmpl']->display("youhui.html");
	}
	
	/*
	 * 领取优惠券
	 * */
	public function download_youhui(){
	    $data_id = intval($_REQUEST['data_id']);
	    $data = request_api("youhui","download_youhui",array("data_id"=>$data_id));

		if($data['user_login_status']!=LOGIN_STATUS_LOGINED)
    	{
    		$data['status'] = 0;
    		$data['info'] = "";
    		$data['jump']  = wap_url("index","user#login");
    	}
	    ajax_return($data);
	}
	
	public function detail(){
	    global_run();
	    init_app_page();
	    
	    $data_id = intval($_REQUEST['data_id']);
	    
	    $data = request_api("youhui","detail",array("data_id"=>$data_id));
	    
	    $GLOBALS['tmpl']->assign("data",$data);
	    $GLOBALS['tmpl']->assign("youhui_info",$data['youhui_info']);
	    $GLOBALS['tmpl']->display("youhui_detail.html");
	}
	
	
}
?>