<?php
//000000000600s:78592:"<?php exit;?>a:3:{s:8:"template";a:4:{i:0;s:45:"D:/phpStudy/WWW/app/Tpl/main/fanwe/index.html";i:1;s:50:"D:/phpStudy/WWW/app/Tpl/main/fanwe/inc/header.html";i:2;s:53:"D:/phpStudy/WWW/app/Tpl/main/fanwe/inc/cate_tree.html";i:3;s:50:"D:/phpStudy/WWW/app/Tpl/main/fanwe/inc/footer.html";}s:7:"expires";i:1451962981;s:8:"maketime";i:1451962381;}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>方维o2o商业系统 - 方维o2o商业系统,国内最优秀的PHP开源o2o系统</title>
<meta name="keywords" content=" 方维o2o商业系统关键词" />
<meta name="description" content=" 方维o2o商业系统描述" />
<script type="text/javascript">
var APP_ROOT = '';
var CART_URL = '/index.php?ctl=cart';
var CART_CHECK_URL = '/index.php?ctl=cart&act=check';
var send_span = 2000;
var IS_RUN_CRON = 1;
var DEAL_MSG_URL = '/index.php?ctl=cron&act=deal_msg_list';
var AJAX_LOGIN_URL	= '/index.php?ctl=user&act=ajax_login';
var AJAX_URL	= '/index.php?ctl=ajax';
var CITY_COUNT	= 4;
//关于图片上传的定义
var LOADER_IMG = 'http://test.gope.cn/app/Tpl/main/fanwe/images/loader_img.gif';
var UPLOAD_SWF = 'http://test.gope.cn/app/Tpl/main/fanwe/js/utils/Moxie.swf';
var UPLOAD_XAP = 'http://test.gope.cn/app/Tpl/main/fanwe/js/utils/Moxie.xap';
var MAX_IMAGE_SIZE = '3000000';
var ALLOW_IMAGE_EXT = 'jpg,gif,png,jpeg';
var UPLOAD_URL = '/index.php?ctl=file&act=upload';
var QRCODE_ON = '0';
</script>
<script type="text/javascript" src="/public/runtime/app/lang.js"></script>
<link rel="stylesheet" type="text/css" href="http://test.gope.cn/public/runtime/statics/0139d8a49db0ec510a12813e212919c2.css?v=3.07.4782" />
<script type="text/javascript" src="http://test.gope.cn/public/runtime/statics/8dba81567a45625b47b4eed075a48de4.js?v=3.07.4782"></script>
</head>
<body>
<img src="http://test.gope.cn/app/Tpl/main/fanwe/images/loader_img.gif" style="display:none;" /><!--延时加载的替代图片生成-->
<div class="city_list">
	<div class="city_list_box">
				<a href="/index.php?city=beijing" class="city_item" >北京</a>
				<a href="/index.php?city=fuzhou" class="city_item" >福州</a>
				<a href="/index.php?city=shanghai" class="city_item" >上海</a>
				<a href="/index.php?city=xiamen" class="city_item" >厦门</a>
			</div>
</div>
<div class="top_nav">
	<div class="wrap_full_w main_layout">
		<span class="f_l">欢迎来到方维o2o商业系统</span>	
		<span class="f_r">
			<ul class="head_tip">
				<li class="user_tip"><a href="/biz.php" target="_blank">商户中心</a></li>
				<li class="cart_tip" id="cart_tip">554fcae493e564ee0dc75bdf2ebf94caload_cart_count|YToxOntzOjQ6Im5hbWUiO3M6MTU6ImxvYWRfY2FydF9jb3VudCI7fQ==554fcae493e564ee0dc75bdf2ebf94ca</li>
				<li class="user_tip" id="history_tip">554fcae493e564ee0dc75bdf2ebf94caload_head_history|YToxOntzOjQ6Im5hbWUiO3M6MTc6ImxvYWRfaGVhZF9oaXN0b3J5Ijt9554fcae493e564ee0dc75bdf2ebf94ca</li>
				<li class="user_tip" id="head_user_tip">554fcae493e564ee0dc75bdf2ebf94caload_user_tip|YToxOntzOjQ6Im5hbWUiO3M6MTM6ImxvYWRfdXNlcl90aXAiO30=554fcae493e564ee0dc75bdf2ebf94ca</li>
			</ul>
		</span>
	</div>
</div><!--顶部横栏-->
<div class="blank15"></div>
<div class="wrap_full_w main_layout head_main">
	<div class="logo f_l">
	<a class="link" href="/">
				<span style='display:inline-block; width:200px; height:44px; background:url(http://test.gope.cn/public/attachment/201011/4cdd501dc023b.png) no-repeat; _filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=http://test.gope.cn/public/attachment/201011/4cdd501dc023b.png, sizingMethod=scale);_background-image:none;'></span>	</a>
	</div>
	<div class="city f_l">
				<a class="city_name" href="javascript:void(0);"  jump="/index.php?ctl=city">554fcae493e564ee0dc75bdf2ebf94caload_city_name|YToxOntzOjQ6Im5hbWUiO3M6MTQ6ImxvYWRfY2l0eV9uYW1lIjt9554fcae493e564ee0dc75bdf2ebf94ca&nbsp;<i></i></a>
		<a href="javascript:void(0);"  jump="/index.php?ctl=city" class="city_switch f_l">切换城市</a>
			</div>
	<div class="search f_r">
		<div class="top_search">
			<form action="/index.php?ctl=search" name="search_form" method=post >
			<select name="search_type" class="ui-select search_type f_l">
				<option value="1" >搜团购</option>
				<option value="2" >搜优惠</option>
				<option value="3" >搜活动</option>
				<option value="4" >搜商家</option>				
				<option value="5" >搜商品</option>
				<option value="6" >搜分享</option>
			</select>
			<input type="text" name="search_keyword" class="ui-textbox search_keyword f_l" holder="请输入您要搜索的关键词" value="" />
			<button class="ui-button f_l" rel="search_btn" type="submit">搜索</button>
			</form>
		</div>
		<ul class="search_hot_keyword">
						<li><a href="/index.php?ctl=search&act=jump"></a></li>
					</ul>
	</div>
