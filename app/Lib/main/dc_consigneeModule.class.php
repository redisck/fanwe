<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class dc_consigneeModule extends MainBaseModule
{
	public function index()
	{
		require APP_ROOT_PATH."system/model/uc_center_service.php";
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}	
		
		$user_id=intval($GLOBALS['user_info']['id']);
		//输出所有配送方式
		$consignee_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_consignee where user_id = ".$user_id);
		foreach($consignee_list as $k=>$v){
			$consignee_list[$k]['dfurl']=url('index','dc_consignee#set_default',array('id'=>$v['id']));	
			$consignee_list[$k]['del_url']=url('index','dc_consignee#del',array('id'=>$v['id']));
		}
		
		
		//print_r($consignee_list);
		$GLOBALS['tmpl']->assign("consignee_list",$consignee_list);
		
		$GLOBALS['tmpl']->assign("page_title","送餐地址");
		assign_uc_nav_list();//左侧导航菜单	
		$GLOBALS['tmpl']->display("dc/dc_consignee.html");
		
	}
	
	public function add()
	{
		global_run();
		init_app_page();
	
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
		if(intval($_REQUEST['id'])>0)
		{ 
			
		$consignee_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_consignee where id = ".intval($_REQUEST['id']));	
		
		$GLOBALS['tmpl']->assign("dc_consignee_info",$consignee_info);
	
		$GLOBALS['tmpl']->assign("consignee_id",intval($_REQUEST['id']));		
			$c_id=$consignee_info['id'];
		}else{
			$c_id=0;
		}
		
		
		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);
		$GLOBALS['tmpl']->assign("page_title","送餐地址");
		$GLOBALS['tmpl']->assign("c_id",$c_id);
		assign_uc_nav_list();//左侧导航菜单	
		$GLOBALS['tmpl']->display("dc/dc_consignee_add.html");	
	}
	
	
	
	public function del(){
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$result['status'] = 2;
			ajax_return($result);	
		}
		
		$count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where user_id=".intval($GLOBALS['user_info']['id']));
		if($count>1){
			$id=intval($_REQUEST['id']);	
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_consignee where id=".$id." and user_id=".intval($GLOBALS['user_info']['id']));
			if($GLOBALS['db']->affected_rows())
			{
				
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_consignee set is_main=1 where user_id=".intval($GLOBALS['user_info']['id'])." order by id desc limit 1");
				showSuccess($GLOBALS['lang']['DELETE_SUCCESS'],1);
			}
			else
			{
				showErr("删除失败",1);
			}
		}else{
			showErr("删除失败",1);
		}
		
	}
	
	
	
	
	
	public function set_default(){
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$result['status'] = 2;
			ajax_return($result);	
		}
		$id=intval($_REQUEST['id']);
		$GLOBALS['db']->query("update ".DB_PREFIX."dc_consignee set is_main=0 where user_id=".intval($GLOBALS['user_info']['id']));
		$GLOBALS['db']->query("update ".DB_PREFIX."dc_consignee set is_main=1 where id=".$id." and user_id=".intval($GLOBALS['user_info']['id']));	
		if($GLOBALS['db']->affected_rows())
		{
			showSuccess("设置成功",1);
		}
		else
		{
			showErr("操作失败",1);
		}	
	}	
	
	
}
?>