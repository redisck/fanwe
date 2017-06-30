<?php 
/**
 * 
 * 商家预订
 * 
 */
require_once APP_ROOT_PATH."system/model/dc.php";
class dc_biz_resorderModule extends MainBaseModule
{
    


	/**
	 * 	商家预订新订单
	 */  
	
    
	public function index()
	{	

		global_run();
		init_app_page();
		$param['lid'] = intval($_REQUEST['lid']);
		$param['page']=intval($_REQUEST['page']);
		$data = request_api("dc_biz_resorder","index",$param);
		
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
		
			if($data['status']==0){
				showErr($data['info'],0,wap_url("index","dc_biz"));
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
			$GLOBALS['tmpl']->display("dc/biz/dcresorder_index.html");
		}
	}
	


	/**
	 * 	商家预订订单记录 
	 */
	
	
	public function order()
	{
	
		
		global_run();
		init_app_page();
		$param['lid'] = intval($_REQUEST['lid']);
		$param['page']=intval($_REQUEST['page']);
		$param['date']=strim($_REQUEST['date']);
		$data = request_api("dc_biz_resorder","order",$param);
		
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
			
			if($data['status']==0){
				showErr($data['info'],0,wap_url("index","dc_biz"));
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
			$GLOBALS['tmpl']->display("dc/biz/dcresorder_order.html");
		}
	}
	
	
	

	
	
	/**
	 * 	商家预订订单接单接口
	 */  
	
	public function accept_order()
	{	
		
		global_run();
		init_app_page();
		$param['id'] = intval($_REQUEST['id']);
		$data = request_api("dc_biz_resorder","accept_order",$param);
		
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
		
			$result['status']=$data['status'];
			$result['info']=$data['info'];
			ajax_return($result);
		}
	}
	
	/**
	 * 	商家预订订单关闭接口
	 */
	
	public function close_order()
	{

		global_run();
		init_app_page();
		$param['id'] = intval($_REQUEST['id']);
		$param['close_reason'] = strim($_REQUEST['close_reason']);
		$data = request_api("dc_biz_resorder","close_order",$param);
		
		if ($data['biz_user_status']==0){ //用户未登录
			showErr('商户未登录',0,wap_url("biz","user#login"));
		}else{
		
			$result['status']=$data['status'];
			$result['info']=$data['info'];
			ajax_return($result);
		}
		
	}
	

	


}
?>