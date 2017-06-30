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

		"app"=>array(
			"name"=>"IOS/Android",
			"mobile_type"=>0,
			"nav"=>array(
				"url" => array(
					"name"	=>	"自定义url",
					"type"	=>	"0",
					"fname"	=>	"url地址",
					"field"	=>	"url",
				),
				"tuan" => array(
					"name"	=>	"团购列表",
					"type"	=>	"11",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"goods" => array(
					"name"	=>	"商品列表",
					"type"	=>	"12",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"scores" => array(
						"name"	=>	"积分商品列表",
						"type"	=>	"13",
						"fname"	=>	"分类ID",
						"field"	=>	"cate_id",
				),
				"events" => array(
					"name"	=>	"活动列表",
					"type"	=>	"14",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"youhuis" => array(
					"name"	=>	"优惠券列表",
					"type"	=>	"15",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"stores" => array(
					"name"	=>	"门店列表",
					"type"	=>	"16",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"notices" => array(
						"name"	=>	"公告列表",
						"type"	=>	"17",
						"fname"	=>	"",
						"field"	=>	"",
				),
				"deal" => array(
					"name"	=>	"购物明细",
					"type"	=>	"21",
					"fname"	=>	"数据ID",
					"field"	=>	"data_id",
				),
				"event" => array(
						"name"	=>	"活动明细",
						"type"	=>	"24",
						"fname"	=>	"数据ID",
						"field"	=>	"data_id",
				),
				"youhui" => array(
					"name"	=>	"优惠明细",
					"type"	=>	"25",
					"fname"	=>	"数据ID",
					"field"	=>	"data_id",
				),				
				"store" => array(
					"name"	=>	"门店明细",
					"type"	=>	"26",
					"fname"	=>	"数据ID",
					"field"	=>	"data_id",
				),
				"notice" => array(
						"name"	=>	"公告详细",
						"type"	=>	"27",
						"fname"	=>	"数据ID",
						"field"	=>	"data_id",
				),
				"scan"	=>	array(
						"name"	=>	"扫一扫",
						"type"	=>	"31",
						"fname"	=>	"",
						"field"	=>	"",
				),
			    "discover" => array(
			        "name"	=>	"发现",
			        "type"	=>	"61",
			        "fname"	=>	"",
			        "field"	=>	"",
			    )
				
			)		
		),
		"wap"=>array(
			"name"=>"Wap端",
			"mobile_type"=>1,
			"nav"=>array(
				"url" => array(
					"name"	=>	"自定义url",
					"type"	=>	"0",
					"fname"	=>	"url地址",
					"field"	=>	"url",
				),
				"tuan" => array(
					"name"	=>	"团购列表",
					"type"	=>	"11",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"goods" => array(
					"name"	=>	"商品列表",
					"type"	=>	"12",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"scores" => array(
						"name"	=>	"积分商品列表",
						"type"	=>	"13",
						"fname"	=>	"分类ID",
						"field"	=>	"cate_id",
				),
				"events" => array(
					"name"	=>	"活动列表",
					"type"	=>	"14",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"youhuis" => array(
					"name"	=>	"优惠券列表",
					"type"	=>	"15",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"stores" => array(
					"name"	=>	"门店列表",
					"type"	=>	"16",
					"fname"	=>	"分类ID",
					"field"	=>	"cate_id",
				),
				"notices" => array(
						"name"	=>	"公告列表",
						"type"	=>	"17",
						"fname"	=>	"",
						"field"	=>	"",
				),
				"deal" => array(
					"name"	=>	"购物明细",
					"type"	=>	"21",
					"fname"	=>	"数据ID",
					"field"	=>	"data_id",
				),
				"event" => array(
						"name"	=>	"活动明细",
						"type"	=>	"24",
						"fname"	=>	"数据ID",
						"field"	=>	"data_id",
				),
				"youhui" => array(
					"name"	=>	"优惠明细",
					"type"	=>	"25",
					"fname"	=>	"数据ID",
					"field"	=>	"data_id",
				),				
				"store" => array(
					"name"	=>	"门店明细",
					"type"	=>	"26",
					"fname"	=>	"数据ID",
					"field"	=>	"data_id",
				),
				"notice" => array(
						"name"	=>	"公告详细",
						"type"	=>	"27",
						"fname"	=>	"数据ID",
						"field"	=>	"data_id",
				),
			    "discover" => array(
			        "name"	=>	"发现",
			        "type"	=>	"61",
			        "fname"	=>	"",
			        "field"	=>	"",
			    ),
			
			)
		),
    
);
?>