</div><!--logo与头部搜索-->
<div class="blank15"></div>
<div class="nav_bar">
	<div class="wrap_full_w main_layout">
				<div class="drop_nav" id="drop_nav" ref="no_drop">
			<span class="drop_title">全部分类<i></i></span>
			<div class="drop_box">
				<div class="cate_tree">
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=8"><i class="diyfont">&#58896;</i>&nbsp;餐饮美食</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=28"  >甜点</a></li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=29"  >面包</a></li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=30" class="heavy" >烧烤</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=8">餐饮美食</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=28" >甜点</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=29" >面包</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=30" class="heavy">烧烤</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=1" class="heavy">咖啡</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=2" class="heavy">闽菜</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=3" >东北菜</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=4" class="heavy">川菜</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=26" >日本料理</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=27" class="heavy">本帮菜</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=38" >西餐</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=9"><i class="diyfont">&#58894;</i>&nbsp;休闲娱乐</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=1" class="heavy" >咖啡</a></li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=5"  >KTV</a></li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=6" class="heavy" >自助游</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=9">休闲娱乐</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=1" class="heavy">咖啡</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=5" >KTV</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=6" class="heavy">自助游</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=31" >足疗按摩</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=32" >水上世界</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=33" >运动健身</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=34" >采摘/农家乐</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=10"><i class="diyfont">&#58880;</i>&nbsp;生活服务</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=35"  >婚纱摄影</a></li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=36"  >个性写真</a></li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=37"  >培训课程</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=10">生活服务</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=35" >婚纱摄影</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=36" >个性写真</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=37" >培训课程</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=39" >配镜</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=11"><i class="diyfont">&#58889;</i>&nbsp;酒店旅游</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=6" class="heavy" >自助游</a></li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=7"  >周边游</a></li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=8" class="heavy" >国内游</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=11">酒店旅游</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=6" class="heavy">自助游</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=7" >周边游</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=8" class="heavy">国内游</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=9" >海外游</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=13"><i class="diyfont">&#58883;</i>&nbsp;爱车学车</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=16" class="heavy" >真皮座椅</a></li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=17" class="heavy" >打蜡</a></li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=10" class="heavy" >洗车</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=13">爱车学车</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=16" class="heavy">真皮座椅</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=17" class="heavy">打蜡</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=10" class="heavy">洗车</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=11" class="heavy">汽车保养</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=12" >驾校</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=13" class="heavy">4S店</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=14" >音响</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=15" class="heavy">车载导航</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=14"><i class="diyfont">&#58888;</i>&nbsp;都市丽人</a></dt>
		</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=15"><i class="diyfont">&#58895;</i>&nbsp;我要结婚</a></dt>
		</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=16"><i class="diyfont">&#58881;</i>&nbsp;医疗健康</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=24"  >心理诊所</a></li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=25" class="heavy" >疗养院</a></li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=18"  >男科</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=16">医疗健康</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=24" >心理诊所</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=25" class="heavy">疗养院</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=18" >男科</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=19" >妇科</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=20" >儿科</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=21" class="heavy">口腔科</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=22" >眼科</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=16&tid=23" class="heavy">体检中心</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="no_border">
	<dt><a href="/index.php?ctl=tuan&cid=17"><i class="diyfont">&#58887;</i>&nbsp;母婴亲子</a></dt>
		</dl>
</div>			</div>
		</div><!--下拉菜单-->
				<div class="main_nav">
			<ul>
								<li class="current"><a href="/index.php" >首页</a></li>
								<li ><a href="/index.php?ctl=tuan" >团购</a></li>
								<li ><a href="/index.php?ctl=mall" >商城</a></li>
								<li ><a href="/index.php?ctl=events" >活动</a></li>
								<li ><a href="/index.php?ctl=stores" >商家</a></li>
								<li ><a href="/index.php?ctl=daren" >达人秀</a></li>
								<li ><a href="/index.php?ctl=group" >小组</a></li>
								<li ><a href="/index.php?ctl=discover" >发现</a></li>
								<li ><a href="/index.php?ctl=youhuis" >优惠券</a></li>
								<li ><a href="/index.php?ctl=scores" >积分商城</a></li>
								<li ><a href="/index.php?ctl=dc" >外卖</a></li>
								
			</ul>
		</div>
	</div>
</div>	
<div class="wrap_full_w main_layout" id="flow_cate_outer">
<div id="flow_cate">
		<ul>
									<li rel="index_cate_8" bg="#a1410d">
								<i class="diyfont">&#58896;</i>
								<font>餐饮美食</font>
			</li>
												<li rel="index_cate_9" bg="#8fc63d">
								<i class="diyfont">&#58894;</i>
								<font>休闲娱乐</font>
			</li>
															<li rel="index_mall_cate_24" bg="#438ccb">
								<i class="diyfont"></i>
								<font>服装</font>
			</li>
												<li rel="index_mall_cate_29" bg="#f16522">
								<i class="diyfont"></i>
								<font>母婴用品</font>
			</li>
								</ul>
