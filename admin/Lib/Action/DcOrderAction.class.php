<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

class DcOrderAction extends CommonAction{

	public function index(){
		
				$reminder = M("RemindCount")->find();
		$reminder['order_count_time'] = NOW_TIME;
		$reminder['refund_count_time'] = NOW_TIME;
		$reminder['retake_count_time'] = NOW_TIME;
		M("RemindCount")->save($reminder);
		
		
		if(!isset($_REQUEST['order_status']))
		{
			$_REQUEST['order_status'] = -1;
		}
		
		$where = " 1=1 ";
		if(intval($_REQUEST['id'])>0)
		$where .= " and id = ".intval($_REQUEST['id']);
		//定义条件
		$where.= " and  type_del = 0 and is_rs = 0";
		if(strim($_REQUEST['user_name'])!='')
			$where.=" and user_name like '%".strim($_REQUEST['user_name'])."%'";
		if(strim($_REQUEST['order_sn'])!='')
		{
			$where.= " and order_sn like '%".strim($_REQUEST['order_sn'])."%' ";
		}
		if(strim($_REQUEST['location_name'])!='')
		{
			$where.= " and location_name like '%".strim($_REQUEST['location_name'])."%' ";
		}

		$order_status=intval($_REQUEST['order_status']);
		if($order_status==0)
		{	//支付中
			$where.= " and is_cancel=0 and refund_status=0 and pay_status = 0";
		}elseif($order_status==1){
			//待接单,在线支付要已支付，货到付款，也可以待接单
			$where.= " and is_cancel=0 and refund_status=0 and ((pay_status = 1 and payment_id = 0 and confirm_status = 0) or (pay_status = 0 and payment_id = 1 and confirm_status = 0))";	
		}elseif($order_status==2){
			//已接单
			$where.= " and is_cancel=0 and refund_status=0 and confirm_status =1";	
		}elseif($order_status==3){
			//已完成
			$where.= " and is_cancel=0 and refund_status=0 and confirm_status =2";	
		}elseif($order_status==4){
			//已结单
			$where.= " and is_cancel=0 and refund_status=0 and confirm_status =2 and order_status=1";	
		}elseif($order_status==5){
			//申请退款
			$where.= " and is_cancel=0 and refund_status=1";	
		}elseif($order_status==6){
			//订单关闭
			$where.= " and (is_cancel >0 or refund_status > 1)";	
		}
	
	
		//关于列表数据的输出
		if (isset ( $_REQUEST ['_order'] ) && $_REQUEST ['_order']!='menu') {
			
			$order = $_REQUEST ['_order'];
			
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : 'id';
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		
	
		
		$count = M("DcOrder")
				->where($where)
				->count();
		
		if ($count > 0) {
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据

			$voList = M("DcOrder")
				->where($where)				
				->field('*')
				->order( $order ." ". $sort)
				->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
			
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			
			foreach($voList as $k=>$v){
				
				$order_menu=unserialize($v['order_menu']);				
				$menu_list=$order_menu['menu_list'];
				$m_cart_list=$menu_list['cart_list'];
				
					$voList[$k]['menu']="";
				foreach($m_cart_list as $km=>$vm){
					
					$voList[$k]['menu'].="<span>".trim($vm['name'])."*".$vm['num']."</span>";
					
				}		
				
			}
			
				
			
			//分页显示
				
			$page = $p->show ();
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
			$this->assign ( 'list', $voList );
			$this->assign ( 'sort', $sort );
			$this->assign ( 'order', $_REQUEST ['_order']?$_REQUEST ['_order']:'id' );
			$this->assign ( 'sortImg', $sortImg );
			$this->assign ( 'sortType', $sortAlt );
			$this->assign ( "page", $page );
			$this->assign ( "nowPage",$p->nowPage);
		}

		//end 
		$this->display ();
		return;

	}
	
	
	
	public function view_order()
	{	
		$id = intval($_REQUEST['id']);
		$order_info = M("DcOrder")->where("id=".$id." and type_del = 0 and is_rs = 0")->find();
		
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		else
		{
			
			$order_info['order_price']=$order_info['total_price']-$order_info['payment_fee'];//订单总额
			
			$order_info['pay_price']=$order_info['total_price']-$order_info['ecv_money']-$order_info['promote_amount'];
			$order_info['paid_price']=$order_info['online_pay']+$order_info['account_money'];
			$order_info['now']=NOW_TIME;
			$notice_info= $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where order_id = ".$id." and is_paid=1 and order_type=1");//付款单号信息

			foreach($notice_info as $k=>$v){	
				$notice_info[$k]['name']=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."payment where id = ".$v['payment_id']);
			}
			
			$order_menu=unserialize($order_info['order_menu']);
			$order_info['promote_str']=unserialize($order_info['promote_str']);
			$order_info['menu_list']=$order_menu['menu_list'];

		//输出订单日志
		$log_list = M("DcOrderLog")->where("order_id=".$id)->order("log_time desc")->findAll();
		$this->assign("log_list",$log_list);
		
		if($order_info['order_delivery_time']==1){
			$over_time=$order_info['create_time']+3600*4;	
		}elseif($order_info['order_delivery_time']>1){
			$over_time=$order_info['order_delivery_time']+3600*4;	
		}
		
		if(NOW_TIME >= $over_time){
			$is_show_confirm_buttom=1;
		}else{
			$is_show_confirm_buttom=0;
			
		}
		$this->assign("close_link",U("DcOrder/close_order",array("id"=>$id)));
		$accept_order_url=U("DcOrder/accept_order");
		$confirm_order_url=U("DcOrder/over_order");
		$this->assign ( "accept_order_url", $accept_order_url );
		$this->assign ( "confirm_order_url", $confirm_order_url );
		$this->assign ( "notice_info", $notice_info );		
		$this->assign ( "is_show_confirm_buttom", $is_show_confirm_buttom );
		$this->assign ( "order_info", $order_info );
		}
		$this->display();
	}

	
	
