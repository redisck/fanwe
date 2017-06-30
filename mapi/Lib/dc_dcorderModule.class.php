<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class dc_dcorderModule extends MainBaseModule
{

	/**
	 *
	 * 会员中心外卖订单列表页
	 *
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dc_dcorder&r_type=2&page=1
	 * 输入：
	 * page:int 当前的页数，没输入些参数时，默认为第一页
	 * 
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * order_list:array:array, 外卖订单列表，结构如下
	 * total_price:订单总额
	 * pay_amount:已支付金额
	 * pay_status：支付状态: 0未支付 1已支付
	 * confirm_status：订单商家确认状态，0:未确认（未接单，用户未付款可以取消，已付款可直接退款），1.已确认（商家已接单,客户可与商家联系，申请退款），2.已配送，或者预订订单验证成功
	 * is_cancel：是否被用户/商家(拒绝接单)/管理员取消,0:未取消,1:用户取消,2:商户取消,3.管理员取消
	 * refund_status：退款状态：0无退款，1退款申请中，2已退款，3退款驳回
	 * is_rs：是否为预定定单，0:否 ，为外卖 订单，1:是，为预订订单
	 * rs_price：如为预订订单，这字段为预订的定金
	 * order_delivery_time：外卖送达时间，等于1时，为立即送达，大于1时，我具体送达时间，格式为时间戳
	 * is_dp：是否已点评，一个订单只能点评一次
	 * dp_id：0为未点评，大于0，为具体的点评ID
	 * location_name：商家名称
	 * payment_id:0为在线支付，1为货到付款
	 * preview：商家图片
	 * order_state返回订单的状态  state_format为状态的文字描述 ，state为状态，代表意义如下：
	 *  1、待支付
		2、待接单
		3、已接单
		4、已完成，未点评
		5、订单关闭
		6.退款申请中
		7.已退款
		8.退款驳回
		9.已点评
	 * order_list：Array
        (
            [0] => Array
                (
                    [id] => 34
                    [order_sn] => 2015072901270120
                    [location_id] => 41
                    [create_time] => 1438118821
                    [order_status] => 0
                    [confirm_status] => 2
                    [pay_status] => 1
                    [total_price] => 43.6000
                    [payment_id] => 0
                    [refund_status] => 0
                    [consignee] => 王明
                    [order_delivery_time] => 1
                    [is_cancel] => 0
                    [rs_price] => 0.0000
                    [is_rs] => 0
                    [is_dp] => 0
                    [location_name] => 果果外卖
                    [dp_id] => 0
                    [preview] => http://localhost/o2onew/public/attachment/201504/17/10/55306e5b0f72a.jpg
                    [order_state] => Array
                        (
                            [state] => 5
                            [state_format] => 订单关闭
                        )
                )
          )      
	 * 
	 */
	public function index()
	{
		global_run();
		$root = array();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			$page = intval($GLOBALS['request']['page']);

			if($page==0){
				$page = 1;
			}	
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
	
			$user_id = $GLOBALS['user_info']['id'];
			require_once APP_ROOT_PATH."system/model/dc.php";
			//获取外卖订单列表
			$list = $GLOBALS['db']->getAll("select do.id ,do.order_sn ,do.location_id ,do.create_time ,do.order_status ,do.confirm_status ,do.pay_status ,do.total_price , do.pay_amount,do.payment_id ,do.refund_status,do.refuse_memo ,do.consignee ,do.order_delivery_time ,do.is_cancel ,do.rs_price ,do.is_rs ,
					do.is_dp ,do.location_name ,do.dp_id,do.is_dp , sl.preview from ".DB_PREFIX."dc_order as do left join ".DB_PREFIX."supplier_location as sl on do.location_id=sl.id where do.is_rs=0 and do.user_id=".$user_id ." order by do.id desc limit ".$limit);
			foreach($list as $k=>$v)
			{
				unset($list[$k]['order_menu']);
				$list[$k]['preview']=get_abs_img_root($v['preview']);
				$list[$k]['create_time_format']=to_date($v['create_time']);
				$list[$k]['order_state']=get_order_state($v);
				
			}
			
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where is_rs=0 and user_id=".$user_id);
			$page_total = ceil($total/$page_size);
			$page_title='外卖订单';
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$total);
			$root['page_title']=$page_title;
			$root['order_list']=$list;
			output($root);
		}	

	}
	
	

	/**
	 *
	 * 会员中心外卖订单详细页
	 *
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dc_dcorder&act=view&r_type=2&id=5
	 * 输入：
	 * id:int 订单ID
	 * 
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * order_info:array, 外卖订单详细页，结构如下
	 * total_price:订单总额
	 * pay_amount:已支付金额
	 * package_price:打包费
	 * delivery_price：配送费
	 * ecv_money：红包支付的金额
	 * promote_amount：优惠的金额
	 * pay_price:合计
	 * pay_status：支付状态: 0未支付 1已支付
	 * order_status：订单的结单状态标识，结单后的订单允许删除,0:否 1:是(结单条件:1.用户确认到货,2.商家在超期后帮用户确认到货,3.用户退款被确认)
	 * confirm_status：订单商家确认状态，0:未确认（未接单，用户未付款可以取消，已付款可直接退款），1.已确认（商家已接单,客户可与商家联系，申请退款），2.已配送，或者预订订单验证成功
	 * is_cancel：是否被用户/商家(拒绝接单)/管理员取消,0:未取消,1:用户取消,2:商户取消,3.管理员取消
	 * refund_status：退款状态：0无退款，1退款申请中，2已退款，3退款驳回
	 * is_rs：是否为预定定单，0:否 ，为外卖 订单，1:是，为预订订单
	 * rs_price：如为预订订单，这字段为预订的定金
	 * order_delivery_time：外卖送达时间，等于1时，为立即送达，大于1时，我具体送达时间，格式为时间戳
	 * is_dp：是否已点评，一个订单只能点评一次
	 * dp_id：0为未点评，大于0，为具体的点评ID
	 * location_name：商家名称
	 * payment_id:0为在线支付，1为货到付款
	 * preview：商家图片
	 * consignee:联系人
	 * mobile：手机号
	 * api_address:定位地址
	 * address:详细地址， 完成地址是： api_address+address
	 * dc_comment：订单备注
	 * invoice：发票信息
	 * promote_str：array:array  此订单享受的优惠信息
	 * order_state返回订单的状态  state_format为状态的文字描述 ，state为状态，代表意义如下：
	 *  1、待支付
		2、待接单
		3、已接单
		4、已完成，未点评
		5、订单关闭
		6.退款申请中
		7.已退款
		8.退款驳回
		9.已点评
	 * order_menu：array:array菜单信息
        [order_info] => Array
        (
            [id] => 5
            [order_sn] => 2015071305094736
            [location_id] => 41
            [create_time] => 1436749787
            [order_status] => 1
            [confirm_status] => 2
            [pay_status] => 1
            [total_price] => 39.4000
            [package_price] => 0.4000
            [delivery_price] => 1.0000
            [ecv_money] => 0.0000
            [promote_amount] => 5.0000
            [pay_amount] => 34.4000
            [payment_id] => 0
            [refund_status] => 0
            [consignee] => 888
            [mobile] => 15159646624
            [promote_str] => Array
                (
                    [PayOnlineDiscount] => Array
                        (
                            [name] => 在线支付优惠
                            [discount_amount] => 5
                            [promote_description] => 在线支付下单满20减5元，满40减12元，活动期间每天2单
                        )

                )

            [order_delivery_time] => 1
            [order_menu] => Array
                (
                    [0] => Array
                        (
                            [id] => 11
                            [session_id] => bmo6jo85rm0c6j9mlbb18cnub5
                            [user_id] => 71
                            [location_id] => 41
                            [supplier_id] => 43
                            [name] => 君子芋（薏仁+芋Q+红豆+奶冻）
                            [icon] => 
                            [num] => 1
                            [unit_price] => 20.0000
                            [total_price] => 20.0000
                            [menu_id] => 43
                            [table_time_id] => 0
                            [table_time] => 0
                            [cart_type] => 1
                            [add_time] => 1436749752
                            [is_effect] => 1
                            [url] => /o2onew/index.php?ctl=dcbuy&lid=41
                        )
                )

            [is_cancel] => 0
            [is_rs] => 0
            [location_name] => 果果外卖
            [order_state] => Array
                        (
                            [state] => 5
                            [state_format] => 订单关闭
                        )
        )
  
	 * 
	 */
	public function view()
	{
		global_run();
		$root = array();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			require_once APP_ROOT_PATH."system/model/dc.php";
			$id = intval($GLOBALS['request']['id']);
			$order_info = $GLOBALS['db']->getRow("select id ,order_sn ,location_id ,create_time ,order_status ,confirm_status ,pay_status ,
					total_price ,package_price,delivery_price,ecv_money,promote_amount,pay_amount,payment_id ,refund_status ,refuse_memo ,refund_memo,consignee ,mobile,api_address,address,dc_comment,invoice,promote_str,order_delivery_time,order_menu ,is_cancel  ,is_rs ,
					location_name,dp_id,is_dp  from ".DB_PREFIX."dc_order where type_del = 0 and user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
		
			$order_info['location_tel']=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where id=".$order_info['location_id']);
			
			if($order_info['id']>0)
			{

				$root['is_order_exists']=1;
				$order_menu_list=unserialize($order_info['order_menu']);
				
				$order_info['order_menu'] = $order_menu_list['menu_list']['cart_list'];
				$order_info['create_time_format']=to_date($order_info['create_time']);
				$order_info['promote_str']=unserialize($order_info['promote_str']);
				$order_info['order_menu']=array_values($order_info['order_menu']);
				
				if($order_info['order_delivery_time']==1){
					$order_info['order_delivery_time_format']='立即送达';
				}else{
					$order_info['order_delivery_time_format']=to_date($order_info['order_delivery_time']);
				}
				
				$page_title='订单详情';
				$root['page_title']=$page_title;
				
				$order_info['order_state']=get_order_state($order_info);
				$order_info['pay_price']=$order_info['total_price']-$order_info['ecv_money']-$order_info['promote_amount'];
				$root['order_info']=$order_info;
				output($root);
			}
			else
			{
				$root['is_order_exists']=0;
				output($root,0,'订单不存在');
			}

	
		}
	}
	
	
	/**
	 * 外卖确认收货接口
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_dcorder&act=verify_delivery&id=55&r_type=2
	 * 
	 * 输入：
	 * id：订单ID
	 * 
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：为外卖确认收货的状态，status=0,确认收货失败，status=1,确认收货成功
	 * info:返回的提示信息
	 * 
	 */
	public function verify_delivery()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			require_once APP_ROOT_PATH."system/model/dc.php";
			$id = intval($GLOBALS['request']['id']);
			
			$order_info=$GLOBALS['db']->getRow("select id from ".DB_PREFIX."dc_order where id=".$id);
			if($order_info){
				$root['is_order_exist']=1;
				$result = dc_confirm_delivery($id);
				output($root,$result['status'],$result['info']);
			}else{
				
				$root['is_order_exist']=0;
				output($root,0,'订单不存在');
			}
			

			
		}
	}
	
	
	/**
	 * 外卖取消订单接口
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_dcorder&act=cancel&id=52&r_type=2
	 * 
	 * 输入：
	 * id：订单ID
	 * 
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：为取消订单的操作的状态，status=0,订单取消失败;status=1,订单取消成功
	 * info:返回的提示信息
	 * 
	 */
	public function cancel()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			$id = intval($GLOBALS['request']['id']);
			$order_info = $GLOBALS['db']->getRow("select do.* , sl.tel as location_tel from ".DB_PREFIX."dc_order as do left join ".DB_PREFIX."supplier_location as sl on do.location_id=sl.id where do.id = ".$id." and do.type_del = 0 and do.user_id = ".$GLOBALS['user_info']['id']);
			if($order_info)
			{	require_once APP_ROOT_PATH."system/model/dc.php";
			
				$root['is_order_exist']=1;
				if($order_info['order_status']==1 || $order_info['type_del']==1 || $order_info['is_cancel']!=0){
					//订单已结单，或者订单已删除，或者订单已取消，不允许取消订单			

					output($root,0,'不允许取消订单');
				}else{
					
					if($order_info['pay_status']==0 and $order_info['payment_id']==0 )   //在线支付，订单未付款成功，直接取消订单
					{
						dc_order_close($id,1,$order_info['user_name'].'会员取消订单');
						output($root,1,'订单取消成功');
					}elseif($order_info['confirm_status']==0 && $order_info['payment_id']==1){  //货到付款，未接单，直接取消订单
						dc_order_close($id,1,$order_info['user_name'].'会员取消订单');
						output($root,1,'订单取消成功');
							
					}
					elseif($order_info['pay_status']==1 && $order_info['confirm_status']==0)
					{	//订单已付款成功，商家未接单，直接取消订单
						dc_order_close($id,1,$order_info['user_name'].'会员取消订单');
						output($root,1,'订单取消成功');
					}elseif($order_info['order_status']==0 && $order_info['confirm_status']==1){
						//订单已付款成功，商家已接单，线下和商家联系沟通
						$tip = "商户已经接单，如需取消订单请联系 ".$order_info['location_name'].",客服电话：".$order_info['location_tel'];
						output($root,0,$tip);
					}
					
					
				}
			}
			else
			{
				$root['is_order_exist']=0;
				output($root,0,"订单不存在");
			}
		}
	}
	

	
	
	/**
	 * 外卖催单接口
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_dcorder&act=dc_reminder&id=55&r_type=2
	 *
	 * 输入：
	 * id：订单ID
	 *
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：为催单的操作的状态，status=0,催单失败，status=1,催单成功
	 * info:返回的提示信息
	 *
	 */
	public function dc_reminder(){
		
		
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			$id = intval($GLOBALS['request']['id']);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where type_del = 0 and confirm_status!=2 and user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
	
			if($order_info)
			{
				$root['is_order_exist']=1;
				//催单时间，每次要间隔15分钟,以下SQL是查询有没有15分钟之内的催单记录
				$dc_reminder=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_reminder where order_id=".$id." and ".NOW_TIME." - create_time < 900 order by create_time desc limit 1");
				if($dc_reminder){
					//$dc_reminder存在，不可以催
					
					output($root,0,"亲,您催的太快了,15分钟催一次哦!");
					
				}else{
					//$dc_reminder不存在，可以催
					$data['order_sn']=$order_info['order_sn'];
					$data['order_id']=$order_info['id'];
					$data['location_id']=$order_info['location_id'];
					$data['supplier_id']=$order_info['supplier_id'];
					$data['create_time']=NOW_TIME;
					$data['user_id']=$order_info['user_id'];
					$data['consignee']=$order_info['consignee'];
					$data['mobile']=$order_info['mobile'];
					$data['api_address']=$order_info['api_address'];
					$data['address']=$order_info['address'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."dc_reminder", $data);
					$rs=$GLOBALS['db']->affected_rows();
					if($rs > 0){

						output($root,1,"催单成功,请耐心等待!");
					}else{

						output($root,0,"催单失败,请重新催单!");
					}
				}
				
			}else{
				$root['is_order_exist']=0;
				output($root,0,"订单不存在");
			}
		}
	}
	
	

}
?>