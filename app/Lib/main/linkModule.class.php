<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class linkModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);		
		if (!$GLOBALS['tmpl']->is_cached('link_index.html', $cache_id))	
		{
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
			$site_nav[] = array('name'=>$GLOBALS['lang']['FRIEND_LINK'],'url'=>url("index","acate"));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
			$p_link_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link_group where is_effect = 1 order by sort desc");
			foreach($p_link_group as $k=>$v)
			{
				$g_links = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link where is_effect = 1 and group_id = ".$v['id']." order by sort desc");
				if($g_links)
				{
					foreach($g_links as $kk=>$vv)
					{
						if(substr($vv['url'],0,7)=='http://')
						{
							$g_links[$kk]['url'] = str_replace("http://","",$vv['url']);
						}
						
					}
					$p_link_group[$k]['links'] = $g_links;
				}
				else
				unset($p_link_group[$k]);
			}
			$GLOBALS['tmpl']->assign("click_url",url('index','link#go'));
			$GLOBALS['tmpl']->assign("p_link_data",$p_link_group);			
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['FRIEND_LINK']);
			
		}
		$GLOBALS['tmpl']->display("link_index.html",$cache_id);
		
	}
	
	public function go()
	{	
		$url = strim($_REQUEST['url']);
		$link_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."link where (url = '".$url."' or url = 'http://".$url."') and is_effect = 1");
		if($link_item)
		{
			if(check_ipop_limit(get_client_ip(),"Link",10,$link_item['id']))
			$GLOBALS['db']->query("update ".DB_PREFIX."link set count = count + 1 where id = ".$link_item['id']);
		}
	}	
	
	
}
?>