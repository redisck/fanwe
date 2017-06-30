<?php 
return array(
		"Supplier"	=>	array(
				"name"	=>	"供应商",
				"node"	=>	array(
						"unbind"	=>	array("name"=>"解绑公众号","action"=>"unbind"),
				)
		),
		"WeixinConf"	=>	array(
				"name"	=>	"微信设置",
				"node"	=>	array(
						"index"	=>	array("name"=>"查看设置","action"=>"index"),
						"update"	=>	array("name"=>"更新设置","action"=>"update"),
				)
		),
		"WeixinInfo"	=>	array(
				"name"	=>	"平台公众号",
				"node"	=>	array(
						"index"	=>	array("name"=>"查看设置","action"=>"index"),
						"unbind"	=>	array("name"=>"解除绑定","action"=>"unbind"),
						"nav_save"	=>	array("name"=>"更新菜单设置","action"=>"nav_save"),
						"syn_to_weixin"	=>	array("name"=>"同步微信菜单","action"=>"syn_to_weixin"),
						"syn_industry"	=>	array("name"=>"同步微信行业","action"=>"syn_industry"),
						"syn_template"	=>	array("name"=>"同步微信消息模板","action"=>"syn_template"),
						"del_template"	=>	array("name"=>"删除微信消息模板","action"=>"del_template"),
				)
		),
		"WeixinReply"	=>	array(
				"name"	=>	"平台公众号回复设置",
				"node"	=>	array(
						"save_dtext"	=>	array("name"=>"设置默认文本回复","action"=>"save_dtext"),
						"save_dnews"	=>	array("name"=>"设置默认图文回复","action"=>"save_dnews"),
						"save_onfocus"	=>	array("name"=>"设置关注文本回复","action"=>"save_onfocus"),
						"save_onfocusn"	=>	array("name"=>"设置关注图文回复","action"=>"save_onfocusn"),
						"save_text"	=>	array("name"=>"设置文本回复","action"=>"save_text"),
						"save_news"	=>	array("name"=>"设置图文回复","action"=>"save_news"),
						"save_lbs"	=>	array("name"=>"设置lbs回复","action"=>"save_lbs"),
						"foreverdelete"	=>	array("name"=>"删除回复","action"=>"foreverdelete"),
				)
		),
		
		
);
?>