</div>
</div>
<div class="wrap_full_w main_layout  clearfix">
	<div class="fix_cate_tree " >
			<div class="cate_tree">
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=8"><i class="diyfont">&#58896;</i>&nbsp;餐饮美食</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=28"  >甜点</a></li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=29"  >面包</a></li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=30" class="heavy" >烧烤</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=8">餐饮美食</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=28" >甜点</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=29" >面包</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=30" class="heavy">烧烤</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=1" class="heavy">咖啡</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=2" class="heavy">闽菜</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=3" >东北菜</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=4" class="heavy">川菜</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=26" >日本料理</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=27" class="heavy">本帮菜</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=8&tid=38" >西餐</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=9"><i class="diyfont">&#58894;</i>&nbsp;休闲娱乐</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=1" class="heavy" >咖啡</a></li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=5"  >KTV</a></li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=6" class="heavy" >自助游</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=9">休闲娱乐</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=1" class="heavy">咖啡</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=5" >KTV</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=6" class="heavy">自助游</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=31" >足疗按摩</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=32" >水上世界</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=33" >运动健身</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=9&tid=34" >采摘/农家乐</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=10"><i class="diyfont">&#58880;</i>&nbsp;生活服务</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=35"  >婚纱摄影</a></li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=36"  >个性写真</a></li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=37"  >培训课程</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=10">生活服务</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=35" >婚纱摄影</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=36" >个性写真</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=37" >培训课程</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=10&tid=39" >配镜</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=11"><i class="diyfont">&#58889;</i>&nbsp;酒店旅游</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=6" class="heavy" >自助游</a></li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=7"  >周边游</a></li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=8" class="heavy" >国内游</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=11">酒店旅游</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=6" class="heavy">自助游</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=7" >周边游</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=8" class="heavy">国内游</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=11&tid=9" >海外游</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="">
	<dt><a href="/index.php?ctl=tuan&cid=13"><i class="diyfont">&#58883;</i>&nbsp;爱车学车</a></dt>
		<dd class="sub_nav ">
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=16" class="heavy" >真皮座椅</a></li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=17" class="heavy" >打蜡</a></li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=10" class="heavy" >洗车</a></li>
					</ul>
	</dd>
			<dd class="pop_nav">
		<span><a href="/index.php?ctl=tuan&cid=13">爱车学车</a></span>
		<ul>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=16" class="heavy">真皮座椅</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=17" class="heavy">打蜡</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=10" class="heavy">洗车</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=11" class="heavy">汽车保养</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=12" >驾校</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=13" class="heavy">4S店</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=14" >音响</a>&nbsp;&nbsp;|</li>
						<li><a href="/index.php?ctl=tuan&cid=13&tid=15" class="heavy">车载导航</a>&nbsp;&nbsp;|</li>
					</ul>
	</dd>
	</dl>
<dl class="no_border">
	<dt><a href="/index.php?ctl=tuan&cid=14"><i class="diyfont">&#58888;</i>&nbsp;都市丽人</a></dt>
		</dl>
</div>	</div>
	
	<div class="main_screen">
		<div class="blank"></div>
		<div class="main_roll f_l" id="main_roll">
		<ul class="roll">
		<li><img src="http://test.gope.cn/public/attachment/201502/25/11/54ed41c0e3216.png" alt="" border="0" /></li>
		<li><img src="http://test.gope.cn/public/attachment/201502/25/11/54ed41b6bfeec.png" alt="" border="0" /><br />
