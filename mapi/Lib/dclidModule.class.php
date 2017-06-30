<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dclidModule extends MainBaseModule
{
	
	/**
	 * 商家详细页中的主入口
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dclid&r_type=2&lid=41
	 * 
	 * 输入：
	 * lid:int 商家ID
	 *
	 * 输出：
	 * is_has_location:int是否存在些商家， 0为不存在，1为存在
	 * $dclocation:array:array:array 商家信息
	 * 
	 * $dclocation下面的字段
	 * is_collected：是否已经收藏
		Array
        (
            [id] => 41
            [name] => 果果外卖
            [preview] => http://localhost/o2onew/public/attachment/201504/17/10/55306e5b0f72a_1200x900.jpg
            [is_dc] => 1
            [is_reserve] => 1
            [is_collected] => 1
        )       
	 **/
	public function index()
	{	
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";

		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		
		$location_id = intval($GLOBALS['request']['lid']);

		$dclocation=$GLOBALS['db']->getRow("select id,name,preview,is_dc,is_reserve from ".DB_PREFIX."supplier_location where id =".$location_id);
		$root=array();
		if($dclocation)
		{	
			$dclocation['preview']=get_abs_img_root(get_spec_image($dclocation['preview'],600,450,1));

			$is_colloect=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where location_id=".$dclocation['id']." and user_id=".$user_id);
			if($is_colloect>0){
				$dclocation['is_collected']=1;
			}else{
				$dclocation['is_collected']=0;
			}
			$root['is_has_location']=1;
			$root['dclocation']=$dclocation;
			
			output($root);
		}
		else
		{	
			$root['is_has_location']=0;
			output($root);
		}
		
		
	}
	
	

	

	
}
?>