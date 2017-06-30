<?php 
return array(
			"OrderManager"	=>	array(
					"name"	=>	"订单中心",
					"node"	=>	array(
							"uc_order"=>array("name"=>"我的订单","module"=>"uc_order","action"=>"index"),
							"uc_coupon"=>array("name"=>"我的团购券","module"=>"uc_coupon","action"=>"index"),
							"uc_youhui"=>array("name"=>"我的优惠券","module"=>"uc_youhui","action"=>"index"),
							"uc_event"=>array("name"=>"我的活动报名","module"=>"uc_event","action"=>"index"),
							"uc_lottery"=>array("name"=>"我的抽奖号","module"=>"uc_lottery","action"=>"index"),
					)
			),
			"Bills"	=>	array(
					"name"	=>	"账户中心",
					"node"	=>	array(
							"uc_account"=>array("name"=>"账户设置","module"=>"uc_account","action"=>"index"),
							"uc_log"=>array("name"=>"我的资产","module"=>"uc_log","action"=>"index"),  //关于账户的余额，积分，经验，uc的兑换日志(index:余额，score:积分,exp:经验,exchange:uc兑换)
							"uc_money"=>array("name"=>"充值与提现","module"=>"uc_money","action"=>"index"),
							"uc_consignee"=>array("name"=>"配送地址","module"=>"uc_consignee","action"=>"index"),		
							"uc_voucher"=>array("name"=>"我的红包","module"=>"uc_voucher","action"=>"index"),
					)
			),
			"Service"	=>	array(
					"name"	=>	"会员服务",
					"node"	=>	array(
							"uc_collect"=>array("name"=>"我的收藏","module"=>"uc_collect","action"=>"index"),  //优惠券，产品，活动收藏
							"uc_review"=>array("name"=>"我的点评","module"=>"uc_review","action"=>"index"),  //查看以往点评（产品，优惠券，活动，商家）
							"uc_invite"=>array("name"=>"我的邀请","module"=>"uc_invite","action"=>"index"),							
							"uc_msg"=>array("name"=>"会员消息","module"=>"uc_msg","action"=>"index"),
							"uc_medal"=>array("name"=>"会员勋章","module"=>"uc_medal","action"=>"index"),
					)
			),
		
		);
				
?>