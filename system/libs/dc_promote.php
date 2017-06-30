<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

//购物促销接口
interface dc_promote{
/**
 * user_id 用户ID
 * location_id 门店ID
 * payment_id  付款方式，0为在线支付，1为货到付款
 * payment        //支付ID
 * account_money  //支付余额
 * all_account_money  //是否全额支付
 * ecvsn  //代金券帐号
 * ecvpassword  //代金券密码
 * old_result 前一个促销规则得到的结果
 * 
 * 返回 array(
		'total_price'	=>	$total_price,	商品总价
		'pay_price'		=>	$pay_price,     支付费用
		'pay_total_price'		=>	$total_price+$delivery_fee+$payment_fee+$package_fee,  应付总费用
		'delivery_fee'	=>	$delivery_fee,  运费
		'payment_fee'	=>	$payment_fee,   支付手续费
		'package_fee'	=>	$package_fee,   打包费
		'account_money'	=>	$account_money, 余额支付	
		'ecv_money'		=>	$ecv_money,		代金券金额
		)
		
		

		
 */
function dc_count_buy_total($location_id,$user_id,$payment_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$result,$old_result);
}
?>