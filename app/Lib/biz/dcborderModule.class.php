<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
require_once APP_ROOT_PATH."system/model/dc.php";
class dcborderModule extends BizBaseModule
{
   public function index(){
   	
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$account_id = $account_info['id'];
		
		$begin_time = strim($_REQUEST['begin_time']);
		$begin_time = to_date(to_timespan($begin_time,"Y-m-d H:i"),"Y-m-d H:i");
		$end_time = strim($_REQUEST['end_time']);
		$end_time = to_date(to_timespan($end_time,"Y-m-d H:i"),"Y-m-d H:i");
		
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i");
		$end_time_s = to_timespan($end_time,"Y-m-d H:i");		

		
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
	 	//$type=1为销售明细, $type=2为已完成明细 , $type=3为退款 , $type=4为已结算明细 

		$type = intval($_REQUEST['type']);
		if($type==0){
			$type=1;
		}
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page_size = 10;
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
			$sql="select * , online_pay + account_money as online_pay , case when balance_price > 0 then total_price - balance_price when balance_price < 0 then -balance_price end as admin_charges from ".DB_PREFIX."dc_supplier_order where (refund_status=2 or is_cancel > 0) and supplier_id=".$supplier_id;
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
		$p_totol=0;  //本页合计金额
		foreach($list as $k=>$v){
				
			$p_totol+=$v['balance_price'];
		}
			
		$all_total=$GLOBALS['db']->getAll($condition);
		$p_totol_price=0;  //本页合计金额
		foreach($all_total as $k=>$v){
				
			$p_totol_price+=$v['balance_price'];
		}
		$count=intval(count($all_total));


   		$total_info = $GLOBALS['db']->getRow("select sum(sale_money) as sale_money , sum(unconfirm_money) as unconfirm_money 
   				 , sum(confirm_money) as confirm_money  , sum(refund_money) as refund_money
   				 , sum(admin_charges) as admin_charges , sum(balance_money) as balance_money
   				 , sum(unbalance_money) as unbalance_money from ".DB_PREFIX."dc_supplier_statements where supplier_id=".$supplier_id);

   		$page = new Page($count, $page_size); // 初始化分页对象
   		$p = $page->show();
   	
   		
   		$GLOBALS['tmpl']->assign('pages', $p);
   		$GLOBALS['tmpl']->assign('type', $type);
   		$GLOBALS['tmpl']->assign('p_totol', $p_totol);
   		$GLOBALS['tmpl']->assign('p_totol_price', $p_totol_price);
   		$GLOBALS['tmpl']->assign("list", $list);
   		$GLOBALS['tmpl']->assign("supplier_id", $supplier_id);
   		$GLOBALS['tmpl']->assign("total_info", $total_info);
   		$form_url=url('biz','dcborder');
   		$GLOBALS['tmpl']->assign("form_url", $form_url);
   		/* 系统默认 */
   		$GLOBALS['tmpl']->assign("page_title", "对帐单");
   		$GLOBALS['tmpl']->display("pages/dc/dcborder_index.html");
   }	
}
?>