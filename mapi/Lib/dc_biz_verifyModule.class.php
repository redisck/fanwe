<?php
/**
 * 商家预订电子劵验证
 * 
 *
 */
class dc_biz_verifyModule extends MainBaseModule{
	
	/**
	 * 	商家预订电子劵页面
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz_verify&r_type=2
	 * 	输入:
	 *  lid：int 门店ID
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *  location_list:该商家的门店列表
	 *  
	 */  
	public function index(){
		
		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$lid = intval($GLOBALS['request']['lid']);
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
		
		if(!in_array($lid, $account_info['location_ids']))
		{
			output($root,0,"没有管理权限");
		}
		
		//返回商户权限
		if(!check_module_auth("dcverify")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
		
		
		//获取支持的门店
		
		$location_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."supplier_location where id in (" . implode(",", $account_info['location_ids']) . ") ");
		$root['location_list']=$location_list;
		$root['lid']=$location_list[0]['id'];
		$root['page_title']="预订电子券验证";
		output($root);
	}
	
	

	/**
	 * 	商家预订电子劵验证接口
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz_verify&r_type=2&act=check_dcverify
	 * 	输入:
	 *  lid：int 商家ID
	 *  verify_sn:string 输入的电子劵
	 *  
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *  location_list:该商家的门店列表
	 *
	 */
	
	public function check_dcverify(){
		
		
		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
			
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
		//返回商户权限
		if(!check_module_auth("dcverify")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
		$lid = intval($GLOBALS['request']['lid']);
		if(!in_array($lid, $account_info['location_ids']))
		{
			output($root,0,"没有管理权限");
		}
		
		require_once  APP_ROOT_PATH."system/model/dc.php";
		$sn = strim($GLOBALS['request']['verify_sn']);
		if($sn=='')
		{
			output($root,0,"请输入电子劵");
		}
		
		$result=biz_check_dcverify($account_info,$sn,$lid);
		output($root,$result['status'],$result['msg']);
		
	}
	

	/**
	 * 	商家预订电子劵确定消费接口
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz_verify&r_type=2&act=use_dcverify
	 * 	输入:
	 *  lid：int 商家ID
	 *  verify_sn:string 输入的电子劵
	 *
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *  location_list:该商家的门店列表
	 *
	 */
	
	public function use_dcverify(){

		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
			
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
		//返回商户权限
		if(!check_module_auth("dcverify")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
		$lid = intval($GLOBALS['request']['lid']);
		if(!in_array($lid, $account_info['location_ids']))
		{
			output($root,0,"没有管理权限");
		}
		
		require_once  APP_ROOT_PATH."system/model/dc.php";
		$sn = strim($GLOBALS['request']['verify_sn']);
		if($sn=='')
		{
			output($root,0,"请输入电子劵");
		}
		$result=biz_use_dcverify($account_info,$sn,$lid);
		output($root,$result['status'],$result['msg']);
	}
}
?>