<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dccollectModule extends MainBaseModule
{
	public function index()
	{	

		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示

		
		require_once APP_ROOT_PATH."system/model/dc.php";
		$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
		if($user_id==0)
			app_redirect(url('index','user#login'));
		
		
		if(!isset($GLOBALS['geo']['address']))
			app_redirect(url('index','dcposition'));
		
		/* 获取最新搜索名 */
		$s_info=get_lastest_search_name();
		$GLOBALS['tmpl']->assign("s_info",$s_info);
		
		$GLOBALS['tmpl']->assign("user_id",$user_id);	
		require_once APP_ROOT_PATH."app/Lib/page.php";
		
		//开始身边团购的地理定位
		$ypoint =  $GLOBALS['geo']['ypoint'];  //ypoint
		$xpoint =  $GLOBALS['geo']['xpoint'];  //xpoint
		$address = $GLOBALS['geo']['address'];
		
		$tname='sl';
		if($GLOBALS['kw'])
		{
			$ext_condition.=" and ".$tname.".name like '%".$GLOBALS['kw']."%' ";
		}
		
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r)/1000 as distance ";
				
				$sort_field = " distance asc ";
				
			$GLOBALS['tmpl']->assign("geo",$GLOBALS['geo']);
		}
		


		//seo元素
		$page_title = "餐厅收藏";
		$page_keyword = "餐厅收藏";
		$page_description = "餐厅收藏";

		$GLOBALS['tmpl']->assign("page_title",$page_title);
		$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
		$GLOBALS['tmpl']->assign("page_description",$page_description);		
		
		//分页
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");

		
		
		//获取餐厅列表

		$idarr=$GLOBALS['db']->getALL("select location_id from ".DB_PREFIX."dc_location_sc where user_id=".$user_id);
		$id=array();
		foreach($idarr as $item){
			
			$id[]=$item['location_id'];
		}
		
		$dc_location_collect_info=$GLOBALS['db']->getALL("select ".$tname.".* ".$field_append." from ".DB_PREFIX."supplier_location as ".$tname." where ".$tname.".id in (".implode(',',$id).") order by ".$tname.".is_close limit ".$limit);
		
		$total = count($idarr);
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$dc_location_list=array();
		$dc_location_collect=array();
		foreach($dc_location_collect_info as $kk=>$vv){
			$dc_location_collect[$vv['id']]=$vv;
			$dc_location_list[$vv['id']]['id']=$vv['id'];
			$dc_location_list[$vv['id']]['distance']=$vv['distance'];
		}

		$location_delivery_info=get_location_delivery_info($dc_location_list);
		

		foreach($dc_location_collect as $k=>$v){
			
			if($location_delivery_info[$k]){
				$dc_location_collect[$k]['location_delivery_info']=$location_delivery_info[$k];
			}
			$dc_location_collect[$k]['url']=url('index','dcbuy',array('lid'=>$v['id']));
	
		}
		
		$promote_info=get_dc_promote_info();
		$GLOBALS['tmpl']->assign("promote_info",$promote_info);
		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);
		$GLOBALS['tmpl']->assign('dc_location_list',$dc_location_collect);
		
		$GLOBALS['tmpl']->display("dc/dc_location_collect.html");
	}
	


	
}
?>