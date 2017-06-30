<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class uc_medalModule extends MainBaseModule
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
		
		$user_id = intval($GLOBALS['user_info']['id']);
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."medal where is_effect = 1 ");
		foreach($list as $k=>$v){
			$list[$k]['url']=url("index","uc_medal#load_medal",array("id"=>$v['id']));
		}		
		$GLOBALS['tmpl']->assign('list',$list);		
		
		$my_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_medal where user_id = ".$user_id." and is_delete = 0 order by create_time desc");
		$GLOBALS['tmpl']->assign('my_list',$my_list);	

		$GLOBALS['tmpl']->assign("page_title","会员勋章");
		assign_uc_nav_list();//左侧导航菜单	
		$GLOBALS['tmpl']->display("uc/uc_medal_index.html");
		
	}
	
	public function load_medal()
	{
		$id = intval($_REQUEST['id']);
		$medal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."medal where id = ".$id);
		$medal['url']=url("index","uc_medal#get_medal",array("id"=>$medal['id']));
		$GLOBALS['tmpl']->assign("medal",$medal);
		$GLOBALS['tmpl']->display("inc/load_medal.html");
	}

	public function get_medal()
	{
		global_run();
		$id = intval($_REQUEST['id']);
		$medal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."medal where id = ".$id);
		$file = APP_ROOT_PATH."system/medal/".$medal['class_name']."_medal.php";
		$cls = $medal['class_name']."_medal";
		$result['status'] = 0;
		$result['info'] = "勋章不存在";
		
		if(file_exists($file))
		{
			require_once $file;
			if(class_exists($cls))
			{
				$o = new $cls;
				$result = $o->get_medal();
			}
		}
		ajax_return($result);
	}	
	
}
?>