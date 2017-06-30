<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class DcBalanceAction extends CommonAction{

	public function index()
	{
		$balance_title = "运营数据";
		$type=0;
		$year = intval($_REQUEST['year']);
		$month = intval($_REQUEST['month']);
		
		$current_year = intval(to_date(NOW_TIME,"Y"));
		$current_month = intval(to_date(NOW_TIME,"m"));
		
		if($year==0)$year = $current_year;
		if($month==0)$month = $current_month;
		
		$year_list = array();
		for($i=$current_year-10;$i<=$current_year+10;$i++)
		{
			$current = $year==$i?true:false;
			$year_list[] = array("year"=>$i,"current"=>$current);
		}
		
		$month_list = array();
		for($i=1;$i<=12;$i++)
		{
			$current = $month==$i?true:false;
			$month_list[] = array("month"=>$i,"current"=>$current);
		}
		
		
		$this->assign("year_list",$year_list);
		$this->assign("month_list",$month_list);
		
		$this->assign("cyear",$year);
		$this->assign("cmonth",$month);
		
		
		$begin_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		
		$next_month = $month+1;
		$next_year = $year;
		if($next_month > 12)
		{
			$next_month = 1;
			$next_year = $next_year + 1;
		}
		$end_time = $next_year."-".str_pad($next_month,2,"0",STR_PAD_LEFT)."-01";
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
		
		$this->assign("balance_title",$year."-".str_pad($month,2,"0",STR_PAD_LEFT)." ".$balance_title);
		$this->assign("month_title",$year."-".str_pad($month,2,"0",STR_PAD_LEFT));
		//
		
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

		$model = D ("DcStatementsLog");
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
		
		//开始计算利润率		
		$stat_month = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);		
		$sql = "select sum(order_num) as order_num,
				sum(sale_money) as sale_money,
				sum(balance_money) as balance_money,
				sum(online_pay_money) as online_pay_money,
				sum(promote_money) as promote_money,
				sum(ecv_money) as ecv_money,
				sum(refund_money) as refund_money,
				sum(admin_charges) as admin_charges from ".DB_PREFIX."dc_statements where stat_month = '".$stat_month."'";
		$stat_result = $GLOBALS['db']->getRow($sql);	
		$this->assign("stat_result",$stat_result);		
		$this->display ();
		return;
	}
	
	
	public function foreverdelete() {
		$year = intval($_REQUEST['year']);
		$month = intval($_REQUEST['month']);
		
		if($year==0||$month==0)
		{
			$this->error("请选择日期");
		}
		
		
		$begin_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		
		$next_month = $month+1;
		$next_year = $year;
		if($next_month > 12)
		{
			$next_month = 1;
			$next_year = $next_year + 1;
		}
		$end_time = $next_year."-".str_pad($next_month,2,"0",STR_PAD_LEFT)."-01";
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
		
		$stat_month = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);
		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_statements_log where create_time between $begin_time_s and $end_time_s");
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_statements where stat_month = '".$stat_month."'");
		
		$this->success("清空成功");
		
	}
	
	/**
	 * 结算报表
	 * 针对商户的报表查看
	 */
	public function bill()
	{
	
		//
		$year = intval($_REQUEST['year']);
		$month = intval($_REQUEST['month']);
		
		$current_year = intval(to_date(NOW_TIME,"Y"));
		$current_month = intval(to_date(NOW_TIME,"m"));
		
		if($year==0)$year = $current_year;
		if($month==0)$month = $current_month;
		
		$year_list = array();
		for($i=$current_year-10;$i<=$current_year+10;$i++)
		{
		$current = $year==$i?true:false;
		$year_list[] = array("year"=>$i,"current"=>$current);
		}
		
		$month_list = array();
		for($i=1;$i<=12;$i++)
		{
		$current = $month==$i?true:false;
		$month_list[] = array("month"=>$i,"current"=>$current);
		}
		
		
		$this->assign("year_list",$year_list);
		$this->assign("month_list",$month_list);

		$this->assign("cyear",$year);
		$this->assign("cmonth",$month);
		
		
		$begin_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		
		$next_month = $month+1;
		$next_year = $year;
		if($next_month > 12)
		{
		$next_month = 1;
		$next_year = $next_year + 1;
		}
		$end_time = $next_year."-".str_pad($next_month,2,"0",STR_PAD_LEFT)."-01";
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
	
		$month_format = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);
		
		$this->assign("balance_title",$month_format);
		$this->assign("month_title",$month_format);
		//
			
		//取商户数据
		$page_idx = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		$page_size = C('PAGE_LISTROWS');
		$limit = (($page_idx-1)*$page_size).",".$page_size;
		
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		}

		if(substr($order, 0,6)=="month_")
			$order = null;
		
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}	
		
		if(strim($_REQUEST['name'])!='')
		{
			
			$list = $GLOBALS['db']->getAll("select s.* from ".DB_PREFIX."supplier as s left join ".DB_PREFIX."supplier_location as sl on s.id=sl.supplier_id where s.name like '%".strim($_REQUEST['name'])."%' and (sl.is_dc=1 or sl.is_reserve=1) limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(s.*) from ".DB_PREFIX."supplier as s left join ".DB_PREFIX."supplier_location as sl on s.id=sl.supplier_id where s.name like '%".strim($_REQUEST['name'])."%' and (sl.is_dc=1 or sl.is_reserve=1)");

		}
		else
		{
			$list= $GLOBALS['db']->getAll("select s.* from ".DB_PREFIX."supplier as s left join ".DB_PREFIX."supplier_location as sl on s.id=sl.supplier_id where sl.is_dc=1 or sl.is_reserve=1 limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(s.*) from ".DB_PREFIX."supplier as s left join ".DB_PREFIX."supplier_location as sl on s.id=sl.supplier_id where sl.is_dc=1 or sl.is_reserve=1");
		}
		$p = new Page ( $total, '' );
		$page = $p->show ();
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
			
		//查询当月报表
		foreach($list as $k=>$v)
		{
			$sql = "select sum(sale_money) as sale_money,sum(balance_money) as balance_money,
					sum(unbalance_money) as unbalance_money,sum(confirm_money) as confirm_money,
					sum(unconfirm_money) as unconfirm_money,sum(online_pay_money) as online_pay_money,
					sum(promote_money) as promote_money,sum(ecv_money) as ecv_money,sum(refund_money) as refund_money,
					sum(admin_charges) as admin_charges from ".DB_PREFIX."dc_supplier_statements where supplier_id = ".$v['id']." and stat_month = '".$month_format."'";			
			$stat_row = $GLOBALS['db']->getRow($sql);
			$list[$k]['sale_money'] = $stat_row['sale_money'];
			$list[$k]['balance_money'] = $stat_row['balance_money'];
			$list[$k]['unbalance_money'] = $stat_row['unbalance_money'];
			$list[$k]['confirm_money'] = $stat_row['confirm_money'];
			$list[$k]['unconfirm_money'] = $stat_row['unconfirm_money'];
			$list[$k]['online_pay_money'] = $stat_row['online_pay_money'];
			$list[$k]['promote_money'] = $stat_row['promote_money'];
			$list[$k]['ecv_money'] = $stat_row['ecv_money'];
			$list[$k]['refund_money'] = $stat_row['refund_money'];
			$list[$k]['admin_charges'] = $stat_row['admin_charges'];			
		}
		
		if (isset ( $_REQUEST ['_order'] ) && isset ( $_REQUEST ['_sort'] )) {
			require_once APP_ROOT_PATH."system/model/dc.php";
			$list=array_sort($list,$order,$sort);
		}
		
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);			
		//end 

		
		$this->display ();
		return;
	}
	
	public function supplier_order(){
		//$type=1为销售明细, $type=2为已完成明细 , $type=3为退款 , $type=4为已结算明细
		$type = intval($_REQUEST['type']);
		if($type==0){
			$type=1;
		}
		$supplier_id=intval($_REQUEST['id']);
		$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id=".$supplier_id);
		//
		$year = intval($_REQUEST['year']);
		$month = intval($_REQUEST['month']);
		
		$current_year = intval(to_date(NOW_TIME,"Y"));
		$current_month = intval(to_date(NOW_TIME,"m"));
		
		if($year==0)$year = $current_year;
		if($month==0)$month = $current_month;
		
		$year_list = array();
		for($i=$current_year-10;$i<=$current_year+10;$i++)
		{
		$current = $year==$i?true:false;
		$year_list[] = array("year"=>$i,"current"=>$current);
		}
		
		$month_list = array();
		for($i=1;$i<=12;$i++)
		{
		$current = $month==$i?true:false;
		$month_list[] = array("month"=>$i,"current"=>$current);
		}
		

		$this->assign("year_list",$year_list);
		$this->assign("month_list",$month_list);
		
		$this->assign("cyear",$year);
		$this->assign("cmonth",$month);
		
		
		$begin_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i:s");
		
		$next_month = $month+1;
		$next_year = $year;
		if($next_month > 12)
		{
		$next_month = 1;
		$next_year = $next_year + 1;
		}
		$end_time = $next_year."-".str_pad($next_month,2,"0",STR_PAD_LEFT)."-01";
		$end_time_s = to_timespan($end_time,"Y-m-d H:i:s");
	
		$month_format = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);
	
		$this->assign("balance_title",$month_format);
		$this->assign("month_title",$month_format);
		
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		}
		
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		//模板赋值显示
		$this->assign ( 'sort', $sort );
		$this->assign ( 'order', $order );
		$this->assign ( 'sortImg', $sortImg );
		$this->assign ( 'sortType', $sortAlt );
		
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page_size = 20;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		if($type==1){
			//销售明细
			$sql="select * , online_pay + account_money as online_pay , case when balance_price > 0 then total_price - balance_price when balance_price < 0 then -balance_price end as admin_charges from ".DB_PREFIX."dc_supplier_order where supplier_id=".$supplier_id;
			if($begin_time > 0){
				$sql.=" and create_time >= $begin_time_s";
			}
			if($end_time > 0){
				$sql.=" and create_time <= $end_time_s";
			}
			$condition=$sql;
			$sql.=" order by create_time desc limit ".$limit;
	
		}elseif($type==2){
			//已完成明细
			$sql="select * , online_pay + account_money as online_pay , case when balance_price > 0 then total_price - balance_price when balance_price < 0 then -balance_price end as admin_charges from ".DB_PREFIX."dc_supplier_order where confirm_status=2 and supplier_id=".$supplier_id;
			if($begin_time > 0){
				$sql.=" and confirm_time >= $begin_time_s";
			}
			if($end_time > 0){
				$sql.=" and confirm_time <= $end_time_s";
			}
			$condition=$sql;
			$sql.=" order by confirm_time desc limit ".$limit;
	
		}elseif($type==3){
			//退款
			$sql="select * , online_pay + account_money as online_pay , case when balance_price > 0 then total_price - balance_price when balance_price < 0 then -balance_price end as admin_charges from ".DB_PREFIX."dc_supplier_order where refund_status=2 and supplier_id=".$supplier_id;
			if($begin_time > 0){
				$sql.=" and refund_time >= $begin_time_s";
			}
			if($end_time > 0){
				$sql.=" and refund_time <= $end_time_s";
			}
			$condition=$sql;
			$sql.=" order by refund_time desc limit ".$limit;
	
		}elseif($type==4){
			//已结算明细
			$sql="select * , online_pay + account_money as online_pay , case when balance_price > 0 then total_price - balance_price when balance_price < 0 then -balance_price end as admin_charges from ".DB_PREFIX."dc_supplier_order where order_status=1 and supplier_id=".$supplier_id;
			if($begin_time > 0){
				$sql.=" and balance_time >= $begin_time_s";
			}
			if($end_time > 0){
				$sql.=" and balance_time <= $end_time_s";
			}
			$condition=$sql;
			$sql.=" order by balance_time desc limit ".$limit;
	
		}
	
		$list=$GLOBALS['db']->getAll($sql);
		
		foreach($list as $k=>$v){
			if($type==1){
			$list[$k]['time']=$v['create_time'];
			}elseif($type==2){
			$list[$k]['time']=$v['confirm_time'];
			}elseif($type==3){
			$list[$k]['time']=$v['refund_time'];
			}elseif($type==4){
			$list[$k]['time']=$v['balance_time'];
			}
			
		}
		if (isset ( $_REQUEST ['_order'] ) && isset ( $_REQUEST ['_sort'] )) {
			require_once APP_ROOT_PATH."system/model/dc.php";
			$list=array_sort($list,$order,$sort);
		}
		$all_total=$GLOBALS['db']->getAll($condition);

		$count=intval(count($all_total));
		$page = new Page($count, $page_size); // 初始化分页对象
		$p = $page->show();
			
		$this->assign('page', $p);
		$this->assign('type', $type);
		$this->assign("list", $list);
		$this->assign("supplier_info", $supplier_info);
		$form_url=url('biz','dcborder');
		$this->assign("form_url", $form_url);
		/* 系统默认 */
		$this->assign("page_title", "对帐单");
		$this->display();
	}
	
	
}
?>