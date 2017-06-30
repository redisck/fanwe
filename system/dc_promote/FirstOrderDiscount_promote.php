<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

$lang = array(
	'name'	=>	'首单立减',
	'description'	=>	'在外卖预订业务中，首单立减XX元(在线支付专享)',
	'discount_amount'	=>	'首单立减'
);

$config = array(
	'discount_amount' =>	''
);

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'FirstOrderDiscount';

    /* 名称 */
    $module['name']    = $lang['name'];
    
    /* 描述 */
    $module['description']    = $lang['description'];

	$module['config'] = $config;
    $module['lang'] = $lang;
    return $module;
}


// 首单在线支付优惠
require_once(APP_ROOT_PATH.'system/libs/dc_promote.php');
class FirstOrderDiscount_promote implements dc_promote {
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
		$is_ordered=$GLOBALS['db']->getOne("select dc_is_share_first from ".DB_PREFIX."user where id=".$user_id);
		//判断是否是首单
		$user_order_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where user_id=".$user_id);
		
		if($is_ordered==1 || $user_order_count>0){	
			return $result;
		}else{
		//取出接口配置
		$promote_obj = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_promote where class_name='FirstOrderDiscount'");
		$promote_cfg = unserialize($promote_obj['config']);
		
			$old_result['pay_price'] = $old_result['pay_total_price'] - $old_result['paid_account_money'] - $old_result['paid_ecv_money'] - $old_result['paid_promote_amount'];
			$tmp_pay_price_xx=$old_result['pay_price'];
			$old_result['pay_price'] = $old_result['pay_price']- $promote_cfg['discount_amount'] ;	
			


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
			$promote_cfg['discount_amount']=$tmp_pay_price_xx;
			$old_result['ecv_money']=0;
			$old_result['pay_price']=0;
		}
		
				
		   
			if($promote_cfg['discount_amount'] > 0){
			$promote=array();
				
			$promote['name'] = '首单立减优惠';
			$promote['class_name'] = $promote_obj['class_name'];
			$promote['discount_amount']=$promote_cfg['discount_amount'];
			$promote['promote_description'] = $promote_obj['description'];

			$old_result['dc_promote'][] = $promote;
			}
		return $old_result;
		}
	}
}
?>