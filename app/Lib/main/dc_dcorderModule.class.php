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
	public function index()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}	
		
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;

		$user_id = $GLOBALS['user_info']['id'];
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		$role='user';
		timeout_accept_order_process($role,$user_id);
		timeout_pay_order_process($role,$user_id);
		//获取外卖订单列表
		$list = $GLOBALS['db']->getAll("select do.* , sl.preview from ".DB_PREFIX."dc_order as do left join ".DB_PREFIX."supplier_location as sl on do.location_id=sl.id where do.is_rs=0 and do.user_id=".$user_id ." order by id desc limit ".$limit);
		foreach($list as $k=>$v)
		{
			$list[$k]['create_time'] = to_date($v['create_time']);
			$list[$k]['pay_amount'] = format_price($v['pay_amount']);
			$list[$k]['total_price'] = format_price($v['total_price']);
			$list[$k]['location_url'] = url("index","dcbuy",array('lid'=>$v['location_id']));
			$list[$k]['dp_url'] = url("index","dcreview",array('location_id'=>$v['location_id'],'id'=>$v['id']));

			if($list[$k]['order_delivery_time']==1){
				$list[$k]['order_delivery_time'] = $GLOBALS['lang']['DC_DELIVERY_RIGHT_NOW'];
			}else{
				$list[$k]['order_delivery_time']=to_date($list[$k]['order_delivery_time'],'Y-m-d').'<br/>'.to_date($list[$k]['order_delivery_time'],'H:i');
			}
			if($v['order_menu'])
			{	$order_menu_list=unserialize($v['order_menu']);
				$list[$k]['order_menu'] = $order_menu_list['menu_list']['cart_list'];				
			}
			else
			{
				$order_id = $v['id'];
				$list[$k]['order_menu'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_order_menu where order_id = ".$order_id);
			}
			
			$list[$k]['c'] = count($list[$k]['order_menu']);
			foreach($list[$k]['order_menu'] as $kk=>$vv)
			{
				$list[$k]['order_menu'][$kk]['total_price'] = format_price($vv['total_price']);
				//$deal_info = load_auto_cache("deal",array("id"=>$vv['deal_id']));
				//$list[$k]['order_menu'][$kk]['url'] = $deal_info['url'];
			}
		}
		
			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where is_rs=0 and user_id=".$user_id);

		$page = new Page($count,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("page_title","我的外卖");
		assign_uc_nav_list();
		$GLOBALS['tmpl']->display("dc/uc/dcorder_index.html");
	}
	
	/**
	 * 确认收货
	 */
	public function verify_delivery()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		else
		{
			require_once APP_ROOT_PATH."system/model/dc.php";
			$id = intval($_REQUEST['id']);
			$result = dc_confirm_delivery($id);
			ajax_return($result);
		}
	}
	
	
	public function refuse_delivery()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		else
		{
			$id = intval($_REQUEST['id']);
			$user_id = intval($GLOBALS['user_info']['id']);
			require_once APP_ROOT_PATH."system/model/deal_order.php";
			$order_table_name = get_user_order_table_name($user_id);
			$content = strim($_REQUEST['content']);

			if($content=="")
			{
				$data['status'] = 0;
				$data['info'] = "请输入具体说明";
				ajax_return($data);
			}
			
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
					
					$data['status'] = true;
					$data['info'] = "维权提交成功";
					ajax_return($data);
				}
				else
				{
					$data['status'] = 0;
					$data['info'] = "维权提交失败";
					ajax_return($data);
				}
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = "订单未发货";
				ajax_return($data);
			}
		}
	}
	
	/**
	 * 删除订单
	 */
	public function cancel()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		else
		{
			$id = intval($_REQUEST['id']);
			$order_info = $GLOBALS['db']->getRow("select do.* , sl.tel as location_tel from ".DB_PREFIX."dc_order as do left join ".DB_PREFIX."supplier_location as sl on do.location_id=sl.id where do.id = ".$id." and do.type_del = 0 and do.user_id = ".$GLOBALS['user_info']['id']);
			if($order_info)
			{	require_once APP_ROOT_PATH."system/model/dc.php";

					
				if($order_info['order_status']==1 || $order_info['type_del']==1 || $order_info['is_cancel']!=0){
					//订单已结单，或者订单已删除，或者订单已取消，不允许取消订单
					$data['status'] = 3;
					$data['info'] = "不允许取消订单";
					ajax_return($data);
				}else{
					if($order_info['pay_status']==0 and $order_info['payment_id']==0 )   //在线支付，订单未付款成功，直接取消订单
					{
						dc_order_close($id,1,$order_info['user_name'].'会员取消订单');
						$data['status'] = 1;
						$data['info'] = "订单删除成功";
						ajax_return($data);
					}elseif($order_info['confirm_status']==0 && $order_info['payment_id']==1){  //货到付款，未接单，直接取消订单
						dc_order_close($id,1,$order_info['user_name'].'会员取消订单');
						$data['status'] = 1;
						$data['info'] = "订单删除成功";
						ajax_return($data);
							
					}
					elseif($order_info['pay_status']==1 && $order_info['confirm_status']==0)
					{	//订单已付款成功，商家未接单，直接取消订单
						dc_order_close($id,1,$order_info['user_name'].'会员取消订单');
						$data['status'] = 1;
						$data['info'] = "订单删除成功";
						ajax_return($data);
					}elseif($order_info['order_status']==0 && $order_info['confirm_status']==1){
						//订单已付款成功，商家已接单，线下和商家联系沟通
						$data['status'] = 2;
						$data['info'] = "商户已经接单，如需取消订单请联系 ".$order_info['location_name']."<br/><br/>客服电话：".$order_info['location_tel'];
						ajax_return($data);
					}	
				}
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = "订单不存在";
				ajax_return($data);
			}
		}
	}
	
	
	/**
	 * 查看订单内容
	 */
	public function view()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
		
		$GLOBALS['tmpl']->assign("page_title","我的外卖");
		assign_uc_nav_list();
		
		$id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where type_del = 0 and user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
		if($order_info)
		{
			if($order_info['order_delivery_time']==1){
				$order_info['order_delivery_time'] = $GLOBALS['lang']['DC_DELIVERY_RIGHT_NOW'];
			}else{
				$order_info['order_delivery_time']=to_date($order_info['order_delivery_time'],'Y-m-d').'<br/>'.to_date($order_info['order_delivery_time'],'H:i');
			}
			if($order_info['order_menu'])
			{	$order_menu_list=unserialize($order_info['order_menu']);
			$order_info['order_menu'] = $order_menu_list['menu_list']['cart_list'];
			}
			else
			{
				$order_id = $order_info['id'];
				$order_info['order_menu'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_order_menu where order_id = ".$order_id);
			}
			
			
			$order_info['create_time'] = to_date($order_info['create_time']);
			$order_info['pay_amount_format'] = format_price($order_info['pay_amount']);
			$order_info['total_price_format'] = format_price($order_info['total_price']);
			$order_info['location_url'] = url("index","dcbuy",array('lid'=>$order_info['location_id']));
			$order_info['c'] = count($order_info['order_menu']);
			
			$order_info['promote_str']=unserialize($order_info['promote_str']);
			
			$GLOBALS['tmpl']->assign("order_info",$order_info);
			
			//输出收款单日志
			$payment_list_res = load_auto_cache("cache_payment");
			foreach($payment_list_res as $k=>$v)
			{
				$payment_list[$v['id']] = $v;
			}
			
			$payment_notice_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_info['id']." and is_paid = 1 order by create_time desc");
			foreach($payment_notice_list as $k=>$v)
			{
				$payment_notice_list[$k]['payment'] = $payment_list[$v['payment_id']];
			}
			$GLOBALS['tmpl']->assign("payment_notice_list",$payment_notice_list);
			
			
			//订单日志
			$order_logs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_order_log where order_id = ".$order_info['id']." order by id desc");
			$GLOBALS['tmpl']->assign("order_logs",$order_logs);
			
			$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
			$GLOBALS['tmpl']->display("dc/uc/dcorder_view.html");
		}
		else
		{
			showErr("订单不存在");
		}
	}
	
	
	public function out_time()
	{

		$order = $GLOBALS['db']->getRow("select id , location_id from ".DB_PREFIX."dc_order where id = ".intval($_REQUEST['id']));
		$GLOBALS['tmpl']->assign("order",$order);
		$GLOBALS['tmpl']->display("dc/dc_order_out_time.html");
	}
	
	public function dc_reminder(){
		
		
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$result['status'] = 1000;
			ajax_return($result);
		}else{
			$id = intval($_REQUEST['id']);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where type_del = 0 and confirm_status!=2 and user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
	
			if($order_info)
			{
				//催单时间，每次要间隔15分钟,以下SQL是查询有没有15分钟之内的催单记录
				$dc_reminder=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_reminder where order_id=".$id." and ".NOW_TIME." - create_time < 900 order by create_time desc limit 1");
				if($dc_reminder){
					//$dc_reminder存在，不可以催
					$result['status'] = 0;
					$result['info'] = "亲,您催的太快了,15分钟催一次哦!";
					ajax_return($result);
					
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
						$result['status'] = 1;
						$result['info'] = "催单成功,请耐心等待!";
						ajax_return($result);
					}else{
						$result['status'] = 0;
						$result['info'] = "催单失败,请重新催单!";
						ajax_return($result);
					}
				}
				
			}else{
				
				$result['status'] = 0;
				$result['info'] = "非法操作";
				ajax_return($result);
			}
		}
	}
	
	

}
?>