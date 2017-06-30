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
class dc_bizModule extends MainBaseModule
{
	
	/**
	 * 	商家管理首页
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz&r_type=2
	 * 	输入: 
	 *
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *  
	 *  order_info:array:array 订单信息，可能存在多家
	 *  其中：id:商家ID ，location_name：商家名称，dc_order_count：外卖订单个数 ，rs_order_count：预订订单个数 
	 *  unbalance_money:待结算金额
	 */  
	public function index()
	{		
	    /* 基本参数初始化 */
        //init_app_page();
        
		/*初始化*/
		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$root['page_title'] = "订单详情";
		 
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
        
        //返回商户权限
        if(!check_module_auth("dc")){
        	$root['is_auth'] = 0;
        	output($root,0,"没有操作验证权限");
        }else{
        	$root['is_auth'] = 1;
        }
        
        $order_info=array();

        foreach($account_info['location_ids'] as $lid){
        
        	$location_info=$GLOBALS['db']->getRow("select id,is_dc,is_reserve from ".DB_PREFIX."supplier_location where id=".$lid);
        	if($location_info['is_dc']==1 || $location_info['is_reserve']==1){	
        	
        	$lid_info=array();
        	//外卖新订单
        	$dc_order_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where confirm_status=0 and is_cancel=0 and refund_status=0 and order_status=0 and type_del=0 and is_rs=0 and ((payment_id=0 and pay_status=1) or payment_id=1) and location_id=".$lid);
        	//外卖新订单
        	$rs_order_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where confirm_status=0 and is_cancel=0 and refund_status=0 and order_status=0 and type_del=0 and is_rs=1 and pay_status=1 and location_id=".$lid);

        	$lid_info['location_info']=$location_info;
        	$location_name=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier_location where id=".$lid);
        	$lid_info['location_name']=$location_name;
        	$lid_info['dc_order_count']=$dc_order_count;
        	$lid_info['rc_order_count']=$rs_order_count;
        	$order_info[]=$lid_info;
        	}
        }
        $unbalance_money = $GLOBALS['db']->getOne("select sum(unbalance_money) as unbalance_money from ".DB_PREFIX."dc_supplier_statements where supplier_id=".$supplier_id);
        
       		$root['unbalance_money']=$unbalance_money;
	        $root['order_info']=$order_info;
	        output($root);
	}

	
	
