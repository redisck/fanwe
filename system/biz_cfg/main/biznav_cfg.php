<?php 
return array(
			"Verify"	=>	array(
					"name"	=>	"验证管理",
					"node"	=>	array(
							"dealv"=>array("name"=>"验证团购券","module"=>"dealv","action"=>"index"),
							"youhuiv"=>array("name"=>"验证优惠券","module"=>"youhuiv","action"=>"index"),
							"eventv"=>array("name"=>"验证活动报名","module"=>"eventv","action"=>"index"),
					)
			),
			"Order"	=>	array(
					"name"	=>	"订单管理",
					"node"	=>	array(
							"dealo"=>array("name"=>"团购订单列表","module"=>"dealo","action"=>"index"),
							"goodso"=>array("name"=>"商品订单列表","module"=>"goodso","action"=>"index"),
							"youhuio"=>array("name"=>"优惠券下载记录","module"=>"youhuio","action"=>"index"),
							"evento"=>array("name"=>"活动报名","module"=>"evento","action"=>"index"),
					)
			),
			"Review"	=>	array(
					"name"	=>	"点评管理",
					"node"	=>	array(
							"dealr"=>array("name"=>"消费评价","module"=>"dealr","action"=>"index"),
							"youhuir"=>array("name"=>"优惠券点评","module"=>"youhuir","action"=>"index"),
							"eventr"=>array("name"=>"活动点评","module"=>"eventr","action"=>"index"),
							"storer"=>array("name"=>"门店点评","module"=>"storer","action"=>"index"),
					)
			),
			"Project"	=>	array(
					"name"	=>	"项目管理",
					"node"	=>	array(
							"deal"=>array("name"=>"团购","module"=>"deal","action"=>"index"),
							"goods"=>array("name"=>"商品","module"=>"goods","action"=>"index"),
							"youhui"=>array("name"=>"优惠券","module"=>"youhui","action"=>"index"),
							"event"=>array("name"=>"活动","module"=>"event","action"=>"index"),
					)
			),
			"Bills"	=>	array(
					"name"	=>	"财务管理",
					"node"	=>	array(
							"balance"=>array("name"=>"财务报表","module"=>"balance","action"=>"index"),
							"withdrawal"=>array("name"=>"商户提现","module"=>"withdrawal","action"=>"index"),
							"bankinfo"=>array("name"=>"银行账户","module"=>"bankinfo","action"=>"index"),
					)
			),
			"Location"	=>	array(
					"name"	=>	"门店管理",
					"node"	=>	array(
							"location"=>array("name"=>"门店列表","module"=>"location","action"=>"index"),
					)
			),
			"Account"	=>	array(
					"name"	=>	"账户管理",
					"node"	=>	array(
							"delivery"=>array("name"=>"配送方式","module"=>"delivery","action"=>"index"),
							"account"=>array("name"=>"子账户列表","module"=>"account","action"=>"index"),
							"setting"=>array("name"=>"微店设置","module"=>"setting","action"=>"index"),
							),
					)
		);
				
?>