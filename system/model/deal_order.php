<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 与订单相关的函数库
 */

/**
 * 使用团购券
 * @param unknown_type $password 密码
 * @param unknown_type $location_id 所消费的门店ID
 * @param unknown_type $account_id 执行使用的商家账号ID
 * @param unknown_type $send_return 是否要发放奖励
 * @param unknown_type $send_notify 是否发放通知(短信/邮件)
 * return:true,false true:已使用掉  false:未使用掉
 */
function use_coupon($password,$location_id=0,$account_id=0,$send_return=false,$send_notify=false)
{
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set is_balance = 1 ,location_id=".$location_id.", confirm_account = ".$account_id.",confirm_time=".NOW_TIME." where password = '".$password."' and confirm_time = 0");
	$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where password = '".$password."'");
	
	if($GLOBALS['db']->affected_rows()&&$coupon_data)
	{		
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set consume_count = consume_count + 1 where id = ".$coupon_data['order_deal_id']);
		update_order_cache($coupon_data['order_id']);
		distribute_order($coupon_data['order_id']);
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$coupon_data['order_id']);
		if($order_info)
		{
			$order_msg = "订单号".$order_info['order_sn']." ";
		}
		
		if($send_return)
		{
			if($coupon_data['coupon_money']>0||$coupon_data['coupon_score']>0)
			{
				$money = $coupon_data['coupon_money'];
				$score = $coupon_data['coupon_score'];
				require_once  APP_ROOT_PATH."system/model/user.php";
				$log = $order_msg.$password."消费券验证成功";
				modify_account(array("money"=>$money,"score"=>$score),$coupon_data['user_id'],$log);	
			}
		}
		if($send_notify)
		{
			send_use_coupon_sms(intval($coupon_data['id'])); //发送团购券确认消息
			send_use_coupon_mail(intval($coupon_data['id'])); //发送团购券确认消息
		}
		update_balance($coupon_data['id'],$coupon_data['deal_id']);
		
		
		$balance_price = $coupon_data['balance_price'] + $coupon_data['add_balance_price'];
		require_once APP_ROOT_PATH."system/model/supplier.php";
		modify_supplier_account("-".$balance_price, $coupon_data['supplier_id'], 1, $order_msg.$password."消费券验证成功");  //解冻资金
		modify_supplier_account($balance_price, $coupon_data['supplier_id'], 2, $order_msg.$password."消费券验证成功");  //等结算金额增加
		
		modify_statements($coupon_data['coupon_price'], 11, $order_msg.$password."消费券验证成功"); //增加消费额
		modify_statements($balance_price, 12, $order_msg.$password."消费券验证成功"); //增加消费额成本
		
		send_msg($coupon_data['user_id'], "消费券验证成功", "orderitem", $coupon_data['order_deal_id']);
		
		$weixin_conf = load_auto_cache("weixin_conf");
		if($weixin_conf['platform_status']==1)
		{
				$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$coupon_data['supplier_id']);
				$rs = send_wx_msg("OPENTM200738546", $order_info['user_id'], $wx_account,array("coupon_sn"=>$password));

		}
		
		auto_over_status($coupon_data['order_id']); //检测自动结单
	}
	return $coupon_data['confirm_time']>0;
}

/**
 * 收货操作：收货后发放积分，钱的返还，更新商家的结算
 * @param unknown_type $delivery_sn
 * @param unknown_type $order_item_id 订单商品ID，将会确认相关的所有订单的同序号发货号。
 */
