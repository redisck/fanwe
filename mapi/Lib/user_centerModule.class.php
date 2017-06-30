<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class user_centerModule extends MainBaseModule
{
	
	/**
	 * 	 会员中心首页接口
	 * 
	 * 	 输入:  
	 *  
	 *  输出:
	 * user_login_status:int   0表示未登录   1表示已登录  2表示临时登录
	 * login_info:string 未登录状态的提示信息，已登录时无此项
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);	
	 * uid:int  	71 会员id
	 * user_name:string     fanwe  会员名
	 * user_money_format:string   ¥9973.2会员账户余额
  	 * user_avatar:string   http://localhost/o2onew/public/avatar/000/00/00/71virtual_avatar_big.jpg 会员头像图路径
  	 * user_score: int 会员积分
  	 * user_score_format:string 会员积分格式化
  	 * not_pay_order_count:int 未付款订单数
	 * wait_dp_count: int 待点评数量
	 */
	public function index()
	{
		$root = array();	
		$user_data = $GLOBALS['user_info'];
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){			
			$root['user_login_status'] = $user_login_status;	
		}else{

			$root['user_login_status'] = $user_login_status;			
			
			$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
			$root['page_title'].="会员中心";
			$root['uid'] = $user_data['id']?$user_data['id']:0;
			$root['user_name'] = $user_data['user_name']?$user_data['user_name']:0;			
			$root['user_money_format'] = format_price($user_data['money'])?format_price($user_data['money']):"";//用户金额
			$root['user_score'] = intval($user_data['score']);
			$root['user_score_format'] = format_score($user_data['score']);
			$root['user_avatar'] = get_abs_img_root(get_muser_avatar($user_data['id'],"big"))?get_abs_img_root(get_muser_avatar($user_data['id'],"big")):"";
			
			$user_id = $user_data['id'];
			$coupon_count = $GLOBALS ['db']->getOne ( "select count(*) from " . DB_PREFIX . "deal_coupon where user_id = " . $user_id . " and is_delete = 0 and is_valid = 1 " );
			$root ['coupon_count'] = $coupon_count;
				
			$youhui_count = $GLOBALS ['db']->getOne ( "select count(*) from " . DB_PREFIX . "youhui_log as yl left join " . DB_PREFIX . "youhui as yh on yh.id = yl.youhui_id where yl.user_id=$user_id " );
			$root ['youhui_count'] = $youhui_count;

			//待点评
			$root['wait_dp_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item as doi LEFT JOIN ".DB_PREFIX."deal_order as do on do.id = doi.order_id where do.type=0 and do.user_id=".$user_id." and do.order_status=1 and do.pay_status=2 and doi.consume_count>0 and doi.dp_id =0");
			require_once APP_ROOT_PATH."system/model/deal_order.php";
			$order_table_name = get_user_order_table_name($user_id);
			$not_pay_order_count = $GLOBALS ['db']->getOne ( "select count(*) from " . $order_table_name . " where user_id = " . $user_id . " and type = 0 and is_delete = 0 and pay_status <> 2" );
			$root ['not_pay_order_count'] = $not_pay_order_count;

		}
		
		output($root);

		

	}
	
}
?>