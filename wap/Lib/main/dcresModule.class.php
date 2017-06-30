<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH."app/Lib/main/core/dc_init.php";

class dcresModule extends MainBaseModule
{

	/**
	 * 预订商家列表页   
	 * 
	 */
	
	public function index()
	{		
		global_run();
		dc_global_run();
		init_app_page();	
		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		$param['page'] = intval($_REQUEST['page']);
		$param['cid'] = intval($_REQUEST['cid']);
		$param['aid'] = intval($_REQUEST['aid']);
		$param['qid'] = intval($_REQUEST['qid']);
		$data = request_api("dcres","index",$param);
		
		foreach($data['bcate_list'] as $k=>$v){
			$data['bcate_list'][$k]['url']=wap_url("index","dcres",array('cid'=>$v['id']));
		}
		
		foreach($data['quan_list'] as $k=>$v){
			$data['quan_list'][$k]['url']=wap_url("index","dcres",array('aid'=>$v['id']));
			foreach($v['quan_sub'] as $kk=>$vv){
				$data['quan_list'][$k]['quan_sub'][$kk]['url']=wap_url("index","dcres",array('aid'=>$vv['pid'],'qid'=>$vv['id']));
			}
		}
		
		$GLOBALS['tmpl']->assign('page_title',$data['page_title']);
		$GLOBALS['tmpl']->assign('page_keyword',$data['page_title']);
		$GLOBALS['tmpl']->assign('page_description',$data['page_description']);
		$GLOBALS['tmpl']->assign('s_info',$s_info);
		foreach($data['dc_location_list'] as $kk=>$vv){
			$data['dc_location_list'][$kk]['url']=wap_url('index','dctable',array('lid'=>$vv['id']));
			
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
		
		$GLOBALS['tmpl']->display("dc/dc_res.html");

	}

	


	
}
?>