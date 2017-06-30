<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dcpointModule extends MainBaseModule
{
	
	/**
	 * 商家点评列表页面       
	 **/
	public function index()
	{	
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		$page = intval($_REQUEST['page']);
		if($page==0){
			$page = 1;
		}
		$param['page']=$page;
		$param['lid'] = strim($_REQUEST['lid']);
		
		$data = request_api("dcpoint","index",$param);

		if($data['is_has_location']==1)
		{	
	
			if(isset($data['page']) && is_array($data['page'])){
			
				//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
				$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
				//$page->parameter
				$p  =  $page->show();
				//print_r($p);exit;
				$GLOBALS['tmpl']->assign('pages',$p);
			}

			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->display("dc/dc_point.html");
			
		}
		else
		{	
			showErr('商家不存在',0,wap_url('index','dc'));
		}
		
		
	}
	

	
	
	
}
?>