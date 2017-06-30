<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class cityModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);		
		if (!$GLOBALS['tmpl']->is_cached('notice_index.html', $cache_id)){
				$param=array();				
				$data = request_api("city","index",$param);
				foreach($data['city_list'] as $k=>$v){
					foreach($data['city_list'][$k] as $kk=>$vv){
						$data['city_list'][$k][$kk]['url']=wap_url("index","city#city_change",array("city_id"=>$vv['id']));
					}
				}
				
				foreach($data['hot_city'] as $k=>$v)
				{
					$data['hot_city'][$k]['url'] = wap_url("index","city#city_change",array("city_id"=>$v['id']));
				}
			
		}
		//print_r($data);
		$GLOBALS['tmpl']->assign("data",$data);		
		$GLOBALS['tmpl']->display("city.html",$cache_id);
	}

	
	
	public function city_change()
	{	
		global_run();
		$id=intval($_REQUEST['city_id']);
		$param=array();
		$param['id'] = intval($_REQUEST['city_id']);
		$data = request_api("city","city_change",$param);
		app_redirect(wap_url("index","index"));
	}
}
?>