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
	
	/**
	 * 团购列表接口
	 * 输入：
	 * cate_id: int 团购大分类ID
	 * tid: int 团购小分类ID
	 * page:int 当前的页数
	 * keyword: string 关键词
	 * qid: int 商圈ID
	 * order_type: string 排序类型(default(默认)/avg_point(评价倒序)/newest(时间倒序)/buy_count(销量倒序)/price_asc(价格升序)/price_desc(价格降序))
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
	 * item:array:array 团购列表，结构如下
	 *  Array
        (
            [0] => Array
                (
                    [id] => 74 [int] 团购ID
                    [name] => 仅售75元！价值100元的镜片代金券1张，仅适用于镜片，可叠加使用。[string] 团购名称
                    [sub_name] => 镜片代金券 [string] 团购短名称
                    [brief] => 【36店通用】明视眼镜 [string] 团购简介
                    [buy_count] => 1 [int] 销量
                    [current_price] => 75 [float] 现价
                    [origin_price] => 100 [float] 原价
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9d05a1020_140x85.jpg [string] 团购图片 140x85
                    [end_time_format] => 2017-02-28 18:00:08 [string] 格式化的结束时间
                    [begin_time_format] => 2015-02-25 18:00:10 [string] 格式化的开始时间
                    [begin_time] => 1424829610 [int] 开始时间戳
                    [end_time] => 1488247208 [int] 结束时间戳
                    [auto_order] => 1 [int] 免预约 0:否 1:是
                    [is_lottery] => 1 [int] 是否抽奖 0:否 1:是
                    [distance]	=>	[float] 有地理定位时的离当前地的距离(米)
                    [xpoint] => [float] 团购所在经度
                    [ypoint] => [float] 团购所在纬度
                    [is_today] => [int] 是否为今日团购 0否 1是
                )
         )
	 * bcate_list:array 大类列表
	 * 结构如下
	 * Array(
	 * 		Array
	        (
	            [id] => 0 [int]分类ID
	            [name] => 全部分类 [string] 分类名
	            [icon_img] => [string] app端使用的分类图标
	            [iconfont]=> [string] wap端使用的iconfont代码
	            [iconcolor]=> #f0f0f0 [string] 颜色配置 16进度
	            [bcate_type] => Array
	                (
	                    [0] => Array
	                        (
	                            [id] => 0 [int]小分类ID
	                            [cate_id] => 0 [int]父分类ID
	                            [name] => 全部分类 [string] 分类名称
	                        )
	
	                )
	
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
			array("name"=>"销量","code"=>"buy_count"),
			array("name"=>"价格最低","code"=>"price_asc"),
			array("name"=>"价格最高","code"=>"price_desc"),
		);
	 * 
	 */
	public function index()
	{
		//缓存下来的地区配置
		$area_data = load_auto_cache("cache_area",array("city_id"=>$GLOBALS['city']['id']));
		
		$root = array();
		$catalog_id = intval($GLOBALS['request']['cate_id']);//商品分类ID
		$cata_type_id=intval($GLOBALS['request']['tid']);//商品二级分类
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
		$bcate_list = getCateList();
		
		/*输出商圈*/
		$quan_list=getQuanList($city_id);
		
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$ext_condition = " d.buy_type <> 1 and d.is_shop = 0 ";
		if($keyword)
		{
			$ext_condition.=" and d.name like '%".$keyword."%' ";
		}
		
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{		
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((d.ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((d.ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (d.xpoint * $pi) / 180 ) ) * $r) as distance ";
			
			if($ybottom!=0&&$ytop!=0&&$xleft!=0&&$xright!=0)
			{
				if($ext_condition!="")
				$ext_condition.=" and ";
				$ext_condition.= " d.ypoint > $ybottom and d.ypoint < $ytop and d.xpoint > $xleft and d.xpoint < $xright ";
				
				$limit = 300;
			}
			$order = " distance asc ";
		}
		else
			$order = "";

		/*排序  
		 智能排序和 离我最的 是一样的 都以距离来升序来排序，只有这两种情况有传经纬度过来，就没有把 这两种情况写在 下面的判断里，写在上面了。
		default 智能（默认），nearby  离我，avg_point 评价，newest 最新，buy_count 人气，price_asc 价低，price_desc 价高 */
		if($order_type=='avg_point')/*评价*/
			$order= " d.avg_point desc  ";
		elseif($order_type=='newest')/*最新*/
			$order= " d.create_time desc  ";
		elseif($order_type=='buy_count')/*销量*/
			$order= " d.buy_count desc  ";
		elseif($order_type=='price_asc')/*价格升*/
			$order= " d.current_price asc  ";
		elseif($order_type=='price_desc')/*价格降*/
			$order= " d.current_price desc  ";
			
			

		$condition_param = array("cid"=>$catalog_id,"tid"=>$cata_type_id,"aid"=>$area_id,"qid"=>$quan_id,"city_id"=>intval($GLOBALS['city']['id']));
		require_once APP_ROOT_PATH."system/model/deal.php";
		$deal_result  = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),$condition_param,"",$ext_condition,$order,$field_append);
		
		$list = $deal_result['list'];
		$count= $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d where ".$deal_result['condition']);
		
		$page_total = ceil($count/$page_size);
		
		$root = array();

		$goodses = array();
		foreach($list as $k=>$v)
		{
			$goodses[$k] = format_deal_list_item($v);
		}
		
		$root['city_id']= $city_id;
		$root['area_id']= $area_id;
		$root['quan_id']= $quan_id;
		$root['cate_id']=$catalog_id;
	
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="团购列表";
		
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		$root['item'] = $goodses?$goodses:array();
		$root['bcate_list'] = $bcate_list?$bcate_list:array();
		$root['quan_list'] = $quan_list?$quan_list:array();
		
		//排序类型(default(默认)/avg_point(评价倒序)/newest(时间倒序)/buy_count(销量倒序)/price_asc(价格升序)/price_desc(价格降序))
		$root['navs'] = array(
			array("name"=>"默认","code"=>"default"),
			array("name"=>"好评","code"=>"avg_point"),
			array("name"=>"最新","code"=>"newest"),
			array("name"=>"销量","code"=>"buy_count"),
			array("name"=>"价格最低","code"=>"price_asc"),
			array("name"=>"价格最高","code"=>"price_desc"),
		);
		
		output($root);
	}
	
}
?>