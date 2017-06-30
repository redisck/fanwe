<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class ofcModule extends BizBaseModule{
	public function balance()
	{
		global_run();		
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		if(empty($s_account_info))
		{
			die("请先登录");
		}
		
		$stat_data = $GLOBALS['db']->getRow("select money,lock_money,sale_money,refund_money,wd_money from ".DB_PREFIX."supplier where id = ".$supplier_id);
		
		$values[] = array("value"=>floatval($stat_data['money']),"label"=>round($stat_data['money'],2)."元", "tip"=>"已消费未提现".round($stat_data['money'],2)."元");
		$values[] = array("value"=>floatval($stat_data['lock_money']),"label"=>round($stat_data['lock_money'],2)."元", "tip"=>"未消费".round($stat_data['lock_money'],2)."元");
		$values[] = array("value"=>floatval($stat_data['refund_money']),"label"=>round($stat_data['refund_money'],2)."元", "tip"=>"退款".round($stat_data['refund_money'],2)."元");
		$values[] = array("value"=>floatval($stat_data['wd_money']),"label"=>round($stat_data['wd_money'],2)."元", "tip"=>"已打款".round($stat_data['wd_money'],2)."元");
		
		$data['bg_colour']	= "#ffffff";
		$data['elements'] = array(
				array(
						"type"=>"pie",
						"colours"=>array("#d01f3c","#356aa0","#C79810","#f5a8df"),
						"alpha"	=>	1,
						"border"	=> 2,
						"start-angle"=>35,
						"values"=>$values
				)
		);
		
		ajax_return($data);
	}

	
	/**
	 * 销售额月报表曲线
	 */
	public function balance_line()
	{
		global_run();
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		if(empty($s_account_info))
		{
			die("请先登录");
		}
		
		$year = intval($_REQUEST['year']);
		$month = intval($_REQUEST['month']);
		
		$current_year = intval(to_date(NOW_TIME,"Y"));
		$current_month = intval(to_date(NOW_TIME,"m"));
		
		if($year==0)$year = $current_year;
		if($month==0)$month = $current_month;
		if($month<1)$month=1;
		if($month>12)$month=12;
		
		
		$days_list = array(31,28,31,30,31,30,31,31,30,31,30,31);		
		$days = $days_list[$month-1];		
		if($days==28&&$year%4==0&&($year%100!=0||$year%400==0))
		{
			$days = 29;
		}		
		
		$stat_month = $year."-".str_pad($month,2,"0",STR_PAD_LEFT);  //月份的key
		
		$stat_result = $GLOBALS['db']->getAll("select money,sale_money,refund_money,stat_time from ".DB_PREFIX."supplier_statements where supplier_id = ".$supplier_id." and stat_month = '".$stat_month."'");
		
		$result = array();
		
		
		
		$x_axis_labels = array();  //x轴标题
		for($i=1;$i<=$days;$i++)
		{
			$x_axis_labels[] = $i."日";
		}

		$result['x_axis'] = array(
			"labels" => array(
				"labels"	=> $x_axis_labels	
			)
		);
		
		//开始定义每个数据的线条元素		
		$max_value = 0;
		
		//营业额
		$sale_money_values = array();
		for($i=1;$i<=$days;$i++)
		{			
			$stat_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-".str_pad($i,2,"0",STR_PAD_LEFT);  //天的key
			$data_row = array("value"=>0,"tip"=>$stat_time."营业额0元");
			foreach($stat_result as $k=>$v)
			{
				if($v['stat_time']==$stat_time)
				{
						if($v['sale_money']>$max_value)$max_value = $v['sale_money'];
						$data_row = array("value"=>floatval($v['sale_money']),"tip"=>$stat_time."营业额".round($v['sale_money'],2)."元");
				}
			}
			$sale_money_values[] = $data_row;
		}
		$sale_money_elements = array(
			"type"	=> "line",
			"colour"	=>	"#ff3300",
			"text"=>      "营业额",
			"width"	=>	2,
			"values"	=>	$sale_money_values
		);
		
		//消费数
		$money_values = array();
		for($i=1;$i<=$days;$i++)
		{
			
			$stat_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-".str_pad($i,2,"0",STR_PAD_LEFT);  //天的key
			$data_row = array("value"=>0,"tip"=>$stat_time."消费0元");
			foreach($stat_result as $k=>$v)
			{
				if($v['stat_time']==$stat_time)
				{
					if($v['money']>$max_value)$max_value = $v['money'];
					$data_row = array("value"=>floatval($v['money']),"tip"=>$stat_time."消费".round($v['money'],2)."元");
				}
			}
			$money_values[] = $data_row;
		}		
		$money_elements = array(
			"type"	=> "line",
			"colour"	=>	"#736AFF",
			"text"=>      "消费额",
			"width"	=>	2,
			"values"	=>	$money_values		
		);
		
		
		//退款数
		$refund_money_values = array();
		for($i=1;$i<=$days;$i++)
		{			
			$stat_time = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-".str_pad($i,2,"0",STR_PAD_LEFT);  //天的key
			$data_row = array("value"=>0,"tip"=>$stat_time."退款0元");
			foreach($stat_result as $k=>$v)
			{
				if($v['stat_time']==$stat_time)
				{
						if($v['refund_money']>$max_value)$max_value = $v['refund_money'];
						$data_row = array("value"=>floatval($v['refund_money']),"tip"=>$stat_time."退款".round($v['refund_money'],2)."元");
				}
			}
			$refund_money_values[] = $data_row;
		}
		$refund_money_elements = array(
			"type"	=> "line",
			"colour"	=>	"#008f47",
			"text"=>      "退款额",
			"width"	=>	2,
			"values"	=>	$refund_money_values
		);
	
		$max_value = ofc_max($max_value);
		
		$result['y_axis'] = array("max"=>floatval($max_value));		
		$result['elements'] = array($sale_money_elements,$money_elements,$refund_money_elements);		
		$result['bg_colour']	= "#ffffff";
		
	
		ajax_return($result);
		
	}
}