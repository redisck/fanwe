<?php 
/**
 * 财务报表
 */
require APP_ROOT_PATH.'app/Lib/page.php';
require_once APP_ROOT_PATH."system/model/user.php";
class balanceModule extends BizBaseModule
{
    
	function __construct()
	{
        parent::__construct();
        global_run();
        $this->check_auth();
    }
	
    
	public function index()
	{			
		init_app_page();
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		
		$begin_time = strim($_REQUEST['begin_time']);
		$begin_time = to_date(to_timespan($begin_time,"Y-m-d H:i"),"Y-m-d H:i");
		$end_time = strim($_REQUEST['end_time']);
		$end_time = to_date(to_timespan($end_time,"Y-m-d H:i"),"Y-m-d H:i");
		
		$begin_time_s = to_date(to_timespan($begin_time,"Y-m-d H:i"),"Y-m-d");
		$end_time_s = to_date(to_timespan($end_time,"Y-m-d H:i"),"Y-m-d");

		if(empty($begin_time_s))$begin_time_s = to_date(NOW_TIME,"Y-m-d");
		if(empty($end_time_s))$end_time_s = to_date(NOW_TIME,"Y-m-d");
		
		$begin_time_m =  to_date(to_timespan($begin_time_s,"Y-m-d"),"Y-m");
		$end_time_m =  to_date(to_timespan($end_time_s,"Y-m-d"),"Y-m");	
		
		
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);		

		
		if($begin_time_m==$end_time_m)
		{
			$month_stat = true; //本月日报形式
		}
		else 
		{
			$month_stat = false; //区间内的月报形式
		}		
		if($month_stat)
		{
			$GLOBALS['tmpl']->assign("stat_title",$begin_time_m);
			$sql = "select sum(money) as money,sum(sale_money) as sale_money,sum(refund_money) as refund_money,sum(wd_money) as wd_money from ".DB_PREFIX."supplier_statements where supplier_id = ".$supplier_id." and stat_month = '".$begin_time_m."'";

			$stat_row = $GLOBALS['db']->getRow($sql);
		}
		else 
		{ 
			$GLOBALS['tmpl']->assign("stat_title",$begin_time_m."至".$end_time_m);
			$stat_row = $GLOBALS['db']->getRow("select sum(money) as money,sum(sale_money) as sale_money,sum(refund_money) as refund_money,sum(wd_money) as wd_money from ".DB_PREFIX."supplier_statements where supplier_id = ".$supplier_id." and stat_time > '".$begin_time_m."' and stat_time < '".$end_time."'");
		}
		
		$GLOBALS['tmpl']->assign("stat_info",$stat_row);
		
		$supplier_info = $GLOBALS['db']->getRow("select money,lock_money,sale_money,refund_money,wd_money from ".DB_PREFIX."supplier where id = ".$supplier_id);
		$GLOBALS['tmpl']->assign("supplier_info",$supplier_info);


		$GLOBALS['tmpl']->assign("ofc_data_url",urlencode(url("biz","ofc#balance",array("r"=>NOW_TIME))));
		$GLOBALS['tmpl']->display("pages/balance/index.html");	
	}
	
	
	/**
	 * 走势图
	 */
	public function line()
	{
		init_app_page();
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		
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

		
		$GLOBALS['tmpl']->assign("year_list",$year_list);
		$GLOBALS['tmpl']->assign("month_list",$month_list);
		
		$GLOBALS['tmpl']->assign("year",$year);
		$GLOBALS['tmpl']->assign("month",$month);
		
		$GLOBALS['tmpl']->assign("ofc_data_url",urlencode(url("biz","ofc#balance_line",array("year"=>$year,"month"=>$month,"r"=>NOW_TIME))));
		
		$GLOBALS['tmpl']->display("pages/balance/line.html");
	}
	
	
	public function detail()
	{
		init_app_page();
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		
		$begin_time = strim($_REQUEST['begin_time']);
		$begin_time = to_date(to_timespan($begin_time,"Y-m-d H:i"),"Y-m-d H:i");
		$end_time = strim($_REQUEST['end_time']);
		$end_time = to_date(to_timespan($end_time,"Y-m-d H:i"),"Y-m-d H:i");
		
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i");
		$end_time_s = to_timespan($end_time,"Y-m-d H:i");		

		
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$type = intval($_REQUEST['type']);
		if($type!=1&&$type!=3&&$type!=5&&$type!=4)
			$type = 1;
		$GLOBALS['tmpl']->assign("type",$type);
		
		$condition = " 1=1 and supplier_id = ".$supplier_id." and type = ".$type." and money > 0 ";
		if($begin_time_s)
		{
			$condition.=" and create_time > ".$begin_time_s." ";
		}
		if($end_time_s)
		{
			$condition.=" and create_time < ".$end_time_s." ";
		}
		
		//分页
		require_once APP_ROOT_PATH.'app/Lib/page.php';
		$page_size = 25;
		$page = intval($_REQUEST['p']);
		if($page==0) $page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$sql = "select log_info,create_time,money from ".DB_PREFIX."supplier_money_log where ".$condition." order by id desc limit ".$limit;
		$sql_count = "select count(*) from ".DB_PREFIX."supplier_money_log where ".$condition;
		
		$sql_sum = "select sum(money) from ".DB_PREFIX."supplier_money_log where ".$condition;
		$sum = $GLOBALS['db']->getOne($sql_sum);
		$GLOBALS['tmpl']->assign('sum',$sum);
				
		$list = $GLOBALS['db']->getAll($sql);
		$total = $GLOBALS['db']->getOne($sql_count);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$page_sum = 0;
		foreach($list as $k=>$v)
		{
			$page_sum+=floatval($v['money']);
		}
		$GLOBALS['tmpl']->assign('page_sum',$page_sum);
		
		$GLOBALS['tmpl']->assign("list",$list);
		
		
		$GLOBALS['tmpl']->display("pages/balance/detail.html");
	}
	
	
	

}
?>