<span class="cart_drop_box" id="cart_drop_box">
	<a href="<?php
echo parse_url_tag("u:index|cart|"."".""); 
?>" title="购物车<?php echo $this->_var['cart_count']; ?>件"><i class="iconfont">&#xe612;</i> 购物车<span class="cart_count"><?php echo $this->_var['cart_count']; ?></span>件<i class="iconfont"></i></a>
</span>
<div id="head_cart_drop_box">
		<dl>
			<?php if (count ( $this->_var['head_cart_data']['cart_list'] ) > 0): ?>
				<?php $_from = $this->_var['head_cart_data']['cart_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'row');if (count($_from)):
    foreach ($_from AS $this->_var['row']):
?>
				<dd id="<?php echo $this->_var['row']['id']; ?>" class="deal-item">
					<a href="<?php echo $this->_var['row']['url']; ?>" title="<?php echo $this->_var['row']['name']; ?>" target="_blank"  class="deal-link" ><img class="deal-cover" src="<?php echo $this->_var['row']['icon']; ?>" width="80" height="50"></a>
					<h5 class="deal-title" id="<?php echo $this->_var['row']['id']; ?>">
						<a href="<?php echo $this->_var['row']['url']; ?>" title="<?php echo $this->_var['row']['name']; ?>" target="_blank" class="deal-link" ><?php echo $this->_var['row']['name']; ?></a>
					</h5>
					<p class="deal-price-w">
						<a href="javascript:void(0);" rel="<?php echo $this->_var['row']['id']; ?>" class="delete">删除</a>
						<em class="deal-price"><i>&yen;</i><?php 
$k = array (
  'name' => 'round',
  'v' => $this->_var['row']['unit_price'],
  'l' => '2',
);
echo $k['name']($k['v'],$k['l']);
?></em>
					</p>
				</dd>
				
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				<dd>
					<p class="check-my-cart" id="<?php echo $this->_var['row']['id']; ?>">
					<a href="<?php
echo parse_url_tag("u:index|cart|"."".""); 
?>" class="btn-small check_cart_btn "  id="<?php echo $this->_var['row']['id']; ?>">查看我的购物车</a></p>
				</dd>
			<?php else: ?>
				<div class="blank10"></div>
				<p class="check-my-cart" id="<?php echo $this->_var['row']['id']; ?>">
					
					<span>暂时没有商品</span>
				</p>
			<?php endif; ?>
			
		</dl>
	</div>