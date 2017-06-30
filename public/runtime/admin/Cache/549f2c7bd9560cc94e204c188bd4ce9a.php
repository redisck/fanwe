<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title><?php echo conf("APP_NAME");?><?php echo l("ADMIN_PLATFORM");?></title>
<script type="text/javascript" src="__ROOT__/public/runtime/admin/lang.js"></script>
<script type="text/javascript">
	var version = '<?php echo app_conf("DB_VERSION");?>';
	var app_type = '<?php echo ($apptype); ?>';
	var ofc_swf = '__TMPL__Common/js/open-flash-chart.swf';
	var sale_line_data_url = '<?php echo urlencode(u("Ofc/sale_line"));?>';
	var sale_refund_data_url = '<?php echo urlencode(u("Ofc/sale_refund"));?>';
</script>
<link rel="stylesheet" type="text/css" href="__TMPL__Common/style/style.css" />
<link rel="stylesheet" type="text/css" href="__TMPL__Common/style/main.css" />
<script type="text/javascript" src="__TMPL__Common/js/jquery.js"></script>
<script type="text/javascript" src="__TMPL__Common/js/swfobject.js"></script>
<script type="text/javascript" src="__TMPL__Common/js/script.js"></script>
<script type="text/javascript" src="__TMPL__Common/js/main.js"></script>
</head>

