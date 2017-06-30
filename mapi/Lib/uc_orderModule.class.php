<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_orderModule extends MainBaseModule
{
	
	/**
	 * 会员中心我的抽奖
	 * 输入：
	 * page:int 当前的页数
	 * pay_status:int 支付状态 0未支付 1已支付
	 * 
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * item:array 订单列表
	 * Array(
	 *    Array
                (
                    [id] => 52 int 订单ID
                    [order_sn] => 2015050405530018 string 订单编号
                    [order_status] => 0 int 订单状态 0:未结单 1:结单(将出现删除订单按钮)
                    [pay_status] => 0 int 支付状态 0:未支付(出现取消订单按钮) 1:已支付
                    [create_time] => 2015-05-04 17:53:00  string 下单时间
                    [pay_amount] => 0 float 已付金额
                    [total_price] => 16.9 float 应付金额
                    [c] => 1 int 商品总量
                    [deal_order_item] => Array
                        (
                            [0] => Array
                                (
                                    [id] => 112 int 订单表中的商品ID
                                    [deal_id] => 22 int 商品ID，用于跳到商品页
                                    [deal_icon] => http://192.168.1.41/o2onew/public/attachment/201502/26/11/54ee909199d43_244x148.jpg 122x74 string 商品图
                                    [name] => 仅售14.9元！价值66元的雨含浴室防滑垫1张，透明材质，环保无毒，两色可选，带吸盘，选择它给您的家人多一份关爱 string 商品全名
                                    [sub_name] => 雨含浴室防滑垫  string 商品短名
                                    [number] => 1 int 购买数量
                                    [unit_price] => 14.9 float 单价
                                    [total_price] => 14.9 float 总价
                                    [dp_id] => int 点评ID ，ID大于0表示已点评
                                    [consume_count] => int 消费数 大于0表示可以点评
                                    [delivery_status]	=>	配送状态0:未发货 1:已发货 5.无需发货
                                    [is_arrival]	=>	int 是否已收货 0:未收货1:已收货2:没收到货(维权)
                                    [is_refund]	=>	int 是否支持退款，由商品表同步而来，0不支持 1支持
                                    [refund_status]	=>	int 退款状态 0未退款 1退款中 2已退款 3退款被拒
                                )
                                
                           ==============每个订单商品的状态与功能的关联说明===============
                           1. 当order_status为1,consume_count大于0，时将出现点评项，dp_id大于0表示已点评，否则为未点评，可以点击链接到点评页面,点评的type为deal，data_id为商品的deal_id
                           2. 当order_status为0（未结单），delivery_status不等于5(需要发货的商品),is_arrival等于1(已收货)时将出现点评项，dp_id大于0表示已点评，否则为未点评，可以点击链接到点评页面,点评的type为deal，data_id为商品的deal_id
                           3. 当delivery_status为0(需发货商品，未发货时),pay_status为2（已支付时），is_refund为1(支持退款)，显示退款功能,refund_status为0时(未退款)，显示退款操作，点击后进入退款操作页(uc_order#refund item_id=deal_order_item_id),1显示退款中 2显示已退款 3显示退款被拒
                           4. 当delivery_status为5(团购券商品，需要退券),pay_status为2（已支付时），is_refund为1(支持退款)，显示退款功能，order_status为0时（未结单）不显示状态，一概显示退款,点击后进入退款操作页(uc_coupon#refund item_id=deal_order_item_id),order_status为1（结果时），当refund_status大于0，有退款状态，显示状态,1显示退款中 2显示已退款 3显示退款被拒
                           5. 当order_status为0（未结单）,当delivery_status不为5(实体商品)显示发货状态,delivery_status:0 显示未发货，1:已发货，is_arrival为0时（未收货）显示查询物流操作,显示确认收货操作，显示没收到货操作 is_arrival为1显示已收货, is_arrival为2显示维权中
                           
                           ==============每个订单商品的状态与功能的关联说明===============

                        )

                    [status] => 未支付 string 订单状态
                )
          )
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * page_title:string 页面标题
	 */
	public function index()
	{
		$root = array();		
		/*参数初始化*/
		
		//检查用户,用户密码
		$user = $GLOBALS['user_info'];
		$user_id  = intval($user['id']);			
		$pay_status = intval($GLOBALS['request']['pay_status']);
		$id = intval($GLOBALS['request']['id']);
		$condition = " do.pay_status = 2 ";
		if($pay_status==0)
			$condition = " do.pay_status <> 2 ";
		
		if($id>0)
			$condition.=" and do.id = ".$id." ";
		
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){
		    $root['user_login_status'] = $user_login_status;	
		}
		else
		{
			$root['user_login_status'] = $user_login_status;	
			
			
			//分页
			$page = intval($GLOBALS['request']['page']);
			$page=$page==0?1:$page;
				
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
			
    		require_once APP_ROOT_PATH."system/model/deal_order.php";
			$order_table_name = get_user_order_table_name($user_id);
			
			$sql = "select do.* from ".$order_table_name." as do where do.is_delete = 0 and ".
			" do.user_id = ".$user_id." and do.type = 0 and ".$condition."  order by do.create_time desc limit ".$limit;		
			$sql_count = "select count(*) from ".$order_table_name." as do where do.is_delete = 0 and ".
			" do.user_id = ".$user_id." and do.type = 0 and ".$condition;
			
			$list = $GLOBALS['db']->getAll($sql);		
			$count = $GLOBALS['db']->getOne($sql_count);
		
				
				
			$page_total = ceil($count/$page_size);
			//end 分页

			//要返回的字段
			$data = array();
			foreach($list as $k=>$v)
			{
				$order_item = array();
				$order_item['id'] = $v['id'];
				$order_item['order_sn'] = $v['order_sn'];
				$order_item['order_status'] = $v['order_status'];
				$order_item['pay_status'] = $v['pay_status'];
				$order_item['create_time'] = to_date($v['create_time']);
				$order_item['pay_amount'] = round($v['pay_amount'],2);
				$order_item['total_price'] = round($v['total_price'],2);
				if($v['deal_order_item'])
				{
					$list[$k]['deal_order_item'] = unserialize($v['deal_order_item']);				
				}
				else
				{
					$order_id = $v['id'];
					update_order_cache($order_id);
					$list[$k]['deal_order_item'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
				}
				$c = 0;
				foreach($list[$k]['deal_order_item'] as $key=>$row)
				{
					$c+=intval($row['number']);
				}
				$order_item['c'] = $c;
				foreach($list[$k]['deal_order_item'] as $kk=>$vv)
				{
					$deal_item = array();	
					$deal_item['id'] = $vv['id'];
					$deal_item['deal_id'] = $vv['deal_id'];
					$deal_item['deal_icon'] = get_abs_img_root(get_spec_image($vv['deal_icon'],122,74,1));
					$deal_item['name'] = $vv['name'];
					$deal_item['sub_name'] = $vv['sub_name'];
					$deal_item['number'] = $vv['number'];
					$deal_item['unit_price'] = round($vv['unit_price'],2);
					$deal_item['total_price'] = round($vv['total_price'],2);
					$deal_item['consume_count'] = intval($vv['consume_count']);
					$deal_item['dp_id'] = intval($vv['dp_id']);
					$deal_item['delivery_status'] = intval($vv['delivery_status']);
					$deal_item['is_arrival'] =	intval($vv['is_arrival']);
					$deal_item['is_refund'] =	intval($vv['is_refund']);
					$deal_item['refund_status']	=	intval($vv['refund_status']);
					
					$order_item['deal_order_item'][$kk] = $deal_item;
				}
				
				//开始处理订单状态
				$order_status = "";				
				if($v['order_status'] == 1) //结单的订单显示说明
				$order_status = "订单已完结";
				else
				{
					if($v['pay_status'] != 2)
					{
						$order_status = "未支付";
					}
					else
					{
						$order_status = "已支付";
					}
				}				
				$order_item['status'] = $order_status;
				//订单状态
				
				$data[$k] = $order_item;
			}
			
			$root['item'] = $data;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		}	

		$root['pay_status'] = $pay_status;
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		
		if($pay_status==0)
			$root['page_title'].="未付款订单";
		else
			$root['page_title'].="我的订单";
		output($root);
	}	
	
	
	/**
	 * 取消删除订单接口
	 * 
	 * 输入
	 * id: int 订单ID
	 * 
	 * 输出
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * status: int 0失败 1成功
	 * info: string 消息
	 */
	public function cancel()
	{
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			 $root['user_login_status'] = $user_login_status;
			 output($root,0,"请先登录");
		}
		else
		{
			$root['user_login_status'] = $user_login_status;
			$id = intval($GLOBALS['request']['id']);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id." and is_delete = 0 and user_id = ".$GLOBALS['user_info']['id']);
			if($order_info)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set is_delete = 1 where (order_status = 1 or pay_status = 0) and is_delete = 0 and user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
				if($GLOBALS['db']->affected_rows())
				{
					require_once APP_ROOT_PATH."system/model/deal_order.php";
					//开始退已付的款
					if($order_info['pay_status']==0&&$order_info['pay_amount']>0)
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set pay_amount = 0,ecv_id = 0,ecv_money=0,account_money = 0 where id = ".$order_info['id']);
						require_once APP_ROOT_PATH."system/model/user.php";
						if($order_info['account_money']>0)
						{
							modify_account(array("money"=>$order_info['account_money']), $order_info['user_id'],"取消订单，退回余额支付 ");
							order_log("用户取消订单，退回余额支付 ".$order_info['account_money']." 元", $order_info['id']);
						}
						if($order_info['ecv_id'])
						{
							$GLOBALS['db']->query("update ".DB_PREFIX."ecv set use_count = use_count - 1 where id = ".$order_info['ecv_id']);
							order_log("用户取消订单，代金券退回 ", $order_info['id']);
						}
	
					}
					over_order($order_info['id']);
					
					output($root,1,"订单删除成功");
				}
				else
				{
					output($root,0,"订单删除失败");
				}
			}
			else
			{
				output($root,0,"订单不存在");
			}
		}
	}
	
	
	/**
	 * 加载退款（实体商品的页面数据加载），本接口不作数据越权验证，提交时验证
	 * 输入:
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 * 
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户):判断==1
	 * page_title: string 页面标题
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 * item:array 订单商品数据
	 *  [id] => 112 int 订单表中的商品ID
        [deal_id] => 22 int 商品ID，用于跳到商品页
        [deal_icon] => http://192.168.1.41/o2onew/public/attachment/201502/26/11/54ee909199d43_244x148.jpg 122x74 string 商品图
        [name] => 仅售14.9元！价值66元的雨含浴室防滑垫1张，透明材质，环保无毒，两色可选，带吸盘，选择它给您的家人多一份关爱 string 商品全名
        [sub_name] => 雨含浴室防滑垫  string 商品短名
        [number] => 1 int 购买数量
        [unit_price] => 14.9 float 单价
        [total_price] => 14.9 float 总价
        [dp_id] => int 点评ID ，ID大于0表示已点评
        [consume_count] => int 消费数 大于0表示可以点评
        [delivery_status]	=>	配送状态0:未发货 1:已发货 5.无需发货
        [is_arrival]	=>	int 是否已收货 0:未收货1:已收货2:没收到货(维权)
        [is_refund]	=>	int 是否支持退款，由商品表同步而来，0不支持 1支持
        [refund_status]	=>	int 退款状态 0未退款 1退款中 2已退款 3退款被拒
	 */
	public function refund()
	{
		
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status'] = $user_login_status;
		}
		else
		{
		
			$root['user_login_status'] = $user_login_status;
			$item_id = intval($GLOBALS['request']['item_id']);
		
			$root['page_title'] = "退款申请";
			$root['item_id'] = $item_id;
			
			$vv = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$item_id);		
			$deal_item = array();
			$deal_item['id'] = $vv['id'];
			$deal_item['deal_id'] = $vv['deal_id'];
			$deal_item['deal_icon'] = get_abs_img_root(get_spec_image($vv['deal_icon'],122,74,1));
			$deal_item['name'] = $vv['name'];
			$deal_item['sub_name'] = $vv['sub_name'];
			$deal_item['number'] = $vv['number'];
			$deal_item['unit_price'] = round($vv['unit_price'],2);
			$deal_item['total_price'] = round($vv['total_price'],2);
			$deal_item['consume_count'] = intval($vv['consume_count']);
			$deal_item['dp_id'] = intval($vv['dp_id']);
			$deal_item['delivery_status'] = intval($vv['delivery_status']);
			$deal_item['is_arrival'] =	intval($vv['is_arrival']);
			$deal_item['is_refund'] =	intval($vv['is_refund']);
			$deal_item['refund_status']	=	intval($vv['refund_status']);			
			$root['item'] = $deal_item;
		}
		
		output($root);	
		
	}
	
	/**
	 * 执行退款接口(实体商品)
	 * 输入:
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 * content:string 退单理由
	 * 
	 * 输出
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * status: int 0失败 1成功
	 * info: string 消息
	 * 
	 */
	public function do_refund()
	{
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status'] = $user_login_status;
			output($root,0,"请先登录");
		}
		else
		{
			//退单
			$item_id = intval($GLOBALS['request']['item_id']);
			$content = strim($GLOBALS['request']['content']);
			$root['user_login_status'] = $user_login_status;
			if($content=="")
			{
				output($root,0,"请输入退款理由");
			}
			
			$deal_order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$item_id);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = '".$deal_order_item['order_id']."' and order_status = 0 and user_id = ".$GLOBALS['user_info']['id']);
			if($order_info)
			{
				if($deal_order_item['delivery_status']==0&&$order_info['pay_status']==2&&$deal_order_item['is_refund']==1)
				{
					if($deal_order_item['refund_status']==0)
					{
						//执行退单,标记：deal_order_item表与deal_order表，
						$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 1 where id = ".$deal_order_item['id']);
						$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_status = 1 where id = ".$deal_order_item['order_id']);
							
						$msg = array();
						$msg['rel_table'] = "deal_order";
						$msg['rel_id'] = $deal_order_item['order_id'];
						$msg['title'] = "退款申请";
						$msg['content'] = "退款申请：".$content;
						$msg['create_time'] = NOW_TIME;
						$msg['user_id'] = $GLOBALS['user_info']['id'];
						$GLOBALS['db']->autoExecute(DB_PREFIX."message",$msg);
							
						update_order_cache($deal_order_item['order_id']);
							
						order_log($deal_order_item['sub_name']."申请退款，等待审核", $deal_order_item['order_id']);
							
						require_once APP_ROOT_PATH."system/model/deal_order.php";
						distribute_order($order_info['id']);
	
						output($root,1,"退款申请已提交，请等待审核");
					}
					else
					{
						output($root,0,"不允许退款");
					}
				}
				else
				{
					output($root,0,"不允许退款");
				}
			}
			else
			{
				output($root,0,"非法操作");
			}
		}
		
		
	}
	
	
	
	/**
	 * 加载退款（团购商品，团购券的页面数据加载），本接口不作数据越权验证，提交时验证
	 * 输入:
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 *
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户):判断==1
	 * page_title: string 页面标题
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 * item:array 订单商品数据
	 *   [id] => 112 int 订单表中的商品ID
		 [deal_id] => 22 int 商品ID，用于跳到商品页
		 [deal_icon] => http://192.168.1.41/o2onew/public/attachment/201502/26/11/54ee909199d43_244x148.jpg 122x74 string 商品图
		 [name] => 仅售14.9元！价值66元的雨含浴室防滑垫1张，透明材质，环保无毒，两色可选，带吸盘，选择它给您的家人多一份关爱 string 商品全名
		 [sub_name] => 雨含浴室防滑垫  string 商品短名
		 [number] => 1 int 购买数量
		 [unit_price] => 14.9 float 单价
		 [total_price] => 14.9 float 总价
		 [dp_id] => int 点评ID ，ID大于0表示已点评
		 [consume_count] => int 消费数 大于0表示可以点评
		 [delivery_status]	=>	配送状态0:未发货 1:已发货 5.无需发货
		 [is_arrival]	=>	int 是否已收货 0:未收货1:已收货2:没收到货(维权)
		 [is_refund]	=>	int 是否支持退款，由商品表同步而来，0不支持 1支持
		 [refund_status]	=>	int 退款状态 0未退款 1退款中 2已退款 3退款被拒
	   coupon_list:array 本单的团购券列表
	   Array(
	   		Array(
	   			id:int 团购券ID
	   			password:string 团购券序列号
	   			deal_type:int 发券类型 0按件发券 1按单发券，为1时显示，共可消费item[number]位
	   			time_str:string 时间状态
	   			status_str:string 团购券状态
	   			is_refund:int 是否允许退款（出现退款勾选项） 0否 1是	   		
	   		)
	   )
	    
	 */
	public function refund_coupon()
	{
	
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status'] = $user_login_status;
		}
		else
		{
	
			$root['user_login_status'] = $user_login_status;
			$item_id = intval($GLOBALS['request']['item_id']);
	
			$root['page_title'] = "退款申请";
			$root['item_id'] = $item_id;
				
			$vv = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$item_id);

			$deal_item = array();
			$deal_item['id'] = $vv['id'];
			$deal_item['deal_id'] = $vv['deal_id'];
			$deal_item['deal_icon'] = get_abs_img_root(get_spec_image($vv['deal_icon'],122,74,1));
			$deal_item['name'] = $vv['name'];
			$deal_item['sub_name'] = $vv['sub_name'];
			$deal_item['number'] = $vv['number'];
			$deal_item['unit_price'] = round($vv['unit_price'],2);
			$deal_item['total_price'] = round($vv['total_price'],2);
			$deal_item['consume_count'] = intval($vv['consume_count']);
			$deal_item['dp_id'] = intval($vv['dp_id']);
			$deal_item['delivery_status'] = intval($vv['delivery_status']);
			$deal_item['is_arrival'] =	intval($vv['is_arrival']);
			$deal_item['is_refund'] =	intval($vv['is_refund']);
			$deal_item['refund_status']	=	intval($vv['refund_status']);
			$root['item'] = $deal_item;
			
			$coupon_list = array();
			$coupon_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where is_valid > 0 and user_id = ".$GLOBALS['user_info']['id']." and order_deal_id = ".$vv['id']);
			foreach($coupon_list_rs as $k=>$v)
			{
				$coupon['id'] = $v['id'];
				$coupon['password'] = $v['password'];
				$coupon['deal_type'] = $v['deal_type'];
				
				if($v['end_time'])
				{
					$time_str = to_date($v['begin_time'],"Y-m-d")."到期";
				}
				if($v['begin_time']==0&&$v['end_time']==0)
				{
					$time_str = "无限期";
				}
				$coupon['time_str'] = $time_str;
				
				
				if($v['confirm_time']!=0)
				{
					$status_str = to_date($v['confirm_time'],"Y-m-d")."已消费";
				}
				else
				{
					if($v['refund_status']==1)
					{
						$status_str = "退款中";
					}
					elseif($v['refund_status']==2)
					{
						$status_str = "已退款";
					}
					elseif($v['refund_status']==3)
					{
						$status_str = "退款被拒";
					}
					else
					{
						if($v['is_valid']==1)
						{
							if($v['end_time']>0&&$v['end_time']<NOW_TIME)
							{
								$status_str = "已过期";
							}
							else
							{
								$status_str = "有效";
							}
						}
						else
						{
							$status_str = "作废";
						}
					}
				}
				$coupon['status_str'] = $status_str;
				
				$is_refund = 0;
				if($v['refund_status']==0&&$v['confirm_time']==0)
				{
					if($v['any_refund']==1||($v['expire_refund']==1&&$v['end_time']>0&&$v['end_time']<NOW_TIME))
					{
						$is_refund = 1;
					}
				}
				$coupon['is_refund'] = $is_refund;
				
				$coupon_list[$k] = $coupon;
			}
			$root['coupon_list'] = $coupon_list;		
		}
	
		output($root);
	
	}
	
	
	/**
	 * 执行退款接口(团购券)
	 * 输入:
	 * item_id: array 团购券ID
	 * Array(
	 * 		1,2,3
	 * )
	 * content:string 退单理由
	 *
	 * 输出
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * status: int 0失败 1成功
	 * info: string 消息
	 *
	 */
	public function do_refund_coupon()
	{
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status'] = $user_login_status;
			output($root,0,"请先登录");
		}
		else
		{
			//退单
			$item_ids = $GLOBALS['request']['item_id'];
			$content = strim($GLOBALS['request']['content']);
			$root['user_login_status'] = $user_login_status;
			if($content=="")
			{
				output($root,0,"请输入退款理由");
			}
				
			$has_success = false; //是否有一条提交成功
			foreach($item_ids as $cid)
			{
				$cid = intval($cid);
				$coupon = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where user_id = ".$GLOBALS['user_info']['id']." and id = ".$cid);
				if($coupon)
				{
					if($coupon['refund_status']==0&&$coupon['confirm_time']==0)//从未退过款可以退款，且未使用过
					{
						if($coupon['any_refund']==1||($coupon['expire_refund']==1&&$coupon['end_time']>0&&$coupon['end_time']<NOW_TIME))//随时退或过期退已过期
						{
							//执行退券
							$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set refund_status = 1 where id = ".$coupon['id']);
							$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 1 where id = ".$coupon['order_deal_id']);
							$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_status = 1 where id = ".$coupon['order_id']);
								
							$deal_order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$coupon['order_deal_id']);
								
							$msg = array();
							$msg['rel_table'] = "deal_order";
							$msg['rel_id'] = $coupon['order_id'];
							$msg['title'] = "退款申请";
							$msg['content'] = $content;
							$msg['create_time'] = NOW_TIME;
							$msg['user_id'] = $GLOBALS['user_info']['id'];
							$GLOBALS['db']->autoExecute(DB_PREFIX."message",$msg);
							update_order_cache($coupon['order_id']);
								
							order_log($deal_order_item['sub_name']."申请退一张团购券，等待审核", $coupon['order_id']);
								
							require_once APP_ROOT_PATH."system/model/deal_order.php";
							distribute_order($coupon['order_id']);
							
							$has_success = true;
						}
					}
				}
			}//end foreach
			if($has_success)
			{
				output($root,1,"提交成功，请等待审核");
			}
			else
			{
				output($root,0,"操作失败");
			}			
		}	
	}
	
	
	/**
	 * 维权页面，没收到货（实体商品的页面数据加载），本接口不作数据越权验证，提交时验证
	 * 输入:
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 *
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户):判断==1
	 * page_title: string 页面标题
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 * item:array 订单商品数据
	 *  [id] => 112 int 订单表中的商品ID
	 [deal_id] => 22 int 商品ID，用于跳到商品页
	 [deal_icon] => http://192.168.1.41/o2onew/public/attachment/201502/26/11/54ee909199d43_244x148.jpg 122x74 string 商品图
	 [name] => 仅售14.9元！价值66元的雨含浴室防滑垫1张，透明材质，环保无毒，两色可选，带吸盘，选择它给您的家人多一份关爱 string 商品全名
	 [sub_name] => 雨含浴室防滑垫  string 商品短名
	 [number] => 1 int 购买数量
	 [unit_price] => 14.9 float 单价
	 [total_price] => 14.9 float 总价
	 [dp_id] => int 点评ID ，ID大于0表示已点评
	 [consume_count] => int 消费数 大于0表示可以点评
	 [delivery_status]	=>	配送状态0:未发货 1:已发货 5.无需发货
	 [is_arrival]	=>	int 是否已收货 0:未收货1:已收货2:没收到货(维权)
	 [is_refund]	=>	int 是否支持退款，由商品表同步而来，0不支持 1支持
	 [refund_status]	=>	int 退款状态 0未退款 1退款中 2已退款 3退款被拒
	 */
	public function refuse_delivery()
	{
	
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status'] = $user_login_status;
		}
		else
		{
	
			$root['user_login_status'] = $user_login_status;
			$item_id = intval($GLOBALS['request']['item_id']);
	
			$root['page_title'] = "没收到货";
			$root['item_id'] = $item_id;
				
			$vv = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$item_id);
			$deal_item = array();
			$deal_item['id'] = $vv['id'];
			$deal_item['deal_id'] = $vv['deal_id'];
			$deal_item['deal_icon'] = get_abs_img_root(get_spec_image($vv['deal_icon'],122,74,1));
			$deal_item['name'] = $vv['name'];
			$deal_item['sub_name'] = $vv['sub_name'];
			$deal_item['number'] = $vv['number'];
			$deal_item['unit_price'] = round($vv['unit_price'],2);
			$deal_item['total_price'] = round($vv['total_price'],2);
			$deal_item['consume_count'] = intval($vv['consume_count']);
			$deal_item['dp_id'] = intval($vv['dp_id']);
			$deal_item['delivery_status'] = intval($vv['delivery_status']);
			$deal_item['is_arrival'] =	intval($vv['is_arrival']);
			$deal_item['is_refund'] =	intval($vv['is_refund']);
			$deal_item['refund_status']	=	intval($vv['refund_status']);
			$root['item'] = $deal_item;
		}
	
		output($root);
	
	}
	
	
	/**
	 * 执行维权，没收到货接口(实体商品)
	 * 输入:
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 * content:string 申请理由
	 *
	 * 输出
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * status: int 0失败 1成功
	 * info: string 消息
	 *
	 */
	public function do_refuse_delivery()
	{
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status'] = $user_login_status;
			output($root,0,"请先登录");
		}
		else
		{
			//退单
			$id = intval($GLOBALS['request']['item_id']);
			$content = strim($GLOBALS['request']['content']);
			$root['user_login_status'] = $user_login_status;
			if($content=="")
			{
				output($root,0,"请输入具体说明");
			}
						
			$user_id = intval($GLOBALS['user_info']['id']);
			require_once APP_ROOT_PATH."system/model/deal_order.php";
			$order_table_name = get_user_order_table_name($user_id);
							
			$delivery_notice = $GLOBALS['db']->getRow("select n.* from ".DB_PREFIX."delivery_notice as n left join ".$order_table_name." as o on n.order_id = o.id where n.order_item_id = ".$id." and o.user_id = ".$user_id." and is_arrival = 0 order by delivery_time desc");
			if($delivery_notice)
			{
				require_once APP_ROOT_PATH."system/model/deal_order.php";
				$res = refuse_delivery($delivery_notice['notice_sn'],$id);
				if($res)
				{
						
					$msg = array();
					$msg['rel_table'] = "deal_order";
					$msg['rel_id'] = $delivery_notice['order_id'];
					$msg['title'] = "订单维权";
					$msg['content'] = "订单维权：".$content;
					$msg['create_time'] = NOW_TIME;
					$msg['user_id'] = $GLOBALS['user_info']['id'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."message",$msg);
						
			
					output($root,1,"维权提交成功");
				}
				else
				{
					output($root,0,"维权提交失败");
				}
			}
			else
			{
				output($root,0,"订单未发货");
			}
		}
	
	
	}
	
	
	/**
	 * 确认收货接口(实体商品)
	 * 输入:
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 *
	 * 输出
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * status: int 0失败 1成功
	 * info: string 消息
	 *
	 */
	public function verify_delivery()
	{
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED)
		{
			$root['user_login_status'] = $user_login_status;
			output($root,0,"请先登录");
		}
		else
		{

			$root['user_login_status'] = $user_login_status;
			
			$id = intval($GLOBALS['request']['item_id']);
			$user_id = intval($GLOBALS['user_info']['id']);
			require_once APP_ROOT_PATH."system/model/deal_order.php";
			$order_table_name = get_user_order_table_name($user_id);
				
			$delivery_notice = $GLOBALS['db']->getRow("select n.* from ".DB_PREFIX."delivery_notice as n left join ".$order_table_name." as o on n.order_id = o.id where n.order_item_id = ".$id." and o.user_id = ".$user_id." and is_arrival = 0 order by delivery_time desc");
			if($delivery_notice)
			{
				require_once APP_ROOT_PATH."system/model/deal_order.php";
				$res = confirm_delivery($delivery_notice['notice_sn'],$id);
				if($res)
				{					
					output($root,1,"确认收货成功");
				}
				else
				{
					output($root,0,"确认收货失败");
				}
			}
			else
			{
				output($root,0,"订单未发货");
			}
		}
	}
	
	
	/**
	 * 快递查询接口
	 * 输入:
	 * item_id: int 订单商品表中的商品ID（order_item_id，非商品的ID）
	 *
	 * 输出
	 * status: int 0失败 1成功
	 * info: string 消息
	 * url: 快递查询的手机端接口地址(仅status为1返回)
	 */
	public function check_delivery()
	{
		$id = intval($GLOBALS['request']['item_id']);
		$user_id = intval($GLOBALS['user_info']['id']);
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		$order_table_name = get_user_order_table_name($user_id);
		
		$delivery_notice = $GLOBALS['db']->getRow("select n.* from ".DB_PREFIX."delivery_notice as n left join ".$order_table_name." as o on n.order_id = o.id where n.order_item_id = ".$id." and o.user_id = ".$user_id." order by delivery_time desc");
		if($delivery_notice)
		{
			$express_id = intval($delivery_notice['express_id']);
			$typeNu = strim($delivery_notice["notice_sn"]);
			$express_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."express where is_effect = 1 and id = ".$express_id);
			$express_info['config'] = unserialize($express_info['config']);
			$typeCom = strim($express_info['config']["app_code"]);
			if(isset($typeCom)&&isset($typeNu))
			{
				$root['url'] = "http://m.kuaidi100.com/index_all.html?type=".$typeCom."&postid=".$typeNu;
				output($root);
			}
			else
			{
				output("",0,"无效的快递查询");
			}
		}
		else
		{
			output("",0,"非法操作");
		}
	}
	
}
?>