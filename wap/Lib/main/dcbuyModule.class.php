<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dcbuyModule extends MainBaseModule
{
	
	/**
	 * 商家详细页中的外卖页面           
	 * location_dc_table_cart:预订的购物车信息
	 * location_dc_cart：外卖的购物车信息
	 **/
	public function index()
	{	
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
	
		//开始身边团购的地理定位
	    $tid=intval($_REQUEST['tid']);

		$param['lid'] =$location_id= intval($_REQUEST['lid']);	
		
		require_once APP_ROOT_PATH."wap/Lib/main/dcajaxModule.class.php";
		$lid_info=array('location_id'=>$location_id,'menu_status'=>1);
		dcajaxModule::set_dc_cart_menu_status(0,$lid_info);
		
		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);
	
		$data = request_api("dcbuy","index",$param);
		if($data['is_has_location']==1)
		{	
			$GLOBALS['tmpl']->assign('s_info',$s_info);
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			//print_r($location_dc_cart);
			//print_r($data);

		//	echo get_gopreview();
			$GLOBALS['tmpl']->assign("tid",$tid);
			$GLOBALS['tmpl']->display("dc/dcbuy.html");
		
		}
		else
		{	
			showErr('商家不存在',0,wap_url('index','dc'));

		}
		
		
	}
	

	

	
}
?>