</li>		
		</ul>
		
		</div>
		<div class="side_roll f_l" id="side_roll">
			<i class="t_left"></i>
			<i class="t_right"></i>
			<ul class="roll">
			<li><img src="http://test.gope.cn/public/attachment/201502/25/12/54ed559176fa9.jpg" alt="" border="0" /></li>
			<li><img src="http://test.gope.cn/public/attachment/201502/25/12/54ed559ba1dc1.jpg" alt="" border="0" /></li>
			</ul>
		</div>
		<div class="blank"></div>
		<div class="index_pick f_l  ">
			<span class="tuan_cate">
				<div class="tag_list">
				<h1><i class="iconfont">&#xe609;</i>&nbsp;热门团购</h1>
				
					<ul>
												<li><a href="/index.php?ctl=tuan&cid=8" class="heavy" >餐饮美食</a></li>
												<li><a href="/index.php?ctl=tuan&cid=9"  >休闲娱乐</a></li>
												<li><a href="/index.php?ctl=tuan&cid=10" class="heavy" >生活服务</a></li>
												<li><a href="/index.php?ctl=tuan&cid=11"  >酒店旅游</a></li>
												<li><a href="/index.php?ctl=tuan&cid=13"  >爱车学车</a></li>
												<li><a href="/index.php?ctl=tuan&cid=14"  >都市丽人</a></li>
												<li><a href="/index.php?ctl=tuan&cid=15"  >我要结婚</a></li>
												<li><a href="/index.php?ctl=tuan&cid=16"  >医疗健康</a></li>
												<li><a href="/index.php?ctl=tuan&cid=17"  >母婴亲子</a></li>
											
					</ul>
				
				</div>
			</span>
			
			<span class="tuan_tag no_border">
				<div class="tag_list">
				<h1><i class="iconfont">&#xe611;</i>&nbsp;热门标签</h1>
				
					<ul>
												<li><a href="/index.php?ctl=tuan&cid=9&tid=6" class="heavy" >自助游</a></li>
												<li><a href="/index.php?ctl=tuan&cid=16&tid=18"  >男科</a></li>
												<li><a href="/index.php?ctl=tuan&cid=11&tid=6" class="heavy" >自助游</a></li>
												<li><a href="/index.php?ctl=tuan&cid=16&tid=19"  >妇科</a></li>
												<li><a href="/index.php?ctl=tuan&cid=11&tid=7"  >周边游</a></li>
												<li><a href="/index.php?ctl=tuan&cid=16&tid=20"  >儿科</a></li>
												<li><a href="/index.php?ctl=tuan&cid=11&tid=8" class="heavy" >国内游</a></li>
												<li><a href="/index.php?ctl=tuan&cid=16&tid=21" class="heavy" >口腔科</a></li>
												<li><a href="/index.php?ctl=tuan&cid=11&tid=9"  >海外游</a></li>
												<li><a href="/index.php?ctl=tuan&cid=16&tid=22"  >眼科</a></li>
												<li><a href="/index.php?ctl=tuan&cid=13&tid=10" class="heavy" >洗车</a></li>
												<li><a href="/index.php?ctl=tuan&cid=16&tid=23" class="heavy" >体检中心</a></li>
												<li><a href="/index.php?ctl=tuan&cid=13&tid=11" class="heavy" >汽车保养</a></li>
												<li><a href="/index.php?ctl=tuan&cid=16&tid=24"  >心理诊所</a></li>
												<li><a href="/index.php?ctl=tuan&cid=8&tid=1" class="heavy" >咖啡</a></li>
											</ul>
				
				</div>
			</span>
			<span class="tuan_area">
				<div class="tag_list">
				<h1><i class="iconfont">&#xe615;</i>&nbsp;全部区域</h1>
				
					<ul>
												<li><a href="/index.php?ctl=tuan&aid=8&qid=13">五一广场</a></li>
												<li><a href="/index.php?ctl=tuan&aid=8&qid=14">东街口</a></li>
												<li><a href="/index.php?ctl=tuan&aid=8&qid=15">福州广场</a></li>
												<li><a href="/index.php?ctl=tuan&aid=8&qid=16">省体育中心</a></li>
												<li><a href="/index.php?ctl=tuan&aid=8&qid=17">西禅寺</a></li>
												<li><a href="/index.php?ctl=tuan&aid=8&qid=18">社会主义学院</a></li>
												<li><a href="/index.php?ctl=tuan&aid=8&qid=19">西洪路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=8&qid=20">屏山</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=21">中亭街</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=22">六一中路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=23">龙华大厦</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=24">时代名城</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=25">台江路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=26">宝龙城市广场</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=27">万象城</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=28">桥亭</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=29">小桥头</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=30">交通路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=31">中亭街</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=32">白马河</a></li>
												<li><a href="/index.php?ctl=tuan&aid=10&qid=33">博美诗邦</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=34">观海路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=35">三叉街新村</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=36">北京金山</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=37">仓山镇</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=38">螺洲</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=39">三高路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=40">下渡</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=41">工农路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=11&qid=42">首山路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=9&qid=43">王庄新村</a></li>
												<li><a href="/index.php?ctl=tuan&aid=9&qid=44">岳峰路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=9&qid=45">融侨东区</a></li>
												<li><a href="/index.php?ctl=tuan&aid=9&qid=46">五里亭</a></li>
												<li><a href="/index.php?ctl=tuan&aid=9&qid=47">五一新村</a></li>
												<li><a href="/index.php?ctl=tuan&aid=9&qid=48">福光路</a></li>
												<li><a href="/index.php?ctl=tuan&aid=9&qid=49">五里亭</a></li>
												
					</ul>
				
				</div>
				
				<a href="javascript:void(0);" class="more">更多</a>
			</span>
		</div>
		<div class="index_mobile f_l">
			<ul>
				<li class="ios"><a href="javascript:void(0);"  down_url="/index.php?ctl=ajax&act=app_download&t=ios" ><i class="iconfont">&#xe614;</i>&nbsp;<em>IPhone</em> 下载</a></li>
				<li class="android"><a href="javascript:void(0);" down_url="/index.php?ctl=ajax&act=app_download&t=android"><i class="iconfont">&#xe613;</i>&nbsp;<em>Android</em> 下载</a></li>
				
			</ul>
		</div>
	</div>
</div>	
<div class="notice_row wrap_full_w main_layout">
	<div class="wrap_full_w main_layout">
			<div class="notice_board">
				<i class="iconfont f_l">&#xe618;</i>
				<ul>
										<li><a href="/index.php?ctl=deal&act=65">泰宁大金湖</a></li>									
										<li><a href="/index.php?ctl=deal&act=70">百度烤肉</a></li>									
									</ul>
				<a href="/index.php?ctl=news" class="more f_l news_more">更多</a>
			</div>
	</div>	
