<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

$lang = array(
	'name'	=>	'在线支付优惠',
	'description'	=>	'在线支付下单满XX减XX元，活动期间每天XX单',
	'discount_limit'	=>	'下单满',
	'discount_amount'	=>	'立减',
	'daily_limit'=>'单&nbsp;[0为不限制]',
);

$config = array(
	'discount_limit'	=>	'',
	'discount_amount' =>	'',
	'daily_limit'=>'',
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'PayOnlineDiscount';

    /* 名称 */
    $module['name']    = $lang['name'];
    
    /* 描述 */
    $module['description']    = $lang['description'];

	$module['config'] = $config;
    $module['lang'] = $lang;
    return $module;
}


require_once(APP_ROOT_PATH.'system/libs/dc_promote.php');
class PayOnlineDiscount_promote implements dc_promote {
	public function dc_count_buy_total(
										$location_id,
										$user_id,
										$payment_id,
										$payment,
										$account_money,
										$all_account_money,
										$ecvsn,
										$ecvpassword,
										$result,
										$old_result){

		//取出接口配置
		$promote_obj = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_promote where class_name='PayOnlineDiscount'");
		$promote_cfg = unserialize($promote_obj['config']);
		//每日限制几单
		$begin_time=to_timespan(to_date(NOW_TIME,"Y-m-d"));
		$end_time=$begin_time+3600*24-1;
		
		$today_order_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_order where user_id=".$user_id ." and create_time > ".$begin_time." and create_time < ".$end_time." and pay_status=1 and payment_id=0 and promote_amount > 0 and is_cancel=0 and refund_status=0");
		//$payment_id=0为在线支付，不在线支付的，不享受该优惠
		if($payment_id!=0 || ($promote_cfg['daily_limit'] <= $today_order_count && $promote_cfg['daily_limit'] > 0)){
			return $old_result;
		}else{
		

			arsort($promote_cfg['discount_limit']);
				
			foreach($promote_cfg['discount_limit'] as $k=>$v){
				if($old_result['total_price']>=$v){
					$key=$k;
					break;
				}
			}
			$discount_amount=0;
			if(isset($key)){
			$discount_amount=$promote_cfg['discount_amount'][$key];
			}
			
			if($discount_amount>0){
						$old_result['pay_price'] = $old_result['pay_total_price'] - $old_result['paid_account_money'] - $old_result['paid_ecv_money'] - $old_result['paid_promote_amount'];	
						$tmp_pay_price_xx=$old_result['pay_price'];
						$old_result['pay_price'] = $old_result['pay_price']- $discount_amount ;	
			

						if($old_result['pay_price'] >= 0){
							
							//同步计算余额支付
							$tmp_pay_price=$old_result['pay_price'];
							$old_result['pay_price'] = $old_result['pay_price']  - $old_result['ecv_money'];
							if($old_result['pay_price'] > 0){
					
								//余额支付
								$user_money = $GLOBALS['db']->getOne("select money from ".DB_PREFIX."user where id = ".$user_id);
								if($all_account_money == 1)
								{
									if($old_result['pay_price'] > $user_money ){
										$account_money=$user_money;
									}else{
										$account_money=$old_result['pay_price'];
									}
								}else{
									if($old_result['pay_price'] > $account_money ){
										$account_money=$account_money;
									}else{
										$account_money=$old_result['pay_price'];
									}
									
								}
								
								
						
								
								$old_result['account_money']= $account_money;
								 $old_result['pay_price'] = $old_result['pay_price'] - $old_result['account_money'];
							}else{
								$account_money=0;
								$old_result['account_money']= $account_money;
								$old_result['pay_price']=0;
								$old_result['ecv_money']=$tmp_pay_price;
								
							}

							
						}else{
							
							$old_result['account_money']=0;
							$discount_amount=$tmp_pay_price_xx;
							$old_result['ecv_money']=0;
							$old_result['pay_price']=0;
						}
						
				
						
			//同步计算余额支付
			if($discount_amount > 0){
			$promote=array();
			
			$promote['name'] = '在线支付优惠';
			$promote['class_name'] = $promote_obj['class_name'];
			$promote['discount_amount']=$discount_amount;
			$promote['promote_description'] = $promote_obj['description'];
			
			$old_result['dc_promote'][] = $promote;
			
			}
			return $old_result;

			
			}else{
			
			return $old_result;
			}
		}
	}
}
?>