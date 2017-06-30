<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dc_bizModule extends MainBaseModule
{
	
	/**
	 * 	商家管理首页
	 */  
	public function index()
	{		
	    /* 基本参数初始化 */  
		
		global_run();
		init_app_page();
		
		$data = request_api("dc_biz","index");

		if ($data['biz_user_status']==0){ //用户未登录
			/*
			//设置当前页面为前一页面
			$url=wap_url('index','dc_biz');
			es_session::set("wap_gopreview",$url);
			*/
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{

			foreach($data['order_info'] as $k=>$v){
				$data['order_info'][$k]['url']=wap_url("biz","dc_biz#view",array('lid'=>$v['id']));
			}
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->display("dc/biz/dc_biz.html");
		}
		 

	}

	
	
	/**
	 * 	商家管理内页
	 *
	 */
	public function view()
	{

		global_run();
		init_app_page();
		$param['lid'] = intval($_REQUEST['lid']);
		$data = request_api("dc_biz","view",$param);
		
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{

			if($data['status']==0){
				showErr($data['info'],0,wap_url("index","dc_biz"));
			}
			
			$GLOBALS['tmpl']->assign("data",$data);
			if($data['open_time_info']){
				$open_time_html=$this->get_open_time_html($data['open_time_info']);
				$GLOBALS['tmpl']->assign("open_time_html",$open_time_html);
			}

			$GLOBALS['tmpl']->display("dc/biz/dc_view.html");
		}
			
	}
	
	

	
	/**
	 * 	商家结算接口，把未结算金额变为可提现金额
	 *  
	 */
	public function dc_supplier_balance(){

		$data = request_api("dc_biz","dc_supplier_balance");
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
			$result['status']=$data['status'];
			$result['info']=$data['info'];
			ajax_return($result);
		}
			
	
	}
	
	
	/**
	 * 	商家内页保存按钮的提交地址
	 *
	 */
	public function save(){
	
	
		$param['lid'] = intval($_REQUEST['lid']);
		$param['is_close'] = intval($_REQUEST['is_close']);
		$param['tel'] = strim($_REQUEST['tel']);

		$param['begin_time_h'] = $_REQUEST['begin_time_h'];
		$param['begin_time_m'] = $_REQUEST['begin_time_m'];
		$param['end_time_h'] = $_REQUEST['end_time_h'];
		$param['end_time_m'] = $_REQUEST['end_time_m'];	
		
		$data = request_api("dc_biz","save",$param);
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
			$result['status']=$data['status'];
			$result['info']=$data['info'];
			ajax_return($result);
		}
			
	
	}
	
	public function get_open_time_html($open_time_arr){
		
		$ajax=intval($_REQUEST['ajax']);
		$hour=array();
		for($i=0;$i<24;$i++){
			$hour[]=str_pad($i,2,0,STR_PAD_LEFT);
		}
			
		$min_sen=array();
		for($i=0;$i<60;$i++){
			$min_sen[]=str_pad($i,2,0,STR_PAD_LEFT);
		}
		$GLOBALS['tmpl']->assign("open_time_arr",$open_time_arr);
		$GLOBALS['tmpl']->assign("hour",$hour);
		$GLOBALS['tmpl']->assign("min_sen",$min_sen);
		$open_time_html=$GLOBALS['tmpl']->fetch('dc/biz/dc_biz_open_time.html');
		if($ajax==0){
			return $open_time_html;
		}elseif($ajax==1){
			ajax_return($open_time_html);
		}
		
	}
	

	
}
?>