</div>
<div class="blank"></div>
<div class="wrap_full_w main_layout">
<div class="f_l wrap_full">
		<div class="index_rec_box" id="supplier_roll">
		<div class="title_row">
			<span><i class="iconfont">&#xe616;</i>&nbsp;名店推荐</span>
			<a href="/index.php?ctl=stores" class="more">更多</a>
		</div>
		<div class="content_row">
						<i class="t_left"></i>
			<i class="t_right"></i>
						<ul class="roll">
			
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=store&act=26" title="百度烤肉">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c4/78e391d4f2951cdefc0fb618bff567fc.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="/index.php?ctl=store&act=26"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/14/3636edc71744aa2820a9598cb15a3d0098_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=store&act=26" title="百度烤肉">百度烤肉</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=store&act=27" title="韩悦风尚烤肉（浦江大道店）">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c1/aff0e4adfae1498aab3683a181391759.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="/index.php?ctl=store&act=27"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/14/54ed724a9d6b5_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=store&act=27" title="韩悦风尚烤肉（浦江大道店）">韩悦风尚烤肉（浦江大道店）</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=store&act=28" title="石山水美式餐厅（东街店）">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/cf/ff30afb991eb23b949a397adb2b7ecee.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="/index.php?ctl=store&act=28"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/15/54ed765bb0d9b_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=store&act=28" title="石山水美式餐厅（东街店）">石山水美式餐厅（东街店）</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=store&act=29" title=" 【万象城/宝龙广场】 agogo量贩KTV">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c2/20b3ffcd3d1ec27a4622025b9c3ccf2e.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="/index.php?ctl=store&act=29"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/15/54ed7b363e1e0_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=store&act=29" title=" 【万象城/宝龙广场】 agogo量贩KTV"> 【万象城/宝龙广场】 ago…</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=store&act=30" title="贵安温泉">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c5/25b7dde2f21b4eca00c8bac8ecfb98a9.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="/index.php?ctl=store&act=30"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/15/54ed7dcce36b1_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=store&act=30" title="贵安温泉">贵安温泉</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=store&act=31" title="国际旅游社">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c2/d53870a8551910afc00b60508a7886a6.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="/index.php?ctl=store&act=31"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/15/54ed80bf64ba6_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=store&act=31" title="国际旅游社">国际旅游社</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=store&act=32" title="爱丁堡尊贵养生会所（福祥店)">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c5/36710ab5888fcf12f31345991dd17c63.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="/index.php?ctl=store&act=32"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/16/54ed864826695_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=store&act=32" title="爱丁堡尊贵养生会所（福祥店)">爱丁堡尊贵养生会所（福祥店)</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=store&act=34" title="美丽人生摄影工作室">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c2/dfd0aa1d2ff71af6f9d6c42eeaa1f970.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="/index.php?ctl=store&act=34"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/17/54ed9486611f6_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=store&act=34" title="美丽人生摄影工作室">美丽人生摄影工作室</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
						
			</ul>
		</div>
	</div>
	<div class="blank"></div>
			<div class="index_rec_box" id="youhui_roll">
		<div class="title_row">
			<span><i class="iconfont">&#xe609;</i>&nbsp;热门优惠券</span>
			<a href="/index.php?ctl=youhuis" class="more">更多</a>
		</div>
		<div class="content_row">
						<ul class="roll">
				
						<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=youhui&act=23" title="华莱士30元抵用券">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c0/b60acfa4bcb83fdea7e374772783b1d3.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<div class="tags">					
									
							<h2 class="tag9"></h2>
										</div>	
					<a href="/index.php?ctl=youhui&act=23" title="华莱士30元抵用券"><img lazy="true"  data-src="http://test.gope.cn/public/attachment/201502/26/11/54ee8fc5497f9_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=youhui&act=23" title="华莱士30元抵用券">华莱士30元抵用券</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
							<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
             			<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=youhui&act=22" title="一元吃肯德基">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c9/f06825a1f03d7c87239d1b216c424d2d.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<div class="tags">					
									
							<h2 class="tag9"></h2>
										</div>	
					<a href="/index.php?ctl=youhui&act=22" title="一元吃肯德基"><img lazy="true"  data-src="http://test.gope.cn/public/attachment/201502/26/11/54ee8eb6c0e75_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=youhui&act=22" title="一元吃肯德基">一元吃肯德基</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
							<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
             			<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=youhui&act=21" title="盛世经典牛排50元代金券">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c6/37ef55fbb2355aaf26c5e525b9e04a37.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<div class="tags">					
									
							<h2 class="tag4"></h2>
										</div>	
					<a href="/index.php?ctl=youhui&act=21" title="盛世经典牛排50元代金券"><img lazy="true"  data-src="http://test.gope.cn/public/attachment/201502/26/10/54ee8ae7cb6a2_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=youhui&act=21" title="盛世经典牛排50元代金券">盛世经典牛排50元代…</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
							<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
             			<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="/index.php?ctl=youhui&act=20" title="肯德基10元汉堡">
					<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c8/c559c9b898b42de4d17b9f227dbf3ce6.png" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<div class="tags">					
									
							<h2 class="tag4"></h2>
										</div>	
					<a href="/index.php?ctl=youhui&act=20" title="肯德基10元汉堡"><img lazy="true"  data-src="http://test.gope.cn/public/attachment/201502/26/09/54ee79ed82c2b_220x140.jpg" /></a>
				</div>
				<div class="name_row">
					<a href="/index.php?ctl=youhui&act=20" title="肯德基10元汉堡">肯德基10元汉堡</a>
				</div>
				<div class="extra_row">
					<div class="sale_review">							
			        	<span>
							<input class="ui-starbar" value="0.0000" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b>0</b>人点评</span>						
					</div>
				</div>
			</li>
                       
			</ul>
		</div>
	</div>
	</div><!--index_rec_layout_left-->
<div class="f_r wrap_s">
	<img src="http://test.gope.cn/public/attachment/201502/26/17/54eee2d489343.jpg" alt="" border="0" />
	<div class="blank"></div>
	<img src="http://test.gope.cn/public/attachment/201502/26/17/54eee2f10078a.jpg" alt="" border="0" />
