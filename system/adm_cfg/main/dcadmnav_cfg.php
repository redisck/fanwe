<?php 
return array( 
	"dc"	=>	array(
				"name"	=>	"外卖预订",
				"key"	=>	"dc",
				"groups"	=>	array(
						"dcorder"	=>	array(
								"name"	=>	"订单管理",
								"key"	=>	"dcorder",
								"nodes"	=>	array(
										array("name"=>"外卖订单","module"=>"DcOrder","action"=>"index"),
										array("name"=>"历史外卖","module"=>"DcOrderHistory","action"=>"index"),
										array("name"=>"预订订单","module"=>"DcResOrder","action"=>"index"),
										array("name"=>"历史预订","module"=>"DcResOrderHistory","action"=>"index"),

								),
						),
						"dccat"	=>	array(
								"name"	=>	"分类管理",
								"key"	=>	"dccat",
								"nodes"	=>	array(
										array("name"=>"店铺分类","module"=>"DcCate","action"=>"index"),
										array("name"=>"宝贝类目","module"=>"DcMenuCate","action"=>"index"),
								),
						),
						"dcpromote"	=>	array(
								"name"	=>	"促销管理",
								"key"	=>	"dcpromote",
								"nodes"	=>	array(
										array("name"=>"促销列表","module"=>"DcPromote","action"=>"index"),										
								),
						),	
						"dcbalance"	=>	array(
						"name"	=>	"报表",
						"key"	=>	"dcbalance",
						"nodes"	=>	array(
								array("name"=>"销售报表","module"=>"DcBalance","action"=>"index"),
								array("name"=>"结算报表","module"=>"DcBalance","action"=>"bill"),
						),
			),						
				),
		),
);
?>