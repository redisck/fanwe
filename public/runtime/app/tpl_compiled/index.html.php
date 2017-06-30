<?php
$this->_var['pagecss'][] = $this->_var['TMPL_REAL']."/css/style.css";
$this->_var['pagecss'][] = $this->_var['TMPL_REAL']."/css/index.css";
$this->_var['pagecss'][] = $this->_var['TMPL_REAL']."/css/utils/weebox.css";
$this->_var['pagecss'][] = $this->_var['TMPL_REAL']."/css/utils/fanweUI.css";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/utils/jquery.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/utils/jquery.bgiframe.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/utils/jquery.weebox.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/utils/jquery.pngfix.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/utils/jquery.animateToClass.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/utils/jquery.timer.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/fanwe_utils/fanweUI.js";
$this->_var['cpagejs'][] = $this->_var['TMPL_REAL']."/js/fanwe_utils/fanweUI.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/script.js";
$this->_var['cpagejs'][] = $this->_var['TMPL_REAL']."/js/script.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/login_panel.js";
$this->_var['cpagejs'][] = $this->_var['TMPL_REAL']."/js/login_panel.js";
$this->_var['pagejs'][] = $this->_var['TMPL_REAL']."/js/page_js/index.js";
$this->_var['cpagejs'][] = $this->_var['TMPL_REAL']."/js/page_js/index.js";
?>
<?php echo $this->fetch('inc/header.html'); ?>

<?php if ($this->_var['index_cates']): ?>
<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>" id="flow_cate_outer">
<div id="flow_cate">
		<ul>
			<?php $_from = $this->_var['index_cates']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'index_cate');if (count($_from)):
    foreach ($_from AS $this->_var['index_cate']):
?>
			<?php if ($this->_var['index_cate']['deal_list']): ?>
			<li rel="index_cate_<?php echo $this->_var['index_cate']['id']; ?>" <?php if ($this->_var['index_cate']['iconcolor']): ?>bg="<?php echo $this->_var['index_cate']['iconcolor']; ?>"<?php endif; ?>>
				<?php if ($this->_var['index_cate']['iconfont']): ?>
				<i class="diyfont"><?php echo $this->_var['index_cate']['iconfont']; ?></i>
				<?php endif; ?>
				<font><?php echo $this->_var['index_cate']['name']; ?></font>
			</li>
			<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			<?php $_from = $this->_var['index_mall_cates']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'index_cate');if (count($_from)):
    foreach ($_from AS $this->_var['index_cate']):
?>
			<?php if ($this->_var['index_cate']['deal_list']): ?>
			<li rel="index_mall_cate_<?php echo $this->_var['index_cate']['id']; ?>" <?php if ($this->_var['index_cate']['iconcolor']): ?>bg="<?php echo $this->_var['index_cate']['iconcolor']; ?>"<?php endif; ?>>
				<?php if ($this->_var['index_cate']['iconfont']): ?>
				<i class="diyfont"><?php echo $this->_var['index_cate']['iconfont']; ?></i>
				<?php endif; ?>
				<font><?php echo $this->_var['index_cate']['name']; ?></font>
			</li>
			<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</ul>
</div>
</div>
<?php else: ?>
<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>" id="flow_cate_outer">
<div id="flow_cate">
</div>
</div>
<?php endif; ?>


<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>  clearfix">
	<div class="fix_cate_tree <?php if (! $this->_var['notice_list']): ?>border_bottom<?php endif; ?>" >
			<?php 
$k = array (
  'name' => 'load_cate_tree',
  'c' => '6',
  't' => $this->_var['cate_tree_type'],
);
echo $k['name']($k['c'],$k['t']);
?>
	</div>
	
	<div class="main_screen">
		<div class="blank"></div>
		<div class="main_roll f_l" id="main_roll">

		<ul class="roll">
		<li><adv adv_id="首页轮播广告2" /></li>
		<li><adv adv_id="首页轮播广告1" /></li>		
		</ul>
		
		</div>
		<div class="side_roll f_l" id="side_roll">
			<i class="t_left"></i>
			<i class="t_right"></i>
			<ul class="roll">
			<li><adv adv_id="首页小轮播广告1" /></li>
			<li><adv adv_id="首页小轮播广告2" /></li>
			</ul>
		</div>
		<div class="blank"></div>
		<div class="index_pick f_l  <?php if (! $this->_var['notice_list']): ?>border_bottom<?php endif; ?>">
			<span class="tuan_cate">
				<div class="tag_list">
				<h1><i class="iconfont">&#xe609;</i>&nbsp;热门团购</h1>
				
					<ul>
						<?php $_from = $this->_var['tuan_cate']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tc');if (count($_from)):
    foreach ($_from AS $this->_var['tc']):
