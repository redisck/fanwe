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
					app_redirect(url("index","dcorder#order",array('id'=>$payment_notice['order_id'])));
				}
				$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$payment_notice['order_id']." and type_del = 0");
				if(empty($order))
				{
					showErr($GLOBALS['lang']['INVALID_ORDER_DATA'],0,url("index","dc"),1);
				}
				$order_menu=unserialize($order['order_menu']);
				$location_dc_table_cart=$order_menu['rs_list'];
				$location_dc_cart=$order_menu['menu_list'];
				
				
				//验证预订位置的库存
				if($order['is_rs']==1 && $order['is_cancel']==0){

						//验证预订位置的库存
						foreach($location_dc_table_cart['cart_list'] as $kk=>$vv){
							$rs_date=date("Y-m-d",$vv['table_time']);
							$total_count=$GLOBALS['db']->getOne("select total_count from ".DB_PREFIX."dc_rs_item_time where  id=".$vv['table_time_id']);
							$rs_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_day where time_id=".$vv['table_time_id']." and rs_date='".$rs_date."'");
			
							if(!$rs_info && $total_count==0){
								showErr($GLOBALS['lang']['DC_TABLE_OUT_STOCK'],$ajax,url("index","dctable",array('lid'=>$order['location_id'])));				
								break;
							}elseif($rs_info && $rs_info['buy_count'] + $vv['num'] > $total_count ){	
								showErr($GLOBALS['lang']['DC_TABLE_OUT_STOCK'],$ajax,url("index","dctable",array('lid'=>$order['$location_id'])));
								break;
							}
						}
				}
				
				
				
				
				if($order['pay_status']==1)
				{
					if($order['refund_status']==1)
					{
						showErr($GLOBALS['lang']['DC_ORDER_ERROR_COMMON'],0,url("index","dc"),1);

					}
					else
					{
						app_redirect(url("index","dc_payment#done",array("id"=>$order['id'])));
						exit;
					}
				}
				require_once APP_ROOT_PATH."system/dc_payment/Dc_".$payment_info['class_name']."_payment.php";
				$payment_class = 'Dc_'.$payment_info['class_name']."_payment";
				$payment_object = new $payment_class();
				$payment_code = $payment_object->get_payment_code($payment_notice['id']);
				$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['DC_ORDER_INFORMATION']);
				$GLOBALS['tmpl']->assign("payment_code",$payment_code);
	
				$GLOBALS['tmpl']->assign("order",$order);
				$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
				$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
				$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
				if(intval($_REQUEST['check'])==1)
				{
					showErr($GLOBALS['lang']['PAYMENT_NOT_PAID_RENOTICE'],0,url("index","dc_payment#pay",array("id"=>$id)));
				}
				$GLOBALS['tmpl']->display("dc/dc_payment_pay.html");
			}
			else
			{
				$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$payment_notice['order_id']);
				if($order['pay_status']==1)
				{
					if($order['refund_status']==1)				
						showErr($GLOBALS['lang']['DC_ORDER_ERROR_COMMON'],0,url("index","dc"),1);
					else
						app_redirect(url("index","dc_payment#done",array("id"=>$order['id'])));
				}
				else
					showSuccess($GLOBALS['lang']['NOTICE_PAY_SUCCESS'],0,url("index","dc"),1);
			}
		}
		else
		{
			showErr($GLOBALS['lang']['NOTICE_SN_NOT_EXIST'],0,url("index","dcorder#order",array('id'=>$payment_notice['order_id'])),1);
		}
	}
	
	
	public function tip()
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
		$GLOBALS['tmpl']->display("dc/dc_payment_tip.html");
	}

	public function out_time()
	{
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".intval($_REQUEST['id']));
		$order = $GLOBALS['db']->getRow("select location_id from ".DB_PREFIX."dc_order where id = ".$payment_notice['order_id']);
		$GLOBALS['tmpl']->assign("payment_notice",$payment_notice);
		$GLOBALS['tmpl']->assign("order",$order);
		$GLOBALS['tmpl']->display("dc/dc_payment_out_time.html");
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
			require_once APP_ROOT_PATH."system/dc_payment/Dc_".$payment_info['class_name']."_payment.php";
			$payment_class ='Dc_'. $payment_info['class_name']."_payment";
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
			require_once APP_ROOT_PATH."system/dc_payment/Dc_".$payment_info['class_name']."_payment.php";
			$payment_class = 'Dc_'.$payment_info['class_name']."_payment";
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
		$rs = intval($_REQUEST['rs']);
		
		if($rs==1){  //正常支付，支付完成
			$payment_notice_id = intval($_REQUEST['id']);
			$payment_notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$payment_notice_info['order_id']);
			$GLOBALS['tmpl']->assign("rs",$rs);
			//在线支付，并有手续费，手续费不退回
			if($order_info['online_pay'] > 0){
				$order_info['return_money']=$order_info['pay_amount'] -$order_info['ecv_money'] - $order_info['payment_fee'];
			}else{
				$order_info['return_money']=$order_info['pay_amount'] -$order_info['ecv_money'];
			}
			$GLOBALS['tmpl']->assign("order_info",$order_info);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
	
			require_once APP_ROOT_PATH."system/model/dc.php";
			$is_in_open_time=is_in_open_time($order_info['location_id']);   //判断是否在营业时间段内
			$GLOBALS['tmpl']->assign("is_in_open_time",$is_in_open_time);
			$GLOBALS['tmpl']->assign("payment_notice_info",$payment_notice_info);
			

			
			if($order_info['is_rs']==1 && $order_info['is_cancel']==0){
				$order_menu=unserialize($order_info['order_menu']);
				
				$location_dc_table_cart=$order_menu['rs_list'];
				//验证预订位置的库存
				foreach($location_dc_table_cart['cart_list'] as $kk=>$vv){
	
					$rs_date=date("Y-m-d",$vv['table_time']);
					$total_count=$GLOBALS['db']->getOne("select total_count from ".DB_PREFIX."dc_rs_item_time where  id=".$vv['table_time_id']);
					$rs_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_day where time_id=".$vv['table_time_id']." and rs_date='".$rs_date."'");
					$is_out_stock_rs=0;
					if(!$rs_info && $total_count==0){
						$is_out_stock_rs=1;
						$GLOBALS['tmpl']->assign("is_out_stock_rs",$is_out_stock_rs);
					}elseif($rs_info && $rs_info['buy_count'] > $total_count ){
						$is_out_stock_rs=1;
						$GLOBALS['tmpl']->assign("is_out_stock_rs",$is_out_stock_rs);
					}
				}
			}
			
			$GLOBALS['tmpl']->display("dc/dc_payment_done.html");
			
		}elseif($rs==0){ //正常支付，还有部分未完成
			$payment_notice_id = intval($_REQUEST['id']);
			$payment_notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$payment_notice_info['order_id']);
			$GLOBALS['tmpl']->assign("rs",$rs);
			$order_info['pay_price']=intval($order_info['total_price']-$order_info['pay_amount']-$order_info['promote_amount']);
			$GLOBALS['tmpl']->assign("order_info",$order_info);
			$GLOBALS['tmpl']->assign("payment_notice_info",$payment_notice_info);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
			$GLOBALS['tmpl']->display("dc/dc_payment_done.html");
			
		}elseif($rs==2){  //付款单号重复支付,当前支付的退到会员帐户
			$payment_notice_id = intval($_REQUEST['id']);
			$payment_notice_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$payment_notice_info['order_id']);
			$GLOBALS['tmpl']->assign("rs",$rs);
			$order_info['pay_price']=$order_info['total_price']-$order_info['pay_amount']-$order_info['promote_amount'];
			$GLOBALS['tmpl']->assign("order_info",$order_info);
			$GLOBALS['tmpl']->assign("payment_notice_info",$payment_notice_info);
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['PAY_SUCCESS']);
			$GLOBALS['tmpl']->display("dc/dc_payment_done.html");
			
			
		}elseif($rs==3){
			require_once APP_ROOT_PATH."system/model/dc.php";
			$order_id = intval($_REQUEST['id']);
			dc_order_close($order_id , 1,'订单已关闭');
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
			$GLOBALS['tmpl']->assign("rs",3);
			//在线支付，并有手续费，手续费不退回
			if($order_info['online_pay'] > 0){
				$order_info['return_money']=$order_info['pay_amount'] -$order_info['ecv_money'] - $order_info['payment_fee'];
			}else{
				$order_info['return_money']=$order_info['pay_amount'] -$order_info['ecv_money'];
			}
			$GLOBALS['tmpl']->assign("order_info",$order_info);
			$GLOBALS['tmpl']->display("dc/dc_payment_done.html");
		}elseif($rs==5){
			// $rs==5  代表货到付款
			$order_id = intval($_REQUEST['id']);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
			$GLOBALS['tmpl']->assign("rs",5);
			$GLOBALS['tmpl']->assign("order_info",$order_info);
			$GLOBALS['tmpl']->display("dc/dc_payment_done.html");
			
			
			
			
		}
		
		
		

	}
	
	public function incharge_done()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
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
		$GLOBALS['tmpl']->display("dc/dc_payment_done.html");
	}
	

}
?>