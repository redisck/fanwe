<?php

class dctableModule  extends MainBaseModule 
{

    function index() {
    	
    	global_run();
		init_app_page();
		
		//$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		
		$date=strim($_REQUEST['date']);
		
		if(!$date)
		{
			
			
			$date = NOW_TIME;
			
			$date=date("Y-m-d", $date);
		}
		else
		{
			$date_num=strtotime($date);
			$now = NOW_TIME;
			
			$now=date("Y-m-d", $now);
			$now=strtotime($now);
			
			if($date_num<$now)
			{	
			showErr("日期已过");
				
			}
	
		}
		
		$location_id=intval($_REQUEST['lid']);
		
    	$rs_result=get_rs_item($location_id,$date);
    
    	$location_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id=".$location_id);
    	$name=$location_info['name'];
    	
    	//print_r($rs_result['list']);die;
    	$GLOBALS['tmpl']->assign('rs_result_list',$rs_result['list']);
    	$GLOBALS['tmpl']->assign('date',$date);
    	$GLOBALS['tmpl']->assign('name',$name);
    	$GLOBALS['tmpl']->assign('lid',$location_id);
    			
    	$GLOBALS['tmpl']->display("dc/dc_table.html");
    	
    }
    
    
    
    public function add_table_cart(){
    	
    	global_run();
    	
    	$location_id=intval($_REQUEST['location_id']);
		$supplier_id=intval($_REQUEST['supplier_id']);
		$date=strim($_REQUEST['date']);
    	$table_time_id=intval($_REQUEST['id']);
    	$session_id=es_session::id();
    	
    	require_once APP_ROOT_PATH."system/model/dc.php";
    	$table_time_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_time where id=".$table_time_id);
    	
    	$is_count=item_day($location_id,$date,$table_time_info['item_id'],$table_time_id,$table_time_info['total_count']);//判断给定的日期是否有库存
    	
    	if($is_count)
    	{
    	$session_list=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cart where session_id= '".$session_id."' ");
    	
    	foreach($session_list as $k=>$v){   //删去已订购的桌子
    		
    		if($v["table_time_id"]>0)
    		{
    			$sql = "DELETE FROM ".DB_PREFIX."dc_cart WHERE session_id='".$session_id."' and id=".$v['id'];
	
				$GLOBALS['db']->query($sql);
    			
    		}
    		
    		
    	}

    	
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		
		
  			$time=$date." ".$table_time_info['rs_time'];
    	
    		$time=to_timespan($time);

			$cart_item['session_id'] = $session_id;
			$cart_item['supplier_id'] = $supplier_id;
			$cart_item['location_id'] = $location_id;
			$cart_item['user_id'] = $user_id;
			$cart_item['add_time'] = NOW_TIME;
			
			$table_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item where id=".$table_time_info['item_id']);
			
			$cart_item['unit_price']=$table_info['price'];
			$cart_item['num'] = 1;
			$cart_item['total_price'] = $table_info['price']*$cart_item['num'];
			$cart_item['icon'] = $table_info['icon'];
			$cart_item['name'] = $table_info['name'];
			$cart_item['table_time'] = $time;
			$cart_item['table_time_id'] = $table_time_id;
			$cart_item['menu_id'] = $table_info['id'];
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cart",$cart_item);
		
		$param['lid'] = $location_id;
		
		app_redirect(url("index","dcorder",$param));
    	}
    	else
    	{
    		
    		showErr("预定额已满",$ajax,url("index","dctable",array('lid'=>$location_id)));
    		
    	}
	
	 }
	 

	 
}
?>