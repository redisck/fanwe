<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 发现
 * @author jobin.lin
 *
 */
class discoverModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$param['page'] = intval($_REQUEST['page']);
		$param['tag'] = strim($_REQUEST['tag']);

		$data = request_api("discover","index",$param);
		

		if(isset($data['page']) && is_array($data['page'])){
			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			if($param['tag'])
			    $p_tag = "&tag=".$param['tag'];
			$page = new Page($data['page']['data_total'],$data['page']['page_size'],$p_tag);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}

		$GLOBALS['tmpl']->assign("ajax_url",wap_url("index","uc_home"));
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->assign("data_list",$data['data_list']);
		$GLOBALS['tmpl']->display("discover.html");
	}
	
}
?>