function confirm_delivery($delivery_sn,$order_item_id)
{
	$order_id = $GLOBALS['db']->getOne("select order_id from ".DB_PREFIX."deal_order_item where id = '".$order_item_id."'");
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".intval($order_id));
	if($order_info)
	{
		
		$delivery_notices = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_notice where order_id = ".$order_info['id']." and notice_sn = '".$delivery_sn."'");
		$order_item_ids = array(0);
		foreach($delivery_notices as $k=>$v)
		{
			$order_item_ids[] = $v['order_item_id'];
		}		
		$sql = "update ".DB_PREFIX."deal_order_item set is_arrival = 1,consume_count = consume_count + 1 where is_arrival <> 1 and id in (".implode(",", $order_item_ids).")";
		$GLOBALS['db']->query($sql);		
		if($GLOBALS['db']->affected_rows())
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."delivery_notice set is_arrival = 1,arrival_time = '".NOW_TIME."' where notice_sn = '".$delivery_sn."' and is_arrival <> 1 and order_id = ".$order_info['id']);
			$return_total = $GLOBALS['db']->getRow("select sum(return_total_score) as return_total_score,
					sum(return_total_money) as return_total_money,
					sum(total_price) as total_price,
					sum(balance_total_price) as balance_total_price,
					sum(add_balance_price_total) as add_balance_price_total from ".DB_PREFIX."deal_order_item where id in (".implode(",", $order_item_ids).")");

			$log = $order_info['order_sn']."订单已收货";
			if($return_total['return_total_score']>0||$return_total['return_total_money']>0)
			{
				$money = $return_total['return_total_money'];
				$score = $return_total['return_total_score'];
				require_once  APP_ROOT_PATH."system/model/user.php";				
				modify_account(array("money"=>$money,"score"=>$score),$order_info['user_id'],$log);
			}
	
			//订单商品
			$sql = "update ".DB_PREFIX."deal_order_item set is_balance = 1 where id in (".implode(",", $order_item_ids).") and is_balance = 0";
			$GLOBALS['db']->query($sql);
			
			$is_refuse_delivery = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."delivery_notice where is_arrival = 2 and order_id = ".$order_id);
			if(!$is_refuse_delivery)
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set is_refuse_delivery = 0 where id = ".$order_id);
			
			$balance_list = $GLOBALS['db']->getAll("select sum(balance_total_price) as balance_total_price,sum(add_balance_price_total) as add_balance_price_total,supplier_id from ".DB_PREFIX."deal_order_item  where id in (".implode(",", $order_item_ids).") group by supplier_id");
			foreach($balance_list as $k=>$v)
			{
				$balance_price = $v['balance_total_price'] + $v['add_balance_price_total'];
				require_once APP_ROOT_PATH."system/model/supplier.php";
				modify_supplier_account("-".$balance_price, $v['supplier_id'], 1, $log);  //解冻资金
				modify_supplier_account($balance_price, $v['supplier_id'], 2, $log);  //等结算金额增加
				
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_supplier_fee set is_arrival = 1 where is_arrival = 0 and supplier_id = ".$v['supplier_id']." and order_id = ".$order_info['id']);
				if($GLOBALS['db']->affected_rows()>0)
				{
					$delivery_fee = $GLOBALS['db']->getRow("select delivery_fee from ".DB_PREFIX."deal_order_supplier_fee where supplier_id =".$v['supplier_id']." and order_id = ".$order_info['id']);
					modify_supplier_account($delivery_fee['delivery_fee'], $v['supplier_id'], 2, $log.",结算运费");
				}			
				
			}
			
			
			
			$stat_balance_price = $return_total['balance_total_price']+$return_total['add_balance_price_total'];
			modify_statements($return_total['total_price'], 11, $log); //增加消费额
			modify_statements($stat_balance_price, 12,$log); //增加消费额成本
			
			auto_over_status($order_info['id']); //检测自动结单
			update_order_cache($order_info['id']);
			distribute_order($order_info['id']);

			$weixin_conf = load_auto_cache("weixin_conf");
			if($weixin_conf['platform_status']==1)
			{
				$supplier_list = $GLOBALS['db']->getAll("select distinct(supplier_id) from ".DB_PREFIX."deal_order_item where id in (".implode(",", $order_item_ids).")");
				foreach($supplier_list as $row)
				{					
						$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$row['supplier_id']);
						$order_item_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_order_item where supplier_id = ".$row['supplier_id']." and delivery_status = 1");
						send_wx_msg("OPENTM202314085", $order_info['user_id'], $wx_account,array("order_item_id"=>$order_item_id));
					
				}
			}
			
			return true;
		}
	}
	return false;
}



