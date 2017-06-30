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
class uc_reviewModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$param=array();
		$param['page'] = intval($_REQUEST['page']);
		
		$data = request_api("uc_review","index",$param);

		if($data['user_login_status']!=LOGIN_STATUS_LOGINED){
		    app_redirect(wap_url("index","user#login"));
		}
		
		if(isset($data['page']) && is_array($data['page'])){
			//感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
			$page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
			//$page->parameter
			$p  =  $page->show();
			//print_r($p);exit;
			$GLOBALS['tmpl']->assign('pages',$p);
		}

		foreach ($data['item'] as $k=>$v){
		          switch ($v['type']){
		              case "deal":
		                  $data['item'][$k]['review_icon'] = "&#xe605;";
		                  $data['item'][$k]['url'] = wap_url("index","deal#index",array("data_id"=>$v['deal_id']));
		                  break;
		              case "youhui":
		                  $data['item'][$k]['review_icon'] = "&#xe603;";
		                  $data['item'][$k]['url'] = wap_url("index","youhui#index",array("data_id"=>$v['youhui_id']));
		                  break;
	                  case "event":
	                      $data['item'][$k]['review_icon'] = "&#xe606;";
	                      $data['item'][$k]['url'] = wap_url("index","event#index",array("data_id"=>$v['event_id']));
	                      break;
                      case "store":
                          $data['item'][$k]['review_icon'] = "&#xe602;";
                          $data['item'][$k]['url'] = wap_url("index","store#index",array("data_id"=>$v['supplier_location_id']));
                          break;
		          }
		}
		
		$GLOBALS['tmpl']->assign("data",$data);	
		$GLOBALS['tmpl']->display("uc_review.html");
	}
	
	
}
?>