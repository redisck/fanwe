<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class cartModule extends MainBaseModule
{
	public function index()
	{	
		global_run();
		init_app_page();		
		$GLOBALS['tmpl']->display("cart.html");
	}
	
	/**
	 * 购物车的提交页
	 */
	public function check()
	{
		global_run();
		init_app_page();
		if((check_save_login()!=LOGIN_STATUS_LOGINED&&$GLOBALS['user_info']['money']>0)||check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			app_redirect(url("index","user#login"));
		}
		require_once APP_ROOT_PATH."system/model/cart.php";
		$cart_result = load_cart_list();
		$cart_list = $cart_result['cart_list'];
		$cart_list_group = cart_list_group($cart_list);
		

		foreach($cart_list_group as $k=>$v)
		{
			$cart_list_group[$k]['supplier'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = '".$v['supplier_id']."'");
			$cart_list_group[$k]['supplier'] = $cart_list_group[$k]['supplier']?$cart_list_group[$k]['supplier']:app_conf("SHOP_TITLE")."直营";
		}
		$total_price = $cart_result['total_data']['total_price'];
		if(!$cart_list)
		{
			app_redirect(url("index"));
		}
			
		foreach($cart_list as $k=>$v)
		{
			$id = intval($v['id']);
			$number = intval($v['number']);
			$data = check_cart($id, $number);
			if(!$data['status'])
			{
				showErr($data['info']);
			}
		}
		//输出购物车内容
		$GLOBALS['tmpl']->assign("cart_list",$cart_list);
		$GLOBALS['tmpl']->assign("cart_list_group",$cart_list_group);
		$GLOBALS['tmpl']->assign('total_price',$total_price);

		
		$is_delivery = 0;
		foreach($cart_list as $k=>$v)
		{			

			if($v['is_delivery']==1)
			{
				$is_delivery = 1;
				break;
			}
		}
			
		if($is_delivery)
		{
			//输出配送方式
			$consignee_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign("consignee_count",intval($consignee_count));
			$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']." and is_default = 1");
			$GLOBALS['tmpl']->assign("consignee_id",intval($consignee_id));
		}
			
		//配送方式由ajax由 consignee 中的地区动态获取
			
		//输出支付方式
		$payment_list = load_auto_cache("cache_payment");
		
		foreach($cart_list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
			{
				$define_payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_payment where deal_id = ".$v['deal_id']);
				$define_payment = array();
				foreach($define_payment_list as $kk=>$vv)
				{
					array_push($define_payment,$vv['payment_id']);
				}
				foreach($payment_list as $k=>$v)
				{
					if(in_array($v['id'],$define_payment))
					{
						unset($payment_list[$k]);
					}
				}
			}
		}		


		$icon_paylist = array(); //用图标展示的支付方式
		$disp_paylist = array(); //特殊的支付方式(Voucher,Account,Otherpay)
		$bank_paylist = array(); //网银直连
		
		$wx_payment = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = 'Wwxjspay'");
		if($wx_payment)
		{
			$wx_payment['config'] = unserialize($wx_payment['config']);
			if($wx_payment['config']['scan']==1)
			{
				$directory = APP_ROOT_PATH."system/payment/";
				$file = $directory. '/' .$wx_payment['class_name']."_payment.php";
				if(file_exists($file))
				{
					require_once($file);
					$payment_class = $wx_payment['class_name']."_payment";
					$payment_object = new $payment_class();
					$wx_payment['display_code'] = $payment_object->get_web_display_code();
					$disp_paylist[] = $wx_payment;
				}
			}
		}
		
		foreach($payment_list as $k=>$v)
		{
			if($v['class_name']=="Voucher"||$v['class_name']=="Account"||$v['class_name']=="Otherpay")
			{
				if($v['class_name']=="Account")
				{
					$directory = APP_ROOT_PATH."system/payment/";
					$file = $directory. '/' .$v['class_name']."_payment.php";
					if(file_exists($file))
					{
						require_once($file);
						$payment_class = $v['class_name']."_payment";
						$payment_object = new $payment_class();
						$v['display_code'] = $payment_object->get_display_code();					
					}
				}
				if($v['class_name']=="Voucher")
				{
					$directory = APP_ROOT_PATH."system/payment/";
					$file = $directory. '/' .$v['class_name']."_payment.php";
					if(file_exists($file))
					{
						require_once($file);
						$payment_class = $v['class_name']."_payment";
						$payment_object = new $payment_class();
						$v['display_code'] = $payment_object->get_display_code();
					}
				}
				$disp_paylist[] = $v;
			}
			else
			{
				if($v['is_bank']==1)
				$bank_paylist[] = $v;	
				else
				$icon_paylist[] = $v;
			}
		}
	
		
		
		
		$GLOBALS['tmpl']->assign("icon_paylist",$icon_paylist);
		$GLOBALS['tmpl']->assign("disp_paylist",$disp_paylist);
		$GLOBALS['tmpl']->assign("bank_paylist",$bank_paylist);
		
			
		$GLOBALS['tmpl']->assign("is_delivery",$is_delivery);
			
		$is_coupon = 0;
		foreach($cart_list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select is_coupon from ".DB_PREFIX."deal where id = ".$v['deal_id']." and forbid_sms = 0")==1)
			{
				$is_coupon = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("is_coupon",$is_coupon);
		$GLOBALS['tmpl']->assign("coupon_name",app_conf("COUPON_NAME"));
			
		//查询总金额
		$delivery_count = 0;
		foreach($cart_list as $k=>$v)
		{
			if($v['is_delivery']==1)
			{
				$delivery_count++;
			}
		}
		if($total_price > 0 || $delivery_count > 0)
			$GLOBALS['tmpl']->assign("show_payment",true);
		
		$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		
		
		//关于短信发送的条件
		$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
		$GLOBALS['tmpl']->assign("sms_ipcount",load_sms_ipcount());

		//购物车检测页
		$GLOBALS['tmpl']->display("cart_check.html");
	}
	
	
	
	//购物车订单提交
	public function done()
	{
		require_once APP_ROOT_PATH."system/model/cart.php";
		require_once APP_ROOT_PATH."system/model/deal.php";
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		global_run();
		$ajax = 1;
		$region4_id = intval($_REQUEST['region_lv4']);
		$region3_id = intval($_REQUEST['region_lv3']);
		$region2_id = intval($_REQUEST['region_lv2']);
		$region1_id = intval($_REQUEST['region_lv1']);
	
		if ($region4_id==0)
		{
			if ($region3_id==0)
			{
				if ($region2_id==0)
				{
					$region_id = $region1_id;
				}
				else
					$region_id = $region2_id;
			}
			else
				$region_id = $region3_id;
		}
		else
			$region_id = $region4_id;
	
		$delivery_id = intval($_REQUEST['delivery']);
		$payment = intval($_REQUEST['payment']);
		$account_money = floatval($_REQUEST['account_money']);
		$all_account_money = intval($_REQUEST['all_account_money']);
		$ecvsn = $_REQUEST['ecvsn']?strim($_REQUEST['ecvsn']):'';
		$ecvpassword = $_REQUEST['ecvpassword']?strim($_REQUEST['ecvpassword']):'';
	
		$user_id = intval($GLOBALS['user_info']['id']);
		$session_id = es_session::id();
		
		$cart_result = load_cart_list();
		$goods_list = $cart_result['cart_list'];
	
		if(!$goods_list)
		{
			showErr($GLOBALS['lang']['CART_EMPTY_TIP'],$ajax);
		}
	
		//验证购物车
		if((check_save_login()!=LOGIN_STATUS_LOGINED&&$GLOBALS['user_info']['money']>0)||check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url("index","user#login"));
		}
		$deal_ids = array();
		foreach($goods_list as $k=>$v)
		{
			$data = check_cart($v['id'], $v['number']);
			if(!$data['status'])
			showErr($data['info'],$ajax,url("index","cart#index"));
			$deal_ids[$v['deal_id']]['deal_id'] = $v['deal_id']; 
		}
		foreach($deal_ids as $row)
		{	
			//验证支付方式的支持
			if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$row['deal_id'])==1)
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_payment where deal_id = ".$row['deal_id']." and payment_id = ".$payment))
				{
					showErr($GLOBALS['lang']['INVALID_PAYMENT'],$ajax,url("index","cart#index"));
				}
			}
		}
					
			
		//结束验证购物车
		//开始验证订单接交信息
		$data = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list);
		
	
		if($data['is_delivery'] == 1)
		{
			foreach($data['delivery_fee_supplier'] as $k=>$v)
			{
				if($v<0)
				{
					showErr("部份商品不支持".$data['delivery_info']['name'],$ajax);
				}
			}
			//配送验证
			if(!$data['region_info']||$data['region_info']['region_level'] != 4)
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE_ADDRESS'],$ajax);
			}
			if(trim($_REQUEST['consignee'])=='')
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE'],$ajax);
			}
			if(trim($_REQUEST['address'])=='')
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_ADDRESS'],$ajax);
			}
			if(trim($_REQUEST['zip'])=='')
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_ZIP'],$ajax);
			}
			if(trim($_REQUEST['mobile'])=='')
			{
				showErr($GLOBALS['lang']['FILL_MOBILE_PHONE'],$ajax);
	
			}
			if(!check_mobile(trim($_REQUEST['mobile'])))
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'],$ajax);
			}
			if(!$data['delivery_info'])
			{
				showErr($GLOBALS['lang']['PLEASE_SELECT_DELIVERY'],$ajax);
			}
		}
	
		if(round($data['pay_price'],4)>0&&!$data['payment_info'])
		{
			showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT'],$ajax);
		}
		//结束验证订单接交信息
	
		//开始生成订单
		$now = NOW_TIME;
		$order['type'] = 0; //普通订单
		$order['user_id'] = $user_id;
		$order['create_time'] = $now;
		$order['total_price'] = $data['pay_total_price'];  //应付总额  商品价 - 会员折扣 + 运费 + 支付手续费
		$order['pay_amount'] = 0;
		$order['pay_status'] = 0;  //新单都为零， 等下面的流程同步订单状态
		$order['delivery_status'] = $data['is_delivery']==0?5:0;
		$order['order_status'] = 0;  //新单都为零， 等下面的流程同步订单状态
		$order['return_total_score'] = $data['return_total_score'];  //结单后送的积分
		$order['return_total_money'] = $data['return_total_money'];  //结单后送的现金
		$order['memo'] = strim($_REQUEST['memo']);
		$order['region_lv1'] = intval($_REQUEST['region_lv1']);
		$order['region_lv2'] = intval($_REQUEST['region_lv2']);
		$order['region_lv3'] = intval($_REQUEST['region_lv3']);
		$order['region_lv4'] = intval($_REQUEST['region_lv4']);
		$order['address']	=	strim($_REQUEST['address']);
		$order['mobile']	=	strim($_REQUEST['mobile']);
		$order['consignee']	=	strim($_REQUEST['consignee']);
		$order['zip']	=	strim($_REQUEST['zip']);
		$order['deal_total_price'] = $data['total_price'];   //团购商品总价
		$order['discount_price'] = $data['user_discount'];
		$order['delivery_fee'] = $data['delivery_fee'];
		$order['ecv_money'] = 0;
		$order['account_money'] = 0;
		$order['ecv_sn'] = '';
		$order['delivery_id'] = $data['delivery_info']['id'];
		$order['payment_id'] = $data['payment_info']['id'];
		$order['payment_fee'] = $data['payment_fee'];
		$order['payment_fee'] = $data['payment_fee'];
		$order['bank_id'] = strim($_REQUEST['bank_id']);
	
		foreach($data['promote_description'] as $promote_item)
		{
			$order['promote_description'].=$promote_item."<br />";
		}
		//更新来路
		$order['referer'] =	$GLOBALS['referer'];
		$user_info = es_session::get("user_info");
		$order['user_name'] = $user_info['user_name'];
		
		/** 更新会员手机号
		$coupon_mobile = htmlspecialchars(addslashes(trim($_REQUEST['coupon_mobile'])));
		if($coupon_mobile!='')
			$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = '".$coupon_mobile."' where id = ".intval($user_info['id']));
		*/
		
		if($user_info['mobile']=="")
		{			
			$user_mobile = strim($_REQUEST['user_mobile']);
			
			if($user_mobile=="")
			{
				$data = array();
				$data['status'] = false;
				$data['info']	=  "请输入手机号";
				$data['jump']  = "";
				ajax_return($data);
			}
			
			if(!check_mobile($user_mobile))
			{
				$data = array();
				$data['status'] = false;
				$data['info']	=  "手机号格式不正确";
				$data['jump']  = "";
				ajax_return($data);
			}
			
			if(app_conf("SMS_ON")==1)
			{
				$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$user_mobile."'");
				$sms_verify = strim($_POST['sms_verify']);
				if(empty($mobile_data)||$mobile_data['code']!=$sms_verify)
				{
					$data = array();
					$data['status'] = false;
					$data['info']	=  "手机验证码错误";
					$data['jump']  = "";
					ajax_return($data);
				}
			}
			
			$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = '".$user_mobile."' where id = ".$user_info['id'],"SILENT");
			if($GLOBALS['db']->affected_rows()>0)
			{
				$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$user_mobile."'"); //删除验证码
			}
			else
			{
				$data = array();
				$data['status'] = false;
				$data['info']	=  "手机号已被注册";
				$data['jump']  = "";
				ajax_return($data);
			}
		}
		
		
	
		do
		{
			$order['order_sn'] = to_date(NOW_TIME,"Ymdhis").rand(10,99);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'INSERT','','SILENT');
			$order_id = intval($GLOBALS['db']->insert_id());
		}while($order_id==0);
	
		//生成商户的运费记录
		$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order_supplier_fee where order_id = ".$order_id);
		foreach($data['delivery_fee_supplier'] as $key=>$fee)
		{			
				$sp_id = str_replace("sid_","",$key);			
				$fee_data = array();
				$fee_data['order_id'] = $order_id;
				$fee_data['supplier_id'] = $sp_id;
				$fee_data['delivery_fee'] = $fee;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_supplier_fee",$fee_data);
			
		}
		
		//生成订单商品
		foreach($goods_list as $k=>$v)
		{
			$deal_info = load_auto_cache("deal",array("id"=>$v['deal_id']));
			$goods_item = array();
			
			//关于fx
			if($deal_info['is_fx'])
			{
				$fx_user_id = intval($GLOBALS['ref_uid']);
				if($fx_user_id)
				{
					$user_deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_deal where deal_id = '".$deal_info['id']."' and user_id = '".$fx_user_id."'");
					if($user_deal||$deal_info['is_fx'])
						$goods_item['fx_user_id'] =  $fx_user_id;
				}
			}
			//关于fx
			
			$goods_item['deal_id'] = $v['deal_id'];
			$goods_item['number'] = $v['number'];
			$goods_item['unit_price'] = $v['unit_price'];
			$goods_item['total_price'] = $v['total_price'];
			$goods_item['name'] = $v['name'];
			$goods_item['sub_name'] = $v['sub_name'];
			$goods_item['attr'] = $v['attr'];
			$goods_item['verify_code'] = $v['verify_code'];
			$goods_item['order_id'] = $order_id;
			$goods_item['return_score'] = $v['return_score'];
			$goods_item['return_total_score'] = $v['return_total_score'];
			$goods_item['return_money'] = $v['return_money'];
			$goods_item['return_total_money'] = $v['return_total_money'];
			$goods_item['buy_type']	=	$v['buy_type'];
			$goods_item['attr_str']	=	$v['attr_str'];
			$goods_item['add_balance_price'] = $v['add_balance_price'];
			$goods_item['add_balance_price_total'] = $v['add_balance_price'] * $v['number'];
			$goods_item['balance_unit_price'] = $deal_info['balance_price'];
			$goods_item['balance_total_price'] = $deal_info['balance_price'] * $v['number'];
			$goods_item['delivery_status'] = $deal_info['is_delivery']==1?0:5;
			$goods_item['is_coupon'] = $deal_info['is_coupon'];
			$goods_item['deal_icon'] = $deal_info['icon'];
			$goods_item['supplier_id'] = $deal_info['supplier_id'];
			$goods_item['is_refund'] = $deal_info['is_refund'];
			$goods_item['user_id'] = $user_id;
			$goods_item['order_sn'] = $order['order_sn'];
			$goods_item['is_shop'] = $deal_info['is_shop'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_item",$goods_item,'INSERT','','SILENT');
		}
	
		//开始更新订单表的deal_ids
		$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set deal_ids = '".$deal_ids."' where id = ".$order_id);
	
		$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cart where session_id = '".$session_id."'");
		load_cart_list(true);
	
		if($data['is_delivery']==1)
		{
			//保存收款人
			$consignee_id = intval($_REQUEST['consignee_id']);
			$user_consignee = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where user_id = ".$order['user_id']." and id = ".$consignee_id);
			$user_consignee['region_lv1'] = intval($_REQUEST['region_lv1']);
			$user_consignee['region_lv2'] = intval($_REQUEST['region_lv2']);
			$user_consignee['region_lv3'] = intval($_REQUEST['region_lv3']);
			$user_consignee['region_lv4'] = intval($_REQUEST['region_lv4']);
			$user_consignee['address']	=	strim($_REQUEST['address']);
			$user_consignee['mobile']	=	strim($_REQUEST['mobile']);
			$user_consignee['consignee']	=	strim($_REQUEST['consignee']);
			$user_consignee['zip']	=	strim($_REQUEST['zip']);
			$user_consignee['user_id']	=	$order['user_id'];
			if(intval($user_consignee['id'])==0)
			{
				//新增
				$user_consignee['is_default'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'INSERT','','SILENT');
			}
			else
			{
				//更新
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'UPDATE','id='.$user_consignee['id'],'SILENT');
				rm_auto_cache("consignee_info",array("consignee_id"=>intval($user_consignee['id'])));
			}
			
		}
	
	
	
		//生成order_id 后
		//1. 代金券支付
		$ecv_data = $data['ecv_data'];
		if($ecv_data)
		{
			$ecv_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Voucher'");
			if($ecv_data['money']>$order['total_price'])$ecv_data['money'] = $order['total_price'];
			$payment_notice_id = make_payment_notice($ecv_data['money'],$order_id,$ecv_payment_id,"",$ecv_data['id']);
			require_once APP_ROOT_PATH."system/payment/Voucher_payment.php";
			$voucher_payment = new Voucher_payment();
			$voucher_payment->direct_pay($ecv_data['sn'],$ecv_data['password'],$payment_notice_id);
		}
	
		//2. 余额支付
		$account_money = $data['account_money'];
		if(floatval($account_money) > 0)
		{
			$account_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Account'");
			$payment_notice_id = make_payment_notice($account_money,$order_id,$account_payment_id);
			require_once APP_ROOT_PATH."system/payment/Account_payment.php";
			$account_payment = new Account_payment();
			$account_payment->get_payment_code($payment_notice_id);
		}
	
		//3. 相应的支付接口
		$payment_info = $data['payment_info'];
		if($payment_info&&$data['pay_price']>0)
		{
			$payment_notice_id = make_payment_notice($data['pay_price'],$order_id,$payment_info['id']);
			//创建支付接口的付款单
		}
	
		$rs = order_paid($order_id);
		update_order_cache($order_id);
		if($rs)
		{
			$data = array();
			$data['info'] = "";
			$data['jump'] = url("index","payment#done",array("id"=>$order_id));
			ajax_return($data); //支付成功
				
		}
		else
		{
			distribute_order($order_id);
			$data = array();
			$data['info'] = "";
			$data['jump'] = url("index","payment#pay",array("id"=>$payment_notice_id));
			ajax_return($data); 
		}
	}
	
	
	
	public function order()
	{
			
		global_run();
		init_app_page();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}

		
		$id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and is_delete = 0 and pay_status <> 2 and order_status <> 1 and user_id =".intval($GLOBALS['user_info']['id']));
		if(!$order_info)
		{
			app_redirect(url("index"));
		}
		if($order_info['type']==1)
		{
			app_redirect(url("index","uc_money#incharge"));
		}
		$GLOBALS['tmpl']->assign('order_info',$order_info);
		$cart_list = $GLOBALS['db']->getAll("select doi.*,d.id as did,d.icon,d.uname as duname from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where doi.order_id = ".$order_info['id']);
		$cart_list_group = cart_list_group($cart_list);
		
		foreach($cart_list_group as $k=>$v)
		{
			$cart_list_group[$k]['supplier'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = '".$v['supplier_id']."'");
			$cart_list_group[$k]['supplier'] = $cart_list_group[$k]['supplier']?$cart_list_group[$k]['supplier']:app_conf("SHOP_TITLE")."直营";
		}
		if(!$cart_list)
		{
			app_redirect(url("index"));
		}
		else
		{
			foreach($cart_list as $k=>$v)
			{
				$bind_data = array();
				$bind_data['id'] = $v['id'];
				if($v['buy_type']==1)
				{
					$cart_list[$k]['unit_price'] = abs($v['return_score']);
					$cart_list[$k]['total_price'] = abs($v['return_total_score']);
				}
					
				if($v['duname']!="")
					$cart_list[$k]['url'] = url("index","deal#".$v['duname']);
				else
					$cart_list[$k]['url'] = url("index","deal#".$v['did']);
			}
		}
		
		//输出购物车内容
		$GLOBALS['tmpl']->assign("cart_list",$cart_list);
		$GLOBALS['tmpl']->assign("cart_list_group",$cart_list_group);
		$GLOBALS['tmpl']->assign('total_price',$order_info['deal_total_price']);
	
		$is_delivery = 0;
		foreach($cart_list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select is_delivery from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
			{
				$is_delivery = 1;
				break;
			}
		}
	
		if($is_delivery)
		{
			//输出配送方式
			$consignee_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign("consignee_count",intval($consignee_count));
		}
	
		//配送方式由ajax由 consignee 中的地区动态获取
	
		//输出支付方式
		$payment_list = load_auto_cache("cache_payment");
	
		foreach($cart_list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
			{
				$define_payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_payment where deal_id = ".$v['deal_id']);
				$define_payment = array();
				foreach($define_payment_list as $kk=>$vv)
				{
					array_push($define_payment,$vv['payment_id']);
				}
				foreach($payment_list as $k=>$v)
				{
					if(in_array($v['id'],$define_payment))
					{
						unset($payment_list[$k]);
					}
				}

			}
		}
		$icon_paylist = array(); //用图标展示的支付方式
		$disp_paylist = array(); //特殊的支付方式(Voucher,Account,Otherpay)
		$bank_paylist = array(); //网银直连
		
		$wx_payment = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name = 'Wwxjspay'");
		if($wx_payment)
		{
			$wx_payment['config'] = unserialize($wx_payment['config']);
			if($wx_payment['config']['scan']==1)
			{
				$directory = APP_ROOT_PATH."system/payment/";
				$file = $directory. '/' .$wx_payment['class_name']."_payment.php";
				if(file_exists($file))
				{
					require_once($file);
					$payment_class = $wx_payment['class_name']."_payment";
					$payment_object = new $payment_class();
					$wx_payment['display_code'] = $payment_object->get_web_display_code();
					$disp_paylist[] = $wx_payment;
				}
			}
		}
		
		foreach($payment_list as $k=>$v)
		{
			if($v['class_name']=="Voucher"||$v['class_name']=="Account"||$v['class_name']=="Otherpay")
			{
				if($v['class_name']=="Account")
				{
					$directory = APP_ROOT_PATH."system/payment/";
					$file = $directory. '/' .$v['class_name']."_payment.php";
					if(file_exists($file))
					{
						require_once($file);
						$payment_class = $v['class_name']."_payment";
						$payment_object = new $payment_class();
						$v['display_code'] = $payment_object->get_display_code();
					}
				}
				
				if($v['class_name']=="Account"||$v['class_name']=="Otherpay") //代金券在订单修改时不再允许支付
				$disp_paylist[] = $v;
			}
			else
			{
				if($v['is_bank']==1)
					$bank_paylist[] = $v;
				else
					$icon_paylist[] = $v;
			}
		}
		
		$GLOBALS['tmpl']->assign("icon_paylist",$icon_paylist);
		$GLOBALS['tmpl']->assign("disp_paylist",$disp_paylist);
		$GLOBALS['tmpl']->assign("bank_paylist",$bank_paylist);
		
	
		$GLOBALS['tmpl']->assign("is_delivery",$is_delivery);
	
		$is_coupon = 0;
		foreach($cart_list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select is_coupon from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
			{
				$is_coupon = 1;
				break;
			}
		}
		$GLOBALS['tmpl']->assign("is_coupon",$is_coupon);
		$GLOBALS['tmpl']->assign("coupon_name",app_conf("COUPON_NAME"));
	
		$GLOBALS['tmpl']->assign("show_payment",true);
		
		$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		
		
		//关于短信发送的条件
		$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
		$GLOBALS['tmpl']->assign("sms_ipcount",load_sms_ipcount());
		
		//购物车检测页
		$GLOBALS['tmpl']->display("cart_check.html");
	
	}
	
	public function order_done()
	{
		require_once APP_ROOT_PATH."system/model/deal.php";
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		global_run();
		$ajax = 1;
		$user_info = $GLOBALS['user_info'];
		$id = intval($_REQUEST['id']); //订单号
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and is_delete = 0 and user_id = ".$user_info['id']);
		if(!$order)
		{
			showErr($GLOBALS['lang']['INVALID_ORDER_DATA'],$ajax);
		}
			
		if($order['refund_status'] == 1)
		{
			showErr($GLOBALS['lang']['REFUNDING_CANNOT_PAY'],$ajax);
		}
		if($order['refund_status'] == 2)
		{
			showErr($GLOBALS['lang']['REFUNDED_CANNOT_PAY'],$ajax);
		}
		$region4_id = intval($_REQUEST['region_lv4']);
		$region3_id = intval($_REQUEST['region_lv3']);
		$region2_id = intval($_REQUEST['region_lv2']);
		$region1_id = intval($_REQUEST['region_lv1']);
	
		if ($region4_id==0)
		{
			if ($region3_id==0)
			{
				if ($region2_id==0)
				{
					$region_id = $region1_id;
				}
				else
					$region_id = $region2_id;
			}
			else
				$region_id = $region3_id;
		}
		else
			$region_id = $region4_id;
		$delivery_id = intval($_REQUEST['delivery']);
		$payment = intval($_REQUEST['payment']);
		$account_money = floatval($_REQUEST['account_money']);
		$all_account_money = intval($_REQUEST['all_account_money']);
		$ecvsn = $_REQUEST['ecvsn']?strim($_REQUEST['ecvsn']):'';
		$ecvpassword = $_REQUEST['ecvpassword']?strim($_REQUEST['ecvpassword']):'';
	
	
		$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order['id']);
	
	
		//验证购物车
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url("index","user#login"));
		}
	
		//验证支付方式的支持
		foreach($goods_list as $k=>$row)
		{
			if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$row['deal_id'])==1)
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_payment where deal_id = ".$row['deal_id']." and payment_id = ".$payment))
				{
					showErr($GLOBALS['lang']['INVALID_PAYMENT'],$ajax);
				}
			}
		}
		//结束验证购物车
		$deal_s = $GLOBALS['db']->getAll("select distinct(deal_id) as deal_id from ".DB_PREFIX."deal_order_item where order_id = ".$order['id']);
	
		//如果属于未支付的
		if($order['pay_status'] == 0)
		{				
			foreach($deal_s as $row)
			{	
				$checker = check_deal_number($row['deal_id'],0);
				if($checker['status']==0)
				{
					showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']],$ajax);
						
				}
			}
			
			foreach($goods_list as $k=>$v)
			{
				$checker = check_deal_number_attr($v['deal_id'],$v['attr_str'],0);
				if($checker['status']==0)
				{
					showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']],$ajax);				
				}
			}
			
			//验证商品是否过期
			foreach($deal_s as $row)
			{			
				$checker = check_deal_time($row['deal_id']);
				if($checker['status']==0)
				{
					showErr($checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']],$ajax);
				}
			}
		}
	
		
	
		//开始验证订单接交信息
		require_once APP_ROOT_PATH."system/model/cart.php";
		$data = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list,$order['account_money'],$order['ecv_money']);
		
	
		if($data['is_delivery'] == 1)
		{
			foreach($data['delivery_fee_supplier'] as $k=>$v)
			{
				if($v<0)
				{
					showErr("部份商品不支持".$data['delivery_info']['name'],$ajax);
				}
			}
			//配送验证
			if(!$data['region_info']||$data['region_info']['region_level'] != 4)
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE_ADDRESS'],$ajax);
			}
			if(trim($_REQUEST['consignee'])=='')
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_CONSIGNEE'],$ajax);
			}
			if(trim($_REQUEST['address'])=='')
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_ADDRESS'],$ajax);
			}
			if(trim($_REQUEST['zip'])=='')
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_ZIP'],$ajax);
			}
			if(trim($_REQUEST['mobile'])=='')
			{
				showErr($GLOBALS['lang']['FILL_MOBILE_PHONE'],$ajax);
	
			}
			if(!check_mobile(trim($_REQUEST['mobile'])))
			{
				showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'],$ajax);
			}
			if(!$data['delivery_info'])
			{
				showErr($GLOBALS['lang']['PLEASE_SELECT_DELIVERY'],$ajax);
			}
		}
	
		if(round($data['pay_price'],4)>0&&!$data['payment_info'])
		{
			showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT'],$ajax);
		}
		//结束验证订单接交信息
	
		//开始修正订单
		$now = NOW_TIME;
		$order['total_price'] = $data['pay_total_price'];  //应付总额  商品价 - 会员折扣 + 运费 + 支付手续费
		$order['memo'] = strim($_REQUEST['memo']);
		$order['region_lv1'] = intval($_REQUEST['region_lv1']);
		$order['region_lv2'] = intval($_REQUEST['region_lv2']);
		$order['region_lv3'] = intval($_REQUEST['region_lv3']);
		$order['region_lv4'] = intval($_REQUEST['region_lv4']);
		$order['address']	=	strim($_REQUEST['address']);
		$order['mobile']	=	strim($_REQUEST['mobile']);
		$order['consignee']	=	strim($_REQUEST['consignee']);
		$order['zip']	=	strim($_REQUEST['zip']);
		$order['delivery_fee'] = $data['delivery_fee'];
		$order['delivery_id'] = $data['delivery_info']['id'];
		$order['payment_id'] = $data['payment_info']['id'];
		$order['payment_fee'] = $data['payment_fee'];
		$order['discount_price'] = $data['user_discount'];
		$order['bank_id'] = strim($_REQUEST['bank_id']);
	
		$order['promote_description'] = "";
		foreach($data['promote_description'] as $promote_item)
		{
			$order['promote_description'].=$promote_item."<br />";
		}
	
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'UPDATE','id='.$order['id'],'SILENT');
		
		
		//生成商户的运费记录
		$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order_supplier_fee where order_id = ".$order['id']);
		foreach($data['delivery_fee_supplier'] as $key=>$fee)
		{
				$sp_id = str_replace("sid_","",$key);
				$fee_data = array();
				$fee_data['order_id'] = $order['id'];
				$fee_data['supplier_id'] = $sp_id;
				$fee_data['delivery_fee'] = $fee;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_supplier_fee",$fee_data);
		}
	
		if($data['is_delivery']==1)
		{
			//保存收款人
			$consignee_id = intval($_REQUEST['consignee_id']);
			$user_consignee = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_consignee where user_id = ".$order['user_id']." and id = ".$consignee_id);
			$user_consignee['region_lv1'] = intval($_REQUEST['region_lv1']);
			$user_consignee['region_lv2'] = intval($_REQUEST['region_lv2']);
			$user_consignee['region_lv3'] = intval($_REQUEST['region_lv3']);
			$user_consignee['region_lv4'] = intval($_REQUEST['region_lv4']);
			$user_consignee['address']	=	strim($_REQUEST['address']);
			$user_consignee['mobile']	=	strim($_REQUEST['mobile']);
			$user_consignee['consignee']	=	strim($_REQUEST['consignee']);
			$user_consignee['zip']	=	strim($_REQUEST['zip']);
			$user_consignee['user_id']	=	$order['user_id'];
			if(intval($user_consignee['id'])==0)
			{
				//新增,修改订单不新增配送
// 				$user_consignee['is_default'] = 1;
// 				$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'INSERT','','SILENT');
			}
			else
			{
				//更新
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_consignee",$user_consignee,'UPDATE','id='.$user_consignee['id'],'SILENT');
				rm_auto_cache("consignee_info",array("consignee_id"=>intval($user_consignee['id'])));
			}
		}
	
	
	
		//生成order_id 后
		//1. 余额支付
		$account_money = $data['account_money'];
		if(floatval($account_money) > 0)
		{
			$account_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Account'");
			$payment_notice_id = make_payment_notice($account_money,$order['id'],$account_payment_id);
			require_once APP_ROOT_PATH."system/payment/Account_payment.php";
			$account_payment = new Account_payment();
			$account_payment->get_payment_code($payment_notice_id);
		}
	
		//3. 相应的支付接口
		$payment_info = $data['payment_info'];
		if($payment_info&&$data['pay_price']>0)
		{
			$payment_notice_id = make_payment_notice($data['pay_price'],$order['id'],$payment_info['id']);
			//创建支付接口的付款单
		}
	
		$rs = order_paid($order['id']);		
		if($rs)
		{
			$data = array();
			$data['info'] = "";
			$data['jump'] = url("index","payment#done",array("id"=>$order['id']));
			ajax_return($data); //支付成功
		
		}
		else
		{
			distribute_order($order['id']);
			$data = array();
			$data['info'] = "";
			$data['jump'] = url("index","payment#pay",array("id"=>$payment_notice_id));
			ajax_return($data);
		}

	}
	
}
?>