	/**
	 * 	商家管理内页
	 *
	 *	测试链接：http://localhost/o2onew/mapi/index.php?ctl=dc_biz&r_type=2&lid=41&act=view
	 *
	 * 	 输入:
	 *  lid:int 门店ID
	 *  
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *  
	 *  location_info：门店信息
	 *   Array
        (
            [id] => 41
            [name] => 果果外卖
            [address] => 八一七中路群升国际E区田田田田田田田田田田田田田田田田
            [tel] => 0591-87555051
            [is_close] => 0
        )
        
        open_time_info:营业时间段，begin_time_h：开始时间的小时，begin_time_m：开始时间的分钟，end_time_h：结束时间的小时，end_time_m：结束时间的分钟
		 Array
        (
            [0] => Array
                (
                    [location_id] => 41
                    [begin_time_h] => 7
                    [begin_time_m] => 0
                    [end_time_h] => 14
                    [end_time_m] => 0
                )

            [1] => Array
                (
                    [location_id] => 41
                    [begin_time_h] => 14
                    [begin_time_m] => 0
                    [end_time_h] => 22
                    [end_time_m] => 0
                )

        )
        
	 *
	 */
	public function view()
	{
		/* 基本参数初始化 */
		//init_app_page();
	
		/*初始化*/
		$root = array();
		$account_info = $GLOBALS['account_info'];

		$root['page_title'] = "门店管理";
			
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
		
		$lid = intval($GLOBALS['request']['lid']);
	
		if(!in_array($lid, $account_info['location_ids']))
		{
			output($root,0,"没有管理权限");
		}
	
		//返回商户权限
		if(!check_module_auth("dc")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
	
		$location_info=$GLOBALS['db']->getRow("select id,name,address,tel,is_close from ".DB_PREFIX."supplier_location where id=".$lid);
		$root['location_info']=$location_info;
		$open_time_info=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$lid);
		
		$open_time_info=array_sort($open_time_info,$keys='begin_time_h',$type='asc');
		
		foreach($open_time_info as $k=>$v){
			$open_time_info[$k]['begin_time_h']=str_pad($v['begin_time_h'],2,0,STR_PAD_LEFT);
			$open_time_info[$k]['begin_time_m']=str_pad($v['begin_time_m'],2,0,STR_PAD_LEFT);
			$open_time_info[$k]['end_time_h']=str_pad($v['end_time_h'],2,0,STR_PAD_LEFT);
			$open_time_info[$k]['end_time_m']=str_pad($v['end_time_m'],2,0,STR_PAD_LEFT);
		}
		
		$root['open_time_info']=$open_time_info;
		output($root);
	}
	
	

	
	/**
	 * 	商家结算接口，把未结算金额变为可提现金额
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz&r_type=2&act=dc_supplier_balance
	 * 	输入:
	 *
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *
	 * status：为结算操作的状态，status=0,结算失败;status=1,结算成功
	 * info:返回的提示信息
	 *  
	 */
	public function dc_supplier_balance(){
		
		/*初始化*/
		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
		
		//返回商户权限
		if(!check_module_auth("dcborder")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		$rs=dc_supplier_balance($supplier_id);
		if($rs > 0){
			output($root,1,'结算成功');
		}else{
			output($root,0,'结算失败');
		}
	
	}
	

	/**
	 * 	商家内页保存按钮的提交地址
	 *  测试链接： http://localhost/o2onew/mapi/index.php?ctl=dc_biz&r_type=2&act=save
	 * 	输入:
	 *  lid：int 门店ID
	 *  is_close:int,是否营业，is_close=1:暂停营业，is_close=0：营业中
	 *  tel：业务电话
     *  begin_time_h：array,开始时间的小时，结构如下：array(9,18)，array('第一个开始时间的小时','第二个开始时间的小时','第三个开始时间的小时');
     *  begin_time_m,开始时间的分钟，结构如下：array(30,28)，array('第一个开始时间的分钟','第二个开始时间的分钟','第三个开始时间的分钟');
     *  end_time_h：array,结束时间的小时，结构如下：array(12,22)，array('第一个结束时间的小时','第二个结束时间的小时','第三个结束时间的小时');
     *  end_time_m：array,结束时间的分钟，结构如下：array(15,45)，array('第一个结束时间的分钟','第二个结束时间的分钟','第三个结束时间的分钟');
     *  
	 *  输出:
	 *  status:int 结果状态 0失败 1成功
	 *  info:信息返回
	 *  biz_user_status：int 商户登录状态 0未登录/1已登录
	 *
	 *  以下仅在biz_user_status为1时会返回
	 *  is_auth：int 模块操作权限 0没有权限 / 1有权限
	 *
	 */
	public function save(){
	
		/*初始化*/
		$root = array();
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		/*业务逻辑*/
		$root['biz_user_status'] = $account_info?1:0;
		if (empty($account_info)){
			output($root,0,"商户未登录");
		}
	
		$lid = intval($GLOBALS['request']['lid']);
		
		if(!in_array($lid, $account_info['location_ids']))
		{
			output($root,0,"没有管理权限");
		}
		
		//返回商户权限
		if(!check_module_auth("dc")){
			$root['is_auth'] = 0;
			output($root,0,"没有操作验证权限");
		}else{
			$root['is_auth'] = 1;
		}
	
		$location_data=array();

		$location_data['is_close']=intval($GLOBALS['request']['is_close']);
		$location_data['tel']=strim($GLOBALS['request']['tel']);
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		
		$begin_time_h=$GLOBALS['request']['begin_time_h'];
		$begin_time_m=$GLOBALS['request']['begin_time_m'];
		$end_time_h=$GLOBALS['request']['end_time_h'];
		$end_time_m=$GLOBALS['request']['end_time_m'];
		$open_time_arr=array();
		
		$open_time_arr['begin_time_h']=$begin_time_h;
		$open_time_arr['begin_time_m']=$begin_time_m;
		$open_time_arr['end_time_h']=$end_time_h;
		$open_time_arr['end_time_m']=$end_time_m;		
		
		foreach ($open_time_arr as $k=>$v)
		{
			foreach($open_time_arr[$k] as $kk=>$vv){
				$sv = intval($vv);
				$open_time_arr[$k][$kk] = $sv;
			}
			
		}
		syn_supplier_location_open_time_match($open_time_arr,$lid);
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$location_data,$mode='UPDATE','id='.$lid,$querymode = 'SILENT');
		output($root,1,'保存成功');

	}
	
}
?>