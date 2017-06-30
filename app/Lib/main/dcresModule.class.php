<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH."app/Lib/main/core/dc_init.php";
/**
 * 外卖订餐
 * 
 *
 */
class dcresModule extends MainBaseModule
{

	public function index()
	{		
		global_run();
		dc_global_run();
		init_app_page();	
		require_once APP_ROOT_PATH."system/model/dc.php";
		/* 获取最新搜索名 */
		$s_info=get_lastest_search_name();
		$GLOBALS['tmpl']->assign("s_info",$s_info);

		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉


		
		$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示
		$GLOBALS['tmpl']->assign("is_login_reload","1");
		$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		require_once APP_ROOT_PATH."app/Lib/page.php";
		if($user_id>0){
		$collect_location=get_user_location_collect();
		}

		
		$tname='sl';
		if($GLOBALS['kw'])
		{
			$ext_condition.=" and ".$tname.".name like '%".$GLOBALS['kw']."%' ";
		}
		
		//参数处理
		$deal_city_id = intval($GLOBALS['city']['id']);
		
		$deal_cate_id = intval($_REQUEST['cid']);
		if($deal_cate_id)$url_param['cid'] = $deal_cate_id;
		
		$deal_area_id = intval($_REQUEST['aid']);
		if($deal_area_id)$url_param['aid'] = $deal_area_id;
		
		$deal_quan_id = intval($_REQUEST['qid']);
		if($deal_quan_id)$url_param['qid'] = $deal_quan_id;
		$param=array("cid"=>$deal_cate_id,"aid"=>$deal_area_id,"qid"=>$deal_quan_id,"city_id"=>$deal_city_id);
		
		//seo元素
		$page_title = "预订";
		$page_keyword = "预订";
		$page_description = "预订";
		
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));	 //商圈缓存
		$cate_list = load_auto_cache("cache_dc_cate"); //分类缓存
		
		$cache_param = array("cid"=>$deal_cate_id,"aid"=>$deal_area_id,"qid"=>$deal_quan_id,"city_id"=>intval($GLOBALS['city']['id']));
		$filter_nav_data = load_auto_cache("dc_filter_res_nav_cache",$cache_param);
		
		
		if(($deal_cate_id>0&&$cate_list[$deal_cate_id])||($deal_area_id>0&&$area_result[$deal_area_id]&&$area_result[$deal_area_id]['pid']==0))
			$filter_row_data['nav_list'][] = array("current"=>array("name"=>"全部","url"=>url("index","dcres"))); //全部
		if($deal_cate_id>0&&$cate_list[$deal_cate_id]) //有大分类
		{
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['cid']);
			$filter_row['current'] = array("name"=>$cate_list[$deal_cate_id]['name'],"cancel"=>url("index","dcres",$tmp_url_param));
			$filter_row['list'] = $filter_nav_data['bcate_list'];
			$filter_row_data['nav_list'][] = $filter_row;
				
			//输出小分类
			if($filter_nav_data['scate_list'])
				$filter_row_data['filter_list'][] = array("list"=>$filter_nav_data['scate_list']);
				
			$page_title = $cate_list[$deal_cate_id]['name']." - ".$page_title;
			$page_keyword = $page_keyword.",".$cate_list[$deal_cate_id]['name'];
			$page_description = $page_description.",".$cate_list[$deal_cate_id]['name'];
				
		
		}

			//输出大分类
			$filter_row_data['filter_list'][] = array("list"=>$filter_nav_data['bcate_list']);
		
		
		/* 开始地区搜索 */
		
		 if($deal_area_id>0&&$area_result[$deal_area_id]&&$area_result[$deal_area_id]['pid']==0) //有大商圈
		{
		$filter_row = array();
		$tmp_url_param = $url_param;
		unset($tmp_url_param['qid']);
		unset($tmp_url_param['aid']);
		$filter_row['current'] = array("name"=>$area_result[$deal_area_id]['name'],"cancel"=>url("index","dcres",$tmp_url_param));
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
		
		/* 结束地区搜索 */

		$GLOBALS['tmpl']->assign("filter_row_data",$filter_row_data);
		
		
	
		
		$GLOBALS['tmpl']->assign("page_title",$page_title);
		$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
		$GLOBALS['tmpl']->assign("page_description",$page_description);
		
		//分页
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		
		
		//获取餐厅列表
		$dc_location_list  = get_dc_location_list($type='is_res',$limit,$param,$tag, $ext_condition,$sort_field='',$field_append='');
		$total = count($GLOBALS['db']->getAll($dc_location_list['condition']));
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		foreach($dc_location_list['list'] as $k=>$v){
				
			$dc_location_list['list'][$k]['url']=url('index','dcbuy',array('lid'=>$v['id']));
			

			
			if($user_id>0){
				if(in_array($v['id'],$collect_location['id'])){
					$dc_location_list['list'][$k]['is_collected']=1;
				}else{
					$dc_location_list['list'][$k]['is_collected']=0;
				}
			}
				
		}
		
		$GLOBALS['tmpl']->assign('dc_location_list',$dc_location_list['list']);
		$GLOBALS['tmpl']->display("dc/dc_res.html");


	}

	


	
}
?>