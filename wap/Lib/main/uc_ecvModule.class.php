<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_ecvModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();		
        $n_valid = intval($_REQUEST['n_valid']);
		$data = request_api("uc_ecv","index",array('n_valid'=>$n_valid));

	    if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
		    app_redirect(wap_url("index","user#login"));
		}
		
		$GLOBALS['tmpl']->assign("n_valid",$n_valid);
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->assign("ecv_list",$data['data']);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("index","uc_ecv"));
		$GLOBALS['tmpl']->display("uc_ecv.html");
	}
	
	public function load_ecv_list(){
	    global_run();
	    init_app_page();
	    $n_valid = intval($_REQUEST['n_valid']);
	    $data = request_api("uc_ecv","load_ecv_list",array('page'=>intval($_REQUEST['page']),'n_valid'=>$n_valid));
	    
	    if($data['user_login_status']!=LOGIN_STATUS_NOLOGIN){
	        $result['status']=-1;
	        $result['jump'] = wap_url("index","user#login");
	    }
	    
	    $result['status'] =1;
	    if($data['page']['page']==$data['page']['page_total'])
	        $result['is_lock'] = 1;
	    if($data['data']){
	        $GLOBALS['tmpl']->assign("ecv_list",$data['data']);
	        $result['html'] = $GLOBALS['tmpl']->fetch("load_ecv_list.html");
	    }
	    ajax_return($result);
	 }
	 /**
	  * 兑换红包
	  */
	 public function exchange(){
	    global_run();		
		init_app_page();

		$data = request_api("uc_ecv","exchange",array());

	    if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
		    app_redirect(wap_url("index","user#login"));
		}
        
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->assign("ecv_list",$data['data']);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("index","uc_ecv"));
		$GLOBALS['tmpl']->display("uc_ecv_exchange.html");
	 }
	 
	 public function do_snexchange(){
	     global_run();
	     init_app_page();
	     $sn = strim($_REQUEST['sn']);
	     if($sn==''){
	         $data['status'] = 0;
	         $data['info'] = '口令不能为空';
	         ajax_return($data);
	     }
	     $data = request_api("uc_ecv","do_snexchange",array('sn'=>$sn));
	     
	     if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
	         $data['status'] = 0;
	         $data['jump'] = wap_url("index","user#login");
	     }
	     
	     ajax_return($data);
	 }
	 
	 public function do_exchange(){
	     global_run();
	     init_app_page();
	     $id = intval($_REQUEST['id']);
	     $data = request_api("uc_ecv","do_exchange",array('id'=>$id));
	 
	     if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
	         $data['status'] = 0;
	         $data['jump'] = wap_url("index","user#login");
	     }
	 
	     ajax_return($data);
	 }
	 
	 public function load_ecv_exchange_list(){
	     global_run();
	     init_app_page();
	     $data = request_api("uc_ecv","load_ecv_exchange_list",array('page'=>intval($_REQUEST['page'])));
	      
	     if($data['user_login_status']!=LOGIN_STATUS_NOLOGIN){
	         $result['status']=-1;
	         $result['jump'] = wap_url("index","user#login");
	     }
	      
	     $result['status'] =1;
	     if($data['page']['page']==$data['page']['page_total'])
	         $result['is_lock'] = 1;
	     if($data['data']){
	         $GLOBALS['tmpl']->assign("ecv_list",$data['data']);
	         $result['html'] = $GLOBALS['tmpl']->fetch("load_ecv_exchange_list.html");
	     }
	     ajax_return($result);
	 }
}
?>