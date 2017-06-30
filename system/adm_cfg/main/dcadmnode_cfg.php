<?php 
return array( 
	"DcCate"	=>	array(
		"name"	=>	"商家分类", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"商家分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"set_effect"	=>	array("name"=>"设置有效性","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),

		)
	),
	"DcMenuCate"	=>	array(
		"name"	=>	"外卖预订标签", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"外卖预订标签列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"set_effect"	=>	array("name"=>"设置有效性","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),

		)
	),
	"DcSupplierMenuCate"	=>	array(
		"name"	=>	"商家自定义宝贝分类", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"商家自定义宝贝分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"set_effect"	=>	array("name"=>"设置有效性","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),

		)
	),
	"DcMenu"	=>	array(
		"name"	=>	"外卖宝贝", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"外卖宝贝列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"set_effect"	=>	array("name"=>"设置有效性","action"=>"set_effect"),


		)
	),
	"DcRsItem"	=>	array(
		"name"	=>	"预订设置", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"预订项目列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
			"table_set_effect"	=>	array("name"=>"设置有效性","action"=>"table_set_effect"),

		)
	),
	"DcRsItemTime"	=>	array(
		"name"	=>	"预订时间设置", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"预订时间列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
			"time_set_effect"	=>	array("name"=>"设置有效性","action"=>"time_set_effect"),

		)
	),
	"DcBalance"	=>	array(
		"name"	=>	"外卖报表", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"销售报表","action"=>"index"),
			"bill"	=>	array("name"=>"结算报表","action"=>"bill"),
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
		)
	),
	
);
?>