<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" />

<?php 
$k = array (
  'name' => 'load_compatible',
);
echo $k['name']();
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php if ($this->_var['page_title']): ?><?php echo $this->_var['page_title']; ?> - <?php endif; ?><?php echo $this->_var['site_seo']['title']; ?></title>
<meta name="keywords" content="<?php if ($this->_var['page_keyword']): ?><?php echo $this->_var['page_keyword']; ?><?php endif; ?> <?php echo $this->_var['site_seo']['keyword']; ?>" />
<meta name="description" content="<?php if ($this->_var['page_description']): ?><?php echo $this->_var['page_description']; ?><?php endif; ?> <?php echo $this->_var['site_seo']['description']; ?>" />
<script type="text/javascript">
var APP_ROOT = '<?php echo $this->_var['APP_ROOT']; ?>';
var CART_URL = '<?php
echo parse_url_tag("u:index|cart|"."".""); 
?>';
var CART_CHECK_URL = '<?php
echo parse_url_tag("u:index|cart#check|"."".""); 
?>';
<?php if (app_conf ( "APP_MSG_SENDER_OPEN" ) == 1): ?>
var send_span = <?php 
$k = array (
  'name' => 'app_conf',
  'v' => 'SEND_SPAN',
);
echo $k['name']($k['v']);
?>000;
var IS_RUN_CRON = 1;
var DEAL_MSG_URL = '<?php
echo parse_url_tag("u:index|cron#deal_msg_list|"."".""); 
?>';
<?php endif; ?>
var AJAX_LOGIN_URL	= '<?php
echo parse_url_tag("u:index|user#ajax_login|"."".""); 
?>';
var AJAX_URL	= '<?php
echo parse_url_tag("u:index|ajax|"."".""); 
?>';
var CITY_COUNT	= <?php echo $this->_var['city_count']; ?>;

//关于图片上传的定义
var LOADER_IMG = '<?php echo $this->_var['TMPL']; ?>/images/loader_img.gif';
var UPLOAD_SWF = '<?php echo $this->_var['TMPL']; ?>/js/utils/Moxie.swf';
var UPLOAD_XAP = '<?php echo $this->_var['TMPL']; ?>/js/utils/Moxie.xap';
var MAX_IMAGE_SIZE = '<?php 
$k = array (
  'name' => 'app_conf',
  'v' => 'MAX_IMAGE_SIZE',
);
echo $k['name']($k['v']);
?>';
var ALLOW_IMAGE_EXT = '<?php 
$k = array (
  'name' => 'app_conf',
  'v' => 'ALLOW_IMAGE_EXT',
);
echo $k['name']($k['v']);
?>';
var UPLOAD_URL = '<?php
echo parse_url_tag("u:index|file#upload|"."".""); 
?>';
var QRCODE_ON = '<?php 
$k = array (
  'name' => 'app_conf',
  'v' => 'QRCODE_ON',
);
echo $k['name']($k['v']);
?>';
</script>
<?php
//前台队列功能开启
if(app_conf("APP_MSG_SENDER_OPEN")==1)
{
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/msg_sender.js";
$this->_var['cpagejs'][] = $this->_var['TMPL_REAL']."/js/msg_sender.js";
}
?>
<script type="text/javascript" src="<?php echo $this->_var['APP_ROOT']; ?>/public/runtime/app/lang.js"></script>
<link rel="stylesheet" type="text/css" href="<?php 
$k = array (
  'name' => 'parse_css',
  'v' => $this->_var['pagecss'],
);
echo $k['name']($k['v']);
?>" />
<script type="text/javascript" src="<?php 
$k = array (
  'name' => 'parse_script',
  'v' => $this->_var['pagejs'],
  'c' => $this->_var['cpagejs'],
);
echo $k['name']($k['v'],$k['c']);
?>"></script>