</div><!--index_rec_layout_right-->
</div>
<div class="blank"></div>
<!--推荐的团购分类-->
<div class="wrap_full_w main_layout">
	
			<div class="index_cate" rel="index_cate_8">
		<div class="title_row">
			<div class="title"><i class="diyfont">&#58896;</i>&nbsp;&nbsp;餐饮美食</div>
			<ul>
								<li><a href="/index.php?ctl=tuan&cid=8&tid=1">咖啡</a> | </li>
								<li><a href="/index.php?ctl=tuan&cid=8&tid=2">闽菜</a> | </li>
								<li><a href="/index.php?ctl=tuan&cid=8&tid=4">川菜</a> | </li>
								<li><a href="/index.php?ctl=tuan&cid=8&tid=27">本帮菜</a> | </li>
								<li><a href="/index.php?ctl=tuan&cid=8&tid=30">烧烤</a></li>
							</ul>
			<a href="/index.php?ctl=tuan&cid=8" class="more">更多</a>
		</div>
		<div class="content_row clearfix">
			<ul class="tuan_list">
				
								
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=70" title="仅售49.90元！价值59元的百度烤肉单人自助午餐，提供免费WiFi。全新升级，盛大开业，特价优惠火爆抢购中">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/cd/42a8d43266915c02677cdd33a06c06e5.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag1"></h2>
											
							<h2 class="tag2"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=70" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/14/3636edc71744aa2820a9598cb15a3d0098_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=70" class="quan">
								【万象城/宝龙广场】百度烤肉							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=70">百度烤肉</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=70">
							仅售49.90元！价值59元的百度烤肉单人自助午餐，提供免费WiFi。全新升级，盛大开业，特价优惠火爆抢购中							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>49.9</span>
							<span class="origin_price">门店价：&yen;59</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=69" title="仅售102元！价值125元的双人套餐，提供免费WiFi。">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/cf/0b9e6a8e211fec846edb01547a8f9938.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag5"></h2>
											
							<h2 class="tag6"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=69" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/14/54ed6f616ffc5_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=69" class="quan">
								【28店通用】盛世经典牛排							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=69">双人套餐</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=69">
							仅售102元！价值125元的双人套餐，提供免费WiFi。							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>102</span>
							<span class="origin_price">门店价：&yen;125</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=shiss" title="石山水代金券 仅售80元！价值100元的代金券1张，全场通用，可叠加使用，提供免费WiFi。">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/cc/553e4b219062de0f31a3d58d66bcf50a.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag4"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=shiss" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/15/54ed765bb0d9b_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=shiss" class="quan">
								仅售80元！价值100元的代金券1张，全场通用，可叠加使用，提供免费WiFi。							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=shiss">石山水代金券</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=shiss">
							石山水代金券 仅售80元！价值100元的代金券1张，全场通用，可叠加使用，提供免费WiFi。							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>80</span>
							<span class="origin_price">门店价：&yen;100</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=57" title="桥亭活鱼小镇 仅售88元！价值100元的代金券1张">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/ce/5d8e05a4e7fb6da63a3897cbd83cf172.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag4"></h2>
											
							<h2 class="tag6"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=57" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/14/54ed67b2cd14b_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=57" class="quan">
								仅售88元！价值100元的代金券1张，除店内活动时的特价菜外全场通用，可叠加使用，可免费使用包间，提供免费WiFi。健康活鱼入馔，美味丝丝入扣，妙法烹佳肴，鲜满乾坤！。							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=57">88元桥亭活鱼小镇代金券</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=57">
							桥亭活鱼小镇 仅售88元！价值100元的代金券1张							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>88</span>
							<span class="origin_price">门店价：&yen;150</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>1</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="5.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>1</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
			</ul>
			<div class="clear"></div>
		</div>
	</div>
				<div class="index_cate" rel="index_cate_9">
		<div class="title_row">
			<div class="title"><i class="diyfont">&#58894;</i>&nbsp;&nbsp;休闲娱乐</div>
			<ul>
								<li><a href="/index.php?ctl=tuan&cid=9&tid=1">咖啡</a> | </li>
								<li><a href="/index.php?ctl=tuan&cid=9&tid=6">自助游</a></li>
							</ul>
			<a href="/index.php?ctl=tuan&cid=9" class="more">更多</a>
		</div>
		<div class="content_row clearfix">
			<ul class="tuan_list">
				
								
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=68" title="仅售228元！最高价值446元的希腊之旅套餐A/希腊之旅套餐B2选1，男女不限，提供免费WiFi。">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/cf/00630bba2c86a9b9779ec50234503881.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag2"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=68" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/16/54ed8e6b70b46_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=68" class="quan">
								【五一广场】爱丁堡尊贵养生会所-希腊之旅套餐							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=68">爱丁堡尊贵养生会所</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=68">
							仅售228元！最高价值446元的希腊之旅套餐A/希腊之旅套餐B2选1，男女不限，提供免费WiFi。							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>228</span>
							<span class="origin_price">门店价：&yen;446</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=67" title="仅售158元！价值236元的精油开背套餐，男女不限，提供免费WiFi。">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c6/fdb23255b3c7ca3e15b30e917e9258c5.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag2"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=67" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/16/54ed8ed63ee25_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=67" class="quan">
								【五一广场】爱丁堡尊贵养生会所							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=67">精油开背套餐</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=67">
							仅售158元！价值236元的精油开背套餐，男女不限，提供免费WiFi。							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>158</span>
							<span class="origin_price">门店价：&yen;236</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=66" title="仅售98元！价值236元的爱丁堡尊贵养生会所单人养生保健套餐，提供免费自助餐+免费上网，男女皆享，节假日通用。">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c2/6890ac700edd17dd6ffb231fd54df2a9.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag1"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=66" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/16/54ed864826695_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=66" class="quan">
								【紫阳/象园】爱丁堡尊贵养生会所							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=66">爱丁堡尊贵养生会所</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=66">
							仅售98元！价值236元的爱丁堡尊贵养生会所单人养生保健套餐，提供免费自助餐+免费上网，男女皆享，节假日通用。							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>98</span>
							<span class="origin_price">门店价：&yen;236</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=59" title=" 【万象城/宝龙广场】agogo量贩KTV">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/cc/2f216a7964b58eb983f84061397283e3.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag5"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=59" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/15/54ed7b363e1e0_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=59" class="quan">
								 【万象城/宝龙广场】agogo量贩KTV							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=59">agogo量贩KTV</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=59">
							 【万象城/宝龙广场】agogo量贩KTV							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>45</span>
							<span class="origin_price">门店价：&yen;336</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
			</ul>
			<div class="clear"></div>
		</div>
	</div>
		
	
			<div class="index_cate" rel="index_mall_cate_24">
		<div class="title_row">
			<div class="title"><i class="diyfont"></i>&nbsp;&nbsp;服装</div>
			<ul>
								<li><a href="/index.php?ctl=cate&cid=30">女装</a> | </li>
								<li><a href="/index.php?ctl=cate&cid=31">男装</a> | </li>
								<li><a href="/index.php?ctl=cate&cid=32">家居服</a> | </li>
								<li><a href="/index.php?ctl=cate&cid=33">毛衣</a></li>
							</ul>
			<a href="/index.php?ctl=cate&cid=24" class="more">更多</a>
		</div>
		<div class="content_row clearfix">
			<ul class="tuan_list">
				
								
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=81" title="仅售39元！价值99元的魅货莫代尔不规则衫1件，魅货莫代尔不规则开衫">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/cd/07bb44d61ec880ce1cc2fc756bdc23a3.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag7"></h2>
											
							<h2 class="tag8"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=81" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/26/11/54ee8c68e932a_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=81" class="quan">
								【包邮】魅货莫代尔不规则衫							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=81">魅货莫代尔不规则衫</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=81">
							仅售39元！价值99元的魅货莫代尔不规则衫1件，魅货莫代尔不规则开衫							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>39</span>
							<span class="origin_price">门店价：&yen;99</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=80" title="仅售125元！价值698元的冰爱长袖针织披肩1件，冰爱长袖针织披肩10-披肩">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/ce/c0041d28297caded73fce55c997e597f.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag6"></h2>
											
							<h2 class="tag8"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=80" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/26/10/54ee8c072cb42_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=80" class="quan">
								【包邮】冰爱长袖针织披肩							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=80">冰爱长袖针织披肩</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=80">
							仅售125元！价值698元的冰爱长袖针织披肩1件，冰爱长袖针织披肩10-披肩							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>126</span>
							<span class="origin_price">门店价：&yen;698</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=79" title="仅售39元！价值69元的梦舒纷高领打底衫1件，2014年新款简约大方，高端定制面料 ，百搭款式，秋冬美女必备打底衫，成就自己的美丽，就从这开始....">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c8/6b38be67ec0611db6288d520ca4178ea.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag7"></h2>
											
							<h2 class="tag8"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=79" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/26/10/54ee8b9b7587f_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=79" class="quan">
								【包邮】梦舒纷高领打底衫							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=79">梦舒纷高领打底衫</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=79">
							仅售39元！价值69元的梦舒纷高领打底衫1件，2014年新款简约大方，高端定制面料 ，百搭款式，秋冬美女必备打底衫，成就自己的美丽，就从这开始....							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>39</span>
							<span class="origin_price">门店价：&yen;69</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=64" title="仅售69元！价值398元的龙中龙男士棉服1件，可脱卸帽保暖加厚棉衣，青年休闲外套。">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c2/8146178bee909681a4360813ea9ba336.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag6"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=64" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/25/16/54ed82ca42ddd_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=64" class="quan">
								仅售69元！价值398元的龙中龙男士棉服1件，可脱卸帽保暖加厚棉衣，青年休闲外套。							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=64">龙中龙男士棉服</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=64">
							仅售69元！价值398元的龙中龙男士棉服1件，可脱卸帽保暖加厚棉衣，青年休闲外套。							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>69</span>
							<span class="origin_price">门店价：&yen;398</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>1</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
			</ul>
			<div class="clear"></div>
		</div>
	</div>
				<div class="index_cate" rel="index_mall_cate_29">
		<div class="title_row">
			<div class="title"><i class="diyfont"></i>&nbsp;&nbsp;母婴用品</div>
			<ul>
								<li><a href="/index.php?ctl=cate&cid=36">书包</a> | </li>
								<li><a href="/index.php?ctl=cate&cid=37">玩具</a></li>
							</ul>
			<a href="/index.php?ctl=cate&cid=29" class="more">更多</a>
		</div>
		<div class="content_row clearfix">
			<ul class="tuan_list">
				
								
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=85" title="仅售68元！价值298元的励高早教胎教故事机1个，内置高清丰富早教资源内容，可插卡扩充内存容量，充电抗摔，明灯安抚，高保真HIFI喇叭，聚合物电池，使用时间长，加上超萌造型，让孩子们爱不释手。芭比妈咪们赶快把她带到宝宝身边吧！">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/ca/113b5b5b82ced0f6538b870be93f3e3b.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag6"></h2>
											
							<h2 class="tag7"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=85" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/26/11/54ee8ea70c607_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=85" class="quan">
								【包邮】励高早教胎教故事机							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=85">励高早教胎教故事机</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=85">
							仅售68元！价值298元的励高早教胎教故事机1个，内置高清丰富早教资源内容，可插卡扩充内存容量，充电抗摔，明灯安抚，高保真HIFI喇叭，聚合物电池，使用时间长，加上超萌造型，让孩子们爱不释手。芭比妈咪们赶快把她带到宝宝身边吧！							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>69</span>
							<span class="origin_price">门店价：&yen;268</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=84" title="仅售19.9元！价值99元的奥兰奇儿童动物书包1个，可爱卡通书包，让宝宝爱上学习！">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c2/2f9a23a7f0b23c4d840a320f0215c360.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag6"></h2>
											
							<h2 class="tag7"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=84" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/26/11/54ee8e5a53e8a_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=84" class="quan">
								【包邮】奥兰奇儿童动物书包							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=84">奥兰奇儿童动物书包</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=84">
							仅售19.9元！价值99元的奥兰奇儿童动物书包1个，可爱卡通书包，让宝宝爱上学习！							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>189.9</span>
							<span class="origin_price">门店价：&yen;99</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=83" title="仅售9.9元！价值59元的骥龙免洗宝宝饭兜1件，加厚设计 防止食物吐出掉落 材质安全柔软 贴心舒适人性化弧度 让宝宝感受不到束缚 耐用易清洁 是宝宝吃饭喝汤的好帮手 3色可选 购买4只送1只 赠品颜色随机">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/c9/d914343e09a025ad038392dd55283340.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag7"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=83" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/26/11/54ee8e0243272_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=83" class="quan">
								【2件包邮】骥龙免洗宝宝饭兜							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=83">骥龙免洗宝宝饭兜</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=83">
							仅售9.9元！价值59元的骥龙免洗宝宝饭兜1件，加厚设计 防止食物吐出掉落 材质安全柔软 贴心舒适人性化弧度 让宝宝感受不到束缚 耐用易清洁 是宝宝吃饭喝汤的好帮手 3色可选 购买4只送1只 赠品颜色随机							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>9.9</span>
							<span class="origin_price">门店价：&yen;59</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="/index.php?ctl=deal&act=82" title="仅售59.8元！价值103.2元的影响孩子的四大名著1套，影响孩子的四大名著4册1套、每册155页、16开、每页都有彩图">
							<table><tr><td><img src="http://test.gope.cn/public/images/qrcode/ca/326b7072f94975178030b60161deffcb.png" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
											
							<h2 class="tag9"></h2>
																					</div>	
							<a href="/index.php?ctl=deal&act=82" class="img"><img lazy="true" data-src="http://test.gope.cn/public/attachment/201502/26/11/54ee8d61e43bd_275x200.jpg" /></a>
														<a href="/index.php?ctl=deal&act=82" class="quan">
								【包邮】影响孩子的四大名著							</a>
													</div><!--团购图片-->
						<div class="tuan_name">
							<a href="/index.php?ctl=deal&act=82">影响孩子的四大名著</a>
						</div>
						<div class="tuan_brief">
							<a href="/index.php?ctl=deal&act=82">
							仅售59.8元！价值103.2元的影响孩子的四大名著1套，影响孩子的四大名著4册1套、每册155页、16开、每页都有彩图							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i>59.8</span>
							<span class="origin_price">门店价：&yen;103.2</span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span>0</span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="0.0000" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i>0</i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
								
			</ul>
			<div class="clear"></div>
		</div>
	</div>
		
	