/**
 * 维权没收到货
 * @param unknown_type $delivery_sn
 * @param unknown_type $order_item_id 订单商品ID，将会确认相关的所有订单的同序号发货号。
 */
function refuse_delivery($delivery_sn,$order_item_id)
{
	$order_id = $GLOBALS['db']->getOne("select order_id from ".DB_PREFIX."deal_order_item where id = '".$order_item_id."'");
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".intval($order_id));
	if($order_info)
	{

		$delivery_notices = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_notice where order_id = ".$order_info['id']." and notice_sn = '".$delivery_sn."'");
		$order_item_ids = array(0);
		foreach($delivery_notices as $k=>$v)
		{
			$order_item_ids[] = $v['order_item_id'];
		}
		$sql = "update ".DB_PREFIX."deal_order_item set is_arrival = 2 where is_arrival = 0 and id in (".implode(",", $order_item_ids).")";
		$GLOBALS['db']->query($sql);
		if($GLOBALS['db']->affected_rows()||true)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set is_refuse_delivery = 1 where id = ".$order_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."delivery_notice set is_arrival = 2 where notice_sn = '".$delivery_sn."' and is_arrival = 0 and order_id = ".$order_info['id']);

			$log = "订单：".$order_info['order_sn'].",运单：".$delivery_sn."未收到货";

			order_log($log, $order_id);
			
			update_order_cache($order_info['id']);
			distribute_order($order_info['id']);
			return true;
		}
	}
	return false;
}

/**
 * 使用优惠券
 * @param unknown_type $password 密码
 * @param unknown_type $location_id 所消费的门店ID
 * @param unknown_type $account_id 执行使用的商家账号ID
 * @param unknown_type $send_return 是否要发放奖励
 * @param unknown_type $send_notify 是否发放通知(短信/邮件)
 */
function use_youhui($password,$location_id=0,$account_id=0,$send_return=false,$send_notify=false)
{
	$GLOBALS['db']->query("update ".DB_PREFIX."youhui_log set location_id=".$location_id.", confirm_id = ".$account_id.",confirm_time=".NOW_TIME." where youhui_sn = '".$password."' and confirm_time = 0");
	$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_log where youhui_sn = '".$password."'");
	if($GLOBALS['db']->affected_rows()&&$data)
	{
		if($send_return)
		{
			if($data['return_money']>0||$data['return_score']>0||$data['return_point']>0)
			{
				$money = $data['return_money'];
				$score = $data['return_score'];
				$point = $data['return_point'];
				require_once  APP_ROOT_PATH."system/model/user.php";
				$log = "验证优惠券,序列号：".$password;
				modify_account(array("money"=>$money,"score"=>$score,"point"=>$point),$data['user_id'],$log);
			}
		}
	}
	return $data['confirm_time']>0;
}

/**
 * 使用活动报名
 * @param unknown_type $password 密码
 * @param unknown_type $location_id 所消费的门店ID
 * @param unknown_type $account_id 执行使用的商家账号ID
 * @param unknown_type $send_return 是否要发放奖励
 * @param unknown_type $send_notify 是否发放通知(短信/邮件)
 */
function use_event($password,$location_id=0,$account_id=0,$send_return=false,$send_notify=false)
{
	$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set location_id=".$location_id.", confirm_id = ".$account_id.",confirm_time=".NOW_TIME." where sn = '".$password."' and confirm_time = 0");
	$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where sn = '".$password."'");
	if($GLOBALS['db']->affected_rows()&&$data)
	{
		if($send_return)
		{
			if($data['return_money']>0||$data['return_score']>0||$data['return_point']>0)
			{
				$money = $data['return_money'];
				$score = $data['return_score'];
				$point = $data['return_point'];
				require_once  APP_ROOT_PATH."system/model/user.php";
				$log = "验证活动,序列号:".$password;
				modify_account(array("money"=>$money,"score"=>$score,"point"=>$point),$data['user_id'],$log);
			}
		}
	}
	return $data['confirm_time']>0;
}