</head>
<body>
<img src="<?php echo $this->_var['TMPL']; ?>/images/loader_img.gif" style="display:none;" /><!--延时加载的替代图片生成-->
<?php if (count ( $this->_var['city_list'] ) > 1): ?>
<div class="city_list">
	<div class="city_list_box">
		<?php $_from = $this->_var['city_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
		<a href="<?php echo $this->_var['item']['url']; ?>" class="city_item" ><?php echo $this->_var['item']['name']; ?></a>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
</div>
<?php endif; ?>
<div class="top_nav">
	<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>">
		<span class="f_l">欢迎来到<?php 
$k = array (
  'name' => 'app_conf',
  'v' => 'SHOP_TITLE',
);
echo $k['name']($k['v']);
?></span>	
		<span class="f_r">
			<ul class="head_tip">
				<li class="user_tip"><a href="<?php
echo parse_url_tag("u:biz|index|"."".""); 
?>" target="_blank">商户中心</a></li>
				<li class="cart_tip" id="cart_tip"><?php 
$k = array (
  'name' => 'load_cart_count',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?></li>
				<li class="user_tip" id="history_tip"><?php 
$k = array (
  'name' => 'load_head_history',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?></li>
				<li class="user_tip" id="head_user_tip"><?php 
$k = array (
  'name' => 'load_user_tip',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?></li>
			</ul>
		</span>
	</div>
</div><!--顶部横栏-->
<div class="blank15"></div>
<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?> head_main">
	<div class="logo f_l">
	<a class="link" href="<?php echo $this->_var['APP_ROOT']; ?>/">
		<?php
			$this->_var['logo_image'] = app_conf("SHOP_LOGO");
		?>
		<?php 
$k = array (
  'name' => 'load_page_png',
  'v' => $this->_var['logo_image'],
);
echo $k['name']($k['v']);
?>
	</a>
	</div>
	<div class="city f_l">
		<?php if (count ( $this->_var['city_list'] ) > 1): ?>
		<a class="city_name" href="javascript:void(0);"  jump="<?php
echo parse_url_tag("u:index|city|"."".""); 
?>"><?php 
$k = array (
  'name' => 'load_city_name',
);
echo $this->_hash . $k['name'] . '|' . base64_encode(serialize($k)) . $this->_hash;
?><?php if ($this->_var['city_count'] < 20): ?>&nbsp;<i></i><?php endif; ?></a>
		<a href="javascript:void(0);"  jump="<?php
echo parse_url_tag("u:index|city|"."".""); 
?>" class="city_switch f_l">切换城市</a>
		<?php endif; ?>
	</div>
	<div class="search f_r">
		<div class="top_search">
			<form action="<?php
echo parse_url_tag("u:index|search|"."".""); 
?>" name="search_form" method=post >
			<select name="search_type" class="ui-select search_type f_l">
				<option value="1" <?php if ($this->_var['search_type'] == 1): ?>selected="selected"<?php endif; ?>>搜团购</option>
				<option value="2" <?php if ($this->_var['search_type'] == 2): ?>selected="selected"<?php endif; ?>>搜优惠</option>
				<option value="3" <?php if ($this->_var['search_type'] == 3): ?>selected="selected"<?php endif; ?>>搜活动</option>
				<option value="4" <?php if ($this->_var['search_type'] == 4): ?>selected="selected"<?php endif; ?>>搜商家</option>				
				<option value="5" <?php if ($this->_var['search_type'] == 5): ?>selected="selected"<?php endif; ?>>搜商品</option>
				<option value="6" <?php if ($this->_var['search_type'] == 6): ?>selected="selected"<?php endif; ?>>搜分享</option>
			</select>
			<input type="text" name="search_keyword" class="ui-textbox search_keyword f_l" holder="请输入您要搜索的关键词" value="<?php echo $this->_var['kw']; ?>" />
			<button class="ui-button f_l" rel="search_btn" type="submit">搜索</button>
			</form>
		</div>
		<ul class="search_hot_keyword">
			<?php $_from = $this->_var['hot_kw']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'kw_0_43706400_1451962381');if (count($_from)):
    foreach ($_from AS $this->_var['kw_0_43706400_1451962381']):
?>
			<li><a href="<?php echo $this->_var['kw_0_43706400_1451962381']['url']; ?>"><?php echo $this->_var['kw_0_43706400_1451962381']['txt']; ?></a></li>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</ul>
	</div>
</div><!--logo与头部搜索-->
<div class="blank15"></div>
<div class="nav_bar">
	<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>">
		<?php if (! $this->_var['no_nav']): ?>
		<div class="drop_nav" id="drop_nav" ref="<?php echo $this->_var['drop_nav']; ?>">
			<span class="drop_title">全部分类<i></i></span>
			<div class="drop_box">
				<?php 
$k = array (
  'name' => 'load_cate_tree',
  'c' => '0',
  't' => $this->_var['cate_tree_type'],
);
echo $k['name']($k['c'],$k['t']);
?>
			</div>
		</div><!--下拉菜单-->
		<?php endif; ?>
		<div class="main_nav">
			<ul>
				<?php $_from = $this->_var['nav_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'nav_item');if (count($_from)):
    foreach ($_from AS $this->_var['nav_item']):
?>
				<li <?php if ($this->_var['nav_item']['current'] == 1): ?>class="current"<?php endif; ?>><a href="<?php echo $this->_var['nav_item']['url']; ?>" <?php if ($this->_var['nav_item']['blank']): ?>target="_blank"<?php endif; ?>><?php echo $this->_var['nav_item']['name']; ?></a></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				
			</ul>
		</div>
	</div>
</div>	
<?php if ($this->_var['site_nav']): ?>
<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>">
	<div class="blank"></div>
	<div class="site_nav">
		<?php $_from = $this->_var['site_nav']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'nav');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['nav']):
?>
		<?php if ($this->_var['key'] > 0): ?>&nbsp;&nbsp;»&nbsp;&nbsp;<?php endif; ?><a href="<?php echo $this->_var['nav']['url']; ?>" title="<?php echo $this->_var['nav']['name']; ?>"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['nav']['name'],
);
echo $k['name']($k['v']);
?></a>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>	
	
</div>
<?php endif; ?>