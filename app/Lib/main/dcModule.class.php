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
class dcModule extends MainBaseModule
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
		if(isset($GLOBALS['geo']['address']) && strim($GLOBALS['city']['name'])==strim($s_info['city_name'])){
	
		$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示
		$GLOBALS['tmpl']->assign("is_login_reload","1");
		$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		require_once APP_ROOT_PATH."app/Lib/page.php";
		if($user_id>0){
		$collect_location=get_user_location_collect();
		}
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
		}
		
		
		
		//参数处理
		$deal_city_id = intval($GLOBALS['city']['id']);
		
		$deal_cate_id = intval($_REQUEST['cid']);
		$ptag = intval($_REQUEST['ptag']);
		if($deal_cate_id)$url_param['cid'] = $deal_cate_id;
		if($ptag)$url_param['ptag'] = $ptag;
		
		$param=array("cid"=>$deal_cate_id,"city_id"=>$deal_city_id);
		
		//seo元素
		$page_title = "外卖";
		$page_keyword = "外卖";
		$page_description = "外卖";
		
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));	 //商圈缓存
		$cate_list = load_auto_cache("cache_dc_cate"); //分类缓存
		
		$cache_param = array("cid"=>$deal_cate_id,"city_id"=>intval($GLOBALS['city']['id']));
		$filter_nav_data = load_auto_cache("dc_filter_dc_nav_cache",$cache_param);

		if(($deal_cate_id>0&&$cate_list[$deal_cate_id]))
			$filter_row_data['nav_list'][] = array("current"=>array("name"=>"全部","url"=>url("index","dc"))); //全部
		if($deal_cate_id>0&&$cate_list[$deal_cate_id]) //有大分类
		{
			$filter_row = array();
			$tmp_url_param = $url_param;
			unset($tmp_url_param['cid']);
			$filter_row['current'] = array("name"=>$cate_list[$deal_cate_id]['name'],"cancel"=>url("index","dc",$tmp_url_param));
			$filter_row['list'] = $filter_nav_data['bcate_list'];
			$filter_row_data['nav_list'][] = $filter_row;
				
			$page_title = $cate_list[$deal_cate_id]['name']." - ".$page_title;
			$page_keyword = $page_keyword.",".$cate_list[$deal_cate_id]['name'];
			$page_description = $page_description.",".$cate_list[$deal_cate_id]['name'];
				
		
		}

			//输出大分类
			$filter_row_data['filter_list'][] = array("list"=>$filter_nav_data['bcate_list']);
	
		
		/**
		 * 外卖促销规则
		 * dc_online_pay：是否在线支付，值定为1
		 * dc_allow_cod：支持货到付款，值定为2
		 * is_firstorderdiscount：是否支持新单立减，值定为3
		 * is_payonlinediscount：是否支持在线支付优惠，值定为4
		 * dc_allow_ecv：支持代金卷，值定为5
		 * dc_allow_invoice：是否支持发票，值定为6
		 * 
		 * 
		 */
		
		for($t=1;$t<=6;$t++)
		{
			$checked = false;
			if(($ptag&pow(2,$t))==pow(2,$t))
			{
			$checked = true;
			}
			$tmp_url_param = $url_param;
			$tmp_url_param['ptag'] = $ptag^pow(2,$t);
			
			$ptags[] = array(
			"name"	=>	lang("DC_PTAG_".$t),
					"checked"	=>	$checked,
					"url"	=>	url("index","dc",$tmp_url_param)
					);
		}
		$ext_condition .= " and ".$tname.".dc_ptag&".$ptag."=".$ptag." ";
		$filter_row_data['promote']=$ptags;	
		$GLOBALS['tmpl']->assign("filter_row_data",$filter_row_data);

		$promote_info=get_dc_promote_info();
		$GLOBALS['tmpl']->assign("promote_info",$promote_info);
		$GLOBALS['tmpl']->assign("page_title",$page_title);
		$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
		$GLOBALS['tmpl']->assign("page_description",$page_description);
		
		//分页
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		
		
		//获取餐厅列表
		$dc_location_list  = get_dc_location_list($type='is_dc',$limit,$param,$tag=array(), $ext_condition,$sort_field,$field_append);
		$total = count($GLOBALS['db']->getAll($dc_location_list['condition']));
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);

		$location_delivery_info=get_location_delivery_info($dc_location_list['id_arr']);

		foreach($dc_location_list['list'] as $k=>$v){
				
			$dc_location_list['list'][$k]['url']=url('index','dcbuy',array('lid'=>$v['id']));
			if($location_delivery_info[$k]){
				$dc_location_list['list'][$k]['location_delivery_info']=$location_delivery_info[$k];	
			}
			
			if($user_id>0){
				if(in_array($v['id'],$collect_location['id'])){
					$dc_location_list['list'][$k]['is_collected']=1;
				}else{
					$dc_location_list['list'][$k]['is_collected']=0;
				}
			}
				
		}

		$GLOBALS['tmpl']->assign('dc_location_list',$dc_location_list['list']);
		$GLOBALS['tmpl']->display("dc/dc_location.html");
		
		}else{		
			$GLOBALS['tmpl']->display("dc/dc_position.html");
		
		}

	}
	public function do_position(){
		global_run();
		$dc_search['dc_xpoint']=floatval($_REQUEST['xpoint']);
		$dc_search['dc_ypoint']=floatval($_REQUEST['ypoint']);
		$dc_search['dc_title']=strim($_REQUEST['dc_title']);
		$dc_search['dc_content']=strim($_REQUEST['dc_content']);
		$dc_search['dc_num']=intval($_REQUEST['dc_num']);
		$dc_search['city_name']=$GLOBALS['city']['name'];
		$dc_search_history_str=es_cookie::get('dc_search_history');
		$dc_search_history=array();
	    $dc_search_history=json_decode($dc_search_history_str,true);
	    $search_key=md5($dc_search['dc_xpoint'].$dc_search['dc_ypoint'].$dc_search['dc_title'].$dc_search['dc_content']); 
	    $dc_search_history_new[$search_key]=$dc_search;
	    foreach($dc_search_history as $k=>$v){
	    	if($k!=$search_key){	
	    	$dc_search_history_new[$k]=$v;
	    	}
	    }
		es_cookie::set('dc_search_history', json_encode($dc_search_history_new),3600*24*7);
		$result['status']= 1;
		ajax_return($result);

	}
	
	public function clear(){
		require_once APP_ROOT_PATH.'system/model/city.php';
		City::clear_geo();
		app_redirect(url('index','dc'));
	}
	public function clear_history(){

		es_cookie::delete('dc_search_history');
		$data['status']= 1;
		ajax_return($data);
	}
	
}
?>