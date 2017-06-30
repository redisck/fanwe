<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class uc_reviewModule extends MainBaseModule
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
		$GLOBALS['tmpl']->assign("page_title","我的点评");
		assign_uc_nav_list();
		
		
		//begin review
		require_once APP_ROOT_PATH."system/model/review.php";
		require_once APP_ROOT_PATH."app/Lib/page.php";

		//分页
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$dp_res = get_dp_list($limit,""," user_id = ".$GLOBALS['user_info']['id']);
		$dp_list = $dp_res['list'];
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp  where ".$dp_res['condition']);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		foreach($dp_list as $k=>$v)
		{
			if($v['deal_id']>0)
				$data_info = load_auto_cache("deal",array("id"=>$v['deal_id']));
			elseif($v['youhui_id']>0)
				$data_info = load_auto_cache("youhui",array("id"=>$v['youhui_id']));
			elseif($v['event_id']>0)
				$data_info = load_auto_cache("event",array("id"=>$v['event_id']));
			if(empty($data_info))
				$data_info = load_auto_cache("store",array("id"=>$v['supplier_location_id']));
			$dp_list[$k]['data_info'] = $data_info;
		}
		$GLOBALS['tmpl']->assign('dp_list',$dp_list);
		require_once APP_ROOT_PATH."system/model/topic.php";
		global $no_lazy;
		$no_lazy = true;
		$review_html = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/uc_review_list.html"));
		$GLOBALS['tmpl']->assign("review_html",$review_html);
		//end review
		
		$no_lazy = false;
		$GLOBALS['tmpl']->display("uc/uc_review_index.html");
	}
	
}
?>