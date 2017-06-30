<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class scoresModule extends MainBaseModule
{
	public function index()
	{	

		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示
		
		
		//参数处理
		$shop_cate_id = intval($_REQUEST['cid']);
		if($shop_cate_id)$url_param['cid'] = $shop_cate_id;		
		
		$brand_id = intval($_REQUEST['bid']);
		if($brand_id)$url_param['bid'] = intval($_REQUEST['bid']);		
	

		$sort_name = strim($_REQUEST["sort"]);
		if($sort_name!="return_score"&&$sort_name!="buy_count")$sort_name="";
		if($sort_name)$url_param['sort'] = $sort_name;
		
		$sort_type = strim($_REQUEST['type'])=="asc"?"asc":"desc";
		if($_REQUEST['type'])$url_param['type'] = $sort_type;		

		
		//seo元素
		$page_title = "积分商城";
		$page_keyword = "积分商城";
		$page_description = "积分商城";
		
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
		$filter_row_data['nav_list'][] = array("current"=>array("name"=>"全部","url"=>url("index","scores"))); //全部
		
		if($shop_cate_id>0&&$cate_cache[$shop_cate_id]) //有分类
		{
			if($cate_cache[$shop_cate_id]['pid']==0)
			{
				//选中大分类
				$bcate_info = $cate_cache[$shop_cate_id];
				$bcate_list = load_auto_cache("cache_shop_cate",array("pid"=>0));
				foreach($bcate_list as $k=>$v)
				{
					$bcate_list[$k]['url'] = $v['score_url'];
				}
				$filter_row = array();
				unset($tmp_url_param['cid']);
				$filter_row['current'] = array("name"=>$cate_cache[$shop_cate_id]['name'],"cancel"=>url("index","scores"));
				$filter_row['list'] = $bcate_list;
				$filter_row_data['nav_list'][] = $filter_row;
				$scate_list = load_auto_cache("cache_shop_cate",array("pid"=>$shop_cate_id));
				foreach($scate_list as $k=>$v)
				{
					$scate_list[$k]['url'] = $v['score_url'];
				}
			}
			else
			{
				//选中小分类
				$scate_info = $cate_cache[$shop_cate_id];
				$bcate_info = $cate_cache[$scate_info['pid']];
				$bcate_list = load_auto_cache("cache_shop_cate",array("pid"=>0));
				foreach($bcate_list as $k=>$v)
				{
					$bcate_list[$k]['url'] = $v['score_url'];
				}
				$filter_row = array();
				$filter_row['current'] = array("name"=>$bcate_info['name'],"cancel"=>url("index","scores"));
				$filter_row['list'] = $bcate_list;
				$filter_row_data['nav_list'][] = $filter_row;
				$scate_list = load_auto_cache("cache_shop_cate",array("pid"=>$bcate_info['id']));
				foreach($scate_list as $k=>$v)
				{
					$scate_list[$k]['url'] = $v['score_url'];
				}
			}
			
			
			//输出小分类			
			if($scate_list)
			{
				$tmp_url_param = $url_param;
				$tmp_url_param['cid'] = $bcate_info['id'];
				if($scate_info)					
					$scate_list_out[] = array("name"=>"全部","url"=>url("index","scores",$tmp_url_param));
				else
					$scate_list_out[] = array("name"=>"全部","url"=>url("index","scores",$tmp_url_param),"current"=>true);
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
			foreach($bcate_list as $k=>$v)
			{
				$bcate_list[$k]['url'] = $v['score_url'];
			}
			$filter_row_data['filter_list'][] = array("name"=>"分类","list"=>$bcate_list);
		} //有分类结束
		

		//关于品牌筛选
		if($brand_list)
		{
			$brand_list_bak = $brand_list;
			$brand_list = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['bid']);
			$brand_list[] = array("name"=>"全部","url"=>url("index","scores",$tmp_url_param),"current"=>true);
			foreach($brand_list_bak as $k=>$v)
			{
				$tmp_url_param = $url_param;
				$tmp_url_param['bid'] = $v['id'];
				$v['url'] = url("index","scores",$tmp_url_param);
				$brand_list[]  = $v;
			}
		}			
		if($brand_info)//有品牌
		{
			//选中大分类
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['bid']);
			$filter_row['current'] = array("name"=>$brand_info['name'],"cancel"=>url("index","scores",$tmp_url_param));
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
		$sort_list[] = array("name"=>"默认排序","current"=>$current,"url"=>url("index","scores",$tmp_url_param));
		
		//价格排序
		$tmp_url_param = $url_param;
		if($tmp_url_param['sort']=="return_score")
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
			$tmp_url_param['sort'] = "return_score";
			$tmp_url_param['type'] = "desc";
			$c_sort_type = "desc";
			$current = false;
		}
		$sort_list[] = array("name"=>"积分","key"=>"return_score","type"=>$c_sort_type,"current"=>$current,"url"=>url("index","scores",$tmp_url_param));
		
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
		$sort_list[] = array("name"=>"销量","key"=>"buy_count","type"=>$c_sort_type,"current"=>$current,"url"=>url("index","scores",$tmp_url_param));
		
		$sort_row_data['sort'] = $sort_list;
		
				
		$ext_condition ="  d.buy_type = 1 and d.is_shop = 1 ";
		$GLOBALS['tmpl']->assign("sort_row_data",$sort_row_data);
		
		//开始获取商品
		//获取排序条件 
		//积分商城积分排序相反
		if($url_param['sort']=="return_score")
		{
			$sort_type = $url_param['type']=="desc"?"asc":"desc";
		}
		else
		{
			$sort_type = $url_param['type'];
		}
		if($url_param['sort'])$sort_field = "d.".$url_param['sort']." ".$sort_type;
		require_once APP_ROOT_PATH."system/model/deal.php";
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		$condition_param = $url_param;
		$condition_param['city_id'] = $GLOBALS['city']['id'];
		

		$deal_result  = get_goods_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),$condition_param,"",$ext_condition,$sort_field);
		
		$deal_list = $deal_result['list'];		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d where ".$deal_result['condition']);
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		
		$side_deal_result  = get_goods_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE,DEAL_NOTICE),array("city_id"=>$GLOBALS['city']['id']),"", " d.buy_type = 1 and d.is_shop = 1 "," d.buy_count desc ");
		$side_deal_list = $side_deal_result['list'];
		$GLOBALS['tmpl']->assign('side_deal_list',$side_deal_list);
		
		$GLOBALS['tmpl']->assign("page_title",$page_title);
		$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
		$GLOBALS['tmpl']->assign("page_description",$page_description);		
		
		$GLOBALS['tmpl']->display("scores.html");
	}
	
	
}
?>