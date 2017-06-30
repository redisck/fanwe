<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 发表点评
 * @author jobin.lin
 *
 */
class add_dpModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		

		$data['type'] = strim($_REQUEST['type']);
		$data['data_id'] = intval($_REQUEST['data_id']);
		

		$data['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
	    $data['page_title'] .= "发表点评";

		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("add_dp.html");
	}
	
	public function do_dp(){
	    global_run();
	    init_app_page();
	    $param=array();
	    $param['page'] = intval($_REQUEST['page']);
	    $param['type'] = strim($_REQUEST['type']);
	    $param['data_id'] = intval($_REQUEST['data_id']);
	    $param['point'] = intval($_REQUEST['point']);
	    $param['content'] = strim($_REQUEST['content']);

	    $data = request_api("dp","add_dp",$param);
	    if($data['user_login_status'] !=LOGIN_STATUS_LOGINED){
	        $data['jump'] = wap_url("index","user#login");
	    }else
	       $data['jump'] = wap_url("index","dp_list#index",array('type'=>$param['type'],'data_id'=>$param['data_id']));
	    ajax_return($data);
	}
	
}
?>