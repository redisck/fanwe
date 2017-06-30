<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class storesModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		init_app_page();
		require_once APP_ROOT_PATH."system/model/supplier.php";
		//浏览历史
		$history_ids = get_view_history("store");
		//浏览历史
		if($history_ids)
		{
			$ids_conditioin = " sl.id in (".implode(",", $history_ids).") ";
			$history_deal_list = get_location_list(app_conf("SIDE_DEAL_COUNT"),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
		
				
			//重新组装排序
			$history_list = array();
			foreach($history_ids as $k=>$v)
			{
				foreach($history_deal_list['list'] as $history_item)
				{
					if($history_item['id']==$v)
					{
						$history_list[] = $history_item;
					}
				}
			}
		
			$GLOBALS['tmpl']->assign("history_deal_list",$history_list);
		}
		
		//参数处理
		$deal_cate_id = intval($_REQUEST['cid']);
		if($deal_cate_id)$url_param['cid'] = $deal_cate_id;
		
		$deal_type_id = intval($_REQUEST['tid']);
		if($deal_type_id)$url_param['tid'] = $deal_type_id;
		
		$deal_area_id = intval($_REQUEST['aid']);
		if($deal_area_id)$url_param['aid'] = $deal_area_id;
		
		$deal_quan_id = intval($_REQUEST['qid']);
		if($deal_quan_id)$url_param['qid'] = $deal_quan_id;
		
		$supplier_id = intval($_REQUEST['supplier_id']);
		if($supplier_id)$url_param['supplier_id'] = $supplier_id;
		
		$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);

		if(empty($supplier_info))
		{
			$url_param['supplier_id'] = $supplier_id = 0;
		}
		else
		{
			
			$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$GLOBALS['city']['id']));
			if($ids)
			{
				$tcondition .= " and city_id in (".implode(",",$ids).") and deal_cate_id>0 ";
			}
			
			$location_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id.$tcondition);
			$GLOBALS['tmpl']->assign("supplier_info",$supplier_info);
			$GLOBALS['tmpl']->assign("location_count",$location_count);
			$GLOBALS['tmpl']->assign("current_city",$GLOBALS['city']);
		}
		
		$sort_name = strim($_REQUEST["sort"]);
		if($sort_name!="good_rate")$sort_name="";
		if($sort_name)$url_param['sort'] = $sort_name;
		
		$sort_type = strim($_REQUEST['type'])=="asc"?"asc":"desc";
		if($_REQUEST['type'])$url_param['type'] = $sort_type;
		
		
		if(isset($_REQUEST['v']))
		{//1: 认证商家
		$vtype = intval($_REQUEST['v']);
		$url_param['v'] = $vtype;
		}
		
		if($GLOBALS['kw'])
		{
			$url_param['kw'] = $GLOBALS['kw'];
		}
		
		//条件初始化
		$condition = " 1=1 ";
		
		//输出自定义的filter_row
		/* array(
		 "nav_list"=>array(
		 		array( //导航类型的切换
		 				"current"=>array("name"=>'xxx',"url"=>"当前的地址","cancel"=>"取消的地址"),
		 				"list"=>array(
		 						array("name"=>"xxx","url"=>"xxx")
		 				)
		 		)
		 ),
				"filter_list"=>array( //列表类型的切换
						array(
								"name"=>"分类",
								"list"	=> array(
										array("name"=>"xxx","url"=>"xxx")
								)
						)
				)
					
		); */
		
		
		
		
		//seo元素
		$page_title = "商家列表";
		$page_keyword = "商家列表";
		$page_description = "商家列表";
		
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));	 //商圈缓存
		$cate_list = load_auto_cache("cache_deal_cate"); //分类缓存
		
		
		$cache_param = array("cid"=>$deal_cate_id,"tid"=>$deal_type_id,"aid"=>$deal_area_id,"qid"=>$deal_quan_id,"supplier_id"=>$supplier_id,"city_id"=>intval($GLOBALS['city']['id']));
		$filter_nav_data = load_auto_cache("store_filter_nav_cache",$cache_param);
		if(($deal_cate_id>0&&$cate_list[$deal_cate_id])||($deal_area_id>0&&$area_result[$deal_area_id]&&$area_result[$deal_area_id]['pid']==0))
			$filter_row_data['nav_list'][] = array("current"=>array("name"=>"全部","url"=>url("index","stores",array("supplier_id"=>$supplier_id)))); //全部
		if($deal_cate_id>0&&$cate_list[$deal_cate_id]) //有大分类
		{
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['cid']);
			unset($tmp_url_param['tid']);
			$filter_row['current'] = array("name"=>$cate_list[$deal_cate_id]['name'],"cancel"=>url("index","stores",$tmp_url_param));
			$filter_row['list'] = $filter_nav_data['bcate_list'];
			$filter_row_data['nav_list'][] = $filter_row;
				
			//输出小分类
			if($filter_nav_data['scate_list'])
				$filter_row_data['filter_list'][] = array("name"=>"分类","list"=>$filter_nav_data['scate_list']);
				
			$page_title = $cate_list[$deal_cate_id]['name']." - ".$page_title;
			$page_keyword = $page_keyword.",".$cate_list[$deal_cate_id]['name'];
			$page_description = $page_description.",".$cate_list[$deal_cate_id]['name'];
				
			$type_list = load_auto_cache("cache_deal_cate_type",array("cate_id"=>$deal_cate_id));
			if($deal_type_id>0&&$type_list[$deal_type_id]) //有小分类
			{
				$page_title = $type_list[$deal_type_id]['name']." - ".$page_title;
				$page_keyword = $page_keyword.",".$type_list[$deal_type_id]['name'];
				$page_description = $page_description.",".$type_list[$deal_type_id]['name'];
			}
		}
		else
		{
			//输出大分类
			$filter_row_data['filter_list'][] = array("name"=>"分类","list"=>$filter_nav_data['bcate_list']);
		}
		if($deal_area_id>0&&$area_result[$deal_area_id]&&$area_result[$deal_area_id]['pid']==0) //有大商圈
		{
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['qid']);
			unset($tmp_url_param['aid']);
			$filter_row['current'] = array("name"=>$area_result[$deal_area_id]['name'],"cancel"=>url("index","stores",$tmp_url_param));
			$filter_row['list'] = $filter_nav_data['bquan_list'];
			$filter_row_data['nav_list'][] = $filter_row;
				
			//输出小商圈
			if($filter_nav_data['squan_list'])
				$filter_row_data['filter_list'][] = array("name"=>"商圈","list"=>$filter_nav_data['squan_list']);
				
			$page_title = $area_result[$deal_area_id]['name']." - ".$page_title;
			$page_keyword = $page_keyword.",".$area_result[$deal_area_id]['name'];
			$page_description = $page_description.",".$area_result[$deal_area_id]['name'];
				
			if($deal_quan_id>0&&$area_result[$deal_quan_id]&&$area_result[$deal_quan_id]['pid']<>0) //有小商圈
			{
				$page_title = $area_result[$deal_quan_id]['name']." - ".$page_title;
				$page_keyword = $page_keyword.",".$area_result[$deal_quan_id]['name'];
				$page_description = $page_description.",".$area_result[$deal_quan_id]['name'];
			}
		}
		else
		{
			//输出大商圈
			$filter_row_data['filter_list'][] = array("name"=>"地区","list"=>$filter_nav_data['bquan_list']);
		}
		
		$GLOBALS['tmpl']->assign("filter_row_data",$filter_row_data);
		
		
		
		//输出排序
		$sort_row_data = array();
		/* $sort_row_data = array(
		 "sort"	=> array(
		 		array("name"=>"xxx","key"=>"xxx","type"=>"desc|asc","url"=>"xxx","current"=>"true|false")
		 ),
				"range"	=> array(
						array
						(
								array("name"=>"xxx","url"=>"xxx","selected"=>"true|false"),
								array("name"=>"xxx","url"=>"xxx","selected"=>"true|false"),
								array("name"=>"xxx","url"=>"xxx","selected"=>"true|false"),
								array("name"=>"xxx","url"=>"xxx","selected"=>"true|false"),
						)
				),
				"tag"	=> array(
						array("name"=>"xxx","url"=>"xxx","checked"=>"true|false")
				)
		); */
		
		//默认排序
		$tmp_url_param = $url_param;
		unset($tmp_url_param['type']);
		unset($tmp_url_param['sort']);
		if(empty($url_param['sort']))
			$current = true;
		else
			$current = false;
		$sort_list[] = array("name"=>"默认排序","current"=>$current,"url"=>url("index","stores",$tmp_url_param));
		
		//价格排序
		$tmp_url_param = $url_param;
		if($tmp_url_param['sort']=="good_rate")
		{
			if($tmp_url_param['type']=="desc")
			{
				$tmp_url_param['type'] = "asc";
				$c_sort_type = "desc";
			}
			else
			{
				$tmp_url_param['type'] = "desc";
				$c_sort_type = "asc";
			}
			$current = true;
		}
		else
		{
			$tmp_url_param['sort'] = "good_rate";
			$tmp_url_param['type'] = "desc";
			$c_sort_type = "desc";
			$current = false;
		}
		$sort_list[] = array("name"=>"好评率","key"=>"good_rate","type"=>$c_sort_type,"current"=>$current,"url"=>url("index","stores",$tmp_url_param));
		
		
		$sort_row_data['sort'] = $sort_list;
		
		//标签筛选
		//0减免：满立减 1:代金券
		$tag_condition = "";
		for($t=1;$t<=1;$t++)
		{
			$checked = false;
			if(isset($url_param['v'])&&$url_param['v']==$t)
			{
				$checked = true;
			}
			$tmp_url_param = $url_param;
			if(isset($tmp_url_param['v'])&&$tmp_url_param['v']==$t)
				unset($tmp_url_param['v']);
			else
				$tmp_url_param['v'] = $t;
				
			$dtags[] = array(
					"name"	=>	lang("STORE_VERIFY_".$t),
					"checked"	=>	$checked,
					"url"	=>	url("index","stores",$tmp_url_param)
			);
		}
		if(isset($url_param['v']))
			$ext_condition = " sl.is_verify = '".$url_param['v']."' ";
		$sort_row_data['tag'] = $dtags;
		
		
		
		$GLOBALS['tmpl']->assign("sort_row_data",$sort_row_data);
		
		//开始获取商家列表
		//获取排序条件
		if($url_param['sort'])$sort_field = "sl.".$url_param['sort']." ".$url_param['type'];
		
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		$condition_param = $url_param;
		$condition_param['city_id'] = $GLOBALS['city']['id'];
		
		if($GLOBALS['kw'])
		{
			$kw_unicode = str_to_unicode_string($GLOBALS['kw']);
	
			if($ext_condition!="")
			$ext_condition.=" and ";
			$ext_condition.=" ( sl.name like '%".$GLOBALS['kw']."%' or match(sl.tags_match) against('".$kw_unicode."' IN BOOLEAN MODE) ) ";
		}
		$store_result = get_location_list($limit,$condition_param,"",$ext_condition,$sort_field);		
		$store_list = $store_result['list'];

		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location as sl where ".$store_result['condition']);
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('store_list',$store_list);
		
		$side_store_result  = get_location_list(app_conf("SIDE_DEAL_COUNT"),array("supplier_id"=>$supplier_id,"cid"=>$deal_cate_id,"city_id"=>$GLOBALS['city']['id']),"", ""," sl.good_rate desc ");
		$side_store_list = $side_store_result['list'];
		$GLOBALS['tmpl']->assign('side_store_list',$side_store_list);

		
		$GLOBALS['tmpl']->assign("page_title",$page_title);
		$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
		$GLOBALS['tmpl']->assign("page_description",$page_description);
		
		$GLOBALS['tmpl']->display("stores_index.html");
	}
	
	
	public function brand()
	{	
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['city']['id']);
		if (!$GLOBALS['tmpl']->is_cached('store_brand.html', $cache_id)){
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
			$site_nav[] = array('name'=>$GLOBALS['lang']['STORE_BRAND'],'url'=>url("index","stores#brand"));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			$title = $GLOBALS['lang']['STORE_BRAND'];
			
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc limit 10");
			$city_id = intval($GLOBALS['city']['id']);
			if($city_id>0){			
					$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));				
					if($ids)
					$city_condition = " and city_id in (".implode(",",$ids).")";	
			}			
			
			foreach($cate_list as $k=>$v){
				$cate_list[$k]['store_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location where deal_cate_id = ".$v['id']." and is_effect = 1 and is_recommend = 1 $city_condition limit 15");
				foreach($cate_list[$k]['store_list'] as $kk=>$vv){
					$cate_list[$k]['store_list'][$kk]['url'] = url("index","store#".$vv['id']);
				}
			}			
			
			$GLOBALS['tmpl']->assign("cate_list",$cate_list);	
			$GLOBALS['tmpl']->assign("page_title",$title);	
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");		
			$GLOBALS['tmpl']->assign("page_description",$title.",");			
			
		}
		
		$GLOBALS['tmpl']->display("stores_brand.html",$cache_id);
	}
	
	
}
?>