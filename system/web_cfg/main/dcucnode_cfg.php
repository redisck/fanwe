<?php 
return array(
			"OrderManager"	=>	array(
					"name"	=>	"订单中心",
					"node"	=>	array(
							"dc_dcorder"=>array("name"=>"外卖订单","module"=>"dc_dcorder","action"=>"index"),
							"dc_rsorder"=>array("name"=>"预订订单","module"=>"dc_rsorder","action"=>"index"),
					)
			),
			"Bills"	=>	array(
					"name"	=>	"账户中心",
					"node"	=>	array(
							"dc_consignee"=>array("name"=>"送货地址","module"=>"dc_consignee","action"=>"index"),	
					)
			),		
		);
				
?>