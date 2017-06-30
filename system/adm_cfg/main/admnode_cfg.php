<?php 
return array( 
	"Admin"	=>	array(
		"name"	=>	"管理员", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"管理员列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
			"set_default"	=>	array("name"=>"设置默认管理员","action"=>"set_default"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),			
		)
	),
	"Adv"	=>	array(
		"name"	=>	"广告模块", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"广告列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
		)
	),
	"ApiLogin"	=>	array(
		"name"	=>	"API登录", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"API插件列表","action"=>"index"),
			"insert"	=>	array("name"=>"API插件安装","action"=>"insert"),
			"update"	=>	array("name"=>"API插件编辑","action"=>"update"),
			"uninstall"	=>	array("name"=>"API插件卸载","action"=>"uninstall"),
		)
	),
	"Area"	=>	array(
		"name"	=>	"商圈地区", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"商圈列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
		)
	),
	"Article"	=>	array(
		"name"	=>	"文章模块", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"文章列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),			
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
		)
	),
	"ArticleCate"	=>	array(
		"name"	=>	"文章分类", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"文章分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),			
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),

			
		)
	),
	"Balance"	=>	array(
		"name"	=>	"报表", 
		"node"	=>	array( 
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
			"index"	=>	array("name"=>"运营报表","action"=>"index"),
			"bill"	=>	array("name"=>"结算报表","action"=>"bill"),
		)
	),
	"Brand"	=>	array(
		"name"	=>	"品牌管理", 
		"node"	=>	array(
			"index"	=>	array("name"=>"品牌列表","action"=>"index"),
			"insert"	=>	array("name"=>"品牌添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"品牌编辑提交","action"=>"update"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"foreverdelete"	=>	array("name"=>"删除品牌","action"=>"foreverdelete"),
		)
	),
	"Cache"	=>	array(
		"name"	=>	"缓存处理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"缓存处理","action"=>"index"),
			"clear_data"	=>	array("name"=>"清空程序缓存","action"=>"clear_data"),
			"clear_image"	=>	array("name"=>"重置缩略图","action"=>"clear_image"),
			"syn_data"	=>	array("name"=>"同步数据","action"=>"syn_data"),
		)
	),
	"Conf"	=>	array(
		"name"	=>	"系统配置", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"系统配置","action"=>"index"),
			"update"	=>	array("name"=>"更新配置","action"=>"update"),
			"mobile"	=>	array("name"=>"手机端配置","action"=>"mobile"),
			"savemobile"	=>	array("name"=>"保存手机端配置","action"=>"savemobile"),
			"news"	=>	array("name"=>"手机端公告","action"=>"news"),
			"insertnews"	=>	array("name"=>"添加手机端公告","action"=>"insertnews"),
			"updatenews"	=>	array("name"=>"编辑手机端公告","action"=>"updatenews"),
			"foreverdelete"	=>	array("name"=>"删除公告","action"=>"foreverdelete"),			
		)
	),
	"DarenSubmit"	=>	array(
		"name"	=>	"达人申请", 
		"node"	=>	array(			
			"index"	=>	array("name"=>"达人申请列表","action"=>"index"),
			"update"	=>	array("name"=>"审核通过","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"Database"	=>	array(
		"name"	=>	"数据库", 
		"node"	=>	array( 
			"delete"	=>	array("name"=>"删除备份","action"=>"delete"),
			"dump"	=>	array("name"=>"备份数据","action"=>"dump"),
			"execute"	=>	array("name"=>"执行SQL语句","action"=>"execute"),
			"index"	=>	array("name"=>"数据库备份列表","action"=>"index"),
			"restore"	=>	array("name"=>"恢复备份","action"=>"restore"),
			"sql"	=>	array("name"=>"SQL操作","action"=>"sql"),
		)
	),
	"Deal"	=>	array(
		"name"	=>	"商品管理", 
		"node"	=>	array(	
			"index"	=>	array("name"=>"团购列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加团购","action"=>"insert"),
			"update"	=>	array("name"=>"编辑团购","action"=>"update"),
			"shop"	=>	array("name"=>"商品列表","action"=>"shop"),
			"shop_insert"	=>	array("name"=>"商品添加","action"=>"shop_insert"),
			"shop_update"	=>	array("name"=>"商品编辑","action"=>"shop_update"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),		
			"trash"	=>	array("name"=>"回收站","action"=>"trash"),
			"restore"	=>	array("name"=>"恢复","action"=>"restore"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),				
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),	
			"tuan_publish"	=>	array("name"=>"团购申请","action"=>"tuan_publish"),
			"shop_publish"	=>	array("name"=>"商品申请","action"=>"shop_publish"),
			"downline"	=>	array("name"=>"下架申请","action"=>"downline"),
			"biz_submit_del"	=>	array("name"=>"删除申请","action"=>"biz_submit_del"),
			"refused_apply"	=>	array("name"=>"拒绝申请","action"=>"refused_apply"),
		)
	),
	"DealCate"	=>	array(
		"name"	=>	"生活服务分类", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),			
		)
	),
	"DealCateType"	=>	array(
		"name"	=>	"生活服务子类", 
		"node"	=>	array( 					
			"index"	=>	array("name"=>"子分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"修改","action"=>"update"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
		)
	),
	"DealCity"	=>	array(
		"name"	=>	"城市管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"城市列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),			
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
			"set_default"	=>	array("name"=>"设为默认城市","action"=>"set_default"),		
		)
	),
	"DealCoupon"	=>	array(
		"name"	=>	"团购券", 
		"node"	=>	array( 
			"add"	=>	array("name"=>"添加页面","action"=>"add"),
			"edit"	=>	array("name"=>"编辑页面","action"=>"edit"),
			"export_csv"	=>	array("name"=>"导出csv","action"=>"export_csv"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"import"	=>	array("name"=>"导入页面","action"=>"import"),
			"importInsert"	=>	array("name"=>"导入执行","action"=>"importInsert"),
			"index"	=>	array("name"=>"团购券列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"mail"	=>	array("name"=>"邮件通知","action"=>"mail"),
			"sample"	=>	array("name"=>"下载样例","action"=>"sample"),
			"sms"	=>	array("name"=>"短信通知","action"=>"sms"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
		)
	),
	"DealMsgList"	=>	array(
		"name"	=>	"业务群发队列", 
		"node"	=>	array( 
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"index"	=>	array("name"=>"业务队列列表","action"=>"index"),
			"send"	=>	array("name"=>"手动发送","action"=>"send"),
			"show_content"	=>	array("name"=>"显示内容","action"=>"show_content"),
		)
	),
	"DealOrder"	=>	array(
		"name"	=>	"订单模块", 
		"node"	=>	array( 
			"deal_index"	=>	array("name"=>"订单列表","action"=>"deal_index"),
			"deal_trash"	=>	array("name"=>"历史订单","action"=>"deal_trash"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"del_lottery"	=>	array("name"=>"删除抽奖号","action"=>"del_lottery"),
			"delivery"	=>	array("name"=>"订单发货","action"=>"delivery"),			
			"do_batch_delivery"	=>	array("name"=>"批量发货","action"=>"do_batch_delivery"),
			"do_delivery"	=>	array("name"=>"执行发货","action"=>"do_delivery"),
			"do_incharge"	=>	array("name"=>"执行收款","action"=>"do_incharge"),
			"export_csv"	=>	array("name"=>"导出csv","action"=>"export_csv"),
			"incharge_index"	=>	array("name"=>"充值订单列表","action"=>"incharge_index"),
			"incharge_trash"	=>	array("name"=>"历史充值订单","action"=>"incharge_trash"),
			"load_batch_delivery"	=>	array("name"=>"批量发货","action"=>"load_batch_delivery"),
			"lottery_index"	=>	array("name"=>"抽奖号列表","action"=>"lottery_index"),
			"order_incharge"	=>	array("name"=>"订单收款","action"=>"order_incharge"),
			"pay_incharge"	=>	array("name"=>"充值单管理员收款","action"=>"pay_incharge"),
			"referer"	=>	array("name"=>"订单来路统计","action"=>"referer"),
			"view_order"	=>	array("name"=>"查看订单","action"=>"view_order"),
			"view_order_history"	=>	array("name"=>"查看历史订单","action"=>"view_order_history"),
			"do_refund"	=>	array("name"=>"同步退款","action"=>"do_refund"),
			"do_refuse"	=>	array("name"=>"拒绝退款","action"=>"do_refuse"),
			"do_verify"	=>	array("name"=>"人工验证，收货","action"=>"do_verify"),
			"cancel"	=>	array("name"=>"关闭交易","action"=>"cancel"),
		)
	),
	"Delivery"	=>	array(
		"name"	=>	"配送方式", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"配送方式列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),			
		)
	),
	"DeliveryRegion"	=>	array(
		"name"	=>	"配送地区", 
		"node"	=>	array(			
			"index"	=>	array("name"=>"配送地区列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"Ecv"	=>	array(
		"name"	=>	"红包", 
		"node"	=>	array( 
			"export_csv"	=>	array("name"=>"导出csv","action"=>"export_csv"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"index"	=>	array("name"=>"红包列表","action"=>"index"),
		)
	),
	"EcvType"	=>	array(
		"name"	=>	"红包类型", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"红包类型列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),			
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"send"	=>	array("name"=>"发放页面","action"=>"send"),
			"doSend"	=>	array("name"=>"发放执行","action"=>"doSend"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"Event"	=>	array(
		"name"	=>	"活动管理", 
		"node"	=>	array( 				
			"index"	=>	array("name"=>"活动列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),			
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"publish"	=>	array("name"=>"商家提交","action"=>"publish"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"downline"	=>	array("name"=>"下架申请","action"=>"downline"),
			"biz_submit_del"	=>	array("name"=>"删除申请","action"=>"biz_submit_del"), 
			"refused_apply"	=>	array("name"=>"拒绝申请","action"=>"refused_apply"),
		)
	),
	"EventCate"	=>	array(
		"name"	=>	"活动分类", 
		"node"	=>	array(				
			"index"	=>	array("name"=>"分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"EventSubmit"	=>	array(
		"name"	=>	"活动报名管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"活动报名列表","action"=>"index"),
			"set_verify"	=>	array("name"=>"活动报名审核","action"=>"set_verify"),
			"refuse"	=>	array("name"=>"报名审核拒绝","action"=>"refuse"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
		)
	),
	"Express"	=>	array(
		"name"	=>	"快递接口", 
		"node"	=>	array(			
			"index"	=>	array("name"=>"快递接口列表","action"=>"index"),
			"insert"	=>	array("name"=>"安装","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"eprint"	=>	array("name"=>"打印","action"=>"eprint"),
			"uninstall"	=>	array("name"=>"卸载","action"=>"uninstall"),
			
		)
	),
	"File"	=>	array(
		"name"	=>	"文件管理", 
		"node"	=>	array( 
			"deleteImg"	=>	array("name"=>"删除图片","action"=>"deleteImg"),
			"do_upload"	=>	array("name"=>"编辑器图片上传","action"=>"do_upload"),
			"do_upload_img"	=>	array("name"=>"图片控件上传","action"=>"do_upload_img"),
			"do_upload_icon"	=>	array("name"=>"上传iconfont","action"=>"do_upload_icon"),
		)
	),
	"Filter"	=>	array(
		"name"	=>	"筛选关键词", 
		"node"	=>	array( 
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"index"	=>	array("name"=>"关键词列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
		)
	),
	"FilterGroup"	=>	array(
		"name"	=>	"筛选分组", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"筛选分组","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
		)
	),
	"GoodsType"	=>	array(
		"name"	=>	"商品类型", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"商品类型列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"GoodsTypeAttr"	=>	array(
		"name"	=>	"商品类型属性", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"商品属性列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"Index"	=>	array(
			"name"	=>	"首页统计",
			"node"	=>	array(
					"main"	=>	array("name"=>"首页统计","action"=>"main"),
			)
	),
	"Integrate"	=>	array(
		"name"	=>	"会员整合", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"会员整合插件","action"=>"index"),
			"install"	=>	array("name"=>"安装页面","action"=>"install"),
			"save"	=>	array("name"=>"保存","action"=>"save"),
			"uninstall"	=>	array("name"=>"卸载","action"=>"uninstall"),
		)
	),
	"Link"	=>	array(
		"name"	=>	"友情链接", 
		"node"	=>	array(				
			"index"	=>	array("name"=>"友情链接列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),						
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"set_effect"	=>	array("name"=>"设置有效性","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),
		)
	),
	"LinkGroup"	=>	array(
		"name"	=>	"友情链接分组", 
		"node"	=>	array( 		
			"index"	=>	array("name"=>"友情链接分组列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
			"set_effect"	=>	array("name"=>"设置有效性","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),
			
		)
	),
	"Log"	=>	array(
		"name"	=>	"系统日志", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"系统日志列表","action"=>"index"),
			"coupon"	=>	array("name"=>"第三方验证日志","action"=>"coupon"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"foreverdeletelog"	=>	array("name"=>"永久删除第三方验证日志","action"=>"foreverdeletelog"),
			
		)
	),
	"MAdv"	=>	array(
		"name"	=>	"手机端广告", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"手机端广告列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"删除广告","action"=>"foreverdelete"),
		)
	),

	"MZt"	=>	array(
			"name"	=>	"手机专题模块",
			"node"	=>	array(
					"index"	=>	array("name"=>"专题列表","action"=>"index"),
					"insert"	=>	array("name"=>"添加","action"=>"insert"),
					"update"	=>	array("name"=>"编辑","action"=>"update"),
					"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
					"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			)
	),
	"MNotice"	=>	array(
		"name"	=>	"手机端公告",
		"node"	=>	array(
				"index"	=>	array("name"=>"手机端公告列表","action"=>"index"),
				"insert"	=>	array("name"=>"添加","action"=>"insert"),
				"update"	=>	array("name"=>"编辑","action"=>"update"),				
				"set_effect"	=>	array("name"=>"设置有效","action"=>"set_effect"),
				"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),
				"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
		)
	),
	"MailList"	=>	array(
		"name"	=>	"邮件订阅", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"邮件订阅列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"do_import_csv"	=>	array("name"=>"邮件导入","action"=>"do_import_csv"),
			"export_csv"	=>	array("name"=>"邮件导出","action"=>"export_csv"),			
			"import_csv"	=>	array("name"=>"邮件导入页","action"=>"import_csv"),			
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
		)
	),
	"MailServer"	=>	array(
		"name"	=>	"邮件服务器", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"邮件服务器列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
			"send_demo"	=>	array("name"=>"发送测试邮件","action"=>"send_demo"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			
		)
	),	
	"Medal"	=>	array(
		"name"	=>	"勋章系统", 
		"node"	=>	array(			
			"index"	=>	array("name"=>"勋章列表","action"=>"index"),
			"insert"	=>	array("name"=>"安装提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"uninstall"	=>	array("name"=>"卸载勋章","action"=>"uninstall"),
		)
	),

	"MIndex"	=>	array(
		"name"	=>	"手机端首页菜单", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"首页菜单列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"删除菜单","action"=>"foreverdelete"),
		)
	),
	"MobileList"	=>	array(
		"name"	=>	"短信订阅列表", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"短信订阅列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"do_import_csv"	=>	array("name"=>"手机号码导入","action"=>"do_import_csv"),
			"export_csv"	=>	array("name"=>"手机号码导出","action"=>"export_csv"),
			"import_csv"	=>	array("name"=>"手机号码导入页","action"=>"import_csv"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
		)
	),
	"MsgBox"	=>	array(
		"name"	=>	"消息记录", 
		"node"	=>	array(
			"index"	=>	array("name"=>"记录列表","action"=>"index"),
			"view"	=>	array("name"=>"查看记录","action"=>"view"), 
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"MsgSystem"	=>	array(
		"name"	=>	"站内消息群发", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"消息列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"MsgTemplate"	=>	array(
		"name"	=>	"消息模板", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"消息模板管理","action"=>"index"),
			"update"	=>	array("name"=>"保存","action"=>"update"),
		)
	),
	"Nav"	=>	array(
		"name"	=>	"导航菜单", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"导航菜单列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
		)
	),
	"Payment"	=>	array(
		"name"	=>	"支付方式", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"支付接口列表","action"=>"index"),
			"insert"	=>	array("name"=>"安装保存","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"uninstall"	=>	array("name"=>"卸载","action"=>"uninstall"),
		)
	),
	"PaymentNotice"	=>	array(
		"name"	=>	"收款单", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"收款单列表","action"=>"index"),
		)
	),
	"PointGroup"	=>	array(
		"name"	=>	"点评评分分组管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"分组列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),	
	"Promote"	=>	array(
		"name"	=>	"促销接口", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"促销接口列表","action"=>"index"),
			"insert"	=>	array("name"=>"安装","action"=>"insert"),
			"update"	=>	array("name"=>"更新","action"=>"update"),
			"uninstall"	=>	array("name"=>"卸载","action"=>"uninstall"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
		)
	),
	"PromoteMsg"	=>	array(
		"name"	=>	"推广邮件短信", 
		"node"	=>	array( 
			"add_mail"	=>	array("name"=>"添加邮件页面","action"=>"add_mail"),
			"add_sms"	=>	array("name"=>"添加短信页面","action"=>"add_sms"),
			"edit_mail"	=>	array("name"=>"编辑邮件页面","action"=>"edit_mail"),
			"edit_sms"	=>	array("name"=>"编辑短信页面","action"=>"edit_sms"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
			"import_mail"	=>	array("name"=>"edm邮件导入","action"=>"import_mail"),
			"insert_mail"	=>	array("name"=>"添加邮件执行","action"=>"insert_mail"),
			"insert_sms"	=>	array("name"=>"添加短信执行","action"=>"insert_sms"),
			"mail_index"	=>	array("name"=>"邮件列表","action"=>"mail_index"),
			"sms_index"	=>	array("name"=>"短信列表","action"=>"sms_index"),
			"update_mail"	=>	array("name"=>"编辑邮件执行","action"=>"update_mail"),
			"update_sms"	=>	array("name"=>"编辑短信执行","action"=>"update_sms"),
		)
	),
	"PromoteMsgList"	=>	array(
		"name"	=>	"推广群发队列", 
		"node"	=>	array( 
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"index"	=>	array("name"=>"推广队列列表","action"=>"index"),
			"send"	=>	array("name"=>"手动发送","action"=>"send"),
			"show_content"	=>	array("name"=>"显示内容","action"=>"show_content"),
		)
	),
	"Referrals"	=>	array(
		"name"	=>	"邀请返利", 
		"node"	=>	array( 
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"index"	=>	array("name"=>"邀请返利列表","action"=>"index"),
			"pay"	=>	array("name"=>"发放返利","action"=>"pay"),
		)
	),
	"Role"	=>	array(
		"name"	=>	"角色管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"管理员分组列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"删除","action"=>"foreverdelete"),
		)
	),
	"ShopCate"	=>	array(
		"name"	=>	"商城分类", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"商城分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"delete"	=>	array("name"=>"删除分类","action"=>"delete"),
			"set_effect"	=>	array("name"=>"设置有效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),
		)
	),
	"Sms"	=>	array(
		"name"	=>	"短信接口", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"短信接口列表","action"=>"index"),
			"insert"	=>	array("name"=>"安装保存","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"uninstall"	=>	array("name"=>"卸载","action"=>"uninstall"),
			"send_demo"	=>	array("name"=>"发送测试短信","action"=>"send_demo"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
		)
	),
	"Statistic"	=>	array(
		"name"	=>	"报表", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"报表列表","action"=>"index"),
		)
	),
	"Supplier"	=>	array(
		"name"	=>	"供应商", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"商户列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"charge_index"	=>	array("name"=>"商户提现列表","action"=>"charge_index"),
			"docharge"	=>	array("name"=>"确认提现/打款","action"=>"docharge"),
			"dorefuse"	=>	array("name"=>"拒绝打款","action"=>"dorefuse"),
			"del_charge"	=>	array("name"=>"提现记录删除","action"=>"del_charge"),
		)
	),
	"SupplierBalance"	=>	array(
			"name"	=>	"商家报表",
			"node"	=>	array(
					"index"	=>	array("name"=>"列表","action"=>"index"),
					"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			)
	),
	"SupplierLocation"	=>	array(
		"name"	=>	"供应商分店与部门", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"供应商分店部门列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"set_sort"	=>	array("name"=>"门店排序","action"=>"set_sort"),
			"publish"	=>	array("name"=>"商户提交","action"=>"publish"),
			"downline"	=>	array("name"=>"下架提交","action"=>"downline"),
			"biz_submit_del"	=>	array("name"=>"删除申请","action"=>"biz_submit_del"),
			"refused_apply"	=>	array("name"=>"拒绝申请","action"=>"refused_apply"),
			"update_biz_submit_images"	=>	array("name"=>"更新图集修改","action"=>"update_biz_submit_images"),
		)
	),
	"SupplierLocationDp"	=>	array(
			"name"	=>	"点评管理",
			"node"	=>	array(
					"index"	=>	array("name"=>"点评列表","action"=>"index"),
					"update"	=>	array("name"=>"点评修改","action"=>"update"),
					"removePhoto"	=>	array("name"=>"删除点评图片","action"=>"removePhoto"),
					"foreverdelete"	=>	array("name"=>"永久删除点评","action"=>"foreverdelete"),
			)
	),
	"SupplierLocationImages"	=>	array(
			"name"	=>	"供应商分店图片",
			"node"	=>	array(
					"index"	=>	array("name"=>"列表","action"=>"index"),
					"insert"	=>	array("name"=>"添加图片","action"=>"insert"),
					"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
					"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			)
	),
	"SupplierSubmit"	=>	array(
		"name"	=>	"商家入驻", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"商家入驻申请","action"=>"index"),
			"update"	=>	array("name"=>"审核通过","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"TagGroup"	=>	array(
		"name"	=>	"点评标签分组管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"分组列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"Topic"	=>	array(
		"name"	=>	"用户主题", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"会员主题管理","action"=>"index"),
			"update"	=>	array("name"=>"更新主题","action"=>"update"),
			"toogle_status"	=>	array("name"=>"置顶","action"=>"toogle_status"),
			"delete"	=>	array("name"=>"删除主题","action"=>"delete"),
		)
	),
	"TopicGroup"	=>	array(
		"name"	=>	"分享小组", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"小组列表","action"=>"index"),
			"insert"	=>	array("name"=>"小组添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"小组编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"user_delete"	=>	array("name"=>"删除组员","action"=>"user_delete"),
			"user_index"	=>	array("name"=>"组员列表","action"=>"user_index"),
			"user_insert"	=>	array("name"=>"添加组员提交","action"=>"user_insert"),
			"user_update"	=>	array("name"=>"组员编辑提交","action"=>"user_update"),
			"apply_index"	=>	array("name"=>"小组申请查看","action"=>"apply_index"),
			"apply_pass"	=>	array("name"=>"小组申请通过","action"=>"apply_pass"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"set_effect"	=>	array("name"=>"设置有效","action"=>"set_effect"),
			"user_index"	=>	array("name"=>"组员管理","action"=>"user_index"),
			"user_insert"	=>	array("name"=>"组员新增","action"=>"user_insert"),
			"user_update"	=>	array("name"=>"组员更新","action"=>"user_update"),
			"user_del"	=>	array("name"=>"组员删除","action"=>"user_del"),
		)
	),
	"TopicGroupCate"	=>	array(
		"name"	=>	"分享小组分类", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"小组分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"小组分类添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"小组分类编辑提交","action"=>"update"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"set_effect"	=>	array("name"=>"设置有效","action"=>"set_effect"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"TopicImage"	=>	array(
		"name"	=>	"主题图片", 
		"node"	=>	array( 
			"delete"	=>	array("name"=>"删除主题图片","action"=>"delete"),
		)
	),
	"TopicReply"	=>	array(
		"name"	=>	"主题回复", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"回复列表","action"=>"index"),
			"udpate"	=>	array("name"=>"更新回复","action"=>"udpate"),
			"delete"	=>	array("name"=>"删除回复","action"=>"delete"),
		)
	),
	"TopicTag"	=>	array(
		"name"	=>	"分享标签", 
		"node"	=>	array(
			"index"	=>	array("name"=>"分享标签列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"TopicTagCate"	=>	array(
		"name"	=>	"分享标签分类", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"分享标签分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
//	"TopicTitle"	=>	array(
//		"name"	=>	"分享话题", 
//		"node"	=>	array( 
//			"index"	=>	array("name"=>"分享话题列表","action"=>"index"),
//			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
//			"update"	=>	array("name"=>"修改提交","action"=>"update"),
//			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
//		)
//	),
	"User"	=>	array(
		"name"	=>	"会员", 
		"node"	=>	array( 
			"account_detail"	=>	array("name"=>"帐户详情","action"=>"account_detail"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"edit"	=>	array("name"=>"编辑页面","action"=>"edit"),
			"export_csv"	=>	array("name"=>"导出csv","action"=>"export_csv"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"foreverdelete_account_detail"	=>	array("name"=>"永久删除帐户详情","action"=>"foreverdelete_account_detail"),
			"index"	=>	array("name"=>"会员列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"modify_account"	=>	array("name"=>"更新账户金额，积分，经验","action"=>"modify_account"),
			"restore"	=>	array("name"=>"恢复","action"=>"restore"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"trash"	=>	array("name"=>"会员回收站","action"=>"trash"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"withdrawal_index"	=>	array("name"=>"会员提现列表","action"=>"withdrawal_index"),
			"withdrawal_edit"	=>	array("name"=>"会员提现弹出框","action"=>"withdrawal_edit"),
			"do_withdrawal"	=>	array("name"=>"会员提现审核","action"=>"do_withdrawal"),
			"del_withdrawal"	=>	array("name"=>"会员提现记录删除","action"=>"del_withdrawal"),
		)
	),
//	"UserField"	=>	array(
//		"name"	=>	"会员字段", 
//		"node"	=>	array( 
//			"index"	=>	array("name"=>"会员字段列表","action"=>"index"),
//			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
//			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
//			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
//			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
//		)
//	),
	"UserGroup"	=>	array(
		"name"	=>	"会员组别", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"会员组别列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"UserLevel"	=>	array(
		"name"	=>	"会员等级", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"会员等级列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
//	"Vote"	=>	array(
//		"name"	=>	"投票调查", 
//		"node"	=>	array( 
//			"add"	=>	array("name"=>"添加页面","action"=>"add"),
//			"add_ask_row"	=>	array("name"=>"添加问题","action"=>"add_ask_row"),
//			"do_vote_ask"	=>	array("name"=>"保存问卷","action"=>"do_vote_ask"),
//			"edit"	=>	array("name"=>"编辑页面","action"=>"edit"),
//			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
//			"index"	=>	array("name"=>"投票调查列表","action"=>"index"),
//			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
//			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
//			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
//			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
//			"vote_ask"	=>	array("name"=>"编辑问卷","action"=>"vote_ask"),
//			"vote_result"	=>	array("name"=>"查看结果","action"=>"vote_result"),
//		)
//	),
	"WeightUnit"	=>	array(
		"name"	=>	"重量单位", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"重量列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"Youhui"	=>	array(
		"name"	=>	"优惠券", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"优惠券列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"publish"	=>	array("name"=>"商家提交","action"=>"publish"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"biz_submit_del"	=>	array("name"=>"删除申请","action"=>"biz_submit_del"),
			"downline"	=>	array("name"=>"下架申请","action"=>"downline"),
			"refused_apply"	=>	array("name"=>"拒绝申请","action"=>"refused_apply"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
				
		)
	),
	"YouhuiLog"	=>	array(
		"name"	=>	"优惠券下载日志", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"优惠券发送日志","action"=>"index"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"send_sms"	=>	array("name"=>"发送短信","action"=>"send_sms"),
		)
	),
);
?>