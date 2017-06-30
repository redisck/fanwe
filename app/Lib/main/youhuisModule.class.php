<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class youhuisModule extends MainBaseModule
{
	public function index() 
	{		
		global_run();
		init_app_page();
		

		require_once APP_ROOT_PATH."system/model/youhui.php";
		//浏览历史
		$history_ids = get_view_history("youhui");
		//浏览历史
		if($history_ids)
		{
			$ids_conditioin = " y.id in (".implode(",", $history_ids).") ";
			$history_deal_list = get_youhui_list(app_conf("SIDE_DEAL_COUNT"),array(YOUHUI_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
				
			
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
		if($sort_name!="user_count")$sort_name="";
		if($sort_name)$url_param['sort'] = $sort_name;
		
		$sort_type = strim($_REQUEST['type'])=="asc"?"asc":"desc";
		if($_REQUEST['type'])$url_param['type'] = $sort_type;
		
		if(isset($_REQUEST['ytype']))
		{//2减免：满立减 1:代金券
			$ytype = intval($_REQUEST['ytype']);
			$url_param['ytype'] = $ytype;
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
		$page_title = "优惠券";
		$page_keyword = "优惠券";
		$page_description = "优惠券";
		
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));	 //商圈缓存	
		$cate_list = load_auto_cache("cache_deal_cate"); //分类缓存
		
		
		$cache_param = array("cid"=>$deal_cate_id,"tid"=>$deal_type_id,"aid"=>$deal_area_id,"qid"=>$deal_quan_id,"city_id"=>intval($GLOBALS['city']['id']));
		$filter_nav_data = load_auto_cache("youhui_filter_nav_cache",$cache_param);
		if(($deal_cate_id>0&&$cate_list[$deal_cate_id])||($deal_area_id>0&&$area_result[$deal_area_id]&&$area_result[$deal_area_id]['pid']==0))
		$filter_row_data['nav_list'][] = array("current"=>array("name"=>"全部","url"=>url("index","youhuis"))); //全部 
		if($deal_cate_id>0&&$cate_list[$deal_cate_id]) //有大分类
		{
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['cid']);
			unset($tmp_url_param['tid']);
			$filter_row['current'] = array("name"=>$cate_list[$deal_cate_id]['name'],"cancel"=>url("index","youhuis",$tmp_url_param));
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
			$filter_row['current'] = array("name"=>$area_result[$deal_area_id]['name'],"cancel"=>url("index","youhuis",$tmp_url_param));
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
		$sort_list[] = array("name"=>"默认排序","current"=>$current,"url"=>url("index","youhuis",$tmp_url_param));
		
		//价格排序
		$tmp_url_param = $url_param;
		if($tmp_url_param['sort']=="user_count")
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
			$tmp_url_param['sort'] = "user_count";
			$tmp_url_param['type'] = "desc";
			$c_sort_type = "desc";
			$current = false;
		}
		$sort_list[] = array("name"=>"下载量","key"=>"user_count","type"=>$c_sort_type,"current"=>$current,"url"=>url("index","youhuis",$tmp_url_param));
		
		
		$sort_row_data['sort'] = $sort_list;
		
		//标签筛选
		//0减免：满立减 1:代金券
		$tag_condition = "";
		for($i=0;$i<=1;$i++)		
		{			
			$t = $i==1?$i:2;
			$checked = false;
			if(isset($url_param['ytype'])&&$url_param['ytype']==$t)
			{
				$checked = true;		
			}
			$tmp_url_param = $url_param;
			if(isset($tmp_url_param['ytype'])&&$tmp_url_param['ytype']==$t)
				unset($tmp_url_param['ytype']);
			else
				$tmp_url_param['ytype'] = $t;
			
			$dtags[] = array(
				"name"	=>	lang("YOUHUI_TYPE".$i),
				"checked"	=>	$checked,
				"url"	=>	url("index","youhuis",$tmp_url_param)
			);
		}	
		if(isset($url_param['ytype']))
		{
			if($url_param['ytype']==1)
			$ext_condition = " y.youhui_type = '".$url_param['ytype']."' ";
			else 
				$ext_condition = " y.youhui_type = 0 ";
		}
		$sort_row_data['tag'] = $dtags;
		
		
		
		$GLOBALS['tmpl']->assign("sort_row_data",$sort_row_data);
		
		//开始获取优惠券
		//获取排序条件 
		if($url_param['sort'])$sort_field = "y.".$url_param['sort']." ".$url_param['type'];
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		$condition_param = $url_param;
		$condition_param['city_id'] = $GLOBALS['city']['id'];
		
		if($GLOBALS['kw'])
		{
			if($ext_condition!="")
				$ext_condition.=" and ";
			$ext_condition.=" y.name like '%".$GLOBALS['kw']."%' ";
		}
		$youhui_result = get_youhui_list($limit,array(YOUHUI_NOTICE,YOUHUI_ONLINE),$condition_param,"",$ext_condition,$sort_field);

		$youhui_list = $youhui_result['list'];		
		foreach($youhui_list as $k=>$v)
		{
			$youhui_list[$k]['qrcode'] = gen_qrcode(SITE_DOMAIN.$v['url'],3);
		}
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui as y where ".$youhui_result['condition'],false);
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('youhui_list',$youhui_list);
		
		
		$side_youhui_result  = get_youhui_list(app_conf("SIDE_DEAL_COUNT"),array(YOUHUI_ONLINE,YOUHUI_NOTICE),array("city_id"=>$GLOBALS['city']['id']),"", ""," y.user_count desc ");
		$side_youhui_list = $side_youhui_result['list'];
		$GLOBALS['tmpl']->assign('side_youhui_list',$side_youhui_list);
		
		$GLOBALS['tmpl']->assign("page_title",$page_title);
		$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
		$GLOBALS['tmpl']->assign("page_description",$page_description);
		
		$GLOBALS['tmpl']->display("youhuis.html");
	}

}
?>