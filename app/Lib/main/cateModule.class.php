<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class cateModule extends MainBaseModule
{
	public function index()
	{	

		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示
		
		require_once APP_ROOT_PATH."system/model/deal.php";
		
		//浏览历史
		$history_ids = get_view_history("shop");
		//浏览历史
		if($history_ids)
		{
			$ids_conditioin = " d.id in (".implode(",", $history_ids).") ";
			$history_deal_list = get_goods_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
				
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
		$shop_cate_id = intval($_REQUEST['cid']);
		if($shop_cate_id)$url_param['cid'] = $shop_cate_id;		
		
		$brand_id = intval($_REQUEST['bid']);
		if($brand_id)$url_param['bid'] = intval($_REQUEST['bid']);
		
		foreach($_REQUEST as $k=>$v)
		{
			if(preg_match("/fid_(\d+)/i", $k,$matches))
			{
				$url_param[$matches[0]] = strim($v);
			}
		}
		

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
		$condition = " buy_type <> 1 and is_shop = 1 "; //商品且不为积分商品
		
		//seo元素
		$page_title = "商城";
		$page_keyword = "商城";
		$page_description = "商城";
		
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
		
		$cate_cache = load_auto_cache("cache_shop_cate",array("all"=>1)); //商城分类缓存

		
		//获取品牌
		if($shop_cate_id>0)
		{
			$cate_key = load_auto_cache("shop_cate_key",array("cid"=>$shop_cate_id));
			$brand_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."brand where match(tag_match) against('".$cate_key."' IN BOOLEAN MODE)  order by sort limit 100");
			if($brand_id>0&&$brand_list)
			{
				$brand_info = $GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."brand where id = ".$brand_id);
			}
		}
		
		if(($shop_cate_id>0&&$cate_cache[$shop_cate_id])||($brand_id>0&&$brand_list))
		$filter_row_data['nav_list'][] = array("current"=>array("name"=>"全部","url"=>url("index","cate"))); //全部
		
		if($shop_cate_id>0&&$cate_cache[$shop_cate_id]) //有分类
		{
			if($cate_cache[$shop_cate_id]['pid']==0)
			{
				//选中大分类
				$bcate_info = $cate_cache[$shop_cate_id];
				$bcate_list = load_auto_cache("cache_shop_cate",array("pid"=>0));
				$filter_row = array();
				unset($tmp_url_param['cid']);
				$filter_row['current'] = array("name"=>$cate_cache[$shop_cate_id]['name'],"cancel"=>url("index","cate"));
				$filter_row['list'] = $bcate_list;
				$filter_row_data['nav_list'][] = $filter_row;
				$scate_list = load_auto_cache("cache_shop_cate",array("pid"=>$shop_cate_id));
			}
			else
			{
				//选中小分类
				$scate_info = $cate_cache[$shop_cate_id];
				$bcate_info = $cate_cache[$scate_info['pid']];
				$bcate_list = load_auto_cache("cache_shop_cate",array("pid"=>0));
				$filter_row = array();
				$filter_row['current'] = array("name"=>$bcate_info['name'],"cancel"=>url("index","cate"));
				$filter_row['list'] = $bcate_list;
				$filter_row_data['nav_list'][] = $filter_row;
				$scate_list = load_auto_cache("cache_shop_cate",array("pid"=>$bcate_info['id']));
			}
			
			
			//输出小分类			
			if($scate_list)
			{
				$tmp_url_param = $url_param;
				$tmp_url_param['cid'] = $bcate_info['id'];
				if($scate_info)					
					$scate_list_out[] = array("name"=>"全部","url"=>url("index","cate",$tmp_url_param));
				else
					$scate_list_out[] = array("name"=>"全部","url"=>url("index","cate",$tmp_url_param),"current"=>true);
				foreach($scate_list as $k=>$v)
				{
					if($v['id']==$shop_cate_id)
					{
						$v['current'] = true;
					}
					$scate_list_out[] = $v;
				}
				$filter_row_data['filter_list'][] = array("name"=>"分类","list"=>$scate_list_out);				
			}
				
			if($bcate_info)
			{
				$page_title = $bcate_info['name']." - ".$page_title;
				$page_keyword = $page_keyword.",".$bcate_info['name'];
				$page_description = $page_description.",".$bcate_info['name'];
			}
			if($scate_info)
			{
				$page_title = $scate_info['name']." - ".$page_title;
				$page_keyword = $page_keyword.",".$scate_info['name'];
				$page_description = $page_description.",".$scate_info['name'];
			}
				
			
		}
		else
		{
			//输出大分类
			$bcate_list = load_auto_cache("cache_shop_cate",array("pid"=>0));
			$filter_row_data['filter_list'][] = array("name"=>"分类","list"=>$bcate_list);
		} //有分类结束
		
		//关于品牌筛选
		if($brand_list)
		{
			$brand_list_bak = $brand_list;
			$brand_list = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['bid']);
			$brand_list[] = array("name"=>"全部","url"=>url("index","cate",$tmp_url_param),"current"=>true);
			foreach($brand_list_bak as $k=>$v)
			{
				$tmp_url_param = $url_param;
				$tmp_url_param['bid'] = $v['id'];
				$v['url'] = url("index","cate",$tmp_url_param);
				$brand_list[]  = $v;
			}
		}			
		if($brand_info)//有品牌
		{
			//选中大分类
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['bid']);
			$filter_row['current'] = array("name"=>$brand_info['name'],"cancel"=>url("index","cate",$tmp_url_param));
			$filter_row['list'] = $brand_list;
			$filter_row_data['nav_list'][] = $filter_row;
			

			$page_title = $brand_info['name']." - ".$page_title;
			$page_keyword = $page_keyword.",".$brand_info['name'];
			$page_description = $page_description.",".$brand_info['name'];
		}
		else
		{
			if($brand_list)
			{
				//输出品牌
				$filter_row_data['filter_list'][] = array("name"=>"品牌","list"=>$brand_list);
			}
		}
		//end 品牌
		
		//获取筛选分组			
		$cate_info = $cate_cache[$shop_cate_id];
		$filter_group = load_auto_cache("cache_shop_filter_group",array("cid"=>$cate_info['id']));

		foreach($filter_group as $k=>$v)
		{
			$filter_list = $v['filter_list'];
			$filter_list_bak = $filter_list;
			$filter_list = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['fid_'.$v['id']]);			
			if(empty($url_param['fid_'.$v['id']]))
				$filter_list[] = array("name"=>"全部","url"=>url("index","cate",$tmp_url_param),"current"=>true);
			else
				$filter_list[] = array("name"=>"全部","url"=>url("index","cate",$tmp_url_param));
			foreach($filter_list_bak as $kk=>$vv)
			{
				$tmp_url_param = $url_param;
				$tmp_url_param['fid_'.$v['id']] = $vv['name'];
				$vv['url'] = url("index","cate",$tmp_url_param);
				if($url_param['fid_'.$v['id']]==$vv['name'])
				{
					$vv['current'] = true;
				}
				$filter_list[]  = $vv;
			}
			
			$filter_row_data['filter_list'][] = array("name"=>$v['name'],"list"=>$filter_list);
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
		$sort_list[] = array("name"=>"默认排序","current"=>$current,"url"=>url("index","cate",$tmp_url_param));
		
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
		$sort_list[] = array("name"=>"价格","key"=>"current_price","type"=>$c_sort_type,"current"=>$current,"url"=>url("index","cate",$tmp_url_param));
		
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
		$sort_list[] = array("name"=>"销量","key"=>"buy_count","type"=>$c_sort_type,"current"=>$current,"url"=>url("index","cate",$tmp_url_param));
		
		$sort_row_data['sort'] = $sort_list;
		
		//标签筛选 
		/* 
		多套餐 2 t
		随时退 6 t 
		七天退 7 t
		免运费 8 t
		满立减 9 t
		*/
		$tag_condition = "";
		for($t=1;$t<10;$t++)		
		{			
			if($t!=1&&$t!=3&&$t!=4&&$t!=5)
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
						"url"	=>	url("index","cate",$tmp_url_param)
				);
			}
			
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
		$price_range[] = array("name"=>"全部价格","url"=>url("index","cate",$tmp_url_param),"selected"=>$all_selected);
		
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
			$price_range[] = array("name"=>$range_name,"url"=>url("index","cate",$tmp_url_param),"selected"=>$current_selected);
		}
		
		if($price_level[$pr-1])
		{			
			$price_range_item = $price_level[$pr-1];	
					
			if($price_range_item['min']>0)
				$ext_condition .= " and d.current_price >= ".$price_range_item['min']." ";
			
			if($price_range_item['max']>0)
				$ext_condition .= " and d.current_price <= ".$price_range_item['max']." ";
		}
		
		$ext_condition.=" and  d.buy_type <> 1 and d.is_shop = 1 ";
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
		$deal_result  = get_goods_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),$condition_param,"",$ext_condition,$sort_field);
		
		$deal_list = $deal_result['list'];		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d where ".$deal_result['condition'],false);
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		
		$side_deal_result  = get_goods_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE,DEAL_NOTICE),array("city_id"=>$GLOBALS['city']['id']),"", " d.buy_type <> 1 and d.is_shop = 1 "," d.buy_count desc ");
		$side_deal_list = $side_deal_result['list'];
		$GLOBALS['tmpl']->assign('side_deal_list',$side_deal_list);
		
		$GLOBALS['tmpl']->assign("page_title",$page_title);
		$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
		$GLOBALS['tmpl']->assign("page_description",$page_description);		
		
		$GLOBALS['tmpl']->display("cate.html");
	}
	
	
}
?>