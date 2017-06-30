<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 身边团购
 * @author jobin.lin
 *
 */
class positionModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();	

		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']); 
		$GLOBALS['tmpl']->display("position.html");
		
	}
	public function do_posiotn(){
	    global_run();
	    app_redirect(url("index","tuan"));
	}
	
	public function clear(){
	    require_once APP_ROOT_PATH.'system/model/city.php';
	    City::clear_geo();
	    app_redirect_preview();
	}
	
}
?>