/**
 * 自动结单检测，如通过则结单
 * 自动结单规则
 * 注：自动结单条件
 * 1. 团购券全部验证成功 
 * 2. 商品全部已收货
 * 3. 商品验证部份收货部份，其余退款
 * 结单后的商品不可再退款，不可再验证，不可再发货，可删除
 * @param unknown_type $order_id
 * return array("status"=>bool,"info"=>str)
 */
function auto_over_status($order_id)
{	
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	if($order_info)
	{
		if($order_info['pay_status']<>2)
		{
			return array("status"=>false,"info"=>"订单未支付");
		}
		if($order_info['order_status']<>0)
		{
			return array("status"=>false,"info"=>"订单已结单");
		}
		
		if($order_info['type'] == 0)
		{
			//团购券未验证且未退款的数量为0
			$coupon_less = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where order_id = ".$order_id." and confirm_time = 0 and refund_status <> 2");
			//全部未收货且未退款的数量为0
			$delivery_less = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id." and delivery_status <> 5 and is_arrival <> 1 and refund_status <> 2");
			
			if(($coupon_less==0&&$delivery_less==0)||$order_info['extra_status']==2)//补充，发货失败自动结单
			{
				over_order($order_id); 
			}
		}
		else
		{
			over_order($order_id); //充值单只要支付过就结单
		}	
		return array("status"=>true,"info"=>"结单成功");
	}
	else
	{
		return array("status"=>false,"info"=>"订单不存在");
	}
}

/**
 * 结单操作，结单操作将发放邀请返利
 * @param unknown_type $order_id
 */
function over_order($order_id)
{	
	
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where order_status = 0 and id = ".$order_id);
	if($order_info)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set order_status = 1,is_refuse_delivery = 0 where order_status = 0 and id = ".$order_id);
		if(!$GLOBALS['db']->affected_rows())
		{
			return;  //结单失败
		}
		
		order_log("订单完结", $order_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set is_valid = 2 where order_id = ".$order_id);
		distribute_order($order_id);
		
		//==关于fx==//
		//订单结单成功后，开始为订单商品进行分销佣金利润计算
		if(defined("FX_LEVEL")&&$order_info['pay_status']==2)
		{
			require_once APP_ROOT_PATH."system/model/fx.php";
			send_fx_order_salary($order_id);
		}
		//==关于fx==//
		
		//结单后只要有未退款的才可返利
		$coupon_refunded = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where order_id = ".$order_id." and refund_status = 2");
		$order_item_refunded = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id." and refund_status = 2");
		if($order_item_refunded>0||$coupon_refunded>0||$order_info['is_delete']==1)
		{
			return; //不再返利
		}
		
		$goods_list = $GLOBALS['db']->getAll("select deal_id,sum(number) as num from ".DB_PREFIX."deal_order_item where order_id = ".$order_id." group by deal_id");
		//返利
		//开始处理返利，只创建返利， 发放将与msg_list的自动运行一起执行
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
		//开始查询所购买的列表中支不支持促销
		$is_referrals = 1; //默认为返利
		foreach($goods_list as $k=>$v)
		{
			$is_referrals = $GLOBALS['db']->getOne("select is_referral from ".DB_PREFIX."deal where id = ".$v['deal_id']);
			if($is_referrals == 0)
			{
				break;
			}
		}
		if($user_info['referral_count']<app_conf("REFERRAL_LIMIT")&&$is_referrals == 1)
		{
			//开始返利给推荐人
			$parent_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_info['pid']);
			if($parent_info)
			{
				if((app_conf("REFERRAL_IP_LIMIT")==1&&$parent_info['login_ip']!=CLIENT_IP)||app_conf("REFERRAL_IP_LIMIT")==0) //IP限制
				{
					if(app_conf("INVITE_REFERRALS_TYPE")==0) //现金返利
					{
						$referral_data['user_id'] = $parent_info['id']; //初返利的会员ID
						$referral_data['rel_user_id'] = $user_info['id'];	 //被推荐且发生购买的会员ID
						$referral_data['create_time'] = NOW_TIME;
						$referral_data['money']	=	app_conf("INVITE_REFERRALS");
						$referral_data['order_id']	=	$order_info['id'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."referrals",$referral_data); //插入
					}
					else
					{
						$referral_data['user_id'] = $parent_info['id']; //初返利的会员ID
						$referral_data['rel_user_id'] = $user_info['id'];	 //被推荐且发生购买的会员ID
						$referral_data['create_time'] = NOW_TIME;
						$referral_data['score']	=	app_conf("INVITE_REFERRALS");
						$referral_data['order_id']	=	$order_info['id'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."referrals",$referral_data); //插入
					}
					$GLOBALS['db']->query("update ".DB_PREFIX."user set referral_count = referral_count + 1 where id = ".$user_info['id']);
				}
					
			}
		}
		//返利over
		
	}
}

