<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class noticeModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['act']));		
		if (!$GLOBALS['tmpl']->is_cached('notice_index.html', $cache_id)){
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
			$site_nav[] = array('name'=>$GLOBALS['lang']['SITE_NOTICE_LIST'],'url'=>url("index","news"));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);	
		
			$id = intval($_REQUEST['act']);
			if($id==0)app_redirect(url("index","news"));	
			
			$article = $GLOBALS['db']->getRow("select a.*,ac.type_id from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.id = ".$id." and a.is_effect = 1 and a.is_delete = 0");		
			
			
			$cate_tree = get_acate_tree('',2,"news");	
			$GLOBALS['tmpl']->assign("acate_tree",$cate_tree);	
			
			if(!$article||$article['type_id']!=2){
				app_redirect(APP_ROOT."/");
			}else{				
				if($article['rel_url']!=''){
					if(!preg_match ("/http:\/\//i", $article['rel_url'])){
						if(substr($article['rel_url'],0,2)=='u:')	{
							app_redirect(parse_url_tag($article['rel_url']));
						}else{
							app_redirect(APP_ROOT."/".$article['rel_url']);
						}						
					}else{
						app_redirect($article['rel_url']);
					}					
				}
			}

			$GLOBALS['tmpl']->assign("article",$article);
			$GLOBALS['tmpl']->assign("cur_id",$article['cate_id']);
			$GLOBALS['tmpl']->assign("cur_title",$GLOBALS['lang']['SITE_NOTICE_LIST']);
			$seo_title = $article['seo_title']!=''?$article['seo_title']:$article['title'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $article['seo_keyword']!=''?$article['seo_keyword']:$article['title'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $article['seo_description']!=''?$article['seo_description']:$article['title'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			
		}
		$GLOBALS['tmpl']->display("notice_index.html",$cache_id);
		
	}
	
	
	
	
}
?>