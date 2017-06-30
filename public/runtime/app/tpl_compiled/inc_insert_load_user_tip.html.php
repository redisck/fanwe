<?php if ($this->_var['user_info']): ?>
	<span class="user_name" id="user_drop">
		欢迎您，<?php echo $this->_var['user_info']['user_name']; ?>
		<?php if ($this->_var['user_info']['level'] > 0): ?>
		<span title="<?php echo $this->_var['user_info']['level_name']; ?>" class="level_bg level_<?php echo $this->_var['user_info']['level']; ?>"></span> 
		<?php endif; ?>
		<i class="iconfont">&#xe610;</i>
	</span>
	<div id="user_drop_box">
		<dl>
			<dd><a href="<?php
echo parse_url_tag("u:index|uc_home|"."".""); 
?>">个人主页</a></dd>
			<dd class="group"><a href="<?php
echo parse_url_tag("u:index|uc_order|"."".""); 
?>">我的订单</a></dd>
			<dd><a href="<?php
echo parse_url_tag("u:index|uc_coupon|"."".""); 
?>">我的团购券</a></dd>
			<dd><a href="<?php
echo parse_url_tag("u:index|uc_youhui|"."".""); 
?>">我的优惠券</a></dd>
			<dd class="group"><a href="<?php
echo parse_url_tag("u:index|uc_myinfo|"."".""); 
?>">账户中心</a></dd>
			<dd><a href="<?php
echo parse_url_tag("u:index|uc_account|"."".""); 
?>">账户设置</a></dd>
			<dd class="group"><a href="<?php
echo parse_url_tag("u:index|user#loginout|"."".""); 
?>">退出</a></dd>
		</dl>
	</div>

	
	<?php if ($this->_var['user_info']['msg_count'] > 0): ?>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="<?php
echo parse_url_tag("u:index|uc_msg|"."".""); 
?>" class="msg_count" title="您共有<?php echo $this->_var['user_info']['msg_count']; ?>条新信息"><span><i class="iconfont">&#xe62c;</i> 消息 <em><?php echo $this->_var['user_info']['msg_count']; ?></em></span></a>
	<?php else: ?>
	<em class="space_span"></em>
	<?php endif; ?>
	<script type="text/javascript">		
			init_drop_user();
			<?php if ($this->_var['signin_result']): ?>
			show_signin_message(<?php echo $this->_var['signin_result']; ?>);
			<?php endif; ?>
	</script>
<?php else: ?>
	<span class="login_tip">请先 [<a href="<?php
echo parse_url_tag("u:index|user#login|"."".""); 
?>" title="登录" id="pop_login">登录</a>]<?php if ($this->_var['wx_login']): ?> / [<a href="javascript:void(0);" rel="<?php
echo parse_url_tag("u:index|user#wx_login|"."".""); 
?>" title="微信登录" id="wx_login">微信登录</a>]<?php endif; ?> 或 [<a href="<?php
echo parse_url_tag("u:index|user#register|"."".""); 
?>" title="注册">注册</a>]</span>
<?php endif; ?>