?>
						<li><a href="<?php echo $this->_var['tc']['url']; ?>" <?php if ($this->_var['tc']['recommend'] == 1): ?>class="heavy"<?php endif; ?> ><?php echo $this->_var['tc']['name']; ?></a></li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					
					</ul>
				
				</div>
			</span>
			
			<span class="tuan_tag no_border">
				<div class="tag_list">
				<h1><i class="iconfont">&#xe611;</i>&nbsp;热门标签</h1>
				
					<ul>
						<?php $_from = $this->_var['tuan_tag']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tt');if (count($_from)):
    foreach ($_from AS $this->_var['tt']):
?>
						<li><a href="<?php echo $this->_var['tt']['url']; ?>" <?php if ($this->_var['tt']['is_recommend'] == 1): ?>class="heavy"<?php endif; ?> ><?php echo $this->_var['tt']['name']; ?></a></li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					</ul>
				
				</div>
			</span>
			<span class="tuan_area">
				<div class="tag_list">
				<h1><i class="iconfont">&#xe615;</i>&nbsp;全部区域</h1>
				
					<ul>
						<?php $_from = $this->_var['tuan_area']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ta');if (count($_from)):
    foreach ($_from AS $this->_var['ta']):
?>
						<li><a href="<?php echo $this->_var['ta']['url']; ?>"><?php echo $this->_var['ta']['name']; ?></a></li>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
						
					</ul>
				
				</div>
				
				<a href="javascript:void(0);" class="more">更多</a>
			</span>
		</div>
		<div class="index_mobile f_l">
			<ul>
				<li class="ios"><a href="javascript:void(0);"  down_url="<?php
echo parse_url_tag("u:index|ajax#app_download|"."t=ios".""); 
?>" ><i class="iconfont">&#xe614;</i>&nbsp;<em>IPhone</em> 下载</a></li>
				<li class="android"><a href="javascript:void(0);" down_url="<?php
echo parse_url_tag("u:index|ajax#app_download|"."t=android".""); 
?>"><i class="iconfont">&#xe613;</i>&nbsp;<em>Android</em> 下载</a></li>
				
			</ul>
		</div>
	</div>
</div>	
<?php if ($this->_var['notice_list']): ?>

