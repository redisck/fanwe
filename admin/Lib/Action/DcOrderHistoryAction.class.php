<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

class DcOrderHistoryAction extends CommonAction{

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
		if($order_status==4){
			//已结单
			$where.= " and is_cancel=0 and refund_status=0 and confirm_status =2 and order_status=1";		
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
		
	
		
		$count = M("DcOrderHistory")
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

			$voList = M("DcOrderHistory")
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
				//print_r($order_menu);die;
				
				
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
		$order_info = M("DcOrderHistory")->where("id=".$id." and type_del = 0 and is_rs = 0")->find();
		
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
			$notice_info=unserialize($order_info['history_payment_notice']);//付款单号信息
			
			$order_menu=unserialize($order_info['order_menu']);
			$order_info['promote_str']=unserialize($order_info['promote_str']);
			$order_info['menu_list']=$order_menu['menu_list'];

		//输出订单日志
		$log_list = unserialize($order_info['history_dc_order_log']);
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

		$this->assign ( "notice_info", $notice_info );		
		$this->assign ( "order_info", $order_info );
		}
		$this->display();
	}

	
	public function delete() {
		//删除指定记录
		require_once APP_ROOT_PATH."system/model/dc.php";
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("DcOrderHistory")->where($condition)->findAll();					
				foreach($rel_data as $data)
				{
						$info[] = $data['order_sn'];
				}
				if($info) $info = implode(",", $info);
				
				$list = M("DcOrderHistory")->where ( $condition )->delete();
				
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
		$where.= " and is_rs = 0";
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
		
			

			$voList = M("DcOrderHistory")
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
