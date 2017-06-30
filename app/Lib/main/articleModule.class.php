<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class articleModule extends MainBaseModule
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
			$site_nav[] = array('name'=>$GLOBALS['lang']['ARTICLE'],'url'=>url("index","acate"));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);	
		
			$id = intval($_REQUEST['act']);
			if($id==0)app_redirect(url("index","acate"));	
			
			$article = $GLOBALS['db']->getRow("select a.*,ac.type_id from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.id = ".$id." and a.is_effect = 1 and a.is_delete = 0");		
			
			
			$cate_tree = get_acate_tree('',0,"acate");	
			$GLOBALS['tmpl']->assign("acate_tree",$cate_tree);	
			
			if(!$article||$article['type_id']!=0){
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
			$GLOBALS['tmpl']->assign("cur_title",$GLOBALS['lang']['ARTICLE']);
			$seo_title = $article['seo_title']!=''?$article['seo_title']:$article['title'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $article['seo_keyword']!=''?$article['seo_keyword']:$article['title'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $article['seo_description']!=''?$article['seo_description']:$article['title'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			
		}
		$GLOBALS['tmpl']->display("notice_index.html",$cache_id);
		
	}
	
	

	
	
	public function system_article()
	{
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME."system_article".trim($_REQUEST['id']).$GLOBALS['city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('system_article.html', $cache_id)){
	
		
			$id = intval($_REQUEST['id']);
			if($id==0)app_redirect(APP_ROOT."/");	
			
			$article = $GLOBALS['db']->getRow("select a.*,ac.type_id from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.id = ".$id." and a.is_effect = 1 and a.is_delete = 0");		
			
			if(!$article||$article['type_id']!=3){
				app_redirect(APP_ROOT."/");
			}
			
			$GLOBALS['tmpl']->assign("article",$article);
			$seo_title = $article['seo_title']!=''?$article['seo_title']:$article['title'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $article['seo_keyword']!=''?$article['seo_keyword']:$article['title'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $article['seo_description']!=''?$article['seo_description']:$article['title'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
			
		}		
		$GLOBALS['tmpl']->display("system_article.html",$cache_id);
		
	}
	
	
}
?>