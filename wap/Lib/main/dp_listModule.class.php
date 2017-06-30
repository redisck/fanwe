<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 我的点评列表
 * @author jobin.lin
 *
 */
class dp_listModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$param['page'] = intval($_REQUEST['page']);
		$param['type'] = strim($_REQUEST['type']);
		$param['data_id'] = intval($_REQUEST['data_id']);
		
		$data = request_api("dp","index",$param);
		
		if(intval($data['data_id'])==0)
		{
		    app_redirect(wap_url("index"));
		}
		if(isset($data['page']) && is_array($data['page'])){
			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}


		
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("dp_list.html");
	}
	
	
}
?>