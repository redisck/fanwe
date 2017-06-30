<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_collectModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$param['page'] = intval($_REQUEST['page']);
		$data = request_api("uc_collect","index",$param);
		
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		$data['collect_list']=$data['goods_list'];
		foreach($data['collect_list'] as $k=>$v){
			$data['collect_list'][$k]['url']= wap_url("index","deal",array("data_id"=>$v['id']));			
		}
		if(isset($data['page']) && is_array($data['page'])){
			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		//print_r($data);exit;
		$data['type']="deal";
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("uc_collect.html");
	}
	
	public function youhui_collect()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$param['page'] = intval($_REQUEST['page']);
		$data = request_api("uc_collect","youhui_collect",$param);
		
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
		}
		$data['collect_list']=$data['youhui_list'];
		foreach($data['collect_list'] as $k=>$v){
			$data['collect_list'][$k]['url']= wap_url("index","youhui",array("data_id"=>$v['id']));
			$data['collect_list'][$k]['sub_name']=$v['name'];
			$data['collect_list'][$k]['brief']=$v['list_brief'];
			$data['collect_list'][$k]['buy_count']=$v['down_count'];					
		}
		if(isset($data['page']) && is_array($data['page'])){
			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		//print_r($data);exit;
		$data['type']="youhui";
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("uc_collect.html");
	}
	

	public function event_collect()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$param['page'] = intval($_REQUEST['page']);
		$data = request_api("uc_collect","event_collect",$param);
		
		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
			app_redirect(wap_url("index","user#login"));
			
		}
		$data['collect_list']=$data['event_list'];
		foreach($data['collect_list'] as $k=>$v){
			$data['collect_list'][$k]['url']= wap_url("index","event",array("data_id"=>$v['id']));	
			$data['collect_list'][$k]['sub_name']=$v['name'];		
			$data['collect_list'][$k]['buy_count']=$v['submit_count'];	
		}
		if(isset($data['page']) && is_array($data['page'])){
			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		//print_r($data);exit;
		$data['type']="event";
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("uc_collect.html");
	}	
	
}
?>