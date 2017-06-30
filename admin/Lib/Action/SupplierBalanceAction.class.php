<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class SupplierBalanceAction extends CommonAction{

	public function index()
	{
		$supplier_id = intval($_REQUEST['id']);
		$supplier_info = M("Supplier")->getById($supplier_id);
		if(!$supplier_info)
		{
			$this->error("非法的商户ID");
		}
		
		$type = intval($_REQUEST['type']);
		if($type!=1&&$type!=3&&$type!=5&&$type!=4)
			$type = 1;
		
		$this->assign("type",$type);
		$this->assign("supplier_info",$supplier_info);
		
		$balance_title = "销售明细";
		if($type==3)
			$balance_title = "消费明细";
		if($type==4)
			$balance_title = "退款明细";
		if($type==5)
			$balance_title = "打款明细";
		
		
		$begin_time = strim($_REQUEST['begin_time']);
		$begin_time = to_date(to_timespan($begin_time,"Y-m-d H:i:s"),"Y-m-d H:i:s");
		$end_time = strim($_REQUEST['end_time']);
		$end_time = to_date(to_timespan($end_time,"Y-m-d H:i:s"),"Y-m-d H:i:s");
		
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
		
		
		$this->assign("begin_time",$begin_time);
		$this->assign("end_time",$end_time);
		
		
		if($begin_time&&$end_time)
			$balance_title = $begin_time."至".$end_time." ".$balance_title;
		elseif($begin_time)
			$balance_title = $begin_time."至今 ".$balance_title;
		elseif($end_time)
			$balance_title = "至".$end_time." ".$balance_title;
		
		
		$this->assign("balance_title",$balance_title);
		
		$map['supplier_id'] = $supplier_id;
		$map['type'] = $type;
		$map['money'] = array("gt",0);
		if($begin_time_s&&$end_time_s)
		{
			$map['create_time'] = array("between",array($begin_time_s,$end_time_s));
		}
		elseif($begin_time_s)
		{
			$map['create_time'] = array("gt",$begin_time_s);
		}
		elseif($end_time_s)
		{
			$map['create_time'] = array("lt",$end_time_s);
		}

		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}

		$model = D ("SupplierMoneyLog");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		
		$sum_money = $model->where($map)->sum("money");
		$this->assign("sum_money",$sum_money);
		
		$voList = $this->get("list");
		$page_sum_money = 0;
		foreach($voList as $row)
		{
			$page_sum_money+=floatval($row['money']);
		}
		$this->assign("page_sum_money",$page_sum_money);
		
		$this->display ();
		return;
	}
	
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
			$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
			
			$list = M("SupplierMoneyLog")->where ( $condition )->delete();
				
			if ($list!==false) {
				save_log(l("FOREVER_DELETE_SUCCESS"),1);
				$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
			} else {
				save_log(l("FOREVER_DELETE_FAILED"),0);
				$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
			}
		} else {
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
}
?>