<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class indexModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		
		init_app_page();
		
		$data = request_api("index","wap");
		foreach($data['advs'] as $k=>$v)
		{
			
			$data['advs'][$k]['url'] =  getWebAdsUrl($v);
		}
		foreach($data['indexs'] as $k=>$v)
		{
			$data['indexs'][$k]['url'] =  getWebAdsUrl($v);
		}
		
		$GLOBALS['tmpl']->assign("data",$data);
		
		if($GLOBALS['geo']['xpoint']>0||$GLOBALS['geo']['ypoint']>0)
		{
			$GLOBALS['tmpl']->assign('has_location',1);
		}
		else
		{
			$GLOBALS['tmpl']->assign('has_location',0);
		}
		
		if (es_cookie::get('is_app_down')){
			$GLOBALS['tmpl']->assign('is_show_down',0);//用户已下载
		}else{
			$GLOBALS['tmpl']->assign('is_show_down',1);//用户未下载
		}		
		
		
		//输出友情链接
		$links = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."link where is_effect = 1 and show_index = 1  order by sort desc");
			
		foreach($links as $kk=>$vv)
		{
			if(substr($vv['url'],0,7)=='http://')
			{
				$links[$kk]['url'] = str_replace("http://","",$vv['url']);
			}
		}			
		
		$GLOBALS['tmpl']->assign("links",$links);
		
		$GLOBALS['tmpl']->display("index.html");
	}
	
	
}
?>