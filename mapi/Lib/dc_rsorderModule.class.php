<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class dc_rsorderModule extends MainBaseModule
{

	/**
	 *
	 * 会员中心预订订单列表页
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dc_rsorder&r_type=2&page=1
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
	 * is_dp：是否已点评，一个订单只能点评一次
	 * dp_id：0为未点评，大于0，为具体的点评ID
	 * location_name：商家名称
	 * preview：商家图片
	 * payment_id:0为在线支付，1为货到付款
	 * rs_info:array:array,为预订信息,其中name为预订座位名称，table_time为预订的时间
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
	 * Array
	 * 		(
			        [id] => 38
                    [order_sn] => 2015080404462699
                    [location_id] => 41
                    [create_time] => 1438649186
                    [order_status] => 0
                    [confirm_status] => 0
                    [pay_status] => 1
                    [total_price] => 150.0000
                    [payment_id] => 0
                    [refund_status] => 0
                    [consignee] => 王明
                    [is_cancel] => 0
                    [rs_price] => 150.0000
                    [is_rs] => 1
                    [is_dp] => 0
                    [location_name] => 果果外卖
                    [dp_id] => 0
                    [preview] => http://localhost/o2onew/public/attachment/201504/17/10/55306e5b0f72a.jpg
                    [rs_info] => Array
                        (
                            [0] => Array
                                (
                                    [name] => 散桌8-10人桌
                                    [table_time] => 1438977600
                                    [table_time_format] => 2015-08-08 星期六 12:00
                                )

                        )
                     [order_state] => Array
                        (
                            [state] => 5
                            [state_format] => 订单关闭
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
			require_once APP_ROOT_PATH."system/model/dc.php";
			$user_id = $GLOBALS['user_info']['id'];
			//获取预订订单列表
			$list = $GLOBALS['db']->getAll("select do.id ,do.order_sn ,do.location_id ,do.create_time ,do.order_status,do.pay_amount ,do.confirm_status ,do.pay_status ,do.total_price ,do.payment_id ,do.refund_status ,do.consignee ,do.mobile,do.order_menu ,do.is_cancel ,do.rs_price ,do.is_rs ,
					do.is_dp ,do.location_name ,do.dp_id,do.is_dp , sl.preview from ".DB_PREFIX."dc_order as do left join ".DB_PREFIX."supplier_location as sl on do.location_id=sl.id where do.is_rs=1 and do.user_id=".$user_id ." order by do.id desc limit ".$limit);
			
			
			foreach($list as $k=>$v)
			{
				unset($list[$k]['order_menu']);
				$list[$k]['preview']=get_abs_img_root($v['preview']);
				$list[$k]['create_time_format']=to_date($v['create_time']);
				$list[$k]['order_state']=get_order_state($v);
				$order_menu=unserialize($v['order_menu']);
				$rows=array("日","一","二","三","四","五","六");
				foreach($order_menu['rs_list']['cart_list'] as $kk=>$vv){
					$rs_info=array();
					$rs_info['name']=$vv['name'];
					$rs_info['table_time']=$vv['table_time'];
					$rs_info['table_time_format']=to_date($vv['table_time'],"Y-m-d").' 星期'.$rows[to_date($vv['table_time'],"w")].' '.to_date($vv['table_time'],"H:i");
					$list[$k]['rs_info'][]=$rs_info;
				}

			}
			
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where is_rs=1 and user_id=".$user_id);
			$page_total = ceil($total/$page_size);
			$page_title='预订订单';
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$total);
			$root['page_title']=$page_title;
			$root['order_list']=$list;
			output($root);
		}	

	}
	
	
	

	/**
	 *
	 * 会员中心预订订单详细页
	 *
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dc_rsorder&act=view&r_type=2&id=35
	 * 输入：
	 * page:int 当前的页数，没输入些参数时，默认为第一页
	 *
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * order_info:array, 外卖订单详细页，结构如下
	 * total_price:订单总额
	 * pay_amount:已支付金额
	 * ecv_money：红包支付的金额
	 * promote_amount：优惠的金额
	 * pay_price：合计
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
	 * order_menu：array:array菜单信息
	 * dc_coupon:电子劵，只有商家接单了，会员才能看电子劵信息，其中：sn为预订电子劵的序列号，end_time为电子劵过期时间，is_expired为电子劵是否过期，0为未过期，1为已过期，is_used为是否已经验证消费过，confirm_time为验证消费的时间
	 * coupon_state:电子劵状态和动作,其中电子劵动作中有个字段has_reason，是用来判断该动作是否需要跳到填写原因的，退款申请中用到该字段
	 * order_menu的 rs_list为预订的座位信息，menu_list为预订的商品信息，如果用户只订座，无点菜，则menu_list为空。total_data为预订或者商品的金额和数量的统计
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
	 * order_info: Array
        (
            [id] => 35
            [order_sn] => 2015080403151339
            [location_id] => 41
            [create_time] => 1438643713
            [order_status] => 0
            [confirm_status] => 0
            [pay_status] => 1
            [total_price] => 164.0000
            [package_price] => 0.0000
            [delivery_price] => 0.0000
            [ecv_money] => 0.0000
            [promote_amount] => 0.0000
            [pay_amount] => 164.0000
            [payment_id] => 0
            [refund_status] => 0
            [consignee] => 王明
            [mobile] => 15158984452
            [api_address] => 
            [address] => 
            [dc_comment] => 
            [invoice] => 
            [promote_str] => 
            [rs_price] => 150.0000
            [order_menu] => Array
                (
                    [rs_list] => Array
                        (
                            [cart_list] => Array
                                (
                                    [0] => Array
                                        (
                                            [name] => 散桌8-10人桌
                                            [table_time] => 1438977600
                                            [table_time_format] => 2015-08-08 星期六 12:00
                                        )

                                )

                            [total_data] => Array
                                (
                                    [total_price] => 150.0000
                                    [total_count] => 1
                                )

                        )

                    [menu_list] => Array
                        (
                            [cart_list] => Array
                                (
                                    [0] => Array
                                        (
                                            [id] => 195
                                            [session_id] => db0gdd485luijc8391rr4l5416
                                            [user_id] => 71
                                            [location_id] => 41
                                            [supplier_id] => 43
                                            [name] => 君子芋（薏仁+芋Q+红豆+奶冻）
                                            [icon] => 
                                            [num] => 2
                                            [unit_price] => 20.0000
                                            [total_price] => 40.0000
                                            [menu_id] => 43
                                            [table_time_id] => 0
                                            [table_time] => 0
                                            [cart_type] => 1
                                            [add_time] => 1438620289
                                            [is_effect] => 1
                                            [url] => /o2onew/index.php?ctl=dcbuy&lid=41
                                        )
                                )

                            [total_data] => Array
                                (
                                    [total_price] => 164.0000
                                    [total_count] => 11
                                )

                        )

                )

            [is_cancel] => 0
            [is_rs] => 1
            [location_name] => 果果外卖
            [dc_coupon] => Array
                (
                    [id] => 16
                    [sn] => 35152157
                    [begin_time] => 1438643713
                    [end_time] => 1439020799
                    [is_valid] => 1
                    [user_id] => 71
                    [order_id] => 35
                    [supplier_id] => 43
                    [location_id] => 41
                    [confirm_account] => 0
                    [is_used] => 0
                    [confirm_time] => 0
                    [is_expired] => 0
                )
             [coupon_state] => Array
                (
                    [state] => 有效
                    [act] => Array
                        (
                            [0] => Array
                                (
                                    [name] => 短信发送
                                    [ctl] => dc_rsorder
                                    [act] => rend_coupon_sms
                                    [id] => 40
                                )

                        )

                )
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
			$order_info = $GLOBALS['db']->getRow("select id ,order_sn ,location_id ,create_time ,order_status ,confirm_status ,pay_status ,refuse_memo ,refund_memo,
					total_price ,ecv_money,promote_amount,pay_amount,payment_id ,refund_status ,consignee ,mobile,api_address,address,dc_comment,invoice,promote_str,rs_price,order_menu ,is_cancel  ,is_rs ,
					location_name,sn_id,dp_id,is_dp  from ".DB_PREFIX."dc_order where type_del = 0 and user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
			$order_info['location_tel']=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where id=".$order_info['location_id']);
			if($order_info['id']>0)
			{
				
				$root['is_order_exists']=1;
				$order_menu_list=unserialize($order_info['order_menu']);
				unset($order_info['order_menu']);
				$rows=array("日","一","二","三","四","五","六");
				foreach($order_menu_list['rs_list']['cart_list'] as $kk=>$vv){
					$rs_info=array();
					$rs_info['name']=$vv['name'];
					$rs_info['table_time']=$vv['table_time'];
					$rs_info['table_time_format']=to_date($vv['table_time'],"Y-m-d").' 星期'.$rows[to_date($vv['table_time'],"w")].' '.to_date($vv['table_time'],"H:i");
					$order_menu_list['rs_list']['cart_list'][$kk]=$rs_info;
				}
				$order_info['order_menu_list'] = $order_menu_list;
				$order_info['create_time_format']=to_date($order_info['create_time']);
				$order_info['promote_str']=unserialize($order_info['promote_str']);
				$order_info['order_menu_list']['rs_list']['cart_list']=array_values($order_info['order_menu_list']['rs_list']['cart_list']);
				$order_info['order_menu_list']['menu_list']['cart_list']=array_values($order_info['order_menu_list']['menu_list']['cart_list']);	
				$page_title='订单详情';
				
				$root['page_title']=$page_title;
				//只有商家接单了，会员才能看到电子劵
				if($order_info['confirm_status']>0){
					$dc_coupon=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_coupon where is_valid > 0 and id =".$order_info['sn_id']);
					if($dc_coupon){
						$dc_coupon['end_time_format']=to_date($dc_coupon['end_time'],"Y-m-d");
						if($dc_coupon['end_time'] > NOW_TIME){
							$dc_coupon['is_expired']=0;
						}else{
							$dc_coupon['is_expired']=1;
						}
						$order_info['dc_coupon']=$dc_coupon;
						$order_info['coupon_state']=$this->get_dc_coupon_state($order_info);
					}
				}
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
	 * 预订电子劵短信发送接口
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_rsorder&act=rend_coupon_sms&id=40&r_type=2
	 *
	 * 输入：
	 * id：订单ID
	 *
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：为预订电子劵短信发送操作的状态，status=0,电子劵短信发送失败，status=1,电子劵短信发送成功
	 * info:返回的提示信息
	 *
	 */
	public function rend_coupon_sms()
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
			$order_info = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."dc_order where id = ".$id);
			if($order_info['id'] >0){
				$root['is_order_exist']=1;
				dc_send_user_coupon_sms($id);
				output($root,1,'短信发送成功');
			}else{
				$root['is_order_exist']=0;
				output($root,0,'订单不存在');
			}

		}
	}
	
	
	/**
	 * 预订取消订单接口
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_rsorder&act=cancel&id=50&r_type=2
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

					output($root,0,"不允许取消订单");
				}else{
					
					if($order_info['pay_status']==0 )   //在线支付，订单未付款成功，直接取消订单
					{
						dc_order_close($id,1,$order_info['user_name'].'会员取消订单');
						output($root,1,"订单取消成功");
					}elseif($order_info['pay_status']==1 && $order_info['confirm_status']==0)
					{	//订单已付款成功，商家未接单，直接取消订单
						dc_order_close($id,1,$order_info['user_name'].'会员取消订单');
						output($root,1,"订单取消成功");
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
	 * 预订电子劵申请退款的接口
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_rsorder&act=do_refund&id=40&r_type=2
	 *
	 * 输入：
	 * id：订单ID
	 * content：申请退款的原因
	 * 输出：
	 * user_login_status:用户登录状态，等于1为已登录，等于0为未登录
	 * status：为申请退款操作的状态，status=0,申请退款失败;status=1,申请退款成功
	 * info:返回的提示信息
	 *
	 */
	public function do_refund()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status']=0;
			output($root);
		}else{
			$root['user_login_status']=1;
			$id = intval($GLOBALS['request']['id']);
			$content = strim($GLOBALS['request']['content']);

			if($id)
			{
				
				//退单
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$id." and order_status = 0 and user_id = ".$GLOBALS['user_info']['id']);
				if($order_info)
				{	$root['is_order_exist']=1;
					if(empty($content))
					{
						output($root,0,"请填写退款原因");
					}						
					
						if($order_info['refund_status']==0)
						{
							//执行退单,标记：deal_order表，
							
							$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set refund_status = 1 , refund_memo='".$content."' where id = ".$id);
							
							$msg = array();
							$msg['rel_table'] = "dc_order";
							$msg['rel_id'] = $id;
							$msg['title'] = "退款申请";
							$msg['content'] = "退款申请：".$content;
							$msg['create_time'] = NOW_TIME;
							$msg['user_id'] = $GLOBALS['user_info']['id'];
							$GLOBALS['db']->autoExecute(DB_PREFIX."message",$msg);
							require_once APP_ROOT_PATH."system/model/dc.php";
							dc_order_log($order_info['order_sn']."申请退款，等待审核", $id);

							output($root,1,"退款申请已提交，请等待审核");
						}
						else
						{
							output($root,0,"不允许退款");
						}
	
				}
				else
				{
					$root['is_order_exist']=0;
					output($root,0,"非法操作");
				}
			}
			else
			{
				output($root,0,"非法操作");
			}
				
		}
	}
	
	/**
	 * 获取电子劵状态和可操作动作
	 * @param unknown_type $order_info 订单信息
	 * @return $order_state：array:array,返回电子劵状态和可操作动作
	 */
	public function get_dc_coupon_state($order_info){
		$order_state=array();
		if($order_info['refund_status']==0){
			if($order_info['dc_coupon']['end_time']==0 || $order_info['dc_coupon']['end_time'] > NOW_TIME ){
				if($order_info['is_cancel']==0 && $order_info['order_status']==0){
					if($order_info['dc_coupon']['is_valid']==1 && $order_info['dc_coupon']['is_used']==0){
						$order_state['state']='未消费';
							$url=wap_url('index','dc_rsorder#rend_coupon_sms',array('id'=>$order_info['id']));
							$order_state['act'][]=array('name'=>'短信发送','ctl'=>'dc_rsorder','act'=>'rend_coupon_sms','id'=>$order_info['id'],'url'=>$url);
						
						
					}elseif($order_info['dc_coupon']['is_used']==1){
						$order_state['state']='已消费&nbsp;&nbsp;'.to_date($order_info['dc_coupon']['confirm_time']);
					}else{
						$order_state['state']='无效';
					}
					
				}elseif($order_info['order_status']==1){
					$order_state['state']='已消费&nbsp;&nbsp;'.to_date($order_info['dc_coupon']['confirm_time']);
				}elseif($order_info['is_cancel']==1){
					$order_state['state']='无效';
				}
				
			}else{
				$order_state['state']='已过期';
				if($order_info['dc_coupon']['is_expired']==1 && $order_info['dc_coupon']['is_used']==0){
					if($order_info['order_status']==0 && $order_info['is_cancel']==0 ){
					$url=wap_url('index','dc_rsorder#do_refund',array('id'=>$order_info['id']));
					$order_state['act'][]=array('name'=>'退款','ctl'=>'dc_rsorder','act'=>'do_refund','id'=>$order_info['id'],'url'=>$url,'has_reason'=>1);
					}
				}
			}
			
		}elseif($order_info['refund_status']==1){
			$order_state['state']='退款中';
			$order_state['memo']=$order_info['refund_memo'];
		}elseif($order_info['refund_status']==2){
			$order_state['state']='已退款';
			$order_state['memo']=$order_info['refuse_memo'];
		}elseif($order_info['refund_status']==3){
			$order_state['state']='退款驳回';
			$order_state['memo']=$order_info['refuse_memo'];
		}
		return $order_state;
		
	}
	
}
?>