<div class="notice_row <?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>">
	<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>">
			<div class="notice_board">
				<i class="iconfont f_l">&#xe618;</i>
				<ul>
					<?php $_from = $this->_var['notice_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'news');if (count($_from)):
    foreach ($_from AS $this->_var['news']):
?>
					<li><a href="<?php echo $this->_var['news']['url']; ?>"><?php echo $this->_var['news']['title']; ?></a></li>									
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				</ul>
				<a href="<?php
echo parse_url_tag("u:index|news|"."".""); 
?>" class="more f_l news_more">更多</a>
			</div>
	</div>	

</div>
<?php endif; ?>
<div class="blank"></div>
<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>">
<div class="f_l wrap_full">
	<?php if ($this->_var['store_list']): ?>
	<div class="index_rec_box" id="supplier_roll">
		<div class="title_row">
			<span><i class="iconfont">&#xe616;</i>&nbsp;名店推荐</span>
			<a href="<?php
echo parse_url_tag("u:index|stores|"."".""); 
?>" class="more">更多</a>
		</div>
		<div class="content_row">
			<?php if (count ( $this->_var['store_list'] ) > 4): ?>
			<i class="t_left"></i>
			<i class="t_right"></i>
			<?php endif; ?>
			<ul class="roll">
			
			<?php $_from = $this->_var['store_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'store');if (count($_from)):
    foreach ($_from AS $this->_var['store']):
?>
			<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="<?php echo $this->_var['store']['url']; ?>" title="<?php echo $this->_var['store']['name']; ?>">
					<table><tr><td><img src="<?php 
$k = array (
  'name' => 'gen_scan_qrcode',
  'v' => $this->_var['store']['url'],
);
echo $k['name']($k['v']);
?>" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<a href="<?php echo $this->_var['store']['url']; ?>"><img lazy="true" src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['store']['preview'],
  'w' => '220',
  'h' => '140',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" /></a>
				</div>
				<div class="name_row">
					<a href="<?php echo $this->_var['store']['url']; ?>" title="<?php echo $this->_var['store']['name']; ?>"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['store']['name'],
  'b' => '0',
  'e' => '15',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
				</div>
				<div class="extra_row">

					<div class="sale_review">							
			        	<span>
			        		<input class="ui-starbar" value="<?php echo $this->_var['store']['avg_point']; ?>" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b><?php echo $this->_var['store']['dp_count']; ?></b>人点评</span>						
					</div>
				</div>
			</li>
			<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			
			</ul>
		</div>
	</div>
	<div class="blank"></div>
	<?php endif; ?>
	<?php if ($this->_var['youhui_list']): ?>
	<div class="index_rec_box" id="youhui_roll">
		<div class="title_row">
			<span><i class="iconfont">&#xe609;</i>&nbsp;热门优惠券</span>
			<a href="<?php
echo parse_url_tag("u:index|youhuis|"."".""); 
?>" class="more">更多</a>
		</div>
		<div class="content_row">
			<?php if (count ( $this->_var['youhui_list'] ) > 4): ?>
			<i class="t_left"></i>
			<i class="t_right"></i>
			<?php endif; ?>
			<ul class="roll">
				
			<?php $_from = $this->_var['youhui_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'youhui');if (count($_from)):
    foreach ($_from AS $this->_var['youhui']):
?>
			<li>
				<!--qr码扫描区-->
				<div rel="qrcode" class="scanbox_index_rec">
				<a class="scan_outter" href="<?php echo $this->_var['youhui']['url']; ?>" title="<?php echo $this->_var['youhui']['name']; ?>">
					<table><tr><td><img src="<?php 
$k = array (
  'name' => 'gen_scan_qrcode',
  'v' => $this->_var['youhui']['url'],
);
echo $k['name']($k['v']);
?>" /> </td></tr></table>
					<div></div>
				</a>
				</div>
				<!--end qr码扫描区-->
				<div class="image_row">
					<div class="tags">					
					<?php $_from = $this->_var['youhui']['tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tag');if (count($_from)):
    foreach ($_from AS $this->_var['tag']):
?>				
							<h2 class="tag<?php echo $this->_var['tag']; ?>"></h2>
					<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					</div>	
					<a href="<?php echo $this->_var['youhui']['url']; ?>" title="<?php echo $this->_var['youhui']['name']; ?>"><img lazy="true"  src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['youhui']['icon'],
  'w' => '220',
  'h' => '140',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" /></a>
				</div>
				<div class="name_row">
					<a href="<?php echo $this->_var['youhui']['url']; ?>" title="<?php echo $this->_var['youhui']['name']; ?>"><?php 
$k = array (
  'name' => 'msubstr',
  'v' => $this->_var['youhui']['name'],
  'b' => '0',
  'e' => '10',
);
echo $k['name']($k['v'],$k['b'],$k['e']);
?></a>
				</div>
				<div class="extra_row">

					<div class="sale_review">							
			        	<span>
							<input class="ui-starbar" value="<?php echo $this->_var['youhui']['avg_point']; ?>" disabled="true"  />
						</span>
						</span>
						<span class="review_count"><b><?php echo $this->_var['youhui']['dp_count']; ?></b>人点评</span>						
					</div>
				</div>
			</li>
             <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>          
			</ul>
		</div>
	</div>
	<?php endif; ?>
</div><!--index_rec_layout_left-->
<div class="f_r wrap_s">
	<adv adv_id="名店右侧广告" />
	<div class="blank"></div>
	<adv adv_id="优惠右侧广告" />
</div><!--index_rec_layout_right-->
</div>
<div class="blank"></div>

<!--推荐的团购分类-->
<div class="<?php 
$k = array (
  'name' => 'load_wrap',
  't' => $this->_var['wrap_type'],
);
echo $k['name']($k['t']);
?>">

<?php if ($this->_var['index_cates']): ?>	
	<?php $_from = $this->_var['index_cates']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'index_cate');if (count($_from)):
    foreach ($_from AS $this->_var['index_cate']):
?>
	<?php if ($this->_var['index_cate']['deal_list']): ?>
	<div class="index_cate" rel="index_cate_<?php echo $this->_var['index_cate']['id']; ?>">
		<div class="title_row">
			<div class="title"><?php if ($this->_var['index_cate']['iconfont']): ?><i class="diyfont"><?php echo $this->_var['index_cate']['iconfont']; ?></i>&nbsp;&nbsp;<?php endif; ?><?php echo $this->_var['index_cate']['name']; ?></div>
			<ul>
				<?php $_from = $this->_var['index_cate']['deal_cate_type_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cate_type');$this->_foreach['type_loop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['type_loop']['total'] > 0):
    foreach ($_from AS $this->_var['cate_type']):
        $this->_foreach['type_loop']['iteration']++;
?>
				<li><a href="<?php
echo parse_url_tag("u:index|tuan|"."cid=".$this->_var['index_cate']['id']."&tid=".$this->_var['cate_type']['id']."".""); 
?>"><?php echo $this->_var['cate_type']['name']; ?></a><?php if (! ($this->_foreach['type_loop']['iteration'] == $this->_foreach['type_loop']['total'])): ?> | <?php endif; ?></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</ul>
			<a href="<?php
echo parse_url_tag("u:index|tuan|"."cid=".$this->_var['index_cate']['id']."".""); 
?>" class="more">更多</a>
		</div>
		<div class="content_row clearfix">
			<ul class="tuan_list">
				
				<?php $_from = $this->_var['index_cate']['deal_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'deal');if (count($_from)):
    foreach ($_from AS $this->_var['deal']):
?>
				
				<li>					
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="<?php echo $this->_var['deal']['url']; ?>" title="<?php echo $this->_var['deal']['name']; ?>">
							<table><tr><td><img src="<?php 
$k = array (
  'name' => 'gen_scan_qrcode',
  'v' => $this->_var['deal']['url'],
  's' => '4',
);
echo $k['name']($k['v'],$k['s']);
?>" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
							<?php $_from = $this->_var['deal']['deal_tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tag');if (count($_from)):
    foreach ($_from AS $this->_var['tag']):
?>				
							<h2 class="tag<?php echo $this->_var['tag']; ?>"></h2>
							<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
							<?php if ($this->_var['deal']['buyin_app'] == 1): ?>
							<h2 class="tag_buyinapp"></h2>
							<?php endif; ?>
							</div>	
							<a href="<?php echo $this->_var['deal']['url']; ?>" class="img"><img lazy="true" src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['deal']['icon'],
  'w' => '275',
  'h' => '200',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" /></a>
							<?php if ($this->_var['deal']['brief']): ?>
							<a href="<?php echo $this->_var['deal']['url']; ?>" class="quan">
								<?php echo $this->_var['deal']['brief']; ?>
							</a>
							<?php endif; ?>
						</div><!--团购图片-->
						<div class="tuan_name">
							<a href="<?php echo $this->_var['deal']['url']; ?>"><?php echo $this->_var['deal']['sub_name']; ?></a>
						</div>
						<div class="tuan_brief">
							<a href="<?php echo $this->_var['deal']['url']; ?>">
							<?php echo $this->_var['deal']['name']; ?>
							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i><?php 
$k = array (
  'name' => 'round',
  'v' => $this->_var['deal']['current_price'],
  'l' => '2',
);
echo $k['name']($k['v'],$k['l']);
?></span>
							<span class="origin_price">门店价：&yen;<?php 
$k = array (
  'name' => 'round',
  'v' => $this->_var['deal']['origin_price'],
  'l' => '2',
);
echo $k['name']($k['v'],$k['l']);
?></span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span><?php echo $this->_var['deal']['buy_count']; ?></span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="<?php echo $this->_var['deal']['avg_point']; ?>" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i><?php echo $this->_var['deal']['dp_count']; ?></i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				
			</ul>
			<div class="clear"></div>
		</div>
	</div>
	<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
<?php endif; ?>





<?php if ($this->_var['index_mall_cates']): ?>	
	<?php $_from = $this->_var['index_mall_cates']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'index_cate');if (count($_from)):
    foreach ($_from AS $this->_var['index_cate']):
?>
	<?php if ($this->_var['index_cate']['deal_list']): ?>
	<div class="index_cate" rel="index_mall_cate_<?php echo $this->_var['index_cate']['id']; ?>">
		<div class="title_row">
			<div class="title"><?php if ($this->_var['index_cate']['iconfont']): ?><i class="diyfont"><?php echo $this->_var['index_cate']['iconfont']; ?></i>&nbsp;&nbsp;<?php endif; ?><?php echo $this->_var['index_cate']['name']; ?></div>
			<ul>
				<?php $_from = $this->_var['index_cate']['deal_cate_type_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cate_type');$this->_foreach['type_loop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['type_loop']['total'] > 0):
    foreach ($_from AS $this->_var['cate_type']):
        $this->_foreach['type_loop']['iteration']++;
?>
				<li><a href="<?php
echo parse_url_tag("u:index|cate|"."cid=".$this->_var['cate_type']['id']."".""); 
?>"><?php echo $this->_var['cate_type']['name']; ?></a><?php if (! ($this->_foreach['type_loop']['iteration'] == $this->_foreach['type_loop']['total'])): ?> | <?php endif; ?></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			</ul>
			<a href="<?php
echo parse_url_tag("u:index|cate|"."cid=".$this->_var['index_cate']['id']."".""); 
?>" class="more">更多</a>
		</div>
		<div class="content_row clearfix">
			<ul class="tuan_list">
				
				<?php $_from = $this->_var['index_cate']['deal_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'deal');if (count($_from)):
    foreach ($_from AS $this->_var['deal']):
?>
				
				<li>
					<div class="tuan_item tuan_item_border">
						<!--qr码扫描区-->
						<div rel="qrcode" class="scanbox_deal_list">
						<a class="scan_outter" href="<?php echo $this->_var['deal']['url']; ?>" title="<?php echo $this->_var['deal']['name']; ?>">
							<table><tr><td><img src="<?php 
$k = array (
  'name' => 'gen_scan_qrcode',
  'v' => $this->_var['deal']['url'],
  's' => '4',
);
echo $k['name']($k['v'],$k['s']);
?>" /> </td></tr></table>
							<div></div>
						</a>
						</div>
						<!--end qr码扫描区-->
						<div class="tuan_img">
							<div class="tags">	
							<?php $_from = $this->_var['deal']['deal_tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tag');if (count($_from)):
    foreach ($_from AS $this->_var['tag']):
?>				
							<h2 class="tag<?php echo $this->_var['tag']; ?>"></h2>
							<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
							<?php if ($this->_var['deal']['buyin_app'] == 1): ?>
							<h2 class="tag_buyinapp"></h2>
							<?php endif; ?>
							</div>	
							<a href="<?php echo $this->_var['deal']['url']; ?>" class="img"><img lazy="true" src="<?php 
$k = array (
  'name' => 'get_spec_image',
  'v' => $this->_var['deal']['icon'],
  'w' => '275',
  'h' => '200',
  'g' => '1',
);
echo $k['name']($k['v'],$k['w'],$k['h'],$k['g']);
?>" /></a>
							<?php if ($this->_var['deal']['brief']): ?>
							<a href="<?php echo $this->_var['deal']['url']; ?>" class="quan">
								<?php echo $this->_var['deal']['brief']; ?>
							</a>
							<?php endif; ?>
						</div><!--团购图片-->
						<div class="tuan_name">
							<a href="<?php echo $this->_var['deal']['url']; ?>"><?php echo $this->_var['deal']['sub_name']; ?></a>
						</div>
						<div class="tuan_brief">
							<a href="<?php echo $this->_var['deal']['url']; ?>">
							<?php echo $this->_var['deal']['name']; ?>
							</a>
						</div>
						<div class="tuan_price">
							<span class="current_price"><i>&yen;</i><?php 
$k = array (
  'name' => 'round',
  'v' => $this->_var['deal']['current_price'],
  'l' => '2',
);
echo $k['name']($k['v'],$k['l']);
?></span>
							<span class="origin_price">门店价：&yen;<?php 
$k = array (
  'name' => 'round',
  'v' => $this->_var['deal']['origin_price'],
  'l' => '2',
);
echo $k['name']($k['v'],$k['l']);
?></span>
						</div>
						<div class="tuan_extra">
							<div class="sale_count">
								已售<span><?php echo $this->_var['deal']['buy_count']; ?></span>
							</div>
							<div class="sale_review">							
					        	<span>
					        	<input class="ui-starbar" value="<?php echo $this->_var['deal']['avg_point']; ?>" disabled="true"  />
								</span>
								</span>
								<span class="review_count"><i><?php echo $this->_var['deal']['dp_count']; ?></i>人点评</span>						
							</div>
						</div>
					</div><!--end tuan_item-->
				</li>
				
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
				
			</ul>
			<div class="clear"></div>
		</div>
	</div>
	<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
<?php endif; ?>


	

</div>
<!--end 推荐团购分类-->


<?php echo $this->fetch('inc/footer.html'); ?>