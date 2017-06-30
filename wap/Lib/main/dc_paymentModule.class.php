<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dc_paymentModule extends MainBaseModule
{
	
	

	/**
	 *  支付订单页面，点击“确认支付”的跳转地址
	 *
	 */
	public function done()
	{
	
		$param['pay_status'] = intval($_REQUEST['pay_status']);
		$param['order_id'] = intval($_REQUEST['order_id']);
		$param['payment_notice_id'] = intval($_REQUEST['payment_notice_id']);
		$param['form'] = 'wap';
		$data = request_api("dc_payment","done",$param);

		$order_info=$GLOBALS['db']->getRow("select is_rs from ".DB_PREFIX."dc_order where id=".$data['order_id']);
		if($order_info['is_rs']==1){
			$url=wap_url('index','dc_rsorder#view',array('id'=>$data['order_id']));
		}else{
			$url=wap_url('index','dc_dcorder#view',array('id'=>$data['order_id']));
		}

		$data['url']=$url;
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("dc/dc_payment_done.html");
	

	}
	


}
?>