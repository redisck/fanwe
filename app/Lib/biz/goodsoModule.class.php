<?php 
/**
 * 订单记录
 */
require APP_ROOT_PATH.'app/Lib/page.php';
require_once APP_ROOT_PATH."system/model/user.php";
class goodsoModule extends BizBaseModule
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
		
		//退款允许
		$allow_refund = $GLOBALS['db']->getOne("select allow_refund from ".DB_PREFIX."supplier where id = ".$supplier_id);
		$GLOBALS['tmpl']->assign("allow_refund",$allow_refund);
		
		$name = strim($_REQUEST['name']);
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);
		
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i");
		$end_time_s = to_timespan($end_time,"Y-m-d H:i");
		
		$condition = "";
		if($name!="")
			$condition .=" and (doi.name like '%".$name."%' or doi.sub_name like '%".$name."%') ";
		if($begin_time_s)
			$condition .=" and do.create_time > ".$begin_time_s." ";
		if($end_time_s)
			$condition .=" and do.create_time < ".$end_time_s." ";
		
		$GLOBALS['tmpl']->assign("name",$name);
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
	    //分页
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
	   
	    require_once APP_ROOT_PATH."system/model/deal_order.php";
	    $order_item_table_name = get_supplier_order_item_table_name($supplier_id);
	    $order_table_name = get_supplier_order_table_name($supplier_id);
	    
	    $sql = "select distinct(doi.id),doi.*,do.order_status,do.delivery_id,do.memo,do.create_time,do.order_sn,do.total_price,do.pay_amount,doi.refund_status,do.region_lv1,do.region_lv2,do.region_lv3,do.region_lv4,do.consignee,do.address,do.zip,do.mobile from ".$order_item_table_name." as doi left join ".
	 	    	$order_table_name." as do on doi.order_id = do.id left join ".
	    		DB_PREFIX."deal_location_link as l on doi.deal_id = l.deal_id ".	    		
	    		" where l.location_id in (".implode(",",$s_account_info['location_ids']).") and do.is_delete = 0 and do.type = 0 and doi.is_shop = 1 and do.pay_status = 2 $condition order by doi.id desc limit ".$limit;
	    
	    $sql_count = "select count(distinct(doi.id)) from ".$order_item_table_name." as doi left join ".
	    		$order_table_name." as do on doi.order_id = do.id left join ".
	    		DB_PREFIX."deal_location_link as l on doi.deal_id = l.deal_id ".
	    		" where l.location_id in (".implode(",",$s_account_info['location_ids']).") and do.is_delete = 0 and do.type = 0 and doi.is_shop = 1  and do.pay_status = 2 $condition ";
	
	    $list = $GLOBALS['db']->getAll($sql);
	    $region_conf = load_auto_cache("cache_delivery_region_conf");
	    $delivery_conf = load_auto_cache("cache_delivery");
	    foreach($list as $k=>$v){
	    	$uinfo = load_user($v['user_id']);
	    	$list[$k]['user_name']= $uinfo['user_name'];
	    	
	    	$list[$k]['create_time'] = to_date($v['create_time']);
	    	$deal_info = load_auto_cache("deal",array("id"=>$v['deal_id']));
	    	$list[$k]['url'] = $deal_info['url'];
	    	$list[$k]['s_total_price'] = $v['balance_total_price'] + $v['add_balance_price_total'];
	    	$list[$k]['region_lv1'] = $region_conf[$v['region_lv1']]['name'];
	    	$list[$k]['region_lv2'] = $region_conf[$v['region_lv2']]['name'];
	    	$list[$k]['region_lv3'] = $region_conf[$v['region_lv3']]['name'];
	    	$list[$k]['region_lv4'] = $region_conf[$v['region_lv4']]['name'];
	    	
	    	$list[$k]['delivery'] = $delivery_conf[$v['delivery_id']]['name'];
	    	
	    	$list[$k]['delivery_notice'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where order_item_id = ".$v['id']." order by delivery_time desc limit 1");
	    }
	    $total = $GLOBALS['db']->getOne($sql_count);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
	    $GLOBALS['tmpl']->assign('NOW_TIME',NOW_TIME);


	    $GLOBALS['tmpl']->assign("list",$list);
	    		
		$GLOBALS['tmpl']->assign("ORDER_DELIVERY_EXPIRE",ORDER_DELIVERY_EXPIRE);
		$GLOBALS['tmpl']->assign("head_title","商品订单记录");
		$GLOBALS['tmpl']->display("pages/goodso/index.html");	
	}
	
	
	
	/**
	 * 快递查询
	 */
	public function check_delivery()
	{
		$id = intval($_REQUEST['id']);
		
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		
		$delivery_notice = $GLOBALS['db']->getRow("select n.* from ".DB_PREFIX."delivery_notice as n left join ".DB_PREFIX."deal_location_link as l on l.deal_id = n.deal_id where n.order_item_id = ".$id." and  l.location_id in (".implode(",",$s_account_info['location_ids']).")  order by n.delivery_time desc");
		if($delivery_notice)
		{
			$data['status'] = true;
				
			$express_id = intval($delivery_notice['express_id']);
			$typeNu = strim($delivery_notice["notice_sn"]);
			$express_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."express where is_effect = 1 and id = ".$express_id);
			$express_info['config'] = unserialize($express_info['config']);
			$typeCom = strim($express_info['config']["app_code"]);
				
			if(isset($typeCom)&&isset($typeNu)){
					
				$AppKey = app_conf("KUAIDI_APP_KEY");//请将XXXXXX替换成您在http://kuaidi100.com/app/reg.html申请到的KEY
				$url ='http://api.kuaidi100.com/api?id='.$AppKey.'&com='.$typeCom.'&nu='.$typeNu.'&show=0&muti=1&order=asc';
					
					
				//优先使用curl模式发送数据
				//KUAIDI_TYPE : 1. API查询 2.页面查询
				if (app_conf("KUAIDI_TYPE")==1){
					$data = es_session::get(md5($url));
					if(empty($data)||(NOW_TIME - $data['time'])>600)
					{
						$api_result = get_delivery_api_content($url);
						$api_result_status = $api_result['status'];
						$get_content = $api_result['html'];
							
						//请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
						$powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';
							
						$data['html'] = $get_content . '<br/>' . $powered;
						$data['status'] = true;   //API查询
						$data['time'] = NOW_TIME;
						if($api_result_status)
						es_session::set(md5($url),$data);
					}
						
					ajax_return($data);
				}else{
					$url = "http://www.kuaidi100.com/chaxun?com=".$typeCom."&nu=".$typeNu;
					app_redirect($url);
				}
					
			}else{
				if(app_conf("KUAIDI_TYPE")==1)
				{
					$data['status'] = false;
					$data['status'] = "非法的快递查询";
					ajax_return($data);
				}
				else
				{
					init_app_page();
					showErr("非法的快递查询");
				}
			}
				
		}
		else
		{
			if(app_conf("KUAIDI_TYPE")==1)
			{
				$data['status'] = false;
				ajax_return($data);
			}
			else
			{
				init_app_page();
				showErr("非法的快递查询");
			}
				
		}
	
	
	}
	
	
