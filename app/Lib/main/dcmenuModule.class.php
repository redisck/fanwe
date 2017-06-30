<?php

class dcmenuModule  extends MainBaseModule 
{

    function index() {
 
    	global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("wrap_type","0"); //宽屏展示
		require_once APP_ROOT_PATH."system/model/dc.php";
		
		$s_info=get_lastest_search_name();
		$GLOBALS['tmpl']->assign("s_info",$s_info);
		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);
		if(!isset($GLOBALS['geo']['address']) || strim($GLOBALS['city']['name'])!=strim($s_info['city_name'])){
			app_redirect(url('index','dc'));
		
		}
		$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		if($user_id>0){
		$collect_location=get_user_location_collect();
		}
		//开始身边团购的地理定位
		$ypoint =  $GLOBALS['geo']['ypoint'];  //ypoint
		$xpoint =  $GLOBALS['geo']['xpoint'];  //xpoint
		$address = $GLOBALS['geo']['address'];
		
		$tname='sl';
		
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r)/1000 as distance ";
		
			$sort_field = " distance asc ";
		}
		
		$dc_location_id = get_dc_location_id($type='is_dc',$sort_field,$field_append);
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		
		$menu_cate_id = intval($_REQUEST['cate_id']);
		if($menu_cate_id)$ur0l_param['cate_id'] = $menu_cate_id;
		
		$type=1;
		$dc_menu_result = get_dcmenu_list($limit,$menu_cate_id,$dc_location_id['list'],$type);
		$dc_menu_list = $dc_menu_result['list'];
		
		$cate_list = load_auto_cache("cache_dc_menu_cate"); //分类缓存
		$id_arr=array();
		foreach($dc_menu_list as $kk=>$vv){
			if(!in_array($vv['location_id'], $id_arr)){
			$id_arr[]=$vv['location_id'];
			}
		}
		
		$location = $GLOBALS['db']->getAll("select id , name , is_close from ".DB_PREFIX."supplier_location where id in (".implode(',',$id_arr).")");	//营业时间 和现在时间 分割对比
		$location=data_format_idkey($location,$key='id');
	
		foreach($dc_menu_list as $k=>$v){
			
			$dc_menu_list[$k]['location_name']=$location[$v['location_id']]['name'];
			$dc_menu_list[$k]['is_close']=$location[$v['location_id']]['is_close'];
			$time_list=explode(",",$v[open_time_cfg_str]);
			$is_in_open_time=is_in_open_time($v['location_id']);
			$dc_menu_list[$k]['ol']=$is_in_open_time;	

		}
			
			foreach ($dc_menu_list as $key => $row) {
					$edition[$key] = $row['ol'];
   					 $volume[$key]  = $row['buy_count'];
   					 $cl[$key]=$row['is_close'];
					}

			array_multisort($edition, SORT_DESC,$cl,SORT_ASC,$volume, SORT_DESC, $dc_menu_list);//按销量和是否在营业时间段内排序
				 
	
	//print_r($dc_menu_list);die;
			
			
		$total = $dc_menu_result['count'];
		$page = new Page($total,app_conf("DEAL_PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);

		$GLOBALS['tmpl']->assign('menu_cate_id',$menu_cate_id);
		$GLOBALS['tmpl']->assign('dc_menu_list',$dc_menu_list);
		$GLOBALS['tmpl']->assign('cate_list',$cate_list);
		$GLOBALS['tmpl']->display("dc/dc_menu.html");
    	
    }
    
    
    
    
    public function add_menu_cart(){
    	
    	
    	global_run();
		require_once APP_ROOT_PATH.'system/model/dc.php';
		$location_id=intval($_REQUEST['location_id']);
		$supplier_id=intval($_REQUEST['supplier_id']);
		$number= 1;
		$menu_id=intval($_REQUEST['menu_id']);
		$session_id=es_session::id();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		
		/*
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where session_id = '".$session_id."'");
		*/
		$dc_cart_result = load_dc_cart_list(true,$location_id);
		
		
		foreach($dc_cart_result['cart_list'] as $k=>$v){
			if($v['session_id']==$session_id && $v['menu_id']==$menu_id && $v['user_id']==$user_id){	
				$cart_item=$v;	
			}	
		}
		
		
		if(!$cart_item)
		{			
			$cart_item['session_id'] = $session_id;
			$cart_item['menu_id'] = $menu_id;
			$menu_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$menu_id);
			$cart_item['name'] = strim($menu_info['name']);
			$cart_item['num'] = $number;
			$cart_item['unit_price']=$menu_info['price'];
			$cart_item['total_price'] = $cart_item['unit_price'] * $cart_item['num'];
			$cart_item['supplier_id'] = $supplier_id;
			$cart_item['location_id'] = $location_id;
			$cart_item['icon'] = $menu_info['image'];
			$cart_item['user_id'] = $user_id;
			$cart_item['add_time'] = NOW_TIME;
			$cart_item['cart_type'] = 1;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cart",$cart_item);
		
		}
		else
		{

			$cart_item['num'] += $number;
			$cart_item['total_price'] = $cart_item['unit_price'] * $cart_item['num'];
			if($cart_item['num']<=0){	
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and user_id=".$user_id." and menu_id=".$menu_id);
			}else{
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cart",$cart_item,"UPDATE","id=".$cart_item['id']);
			}
		}
		
		$param['lid'] = $location_id;
		
		app_redirect(url("index","dcbuy",$param));
		
		
    }
}
?>