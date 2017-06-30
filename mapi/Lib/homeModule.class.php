<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class homeModule extends MainBaseModule
{
	/*
	 * 获取商家微店设置信息
	 */
	public function load_sp_info()
	{
		$data_s = $GLOBALS['supplier_info'];
		$banner_images = unserialize($data_s['weishop_banner']);
		// 如果没有设置过banner，自动设置一个默认图片
		if($banner_images){
			foreach($banner_images as $k=>$v)
			{
				$banner_images[$k] = get_abs_img_root(get_spec_image($v,320,0));
			}
		}else{
			$default_img = './mapi/image/banner.jpg';
			$banner_images[0] = get_abs_img_root($default_img);
		}
		$data['weishop_banner'] = $banner_images;
		$data['weishop_logo'] = $data_s['weishop_logo']?get_abs_img_root(get_spec_image($data_s['weishop_logo'],75,75,1)):get_abs_img_root(get_spec_image($data_s['preview'],75,75,1));
		$data['weishop_name'] = $data_s['weishop_name']?$data_s['weishop_name']:$data_s['name'];
		return $data;
	}
	
	/**
	 * 团购列表接口
	 * 输入：
	 * spid: int 商家ID
	 * page:int 当前的页数
	 * keyword: string 关键词
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
	 * 
	 */
	public function tuan()
	{
		
		$root = array();
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']);
		$supplier_id = intval($GLOBALS['request']['spid']); //商家id
		$page=$page==0?1:$page;
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$ext_condition = " d.buy_type <> 1 and d.is_shop = 0 and supplier_id=$supplier_id";
		if($keyword)
		{
			$ext_condition.=" and d.name like '%".$keyword."%' ";
		}
		
		$order = "";

		$condition_param = array();
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
		
		$root['supplier_id']= $supplier_id;
	
// 		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
// 		$root['page_title'].="团购列表";
		
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		$root['item'] = $goodses?$goodses:array();
		$sp_info = $this->load_sp_info();
		$root = array_merge($root,$sp_info);
		$root['page_title']=$sp_info['weishop_name'];

		output($root);
	}
	
	
	/**
	 * 商品列表接口
	 * 输入：
	 * spid: int 商家ID
	 * page:int 当前的页数
	 * keyword: string 关键词
	 *
	 *
	 * 输出：
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
	 [current_price] => 75 [float] 现价
	 [origin_price] => 100 [float] 原价
	 [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9d05a1020_140x85.jpg [string] 团购图片 140x85
	 [end_time_format] => 2017-02-28 18:00:08 [string] 格式化的结束时间
	 [begin_time_format] => 2015-02-25 18:00:10 [string] 格式化的开始时间
	 [begin_time] => 1424829610 [int] 开始时间戳
	 [end_time] => 1488247208 [int] 结束时间戳
	 [is_refund] => [int] 随时退 0:否 1:是
	 )
	 )
	 *
	 */
	public function goods()
	{
	
		$root = array();
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']);
		$page=$page==0?1:$page;
		$supplier_id = intval($GLOBALS['request']['spid']); //商家id
	
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$ext_condition = " d.buy_type <> 1 and d.is_shop = 1  and supplier_id=$supplier_id";
		if($keyword)
		{
			$ext_condition.=" and d.name like '%".$keyword."%' ";
		}
	
	
		$order = "";
	
		$condition_param = array();
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
	
// 		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
// 		$root['page_title'].="商品列表";
	
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);

		$root['item'] = $goodses?$goodses:array();
		$sp_info = $this->load_sp_info();
		$root = array_merge($root,$sp_info);
		$root['page_title']=$sp_info['weishop_name'];

		output($root);
	}
	
	/**
	 * 优惠券列表接口
	 * 输入：
	 * spid: int 商家ID
	 * page:int 当前的页数
	 * keyword: string 关键词
	 *
	 *
	 *
	 * 输出：
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * item:array:array 优惠券列表，结构如下
	 *  Array
	 (
	 [0] => Array
	 (
	 [id] => 21 [int] 优惠券ID
	 [distance] => 0 [float] 与当前位置的距离 (米)
	 [name] => 盛世经典牛排50元代金券 [string] 优惠券名称
	 [list_brief] => 盛世经典牛排50元代金券 [string] 优惠券列表介绍
	 [icon] => http://localhost/o2onew/public/attachment/201502/26/10/54ee8ae7cb6a2_140x85.jpg [string] 优惠券图片
	 [down_count] => 0 [int] 下载量
	 [youhui_type] => 0 [int] 0:满立减 1:折扣券
	 [begin_time] => 2015-02-01至2021-02-26 [string] 时间
	 [xpoint] => [float] 所在经度
	 [ypoint] => [float] 所在纬度
	 )
	 )
	 *
	 */
	public function youhui()
	{
		$root = array();
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']);
		$page=$page==0?1:$page;
		$supplier_id = intval($GLOBALS['request']['spid']); //商家id

		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$ext_condition = " supplier_id=$supplier_id";
		if($keyword)
		{
			$ext_condition ="  y.name like '%".$keyword."%' ";
		}
	
		$order = "";	
	
		$condition_param = array();
		require_once APP_ROOT_PATH."system/model/youhui.php";
		$deal_result  = get_youhui_list($limit,array(YOUHUI_NOTICE,YOUHUI_ONLINE),$condition_param,"",$ext_condition,$order,$field_append);

		$list = $deal_result['list'];
		$count= $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui as y where ".$deal_result['condition']);

		$page_total = ceil($count/$page_size);

		$root = array();

		$goodses = array();
		foreach($list as $k=>$v)
		{
			$goodses[$k] = format_youhui_list_item($v);
		}

// 		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
// 		$root['page_title'].="优惠券列表";

		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);

		$root['item'] = $goodses?$goodses:array();
		$sp_info = $this->load_sp_info();
		$root = array_merge($root,$sp_info);
		$root['page_title']=$sp_info['weishop_name'];
		output($root);
	}
	
	/**
	 * 活动列表接口
	 * 输入：
	 * spid: int 商家ID
	 * page:int 当前的页数
	 * keyword: string 关键词
	 *
	 *
	 *
	 * 输出：
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
	 *
	 */
	public function event()
	{
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']);
		$page=$page==0?1:$page;
		$supplier_id = intval($GLOBALS['request']['spid']); //商家id
	
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$ext_condition = " supplier_id=$supplier_id";
		if($keyword)
		{
			$ext_condition ="  e.name like '%".$keyword."%' ";
		}
	
		$order = "";	
	
		$condition_param = array();
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
			$goodses[$k]['icon'] = get_abs_img_root(get_spec_image($v['icon'],140,85,1));
		}
	
// 		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
// 		$root['page_title'].="活动列表";
	
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
	
		$root['item'] = $goodses?$goodses:array();
		$sp_info = $this->load_sp_info();
		$root = array_merge($root,$sp_info);
		$root['page_title']=$sp_info['weishop_name'];
		
		output($root);
	}
	
}
?>