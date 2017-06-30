<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_dealvModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		
		$data = request_api("biz_dealv","index");

		if ($data['biz_user_status']==0){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}
		//设定页面类型为验证部分
		$data['page_type'] = "v";
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","dealv"));
		$GLOBALS['tmpl']->display("biz_dealv.html");
	}
	
	public function do_submit(){
	    $param=array();
	    $param['location_id'] = intval($_REQUEST['location_id']);
	    $param['coupon_pwd'] = strim($_REQUEST['coupon_pwd']);
	    $data = request_api("biz_dealv","check_coupon",$param);
	    ajax_return($data);
	}
	
	public function use_coupon(){
	    $param = array();
	    $param['location_id'] = intval($_REQUEST['location_id']);
	    $param['coupon_pwd'] = strim($_REQUEST['coupon_pwd']);
	    $param['coupon_use_count'] = intval($_REQUEST['coupon_use_count']);
	    
	    $data = request_api("biz_dealv","use_coupon",$param);
	    ajax_return($data);
	}
	
	
	
	
}
?>