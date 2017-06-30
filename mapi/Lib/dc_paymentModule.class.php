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
	 * 支付订单页面，点击“确认支付”的跳转地址
	 *
	 * 测试地址：http://localhost/o2onew/mapi/index.php?ctl=dc_payment&act=done&r_type=2&order_id=41
	 * 输入：
	 * order_id：int 订单ID
	 * pay_status：int 订单返回状态
	 * pay_status=0,正常支付，还有部分未完成
	 * pay_status=1,正常支付，支付完成
	 * pay_status=2,付款单号重复支付,当前支付的退到会员帐户
	 * pay_status=3,订单已关闭
	 * pay_status=5,货到付款
	 *
	 * payment_notice_id：int 支付单号的ID,只有pay_status=0和pay_status=2时，才有这个值传入
	 *
	 * 输出：
	 *
	 * order_id：int 订单ID
	 * order_sn:订单编号
	 * pay_status：int 订单返回状态
	 * pay_status=0,订单未完成
	 * pay_status=1,正常支付，支付完成
	 * pay_status=2,付款单号重复支付,当前支付的退到会员帐户
	 * pay_status=3,订单已关闭
	 * pay_status=5,货到付款
	 * pay_info：提示信息
	 * page_title：标题
	 * payment_code：订单未支付完成时，返回的付款单号的信息，该值只有pay_status=0时才有
	 * is_rs:是否为预订订单，等于0，为外卖订单，等于1，为预订订单
	 *
	 */
	public function done()
	{
		global_run();
		$pay_status = intval($GLOBALS['request']['pay_status']);
		$order_id = intval($GLOBALS['request']['order_id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
		$root['is_rs']=$order_info['is_rs'];
		if($pay_status==1){  //正常支付，支付完成

			$root['order_id']=$order_id;
			$root['order_sn']=$order_info['order_sn'];
			$root['pay_status']=$pay_status;
			$root['pay_info']="订单号:".$order_info['order_sn']."支付成功， 订单接单中, 请耐心等待";
			$root['page_title']=$GLOBALS['lang']['PAY_SUCCESS'];
			output($root);
	
				
		}elseif($pay_status==0){ //订单未完成
			$payment_notice_id = intval($GLOBALS['request']['payment_notice_id']);
			$payment_notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
			$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_notice_info['payment_id']);

			
			if($GLOBALS['request']['from']=="wap")
			{
				if ($payment_info['online_pay']!=2&&$payment_info['online_pay']!=4&&$payment_info['online_pay']!=5)
				{
					output(array(),0,"该支付方式不支持wap支付");
				}
			}
			else
			{
				if ($payment_info['online_pay']!=3&&$payment_info['online_pay']!=4&&$payment_info['online_pay']!=5)
				{
					output(array(),0,"该支付方式不支持手机支付");
				}
			}
				
			require_once APP_ROOT_PATH."system/dc_payment/Dc_".$payment_info['class_name']."_payment.php";
			$payment_class = 'Dc_'.$payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			$payment_code = $payment_object->get_payment_code($payment_notice_id);

			$root['order_id']=$order_id;
			$root['order_sn']=$order_info['order_sn'];
			$root['pay_status']=$pay_status;
			$pay_price=$order_info['total_price']-$order_info['pay_amount']-$order_info['promote_amount'];
			if($GLOBALS['request']['from']=="wap"){
				$root['page_title']='订单未完成';
				$url=wap_url("index","dcorder#order",array("id"=>$order_id));
				$root['pay_info']="订单未完成，尚余".format_price($pay_price)."未支付,请<a href='".$url."'>继续支付</a>";
				$root['payment_code'] = $payment_code;
			}else{
				$root['page_title']='订单未完成';
				$root['pay_info']="订单未完成，尚余".format_price($pay_price)."未支付,请继续支付";
				$root['payment_code'] = $payment_code;
			}
			
			
			output($root);
				
				
		}elseif($pay_status==2){  //付款单号重复支付,当前支付的退到会员帐户
			$payment_notice_id = intval($GLOBALS['request']['payment_notice_id']);
			$payment_notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);

			$root['order_id']=$order_id;
			$root['order_sn']=$order_info['order_sn'];
			$root['pay_status']=$pay_status;
			$root['pay_info']="您的支付单号".$payment_notice_info['notice_sn']."已支付。重复支付金额".format_price($payment_notice_info['money'])."已经转到账户余额中";
			$root['page_title']='付款单号重复支付';
			output($root);
		}elseif($pay_status==3){
			require_once APP_ROOT_PATH."system/model/dc.php";
			dc_order_close($order_id , 1,'订单已关闭');

			//在线支付，并有手续费，手续费不退回
			if($order_info['online_pay'] > 0){
				$order_info['return_money']=$order_info['pay_amount'] -$order_info['ecv_money'] - $order_info['payment_fee'];
			}else{
				$order_info['return_money']=$order_info['pay_amount'] -$order_info['ecv_money'];
			}
				
			$root['order_id']=$order_id;
			$root['order_sn']=$order_info['order_sn'];
			$root['pay_status']=$pay_status;
			$root['pay_info']="您的订单号".$order_info['order_sn']."已关闭,您支付的".format_price($order_info['return_money'])."已经转到账户余额中";
			$root['page_title']='订单已关闭';
			output($root);
		}elseif($pay_status==5){
			//货到付款
	

			$root['order_id']=$order_id;
			$root['order_sn']=$order_info['order_sn'];
			$root['pay_status']=$pay_status;
			$root['pay_info']="您已成功提交订单 ,订单号".$order_info['order_sn']."订单接单中, 请耐心等待";
			$root['page_title']='货到付款，订单已提交';
			output($root);
				
		}
	
	
	
	
	}
	


}
?>