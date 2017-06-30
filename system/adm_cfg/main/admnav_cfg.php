<?php

return array(
    "index" => array(
        "name" => "首页",
        "key" => "index",
        "groups" => array(
            "index" => array(
                "name" => "首页",
                "key" => "index",
                "nodes" => array(
                    array("name" => "首页", "module" => "Index", "action" => "main"),
                ),
            ),
            "balance" => array(
                "name" => "报表",
                "key" => "balance",
                "nodes" => array(
                    array("name" => "报表统计", "module" => "Balance", "action" => "index"),
                    array("name" => "结算报表", "module" => "Balance", "action" => "bill"),
                ),
            ),
        ),
    ),
    "deal" => array(
        "name" => "项目管理",
        "key" => "deal",
        "groups" => array(
            "deal" => array(
                "name" => "商品管理",
                "key" => "deal",
                "nodes" => array(
                    array("name" => "团购列表", "module" => "Deal", "action" => "index"),
                    array("name" => "商品列表", "module" => "Deal", "action" => "shop"),
                    array("name" => "商品回收站", "module" => "Deal", "action" => "trash"),
                    array("name" => "商家团购提交", "module" => "Deal", "action" => "tuan_publish"),
                    array("name" => "商家商品提交", "module" => "Deal", "action" => "shop_publish"),
                ),
            ),
            "youhui" => array(
                "name" => "优惠券管理",
                "key" => "youhui",
                "nodes" => array(
                    array("name" => "优惠券列表", "module" => "Youhui", "action" => "index"),
                    array("name" => "优惠券发送日志", "module" => "YouhuiLog", "action" => "index"),
                    array("name" => "商家优惠券提交", "module" => "Youhui", "action" => "publish"),
                ),
            ),
            "event" => array(
                "name" => "活动管理",
                "key" => "event",
                "nodes" => array(
                    array("name" => "活动列表", "module" => "Event", "action" => "index"),
                    array("name" => "商家活动提交", "module" => "Event", "action" => "publish"),
                ),
            ),
            "article" => array(
                "name" => "文章管理",
                "key" => "article",
                "nodes" => array(
                    array("name" => "文章分类", "module" => "ArticleCate", "action" => "index"),
                    array("name" => "文章列表", "module" => "Article", "action" => "index"),
                ),
            ),
            "shopcate" => array(
                "name" => "分类管理",
                "key" => "shopcate",
                "nodes" => array(
                    array("name" => "生活服务分类", "module" => "DealCate", "action" => "index"),
                    array("name" => "生活服务子类", "module" => "DealCateType", "action" => "index"),
                    array("name" => "商城分类", "module" => "ShopCate", "action" => "index"),
                    array("name" => "商品类型", "module" => "GoodsType", "action" => "index"),
                    array("name" => "商品检索分组", "module" => "FilterGroup", "action" => "index"),
                    array("name" => "活动分类", "module" => "EventCate", "action" => "index"),
                    array("name" => "品牌列表", "module" => "Brand", "action" => "index"),
                ),
            ),
        ),
    ),
    "youhui" => array(
        "name" => "商家与点评",
        "key" => "youhui",
        "groups" => array(
            "supplier" => array(
                "name" => "供应商",
                "key" => "supplier",
                "nodes" => array(
                    array("name" => "商户列表", "module" => "Supplier", "action" => "index"),
                    array("name" => "门店列表", "module" => "SupplierLocation", "action" => "index"),
                    array("name" => "商家入驻申请", "module" => "SupplierSubmit", "action" => "index"),
                    array("name" => "商家门店提交", "module" => "SupplierLocation", "action" => "publish"),
                    array("name" => "商户提现", "module" => "Supplier", "action" => "charge_index"),
                    array("name" => "二次确认", "module"=>"Confirm","action"=>"index"),
                ),
            ),
            "dpgroup" => array(
                "name" => "点评分组管理",
                "key" => "dpgroup",
                "nodes" => array(
                    array("name" => "点评标签分组", "module" => "TagGroup", "action" => "index"),
                    array("name" => "点评评分分组", "module" => "PointGroup", "action" => "index"),
                ),
            ),
            "dp" => array(
                "name" => "点评管理",
                "key" => "dp",
                "nodes" => array(
                    array("name" => "点评列表", "module" => "SupplierLocationDp", "action" => "index"),
                ),
            ),
        ),
    ),
    "order" => array(
        "name" => "订单管理",
        "key" => "order",
        "groups" => array(
            "order" => array(
                "name" => "订单管理",
                "key" => "order",
                "nodes" => array(
                    array("name" => "订单列表", "module" => "DealOrder", "action" => "deal_index"),
                    array("name" => "历史订单", "module" => "DealOrder", "action" => "deal_trash"),
                    array("name" => "充值订单列表", "module" => "DealOrder", "action" => "incharge_index"),
                    array("name" => "历史充值订单", "module" => "DealOrder", "action" => "incharge_trash"),
                    array("name" => "收款单列表", "module" => "PaymentNotice", "action" => "index"),
                    array("name" => "抽奖号列表", "module" => "DealOrder", "action" => "lottery_index"),
                    array("name" => "订单来路统计", "module" => "DealOrder", "action" => "referer"),
                ),
            ),
            "orderinterface" => array(
                "name" => "订单业务接口",
                "key" => "orderinterface",
                "nodes" => array(
                    array("name" => "支付接口列表", "module" => "Payment", "action" => "index"),
                    array("name" => "促销接口列表", "module" => "Promote", "action" => "index"),
                    array("name" => "快递接口列表", "module" => "Express", "action" => "index"),
                    array("name" => "红包类型列表", "module" => "EcvType", "action" => "index"),
                ),
            ),
            "delivery" => array(
                "name" => "配送方式",
                "key" => "delivery",
                "nodes" => array(
                    array("name" => "重量列表", "module" => "WeightUnit", "action" => "index"),
                    array("name" => "配送方式列表", "module" => "Delivery", "action" => "index"),
                    array("name" => "配送地区列表", "module" => "DeliveryRegion", "action" => "index"),
                ),
            ),
// 					"balance"	=>	array(
// 							"name"	=>	"报表与结算",
// 							"key"	=>	"balance",
// 							"nodes"	=>	array(
// 									array("name"=>"结算列表","module"=>"Balance","action"=>"index"),
// 									array("name"=>"报表列表","module"=>"Statistic","action"=>"index"),
// 							),
// 					),
        ),
    ),
    "user" => array(
        "name" => "会员管理",
        "key" => "user",
        "groups" => array(
            "user" => array(
                "name" => "会员管理",
                "key" => "user",
                "nodes" => array(
                    array("name" => "会员列表", "module" => "User", "action" => "index"),
                    array("name" => "会员回收站", "module" => "User", "action" => "trash"),
                    array("name" => "会员提现", "module" => "User", "action" => "withdrawal_index"),
                    array("name" => "达人申请列表", "module" => "DarenSubmit", "action" => "index"),
                ),
            ),
            "userconf" => array(
                "name" => "会员配置",
                "key" => "userconf",
                "nodes" => array(
//									array("name"=>"会员字段列表","module"=>"UserField","action"=>"index"),
                    array("name" => "会员组别列表", "module" => "UserGroup", "action" => "index"),
                    array("name" => "会员等级列表", "module" => "UserLevel", "action" => "index"),
                    array("name" => "勋章列表", "module" => "Medal", "action" => "index"),
                ),
            ),
            "referral" => array(
                "name" => "会员返利",
                "key" => "referral",
                "nodes" => array(
                    array("name" => "邀请返利列表", "module" => "Referrals", "action" => "index"),
                ),
            ),
            "notice" => array(
                "name" => "站内消息",
                "key" => "notice",
                "nodes" => array(
                    array("name" => "消息群发", "module" => "MsgSystem", "action" => "index"),
                    array("name" => "消息列表", "module" => "MsgBox", "action" => "index"),
                ),
            ),
            "usergroup" => array(
                "name" => "小组管理",
                "key" => "usergroup",
                "nodes" => array(
                    array("name" => "小组分类列表", "module" => "TopicGroupCate", "action" => "index"),
                    array("name" => "小组列表", "module" => "TopicGroup", "action" => "index"),
                    array("name" => "小组申请审核", "module" => "TopicGroup", "action" => "apply_index"),
                ),
            ),
            "sharetag" => array(
                "name" => "分享设置",
                "key" => "sharetag",
                "nodes" => array(
                    array("name" => "分享标签分类列表", "module" => "TopicTagCate", "action" => "index"),
                    array("name" => "分享标签列表", "module" => "TopicTag", "action" => "index"),
//									array("name"=>"分享话题列表","module"=>"TopicTitle","action"=>"index"),
                ),
            ),
            "msgadmin" => array(
                "name" => "会员发表管理",
                "key" => "msgadmin",
                "nodes" => array(
// 									array("name"=>"会员留言管理","module"=>"Message","action"=>"index"),
                    array("name" => "会员分享管理", "module" => "Topic", "action" => "index"),
                ),
            ),
            "userinterface" => array(
                "name" => "会员插件管理",
                "key" => "userinterface",
                "nodes" => array(
                    array("name" => "会员整合插件", "module" => "Integrate", "action" => "index"),
                    array("name" => "API插件列表", "module" => "ApiLogin", "action" => "index"),
                ),
            ),
        ),
    ),
    "promote" => array(
        "name" => "短信邮件",
        "key" => "promote",
        "groups" => array(
            "msg" => array(
                "name" => "消息模板管理",
                "key" => "msg",
                "nodes" => array(
                    array("name" => "消息模板管理", "module" => "MsgTemplate", "action" => "index"),
                ),
            ),
            "mail" => array(
                "name" => "邮件管理",
                "key" => "mail",
                "nodes" => array(
                    array("name" => "邮件订阅列表", "module" => "MailList", "action" => "index"),
                    array("name" => "邮件服务器列表", "module" => "MailServer", "action" => "index"),
                    array("name" => "邮件列表", "module" => "PromoteMsg", "action" => "mail_index"),
                ),
            ),
            "sms" => array(
                "name" => "短信管理",
                "key" => "sms",
                "nodes" => array(
                    array("name" => "短信接口列表", "module" => "Sms", "action" => "index"),
                    array("name" => "短信订阅列表", "module" => "MobileList", "action" => "index"),
                    array("name" => "短信列表", "module" => "PromoteMsg", "action" => "sms_index"),
                ),
            ),
            "msglist" => array(
                "name" => "队列管理",
                "key" => "msglist",
                "nodes" => array(
                    array("name" => "业务队列列表", "module" => "DealMsgList", "action" => "index"),
                    array("name" => "推广队列列表", "module" => "PromoteMsgList", "action" => "index"),
                ),
            ),
        ),
    ),
    "mobile" => array(
        "name" => "移动平台",
        "key" => "mobile",
        "groups" => array(
            "mobile" => array(
                "name" => "移动平台设置",
                "key" => "mobile",
                "nodes" => array(
                    array("name" => "手机端配置", "module" => "Conf", "action" => "mobile"),
                    array("name" => "手机端专题位", "module" => "MZt", "action" => "index"),
                    array("name" => "手机端广告列表", "module" => "MAdv", "action" => "index"),
                    array("name" => "首页菜单列表", "module" => "MIndex", "action" => "index"),
                    array("name" => "手机端公告列表", "module" => "MNotice", "action" => "index"),
                ),
            ),
        ),
    ),
    "system" => array(
        "name" => "系统设置",
        "key" => "system",
        "groups" => array(
            "sysconf" => array(
                "name" => "系统设置",
                "key" => "sysconf",
                "nodes" => array(
                    array("name" => "系统配置", "module" => "Conf", "action" => "index"),
                    array("name" => "导航菜单", "module" => "Nav", "action" => "index"),
                    array("name" => "广告设置", "module" => "Adv", "action" => "index"),
                //array("name"=>"投票调查","module"=>"Vote","action"=>"index"),
                ),
            ),
            "dealcity" => array(
                "name" => "城市与地区",
                "key" => "dealcity",
                "nodes" => array(
                    array("name" => "城市列表", "module" => "DealCity", "action" => "index"),
                    array("name" => "商圈列表", "module" => "Area", "action" => "index"),
                ),
            ),
            "link" => array(
                "name" => "友情链接",
                "key" => "link",
                "nodes" => array(
                    array("name" => "友情链接分组", "module" => "LinkGroup", "action" => "index"),
                    array("name" => "友情链接列表", "module" => "Link", "action" => "index"),
                ),
            ),
            "admin" => array(
                "name" => "系统管理员",
                "key" => "admin",
                "nodes" => array(
                    array("name" => "角色管理", "module" => "Role", "action" => "index"),
                    array("name" => "管理员管理", "module" => "Admin", "action" => "index"),
                ),
            ),
            "datebase" => array(
                "name" => "数据库",
                "key" => "datebase",
                "nodes" => array(
                    array("name" => "数据库备份", "module" => "Database", "action" => "index"),
                    array("name" => "SQL操作", "module" => "Database", "action" => "sql"),
                ),
            ),
            "syslog" => array(
                "name" => "系统日志",
                "key" => "syslog",
                "nodes" => array(
                    array("name" => "系统日志列表", "module" => "Log", "action" => "index"),
                    array("name" => "第三方验证日志", "module" => "Log", "action" => "coupon"),
                ),
            ),
        ),
    ),
);
?>