/**
 * 删除订单至回收站(历史订单)
 * @param unknown_type $order_id
 * 返回:true/false
 */
function del_order($order_id)
{
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id." and order_status = 1");
	if($order_info)
	{
		unset($order_info['id']);
		unset($order_info['deal_order_item']);
		$order_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
		$order_info['history_deal_order_item'] = serialize($order_items);
		$order_info['history_deal_coupon'] = serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where order_id = ".$order_id));
		$order_info['history_deal_order_log'] =  serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_log where order_id = ".$order_id));
		$order_info['history_delivery_notice'] =  serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_notice where order_id = ".$order_id));
		$order_info['history_payment_notice'] = serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_id));
		$order_info['history_message'] = serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."message where rel_table = 'deal_order' and rel_id = ".$order_id));
		$order_info['history_delivery_fee'] = serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_supplier_fee where order_id = ".$order_id));
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_history",$order_info,'INSERT','','SILENT');
		if($GLOBALS['db']->insert_id())
		{
			//删除会员相关分表
			$user_order_table = get_user_order_table_name($order_info['user_id']);
			$user_order_item_table = get_user_order_item_table_name($order_info['user_id']);
			$GLOBALS['db']->query("delete from ".$user_order_table." where id = ".$order_id);
			$GLOBALS['db']->query("delete from ".$user_order_item_table." where order_id = ".$order_id);
			
			//删除商户相关表
			foreach($order_items as $item)
			{
				$supplier_order_table = get_supplier_order_table_name($item['supplier_id']);
				$supplier_order_item_table = get_supplier_order_item_table_name($item['supplier_id']);
				$GLOBALS['db']->query("delete from ".$supplier_order_table." where id = ".$order_id);
				$GLOBALS['db']->query("delete from ".$supplier_order_item_table." where order_id = ".$order_id);
			}
			
			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order where id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
			//$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_coupon where order_id = ".$order_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set order_id = -1 where order_id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order_log where order_id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."delivery_notice where order_id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."payment_notice where order_id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."message where rel_table='deal_order' and rel_id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order_supplier_fee where order_id = ".$order_id);
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}


/**
 * 某个商品项的退款,只为具体的某款不发券商品退款,主要用于商户审核退款，自动计算退款额
 * @param unknown_type $order_item_id
 */
