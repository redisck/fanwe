<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dcbuyModule extends MainBaseModule
{
	public function index()
	{	
		global_run();
		init_app_page();
		//$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示
		require_once APP_ROOT_PATH."system/model/dc.php";
		$s_info=get_lastest_search_name();
		if(!isset($GLOBALS['geo']['address']) || strim($GLOBALS['city']['name'])!=strim($s_info['city_name'])){

		/*	app_redirect(url('index','dc')); */

		
		}
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		//开始身边团购的地理定位
		$ypoint =  $GLOBALS['geo']['ypoint'];  //ypoint
		$xpoint =  $GLOBALS['geo']['xpoint'];  //xpoint
		$address = $GLOBALS['geo']['address'];
		$tname='l';

		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r)/1000 as distance ";

		}
		
		$location_id = strim($_REQUEST['lid']);
		

		$dclocation=get_location_info($tname,$location_id,$field_append);


		if($dclocation)
		{	
			/*
			if($dclocation['is_dc']==0){
				app_redirect(url('index','dc'));
				
			}
			*/
			$GLOBALS['tmpl']->assign("s_info",$s_info);
			$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
			$site_nav[] = array('name'=>$GLOBALS['lang']['DC_LOCATION_TITLE'],'url'=>url("index",'dc'));
			$site_nav[] = array('name'=>$dclocation['name'],'url'=>url("index",'dcbuy',array('lid'=>$dclocation['id'])));
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//开始输出商户图库数据json
			$store_images = $GLOBALS['db']->getAll("select image from ".DB_PREFIX."supplier_location_images where supplier_location_id = ".$dclocation['id']." and status = 1 order by sort limit ".MAX_SP_IMAGE);
			$location_main_image=array();
			$location_main_image['image']=$dclocation['preview'];
			array_unshift($store_images,$location_main_image);
			foreach($store_images as $k=>$v)
			{
				$store_images[$k]['image'] = format_image_path(get_spec_image($v['image'],600,450,1));
			}
			$GLOBALS['tmpl']->assign("store_images_json",json_encode($store_images));
			$GLOBALS['tmpl']->assign("store_images_count",count($store_images));
				


			//关于分类信息与seo
			$page_title = $dclocation['name'];
			$page_keyword = $dclocation['name'];
			$page_description = $dclocation['name'];

			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);
			$location_package_conf=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_package_conf where location_id=".$dclocation['id']);
			$location_collect_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where location_id=".$dclocation['id']);
			$location_review_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status=1 and supplier_location_id=".$dclocation['id'] );
			$is_colloect=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where location_id=".$dclocation['id']." and user_id=".$user_id);
			if($is_colloect>0){
				$dclocation['is_collected']=1;
			}else{
				$dclocation['is_collected']=0;
			}
			
			require_once APP_ROOT_PATH."app/Lib/main/dcajaxModule.class.php";
			$lid_info=array('location_id'=>$dclocation['id'],'menu_status'=>1);
			dcajaxModule::set_dc_cart_menu_status($is_ajax_return=0,$lid_info);
			
			$location_dc_cart=load_dc_cart_list(true,$dclocation['id'],$type=1);			
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			$id_arr=array($dclocation['id']=>array('id'=>$dclocation['id'],'distance'=>$dclocation['distance']));
			$location_delivery_info=get_location_delivery_info($id_arr);
			$location_delivery=array();
			foreach($location_delivery_info as $kk=>$vv){
				$location_delivery=$vv;	
			}
			
			$location_menu_cate=$this->get_location_menu_cate($dclocation['id']);
			$dclocation['location_delivery_info']=$location_delivery;
			if(!isset($dclocation['distance'])){
				$dclocation['distance']=0;
			}
			$location_dc_table_cart=load_dc_cart_list(true,$dclocation['id'],$type=0);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			
			$is_in_open_time=is_in_open_time($dclocation['id']);
			if($location_dc_table_cart['total_data']['total_count'] == 0 ){	
				if($dclocation['is_dc']==1){
					if ($is_in_open_time==1){
							if($location_delivery['is_free_delivery']==2){
								$is_allow_add_cart=0;
							}else{
								$is_allow_add_cart=1;
							}
						
					}else{
						$is_allow_add_cart=0;
					}
				}else{
					$is_allow_add_cart=0;
				}
			}else{
					if ($dclocation['is_close']==0){
						$is_allow_add_cart=1;
					}else{
						$is_allow_add_cart=0;
					}
			}
	
			$package_fee=get_location_package_fee($dclocation['id'],$location_dc_cart);
			if(!isset($dclocation['distance'])){
				$dclocation['distance']=0;
			}
			$promote_info=get_dc_promote_info();
			$GLOBALS['tmpl']->assign("promote_info",$promote_info);
			$GLOBALS['tmpl']->assign("is_allow_add_cart",$is_allow_add_cart);
			$dclocation['location_collect_count']=$location_collect_count;
			$dclocation['review_count']=$location_review_count;
			$dclocation['location_package_conf']=$location_package_conf;
			$dclocation['location_collect_count']=$this->location_collect_count($dclocation['id']);
			$dclocation['location_menu_cate']=$location_menu_cate;
			$less_money=$dclocation['location_delivery_info']['start_price']-$location_dc_cart['total_data']['total_price'];
			$less_table_money=$location_dc_table_cart['total_data']['total_price']-$location_dc_cart['total_data']['total_price'];
			$GLOBALS['tmpl']->assign("less_money",$less_money);
			$GLOBALS['tmpl']->assign("less_table_money",$less_table_money);
			$GLOBALS['tmpl']->assign("user_id",$user_id);
					
			if($location_dc_table_cart['total_data']['total_count']!=0)//是否为预定 ，如果预定就不用打包费
			{
				$total_price=$location_dc_cart['total_data']['total_price'];
				
			}
			else
			{
				$total_price=$package_fee+$location_dc_cart['total_data']['total_price'];	
			}
			$GLOBALS['tmpl']->assign("package_fee",$package_fee);
			$GLOBALS['tmpl']->assign("total_price",$total_price);
			$GLOBALS['tmpl']->assign("is_login_reload","1");
			$GLOBALS['tmpl']->assign("dclocation",$dclocation);
			$GLOBALS['tmpl']->display("dc/dcbuy.html");

		}
		else
		{
			app_redirect(url('index','dc'));
		}
		
		
	}
	
	
