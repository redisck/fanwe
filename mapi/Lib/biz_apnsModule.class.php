<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_apnsModule extends MainBaseModule
{
	
	/**
	 * 推送注册与注销
	 * 输入
	 * dev_type: string 设备类型 android/ios
	 * device_token:string 
	 * 
	 * 输出
	 * 
	 */
	public function index()
	{
		$root = array();
		$account_info = $GLOBALS['account_info'];
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}

		
		//手机类型dev_type=android,ios		
		$data = array();
		$data['dev_type'] = strim($GLOBALS['request']['dev_type']);
		$data['device_token'] = strim($GLOBALS['request']['device_token']);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_account", $data, 'UPDATE','id = '.intval($account_info['id']));
		
		output($root);
	}
	
}
?>