public function do_delivery()
	{		
		
		$s_account_info = $GLOBALS['account_info'];
		$supplier_id = intval($s_account_info['supplier_id']);
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		$order_item_table_name = get_supplier_order_item_table_name($supplier_id);
		$order_table_name = get_supplier_order_table_name($supplier_id);
		 
		$id = intval($_REQUEST['id']); //发货商品的ID
		$delivery_sn = strim($_REQUEST['delivery_sn']);
		$memo = strim($_REQUEST['memo']);
		$express_id = intval($_REQUEST['express_id']);
		$location_id = intval($_REQUEST['location_id']);
		$order_id = $GLOBALS['db']->getOne("select order_id from ".$order_item_table_name." where id = ".$id);
		$order_info = $GLOBALS['db']->getRow("select * from ".$order_table_name." where id = '".$order_id."'");
		
		if(empty($delivery_sn))
		{
			$data['status'] = 0;
			$data['info'] = "请输入快递单号";
			ajax_return($data);
		}
		
		$item = $GLOBALS['db']->getRow("select doi.* from ".$order_item_table_name." as doi left join ".DB_PREFIX."deal_location_link as l on doi.deal_id = l.deal_id where doi.id = ".$id." and l.location_id in (".implode(",",$s_account_info['location_ids']).")");
		if($item)
		{
			$rs = make_delivery_notice($order_id,$id,$delivery_sn,$memo,$express_id,$location_id);
			if($rs)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set delivery_status = 1 where id = ".$id);				
			}
			send_delivery_mail($delivery_sn,$item['name'],$order_id);
			send_delivery_sms($delivery_sn,$item['name'],$order_id);
			
			//开始同步订单的发货状态
			$order_deal_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
			foreach($order_deal_items as $k=>$v)
			{
				if($v['delivery_status']==5) //无需发货的商品
				{
					unset($order_deal_items[$k]);
				}
			}
			$delivery_deal_items = $order_deal_items;
			foreach($delivery_deal_items as $k=>$v)
			{
				if($v['delivery_status']==0) //未发货去除
				{
					unset($delivery_deal_items[$k]);
				}
			}
				
			
			if(count($delivery_deal_items)==0&&count($order_deal_items)!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 0,update_time = '".NOW_TIME."' where id = ".$order_id); //未发货
			}
			elseif(count($delivery_deal_items)>0&&count($order_deal_items)!=0&&count($delivery_deal_items)<count($order_deal_items))
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 1,update_time = '".NOW_TIME."' where id = ".$order_id); //部分发
			}
			else
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 2,update_time = '".NOW_TIME."' where id = ".$order_id); //全部发
			}

			order_log($item['name']."发货了，发货单号：".$delivery_sn, $order_id);
			update_order_cache($order_id);
			distribute_order($order_id);
			
			send_msg($order_info['user_id'], $item['name']."发货了，发货单号：".$delivery_sn, "orderitem", $item['id']);


			//发微信通知			
			$weixin_conf = load_auto_cache("weixin_conf");
			if($weixin_conf['platform_status']==1)
			{
				$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$supplier_id);
				$express_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."express where id = ".$express_id);				
				send_wx_msg("OPENTM200565259", $order_info['user_id'], $wx_account,array("order_id"=>$order_id,"order_sn"=>$order_info['order_sn'],"company_name"=>$express_name,"delivery_sn"=>$delivery_sn,"order_item_id"=>$item['id']));
			}
			
			
			$data['status'] = 1;
			$data['info'] = "发货成功";

			ajax_return($data);
			
		}
		else
		{
			$data['status'] = 0;
			$data['info'] = "非法的数据";
			ajax_return($data);
		}
	}
	
	

}
?>