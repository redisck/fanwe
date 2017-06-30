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
	
	/**
	 * 积分商品列表接口
	 * 输入：
	 * cate_id: int 商品分类ID
	 * bid: int 品牌ID
	 * page:int 当前的页数
	 * keyword: string 关键词
	 * order_type: string 排序类型(default(默认)/avg_point(评价倒序)/newest(时间倒序)/buy_count(销量倒序)/price_asc(价格升序)/price_desc(价格降序))
	 * 
	 * 
	 * 
	 * 输出：
	 * cate_id:int 当前分类ID
	 * bid:int 当前品牌ID
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * item:array:array 团购列表，结构如下
	 *  Array
        (
            [0] => Array
                (
                    [id] => 74 [int] 商品ID
                    [name] => 仅售75元！价值100元的镜片代金券1张，仅适用于镜片，可叠加使用。[string] 商品名称
                    [sub_name] => 镜片代金券 [string] 商品短名称
                    [brief] => 【36店通用】明视眼镜 [string] 商品简介
                    [buy_count] => 1 [int] 销量
                    [deal_score] => 75 [int] 所需积分
                    [origin_price] => 100 [float] 原价
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9d05a1020_140x85.jpg [string] 团购图片 140x85
                    [end_time_format] => 2017-02-28 18:00:08 [string] 格式化的结束时间
                    [begin_time_format] => 2015-02-25 18:00:10 [string] 格式化的开始时间
                    [begin_time] => 1424829610 [int] 开始时间戳
                    [end_time] => 1488247208 [int] 结束时间戳
                    [is_refund] => [int] 随时退 0:否 1:是
                )
         )
	 * bcate_list:array 大类列表
	 * 结构如下
	 * Array(
	 * 		Array
	        (
	            [id] => 0 [int]分类ID
	            [name] => 全部分类 [string] 分类名
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
	 * brand_list:array 品牌列表
	 * 结构如下
	 * Array(
	 * 		Array
	        (
	            [id] => 0 [int] 品牌ID
	            [name] => xx [string] 品牌名称
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

		$root = array();
		$catalog_id = intval($GLOBALS['request']['cate_id']);//商品分类ID		
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']);
		$page=$page==0?1:$page;
		$brand_id = intval($GLOBALS['request']['bid']); //品牌id	
		$order_type=strim($GLOBALS['request']['order_type']);
		
		
		/*输出分类*/
		$bcate_list = getShopCateList();
		
		/*输出品牌*/
		$brand_list = getBrandList($catalog_id); 
		
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$ext_condition = " d.buy_type = 1 and d.is_shop = 1 ";
		if($keyword)
		{
			$ext_condition.=" and d.name like '%".$keyword."%' ";
		}
		
		
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
			$order= " d.return_score desc  ";
		elseif($order_type=='price_desc')/*价格降*/
			$order= " d.return_score asc  ";
			
			

		$condition_param = array("cid"=>$catalog_id,"bid"=>$brand_id);
		require_once APP_ROOT_PATH."system/model/deal.php";
		$deal_result  = get_goods_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),$condition_param,"",$ext_condition,$order);
		
		$list = $deal_result['list'];
		$count= $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d where ".$deal_result['condition']);
		
		$page_total = ceil($count/$page_size);
		
		$root = array();
		$goodses = array();
		foreach($list as $k=>$v)
		{
			$goodses[$k] = format_deal_list_item($v);
		}

		$root['bid']= $brand_id;
		$root['cate_id']=$catalog_id;
	
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="积分商城";
		
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		$root['item'] = $goodses?$goodses:array();
		$root['bcate_list'] = $bcate_list?$bcate_list:array();
		$root['brand_list'] = $brand_list?$brand_list:array();
		
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