/**
 * 获取餐厅的菜单分类与菜单
 * @param unknown_type $location_id  门店ID
 * @return $location_menu_arr 返回菜单的数据集
 */
public function get_location_menu_cate($location_id){
	require_once APP_ROOT_PATH."system/model/dc.php";
	global_run();
	
	$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
	$location_menu_cate=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_supplier_menu_cate where location_id=".$location_id." order by sort");
	$location_menu=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_menu where is_effect=1 and location_id=".$location_id." order by image desc");
	$location_menu_arr=array();
	$location_menu_info=array();	
	
	if($location_menu){	
			$id_str=get_id_str($location_menu);
			$cart_info=$GLOBALS['db']->getAll("select num, menu_id from ".DB_PREFIX."dc_cart where user_id=".$user_id." and session_id='".es_session::id()."' and cart_type=1 and menu_id in (".$id_str.")");
			$cart_info_new=data_format_idkey($cart_info,$key='menu_id');
	
			$location_menu_new=data_format_idkey($location_menu,$key='id');
		foreach($location_menu_new as $m=>$n){			
			$n['cart_count']=isset($cart_info_new[$m]['num'])?$cart_info_new[$m]['num']:0;
			$location_menu_info[$n['cate_id']][]=$n;		
		}
		
		$id_cat_str=get_id_str($location_menu_cate);
		$has_p_menu_count=$GLOBALS['db']->getAll("select count(*) as count,cate_id from ".DB_PREFIX."dc_menu where is_effect=1 and location_id=".$location_id." and cate_id in (".$id_cat_str.") and image!='' group by cate_id");
		$no_p_menu_count=$GLOBALS['db']->getAll("select count(*) as count,cate_id from ".DB_PREFIX."dc_menu where is_effect=1 and location_id=".$location_id." and cate_id in (".$id_cat_str.") and image='' group by cate_id");
		$has_p_menu_count_new=data_format_idkey($has_p_menu_count,$key='cate_id');
		$no_p_menu_count_new=data_format_idkey($no_p_menu_count,$key='cate_id');

	foreach($location_menu_cate as $k=>$v){
		if(count($location_menu_info[$v['id']])>0){
			$location_menu_arr[$v['id']]['main_cate']=$v;
			$location_menu_arr[$v['id']]['has_image_count']=isset($has_p_menu_count_new[$v['id']]['count'])?$has_p_menu_count_new[$v['id']]['count']:0;
			$location_menu_arr[$v['id']]['no_image_count']=isset($no_p_menu_count_new[$v['id']]['count'])?$no_p_menu_count_new[$v['id']]['count']:0;
			$location_menu_arr[$v['id']]['sub_menu']=$location_menu_info[$v['id']];
		}
	}
	
	}
	
	$row_num=4;
	foreach($location_menu_arr as $k=>$v){
		if($v['has_image_count'] > 0){
			$last_row=ceil($v['has_image_count']/$row_num);
			$min=$row_num*($last_row-1);
			foreach($v['sub_menu'] as $m=>$n){
				/* 有图片菜单的最后一行 */
				if($m>=$min && $m<$v['has_image_count']){
					$location_menu_arr[$k]['sub_menu'][$m]['is_last_row']=1;		
				}
				if(($m+1)%$row_num==0){
					$location_menu_arr[$k]['sub_menu'][$m]['is_last_col']=1;
				}
			}
		}
		if($v['no_image_count'] > 0){
			/* 没有图片菜单的第一行 */
			$location_menu_arr[$k]['sub_menu'][$v['has_image_count']]['is_first_row']=1;
		}
		
		
	}
	

	return $location_menu_arr;
}
	

	public function location_collect_count($location_id){
	
		$location_collect_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where location_id=".$location_id);
		return $location_collect_count;
	}
	
	
}
?>