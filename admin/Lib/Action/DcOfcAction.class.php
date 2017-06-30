<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

define("SALE_COLOR","#404d60");
define("REFUND_COLOR","#10b9a5");
define("VERIFY_COLOR","#ff6600");
class DcOfcAction extends CommonAction{
	
	
	/**
	 * 订单来路的图饼展示
	 */
	public function order_referer()
	{
		$where = " 1=1 ";
			
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
		$sql = "select referer,count(id) as ct from ".DB_PREFIX."deal_order where ".$where." and referer <> '' group by referer having count(id) > 0 order by ct desc limit 8 ";
		$total_sql = "select count(*) from ".DB_PREFIX."deal_order where ".$where;
		$colors = array("0xAAAAAA", "0x669999",
		"0xBBBB55", "0xCC6600", "0x9999FF", "0x0066CC",
		"0x99CCCC", "0x999999", "0xFFCC00", "0x009999",
		"0x99CC33", "0xFF9900", "0x999966", "0x66CCCC",
		"0x339966", "0xCCCC33");
		
		$total = intval($GLOBALS['db']->getOne($total_sql));
		$list = $GLOBALS['db']->getAll($sql);
		
		$items = array();
		foreach($list as $k=>$v)
		{
			$total -= intval($v['ct']);
			$items[] = array("value"=>intval($v['ct']),"tip"=>$v['referer'],"on-click"=>"jump_to('".u("DealOrder/deal_index",array("referer"=>$v['referer']))."')");
		}

		$items[] = array("value"=>$total,"tip"=>"直接访问","on-click"=>"jump_to('".u("DealOrder/deal_index",array("referer"=>-1))."')");
		
		$data['bg_colour']	= "#ffffff";
		$data['elements'] = array(
				array(
						"type"=>"pie",
						"colours"=>$colors,
						"alpha"	=>	1,
						"border"	=> 2,
						"start-angle"=>35,
						"values"=>$items
				)
		);
		
		ajax_return($data);
	}
	
	
	public function sale_line()
	{

		
		//定义天数最近30天
		$begin_time = NOW_TIME - 30*24*3600;
		$end_time = NOW_TIME;
		$begin_time_date = to_date($begin_time,"Y-m-d");
		$end_time_date = to_date($end_time,"Y-m-d");
		
		$x_labels = array();  //x轴的标题
		for($i=0;$i<30;$i++)
		{
			$x_labels[] = to_date($begin_time+$i*24*3600,"d");
		}		
		$result['x_axis'] = array("labels"=>array("labels"=>$x_labels));
		
		$sql = "select income_order,refund_money,verify_money,stat_time from ".DB_PREFIX."statements where stat_time > '".$begin_time_date."' and stat_time <= '".$end_time_date."'";		
		$stat_result = $GLOBALS['db']->getAll($sql);

		//开始定义每个数据的线条元素
		$max_value = 0;
		
		//销售额线条元素
		$sale_line_values = array();
		for($i=0;$i<=30;$i++)
		{
			$stat_time = to_date($begin_time+$i*24*3600,"Y-m-d");
			$data_row = array("value"=>0,"tip"=>$stat_time."营业额0元");
			foreach($stat_result as $row)
			{				
				if($row['stat_time']==$stat_time)
				{				
					if($row['income_order']>$max_value)$max_value = $row['income_order'];
					$data_row = array("value"=>floatval($row['income_order']),"tip"=>$stat_time."营业额".round($row['income_order'],2)."元");
				}				
			}
			$sale_line_values[] = $data_row;
		}
		$sale_line_element = array("type"=>"line","colour"=>SALE_COLOR,"text"=>"营业额","width"=>2,"values"=>$sale_line_values);
		
		
		
		//退款额线条元素
		$refund_line_values = array();
		for($i=0;$i<=30;$i++)
		{
			$stat_time = to_date($begin_time+$i*24*3600,"Y-m-d");
			$data_row = array("value"=>0,"tip"=>$stat_time."退款额0元");
			foreach($stat_result as $row)
			{				
				if($row['stat_time']==$stat_time)
				{
					if($row['refund_money']>$max_value)$max_value = $row['refund_money'];
					$data_row = array("value"=>floatval($row['refund_money']),"tip"=>$stat_time."退款额".round($row['refund_money'],2)."元");
				}
			}
			$refund_line_values[] = $data_row;
		}
		$refund_line_element = array("type"=>"line","colour"=>REFUND_COLOR,"text"=>"退款额","width"=>2,"values"=>$refund_line_values);
		
		//消费额线条元素
		$verify_line_values = array();
		for($i=0;$i<=30;$i++)
		{
			$stat_time = to_date($begin_time+$i*24*3600,"Y-m-d");
			$data_row = array("value"=>0,"tip"=>$stat_time."消费额0元");
			foreach($stat_result as $row)
			{				
				if($row['stat_time']==$stat_time)
				{
					if($row['verify_money']>$max_value)$max_value = $row['verify_money'];
					$data_row = array("value"=>floatval($row['verify_money']),"tip"=>$stat_time."消费额".round($row['verify_money'],2)."元");
				}
			}
			$verify_line_values[] = $data_row;
		}
		$verify_line_element = array("type"=>"line","colour"=>VERIFY_COLOR,"text"=>"消费额","width"=>2,"values"=>$verify_line_values);
		
		$max_value = ofc_max($max_value);
		
		$result['y_axis'] = array("max"=>floatval($max_value));
		$result['elements'] = array($sale_line_element,$refund_line_element,$verify_line_element);
		$result['bg_colour']	= "#ffffff";
		
		
		ajax_return($result);
	}
	
