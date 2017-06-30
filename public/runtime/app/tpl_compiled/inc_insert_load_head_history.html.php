	<span class="head_history" id="head_history">
		最近浏览<i class="iconfont"></i>
	</span>
	<div id="head_history_drop_box">

		<dl>
			<?php if ($this->_var['history_list']): ?>
				<?php $_from = $this->_var['history_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');if (count($_from)):
    foreach ($_from AS $this->_var['row']):
?>
					<dd id="<?php echo $this->_var['row']['id']; ?>" class="deal-item">
						<a href="<?php echo $this->_var['row']['url']; ?>" title="<?php echo $this->_var['row']['name']; ?>" target="_blank"  class="deal-link" ><img class="deal-cover" src="<?php echo $this->_var['row']['icon']; ?>" width="80" height="50"></a>
						<h5 class="deal-title" id="<?php echo $this->_var['row']['id']; ?>">
							<a href="<?php echo $this->_var['row']['url']; ?>" title="<?php echo $this->_var['row']['name']; ?>" target="_blank" class="deal-link"  id="" ><?php echo $this->_var['row']['name']; ?></a>
						</h5>
						<p class="deal-price-w">
							
							<em class="deal-price"><i>&yen;</i><?php 
$k = array (
  'name' => 'round',
  'v' => $this->_var['row']['current_price'],
  'l' => '2',
);
echo $k['name']($k['v'],$k['l']);
?></em>
						</p>
					</dd>
					
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				<dd>
					<p class="clear-my-history">
					<a href="javascript:void(0);" class="btn-small check_cart_btn clear_history_head">清空浏览记录</a></p>
				</dd>
			<?php else: ?>
			<div class="blank10"></div>
				<p class="clear-my-history" id="<?php echo $this->_var['row']['id']; ?>">
					
					<span>你还没有浏览商品</span>
				</p>
			<?php endif; ?>
		</dl>
	
	</div>
<script type="text/javascript">
		init_drop_head_history();
</script>