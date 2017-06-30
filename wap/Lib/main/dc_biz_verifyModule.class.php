<?php
/**
 * 商家预订电子劵验证
 * 
 *
 */
class dc_biz_verifyModule extends MainBaseModule{
	
	/**
	 * 	商家预订电子劵页面
	 *  
	 */  
	public function index(){
		

		global_run();
		init_app_page();
		$param['lid'] = intval($_REQUEST['lid']);
		$data = request_api("dc_biz_verify","index",$param);
		
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
		
			if($data['status']==0){
				showErr($data['info'],0,wap_url("index","dc_biz"));
			}
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->display("dc/biz/dcverify_index.html");
		}
	}
	
	

	/**
	 * 	商家预订电子劵验证接口
	 *
	 */
	
	public function check_dcverify(){
	
		global_run();
		init_app_page();
		$param['lid'] = intval($_REQUEST['lid']);
		$param['verify_sn'] = strim($_REQUEST['verify_sn']);
		$data = request_api("dc_biz_verify","check_dcverify",$param);
		
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
		
			$result['status']=$data['status'];
			$result['info']=$data['info'];
			ajax_return($result);
		}
		
	}
	

	/**
	 * 	商家预订电子劵确定消费接口
	 *
	 */
	
	public function use_dcverify(){

		global_run();
		init_app_page();
		$param['lid'] = intval($_REQUEST['lid']);
		$param['verify_sn'] = strim($_REQUEST['verify_sn']);
		$data = request_api("dc_biz_verify","use_dcverify",$param);
		
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
		
			$result['status']=$data['status'];
			$result['info']=$data['info'];
			ajax_return($result);
		}
	}
}
?>