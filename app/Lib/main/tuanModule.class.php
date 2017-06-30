<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class tuanModule extends MainBaseModule
{
	public function index()
	{	

		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示

		require_once APP_ROOT_PATH."system/model/deal.php";
		
		//浏览历史
		$history_ids = get_view_history("deal");		
			
		//浏览历史
		if($history_ids)
		{
			$ids_conditioin = " d.id in (".implode(",", $history_ids).") ";		
			$history_deal_list = get_deal_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
			
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
		

		$sort_name = strim($_REQUEST["sort"]);
		if($sort_name!="current_price"&&$sort_name!="buy_count")$sort_name="";
		if($sort_name)$url_param['sort'] = $sort_name;
		
		$sort_type = strim($_REQUEST['type'])=="asc"?"asc":"desc";
		if($_REQUEST['type'])$url_param['type'] = $sort_type;
		
		$dtag = intval($_REQUEST['dtag']);
		if($dtag)$url_param['dtag'] = $dtag;
		
		$pr = intval($_REQUEST['pr']);//价格阶梯序号
		if($pr)$url_param['pr'] = $pr;
		
		if($GLOBALS['kw'])
		{
			$url_param['kw'] = $GLOBALS['kw'];
		}
		
		//条件初始化
		$condition = " buy_type <> 1 and is_shop = 0 "; //团购且不为积分商品
		
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
		$page_title = "团购";
		$page_keyword = "团购";
		$page_description = "团购";
		
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));	 //商圈缓存	
		$cate_list = load_auto_cache("cache_deal_cate"); //分类缓存
		
		
		$cache_param = array("cid"=>$deal_cate_id,"tid"=>$deal_type_id,"aid"=>$deal_area_id,"qid"=>$deal_quan_id,"city_id"=>intval($GLOBALS['city']['id']));
		$filter_nav_data = load_auto_cache("tuan_filter_nav_cache",$cache_param);
		if(($deal_cate_id>0&&$cate_list[$deal_cate_id])||($deal_area_id>0&&$area_result[$deal_area_id]&&$area_result[$deal_area_id]['pid']==0))
		$filter_row_data['nav_list'][] = array("current"=>array("name"=>"全部","url"=>url("index","tuan"))); //全部 
		if($deal_cate_id>0&&$cate_list[$deal_cate_id]) //有大分类
		{
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['cid']);
			unset($tmp_url_param['tid']);
			$filter_row['current'] = array("name"=>$cate_list[$deal_cate_id]['name'],"cancel"=>url("index","tuan",$tmp_url_param));
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
			$filter_row['current'] = array("name"=>$area_result[$deal_area_id]['name'],"cancel"=>url("index","tuan",$tmp_url_param));
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
		$sort_list[] = array("name"=>"默认排序","current"=>$current,"url"=>url("index","tuan",$tmp_url_param));
		
		//价格排序
		$tmp_url_param = $url_param;
		if($tmp_url_param['sort']=="current_price")
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
			$tmp_url_param['sort'] = "current_price";
			$tmp_url_param['type'] = "desc";
			$c_sort_type = "desc";
			$current = false;
		}
		$sort_list[] = array("name"=>"价格","key"=>"current_price","type"=>$c_sort_type,"current"=>$current,"url"=>url("index","tuan",$tmp_url_param));
		
		//销量排序
		$tmp_url_param = $url_param;
		if($tmp_url_param['sort']=="buy_count")
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
			$tmp_url_param['sort'] = "buy_count";
			$tmp_url_param['type'] = "desc";
			$c_sort_type = "desc";
			$current = false;
		}
		$sort_list[] = array("name"=>"销量","key"=>"buy_count","type"=>$c_sort_type,"current"=>$current,"url"=>url("index","tuan",$tmp_url_param));
		
		$sort_row_data['sort'] = $sort_list;
		
		//标签筛选 1 - 6
		/* 
		 免预约 1 t
		多套餐 2 t
		可订座 3 t
		代金券 4 t
		过期退 5 t
		随时退 6 t 
		*/
		$tag_condition = "";
		for($t=1;$t<=6;$t++)		
		{			
			$checked = false;
			if(($dtag&pow(2,$t))==pow(2,$t))
			{
				$checked = true;		
			}
			$tmp_url_param = $url_param;
			$tmp_url_param['dtag'] = $dtag^pow(2,$t);
			
			$dtags[] = array(
				"name"	=>	lang("DEAL_TAG_".$t),
				"checked"	=>	$checked,
				"url"	=>	url("index","tuan",$tmp_url_param)
			);
		}	
		$ext_condition = " d.deal_tag&".$dtag."=".$dtag." ";
		$sort_row_data['tag'] = $dtags;
		
		
		//价格区间
		$price_level = array(
				array("min"=>"0","max"=>"100"),
				array("min"=>"100","max"=>"500"),
				array("min"=>"500","max"=>"2000"),
				array("min"=>"2000","max"=>"0")
		);
		
		$tmp_url_param = $url_param;
		unset($tmp_url_param['pr']);
		$all_selected = false;
		if(empty($url_param['pr']))
			$all_selected = true;
		$price_range[] = array("name"=>"全部价格","url"=>url("index","tuan",$tmp_url_param),"selected"=>$all_selected);
		
		foreach($price_level as $k=>$v)
		{
			$tmp_url_param = $url_param;
			$tmp_url_param['pr'] = $k+1;
			$current_selected = false;
			if($url_param['pr']==($k+1))
			{
				$current_selected = true;
			}
			if($v['min']==0&&$v['max']>0)
			{
				$range_name = $v['max']."元以下";
			}
			else if($v['max']==0&&$v['min']>0)
			{
				$range_name = $v['min']."元以上";
			}
			else 
			{
				$range_name = $v['min']."-".$v['max']."元";
			}
			$price_range[] = array("name"=>$range_name,"url"=>url("index","tuan",$tmp_url_param),"selected"=>$current_selected);
		}
		
		if($price_level[$pr-1])
		{			
			$price_range_item = $price_level[$pr-1];	
					
			if($price_range_item['min']>0)
				$ext_condition .= " and d.current_price >= ".$price_range_item['min']." ";
			
			if($price_range_item['max']>0)
				$ext_condition .= " and d.current_price <= ".$price_range_item['max']." ";
		}
		
		$ext_condition.=" and  d.buy_type <> 1 and d.is_shop = 0 ";
		$sort_row_data['range'][] = $price_range;
		$GLOBALS['tmpl']->assign("sort_row_data",$sort_row_data);
		
		//开始获取商品
		//获取排序条件 
		if($url_param['sort'])$sort_field = "d.".$url_param['sort']." ".$url_param['type'];
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		$condition_param = $url_param;
		$condition_param['city_id'] = $GLOBALS['city']['id'];
		
		if($GLOBALS['kw'])
		{
			$ext_condition.=" and d.name like '%".$GLOBALS['kw']."%' ";
		}
		
		
		//开始身边团购的地理定位
		$ypoint =  $GLOBALS['geo']['ypoint'];  //ypoint
		$xpoint =  $GLOBALS['geo']['xpoint'];  //xpoint
		$address = $GLOBALS['geo']['address'];
		
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((d.ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((d.ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (d.xpoint * $pi) / 180 ) ) * $r) as distance ";
							
			if(empty($sort_field))
			$sort_field = " distance asc ";
			
			$GLOBALS['tmpl']->assign("geo",$GLOBALS['geo']);
		}
		
		$deal_result  = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),$condition_param,"",$ext_condition,$sort_field,$field_append);
		
		$deal_list = $deal_result['list'];		
		
		foreach($deal_list as $k=>$v)
		{
			$distance = $v['distance'];
			$distance_str = "";
			if($distance>0)
			{
				if($distance>1500)
				{
					$distance_str = "距离".round($distance/1000)."公里";
				}
				else
				{
					$distance_str = "距离".round($distance)."米";
				}
			}
			$deal_list[$k]['distance'] = $distance_str;
		}
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d where ".$deal_result['condition'],false);
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		
		$side_deal_result  = get_deal_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE,DEAL_NOTICE),array("city_id"=>$GLOBALS['city']['id']),"", " d.buy_type <> 1 and d.is_shop = 0 "," d.buy_count desc ");
		$side_deal_list = $side_deal_result['list'];
		$GLOBALS['tmpl']->assign('side_deal_list',$side_deal_list);
		
		$GLOBALS['tmpl']->assign("page_title",$page_title);
		$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
		$GLOBALS['tmpl']->assign("page_description",$page_description);		
		
		$GLOBALS['tmpl']->display("tuan.html");
	}
	
	
}
?>