<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dcdetailModule extends MainBaseModule
{
	
	/**
	 * 商家详细页中的外卖页面
	 *
	 * 输入：
	 * lid:int 商家ID
	 *
	 * 输出：
	 * is_has_location:int是否存在些商家， 0为不存在，1为存在
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page_keyword:string 页面关键词
	 * page_description:string 页面描述
	 * $dclocation:array:array:array 商家信息
	 * 
	 * $dclocation下面的字段
	            优惠详情用到的字段：
      	is_payonlinediscount：是否支持在线支付优惠
      	is_firstorderdiscount：是否支持新单立减
      	dc_allow_invoice：是否支持发票
      	dc_allow_ecv：是否支持代金卷
      	dc_online_pay：是否支持在线支付
      	dc_allow_cod：是否支持线下支付（即餐到付款）
      	其他有用字段
      	preview：图片
      	distance：距离,单位为米
      	dc_location_notice:商家公告
      	address：商家地址
      	dp_count：评价个数
      	avg_point：评价平均分
      	open_time_cfg_str：营业时间段
      	cate_data:array,为商家的分类，可以是多个
      	
      	Array(
            [name] => 果果外卖
            [address] => 八一七中路群升国际E区田田田田田田田田田田田田田田田田
            [xpoint] => 119.314685
            [ypoint] => 26.092901
            [supplier_id] => 43
            [city_id] => 15
			[avg_point] => 3.4375
            [total_point] => 55
            [dp_count] => 16
            [is_dc] => 1
            [is_reserve] => 1
            [dc_online_pay] => 1
            [is_close] => 0
            [open_time_cfg_str] => 07:00-14:00,14:00-22:00
            [dc_allow_cod] => 1
            [max_delivery_scale] => 100
            [dc_location_notice] => 注意：每一订单个最多只有三份外卖可以享受活动优惠，若想享受更多的优惠请用不同的手机号码来定外卖。
            [dc_buy_count] => 756
            [dc_allow_invoice] => 1
            [is_payonlinediscount] => 1
            [dc_allow_ecv] => 1
            [is_firstorderdiscount] => 1
            [dc_ptag] => 126
            [distance] => 3196.598048052
            [location_delivery_info] => Array
                (
                    [id] => 635
                    [location_id] => 41
                    [start_price] => 10.0000
                    [scale] => 100
                    [delivery_price] => 1.0000
                    [is_free_delivery] => 0
                )
            [cate_data] => Array
                (
                    [0] => 生鲜超市
                    [1] => 新店推荐
                    [2] => 小吃快餐
                    [3] => 咖啡甜品
                )
        )    
      	
      	location_delivery_info：array  配送信息，没有，则是免运费       
	 **/
	public function index()
	{	
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";		

		
		$param['lid'] = intval($_REQUEST['lid']);
		$data = request_api("dcdetail","index",$param);
		if($data['is_has_location']==1)
		{	

			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->display("dc/dc_detail.html");
		}
		else
		{	
			
			showErr('商家不存在',0,wap_url('index','dc'));
		}
		
		
	}
	
	
}
?>