function refund_item($order_item_id)
{
	$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
	$order_id = $data['order_id'];
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = '".$order_id."'");
	if($order_info['order_status']==0&&$order_info['pay_status']==2&&$data)
	{
		if($data['refund_status']!=2)
		{
			if($data['is_coupon']==0)
			{						
				$price = $data['total_price'];
				$balance_price = $data['balance_total_price'] + $data['add_balance_price_total'];
				
				$oi = $order_item_id;				
				
				$supplier_id = $data['supplier_id'];		
					
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 2,is_arrival = 0 where id = ".$order_item_id);

				//同商户需发货但未退款的商品数量，如为零表示所有商品均已退货，退运费
				$supplier_remain_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id." and supplier_id = ".$data['supplier_id']." and refund_status <> 2 and delivery_status <> 5");
				if(intval($supplier_remain_item_count)==0)
				{
					$delivery_fee = $GLOBALS['db']->getOne("select delivery_fee from ".DB_PREFIX."deal_order_supplier_fee where order_id = ".$order_id." and supplier_id = ".$data['supplier_id']);					
				}
				
				$refund_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where (refund_status = 1 or is_arrival = 2) and order_id = ".$order_id);
				$coupon_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where refund_status = 1 and order_id = ".$order_id);
				if(intval($refund_item_count)==0&&intval($coupon_item_count)==0)
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount + ".$price.",refund_money = refund_money + ".$price.",refund_status = 2,after_sale = 1,is_refuse_delivery=0 where id = ".$order_id);
				else
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount + ".$price.",refund_money = refund_money + ".$price.",is_refuse_delivery=0 where id = ".$order_id);
								
								
				if($price>0)
				{
					require_once APP_ROOT_PATH."system/model/user.php";
					modify_account(array("money"=>$price), $order_info['user_id'],$data['name']."退款成功");
					modify_statements($price, 6, $data['name']."用户退款");
				}
				if($delivery_fee>0)
				{
					require_once APP_ROOT_PATH."system/model/user.php";
					modify_account(array("money"=>$delivery_fee), $order_info['user_id'],$data['name']."退回运费");
					modify_statements($delivery_fee, 6, $data['name']."用回退费");
				}
				
				if($balance_price>0)
				{
					require_once APP_ROOT_PATH."system/model/supplier.php";
					modify_supplier_account("-".$balance_price, $supplier_id, 1, $data['name']."用户退款"); //冻结资金减少
					modify_supplier_account($balance_price, $supplier_id, 4, $data['name']."用户退款"); //退款增加
					modify_statements($balance_price, 7, $data['name']."用户退款");
				}
				
				order_log($data['name']."退款成功 ".format_price($price), $order_id);
				auto_over_status($order_id);
				update_order_cache($order_id);
				distribute_order($order_id);
				
				send_msg($order_info['user_id'], $data['name']."退款成功 ".format_price($price), "orderitem", $oi);
				
				//发微信通知
				$weixin_conf = load_auto_cache("weixin_conf");
				if($weixin_conf['platform_status']==1)
				{
					$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$supplier_id);
					send_wx_msg("TM00430", $order_info['user_id'], $wx_account,array("order_id"=>$order_id,"refund_price"=>$price,"deal_name"=>$data['name'],"order_sn"=>$order_info['order_sn']));
				}
			}//不发券
		}
	}
}

/**
 * 退券
 * @param unknown_type $coupon_id
 */
