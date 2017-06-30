<?php
// +----------------------------------------------------------------------
// | Fanwe 方维系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// 前端可配置的导航菜单
// +----------------------------------------------------------------------

return array(

			"wap"=>array(
						"name"=>"Wap端",
						"mobile_type"=>1,
						"nav"=>array(
								"dc" => array(
									"name"	=>	"外卖列表",
									"type"	=>	"100",
									"fname"	=>	"",
									"field"	=>	"",
								),
								"dcres" => array(
									"name"	=>	"预订列表",
									"type"	=>	"101",
									"fname"	=>	"",
									"field"	=>	"",
								),	
						)
							
					),
			"app"=>array(
					"name"=>"IOS/Android",
					"mobile_type"=>0,
					"nav"=>array(
									"dc" => array(
										"name"	=>	"外卖列表",
										"type"	=>	"100",
										"fname"	=>	"",
										"field"	=>	"",
									),
									"dcres" => array(
										"name"	=>	"预订列表",
										"type"	=>	"101",
										"fname"	=>	"",
										"field"	=>	"",
									),	
							)
								
						),

			

    
);
?>