	public function order_incharge()
		{
		$order_id  = intval($_REQUEST['id']);
		$order_info = M("DcOrder")->where("id=".$order_id." and type_del = 0 and is_rs = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}

		$payment_id = 0;			
		$GLOBALS['user_info']['id'] = $order_info['user_id'];
		$order_info['promote_str']=unserialize($order_info['promote_str']);
		$order_info['pay_price']=$order_info['total_price']-$order_info['pay_amount']-$order_info['promote_amount'];
		$payment_list = M("Payment")->where("is_effect = 1 and class_name = 'Account'")->findAll();
		$this->assign("payment_list",$payment_list);
		$this->assign("user_money",M("User")->where("id=".$order_info['user_id'])->getField("money"));
		$this->assign("order_info",$order_info);
		$this->display();
		}	

	
	
	public function do_incharge()
	{
		$order_id  = intval($_REQUEST['order_id']);
		$payment_id = intval($_REQUEST['payment_id']);
		$payment_info = M("Payment")->getById($payment_id);
		$memo = $_REQUEST['memo'];
		$order_info = M("DcOrder")->where("id=".$order_id." and type_del = 0")->find();	
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		
		
		$payment = intval($_REQUEST['payment_id']);		
		$GLOBALS['user_info']['id'] = $order_info['user_id'];
		require_once APP_ROOT_PATH."system/model/dc.php";
		
		$payment=0;
		$order_menu=unserialize($order_info['order_menu']);
		
		$location_dc_table_cart=$order_menu['rs_list'];
		$location_dc_cart=$order_menu['menu_list'];
		$dc_type=1;
		
		$result = dc_count_buy_total($order_info['location_id'],$location_dc_table_cart,$location_dc_cart,$payment,$dc_type,$consignee_id,$payment_id,$account_money=0,$all_account_money=0,$ecvsn,$ecvpassword, $bank_id,$order_info['account_money'],$order_info['ecv_money'],$order_info['promote_amount']);
		
		$user_money = M("User")->where("id=".$order_info['user_id'])->getField("money");
		//$pay_amount = $order_info['deal_total_price']+ $order_info['delivery_fee']-$order_info['account_money']-$order_info['ecv_money']+$payment_info['fee_amount'];
		$pay_amount = $result['pay_price'];
		
		
		if($payment_info['class_name']=='Account'&&$user_money<$pay_amount) 
		$this->error(l("ACCOUNT_NOT_ENOUGH"));

		echo $notice_id = make_dcpayment_notice($pay_amount,$order_id,$payment_id,$memo);
		
		$order_info['total_price'] = $result['pay_total_price'];
		$order_info['payment_fee'] = $result['payment_fee'];  
		//$order_info['payment_id'] = $payment_info['id'];
		//$order_info['update_time'] = NOW_TIME;
		M("DcOrder")->save($order_info);
		
		$payment_notice = M("PaymentNotice")->getById($notice_id);
	
		if($payment_info['class_name']=='Account')
		{
			//余额支付
			require_once APP_ROOT_PATH."system/dc_payment/Dc_Account_payment.php";
			$account_payment = new Dc_Account_payment();
			$account_payment->get_payment_code($notice_id);
		

	
			dcorder_paid($order_id);
			$msg = sprintf(l("MAKE_PAYMENT_NOTICE_LOG"),$order_info['order_sn'],$payment_notice['notice_sn']);
			dc_order_log($msg.$_REQUEST['memo'],$order_id);
			$this->assign("jumpUrl",U("DcOrder/view_order",array("id"=>$order_id)));
			$this->success(l("ORDER_INCHARGE_SUCCESS"));
		}
	}
	
	
	public function close_tip()
	{
		$id = intval($_REQUEST['id']);
		$form_url=U("DcOrder/close_order",array("id"=>$id));
		$this->assign("form_url",$form_url);
		$this->assign("is_rs",0);
		$this->display();

	}
	
	
	public function close_order()
	{

		$id = intval($_REQUEST['id']);
		$close_reason=strim($_REQUEST['close_reason'])==''?strim($_REQUEST['close_reason_text']):strim($_REQUEST['close_reason']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where type_del = 0 and is_rs = 0 and order_status = 0 and confirm_status < 2 and refund_status=0 and is_cancel=0 and id=".$id);
		
		
		if(!$order_info)
		{
			 
	     $this->error(l("些订单不能被关闭"));
	  
		}
		
	   	 require_once  APP_ROOT_PATH."system/model/dc.php";
	   	 dc_order_close($id,3,$close_reason);
	   	$this->assign("jumpUrl",U("DcOrder/view_order",array("id"=>$id)));
	//	$this->success(l("关闭成功"));
		
	   	$root['status'] = 1;
	   	$root['info'] ="关闭成功";
	   	ajax_return($root);
	   	
	}
	
	
	public function accept_order()
	{
	
		$id = intval($_REQUEST['id']);
	
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where type_del = 0 and is_rs = 0 and id=".$id);
	
	
		if(!$order_info)
		{
	
			$root['status'] = 0;
			$root['info'] ="数据不存在";
			ajax_return($root);
			 
		}
	
	
		if($order_info['order_delivery_time']==1){
			//立即送达超过两小时不接单，直接关闭订单
			if(NOW_TIME-$order_info['create_time'] > 3600*2){
				require_once  APP_ROOT_PATH."system/model/dc.php";
	
				$root['status'] = 0;
				$root['jump'] = U("DcOrder/view_order",array('id'=>$id));
				$root['info'] ="超过配送时间，已不能接单，订单关闭";
				dc_order_close($id,3,$root['info']);
				ajax_return($root);
			}
		}elseif($order_info['order_delivery_time'] > 10000){
			//有具体送达时间，超过送达时间，直接关闭订单
			if(NOW_TIME > $order_info['order_delivery_time']){
				require_once  APP_ROOT_PATH."system/model/dc.php";
	
				$root['status'] = 0;
				$root['jump'] = U("DcOrder/view_order",array('id'=>$id));
				$root['info'] ="超过配送时间，已不能接单，订单关闭";
				dc_order_close($id,3,$root['info']);
				ajax_return($root);
			}
		}
	
		$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set confirm_status = 1 where id = ".$id);
		$rs=$GLOBALS['db']->affected_rows();
		if($rs> 0){
			$root['status'] = 1;
			$root['jump'] = U("DcOrder/view_order",array('id'=>$id));
			$root['info'] ="接单成功";
			ajax_return($root);
	
		}else{
			$root['status'] = 0;
			$root['info'] ="接单失败，请重新接单";
			ajax_return($root);
	
		}
	
	
	}
	
	
	
	public function delete() {
		//删除指定记录
		require_once APP_ROOT_PATH."system/model/dc.php";
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("DcOrder")->where($condition)->findAll();					
				foreach($rel_data as $data)
				{
					if(dc_del_order($data['id']))
					{
						$info[] = $data['order_sn'];
					}
				}
				$info = implode(",", $info);
				save_log($info.l("DELETE_SUCCESS"),1);
				$this->success (l("DELETE_SUCCESS"),$ajax);
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	
	public function over_order()
	{	

		$id = intval($_REQUEST['id']);

		 $order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where type_del = 0 and is_rs = 0 and pay_status = 1 and id=".$id);
		
		
		if(!$order_info)
		{
			 
	     $this->error(l("数据不存在"));
		}
		else
		{
		
			require_once APP_ROOT_PATH."system/model/dc.php";
		   	 $result=dc_confirm_delivery($id);
		   	 $result['jump'] =U("DcOrder/view_order",array("id"=>$id));
			 ajax_return( $result);

			//$this->success(l("确认成功"));
			
		}
	

	}
	
	
		public function export_csv($page = 1){
		
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$reminder = M("RemindCount")->find();
		$reminder['order_count_time'] = NOW_TIME;
		$reminder['refund_count_time'] = NOW_TIME;
		$reminder['retake_count_time'] = NOW_TIME;
		M("RemindCount")->save($reminder);
		
		
		if(!isset($_REQUEST['order_status']))
		{
			$_REQUEST['order_status'] = -1;
		}
		
		$where = " 1=1 ";
		if(intval($_REQUEST['id'])>0)
		$where .= " and id = ".intval($_REQUEST['id']);
		//定义条件
		$where.= " and  type_del = 0 and is_rs = 0";
		if(strim($_REQUEST['user_name'])!='')
			$where.=" and user_name like '%".strim($_REQUEST['user_name'])."%'";
		if(strim($_REQUEST['order_sn'])!='')
		{
			$where.= " and order_sn like '%".strim($_REQUEST['order_sn'])."%' ";
		}
		if(strim($_REQUEST['location_name'])!='')
		{
			$where.= " and location_name like '%".strim($_REQUEST['location_name'])."%' ";
		}

		$order_status=intval($_REQUEST['order_status']);
		if($order_status==0)
		{	//支付中
			$where.= " and is_cancel=0 and refund_status=0 and pay_status = 0";
		}elseif($order_status==1){
			//待接单,在线支付要已支付，货到付款，也可以待接单
			$where.= " and is_cancel=0 and refund_status=0 and ((pay_status = 1 and payment_id = 0 and confirm_status = 0) or (pay_status = 0 and payment_id = 1 and confirm_status = 0))";	
		}elseif($order_status==2){
			//已接单
			$where.= " and is_cancel=0 and refund_status=0 and confirm_status =1";	
		}elseif($order_status==3){
			//已完成
			$where.= " and is_cancel=0 and refund_status=0 and confirm_status =2";	
		}elseif($order_status==4){
			//已结单
			$where.= " and is_cancel=0 and refund_status=0 and confirm_status =2 and order_status=1";	
		}elseif($order_status==5){
			//申请退款
			$where.= " and is_cancel=0 and refund_status=1";	
		}elseif($order_status==6){
			//订单关闭
			$where.= " and (is_cancel >0 or refund_status > 1)";	
		}
	
	
		//关于列表数据的输出
		if (isset ( $_REQUEST ['_order'] ) && $_REQUEST ['_order']!='menu') {
			
			$order = $_REQUEST ['_order'];
			
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : 'id';
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		
			

			$voList = M("DcOrder")
				->where($where)				
				->field('*')
				->order( $order ." ". $sort)
				->limit($limit)->findAll ( );
	if($voList){ 
		
		
		
				foreach($voList as $k=>$v)
				{
				
				$order_menu=unserialize($v['order_menu']);
				//print_r($order_menu);die;
				
				
				$menu_list=$order_menu['menu_list'];
				$m_cart_list=$menu_list['cart_list'];
				if($m_cart_list){
					$voList[$k]['menu']="";
					foreach($m_cart_list as $km=>$vm){
						
						$voList[$k]['menu'].=trim($vm['name'])."*".$vm['num']."\n";
						
					}		
					}else{
					
						$voList[$k]['menu']='无';
					}
				
					$voList[$k]["sn_info"] = $v['order_sn'];
	
					$voList[$k]["sn_info"].= "\n".$v['location_name'];
		
					if($v['dc_comment']){
		
					$voList[$k]["sn_info"].="\n 订单备注：".$v['dc_comment']." ";
					}
		
					if($v['order_delivery_time']>1){
		
					$voList[$k]["sn_info"].= "\n期望时间：".to_date($v['order_delivery_time'])." ";
					}
					else{
		
					$voList[$k]["sn_info"].= "\n期望时间：立即送餐 ";
					}
					
					
				if($v['is_cancel']>0)
				{
				$voList[$k]['csv_order_staus']="订单已关闭";
				}
				else
				{
					if($v['pay_status']==0)
					{
					$voList[$k]['csv_order_staus']="支付中";
					}
					elseif($v['pay_status']==1)
					{
			
						if($v['order_status']==0)
						{
							if($v['confirm_status']==0)
							{
								$voList[$k]['csv_order_staus']="待接单";
								if($v['refund_status']==1)
								{
							$voList[$k]['csv_order_staus'].="\n申请退款";
								}
								elseif($v['refund_status']==2)
								{
									$voList[$k]['csv_order_staus'].="\n已退款";
								}
								elseif($v['refund_status']==3)
								{
									$voList[$k]['csv_order_staus'].="\n退款驳回";
								}
					
							}
							elseif($v['confirm_status']==1)
							{
					
								$voList[$k]['csv_order_staus']="已接单";
								if($v['refund_status']==1)
								{
									$voList[$k]['csv_order_staus'].="\n申请退款";
								}
								elseif($v['refund_status']==2)
								{
									$voList[$k]['csv_order_staus'].="\n已退款";
								}
								elseif($v['refund_status']==3)
								{
									$voList[$k]['csv_order_staus'].="\n退款驳回";
								}
							}
							elseif($v['confirm_status']==2)
							{
								$voList[$k]['csv_order_staus']="已完成";
							}
						}
						else
						{
							$voList[$k]['csv_order_staus']="已结单";
						}
					}
				}
				
				}		
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);

			
			$order_value = array('sn_info'=>'""','menu'=>'""','user_name'=>'""', 'create_time'=>'""', 'total_price'=>'""', 'pay_amount'=>'""', 'csv_order_staus'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","订单信息,菜单,会员名称,下单时间,应付总额,已付金额,订单状态");	    		    	
		    	$content = $content . "\n";
	    	}
	    		

			foreach($voList as $kt=>$vt)
			{
						
				$order_value['sn_info'] = '"' . iconv('utf-8','gbk',$vt['sn_info']) . '"';
				$order_value['menu'] = '"' . iconv('utf-8','gbk',$vt['menu']) . '"';
				$order_value['user_name'] = '"' . iconv('utf-8','gbk',$vt['user_name']) . '"';
				$order_value['create_time'] = '"' . iconv('utf-8','gbk',to_date($vt['create_time'])) . '"';
				$order_value['total_price'] = '"' . iconv('utf-8','gbk',floatval($vt['total_price'])."元") . '"';

				$order_value['pay_amount'] = '"' . iconv('utf-8','gbk',floatval($vt['pay_amount'])."元") . '"';
				
				
				$order_value['csv_order_staus'] = '"' . iconv('utf-8','gbk',$vt['csv_order_staus']) . '"';
				
				
				
				$content .= implode(",", $order_value) . "\n";
			}	
			
		
			header("Content-Disposition: attachment; filename=order_list.csv");
	    	echo $content; 
		}
		else
		{
			if($page==1)
			$this->error(L("NO_RESULT"));
		}	
		
	}
	
	

	
	
}
?>