function refund_coupon($coupon_id)
{
	$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$coupon_id);
	if($data)
	{
		$order_id = $data['order_id'];
		$supplier_id = $data['supplier_id'];
		$oi = $data['order_deal_id'];
		$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$data['order_deal_id']);
		$data['name'] = $order_item['name'];	
		if($data['deal_type']==0)//按件
		{
			$price = $order_item['unit_price'];
			$balance_price = $order_item['balance_unit_price'] + $order_item['add_balance_price'];
		}
		else
		{
			$price = $order_item['total_price'];
			$balance_price = $order_item['balance_total_price'] + $order_item['add_balance_price_total'];
		}
		
		if($data['refund_status']==2)
		{
			return;
		}
			
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set refund_status = 2 where id = ".$coupon_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 2 where id = ".$data['order_deal_id']);
			
		$refund_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where (refund_status = 1 or is_arrival = 2) and order_id = ".$order_id);
		$coupon_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where refund_status = 1 and order_id = ".$order_id);
		if(intval($refund_item_count)==0&&intval($coupon_item_count)==0)
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount + ".$price.",refund_money = refund_money + ".$price.",refund_status = 2,after_sale = 1 where id = ".$order_id);
		else
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount + ".$price.",refund_money = refund_money + ".$price." where id = ".$order_id);
	
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		
		if($price>0)
		{
			require_once APP_ROOT_PATH."system/model/user.php";
			modify_account(array("money"=>$price), $order_info['user_id'],$data['name']."退款成功");
			modify_statements($price, 6, $data['name']."退款");
		}
		
		if($balance_price>0)
		{
			require_once APP_ROOT_PATH."system/model/supplier.php";
			modify_supplier_account("-".$balance_price, $supplier_id, 1, $data['name']."退款"); //冻结资金减少
			modify_supplier_account($balance_price, $supplier_id, 4, $data['name']."退款"); //退款增加
			modify_statements($balance_price, 7, $data['name']."退款");
		}
		
		order_log($data['name']."退款成功 ".format_price($price), $order_id);
		auto_over_status($order_id);
		update_order_cache($order_id);
		distribute_order($order_id);
		
		send_msg($order_info['user_id'], $data['name']."退款成功 ".format_price($price), "orderitem", $oi);
		
		//发微信通知
		$weixin_conf = load_auto_cache("weixin_conf");
		if($weixin_conf['platform_status']==1)
		{
			$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$supplier_id);
			send_wx_msg("TM00430", $order_info['user_id'], $wx_account,array("order_id"=>$order_id,"refund_price"=>$price,"deal_name"=>$data['name'],"order_sn"=>$order_info['order_sn']));
		}
	}
}

/**
 * 拒绝退货
 * @param unknown_type $order_item_id
 */
function refuse_item($order_item_id)
{
	$oi = $order_item_id;
	$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
	if($data['refund_status']==2)
	{
		return;
	}
	if($data)
	{
		$order_id = $data['order_id'];
		$supplier_id = $data['supplier_id'];
	}
	
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 3,is_arrival = 0 where id = ".$order_item_id);
	
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_status = 3,is_refuse_delivery=0 where id = ".$order_id);
	
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	
	
	order_log($data['name']."退款不通过 ", $order_id);
	auto_over_status($order_id);
	update_order_cache($order_id);
	distribute_order($order_id);
	
	send_msg($order_info['user_id'], $data['name']."退款不通过 ", "orderitem", $oi);
}


function refuse_coupon($coupon_id)
{
	$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$coupon_id);
	if($data['refund_status']==2)
	{
		return;
	}
	if($data)
	{
		$oi = $data['order_deal_id'];
		$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$data['order_deal_id']);
		$data['name'] = $order_item['name'];
		$order_id = $data['order_id'];
		$supplier_id = $data['supplier_id'];
	}
	
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set refund_status = 3 where id = ".$coupon_id);
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 3 where id = ".$data['order_deal_id']);
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set  refund_status = 3  where id = ".$order_id);
	
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	
	
	order_log($data['name']."退款不通过 ", $order_id);
	auto_over_status($order_id);
	update_order_cache($order_id);
	distribute_order($order_id);
	
	send_msg($order_info['user_id'], $data['name']."退款不通过 ", "orderitem", $oi);
}


/**
 * 订单分片,用户散列订单表，商户散列订单商品表
 * @param unknown_type $order_id
 */