<body>
	<div class="main">
	<div class="main_title"><?php echo conf("APP_NAME");?><?php echo l("ADMIN_PLATFORM");?> <?php echo L("HOME");?>	</div>
	<div class="notify_box">
		<table>
			<tr>
			<td class="version_box">
				<table>
					<tr><td>
						当前版本：V3.07.4946<?php  ?><br />
						正版授权</div>
						<!--<div>授权号：S201511251588</div>-->
					</td></tr>
				</table>
			</td>
			<td class="order_box">
				<table>
					<tr><td>
						订单累计成交额 <?php echo (format_price($income_order)); ?><br />
						订单累计退款额 <?php echo (format_price($refund_money)); ?><br />
						<?php if($dealing_order > 0): ?>待处理订单共计 <?php echo ($dealing_order); ?> <a href="<?php echo u("DealOrder/deal_index",array("order_status"=>0));?>">【去处理】</a> <br /><?php endif; ?>
						<?php if($refund_order > 0): ?>退款申请数 <?php echo ($refund_order); ?> <a href="<?php echo u("DealOrder/deal_index",array("refund_status"=>1));?>">【去处理】</a> <br /><?php endif; ?>
						<?php if($no_arrival_order > 0): ?>维权订单数 <?php echo ($no_arrival_order); ?> <a href="<?php echo u("DealOrder/deal_index",array("is_refuse_delivery"=>1));?>">【去处理】</a><?php endif; ?>
					</td></tr>
				</table>
			</td><!--order_box 订单提醒-->
			<td class="user_box">
				<table>
					<tr><td>
						平台会员总数 <?php echo ($user_count); ?><br />
						<?php if($income_incharge > 0): ?>预付款总金额 <?php echo (format_price($income_incharge)); ?><br /><?php endif; ?>
						<?php if($withdraw > 0): ?>共有 <?php echo ($withdraw); ?> 笔提现申请 <a href="<?php echo u("User/withdrawal_index",array("is_paid"=>0));?>">【去处理】</a><?php endif; ?>
					</td></tr>
				</table>
			</td><!--user_box 会员提醒-->
			<td class="tuan_box">
				<table>
					<tr><td>
						上线的团购数 <?php echo ($tuan_count); ?><br />			
						<?php if($tuan_dp_count > 0): ?>共有 <?php echo ($tuan_dp_count); ?> 则购物点评<br /><?php endif; ?>			
						<?php if($tuan_dp_wait_count > 0): ?><?php echo ($tuan_dp_wait_count); ?> 则购物点评未回复 <a href="<?php echo u("SupplierLocationDp/index",array("type"=>"dealdp","wait_reply"=>1));?>">【去处理】</a> <br /><?php endif; ?>
						<?php if($tuan_submit_count > 0): ?><?php echo ($tuan_submit_count); ?>条商户提交团购未审核  <a href="<?php echo u("Deal/tuan_publish",array("admin_check_status"=>0));?>">【去处理】</a> <br /><?php endif; ?>
					</td></tr>
				</table>
			</td><!--tuan_box 团购提醒-->
			</tr>
			
			<tr>
			<td class="shop_box">
				<table>
					<tr><td>
						上线的商品数 <?php echo ($shop_count); ?><br />
						<?php if($shop_dp_count > 0): ?>共有 <?php echo ($shop_dp_count); ?> 则购物点评<br /><?php endif; ?>			
						<?php if($shop_dp_wait_count > 0): ?><?php echo ($shop_dp_wait_count); ?> 则购物点评未回复 <a href="<?php echo u("SupplierLocationDp/index",array("type"=>"dealdp","wait_reply"=>1));?>">【去处理】</a> <br /><?php endif; ?>
						<?php if($shop_submit_count > 0): ?><?php echo ($shop_submit_count); ?>条商户提交商品未审核  <a href="<?php echo u("Deal/shop_publish",array("admin_check_status"=>0));?>">【去处理】</a> <br /><?php endif; ?>
					</td></tr>
				</table>
			</td><!--shop_box 商城提醒-->
			<td class="youhui_box">
				<table>
					<tr><td>
						上线的优惠券数 <?php echo ($youhui_count); ?><br />
						<?php if($youhui_dp_count > 0): ?>共有 <?php echo ($youhui_dp_count); ?> 则点评<br /><?php endif; ?>			
						<?php if($youhui_dp_wait_count > 0): ?><?php echo ($youhui_dp_wait_count); ?> 则点评未回复 <a href="<?php echo u("SupplierLocationDp/index",array("type"=>"youhuidp","wait_reply"=>1));?>">【去处理】</a> <br /><?php endif; ?>
						<?php if($youhui_submit_count > 0): ?><?php echo ($youhui_submit_count); ?>条商户提交优惠券未审核  <a href="<?php echo u("Youhui/publish",array("admin_check_status"=>0));?>">【去处理】</a> <br /><?php endif; ?>
					</td></tr>
				</table>
			</td><!--youhui_box 优惠券提醒-->
			<td class="event_box">
				<table>
					<tr><td>
						上线的活动数 <?php echo ($event_count); ?><br />
						<?php if($event_dp_count > 0): ?>共有 <?php echo ($event_dp_count); ?> 则点评<br /><?php endif; ?>			
						<?php if($event_dp_wait_count > 0): ?><?php echo ($event_dp_wait_count); ?> 则点评未回复 <a href="<?php echo u("SupplierLocationDp/index",array("type"=>"eventdp","wait_reply"=>1));?>">【去处理】</a> <br /><?php endif; ?>
						<?php if($event_submit_count > 0): ?><?php echo ($event_submit_count); ?>条商户提交活动未审核  <a href="<?php echo u("Event/publish",array("admin_check_status"=>0));?>">【去处理】</a> <br /><?php endif; ?>
					</td></tr>
				</table>
			</td><!--event_box 活动提醒-->
			<td class="store_box">
				<table>
					<tr><td>
						平台共入驻 <?php echo ($supplier_count); ?> 家商户<br />
						共计 <?php echo ($store_count); ?> 家门店 <br />
						<?php if($supplier_submit_count > 0): ?><?php echo ($supplier_submit_count); ?> 条商户入驻申请 <a href="<?php echo u("SupplierSubmit/index",array("is_publish"=>0));?>">【去处理】</a> <br /><?php endif; ?>
						<?php if($store_dp_count > 0): ?>共有 <?php echo ($store_dp_count); ?> 则点评<br /><?php endif; ?>			
						<?php if($store_dp_wait_count > 0): ?><?php echo ($store_dp_wait_count); ?> 则点评未回复 <a href="<?php echo u("SupplierLocationDp/index",array("wait_reply"=>1));?>">【去处理】</a> <br /><?php endif; ?>
						<?php if($location_submit_count > 0): ?><?php echo ($location_submit_count); ?>条商户提交门店未审核  <a href="<?php echo u("SupplierLocation/publish",array("admin_check_status"=>0));?>">【去处理】</a> <br /><?php endif; ?>						
						<?php if($sp_withdraw_count > 0): ?><?php echo ($sp_withdraw_count); ?>条商户提现未审核  <a href="<?php echo u("Supplier/charge_index",array("status"=>0));?>">【去处理】</a> <br /><?php endif; ?>
					</td></tr>
				</table>
			</td><!--store_box 门店提醒-->
			</tr>
		</table>
	</div>	
	<div class="blank5"></div>
	<div class="blank5"></div>
	<div class="blank5"></div>
	<div class="blank5"></div>
	<div class="main_title">最近30天运营数据</div>
	<table width=100%>
		
		<tr>
			<td width=10>&nbsp;</td>
			<td width=50%>
				<div id="sale_line_data_chart"></div>
			</td>
			<td width=10>&nbsp;</td>
			<td width=50%>
				<div id="sale_refund_data_chart"></div>
			</td>
			<td width=10>&nbsp;</td>
		</tr>
	</table>
	</div>
</body>
</html>