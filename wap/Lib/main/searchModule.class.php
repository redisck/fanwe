<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 搜索主页
 * @author jobin.lin
 *
 */
class searchModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		
		init_app_page();
		$data = request_api("search","index");

		$GLOBALS['tmpl']->assign("hot_kw",$data['hot_kw']);
		$GLOBALS['tmpl']->display("search_index.html");
	}
	
	public function do_search(){
	    $search_type = intval($_REQUEST['search_type']);
	    $keyword = strim($_REQUEST['keyword']);
	    $module_name = "tuan";
	    switch ($search_type){
	        case 1:
	            $module_name = "tuan";
	            break;
	        case 2:
	            $module_name = "goods";
	            break;
            case 3:
                $module_name = "youhuis";
                break;
            case 4:
                $module_name = "stores";
                break;
            case 5:
                $module_name = "events";
                break;
	    }

	    app_redirect(wap_url("index",$module_name,array("keyword"=>$keyword)));
	}
}
?>