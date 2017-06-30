<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH."app/Lib/main/core/dc_init.php";

class dcresModule extends MainBaseModule
{

	/**
	 * 预订商家列表页
	 * 
	 * 输入：
	 * page:int 当前的页数
	 * cid：int 商家分类id
	 * aid：int  区id	,如鼓楼区 ，aid是qid的上一级
	 * qid：int  商圈id，如五一广场
	 * 
	 * 输出：
	 * advs: array 首页广告
	 * city_id：int 城市ID
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page_keyword:string 页面关键词
	 * page_description:string 页面描述
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * bcate_list:array:array，商家分类，结构如下
		 Array
                (
                    [0] => Array
                        (
                            [url] => /o2onew/index.php?ctl=dc
                            [name] => 全部
                            [current] => 1
                        )
                )
	 * 
	 * quan_list:区列表，如鼓楼区 ，台江区，quan_sub为该区下面的商圈列表，如五一广场，东街口
	 *  Array
        (
            [1] => Array
                (
                    [id] => 8
                    [name] => 鼓楼区
                    [url] => /o2onew/index.php?ctl=dcres&cid=2&aid=8
                    [quan_sub] => Array
                        (
                            [0] => Array
                                (
                                    [id] => 13
                                    [name] => 五一广场
                                    [pid] => 8
                                )

                            [1] => Array
                                (
                                    [id] => 14
                                    [name] => 东街口
                                    [pid] => 8
                                )
                        )

                )
         ) 
         
                   
      dc_location_list：array:array 商家列表，结构如下
      	其他有用字段
      	preview：图片
      	distance：距离,单位为米
      	
          Array
        (
            [0] => Array
                (
                    [id] => 28
                    [name] => 石山水美式餐厅（东街店）
                    [route] => 
                    [address] => 鼓楼区东街14号闽辉大厦1楼
                    [tel] => 059188855588
                    [contact] => 李四
                    [xpoint] => 119.307134
                    [ypoint] => 26.092442
                    [supplier_id] => 28
                    [open_time] => 9:00-22:00
                    [brief] => 
                    [is_main] => 1
                    [api_address] => 
                    [city_id] => 15
                    .
                    .
                    .
                    .
                    .
                  )
         )      
	 * 
	 */
	
	public function index()
	{		
		global_run();
		dc_global_run();
		//init_app_page();	
		require_once APP_ROOT_PATH."system/model/dc.php";
		$root = array();
		$tname='sl';
		if($GLOBALS['kw'])
		{
			$ext_condition.=" and ".$tname.".name like '%".$GLOBALS['kw']."%' ";
		}
		
		//参数处理
		$deal_city_id = intval($GLOBALS['city']['id']);
		$page = intval($GLOBALS['request']['page']);
		if($page==0){
			$page = 1;
		}
		$deal_cate_id = intval($GLOBALS['request']['cid']);
		if($deal_cate_id)$url_param['cid'] = $deal_cate_id;
		
		$deal_area_id = intval($GLOBALS['request']['aid']);
		
		if($deal_area_id)$url_param['aid'] = $deal_area_id;
		
		$deal_quan_id = intval($GLOBALS['request']['qid']);
		if($deal_quan_id)$url_param['qid'] = $deal_quan_id;
		$param=array("cid"=>$deal_cate_id,"aid"=>$deal_area_id,"qid"=>$deal_quan_id,"city_id"=>$deal_city_id);
		
		//seo元素
		$page_title = "预订";
		$page_keyword = "预订";
		$page_description = "预订";
		
		$area_result = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));	 //商圈缓存
		$cate_list = load_auto_cache("cache_dc_cate"); //分类缓存
		
		$cache_param = array("cid"=>$deal_cate_id,"aid"=>$deal_area_id,"qid"=>$deal_quan_id,"city_id"=>intval($GLOBALS['city']['id']));
		$filter_nav_data = load_auto_cache("dc_mapi_filter_res_nav_cache",$cache_param);
		
		
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
			
			$bcate_list = $filter_nav_data['bcate_list'];
			foreach($bcate_list as $k=>$v){
				$bcate_list[$k]['icon_img']=get_abs_img_root(get_spec_image($v['icon_img'],140,85,1));
			}
		/* 开始地区搜索 */

		$quan_list = $filter_nav_data['bquan_list'];
		

		//分页
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;

		//获取餐厅列表
		$dc_location_list  = get_dc_location_list($type='is_res',$limit,$param,$tag=array(), $ext_condition,$sort_field='',$field_append='');

		if($GLOBALS['db']->getAll($dc_location_list['condition'])){
			$total = count($GLOBALS['db']->getAll($dc_location_list['condition']));
		}else{
			$total=0;
		}
		
		foreach($dc_location_list['list'] as $k=>$v){
				
			$dc_location_list['list'][$k]['url']=url('index','dcbuy',array('lid'=>$v['id']));
			$dc_location_list['list'][$k]['preview']=get_abs_img_root(get_spec_image($v['preview'],360,270,1));
			$dc_location_list['list'][$k]['distance']=$v['distance']*1000;
				
		}
		
		
		//广告
		$city_id = $GLOBALS['city']['id'];
		$city_name =  $GLOBALS['city']['name'];
		
		$root['city_id'] = $city_id;
		$root['city_name'] = $city_name;
		$adv_list = $GLOBALS['cache']->get("MOBILE_INDEX_ADVS_".intval($city_id));
		//广告列表
		if($adv_list===false)
		{
			$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '0' and  position=0 and city_id in (0,".intval($city_id).") and status = 1 order by sort desc ";
			$advs = $GLOBALS['db']->getAll($sql);
		
		
			$adv_list = array();
			foreach($advs as $k=>$v)
			{
				$adv_list[$k]['id'] = $v['id'];
				$adv_list[$k]['name'] = $v['name'];
				$adv_list[$k]['img'] = get_abs_img_root($v['img']);  //首页广告图片规格为 宽: 640px 高: 240px
				$adv_list[$k]['type'] = $v['type'];
				$adv_list[$k]['data'] = $v['data'] = unserialize($v['data']);
				$adv_list[$k]['ctl'] = $v['ctl'];
			}
			$GLOBALS['cache']->set("MOBILE_INDEX_ADVS_".intval($city_id),$adv_list,300);
		}
		if($deal_quan_id){
			
			$quan_info=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."area where id=".$deal_quan_id);
		}else{
			
			$quan_info=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."area where id=".$deal_area_id);
			
		}
		$cate_info=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."dc_cate where id=".$deal_cate_id);
		$root['quan_info'] = $quan_info;
		$root['cate_info'] = $cate_info;
		$root['advs'] = $adv_list?$adv_list:array();
		$page_total = ceil($total/$page_size);
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$total);
		$root['page_title']=$page_title;
		$root['page_keyword']=$page_keyword;
		$root['page_description']=$page_description;
		$root['bcate_list']=$bcate_list;
		$root['quan_list']=$quan_list;

		$root['dc_location_list']=array_values($dc_location_list['list']);


		output($root);

	}

	


	
}
?>