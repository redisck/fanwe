<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class paymentModule extends MainBaseModule
{
	//订单支付页
	public function pay()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$id = intval($_REQUEST['id']);
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$id);
	
		if($payment_notice)
		{
			if($payment_notice['is_paid'] == 0)
			{
				$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_notice['payment_id']);
				if(empty($payment_info))
				{
					app_redirect(url("index"));
				}
				$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']." and is_delete = 0");
				if(empty($order))
				{
					app_redirect(url("index"));
				}
				if($order['pay_status']==2)
				{
					if($order['after_sale']==0)
					{
						app_redirect(url("index","payment#done",array("id"=>$order['id'])));
						exit;
					}
					else
					{
						showErr($GLOBALS['lang']['DEAL_ERROR_COMMON'],0,url("index"),1);
					}
				}
				require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
				$payment_class = $payment_info['class_name']."_payment";
				$payment_object = new $payment_class();
				$payment_code = $payment_object->get_payment_code($payment_notice['id']);
				$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_NOW']);
				$GLOBALS['tmpl']->assign("payment_code",$payment_code);
	
				$GLOBALS['tmpl']->assign("order",$order);
				$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
				if(intval($_REQUEST['check'])==1)
				{
					showErr($GLOBALS['lang']['PAYMENT_NOT_PAID_RENOTICE'],0,url("index","payment#pay",array("id"=>$id)));
				}
				$GLOBALS['tmpl']->display("payment_pay.html");
			}
			else
			{
				$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$payment_notice['order_id']);
				if($order['pay_status']==2)
				{
					if($order['after_sale']==0)
						app_redirect(url("index","payment#done",array("id"=>$order['id'])));
					else
						showErr($GLOBALS['lang']['DEAL_ERROR_COMMON'],0,url("index"),1);
				}
				else
					showSuccess($GLOBALS['lang']['NOTICE_PAY_SUCCESS'],0,url("index"),1);
			}
		}
		else
		{
			showErr($GLOBALS['lang']['NOTICE_SN_NOT_EXIST'],0,url("index"),1);
		}
	}
	
	
	public function tip()
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
		$GLOBALS['tmpl']->display("payment_tip.html");
	}
	
	
	public function response()
	{
		//支付跳转返回页
		if($GLOBALS['pay_req']['class_name'])
			$_REQUEST['class_name'] = $GLOBALS['pay_req']['class_name'];
			
		$class_name = strim($_REQUEST['class_name']);
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = '".$class_name."'");
		if($payment_info)
		{
			require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
			$payment_class = $payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			adddeepslashes($_REQUEST);
			$payment_code = $payment_object->response($_REQUEST);
		}
		else
		{
			showErr($GLOBALS['lang']['PAYMENT_NOT_EXIST']);
		}
	}
	
	public function notify()
	{
		//支付跳转返回页
		if($GLOBALS['pay_req']['class_name'])
			$_REQUEST['class_name'] = $GLOBALS['pay_req']['class_name'];
			
		$class_name = strim($_REQUEST['class_name']);
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = '".$class_name."'");
		if($payment_info)
		{
			require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
			$payment_class = $payment_info['class_name']."_payment";
			$payment_object = new $payment_class();
			adddeepslashes($_REQUEST);
			$payment_code = $payment_object->notify($_REQUEST);
		}
		else
		{
			showErr($GLOBALS['lang']['PAYMENT_NOT_EXIST']);
		}
	}
	
	
	
	public function done()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	
		if($order_info['type']==0)
		{		
			$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
			if(!$deal_ids)
				$deal_ids = 0;
			$order_deals = $GLOBALS['db']->getAll("select d.* from ".DB_PREFIX."deal as d where id in (".$deal_ids.")");
		
			$GLOBALS['tmpl']->assign("order_info",$order_info);
			
			$is_coupon = 0;
			$send_coupon_sms = 0;
			$is_lottery = 0;
			foreach($order_deals as $k=>$v)
			{
				if($v['is_coupon'] == 1&&$v['buy_status']>0)
				{
					$is_coupon = 1;
				}
				if($v['forbid_sms'] == 0)
				{
					$send_coupon_sms = 1;
				}
				if($v['is_lottery'] == 1&&$v['buy_status']>0)
				{
					$is_lottery = 1;
				}
				if($v['uname']=="")
					$order_deals[$k]['url'] = url("index","deal#".$v['id']);
				else
					$order_deals[$k]['url'] = url("index","deal#".$v['uname']);
			}
			$GLOBALS['tmpl']->assign("order_deals",$order_deals);		
			$GLOBALS['tmpl']->assign("is_lottery",$is_lottery);
			$GLOBALS['tmpl']->assign("is_coupon",$is_coupon);
			$GLOBALS['tmpl']->assign("send_coupon_sms",$send_coupon_sms);
		}
		else
		{
			if($order_info['user_id']==$GLOBALS['user_info']['id'])
			{
				showSuccess(round($order_info['pay_amount'],2)." 元 充值成功",0,url("index","uc_money"));
			}
			else
			{
				showSuccess(round($order_info['pay_amount'],2)." 元 充值成功",0);
			}
		}
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
		$GLOBALS['tmpl']->display("payment_done.html");
	}
	
	public function incharge_done()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		//$order_deals = $GLOBALS['db']->getAll("select d.* from ".DB_PREFIX."deal as d where id in (select distinct deal_id from ".DB_PREFIX."deal_order_item where order_id = ".$order_id.")");
		$GLOBALS['tmpl']->assign("order_info",$order_info);
		//$GLOBALS['tmpl']->assign("order_deals",$order_deals);
		
		if($order_info['user_id']==$GLOBALS['user_info']['id'])
		{
			showSuccess(round($order_info['pay_amount'],2)." 元 充值成功",0,url("index","uc_money"));
		}
		else
		{
			showSuccess(round($order_info['pay_amount'],2)." 元 充值成功",0);
		}
	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
		$GLOBALS['tmpl']->display("payment_done.html");
	}
}
?>