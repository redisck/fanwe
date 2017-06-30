<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class eventsModule extends MainBaseModule
{
	
	/**
	 * 活动列表接口
	 * 输入：
	 * cate_id: int 大分类ID
	 * page:int 当前的页数
	 * keyword: string 关键词
	 * qid: int 商圈ID
	 * order_type: string 排序类型(default(默认)/avg_point(评价倒序)/newest(时间倒序)/buy_count(报名量倒序))
	 * 
	 * latitude_top:float 最上边纬线值 ypoint
	 * latitude_bottom:float 最下边纬线值 ypoint
	 * longitude_left:float 最左边经度值  xpoint
	 * longitude_right:float 最右边经度值 xpoint
	 * 
	 * 
	 * 
	 * 输出：
	 * city_id:int 当前城市ID
	 * area_id:int 当前大区ID
	 * quan_id:int 当前商圈ID
	 * cate_id:int 当前大分类ID
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * item:array:array 活动列表，结构如下
	 *  Array
        (
            [0] => Array
                (
                    [id] => 4  [int] 活动ID
                    [name] => 贵安温泉自驾游 [string] 活动名称
                    [icon] => http://localhost/o2onew/public/attachment/201502/26/14/54eec33c40e99_140x85.jpg [string] 活动图片 140x85
                    [submit_begin_time_format] => 2015-02-01 14:54:59 [string] 活动开始时间
                    [submit_end_time_format] => 2020-02-26 14:55:01 [string] 活动结束时间
                    [sheng_time_format] => 04天01小时41分 [string] 倒计时
                    [distance] => [float] 距当前定位的距离(米)
                    [submit_count]=> 10 [int] 报名人数
                    [xpoint] => [float] 所在经度
                    [ypoint] => [float] 所在纬度
                )
         )            
	 * bcate_list:array 大类列表
	 * 结构如下
	 * Array(
	 * 		Array
	        (
	            [id] => 0 [int]分类ID
	            [name] => 全部分类 [string] 分类名	           
	        )
	 )
	 * quan_list:array 商圈列表
	 * 结构如下
	 * Array(
	 * 		Array
	        (
	            [id] => 0 [int] 大区ID
	            [name] => 全城 [string] 大区名称
	            [quan_sub] => Array
	                (
	                    [0] => Array
	                        (
	                            [id] => 0 [int] 小区ID
	                            [pid] => 0 [int] 大区ID
	                            [name] => 全城 [string] 商圈名称
	                        )
	
	                )
	
	        )
	 * )
	 * navs:array 排序菜单 
	 * 固定数据如下
	 * array(
			array("name"=>"默认","code"=>"default"),
			array("name"=>"好评","code"=>"avg_point"),
			array("name"=>"最新","code"=>"newest"),
			array("name"=>"报名量","code"=>"buy_count"),
		);
	 * 
	 */
	public function index()
	{
		//缓存下来的地区配置
		$area_data = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));
		
		$root = array();
		$catalog_id = intval($GLOBALS['request']['cate_id']);//活动分类ID
		$city_id = intval($GLOBALS['city']['id']);//城市分类ID			
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']);
		$page=$page==0?1:$page;
		$quan_id = intval($GLOBALS['request']['qid']); //商圈id	
		$area_id = intval($area_data[$quan_id]['pid']); //大区id
		$order_type=strim($GLOBALS['request']['order_type']);


		$ytop = $latitude_top = floatval($GLOBALS['request']['latitude_top']);//最上边纬线值 ypoint
		$ybottom = $latitude_bottom = floatval($GLOBALS['request']['latitude_bottom']);//最下边纬线值 ypoint
		$xleft = $longitude_left = floatval($GLOBALS['request']['longitude_left']);//最左边经度值  xpoint
		$xright = $longitude_right = floatval($GLOBALS['request']['longitude_right']);//最右边经度值 xpoint
		$ypoint =  $m_latitude = $GLOBALS['geo']['ypoint'];  //ypoint 
		$xpoint = $m_longitude = $GLOBALS['geo']['xpoint'];  //xpoint
		
		
		/*输出分类*/
		$bcate_list = getEventCateList();
		
		/*输出商圈*/
		$quan_list=getQuanList($city_id);
		
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	

		if($keyword)
		{
			$ext_condition ="  e.name like '%".$keyword."%' ";
		}
		
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认），nearby  离我最近*/
		{		
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((e.ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((e.ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (e.xpoint * $pi) / 180 ) ) * $r) as distance ";
			
			if($ybottom!=0&&$ytop!=0&&$xleft!=0&&$xright!=0)
			{
				if($ext_condition!="")
				$ext_condition.=" and ";
				$ext_condition.= " e.ypoint > $ybottom and e.ypoint < $ytop and e.xpoint > $xleft and e.xpoint < $xright ";
				
				$limit = 300;
			}
			$order = " distance asc ";
		}
		else
			$order = "";

		/*排序  */
		if($order_type=='avg_point')/*评价*/
			$order= " e.avg_point desc  ";
		elseif($order_type=='newest')/*最新*/
			$order= " e.submit_begin_time desc  ";
		elseif($order_type=='buy_count')/*报名量量*/
			$order= " e.submit_count desc  ";
			
			

		$condition_param = array("cid"=>$catalog_id,"tid"=>$cata_type_id,"aid"=>$area_id,"qid"=>$quan_id,"city_id"=>intval($GLOBALS['city']['id']));
		require_once APP_ROOT_PATH."system/model/event.php";
		$deal_result  = get_event_list($limit,array(EVENT_NOTICE,EVENT_ONLINE),$condition_param,"",$ext_condition,$order,$field_append);
		
		$list = $deal_result['list'];
		$count= $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event as e where ".$deal_result['condition']);
		
		$page_total = ceil($count/$page_size);
		
		$root = array();

		$goodses = array();
		foreach($list as $k=>$v)
		{
			$goodses[$k] = format_event_list_item($v);
			$goodses[$k]['icon'] = get_abs_img_root(get_spec_image($v['icon'],92,82,1));
		}
		
		$root['city_id']= $city_id;
		$root['area_id']= $area_id;
		$root['quan_id']= $quan_id;
		$root['cate_id']=$catalog_id;
	
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="活动列表";
		
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		$root['item'] = $goodses?$goodses:array();
		$root['bcate_list'] = $bcate_list?$bcate_list:array();
		$root['quan_list'] = $quan_list?$quan_list:array();
		
		//排序类型(default(默认)/avg_point(评价倒序)/newest(时间倒序)/buy_count(销量倒序)/price_asc(价格升序)/price_desc(价格降序))
		$root['navs'] = array(
			array("name"=>"默认","code"=>"default"),
			array("name"=>"好评","code"=>"avg_point"),
			array("name"=>"最新","code"=>"newest"),
			array("name"=>"报名量","code"=>"buy_count")
		);
		
		output($root);
	}
	
}
?>