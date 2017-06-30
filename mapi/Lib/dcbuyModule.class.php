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
	
	/**
	 * 商家详细页中的外卖页面
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dcbuy&r_type=2&lid=41
	 * 
	 * 输入：
	 * lid:int 商家ID
	 *
	 * 输出：
	 * is_has_location:int是否存在些商家， 0为不存在，1为存在
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page_keyword:string 页面关键词
	 * page_description:string 页面描述
	 * is_allow_add_cart：int 是否允许下单，有可能是因为商家不支付外卖，或者暂停营业，或者不在营业时间范围内导致不能下单，
	 * less_money：float,当为大于0时，下单金额小于起送价，不能进入付款页面，如果小于等于0时，下单金额大于等于起送价，可以进入付款页面，进行付款
	 * less_table_money：float,这个是用来判断当客人有订座并且提前点菜时，是否菜金大于订座的定价，系统规定，只有菜金大于等于订座的定金才能点菜，避免消费不足还得退还客人余额，
	 * 当less_table_money为大于0时，菜金金额不足，不能进入付款页面，当less_table_money为小于或等于0时，点菜金额足够，可以进入下单页面
	 * location_dc_table_cart为订座购物车信息，$location_dc_cart购物车中菜品的信息，结构如下
	 * cart_list:购物车中订座或者菜品的购物信息，total_data为购物车的统计数据，total_price为购物总金额，total_count：为购物总数量
	 *  Array
        (
            [cart_list] => Array
                (
                    [185] => Array
                        (
                            [id] => 185
                            [session_id] => 509rbke31684ltek2jo04n70p4
                            [user_id] => 71
                            [location_id] => 41
                            [supplier_id] => 43
                            [name] => 饕餮鸡排饭
                            [icon] => http://localhost/o2onew/public/attachment/201504/17/10/55306e5b0f72a_400x300.jpg
                            [num] => 1
                            [unit_price] => 23.0000
                            [total_price] => 23.0000
                            [menu_id] => 48
                            [table_time_id] => 0
                            [table_time] => 0
                            [cart_type] => 1
                            [add_time] => 1438303097
                            [is_effect] => 1
                            [url] => /o2onew/index.php?ctl=dcbuy&lid=41
                        )
                 )
           [total_data] => Array
                (
                    [total_price] => 117.0000
                    [total_count] => 6
                )
          )          
	 * 
	 * promote_info:array:array 首单立减和在线支付的具体信息，结构如下
        * Array
        (
            [is_firstorderdiscount] => Array
                (
                    [id] => 3
                    [class_name] => FirstOrderDiscount
                    [sort] => 5
                    [config] => a:1:{s:15:"discount_amount";s:2:"10";}
                    [description] => 首单立减10元（在线支付专享）
                )

            [is_payonlinediscount] => Array
                (
                    [id] => 7
                    [class_name] => PayOnlineDiscount
                    [sort] => 6
                    [config] => a:3:{s:14:"discount_limit";a:2:{i:0;s:2:"20";i:1;s:2:"40";}s:15:"discount_amount";a:2:{i:0;s:1:"5";i:1;s:2:"12";}s:11:"daily_limit";s:1:"2";}
                    [description] => 在线支付下单满20减5元，满40减12元，活动期间每天2单
                )

        )
	 * $dclocation:array:array:array 商家信息
	 * 
	 * $dclocation下面的字段
	 * is_collected：是否已经收藏
	 * $dclocation['location_delivery_info']：array配送费信息，用于显示多少配送
	 *           Array
                (
                    [id] => 635
                    [location_id] => 41
                    [start_price] => 10.0000
                    [scale] => 100
                    [delivery_price] => 1.0000
                    [is_free_delivery] => 0
                )
	 * $dclocation['location_menu_cate']:array:array 菜单分类以及分类下的菜品信息,main_cate这菜单分类，sub_menu为该菜单分类下面的子菜单
	 * Array
                (
                    [0] => Array
                        (
                            [main_cate] => Array
                                (
                                    [id] => 14
                                    [name] => 热销菜品
                                    [sort] => 1
                                    [iconfont] => 
                                    [iconcolor] => 
                                    [icon_img] => http://localhost/o2onew/public/attachment/201505/15/14/5555927a2130a_400x300.jpg
                                    [is_effect] => 1
                                    [supplier_id] => 43
                                    [location_id] => 41
                                )

                            [has_image_count] => 3
                            [no_image_count] => 0
                            [sub_menu] => Array
                                (
                                    [0] => Array
                                        (
                                            [id] => 47
                                            [name] => 古早鸡腿饭
                                            [cate_id] => 14
                                            [price] => 18.0000
                                            [image] => http://localhost/o2onew/public/attachment/201504/17/10/55306e5b0f72a_400x300.jpg
                                            [tags] => 10
                                            [tags_match] => ux20013ux39184
                                            [tags_match_row] => 中餐
                                            [is_effect] => 1
                                            [location_id] => 41
                                            [supplier_id] => 43
                                            [buy_count] => 45
                                            [xpoint] => 119.314685
                                            [ypoint] => 26.092901
                                            [menu_cate_type] => 0
                                            [open_time_cfg_str] => 07:00-14:00,14:00-22:00
                                            [cart_count] => 0
                                            [is_last_row] => 1
                                        )
                                 )
                         )
                 )              
	 **/
	public function index()
	{	
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";

		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;

		$tname='l';
		
		//开始身边团购的地理定位
		

		if(	$GLOBALS['request']['from']=='wap'){
			$ypoint =  $GLOBALS['geo']['ypoint'];  //ypoint
			$xpoint =  $GLOBALS['geo']['xpoint'];  //xpoint
		
		}else{
			$ypoint = $GLOBALS['request']['ypoint'];  //ypoint
			$xpoint = $GLOBALS['request']['xpoint'];  //xpoint
		}
		
		if($xpoint>0)
		{
			$pi = PI;  
			$r = EARTH_R;  
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r)/1000 as distance ";

		}
		
		$location_id = intval($GLOBALS['request']['lid']);
		$dclocation=get_location_info($tname,$location_id,$field_append);

		$root=array();


		if($dclocation)
		{	
			$dclocation['preview']=get_abs_img_root(get_spec_image($dclocation['preview'],600,450,1));

			//关于分类信息与seo
			$page_title = $dclocation['name'];
			$page_keyword = $dclocation['name'];
			$page_description = $dclocation['name'];

			$is_colloect=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where location_id=".$dclocation['id']." and user_id=".$user_id);
			if($is_colloect>0){
				$dclocation['is_collected']=1;
			}else{
				$dclocation['is_collected']=0;
			}
			
			/*
			require_once APP_ROOT_PATH."app/Lib/main/dcajaxModule.class.php";
			$lid_info=array('location_id'=>$dclocation['id'],'menu_status'=>1);
			dcajaxModule::set_dc_cart_menu_status($is_ajax_return=0,$lid_info);
			*/
			$location_dc_cart=load_dc_cart_list(true,$dclocation['id'],$type=1);			

			foreach($location_dc_cart['cart_list'] as $xx=>$zz){
				
				$location_dc_cart['cart_list'][$xx]['icon']=get_abs_img_root(get_spec_image($zz['icon'],200,150,1));		
			}

			$id_arr=array($dclocation['id']=>array('id'=>$dclocation['id'],'distance'=>$dclocation['distance']));
			$location_delivery_info=get_location_delivery_info($id_arr);
			$location_delivery=array();
			foreach($location_delivery_info as $kk=>$vv){
				$location_delivery=$vv;	
			}
			
			$location_menu_cate=$this->get_location_menu_cate($dclocation['id']);
			
			foreach($location_menu_cate as $k=>$v){
				$location_menu_cate[$k]['main_cate']['icon_img']=get_abs_img_root(get_spec_image($v['main_cate']['icon_img'],250,250,1));
				foreach($location_menu_cate[$k]['sub_menu'] as $kk=>$vv){
					$location_menu_cate[$k]['sub_menu'][$kk]['image']=get_abs_img_root(get_spec_image($vv['image'],250,250,1));
				}
			}
			
			
			$dclocation['location_delivery_info']=$location_delivery;
		
			$location_dc_table_cart=load_dc_cart_list(true,$dclocation['id'],$type=0);
			
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
				$is_allow_add_cart=1;
			}
			

			
			$promote_info=get_dc_promote_info();

			$dclocation['location_menu_cate']=$location_menu_cate;
			$less_money=$dclocation['location_delivery_info']['start_price']-$location_dc_cart['total_data']['total_price'];
			$less_table_money=$location_dc_table_cart['total_data']['total_price']-$location_dc_cart['total_data']['total_price'];
			$root['is_has_location']=1;
			$root['page_title']=$page_title;
			$root['page_keyword']=$page_keyword;
			$root['page_description']=$page_description;
			$root['is_allow_add_cart']=$is_allow_add_cart;
			$root['promote_info']=$promote_info;
			$root['less_money']=$less_money;
			$root['less_table_money']=$less_table_money;
			//$root['location_dc_table_cart']=$location_dc_table_cart;
			//$root['location_dc_cart']=$location_dc_cart;
			$root['dclocation']=$dclocation;
			
			if($location_dc_table_cart['total_data']['total_price'] > 0){
				$dc_type=1;
			}else{
				$dc_type=-1;
			}
			$root['dc_type']=$dc_type;
			output($root);
		}
		else
		{	
			$root['is_has_location']=0;
			output($root);
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
			$cart_info=$GLOBALS['db']->getAll("select num, menu_id from ".DB_PREFIX."dc_cart where user_id=".$user_id." and session_id='".es_session::id()."' and cart_type=1  and is_effect=1 and menu_id in (".$id_str.")");
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
	
	$row_num=3;
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
	

	return array_values($location_menu_arr);
}
	

	
}
?>