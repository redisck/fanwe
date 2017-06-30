<?php 
return array(
			"DcOrder"	=>	array(
					"name"	=>	"外卖预订管理",
			        "iconfont"=>"&#xe60a;",
					"node"	=>	array(
							"dcorder"=>array("name"=>"外卖订单","module"=>"dcorder","action"=>"index"),
							"dcresorder"=>array("name"=>"预订订单","module"=>"dcresorder","action"=>"index"),
							"dcborder"=>array("name"=>"对账单","module"=>"dcborder","action"=>"index"),
							"dcreminder"=>array("name"=>"催单记录","module"=>"dcreminder","action"=>"index"),
							"dcverify"=>array("name"=>"预订电子券验证","module"=>"dcverify","action"=>"index"),
					)
			),
			"Location"	=>	array(
					"name"	=>	"门店管理",
					"node"	=>	array(
							"dc"=>array("name"=>"外卖预订设置","module"=>"dc","action"=>"index"),
					)
			)
		);
				
?>