function distribute_order($order_id)
{
	if($GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']>1)
	{
		//定义建表sql
		$rs = $GLOBALS['db']->getRow("show create table ".DB_PREFIX."deal_order");
		$order_sql = $rs['Create Table'];
		$order_sql = preg_replace("/create table/i", "create table if not exists ", $order_sql);
		$rs_item = $GLOBALS['db']->getRow("show create table ".DB_PREFIX."deal_order_item");
		$order_item_sql = $rs_item['Create Table'];
		$order_item_sql = preg_replace("/create table/i", "create table if not exists ", $order_item_sql);
		
		//散列订单
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		$order_end = hash_table($order_info['user_id'], $GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']); //通过订单用户ID的散列后缀
		$order_table_name = DB_PREFIX."deal_order_u_".$order_end; //散列订单表	
		$sql = preg_replace("/".DB_PREFIX."deal_order/", $order_table_name, $order_sql);	
		$GLOBALS['db']->query($sql); //创建散列表
		$GLOBALS['db']->query("delete from ".$order_table_name." where id = ".$order_id);
		$GLOBALS['db']->autoExecute($order_table_name,$order_info);
		
		//开始散列订单商品
		$order_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
		foreach($order_items as $k=>$item)
		{
			$order_end = hash_table($item['supplier_id'], $GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']); //通过订单用户ID的散列后缀
			$order_table_name = DB_PREFIX."deal_order_s_".$order_end; //散列订单表
			$sql = preg_replace("/".DB_PREFIX."deal_order/", $order_table_name, $order_sql);
			$GLOBALS['db']->query($sql); //创建散列表
			$GLOBALS['db']->query("delete from ".$order_table_name." where id = ".$order_id);
			$GLOBALS['db']->autoExecute($order_table_name,$order_info);			
			
			$order_item_end = hash_table($item['supplier_id'], $GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']); //通过订单商品商家ID的散列后缀
			$order_item_table_name = DB_PREFIX."deal_order_item_s_".$order_item_end; //散列订单商品表
			$sql = preg_replace("/".DB_PREFIX."deal_order_item/", $order_item_table_name, $order_item_sql);	
			$GLOBALS['db']->query($sql); //创建散列表
			$GLOBALS['db']->query("delete from ".$order_item_table_name." where id = ".$item['id']);
			$GLOBALS['db']->autoExecute($order_item_table_name,$item);
			
			$order_item_end = hash_table($order_info['user_id'], $GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']); //通过订单商品用户ID的散列后缀
			$order_item_table_name = DB_PREFIX."deal_order_item_u_".$order_item_end; //散列订单商品表
			$sql = preg_replace("/".DB_PREFIX."deal_order_item/", $order_item_table_name, $order_item_sql);
			$GLOBALS['db']->query($sql); //创建散列表
			$GLOBALS['db']->query("delete from ".$order_item_table_name." where id = ".$item['id']);
			$GLOBALS['db']->autoExecute($order_item_table_name,$item);
		}
	}	
}

/**
 * 为会员获取指定的散列订单表名
 * @param unknown_type $user_id
 */
function get_user_order_table_name($user_id)
{
	if($GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']>1)
	{
		$order_end = hash_table($user_id, $GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']); //通过订单用户ID的散列后缀
		$order_table_name = DB_PREFIX."deal_order_u_".$order_end; //散列订单表
		return $order_table_name;
	}
	else
	{
		return DB_PREFIX."deal_order";
	}
}

/**
 * 为会员获取指定的散列订单表名
 * @param unknown_type $user_id
 */
function get_supplier_order_table_name($supplier_id)
{
	if($GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']>1)
	{
		$order_end = hash_table($supplier_id, $GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']); //通过订单用户ID的散列后缀
		$order_table_name = DB_PREFIX."deal_order_s_".$order_end; //散列订单表
		return $order_table_name;
	}
	else
	{
		return DB_PREFIX."deal_order";
	}
}

/**
 * 为商户获取指定的散列订单商品表名
 * @param unknown_type $supplier_id
 */
function get_supplier_order_item_table_name($supplier_id)
{
	if($GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']>1)
	{
		$order_end = hash_table($supplier_id, $GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']); 
		$order_table_name = DB_PREFIX."deal_order_item_s_".$order_end; //散列订单表
		return $order_table_name;
	}
	else
	{
		return DB_PREFIX."deal_order_item";
	}
}

/**
 * 为用户获取指定的散列订单商品表名
 * @param unknown_type $user_id
 */
function get_user_order_item_table_name($user_id)
{
	if($GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']>1)
	{
		$order_end = hash_table($user_id, $GLOBALS['distribution_cfg']['ORDER_DISTRIBUTE_COUNT']);
		$order_table_name = DB_PREFIX."deal_order_item_u_".$order_end; //散列订单表
		return $order_table_name;
	}
	else
	{
		return DB_PREFIX."deal_order_item";
	}
}

?>