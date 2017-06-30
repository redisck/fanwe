<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
require APP_ROOT_PATH.'system/model/article.php';
class newsModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['act']).intval($_REQUEST['p']));		
		if (!$GLOBALS['tmpl']->is_cached('notice_list.html', $cache_id))	
		{
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
			$site_nav[] = array('name'=>$GLOBALS['lang']['SITE_NOTICE_LIST'],'url'=>url("index","news"));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
			$id = intval($_REQUEST['act']);
			$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."article_cate where id = ".$id." and is_effect = 1 and is_delete = 0");
			
			if($id>0&&!$cate_item){
				app_redirect(APP_ROOT."/");
			}elseif($cate_item&&$cate_item['type_id']!=2){
				if($cate_item['type_id']==1)app_redirect(url("index","help"));
				if($cate_item['type_id']==0)app_redirect(url("index","acate"));
				if($cate_item['type_id']==3)app_redirect(APP_ROOT."/");
			}			

			$cate_id = intval($cate_item['id']);
			$cate_tree = get_acate_tree('',2,"news");	
			$GLOBALS['tmpl']->assign("acate_tree",$cate_tree);	

			//分页
			$page = intval($_REQUEST['p']);
			if($page<=0)	$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");	
			$result = get_article_list($limit,$cate_id,'ac.type_id = 2','',false);
			
			$GLOBALS['tmpl']->assign("list",$result['list']);
			$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
			
			$GLOBALS['tmpl']->assign("cur_id",$cate_id);
			$GLOBALS['tmpl']->assign("cur_title",$GLOBALS['lang']['SITE_NOTICE_LIST']);
			$GLOBALS['tmpl']->assign("page_title",$cate_item['title']);
			$GLOBALS['tmpl']->assign("page_keyword",$cate_item['title'].",");
			$GLOBALS['tmpl']->assign("page_description",$cate_item['title'].",");
			
		}
		$GLOBALS['tmpl']->display("notice_list.html",$cache_id);
		
	}
	
	

	
	
}
?>