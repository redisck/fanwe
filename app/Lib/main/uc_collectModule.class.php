<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
require APP_ROOT_PATH."system/model/uc_center_service.php";
class uc_collectModule extends MainBaseModule
{
	public function index()
	{

		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}	
		
		$page = intval($_REQUEST['p']);
		if($page==0)	$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_collect_list($limit,$GLOBALS['user_info']['id']);
		foreach($result['list']  as $k=>$v){
			if($v['uname']!=""){
				$result['list'][$k]['url']=url('index','deal#'.$v['uname']);
			}else{
				$result['list'][$k]['url']=url('index','deal#'.$v['id']);
			}
			
			$result['list'][$k]['del_url']=url('index','uc_collect#del',array('id'=>$v['cid'],'type'=>'deal'));
		}		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);	
		
		$GLOBALS['tmpl']->assign("page_title","我的收藏");
		$GLOBALS['tmpl']->assign("type","deal");
		assign_uc_nav_list();//左侧导航菜单	
		$GLOBALS['tmpl']->display("uc/uc_collect.html");
		
	}

	public function youhui_collect(){
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}	
		
		$page = intval($_REQUEST['p']);
		if($page==0)	$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");	

		$youhui_result = get_youhui_collect($limit,$GLOBALS['user_info']['id']);
		foreach($youhui_result['list']  as $k=>$v){
			$youhui_result['list'][$k]['url']=url('index','youhui#'.$v['id']);
			$youhui_result['list'][$k]['del_url']=url('index','uc_collect#del',array('id'=>$v['cid'],'type'=>'youhui'));
		}		
		$GLOBALS['tmpl']->assign("list",$youhui_result['list']);	
		$page = new Page($youhui_result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);	
		
		$GLOBALS['tmpl']->assign("type","youhui");
		$GLOBALS['tmpl']->assign("page_title","我的收藏");
		assign_uc_nav_list();//左侧导航菜单	
		$GLOBALS['tmpl']->display("uc/uc_collect.html");	
		
	}	
	

	public function event_collect(){
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}	
		
		$page = intval($_REQUEST['p']);
		if($page==0)	$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");	

		$event_result = get_event_collect($limit,$GLOBALS['user_info']['id']);	
		foreach($event_result['list']  as $k=>$v){
			$event_result['list'][$k]['url']=url('index','event#'.$v['id']);
			$event_result['list'][$k]['del_url']=url('index','uc_collect#del',array('id'=>$v['cid'],'type'=>'event'));
		}		
		$GLOBALS['tmpl']->assign("list",$event_result['list']);	
		$page = new Page($event_result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);	
		
		$GLOBALS['tmpl']->assign("type","event");
		$GLOBALS['tmpl']->assign("page_title","我的收藏");
		assign_uc_nav_list();//左侧导航菜单	
		$GLOBALS['tmpl']->display("uc/uc_collect.html");	
	}
	
	
	public function dc_location_collect(){
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
	
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
		require_once APP_ROOT_PATH."system/model/dc.php";
		$page = intval($_REQUEST['p']);
		if($page==0)	$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
	
		$location_collect = get_user_location_collect($limit);
		foreach($location_collect['list']  as $k=>$v){

			$location_collect['list'][$k]['del_url']=url('index','uc_collect#del',array('id'=>$v['link_id'],'type'=>'dc_location'));
		}
		$GLOBALS['tmpl']->assign("list",$location_collect['list']);
		$page = new Page($location_collect['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		$GLOBALS['tmpl']->assign("type","dc_location");
		$GLOBALS['tmpl']->assign("page_title","我的收藏");
		assign_uc_nav_list();//左侧导航菜单
		$GLOBALS['tmpl']->display("uc/uc_collect.html");
	
	}
	
	public function del()
	{	
		global_run();
		$id = intval($_REQUEST['id']);
		$type=strim($_REQUEST['type']);
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$result['status'] = 2;
			ajax_return($result);
		}	

		if($type=='deal'){
			$table='deal_collect';
			$field='user_id';
		}elseif($type=='youhui'){
			$table='youhui_sc';
			$field='uid';
		}elseif($type=='event'){
			$table='event_sc';
			$field='uid';
		}elseif($type=='dc_location'){
				$table='dc_location_sc';
				$field='user_id';
		}else{
			showErr($GLOBALS['lang']['INVALID_COLLECT'],1);
		}
		$GLOBALS['db']->query("delete from ".DB_PREFIX.$table." where id = ".$id." and ".$field." = ".intval($GLOBALS['user_info']['id']));
		if($GLOBALS['db']->affected_rows())
		{
			showSuccess($GLOBALS['lang']['DELETE_SUCCESS'],1);
		}
		else
		{
			showErr($GLOBALS['lang']['INVALID_COLLECT'],1);
		}
	}
	
	
}
?>