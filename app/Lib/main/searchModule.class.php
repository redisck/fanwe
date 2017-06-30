<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class searchModule extends MainBaseModule
{
	public function index()
	{	
		/**
		1. 团购
		2. 优惠
		3. 活动
		4. 商家
		5. 商品
		6. 分享
		 */
		$search_type = intval($_REQUEST['search_type']);
		$search_keyword = strim($_REQUEST['search_keyword']);
		
		$param = array();
		if(!empty($search_keyword))
		$param['kw'] = $search_keyword;
		
		switch ($search_type)
		{
			case 2:
				app_redirect(url("index","youhuis",$param));
			case 3:
				app_redirect(url("index","events",$param));
			case 4:
				app_redirect(url("index","stores",$param));
			case 5:
				app_redirect(url("index","cate",$param));
			case 6:
				app_redirect(url("index","discover",$param));
			default:
				app_redirect(url("index","tuan",$param));
		}
	}
	
	
	/**
	 * 智能跳转
	 * 用于热门关键词的智能检索，优先搜团购->商品->优惠券->活动->商家->分享，如都没有提示没有相关数据
	 */
	public function jump()
	{
		$kw = strim($_REQUEST['kw']);
		if($kw)
		{
			$param['kw'] = $kw;
			$condition_param['city_id'] = $GLOBALS['city']['id'];
			global_run();
			//先验证团购
			require_once APP_ROOT_PATH."system/model/deal.php";
			$ext_condition = "  d.buy_type <> 1 and d.is_shop = 0 ";
			$ext_condition.=" and d.name like '%".$kw."%' ";			
			if(get_deal_count(array(DEAL_ONLINE,DEAL_NOTICE),$condition_param,"",$ext_condition))
			{
				app_redirect(url("index","tuan",$param));
			}
			else
			{
				//商品
				$ext_condition = "  d.buy_type <> 1 and d.is_shop = 1 ";
				$ext_condition.=" and d.name like '%".$kw."%' ";
				if(get_goods_count(array(DEAL_ONLINE,DEAL_NOTICE),$condition_param,"",$ext_condition))
				{
					app_redirect(url("index","cate",$param));
				}
				else
				{
					//优惠券
					require_once APP_ROOT_PATH."system/model/youhui.php";
					$ext_condition = " y.name like '%".$kw."%' ";
					if(get_youhui_count(array(YOUHUI_NOTICE,YOUHUI_ONLINE),$condition_param,"",$ext_condition))
					{
						app_redirect(url("index","youhuis",$param));
					}
					else
					{
						//活动
						require_once APP_ROOT_PATH."system/model/event.php";
						$ext_condition = " e.name like '%".$kw."%' ";
						if(get_event_count(array(EVENT_NOTICE,EVENT_ONLINE),$condition_param,"",$ext_condition))
						{
							app_redirect(url("index","events",$param));
						}
						else
						{
							//商家
							require_once APP_ROOT_PATH."system/model/supplier.php";
							$ext_condition = " sl.name like '%".$kw."%' ";
							$rs = get_location_list(1,$condition_param,"",$ext_condition);							
							if($rs['list'])
							{
								app_redirect(url("index","stores",$param));
							}
							else
							{
								app_redirect(url("index","discover",$param));
							}
						}
					}
				}
			}
		}
		else
		{
			app_redirect(url("index"));
		}
	}
	
	
}
?>