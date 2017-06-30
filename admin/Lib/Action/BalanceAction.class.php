<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class BalanceAction extends CommonAction{

	public function index()
	{
// 		8..订单收入明细、2. 充值收入收细、5商户提现明细、4会员提现明细、6会员退款明细
		
		$type = intval($_REQUEST['type']);
		if($type!=2&&$type!=4&&$type!=5&&$type!=6&&$type!=8)
			$type = 8;
		
		$this->assign("type",$type);
		
		$balance_title = "销售明细";
		if($type==2)
			$balance_title = "会员充值明细";
		if($type==4)
			$balance_title = "会员提现明细";
		if($type==5)
			$balance_title = "商户打款明细";
		if($type==6)
			$balance_title = "会员退款明细";
		
		
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

		$model = D ("StatementsLog");
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
		$sql = "select sum(income_money) as income_money,
				sum(out_money) as out_money,
				sum(sale_money) as sale_money,
				sum(sale_cost_money) as sale_cost_money,
				sum(refund_money) as refund_money,
				sum(refund_cost_money) as refund_cost_money from ".DB_PREFIX."statements where stat_month = '".$stat_month."'";
		$stat_result = $GLOBALS['db']->getRow($sql);
		
		
		
		$accout_money = floatval($stat_result['income_money']) -  floatval($stat_result['out_money']);
		$gross_money = ( floatval($stat_result['sale_money']) -  floatval($stat_result['sale_cost_money'])) - ( floatval($stat_result['refund_money']) -  floatval($stat_result['refund_cost_money']));
		
		$balance_supplier = floatval($stat_result['sale_cost_money']) - floatval($stat_result['refund_cost_money']);
		
		$this->assign("stat_result",$stat_result);
		$this->assign("accout_money",$accout_money);
		$this->assign("gross_money",$gross_money);
		$this->assign("balance_supplier",$balance_supplier);
		
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
		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."statements_log where create_time between $begin_time_s and $end_time_s");
		$GLOBALS['db']->query("delete from ".DB_PREFIX."statements where stat_month = '".$stat_month."'");
		
		$this->error("清空成功");
		
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
		
		$id = intval($_REQUEST['id']);
		if($id)
			$ex_condition = " and id = ".$id." ";
		
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = 'desc';
		}
		if(isset($order))
		{
			$orderby = "order by ".$order." ".$sort;
		}else
		{
			$orderby = "";
		}
		
		
		
		if(strim($_REQUEST['name'])!='')
		{
			
			$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['name'])."%' $ex_condition  $orderby limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where name like '%".strim($_REQUEST['name'])."%' $ex_condition");

		}
		else
		{
			$list= $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier where 1=1 $ex_condition  $orderby limit ".$limit);
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier where 1=1 $ex_condition");
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
			$sql = "select sum(money) as money,sum(sale_money) as sale_money,sum(refund_money) as refund_money,sum(wd_money) as wd_money from ".DB_PREFIX."supplier_statements where supplier_id = ".$v['id']." and stat_month = '".$month_format."'";			
			$stat_row = $GLOBALS['db']->getRow($sql);
			$list[$k]['month_money'] = $stat_row['money'];
			$list[$k]['month_sale_money'] = $stat_row['sale_money'];
			$list[$k]['month_refund_money'] = $stat_row['refund_money'];
			$list[$k]['month_wd_money'] = $stat_row['wd_money'];
		}
		
		$this->assign ( 'list', $list );
		$this->assign ( "page", $page );
		$this->assign ( "nowPage",$p->nowPage);			
		//end 

		
		$this->display ();
		return;
	}
}
?>