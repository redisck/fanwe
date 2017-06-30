<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dctableModule extends MainBaseModule
{
	
	/**
	 * 商家详细页中的订座页面      
	 **/
	public function index()
	{	
		global_run();
		
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		require_once APP_ROOT_PATH."system/model/dc.php";
		$location_id=$param['lid'] = intval($_REQUEST['lid']);
		//$book_way=0时，只订座，不提前点菜，$book_way=1时，提前点菜，去了等着吃
		$has_menu=0;
		$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and cart_type=1 and user_id=".$user_id);
		
		if($total_data['total_count'] >0){
			$has_menu=1;
		}
		$book_way= intval($_REQUEST['book_way']);
		require_once APP_ROOT_PATH."wap/Lib/main/dcajaxModule.class.php";
		$lid_info=array('location_id'=>$location_id,'menu_status'=>$book_way);
		dcajaxModule::set_dc_cart_menu_status(0,$lid_info);
		
		
		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);
		
		$cart_time_id=0;  //有预订座位的时间ID
		$cart_info=array('cart_time_id'=>0,'cart_date'=>0);
		if($location_dc_table_cart){
			$location_dc_table_cart['cart_list']=array_values($location_dc_table_cart['cart_list']);
			//座位ID
			$tid=$location_dc_table_cart['cart_list'][0]['menu_id'];
			$cart_info['cart_time_id']=intval($location_dc_table_cart['cart_list'][0]['table_time_id']);
			$cart_info['cart_date']=to_date($location_dc_table_cart['cart_list'][0]['table_time'],"Y-m-d");
			$cart_info['cart_date_format']=to_date($location_dc_table_cart['cart_list'][0]['table_time'],"Y-m-d")." ". to_date($location_dc_table_cart['cart_list'][0]['table_time'],"H:i");
			$cart_info['tid']=$tid;
		}
		
		$ss_tid=$s_tid = intval($_REQUEST['tid']);  //前台传过来的座位ID
		
		$ttid=0;
		if($tid > 0 && $s_tid >0 ){  //已经有订座，前台同时搜索其他座位信息，返回前台搜索的座位ID
			$ttid=$s_tid;
		}elseif($tid > 0 && $s_tid==0){  //已经有订座，前台没有搜索其他座位信息，返回购物车中的座位ID
			$ttid=$tid;
		}else{
			$ttid=$s_tid;
		}
		
	
		$param['tid'] = $ttid;

		$s_info=get_lastest_search_name();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		

	
		$data = request_api("dctable","index",$param);
		
		if($data['is_has_location']==1)
		{	

			$GLOBALS['tmpl']->assign('page_title',$data['page_title']);
			$GLOBALS['tmpl']->assign('page_keyword',$data['page_title']);
			$GLOBALS['tmpl']->assign('page_description',$data['page_description']);
			$GLOBALS['tmpl']->assign('s_info',$s_info);
			

			foreach($data['table_info']  as $k=>$v){	
				$data['table_info'][$k]['url']=wap_url('index','dctable',array("lid"=>$param['lid'],'tid'=>$v['id']));
			}
			$table_info=data_format_idkey($data['table_info'],$key='id');
			

			if($s_tid==0){  //前端没有选择座位

				//有预订又有点菜
				if($location_dc_table_cart && $location_dc_cart){
					$table_now=$table_info[$tid];

					if($location_dc_table_cart['total_data']['total_price'] <= $location_dc_cart['total_data']['total_price']){
						//点菜金达到座位预订的定金
						$pay_price=$location_dc_cart['total_data']['total_price'];
		
					}else{
						//点菜金未达到座位预订的定金
						showErr('点菜金不足',0,wap_url('index','dcbuy',array('lid'=>$data['dclocation']['id'])));
					}
				}elseif($location_dc_table_cart && !$location_dc_cart){
					//有预订无点菜
					$table_now=$table_info[$tid];
					$pay_price=$location_dc_table_cart['total_data']['total_price'];
				}elseif(!$location_dc_table_cart && $location_dc_cart){
					
					$table_now=$data['table_info'][0];

					if($location_dc_cart['total_data']['total_price'] >$table_now['price']){
					
						$pay_price=$location_dc_cart['total_data']['total_price'];
					}else{
						$pay_price=$table_now['price'];
						$less_table_money=$pay_price-floatval($location_dc_cart['total_data']['total_price']);
					}
					
					
					
				}else{
					//无预订无点菜
					$table_now=$data['table_info'][0];
					$pay_price=$table_now['price'];
				}
				
				if($location_dc_table_cart){
					$s_tid=$tid;
				}else{
					$s_tid=$data['table_info'][0]['id'];
				}
				
				
	   
			}else{   //前台有选择座位
			
					$table_now=$table_info[$param['tid']];

					if($location_dc_cart['total_data']['total_price'] >$table_now['price']){
						
						$pay_price=$location_dc_cart['total_data']['total_price'];
					}else{
						$pay_price=$table_now['price'];
						$less_table_money=$pay_price-floatval($location_dc_cart['total_data']['total_price']);
					}
				

					
			}
			
			//定金
			//print_r($data);
		/*
			print_r($table_now);
			print_r($location_dc_table_cart);
			print_r($data);
			
			print_r($table_info);
			*/
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			$GLOBALS['tmpl']->assign("table_now",$table_now);
			$GLOBALS['tmpl']->assign("pay_price",$pay_price);
			$GLOBALS['tmpl']->assign("cart_info",$cart_info);
			$GLOBALS['tmpl']->assign("book_way",$book_way);

			$GLOBALS['tmpl']->assign("data",$data);
			
			$GLOBALS['tmpl']->assign("s_tid",$s_tid);
			$GLOBALS['tmpl']->assign("ss_tid",$ss_tid);
			$GLOBALS['tmpl']->assign("has_menu",$has_menu);
			$GLOBALS['tmpl']->assign("less_table_money",$less_table_money);	
			$GLOBALS['tmpl']->assign("book_0_url",wap_url('index','dctable',array('lid'=>$location_id,'tid'=>$s_tid,'book_way'=>0)));
			$GLOBALS['tmpl']->assign("book_1_url",wap_url('index','dctable',array('lid'=>$location_id,'tid'=>$s_tid,'book_way'=>1)));
			$GLOBALS['tmpl']->display("dc/dc_table.html");	
		}
		else
		{	
			
			showErr('商家不存在',0,wap_url('index','dcres'));
		}
		
		
	}

	
	
	
	public function add_table_cart(){
	
		
		
		$location_id = intval($_REQUEST['lid']);
		$date=strim($_REQUEST['date']);
		$table_time_id=intval($_REQUEST['table_time_id']);
		
		global_run();
	
		$supplier_id=$GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."supplier_location where id=".$location_id);
	
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
	
			
			$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			
			$res['html'] = $GLOBALS['tmpl']->fetch("dc/inc/dc_table_cart.html");
			$res['status']=1;
			ajax_return($res);
		}
		else
		{
			$res['status']=0;
			$res['info']='预定额已满';
			ajax_return($res);

	
		}
	
	}
	
	
	public function del_table_cart(){
		$location_id = intval($_REQUEST['lid']);
		$id = intval($_REQUEST['id']);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where id=".$id);
		if($GLOBALS['db']->affected_rows()>0){
			require_once APP_ROOT_PATH."system/model/dc.php";
			$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
				
			$res['html'] = $GLOBALS['tmpl']->fetch("dc/inc/dc_table_cart.html");
			ajax_return($res);
		}
		
	}

	public function check_table_cart(){

		
		 $location_id = intval($_REQUEST['lid']);
		
		$tid = intval($_REQUEST['tid']);
		require_once APP_ROOT_PATH."system/model/dc.php";
		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
	
		$result=array();
		if($location_dc_table_cart['total_data']['total_count']>0){
			$result['status']=1;
			$result['info']='';
			$result['jump']=wap_url("index","dcbuy",array("lid"=>$location_id,"tid"=>$tid));
			
		}else{
			$result['status']=0;
			$result['info']='请先选择预订时间';
		}
		ajax_return($result);
	}
	
}
?>