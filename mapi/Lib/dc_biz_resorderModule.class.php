<?php 
/**
 * 
 * 商家预订
 * 
 */
require_once APP_ROOT_PATH."system/model/dc.php";
class dc_biz_resorderModule extends MainBaseModule
{
    


	/**
	 * 	商家预订新订单
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz_resorder&r_type=2&page=1
	 * 	输入:
	 *	page:int 当前的页数，没输入些参数时，默认为第一页
	 *  lid：int 门店ID
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * dc_order,array, 外卖订单详细页，结构如下
	 * total_price:订单总额
	 * pay_amount:已支付金额
	 * pay_price:定金合计
	 * payment_fee：手续费
	 * account_money：余额支付金额
	 * ecv_money：红包支付的金额
	 * promote_amount：优惠的金额
	 * pay_status：支付状态: 0未支付 1已支付
	 * location_id:int门店ID
	 * order_status：订单的结单状态标识，结单后的订单允许删除,0:否 1:是(结单条件:1.用户确认到货,2.商家在超期后帮用户确认到货,3.用户退款被确认)
	 * confirm_status：订单商家确认状态，0:未确认（未接单，用户未付款可以取消，已付款可直接退款），1.已确认（商家已接单,客户可与商家联系，申请退款），2.已配送，或者预订订单验证成功
	 * location_name：商家名称
	 * payment_id:0为在线支付，1为货到付款
	 * consignee:联系人
	 * mobile：手机号
	 * dc_comment：订单备注
	 * invoice：发票信息
	 * create_time_format:格式化后的下单时间
	 * promote_str：array:array  此订单享受的优惠信息
	 * order_state返回订单的状态  state_format为状态的文字描述 ，state为状态，代表意义如下：
	 *  1、待支付
		2、待接单
		3、已接单
		4、已完成
		5、订单关闭
		6.退款申请中
		7.已退款
		8.退款驳回
	 * order_menu：array:array菜单信息
	 *  Array
        (
            [0] => Array
                (
                    [id] => 69
                    [order_sn] => 2015081412594639
                    [create_time] => 1439499586
                    [confirm_status] => 0
                    [pay_status] => 1
                    [total_price] => 89.0000
                    [package_price] => 1.0000
                    [delivery_price] => 1.0000
                    [payment_fee] => 0.0000
                    [promote_str] => Array
                        (
                        )

                    [pay_amount] => 89.0000
                    [ecv_money] => 0.0000
                    [account_money] => 89.0000
                    [payment_id] => 0
                    [order_menu] => Array
                        (
                            [cart_list] => Array
                                (
                                    [0] => Array
                                        (
                                            [id] => 260
                                            [session_id] => uf5jq74qf5iq4rcke6fuhmt405
                                            [user_id] => 71
                                            [location_id] => 41
                                            [supplier_id] => 43
                                            [name] => 饕餮鸡排饭
                                            [num] => 1
                                            [unit_price] => 23.0000
                                            [total_price] => 23.0000
                                            [menu_id] => 48
                                            [table_time_id] => 0
                                            [table_time] => 0
                                            [cart_type] => 1
                                            [add_time] => 1439499579
                                            [is_effect] => 1

                                        )
                                   )
                            )
                    [consignee] => 王明
                    [mobile] => 15158789965
                    [dc_comment] => 
                    [invoice] => 
                    [promote_amount] => 0.0000
                    [create_time_format] => 2015-08-14 12:59
                    [pay_price] => 89                  
			)
		)	
	 */  
	
    
	public function index()
	{	

		/*初始化*/
		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$root['page_title'] = "预订新订单";
			
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
		

		$lid = intval($GLOBALS['request']['lid']);
		$page = intval($GLOBALS['request']['page']);
		if($page==0){
			$page = 1;
		}
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		if(!in_array($lid, $account_info['location_ids']))
		{
			output($root,0,"没有管理权限");
		}
		
		//返回商户权限
		if(!check_module_auth("dcresorder")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
		$from = strim($GLOBALS['request']['from']);
		
		if($from=='wap'){
			$type='wap';
		}
		
		$rows=array("日","一","二","三","四","五","六");
		$dc_order=$GLOBALS['db']->getAll("select id,order_sn,location_id,create_time,confirm_status,pay_status,total_price,payment_fee,promote_str,pay_amount,ecv_money,is_cancel,refund_status,is_rs,
				account_money,payment_id,order_menu,rs_price,consignee,mobile,dc_comment,invoice,promote_amount from ".DB_PREFIX."dc_order where confirm_status=0 and is_cancel=0 and refund_status=0 and order_status=0 and type_del=0 and is_rs=1 and pay_status=1 and location_id=".$lid." order by id desc limit ".$limit);
		foreach($dc_order as $k=>$v){
			
			$dc_order[$k]['promote_str']=unserialize($v['promote_str'])?unserialize($v['promote_str']):array();
			$menu_list=array();
			$menu_list=unserialize($v['order_menu']);
			
			
			foreach($menu_list['rs_list']['cart_list'] as $kk=>$vv){
				$rs_info=array();
				$rs_info['name']=$vv['name'];
				$rs_info['table_time']=$vv['table_time'];
				$rs_info['table_time_format']=to_date($vv['table_time'],"Y-m-d").' 星期'.$rows[to_date($vv['table_time'],"w")].' '.to_date($vv['table_time'],"H:i");
				$menu_list['rs_list']['cart_list'][$kk]=$rs_info;
			}
			
			$menu_list['rs_list']['cart_list']=array_values($menu_list['rs_list']['cart_list']);
			
			foreach($menu_list['menu_list']['cart_list'] as $kk=>$vv){
				unset($menu_list['menu_list']['cart_list'][$kk]['icon']);
				unset($menu_list['menu_list']['cart_list'][$kk]['url']);
			}
			$menu_list['menu_list']['cart_list']=array_values($menu_list['menu_list']['cart_list']);
			if($menu_list['menu_list']['total_data']['total_price'] > $menu_list['rs_list']['total_data']['total_price']){
				$dc_order[$k]['pay_price']=$menu_list['menu_list']['total_data']['total_price'];
			}else{
				$dc_order[$k]['pay_price']=$v['rs_price'];
			}
			
			$dc_order[$k]['order_menu']=$menu_list;
			$dc_order[$k]['create_time_format']=to_date($v['create_time'],"Y-m-d H:i");
			$dc_order[$k]['order_state']=get_order_state($v);
			$dc_order[$k]['biz_order_state']=get_biz_order_state($v,$type);
		}
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where  confirm_status=0 and is_cancel=0 and refund_status=0 and order_status=0 and type_del=0 and is_rs=1 and pay_status=1 and location_id=".$lid);
		$page_total = ceil($total/$page_size);
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$total);
		$root['lid']=$lid;
		$root['dc_order']=$dc_order;
	
		output($root);
	}
	


	/**
	 * 	商家预订订单记录
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz_resorder&r_type=2&page=1&act=order
	 * 	输入:
	 *	page:int 当前的页数，没输入些参数时，默认为第一页
	 *  lid：int 门店ID
	 *  date:string 筛选的日期,如：2015-08-15，如果没有传入其参数，则默认当天
	 *  
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * dc_order,array, 外卖订单详细页，结构如下
	 * total:订单个数
	 * total_price:订单总额
	 * pay_amount:已支付金额
	 * pay_price:定金合计
	 * payment_fee：手续费
	 * account_money：余额支付金额
	 * ecv_money：红包支付的金额
	 * promote_amount：优惠的金额
	 * pay_status：支付状态: 0未支付 1已支付
	 * location_id:int门店ID
	 * order_status：订单的结单状态标识，结单后的订单允许删除,0:否 1:是(结单条件:1.用户确认到货,2.商家在超期后帮用户确认到货,3.用户退款被确认)
	 * confirm_status：订单商家确认状态，0:未确认（未接单，用户未付款可以取消，已付款可直接退款），1.已确认（商家已接单,客户可与商家联系，申请退款），2.已配送，或者预订订单验证成功
	 * location_name：商家名称
	 * payment_id:0为在线支付，1为货到付款
	 * consignee:联系人
	 * mobile：手机号
	 * dc_comment：订单备注
	 * invoice：发票信息
	 * create_time_format:格式化后的下单时间
	 * promote_str：array:array  此订单享受的优惠信息
	 * order_state返回订单的状态  state_format为状态的文字描述 ，state为状态，代表意义如下：
	 *  1、待支付
	 2、待接单
	 3、已接单
	 4、已完成
	 5、订单关闭
	 6.退款申请中
	 7.已退款
	 8.退款驳回
	 * order_menu：array:array菜单信息
	  Array
        (
            [0] => Array
                (
                    [id] => 76
                    [order_sn] => 2015081403201268
                    [location_id] => 41
                    [create_time] => 1439508012
                    [confirm_status] => 1
                    [pay_status] => 1
                    [total_price] => 150.0000
                    [payment_fee] => 0.0000
                    [promote_str] => Array
                        (
                        )

                    [pay_amount] => 150.0000
                    [ecv_money] => 0.0000
                    [account_money] => 150.0000
                    [payment_id] => 0
                    [order_menu] => Array
                        (
                            [rs_list] => Array
                                (
                                    [cart_list] => Array
                                        (
                                            [0] => Array
                                                (
                                                    [name] => 散桌8-10人桌
                                                    [table_time] => 1439527500
                                                    [table_time_format] => 2015-08-14 星期五 20:45
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
                                    [cart_list] => 
                                )

                        )

                    [rs_price] => 150.0000
                    [consignee] => 王明
                    [mobile] => 15158789965
                    [dc_comment] => 
                    [invoice] => 
                    [promote_amount] => 0.0000
                    [pay_price] => 150.0000
                    [create_time_format] => 2015-08-14 15:20
                    [order_state] => Array
                        (
                            [state] => 3
                            [state_format] => 已接单
                        )

                )
           )  
	 */
	
	
	public function order()
	{
	
		/*初始化*/
		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$root['page_title'] = "预订订单记录";
			
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
	
		$date = strim($GLOBALS['request']['date']);
		if(!$date){
			$date=to_date(NOW_TIME,"Y-m-d");
		}
		
		$begin_time=to_timespan($date);
		$end_time=$begin_time+3600*24-1;
		
		$lid = intval($GLOBALS['request']['lid']);
		$page = intval($GLOBALS['request']['page']);
		$from = strim($GLOBALS['request']['from']);
		if($from=='wap'){
			$type='wap';
		}
		if($page==0){
			$page = 1;
		}
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		if(!in_array($lid, $account_info['location_ids']))
		{
			output($root,0,"没有管理权限");
		}
	
		//返回商户权限
		if(!check_module_auth("dcresorder")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
		$rows=array("日","一","二","三","四","五","六");
		$dc_order=$GLOBALS['db']->getAll("select id,is_cancel,refund_status,order_sn,location_id,create_time,confirm_status,pay_status,total_price,payment_fee,promote_str,pay_amount,ecv_money,is_rs,
				account_money,payment_id,order_menu,rs_price,consignee,mobile,dc_comment,invoice,promote_amount from ".DB_PREFIX."dc_order where is_rs=1 and pay_status=1 and location_id=".$lid." and create_time between ".$begin_time." and ".$end_time." order by id desc limit ".$limit);
		foreach($dc_order as $k=>$v){
				
			$dc_order[$k]['promote_str']=unserialize($v['promote_str'])?unserialize($v['promote_str']):array();
			$menu_list=array();
			$menu_list=unserialize($v['order_menu']);
				
				
			foreach($menu_list['rs_list']['cart_list'] as $kk=>$vv){
				$rs_info=array();
				$rs_info['name']=$vv['name'];
				$rs_info['table_time']=$vv['table_time'];
				$rs_info['table_time_format']=to_date($vv['table_time'],"Y-m-d").' 星期'.$rows[to_date($vv['table_time'],"w")].' '.to_date($vv['table_time'],"H:i");
				$menu_list['rs_list']['cart_list'][$kk]=$rs_info;
			}
				
			$menu_list['rs_list']['cart_list']=array_values($menu_list['rs_list']['cart_list']);
				
			foreach($menu_list['menu_list']['cart_list'] as $kk=>$vv){
				unset($menu_list['menu_list']['cart_list'][$kk]['icon']);
				unset($menu_list['menu_list']['cart_list'][$kk]['url']);
			}
			$menu_list['menu_list']['cart_list']=array_values($menu_list['menu_list']['cart_list']);
			if($menu_list['menu_list']['total_data']['total_price'] > $menu_list['rs_list']['total_data']['total_price']){
				$dc_order[$k]['pay_price']=$menu_list['menu_list']['total_data']['total_price'];
			}else{
				$dc_order[$k]['pay_price']=$v['rs_price'];
			}
				
			$dc_order[$k]['order_menu']=$menu_list;
			$dc_order[$k]['create_time_format']=to_date($v['create_time'],"Y-m-d H:i");
			$dc_order[$k]['order_state']=get_order_state($v);
			$dc_order[$k]['biz_order_state']=get_biz_order_state($v,$type);
		}
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where is_rs=1 and pay_status=1 and location_id=".$lid." and create_time between ".$begin_time." and ".$end_time);
		$page_total = ceil($total/$page_size);
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$total);
		
		$today=to_timespan(to_date(NOW_TIME,"Y-m-d"));
		
		$data_arr=array();
		for($i=0;$i<10;$i++){
			$data_arr[]=to_date($today-3600*24*$i,"Y-m-d");
		
		}
		$root['lid']=$lid;
		$root['data_arr']=$data_arr;
		$root['total']=$total;
		$root['dc_order']=$dc_order;
		$root['date']=$date;
		output($root);
	}
	

	/**
	 * 	商家预订订单接单接口
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz_resorder&id=78&r_type=2&act=accept_order
	 * 	输入:
	 *	id:int 订单
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *  
	 *  status：为接单的操作的状态，status=0,接单失败;status=1,接单成功
	 *  info:返回的提示信息
	 */  
	
	public function accept_order()
	{	
		
		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
			
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
		
		//返回商户权限
		if(!check_module_auth("dcresorder")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
		
		
		$id = intval($GLOBALS['request']['id']);
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where type_del = 0 and id=".$id);

		if($order_info){
			
		if(!in_array($order_info['location_id'], $account_info['location_ids']))
		{
			output($root,0,"没有管理权限");
		}
			

		//商家接单时间，要在用户预订时间前半小时
		$order_info['order_menu']=unserialize($order_info['order_menu']);
		foreach($order_info['order_menu']['rs_list']['cart_list'] as $k=>$v){
			$rs_time=$v['table_time'];
		}
		
		if($rs_time < NOW_TIME + 1800){  //商家接单时间，要在用户预订时间前半小时，否则关闭订单
			//订单关闭
			require_once  APP_ROOT_PATH."system/model/dc.php";
			$close_reason='商家接单超时，订单关闭';
			dc_order_close($id,2,$close_reason);		
			output($root,0,"请在用户预订时间前半小时接单，接单超时，订单关闭");
			 
		}else{
			$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set confirm_status = 1 where id = ".$id);
			$rs=$GLOBALS['db']->affected_rows();
	
			if($rs> 0){
				require_once  APP_ROOT_PATH."system/model/dc.php";
		
				dc_send_user_coupon_sms($id);		
				output($root,1,"接单成功");
				 
			}else{
				output($root,0,"已接单，不用重复操作");
				 
			}
		}

	}else{
			output($root,0,"订单不存在");
	}
}
	
	/**
	 * 	商家预订订单关闭接口
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz_resorder&id=2&r_type=2&act=close_order
	 * 	输入:
	 *	id:int 订单ID
	 *  close_reason:string 关闭订单的原因
	 *  
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *
	 *  status：为关闭订单的操作的状态，status=0,关闭订单失败;status=1,关闭订单成功
	 *  info:返回的提示信息
	 */
	
	public function close_order()
	{

		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
			
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
		//返回商户权限
		if(!check_module_auth("dcresorder")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
		$id = intval($GLOBALS['request']['id']);
		$close_reason=strim($GLOBALS['request']['close_reason']);

		if($close_reason==''){
			output($root,0,"请填写关闭订单原因");
		}
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where type_del = 0 and id=".$id);
		
		
		if(!$order_info)
		{
	        output($root,0,"订单不存在");
		}
		
		if(!in_array($order_info['location_id'], $account_info['location_ids']))
		 {
	        output($root,0,"没有管理权限");
	   	 }
	   	 
	   	 require_once  APP_ROOT_PATH."system/model/dc.php";
	 	 dc_order_close($id,2,$close_reason);
	     output($root,1,"关闭订单成功");
		
	}
	

	


}
?>