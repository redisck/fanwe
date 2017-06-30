<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 外卖搜索主页
 * @author jobin.lin
 *
 */
class dc_searchModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		
		init_app_page();
		$data = request_api("dc_search","index");
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("dc/dc_search_index.html");
	}
	
	public function do_search(){
	    
		$param['keyword']=strim($_REQUEST['keyword']);
		$data = request_api("dc_search","do_search",$param);
		$GLOBALS['tmpl']->assign("data",$data);
		
		$result['html']=$GLOBALS['tmpl']->fetch('dc/inc/dc_search_location_result.html');

		ajax_return($result);
		
	}
}
?>