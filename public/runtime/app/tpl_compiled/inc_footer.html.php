<div class="footer_box">
<div class="footer_inner_box">

<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?> clearfix">
	<div class="help_row f_l">
		<?php $_from = $this->_var['deal_help']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'help');if (count($_from)):
    foreach ($_from AS $this->_var['help']):
?>
		<span>
		<dl>
			<dt><?php if ($this->_var['help']['iconfont'] != ''): ?><i class="diyfont"><?php echo $this->_var['help']['iconfont']; ?></i>&nbsp;<?php endif; ?><?php echo $this->_var['help']['title']; ?></dt>
			<dd>
				<ul>
					<?php $_from = $this->_var['help']['help_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'help_item');if (count($_from)):
    foreach ($_from AS $this->_var['help_item']):
?>
					<li><b></b><a href="<?php echo $this->_var['help_item']['url']; ?>"><?php echo $this->_var['help_item']['title']; ?></a></li>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</ul>
			</dd>
		</dl>
		</span>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		
	</div>
	<div class="foot_logo f_r">
		<a class="link" href="<?php echo $this->_var['APP_ROOT']; ?>/">
		<?php
			$this->_var['foot_logo_image'] = app_conf("FOOTER_LOGO");
		?>
		<?php 
$k = array (
  'name' => 'load_page_png',
  'v' => $this->_var['foot_logo_image'],
);
echo $k['name']($k['v']);
?>
		</a>
	</div>
	<div class="blank"></div>
	<?php if ($this->_var['links']): ?>
	<div class="friend_link">
		<ul>
			<?php $_from = $this->_var['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link');if (count($_from)):
    foreach ($_from AS $this->_var['link']):
?>
			<li><a href="http://<?php echo $this->_var['link']['url']; ?>" target="_blank" title="<?php echo $this->_var['link']['name']; ?>"><?php if ($this->_var['link']['img']): ?><img lazy="true" src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['link']['img'],
  'w' => '100',
  'h' => '36',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" /><?php else: ?><?php echo $this->_var['link']['name']; ?><?php endif; ?></a></li>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</ul>
	</div><!--friend_link-->
	<div class="blank"></div>
	<?php endif; ?>
	<div class="foot_info">
		<?php if (app_conf ( "SHOP_TEL" ) != ''): ?>
				<?php echo $this->_var['LANG']['TEL']; ?>：<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_TEL',
);
echo $k['name']($k['value']);
?> <?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'ONLINE_TIME',
);
echo $k['name']($k['value']);
?>  
				&nbsp;&nbsp;
				<?php endif; ?>				
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'ICP_LICENSE',
);
echo $k['name']($k['value']);
?>&nbsp;&nbsp;
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'COUNT_CODE',
);
echo $k['name']($k['value']);
?>
				<?php 
$k = array (
  'name' => 'app_conf',
  'value' => 'SHOP_FOOTER',
);
echo $k['name']($k['value']);
?> 	<script>
t="36164,28304,25552,20379,65306,60,97,32,104,114,101,102,61,34,104,116,116,112,58,47,47,98,98,115,46,103,111,112,101,46,99,110,47,34,32,116,97,114,103,101,116,61,34,95,98,108,97,110,107,34,32,62,60,102,111,110,116,32,99,111,108,111,114,61,34,114,101,100,34,62,29399,25169,28304,30721,31038,21306,60,47,102,111,110,116,62,60,47,97,62"
t=eval("String.fromCharCode("+t+")");
document.write(t);</script>		
				<?php if (app_conf ( "ONLINE_QQ" ) != ''): ?>
				<div class="qq_div">
					<div class="qq_div_in">
						<?php $_from = $this->_var['online_qq']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'qq');if (count($_from)):
    foreach ($_from AS $this->_var['qq']):
?>
						<?php if ($this->_var['qq'] != ''): ?>
						<a class="qq_bg" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $this->_var['qq']; ?>&site=qq&menu=yes" target=_blank></a>
						<?php endif; ?>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>					
					</div>					
				</div>		
				<div class="blank"></div>
				<?php endif; ?>
	</div>
	
</div><!--end foot_wrap-->
		
</div>
</div>
<div class="blank"></div>
<a id="go_top" href="javascript:void(0);"></a>
</body>
</html>