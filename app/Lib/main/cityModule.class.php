<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class cityModule extends MainBaseModule
{
	public function index()
	{		
		//用于重写模式下的城市定位跳转
		$act = strim($_REQUEST['act']);
		if($act)
		{
			require_once APP_ROOT_PATH."system/model/city.php";
			$_GET['city'] = $act;
			City::locate_city();
			app_redirect(url("index"));
		}
		
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);		
		if (!$GLOBALS['tmpl']->is_cached('city_index.html', $cache_id))	
		{		
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
			$site_nav[] = array('name'=>$GLOBALS['lang']['SWITCH_CITY'],'url'=>url("index","city"));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);		

			$city_lists = load_auto_cache("city_list_result");//print_r($city_lists);
			$GLOBALS['tmpl']->assign("city_lists",$city_lists['zm']);
			$province_list=$GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_city where pid=0 and is_effect=1 order by uname asc");
			foreach($province_list as $k=>$v){				
				$province_new[$v['id']]=$v;
				$province_new[$v['id']]['city_list']  = $GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_city where is_effect=1 and pid = ".$v['id']." order by uname asc");
				foreach($province_new[$v['id']]['city_list']  as $kk=>$vv){		
					$province_new[$v['id']]['city_list'][$kk]['url']	 = url("index","index",array("city"=>$vv['uname']));
				}
			}
			//print_r($province_new);
			$GLOBALS['tmpl']->assign("province_list",$province_new);
			$GLOBALS['tmpl']->assign("city_json",json_encode($province_new));
			$GLOBALS['tmpl']->assign("city_lists_data",$city_lists['ls']);
			
			//输出热门城市
			$hot_city = $GLOBALS['db']->getAll("select id,name,uname from ".DB_PREFIX."deal_city where pid>0 and is_effect = 1 and is_hot = 1 order by uname asc");
			foreach($hot_city as $k=>$v)
			{
				$hot_city[$k]['url'] = url("index","index",array("city"=>$v['uname']));
			}
			$GLOBALS['tmpl']->assign("hot_city",$hot_city);
		}
		
			
		
		$GLOBALS['tmpl']->display("city_index.html",$cache_id);
		
	}
	
	
	
	
}
?>