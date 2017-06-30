<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class eventModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();		

		$param['data_id'] = intval($_REQUEST['data_id']); //分类ID

		$request = $param;
		//获取品牌
		$data = request_api("event","index",$param);

		if($data['user_login_status']!=LOGIN_STATUS_NOLOGIN){
		    $data['is_login'] = 1;
		}
		
		if(intval($data['id'])==0)
		{
		    app_redirect(wap_url("index"));
		}

		$GLOBALS['tmpl']->assign("event",$data['event_info']);
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("index","event"));
		$GLOBALS['tmpl']->display("event.html");
	}
	
	/*
	 * 领取优惠券
	 * */
	public function load_event_submit(){
	    global_run();
	    init_app_page();
	    $data_id = intval($_REQUEST['data_id']);
	    $data = request_api("event","load_event_submit",array("data_id"=>$data_id));
	    if ($data['user_login_status']!=LOGIN_STATUS_LOGINED){
	    	app_redirect(wap_url("index","user#login"));
	    }
	    
	    if($data['status']==0){
	        showErr($data['info'],0,wap_url("index","event#index",array("data_id"=>$data_id)));
	    }
	    
	    $GLOBALS['tmpl']->assign("event_id",$data_id);
		$GLOBALS['tmpl']->assign("event_fields",$data['event_fields']);	
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("index","event"));
		$GLOBALS['tmpl']->display("event_submit.html");
	}
	
	public function do_submit(){
	   
	    global_run();
        /*获取参数*/
	    $event_id = intval($_REQUEST['event_id']);
	    $param=array();
	    $param['event_id'] = $event_id;
	    $param['result'] = $_REQUEST['result'];
	    $param['field_id'] = $_REQUEST['field_id'];
	    
	    $data = request_api("event","do_submit",$param);
		if ($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			$data['status'] = 0;
			$data['info'] = "";
	        $data['jump'] = wap_url("index","user#login");
	    }
	    else
	    {
	        if ($data['status'] == 1){
	            $data['jump'] = wap_url("index","uc_event#index");
	        }else
	            $data['jump'] = wap_url("index","event#index",array("data_id"=>$event_id));
	    }
	  
	    ajax_return($data);
	    
	}
	
	public function detail()
	{
	    global_run();
	    init_app_page();
	
	    $data_id = intval($_REQUEST['data_id']);
	
	    $data = request_api("event","detail",array("data_id"=>$data_id));
	
	    $GLOBALS['tmpl']->assign("data",$data);
	    $GLOBALS['tmpl']->assign("event_info",$data['event_info']);
	    $GLOBALS['tmpl']->display("event_detail.html");
	}
	
	
	
}
?>