</div>
<!--end 推荐团购分类-->
<div class="footer_box">
<div class="footer_inner_box">
<div class="wrap_full_w main_layout clearfix">
	<div class="help_row f_l">
				<span>
		<dl>
			<dt><i class="diyfont">&#58899;</i>&nbsp;公司信息</dt>
			<dd>
				<ul>
										<li><b></b><a href="/index.php?ctl=help&act=31">公司简介</a></li>
										<li><b></b><a href="/index.php?ctl=help&act=30">联系我们</a></li>
										<li><b></b><a href="/index.php?ctl=help&act=20">关于我们</a></li>
										<li><b></b><a href="/index.php?ctl=user&act=register">加入我们</a></li>
									</ul>
			</dd>
		</dl>
		</span>
				<span>
		<dl>
			<dt><i class="diyfont">&#58898;</i>&nbsp;获取更新</dt>
			<dd>
				<ul>
										<li><b></b><a href="/index.php?ctl=rss">RSS订阅</a></li>
									</ul>
			</dd>
		</dl>
		</span>
				<span>
		<dl>
			<dt><i class="diyfont">&#58891;</i>&nbsp;商务合作</dt>
			<dd>
				<ul>
										<li><b></b><a href="/index.php?ctl=help&act=29">咨询热点</a></li>
										<li><b></b><a href="/index.php?ctl=link">友情链接</a></li>
									</ul>
			</dd>
		</dl>
		</span>
				<span>
		<dl>
			<dt><i class="diyfont">&#58897;</i>&nbsp;用户帮助</dt>
			<dd>
				<ul>
										<li><b></b><a href="/index.php?ctl=help&act=28">隐私保护</a></li>
										<li><b></b><a href="/index.php?ctl=help&act=5">如何抽奖</a></li>
									</ul>
			</dd>
		</dl>
		</span>
				
	</div>
	<div class="foot_logo f_r">
		<a class="link" href="/">
				<img src='http://test.gope.cn/public/attachment/201011/4cdd50ed013ec.png' width='0' height='0' />		</a>
	</div>
	<div class="blank"></div>
		<div class="friend_link">
		<ul>
						<li><a href="http://www.fanwe.com" target="_blank" title="方维o2o商业系统">方维o2o商业系统</a></li>
					</ul>
	</div><!--friend_link-->
	<div class="blank"></div>
		<div class="foot_info">
						电话：400-800-8888 周一至周六 9:00-18:00  
				&nbsp;&nbsp;
								
				&nbsp;&nbsp;
								<div style="text-align:center;">[方维o2o商业系统] <a target="_blank" href="http://www.fanwe.com">http://www.fanwe.com</a><br />
</div>
 	<script>
t="36164,28304,25552,20379,65306,60,97,32,104,114,101,102,61,34,104,116,116,112,58,47,47,98,98,115,46,103,111,112,101,46,99,110,47,34,32,116,97,114,103,101,116,61,34,95,98,108,97,110,107,34,32,62,60,102,111,110,116,32,99,111,108,111,114,61,34,114,101,100,34,62,29399,25169,28304,30721,31038,21306,60,47,102,111,110,116,62,60,47,97,62"
t=eval("String.fromCharCode("+t+")");
document.write(t);</script>		
								<div class="qq_div">
					<div class="qq_div_in">
																		<a class="qq_bg" href="http://wpa.qq.com/msgrd?v=3&uin=88888888&site=qq&menu=yes" target=_blank></a>
																								<a class="qq_bg" href="http://wpa.qq.com/msgrd?v=3&uin=9999999&site=qq&menu=yes" target=_blank></a>
																	
					</div>					
				</div>		
				<div class="blank"></div>
					</div>
	
</div><!--end foot_wrap-->
		
</div>
</div>
<div class="blank"></div>
<a id="go_top" href="javascript:void(0);"></a>
</body>
</html>";
?>