	public function sale_refund()
	{		
		//定义天数最近30天
		$begin_time = NOW_TIME - 30*24*3600;
		$end_time = NOW_TIME;
		$begin_time_date = to_date($begin_time,"Y-m-d");
		$end_time_date = to_date($end_time,"Y-m-d");
		
		
		$sql = "select sum(income_order) as income_order,sum(refund_money) as refund_money,sum(verify_money) as verify_money from ".DB_PREFIX."statements where stat_time > '".$begin_time_date."' and stat_time <= '".$end_time_date."'";
		$stat_data = $GLOBALS['db']->getRow($sql);
		
		$not_verify = $stat_data['income_order'] - $stat_data['refund_money'] - $stat_data['verify_money'];
		

		$values[] = array("value"=>floatval($stat_data['income_order']),"label"=>round($stat_data['income_order'],2)."元", "tip"=>"营业额".round($stat_data['income_order'],2)."元");		
		$values[] = array("value"=>floatval($stat_data['refund_money']),"label"=>round($stat_data['refund_money'],2)."元", "tip"=>"退款".round($stat_data['refund_money'],2)."元");
		$values[] = array("value"=>floatval($stat_data['verify_money']),"label"=>round($stat_data['verify_money'],2)."元", "tip"=>"消费".round($stat_data['verify_money'],2)."元");

		
		$data['bg_colour']	= "#ffffff";
		$data['elements'] = array(
				array(
						"type"=>"pie",
						"colours"=>array(SALE_COLOR,REFUND_COLOR,VERIFY_COLOR),
						"alpha"	=>	1,
						"border"	=> 2,
						"start-angle"=>35,
						"values"=>$values
				)
		);
		
		ajax_return($data);
	}
	
	
	
	
	public function sale_month_line()
	{
	
	
		$year = intval($_REQUEST['year']);
		$month = intval($_REQUEST['month']);
		
		$current_year = intval(to_date(NOW_TIME,"Y"));
		$current_month = intval(to_date(NOW_TIME,"m"));
		
		if($year==0)$year = $current_year;
		if($month==0)$month = $current_month;
		
		$days_list = array(31,28,31,30,31,30,31,31,30,31,30,31);
		$days = $days_list[$month-1];
		if($days==28&&$year%4==0&&($year%100!=0||$year%400==0))
		{
			$days = 29;
		}
				
		$stat_month = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);
		
		//月数据	
		$x_labels = array();  //x轴的标题
		for($i=1;$i<=$days;$i++)
		{
			$x_labels[] = $i."日";
		}
		$result['x_axis'] = array("labels"=>array("labels"=>$x_labels));
	
		$sql = "select order_num,sale_money,stat_time from ".DB_PREFIX."dc_statements where stat_month = '".$stat_month."'";
		$stat_result = $GLOBALS['db']->getAll($sql);

		//开始定义每个数据的线条元素
		$max_value = 0;
	
		//销售额线条元素
		$sale_line_values = array();
		for($i=1;$i<=$days;$i++)
		{
			$stat_time = $stat_month."-".str_pad($i,2,"0",STR_PAD_LEFT);
			$data_row = array("value"=>0,"tip"=>$stat_time."营业额0元");
			foreach($stat_result as $row)
			{
				if($row['stat_time']==$stat_time)
				{
					if($row['sale_money']>$max_value)$max_value = $row['sale_money'];
					$data_row = array("value"=>floatval($row['sale_money']),"tip"=>$stat_time."营业额".round($row['sale_money'],2)."元");
				}
			}
			$sale_line_values[] = $data_row;
		}
		$sale_line_element = array("type"=>"line","colour"=>SALE_COLOR,"text"=>"营业额","width"=>2,"values"=>$sale_line_values);
	
	
	
		//订单数线条元素
		$order_num = array();
		for($i=1;$i<=$days;$i++)
		{
			$stat_time = $stat_month."-".str_pad($i,2,"0",STR_PAD_LEFT);
			$data_row = array("value"=>0,"tip"=>$stat_time."订单数0单");
			foreach($stat_result as $row)
			{
				if($row['stat_time']==$stat_time)
				{
					if($row['order_num']>$max_value)$max_value = $row['order_num'];
					$data_row = array("value"=>floatval($row['order_num']),"tip"=>$stat_time."订单数".round($row['order_num'],2)."单");
				}
			}
			$order_num[] = $data_row;
		}
		$order_num = array("type"=>"line","colour"=>REFUND_COLOR,"text"=>"订单数","width"=>2,"values"=>$order_num);
	

		$max_value = ofc_max($max_value);

		$result['y_axis'] = array("max"=>floatval($max_value));
		$result['elements'] = array($sale_line_element,$order_num);
		$result['bg_colour']	= "#ffffff";


		ajax_return($result);
	}
	
}
?>