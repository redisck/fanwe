<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

class DealOrderAction extends CommonAction{
	public function incharge_index()
	{
		$reminder = M("RemindCount")->find();
		$reminder['incharge_count_time'] = NOW_TIME;
		M("RemindCount")->save($reminder);

		$condition['type'] = 1;
		if(strim($_REQUEST['user_name'])!='')
		{		
			$ids = M("User")->where(array("user_name"=>array('eq',strim($_REQUEST['user_name']))))->field("id")->findAll();
			$ids_arr = array();
			foreach($ids as $k=>$v)
			{
				array_push($ids_arr,$v['id']);
			}	
			$condition['user_id'] = array("in",$ids_arr);
		}
		
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function incharge_trash()
	{
		$condition['type'] = 1;
		$this->assign("default_map",$condition);
		
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		//追加默认参数
		if($this->get("default_map"))
			$map = array_merge($map,$this->get("default_map"));
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name="DealOrderHistory";
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
public function deal_index()
	{
		
		$reminder = M("RemindCount")->find();
		$reminder['order_count_time'] = NOW_TIME;
		$reminder['refund_count_time'] = NOW_TIME;
		$reminder['retake_count_time'] = NOW_TIME;
		M("RemindCount")->save($reminder);
		
		//处理-1情况的select
		if(!isset($_REQUEST['pay_status']))
		{
			$_REQUEST['pay_status'] = -1;
		}
		if(!isset($_REQUEST['delivery_status']))
		{
			$_REQUEST['delivery_status'] = -1;
		}
		if(!isset($_REQUEST['extra_status']))
		{
			$_REQUEST['extra_status'] = -1;
		}
		if(!isset($_REQUEST['after_sale']))
		{
			$_REQUEST['after_sale'] = -1;
		}
		if(!isset($_REQUEST['refund_status']))
		{
			$_REQUEST['refund_status'] = -1;
		}
		
		if(!isset($_REQUEST['is_refuse_delivery']))
		{
			$_REQUEST['is_refuse_delivery'] = -1;
		}
		if(!isset($_REQUEST['order_status']))
		{
			$_REQUEST['order_status'] = -1;
		}
		
		$where = " 1=1 ";
		if(intval($_REQUEST['id'])>0)
		$where .= " and id = ".intval($_REQUEST['id']);
		//定义条件
		if(isset($_REQUEST['referer'])&&strim($_REQUEST['referer'])!='')
		{
			if(intval($_REQUEST['referer'])==-1)
				$where.=" and ".DB_PREFIX."deal_order.referer = ''";
			else
				$where.=" and ".DB_PREFIX."deal_order.referer = '".strim($_REQUEST['referer'])."'";
		}
		if(strim($_REQUEST['user_name'])!='')
		$where.=" and ".DB_PREFIX."deal_order.user_name = '".strim($_REQUEST['user_name'])."'";
		if(intval($_REQUEST['deal_id'])>0)		
		$where.=" and ".DB_PREFIX."deal_order.deal_ids = ".intval($_REQUEST['deal_id'])." ";
		

		$where.= " and ".DB_PREFIX."deal_order.type = 0 ";

		if(strim($_REQUEST['order_sn'])!='')
		{
			$where.= " and ".DB_PREFIX."deal_order.order_sn = '".strim($_REQUEST['order_sn'])."' ";
		}
		if(intval($_REQUEST['pay_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.pay_status = ".intval($_REQUEST['pay_status']);
		}
		if(intval($_REQUEST['delivery_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.delivery_status = ".intval($_REQUEST['delivery_status']);
		}
		if(intval($_REQUEST['extra_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.extra_status = ".intval($_REQUEST['extra_status']);
		}
		if(intval($_REQUEST['after_sale'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.after_sale = ".intval($_REQUEST['after_sale']);
		}
		if(intval($_REQUEST['refund_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.refund_status = ".intval($_REQUEST['refund_status']);
		}
		if(intval($_REQUEST['is_refuse_delivery'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.is_refuse_delivery = ".intval($_REQUEST['is_refuse_delivery']);
		}
		if(intval($_REQUEST['order_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.order_status = ".intval($_REQUEST['order_status']);
		}
	
		
		//关于列表数据的输出
		if (isset ( $_REQUEST ['_order'] )) {
			$order = DB_PREFIX.'deal_order.'.$_REQUEST ['_order'];
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : DB_PREFIX.'deal_order.id';
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		
		
		
		$count = M("DealOrder")
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

			$voList = M("DealOrder")
				->where($where)				
				->field(DB_PREFIX.'deal_order.*')
				->order( $order ." ". $sort)
				->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
			

			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
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
		
		
		//输出快递接口
		$express_list = M("Express")->where("is_effect = 1")->findAll();
		$this->assign("express_list",$express_list);
		//end 
		$this->display ();
		return;
	}
	
	
	public function export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		//处理-1情况的select
		if(!isset($_REQUEST['pay_status']))
		{
			$_REQUEST['pay_status'] = -1;
		}
		if(!isset($_REQUEST['delivery_status']))
		{
			$_REQUEST['delivery_status'] = -1;
		}
		if(!isset($_REQUEST['extra_status']))
		{
			$_REQUEST['extra_status'] = -1;
		}
		if(!isset($_REQUEST['after_sale']))
		{
			$_REQUEST['after_sale'] = -1;
		}
		
		if(!isset($_REQUEST['refund_status']))
		{
			$_REQUEST['refund_status'] = -1;
		}
		
		if(!isset($_REQUEST['is_refuse_delivery']))
		{
			$_REQUEST['is_refuse_delivery'] = -1;
		}
		if(!isset($_REQUEST['order_status']))
		{
			$_REQUEST['order_status'] = -1;
		}
		
		$where = " 1=1 ";
		//定义条件
		if(isset($_REQUEST['referer'])&&strim($_REQUEST['referer'])!='')
		{
			if(intval($_REQUEST['referer'])==-1)
				$where.=" and ".DB_PREFIX."deal_order.referer = ''";
			else
			$where.=" and ".DB_PREFIX."deal_order.referer = '".strim($_REQUEST['referer'])."'";
		}
		if(strim($_REQUEST['user_name'])!='')
		$where.=" and ".DB_PREFIX."deal_order.user_name = '".strim($_REQUEST['user_name'])."'";
		if(intval($_REQUEST['deal_id'])>0)		
		$where.=" and ".DB_PREFIX."deal_order.deal_ids = ".intval($_REQUEST['deal_id'])." ";
		
		
		$where.= " and ".DB_PREFIX."deal_order.is_delete = 0 ";
		$where.= " and ".DB_PREFIX."deal_order.type = 0 ";

		if(strim($_REQUEST['order_sn'])!='')
		{
			$where.= " and ".DB_PREFIX."deal_order.order_sn = '".strim($_REQUEST['order_sn'])."' ";
		}
		if(intval($_REQUEST['pay_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.pay_status = ".intval($_REQUEST['pay_status']);
		}
		if(intval($_REQUEST['delivery_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.delivery_status = ".intval($_REQUEST['delivery_status']);
		}
		if(intval($_REQUEST['extra_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.extra_status = ".intval($_REQUEST['extra_status']);
		}
		if(intval($_REQUEST['after_sale'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.after_sale = ".intval($_REQUEST['after_sale']);
		}

		if(intval($_REQUEST['refund_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.refund_status = ".intval($_REQUEST['refund_status']);
		}
		if(intval($_REQUEST['is_refuse_delivery'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.is_refuse_delivery = ".intval($_REQUEST['is_refuse_delivery']);
		}
		if(intval($_REQUEST['order_status'])>=0)
		{
			$where.= " and ".DB_PREFIX."deal_order.order_status = ".intval($_REQUEST['order_status']);
		}
		require_once APP_ROOT_PATH."system/model/user.php";
		
		$list = M("DealOrder")
				->where($where)
				->field(DB_PREFIX.'deal_order.*')
				->limit($limit)->findAll ( );
			
		if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);
			
			$order_value = array('sn'=>'""', 'user_name'=>'""', 'deal_name'=>'""','number'=>'""', 'create_time'=>'""', 'total_price'=>'""', 'pay_amount'=>'""', 'consignee'=>'""', 'address'=>'""','zip'=>'""','email'=>'""', 'mobile'=>'""', 'memo'=>'""', 'pay_status'=>'""', 'delivery_status'=>'""','refund_status'=>'""','is_refuse_delivery'=>'""','order_status'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","订单编号,用户名,商品名称,订购数量,下单时间,订单总额,已收金额,收货人,发货地址,邮编,用户邮件,手机号码,订单留言,支付状态,发货状态,退款申请,维权申请,订单状态");	    		    	
		    	$content = $content . "\n";
	    	}
	    	
			foreach($list as $k=>$v)
			{
				$user_info = load_user($v['user_id']);
				$order_value['sn'] = '"' . "sn:".iconv('utf-8','gbk',$v['order_sn']) . '"';		
				$order_value['user_name'] = '"' . iconv('utf-8','gbk',$v['user_name']) . '"';
				$order_items = unserialize($v['deal_order_item']);
				$names = "";
				$total_num = 0;
				foreach($order_items as $key => $row)
				{
					$names.=  addslashes($row['name'])."[".$row['number']."]";
					if($key<count($order_items)-1)
					$names.="\n";
					$total_num+=$row['number'];
				}
			
				$order_value['deal_name'] = '"' . iconv('utf-8','gbk',$names) . '"';
				$order_value['number'] = '"' . iconv('utf-8','gbk',$total_num) . '"';

				$order_value['create_time'] = '"' . iconv('utf-8','gbk',to_date($v['create_time'])) . '"';
				$order_value['total_price'] = '"' . iconv('utf-8','gbk',floatval($v['total_price'])."元") . '"';

				$order_value['pay_amount'] = '"' . iconv('utf-8','gbk',floatval($v['pay_amount'])."元") . '"';
				
				$order_value['consignee'] = '"' . iconv('utf-8','gbk',$v['consignee']) . '"';
				
				$region_lv1_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv1']);
				$region_lv2_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv2']);
				$region_lv3_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv3']);
				$region_lv4_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv4']);
				$address = $region_lv1_name.$region_lv2_name.$region_lv3_name.$region_lv4_name.$v['address'];
				$order_value['address'] = '"' . iconv('utf-8','gbk',$address) . '"';
				$order_value['zip'] = '"' . iconv('utf-8','gbk',$v['zip']) . '"';
				$order_value['email'] = '"' . iconv('utf-8','gbk',$user_info['email']) . '"';
				if($v['mobile']!='')
					$mobile = $v['mobile'];
				else
					$mobile = $user_info['mobile'];
				$order_value['mobile'] = '"' . iconv('utf-8','gbk',$mobile) . '"';
				$order_value['memo'] = '"' . iconv('utf-8','gbk',$v['memo']) . '"';
				
				$order_value['pay_status'] = '"' . iconv('utf-8','gbk',l("PAY_STATUS_".$v['pay_status'])) . '"';
				$order_value['delivery_status'] = '"' . iconv('utf-8','gbk',l("ORDER_DELIVERY_STATUS_".$v['delivery_status'])) . '"';
				
				$refund_status = $v['refund_status']==1?"有":"无";
				$order_value['refund_status'] = '"' . iconv('utf-8','gbk',$refund_status) . '"';
				$is_refuse_delivery = $v['is_refuse_delivery']==1?"有":"无";
				$order_value['is_refuse_delivery'] = '"' . iconv('utf-8','gbk',$is_refuse_delivery) . '"';
				
				
				$order_value['order_status'] = '"' . iconv('utf-8','gbk',get_order_status_csv($v['order_status'], $v)) . '"';
				
				
				
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
	
	public function deal_trash()
	{

		//处理-1情况的select
		if(!isset($_REQUEST['pay_status']))
		{
			$_REQUEST['pay_status'] = -1;
		}
		if(!isset($_REQUEST['delivery_status']))
		{
			$_REQUEST['delivery_status'] = -1;
		}
		if(!isset($_REQUEST['extra_status']))
		{
			$_REQUEST['extra_status'] = -1;
		}
		if(!isset($_REQUEST['after_sale']))
		{
			$_REQUEST['after_sale'] = -1;
		}
		if(!isset($_REQUEST['refund_status']))
		{
			$_REQUEST['refund_status'] = -1;
		}
		
		if(!isset($_REQUEST['is_refuse_delivery']))
		{
			$_REQUEST['is_refuse_delivery'] = -1;
		}
		
		$where = " 1=1 ";
		if(intval($_REQUEST['id'])>0)
			$where .= " and id = ".intval($_REQUEST['id']);
		//定义条件

		if(strim($_REQUEST['user_name'])!='')
			$where.=" and user_name = '".strim($_REQUEST['user_name'])."'";
		if(intval($_REQUEST['deal_id'])>0)
			$where.=" and deal_ids = ".intval($_REQUEST['deal_id'])." ";
		
		
		$where.= " and  type = 0 ";
		
		if(strim($_REQUEST['order_sn'])!='')
		{
			$where.= " and order_sn = '".strim($_REQUEST['order_sn'])."' ";
		}
		if(intval($_REQUEST['pay_status'])>=0)
		{
			$where.= " and pay_status = ".intval($_REQUEST['pay_status']);
		}
		if(intval($_REQUEST['delivery_status'])>=0)
		{
			$where.= " and delivery_status = ".intval($_REQUEST['delivery_status']);
		}
		if(intval($_REQUEST['extra_status'])>=0)
		{
			$where.= " and extra_status = ".intval($_REQUEST['extra_status']);
		}
		if(intval($_REQUEST['after_sale'])>=0)
		{
			$where.= " and after_sale = ".intval($_REQUEST['after_sale']);
		}
		if(intval($_REQUEST['refund_status'])>=0)
		{
			$where.= " and refund_status = ".intval($_REQUEST['refund_status']);
		}
		if(intval($_REQUEST['is_refuse_delivery'])>=0)
		{
			$where.= " and is_refuse_delivery = ".intval($_REQUEST['is_refuse_delivery']);
		}
		
		
		//关于列表数据的输出
		if (isset ( $_REQUEST ['_order'] )) {
			$order =  $_REQUEST ['_order'];
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
		
		
		
		$count = M("DealOrderHistory")
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
		
			$voList = M("DealOrderHistory")
			->where($where)
			->order( $order ." ". $sort)
			->limit($p->firstRow . ',' . $p->listRows)->findAll ( );

			//模板赋值显示
			foreach($voList as $k=>$v)
			{
				$voList[$k]['history_deal_order_item'] = unserialize($v['history_deal_order_item']);
				$voList[$k]['history_deal_coupon'] = unserialize($v['history_deal_coupon']);
				$voList[$k]['history_deal_order_log'] = unserialize($v['history_deal_order_log']);
				$voList[$k]['history_delivery_notice'] = unserialize($v['history_delivery_notice']);
				$voList[$k]['history_payment_notice'] = unserialize($v['history_payment_notice']);
				$voList[$k]['history_message'] = unserialize($v['history_message']);
			}
		
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
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
		
		

		$this->display ();
		return;	
		
	}
	public function pay_incharge()
	{
		$id = intval($_REQUEST['id']);
		//开始由管理员手动收款
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id);
		if($order_info['pay_status'] != 2)
		{
			require_once APP_ROOT_PATH."system/model/cart.php";
			$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_info['id']." and payment_id = ".$order_info['payment_id']." and is_paid = 0");
			if(!$payment_notice)
			{
				make_payment_notice($order_info['total_price'],$order_info['id'],$order_info['payment_id']);
				$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_info['id']." and payment_id = ".$order_info['payment_id']." and is_paid = 0");
			}
			
			payment_paid(intval($payment_notice['id']));	//对其中一条款支付的付款单付款					
			$msg = sprintf(l("ADMIN_PAYMENT_PAID"),$payment_notice['notice_sn']);
			save_log($msg,1);
			$rs = order_paid($order_info['id']);
			
			if($rs)
			{
				$msg = sprintf(l("ADMIN_ORDER_PAID"),$order_info['order_sn']);
				save_log($msg,1);
				$this->success(l("ORDER_PAID_SUCCESS"));
			}
			else
			{
				$msg = sprintf(l("ADMIN_ORDER_PAID"),$order_info['order_sn']);
				save_log($msg,0);
				$this->error(l("ORDER_PAID_FAILED"));
			}
		}
		else 
		{
			$this->error(l("ORDER_PAID_ALREADY"));
		}
	}	
	public function delete() {
		//删除指定记录
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();					
				foreach($rel_data as $data)
				{
					if(del_order($data['id']))
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
	
// 	public function restore() {
// 		//删除指定记录
// 		$ajax = intval($_REQUEST['ajax']);
// 		$id = $_REQUEST ['id'];
// 		if (isset ( $id )) {
// 				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
// 				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
// 				foreach($rel_data as $data)
// 				{
// 					$info[] = $data['order_sn'];						
// 				}
// 				if($info) $info = implode(",",$info);
// 				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
// 				if ($list!==false) {
// 					save_log($info.l("RESTORE_SUCCESS"),1);
// 					$this->success (l("RESTORE_SUCCESS"),$ajax);
// 				} else {
// 					save_log($info.l("RESTORE_FAILED"),0);
// 					$this->error (l("RESTORE_FAILED"),$ajax);
// 				}
// 			} else {
// 				$this->error (l("INVALID_OPERATION"),$ajax);
// 		}		
// 	}
	
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("DealOrderHistory")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['order_sn'];
				}
				if($info) $info = implode(",",$info);
				$list = M("DealOrderHistory")->where ( $condition )->delete();	
		
				if ($list!==false) {
					//删除关联数据
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function view_order()
	{
		$id = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$id." and type = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		$order_deal_items = M("DealOrderItem")->where("order_id=".$order_info['id'])->findAll();
		foreach($order_deal_items as $k=>$v)
		{
			$order_deal_items[$k]['is_delivery'] = M("Deal")->where("id=".$v['deal_id'])->getField("is_delivery");
		}
		require_once APP_ROOT_PATH."system/model/cart.php";
		$order_deal_items = cart_list_group($order_deal_items);
		foreach($order_deal_items as $k=>$v)
		{
			$order_deal_items[$k]['supplier'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = '".$v['supplier_id']."'");
			$order_deal_items[$k]['supplier'] = $order_deal_items[$k]['supplier']?$order_deal_items[$k]['supplier']:app_conf("SHOP_TITLE")."直营";
			$s_is_delivery = 0;
			foreach($v['goods_list'] as $kk=>$vv)
			{
				if($vv['is_delivery'])
					$s_is_delivery = 1;
			}
			if($s_is_delivery)
			$order_deal_items[$k]['delivery_fee'] = round($GLOBALS['db']->getOne("select delivery_fee from ".DB_PREFIX."deal_order_supplier_fee where order_id = ".$order_info['id']." and supplier_id = ".$v['supplier_id']),2);
			else
				$order_deal_items[$k]['delivery_fee'] = -1;
		}
		
		$this->assign("order_deals",$order_deal_items);
		$this->assign("order_info",$order_info);
		
		$payment_notice = M("PaymentNotice")->where("order_id = ".$order_info['id']." and is_paid = 1")->order("pay_time desc")->findAll();
		$this->assign("payment_notice",$payment_notice);
		
		
		
		//输出订单留言
		$map['rel_table'] = 'deal_order';
		$map['rel_id'] = $order_info['id'];
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name= "Message"; 
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		
		//输出订单相关的团购券
		$coupon_list = M("DealCoupon")->where("order_id = ".$order_info['id']." and is_delete = 0")->findAll();
		$this->assign("coupon_list",$coupon_list);
		
		//输出订单日志
		$log_list = M("DealOrderLog")->where("order_id=".$order_info['id'])->order("log_time desc")->findAll();
		$this->assign("log_list",$log_list);
		
		$this->display();
	}
	
	
	public function view_order_history()
	{
		$id = intval($_REQUEST['id']);
		$order_info = M("DealOrderHistory")->where("id=".$id." and type = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		$order_deal_items = unserialize($order_info['history_deal_order_item']);
		foreach($order_deal_items as $k=>$v)
		{
			$order_deal_items[$k]['is_delivery'] = $v['delivery_status']==5?0:1;
		}
		
		require_once APP_ROOT_PATH."system/model/cart.php";
		$order_deal_items = cart_list_group($order_deal_items);
		
		foreach($order_deal_items as $k=>$v)
		{
			$order_deal_items[$k]['supplier'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = '".$v['supplier_id']."'");
			$order_deal_items[$k]['supplier'] = $order_deal_items[$k]['supplier']?$order_deal_items[$k]['supplier']:app_conf("SHOP_TITLE")."直营";
			$s_is_delivery = 0;
			foreach($v['goods_list'] as $kk=>$vv)
			{
				if($vv['is_delivery'])
					$s_is_delivery = 1;
			}
			
			
			$delivery_fees = unserialize($order_info['history_delivery_fee']);
			
			if($s_is_delivery)
			{
				foreach($delivery_fees as $kk=>$vv)
				{
					if($vv['supplier_id']==$v['supplier_id'])
					{
						$delivery_fee = $vv['delivery_fee'];
					}
				}
				$order_deal_items[$k]['delivery_fee'] = round($delivery_fee,2);
			}
			else
				$order_deal_items[$k]['delivery_fee'] = -1;
		}
		
		$this->assign("order_deals",$order_deal_items);
		$this->assign("order_info",$order_info);
	
		$payment_notice = unserialize($order_info['history_payment_notice']);
		$this->assign("payment_notice",$payment_notice);
		
		
		$delivery_notice_rs = unserialize($order_info['history_delivery_notice']);
		foreach($delivery_notice_rs as $k=>$v)
		{
			$v['express_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."express where id = ".$v['express_id']);
			$delivery_notice[$v['order_item_id']] = $v;
		}
		$this->assign("delivery_notice",$delivery_notice);
	
	
	
		$deal_msg = unserialize($order_info['history_message']);
		$this->assign("deal_msg",$deal_msg);
		
		//输出订单相关的团购券
		$coupon_list = unserialize($order_info['history_deal_coupon']);
		$this->assign("coupon_list",$coupon_list);
	
		//输出订单日志
		$log_list = unserialize($order_info['history_deal_order_log']);
		$this->assign("log_list",$log_list);
	
		$this->display();
	}
	
	public function delivery()
	{
		$id = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$id." and is_delete = 0 and type = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		$order_deal_items = M("DealOrderItem")->where("order_id=".$order_info['id'])->findAll();
		foreach($order_deal_items as $k=>$v)
		{
			if(M("Deal")->where("id=".$v['deal_id'])->getField("is_delivery")==0||$v['refund_status']==2||$v['is_arrival']==1) //无需发货的商品，或者是已退款的商品
			{
				unset($order_deal_items[$k]);
			}
		}
		
		//输出快递接口
		$express_list = M("Express")->where("is_effect = 1")->findAll();
		$this->assign("express_list",$express_list);
		$this->assign("order_deals",$order_deal_items);
		$this->assign("order_info",$order_info);
		$this->display();
	}
	
	//批量发货
	public function do_batch_delivery()
	{
		$delivery_sn = floatval($_REQUEST['begin_sn']);
		$order_ids = $_REQUEST['ids'];
		$order_ids = explode(",",$order_ids);
		$_REQUEST['silent'] = 1;	

		$idx = 0;
		foreach($order_ids as $k=>$order_id)
		{
			$_REQUEST['order_id'] = $order_id;			
			$order_items = $GLOBALS['db']->getAll("select doi.* from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where doi.order_id = ".$order_id." and d.is_delivery = 1");
			$order_deals = array();
			foreach($order_items as $kk=>$vv)
			{
				array_push($order_deals,$vv['id']);
			}
			if(count($order_deals)>0)
			{
				$_REQUEST['delivery_sn'] = $delivery_sn + $idx;
				$idx++;
			}
			$_REQUEST['order_deals'] = $order_deals;
			$_REQUEST['express_id'] = intval($_REQUEST['express_id']);
			$this->do_delivery();
		}
		
		$this->assign("jumpUrl",U("DealOrder/deal_index"));
		$this->success(l("BATCH_DELIVERY_SUCCESS"));	
	}
	public function load_batch_delivery()
	{
		$ids = strim($_REQUEST['ids']);
		$express_id = intval($_REQUEST['express_id']);
		if($express_id==0)
		{
			header("Content-Type:text/html; charset=utf-8");
			echo l("SELECT_EXPRESS_WARNING");
			exit;
		}
		$this->assign("ids",$ids);
		$this->assign("express_id",$express_id);
		$this->display();
	}

	public function do_delivery()
	{
		$silent = intval($_REQUEST['silent']);
		$order_id = intval($_REQUEST['order_id']);
		$order_deals = $_REQUEST['order_deals'];
		$delivery_sn = $_REQUEST['delivery_sn'];
		$express_id = intval($_REQUEST['express_id']);
		$memo = $_REQUEST['memo'];
		if(!$order_deals)
		{
			if($silent==0)
			$this->error(l("PLEASE_SELECT_DELIVERY_ITEM"));
		}
		else
		{
			$deal_names = array();
			foreach($order_deals as $order_deal_id)
			{
				$deal_info =$GLOBALS['db']->getRow("select d.*,doi.id as doiid from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_order_item as doi on doi.deal_id = d.id where doi.id = ".$order_deal_id);
				$deal_name = $deal_info['sub_name'];
				array_push($deal_names,$deal_name);
				$rs = make_delivery_notice($order_id,$order_deal_id,$delivery_sn,$memo,$express_id);
				if($rs)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set delivery_status = 1,is_arrival = 0 where id = ".$order_deal_id);
					update_balance($order_deal_id,$deal_info['id']);
				}
			}
			$deal_names = implode(",",$deal_names);
			
			send_delivery_mail($delivery_sn,$deal_names,$order_id);
			send_delivery_sms($delivery_sn,$deal_names,$order_id);
			//开始同步订单的发货状态
			$order_deal_items = M("DealOrderItem")->where("order_id=".$order_id)->findAll();
			foreach($order_deal_items as $k=>$v)
			{
				if(M("Deal")->where("id=".$v['deal_id'])->getField("is_delivery")==0) //无需发货的商品
				{
					unset($order_deal_items[$k]);
				}		
			}
			$delivery_deal_items = $order_deal_items;
			foreach($delivery_deal_items as $k=>$v)
			{
				if($v['delivery_status']==0) //未发货去除
				{
					unset($delivery_deal_items[$k]);
				}				 
			}
			

			if(count($delivery_deal_items)==0&&count($order_deal_items)!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 0 where id = ".$order_id); //未发货
			}
			elseif(count($delivery_deal_items)>0&&count($order_deal_items)!=0&&count($delivery_deal_items)<count($order_deal_items))
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 1 where id = ".$order_id); //部分发
			}
			else
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 2 where id = ".$order_id); //全部发
			}		
			M("DealOrder")->where("id=".$order_id)->setField("update_time",NOW_TIME);
			M("DealOrder")->where("id=".$order_id)->setField("is_refuse_delivery",0);
			
			$refund_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where (refund_status = 1 or is_arrival = 2) and order_id = ".$order_id);
			$coupon_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where refund_status = 1 and order_id = ".$order_id);
			if($refund_item_count==0&&$coupon_item_count==0)
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_status = 0,is_refuse_delivery=0 where id = ".$order_id);
				
			
			
			
			$msg = l("DELIVERY_SUCCESS");
			//发货完毕，开始同步相应支付接口中的发货状态
			if(intval($_REQUEST['send_goods_to_payment'])==1)
			{
				$payment_notices = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_id);
				foreach($payment_notices as $k=>$v)
				{
					$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$v['payment_id']);
					if($v['outer_notice_sn']!='')
					{						
						require_once APP_ROOT_PATH."system/payment/".$payment_info['class_name']."_payment.php";
						$payment_class = $payment_info['class_name']."_payment";
						$payment_object = new $payment_class();
						if(method_exists ($payment_object,"do_send_goods"))
						{
							$result = $payment_object->do_send_goods($v['id'],$delivery_sn);
							$msg = $msg."[".$payment_info['name'].$result."]";							
						}
						else 
						{
							$msg = $msg."[".$payment_info['name'].l("NOT_SUPPORT_SEND_GOODS")."]";							
						}						
					}
					else
					{
						$msg = $msg."[".$payment_info['name'].l("NOT_TRADE_SN")."]";	
					}
				}
			}
						
			$this->assign("jumpUrl",U("DealOrder/view_order",array("id"=>$order_id)));		

			//查询快递名
			$express_name = M("Express")->where("id=".$express_id)->getField("name");
			
			require_once APP_ROOT_PATH."system/model/deal_order.php";
			order_log(l("DELIVERY_SUCCESS").$express_name.$delivery_sn.$_REQUEST['memo'],$order_id);
			update_order_cache($order_id);
			distribute_order($order_id);
				
			$order_info = M("DealOrder")->getById($order_id);
			send_msg($order_info['user_id'], $deal_info['name']."等发货了，发货单号：".$delivery_sn, "orderitem", $deal_info['doiid']);
			
			//发微信通知
			//通知商户
			$supplier_list = $GLOBALS['db']->getAll("select distinct(supplier_id) from ".DB_PREFIX."deal_order_item where id in (".implode(",", $order_deals).")");
			foreach($supplier_list as $row)
			{			
				$weixin_conf = load_auto_cache("weixin_conf");
				if($weixin_conf['platform_status']==1)
				{
					$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$row['supplier_id']);
					$order_item_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_order_item where supplier_id = ".$row['supplier_id']." and delivery_status = 1");
					send_wx_msg("OPENTM200565259", $order_info['user_id'], $wx_account,array("order_id"=>$order_id,"order_sn"=>$order_info['order_sn'],"company_name"=>$express_name,"delivery_sn"=>$delivery_sn,"order_item_id"=>$order_item_id));
				}
			}
			
			if($silent==0)
			$this->success($msg);
		}
	}
	
	//查看快递
	public function check_delivery()
	{
		$express_id = intval($_REQUEST['express_id']);
		$typeNu = addslashes(trim($_REQUEST["express_sn"]));
		$express_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."express where is_effect = 1 and id = ".$express_id);
		$express_info['config'] = unserialize($express_info['config']);
		$typeCom = trim($express_info['config']["app_code"]);
		
		if(isset($typeCom)&&isset($typeNu)){
		
			$AppKey = app_conf("KUAIDI_APP_KEY");//请将XXXXXX替换成您在http://kuaidi100.com/app/reg.html申请到的KEY

			$data['msg'] = "http://www.kuaidi100.com/chaxun?com=".$typeCom."&nu=".$typeNu;
			$data['status'] = 1;   //页面查询
			ajax_return($data);
		}else{
			$data['msg'] = '查询失败，请重试';
			$data['status'] = 0;   //查询失败
			ajax_return($data);
		}
		exit();
	}	
	
	
	public function order_incharge()
	{
		$order_id  = intval($_REQUEST['id']);
		$order_info = M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0")->find();
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		
		
		if($order_info['region_lv4']>0)
		$region_id = $order_info['region_lv4'];
		elseif($order_info['region_lv3']>0)
		$region_id = $order_info['region_lv3'];
		elseif($order_info['region_lv2']>0)
		$region_id = $order_info['region_lv2'];
		else
		$region_id = $order_info['region_lv1'];
		
		$delivery_id = $order_info['delivery_id'];
		$payment_id = 0;		
		$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);		
		$GLOBALS['user_info']['id'] = $order_info['user_id'];
		require_once APP_ROOT_PATH."system/model/cart.php";
		$result = count_buy_total($region_id,$delivery_id,$payment_id,$account_money=0,$all_account_money=0,$ecvsn,$ecvpassword,$goods_list,$order_info['account_money'],$order_info['ecv_money'],$order_info['bank_id']);
		
		$this->assign("result",$result);
		
	
		
		
		$payment_list = M("Payment")->where("is_effect = 1 and class_name <> 'Voucher'")->findAll();
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
		$order_info = M("DealOrder")->where("id=".$order_id." and is_delete = 0 and type = 0")->find();		
		if(!$order_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		
		if($order_info['region_lv4']>0)
		$region_id = $order_info['region_lv4'];
		elseif($order_info['region_lv3']>0)
		$region_id = $order_info['region_lv3'];
		elseif($order_info['region_lv2']>0)
		$region_id = $order_info['region_lv2'];
		else
		$region_id = $order_info['region_lv1'];
		
		$delivery_id = $order_info['delivery_id'];
		$payment_id = intval($_REQUEST['payment_id']);		
		$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);		
		$GLOBALS['user_info']['id'] = $order_info['user_id'];
		require_once APP_ROOT_PATH."system/model/cart.php";
		$result = count_buy_total($region_id,$delivery_id,$payment_id,$account_money=0,$all_account_money=0,$ecvsn,$ecvpassword,$goods_list,$order_info['account_money'],$order_info['ecv_money'],$order_info['bank_id']);
		

		$user_money = M("User")->where("id=".$order_info['user_id'])->getField("money");
		//$pay_amount = $order_info['deal_total_price']+ $order_info['delivery_fee']-$order_info['account_money']-$order_info['ecv_money']+$payment_info['fee_amount'];
		$pay_amount = $result['pay_price'];
		
		
		if($payment_info['class_name']=='Account'&&$user_money<$pay_amount) 
		$this->error(l("ACCOUNT_NOT_ENOUGH"));

		$notice_id = make_payment_notice($pay_amount,$order_id,$payment_id,$memo);
		
		$order_info['total_price'] = $result['pay_total_price'];
		$order_info['payment_fee'] = $result['payment_fee'];  
		$order_info['delivery_fee'] = $result['delivery_fee'];
		$order_info['discount_price'] = $result['user_discount'];
		$order_info['payment_id'] = $payment_info['id'];
		$order_info['update_time'] = NOW_TIME;
		M("DealOrder")->save($order_info);
		
		$payment_notice = M("PaymentNotice")->getById($notice_id);
		$rs = payment_paid($payment_notice['id']);	
		if($rs&&$payment_info['class_name']=='Account')
		{
			//余额支付
			require_once APP_ROOT_PATH."system/payment/Account_payment.php";				
			require_once APP_ROOT_PATH."system/model/user.php";
			$msg = sprintf($payment_lang['USER_ORDER_PAID'],$order_info['order_sn'],$payment_notice['notice_sn']);			
			modify_account(array('money'=>"-".$payment_notice['money'],'score'=>0),$payment_notice['user_id'],$msg);
		}

		
		if($rs)
		{	
			order_paid($order_id);
			$msg = sprintf(l("MAKE_PAYMENT_NOTICE_LOG"),$order_info['order_sn'],$payment_notice['notice_sn']);
			save_log($msg,1);
			order_log($msg.$_REQUEST['memo'],$order_id);
			$this->assign("jumpUrl",U("DealOrder/view_order",array("id"=>$order_id)));
			$this->success(l("ORDER_INCHARGE_SUCCESS"));
		}
		else
		{
			$this->assign("jumpUrl",U("DealOrder/view_order",array("id"=>$order_id)));
			$this->success(l("ORDER_INCHARGE_FAILED"));
		}
	}
	
	public function lottery_index()
	{
		if(strim($_REQUEST['user_name'])!='')
		{		
			$ids = M("User")->where(array("user_name"=>array('eq',strim($_REQUEST['user_name']))))->field("id")->findAll();
			$ids_arr = array();
			foreach($ids as $k=>$v)
			{
				array_push($ids_arr,$v['id']);
			}	
			$map['user_id'] = array("in",$ids_arr);
		}
		
		if(intval($_REQUEST['deal_id'])>0)
		$map['deal_id'] = intval($_REQUEST['deal_id']);
		
		if(strim($_REQUEST['lottery_sn'])!='')
		$map['lottery_sn'] = strim($_REQUEST['lottery_sn']);

		$model = D ("Lottery");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}
	
	public function del_lottery()
	{
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("Lottery")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['lottery_sn'];						
				}
				if($info) $info = implode(",",$info);
				$list = M("Lottery")->where ( $condition )->delete();
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function referer()
	{
		if(isset($_REQUEST['referer'])&&strim($_REQUEST['referer'])!='')
		{
			$where = "referer = '".strim($_REQUEST['referer'])."' ";
			$map['referer'] = array("eq",strim($_REQUEST['referer']));

		}
		else
		{
			$where = " 1=1 ";

		}
		$where.=" and type <> 1";
		$map['type'] = array("neq",1);
		$begin_time  = strim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = strim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		if($end_time==0)
		{
			$where.=" and create_time > ".$begin_time;			
			$map['create_time'] = array("gt",$begin_time);
		}
		else
		{
			$where.=" and create_time between ".$begin_time." and ".$end_time;	
			$map['create_time'] = array("between",array($begin_time,$end_time));
		}	
		$sql = "select referer,count(id) as ct from ".DB_PREFIX."deal_order where ".$where." group by referer having count(id) > 0 ";
		$sql_count = "select referer from ".DB_PREFIX."deal_order where ".$where." group by referer having count(id) > 0 ";
		
		$count = $GLOBALS['db']->getAll($sql_count);
		
		//开始list
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : "ct";
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		$count = count($count);
		if ($count > 0) {
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据
			$sql .= "order by `" . $order . "` " . $sort;
			$sql .= " limit ".$p->firstRow . ',' . $p->listRows;

			$voList = $GLOBALS['db']->getAll($sql);
			
//			echo $model->getlastsql();
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
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
			$this->assign ( 'order', $order );
			$this->assign ( 'sortImg', $sortImg );
			$this->assign ( 'sortType', $sortAlt );
			$this->assign ( "page", $page );
			$this->assign ( "nowPage",$p->nowPage);
		}
		$this->display ();
	}
	
	
	
	//退款的审核界面
	public function refund()
	{
		$order_item_id = intval($_REQUEST['order_item_id']);
		$coupon_id = intval($_REQUEST['coupon_id']);
		
		if($order_item_id)
		{			
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
			if($data)
			{
				$order_id = $data['order_id'];
				$data['price'] = $data['total_price'];
				$data['balance_price'] = $data['balance_total_price'] + $data['add_balance_price_total'];
				$data['key'] = "order_item_id";
			}
		}		
		elseif($coupon_id)
		{
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$coupon_id);
			if($data)
			{
				$order_id = $data['order_id'];
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$data['order_deal_id']);
				$data['name'] = $order_item['name'];
				$data['deal_icon'] = $order_item['deal_icon'];
				if($data['deal_type']==0)//按件
				{
					$data['price'] = $order_item['unit_price'];
					$data['balance_price'] = $order_item['balance_unit_price'] + $order_item['add_balance_price'];
				}
				else
				{
					$data['price'] = $order_item['total_price'];
					$data['balance_price'] = $order_item['balance_total_price'] + $order_item['add_balance_price_total'];				
				}	
				$data['key'] = "coupon_id";
			}	
		}
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = '".$order_id."'");
		
		if($data)
		{
			$data['price'] = $order_info['pay_amount']-$order_info['refund_amount']>$data['price']?$data['price']:$order_info['pay_amount']-$order_info['refund_amount'];
			if($data['price']<0)$data['price'] = 0;
			$this->assign("data",$data);
			$this->assign("order_info",$order_info);
			$obj['status'] = true;
			$obj['html'] = $this->fetch();
			ajax_return($obj);
		}
		else		
		$this->error("非法请求",1);
	}
	
	/**
	 * 退款执行流：
	 * 1. 退还金额至会员账户
	 * 2. 更新商家账户
	 * 3. 更新订单及订单关联表的相关状态
	 * 3. 更新平台报表
	 * 4. 更新订单缓存
	 * 5. 为订单重新分片
	 */
	public function do_refund()
	{
		$order_item_id = intval($_REQUEST['order_item_id']);
		$coupon_id = intval($_REQUEST['coupon_id']);
		$price = floatval($_REQUEST['price']);
		$balance_price = floatval($_REQUEST['balance_price']);
		$content = strim($_REQUEST['content']);
		if($price<0||$balance_price<0)
		{
			$this->error("金额出错",1);
		}
		
		if($order_item_id)
		{
			$oi = $order_item_id;
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
			if($data['refund_status']==2) 
			{
				$this->error("已退款",1);
			}
			if($data)
			{
				$order_id = $data['order_id'];
				$supplier_id = $data['supplier_id'];
			}
					
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 2,is_arrival = 0 where id = ".$order_item_id);
			
			$refund_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where (refund_status = 1 or is_arrival = 2) and order_id = ".$order_id);
			$coupon_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where refund_status = 1 and order_id = ".$order_id);
			if(intval($refund_item_count)==0&&intval($coupon_item_count)==0)
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount + ".$price.",refund_money = refund_money + ".$price.",refund_status = 2,after_sale = 1,is_refuse_delivery=0 where id = ".$order_id);
			else
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount + ".$price.",refund_money = refund_money + ".$price.",is_refuse_delivery=0 where id = ".$order_id);
					
		}
		elseif($coupon_id)
		{
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$coupon_id);
			if($data['refund_status']==2)
			{
				$this->error("已退款",1);
			}
			if($data)
			{
				$oi = $data['order_deal_id'];
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$data['order_deal_id']);
				$data['name'] = $order_item['name'];
				$order_id = $data['order_id'];		
				$supplier_id = $data['supplier_id'];
			}
			
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set refund_status = 2 where id = ".$coupon_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 2 where id = ".$data['order_deal_id']);
			
			$refund_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_order_item where (refund_status = 1 or is_arrival = 2) and order_id = ".$order_id);
			$coupon_item_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where refund_status = 1 and order_id = ".$order_id);
			if(intval($refund_item_count)==0&&intval($coupon_item_count)==0)
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount + ".$price.",refund_money = refund_money + ".$price.",refund_status = 2,after_sale = 1 where id = ".$order_id);
			else
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_amount = refund_amount + ".$price.",refund_money = refund_money + ".$price." where id = ".$order_id);
					
		}
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		
		if($price>0)
		{
			require_once APP_ROOT_PATH."system/model/user.php";
			modify_account(array("money"=>$price), $order_info['user_id'],$data['name']."退款成功");
			modify_statements($price, 6, $data['name']."用户退款");
		}
		
		if($balance_price>0)
		{
			require_once APP_ROOT_PATH."system/model/supplier.php";
			modify_supplier_account("-".$balance_price, $supplier_id, 1, $data['name']."用户退款"); //冻结资金减少
			modify_supplier_account($balance_price, $supplier_id, 4, $data['name']."用户退款"); //退款增加
			modify_statements($balance_price, 7, $data['name']."用户退款");
		}
		
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		order_log($data['name']."退款成功 ".format_price($price)." ".$content, $order_id);
		auto_over_status($order_id);
		update_order_cache($order_id);
		distribute_order($order_id);
		
		send_msg($order_info['user_id'], $data['name']."退款成功 ".format_price($price)." ".$content, "orderitem", $oi);		
		
		//发微信通知
		$weixin_conf = load_auto_cache("weixin_conf");
		if($weixin_conf['platform_status']==1)
		{
			$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$supplier_id);
			send_wx_msg("TM00430", $order_info['user_id'], $wx_account,array("order_id"=>$order_id,"refund_price"=>$price,"deal_name"=>$data['name'],"order_sn"=>$order_info['order_sn']));
		}
		$this->success("退款成功",1);
	}
	
	public function do_refuse()
	{
		$order_item_id = intval($_REQUEST['order_item_id']);
		$coupon_id = intval($_REQUEST['coupon_id']);
		$price = floatval($_REQUEST['price']);
		$balance_price = floatval($_REQUEST['balance_price']);
		$content = strim($_REQUEST['content']);
		
		if($order_item_id)
		{
			$oi = $order_item_id;
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
			if($data['refund_status']==2)
			{
				$this->error("已退款",1);
			}
			if($data)
			{
				$order_id = $data['order_id'];
				$supplier_id = $data['supplier_id'];
			}
				
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 3,is_arrival = 0 where id = ".$order_item_id);
				
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set refund_status = 3,is_refuse_delivery=0 where id = ".$order_id);
			
		}
		elseif($coupon_id)
		{
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$coupon_id);
			if($data['refund_status']==2)
			{
				$this->error("已退款",1);
			}
			if($data)
			{
				$oi = $data['order_deal_id'];
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$data['order_deal_id']);
				$data['name'] = $order_item['name'];
				$order_id = $data['order_id'];
				$supplier_id = $data['supplier_id'];
			}
				
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set refund_status = 3 where id = ".$coupon_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set refund_status = 3 where id = ".$data['order_deal_id']);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set  refund_status = 3  where id = ".$order_id);
			
				
		}
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		
		
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		order_log($data['name']."退款不通过 "." ".$content, $order_id);
		auto_over_status($order_id);
		update_order_cache($order_id);
		distribute_order($order_id);
		
		send_msg($order_info['user_id'], $data['name']."退款不通过 "." ".$content, "orderitem", $oi);
		$this->success("操作成功",1);
	}
	
	
	public function do_verify()
	{
		$order_item_id = intval($_REQUEST['order_item_id']);
		$coupon_id = intval($_REQUEST['coupon_id']);
		
		if($order_item_id)
		{
			$oi = $order_item_id;
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$data['order_id']);
			$delivery_notice = $GLOBALS['db']->getRow("select n.* from ".DB_PREFIX."delivery_notice as n left join ".DB_PREFIX."deal_order as o on n.order_id = o.id where n.order_item_id = ".$order_item_id." and o.id = ".$data['order_id']." and is_arrival <> 1 order by delivery_time desc");
				
			if($delivery_notice)
			{
				require_once APP_ROOT_PATH."system/model/deal_order.php";
				$res = confirm_delivery($delivery_notice['notice_sn'],$order_item_id);
				if($res)
				{
					send_msg($order_info['user_id'], "订单经管理员审核，确认收货", "orderitem", $oi);
					$data['status'] = true;
					$data['info'] = "操作收货成功";
					ajax_return($data);
				}
				else
				{
					$data['status'] = 0;
					$data['info'] = "操作收货失败";
					ajax_return($data);
				}
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = "订单已收货";
				ajax_return($data);
			}
		}
		elseif($coupon_id)
		{
			$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$coupon_id);
			if($data['refund_status']==2)
			{
				$this->error("已退款",1);
			}
			if($data)
			{
				$oi = $data['order_deal_id'];
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$data['order_deal_id']);
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_item['order_id']);
				require_once APP_ROOT_PATH."system/model/deal_order.php";
				$rs = use_coupon($data['password'],0,0,true,true);
				if($rs)
				{
					$this->success("验证成功",1);
				}
				else
				{
					$this->error("验证失败",1);
				}
			}
			else
			{
				$this->error("非法操作",1);
			}			
		
		}
		
		
	}
	
	public function cancel()
	{
	
			$id = intval($_REQUEST['id']);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$id);
			if($order_info)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set is_delete = 1 where (order_status = 1 or pay_status = 0) and is_delete = 0 and  id = ".$id);
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
							order_log("交易被关闭，退回余额支付 ".$order_info['account_money']." 元", $order_info['id']);
						}
						if($order_info['ecv_id'])
						{
							$GLOBALS['db']->query("update ".DB_PREFIX."ecv set use_count = use_count - 1 where id = ".$order_info['ecv_id']);
							order_log("交易被关闭，代金券退回 ", $order_info['id']);
						}
	
					}
					over_order($order_info['id']);
					$this->success("操作成功");
				}
				else
				{
					$this->error("交易无法被关闭");
				}
			}
			else
			{
				$this->error("订单不存在");
			}
		
	}
}
?>