<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 输出接口数据
 * @param unknown_type $data 返回的接口数据
 * @param unknown_type $status 当前状态 0/1
 * @param unknown_type $info 当前消息 可选，为空时由客户端取默认提示(默认提示包含成功的默认提示与失败的默认提示)
 */
function output($data,$status=1,$info="")
{
	header("Content-Type:text/html; charset=utf-8");
	$r_type = intval($_REQUEST['r_type']);//返回数据格式类型; 0:base64;1;json_encode;2:array 3:jsonp
	$data[CTL] = MODULE_NAME;
	$data[ACT] = ACTION_NAME;
	$data['status'] = $status;
	$data['info'] = $info;
	$data['city_name'] = $GLOBALS['city']['name'];
	$data['return'] = 1; //弃用该返回，统一返回1
	$data['sess_id'] = $GLOBALS['sess_id'];
	$data['ref_uid'] = $GLOBALS['ref_uid'];
	
	ob_clean();
	
	if ($r_type == 0)
	{
		echo base64_encode(json_encode($data));
	}
	else if ($r_type == 1)
	{
        echo(json_encode($data));
	}
	else if ($r_type == 2)
	{
		print_r($data);
	}
	else if($r_type == 3)
	{
		$json = json_encode($data);
		echo $_GET['callback']."(".$json.")";
	}else if($r_type == 4){
		require_once APP_ROOT_PATH.'/system/libs/crypt_aes.php';
		$aes = new CryptAES();
		$aes->set_key(FANWE_AES_KEY);
		$aes->require_pkcs5();
		$encText = $aes->encrypt(json_encode($data));
		echo $encText;
	}
	exit;
}


function get_abs_img_root($content)
{
	return format_image_path($content);
}
function get_muser_avatar($id,$type)
{
	$uid = sprintf("%09d", $id);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$path = $dir1.'/'.$dir2.'/'.$dir3;

	$id = str_pad($id, 2, "0", STR_PAD_LEFT);
	$id = substr($id,-2);
	$avatar_file = format_image_path("./public/avatar/".$path."/".$id."virtual_avatar_".$type.".jpg");
	$avatar_check_file = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_".$type.".jpg";
	if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	{
		if(!check_remote_file_exists($avatar_file))
			$avatar_file =  SITE_DOMAIN.APP_ROOT."/public/avatar/noavatar_".$type.".gif";
	}
	else
	{
		if(!file_exists($avatar_check_file))
			$avatar_file =  SITE_DOMAIN.APP_ROOT."/public/avatar/noavatar_".$type.".gif";
	}

	return  $avatar_file;
}


/**
 * 刷新会员安全登录状态
 */
function refresh_user_info()
{
	global $user_info;
	global $user_logined;
	//实时刷新会员数据
	if($user_info)
	{
		$user_info = load_user($user_info['id']);
		$user_level = load_auto_cache("cache_user_level");
		$user_info['level'] = $user_level[$user_info['level_id']]['level'];
		$user_info['level_name'] = $user_level[$user_info['level_id']]['name'];
		es_session::set('user_info',$user_info);

		$user_logined_time = intval(es_session::get("user_logined_time"));
		$user_logined = es_session::get("user_logined");
		if(NOW_TIME-$user_logined_time>=MAX_LOGIN_TIME)
		{
			es_session::set("user_logined_time",0);
			es_session::set("user_logined", false);
			$user_logined = false;
		}
		else
		{
			if($user_logined)
				es_session::set("user_logined_time",NOW_TIME);
		}
	}
}

/**
 * 前端全运行函数，生成系统前台使用的全局变量
 * 1. 定位城市 GLOBALS['city'];
 * 2. 加载会员 GLOBALS['user_info'];
 * 4. 加载推荐人与来路
 * 5. 更新购物车
 */
function global_run()
{
	if(app_conf("SHOP_OPEN")==0)  //网站关闭时跳转到站点关闭页
	{
		//app_redirect(wap_url("index","close"));
	}

	//处理城市
	global $city;
	require_once APP_ROOT_PATH."system/model/city.php";
	$city = City::locate_city($GLOBALS['request']['city_id']);
	
	//处理经纬度
	global $geo;
	$geo = City::locate_geo($GLOBALS['request']['m_longitude'],$GLOBALS['request']['m_latitude']);
	
	//会员自动登录及输出
	global $cookie_uname;
	global $cookie_upwd;
	global $user_info;
	global $user_logined;
	require_once APP_ROOT_PATH."system/model/user.php";
	$user_info = es_session::get('user_info');
	if(empty($user_info))
	{
		$cookie_uname = $GLOBALS['request']['email']?$GLOBALS['request']['email']:'';
		$cookie_upwd = $GLOBALS['request']['pwd']?$GLOBALS['request']['pwd']:'';
		if($cookie_uname!=''&&$cookie_upwd!=''&&!es_session::get("user_info"))
		{
			$cookie_uname = strim($cookie_uname);
			$cookie_upwd = strim($cookie_upwd);
			auto_do_login_user($cookie_uname,$cookie_upwd,false);
			$user_info = es_session::get('user_info');
		}
	}
	refresh_user_info();

	//此处是会员（商家登录状态的初始化）
	require_once APP_ROOT_PATH."system/libs/biz_user.php";
	global $cookie_biz_uname;
	global $cookie_biz_upwd;
	global $account_info;
	$account_info = es_session::get('account_info');
	
	if(empty($account_info))
	{
		$cookie_biz_uname = $GLOBALS['request']['biz_uname']?$GLOBALS['request']['biz_uname']:'';
		$cookie_biz_upwd = $GLOBALS['request']['biz_upwd']?$GLOBALS['request']['biz_upwd']:'';
		if($cookie_biz_uname!=''&&$cookie_biz_upwd!=''&&!es_session::get("account_info"))
		{
			$cookie_biz_uname = strim($cookie_biz_uname);
			$cookie_biz_upwd = strim($cookie_biz_upwd);
			do_login_biz($cookie_biz_uname, $cookie_biz_upwd);
			$account_info = es_session::get('account_info');
		}
	}
	//实时刷新会员数据
	if($account_info)
	{
		$account_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where is_delete = 0 and is_effect = 1 and id = ".intval($account_info['id']));
		if($account_info['is_main'] == 1){ //主账户取所有门店
			$account_locations = $GLOBALS['db']->getAll("select id as location_id from ".DB_PREFIX."supplier_location where supplier_id = ".$account_info['supplier_id']);
		}else
			$account_locations = $GLOBALS['db']->getAll("select location_id from ".DB_PREFIX."supplier_account_location_link where account_id = ".$account_info['id']);
	
		$account_location_ids = array();
		foreach($account_locations as $row)
		{
			$account_location_ids[] = $row['location_id'];
		}
		$account_info['location_ids'] =  $account_location_ids;
		$GLOBALS['account_info']['location_ids'] =  $account_location_ids;
	
		es_session::set('account_info',$account_info);
	}
	//end 商家端处理
	//刷新购物车
	require_once APP_ROOT_PATH."system/model/cart.php";
	refresh_cart_list();

	global $ref_uid;
		
	//保存返利的cookie
	if($GLOBALS['request']['ref_uid'])
	{
		$rid = intval($GLOBALS['request']['ref_uid']);
		$ref_uid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval($rid)));
	}
	
	global $supplier_info;  //商家信息
	//处理商户信息
	global $spid;
	$spid = intval($GLOBALS['request']['spid']);
	if($spid>0)
	{
		$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$spid);
	}
	
	global $referer;
	//保存来路
	$referer = $GLOBALS['request']['from'];
	$referer = strim($referer);

}

/**
 * 获取生活服务分类的大->小类结构
 * @return array
 * 结构如下
 * Array(
 * 		Array
        (
            [id] => 0
            [name] => 全部分类
            [icon_img] => 
            [iconfont]=>
            [iconcolor]=>
            [bcate_type] => Array
                (
                    [0] => Array
                        (
                            [id] => 0
                            [cate_id] => 0
                            [name] => 全部分类
                        )

                )

        )
 * )
 */
function getCateList(){
	$cate_list_rs = load_auto_cache("cache_cate_tree",array("type"=>0));
	
	$cate_list = array(
		array(
			"id"	=>	0,
			"name"	=>	"全部分类",	
			"icon_img"	=>	"",
			"iconfont"	=>	"",
			"iconcolor"	=>	"",
			"bcate_type"	=>	array(
				array(
						"id"	=>	0,
						"cate_id"	=>	0,
						"name"	=>	"全部分类"
				)		
			)	
		)		
	);
	foreach($cate_list_rs as $k=>$v)
	{
		$cate = array();
		$cate['id'] = $v['id'];
		$cate['name'] = $v['name']?$v['name']:"";
		$cate['icon_img'] = $v['icon_img']?$v['icon_img']:"";
		$cate['iconfont'] = $v['iconfont']?$v['iconfont']:"";
		$cate['iconcolor'] = $v['iconcolor']?$v['iconcolor']:"";
		$cate['bcate_type']	= array(
			array(
					"id"	=>	0,
					"cate_id"	=>	0,
					"name"	=>	"全部"
			)		
		);
		foreach($v['pop_nav'] as $kk=>$vv)
		{
			$bcate_type = array();
			$bcate_type['id']	=	$vv['id'];
			$bcate_type['cate_id'] = $vv['cate_id']?$vv['cate_id']:"";
			$bcate_type['name'] = $vv['name']?$vv['name']:"";
			$cate['bcate_type'][] = $bcate_type;
		}		
		$cate_list[] = $cate;
	}
	
	return $cate_list;
}


/**
 * 获取活动分类的结构
 * @return array
 * 结构如下
 * Array(
 * 		Array
        (
            [id] => 0
            [name] => 全部分类            
        )
 * )
 */
function getEventCateList(){
	$cate_list_rs = load_auto_cache("cache_cate_tree",array("type"=>4));

	$cate_list = array(
			array(
					"id"	=>	0,
					"name"	=>	"全部分类"					
			)
	);
	foreach($cate_list_rs as $k=>$v)
	{
		$cate = array();
		$cate['id'] = $v['id'];
		$cate['name'] = $v['name']?$v['name']:"";		
		$cate_list[] = $cate;
	}

	return $cate_list;
}


/**
 * 获取商城分类的大->小类结构
 * @return array
 * 结构如下
 * Array(
 * 		Array
        (
            [id] => 0
            [name] => 全部分类
            [iconfont]=>
            [iconcolor]=>
            [bcate_type] => Array
                (
                    [0] => Array
                        (
                            [id] => 0
                            [cate_id] => 0
                            [name] => 全部分类
                        )

                )

        )
 * )
 */
function getShopCateList(){
	$cate_list_rs = load_auto_cache("cache_cate_tree",array("type"=>1));

	$cate_list = array(
			array(
					"id"	=>	0,
					"name"	=>	"全部分类",
					"iconfont"	=>	"",
					"iconcolor"	=>	"",
					"bcate_type"	=>	array(
							array(
									"id"	=>	0,
									"cate_id"	=>	0,
									"name"	=>	"全部分类"
							)
					)
			)
	);
	foreach($cate_list_rs as $k=>$v)
	{
		$cate = array();
		$cate['id'] = $v['id'];
		$cate['name'] = $v['name']?$v['name']:"";
		$cate['iconfont'] = $v['iconfont']?$v['iconfont']:"";
		$cate['iconcolor'] = $v['iconcolor']?$v['iconcolor']:"";
		$cate['bcate_type']	= array(
				array(
						"id"	=>	$v['id'],
						"cate_id"	=>	$v['id'],
						"name"	=>	"全部"
				)
		);
		foreach($v['pop_nav'] as $kk=>$vv)
		{
			$bcate_type = array();
			$bcate_type['id']	=	$vv['id'];
			$bcate_type['cate_id'] = $vv['pid']?$vv['pid']:"";
			$bcate_type['name'] = $vv['name']?$vv['name']:"";
			$cate['bcate_type'][] = $bcate_type;
		}
		$cate_list[] = $cate;
	}

	return $cate_list;
}



/**
 * 获取地区商圈列表
 * @param int $city_id
 * @return array
 * 结构如下
 * Array(
 * 		Array
        (
            [id] => 0
            [name] => 全城
            [quan_sub] => Array
                (
                    [0] => Array
                        (
                            [id] => 0
                            [pid] => 0
                            [name] => 全城
                        )

                )

        )
 * )
 */
function getQuanList($city_id =0){
	$all_quan_list= load_auto_cache("cache_area",array("city_id"=>$city_id));

	
	$quan_list = array(
			array(
					"id"	=>	0,
					"name"	=>	"全城",
					"quan_sub"	=>	array(
							array(
									"id"	=>	0,
									"pid"	=>	0,
									"name"	=>	"全城"
							)
					)
			)
	);
	
	foreach($all_quan_list as $k=>$v)
	{
		if($v['pid']==0)
		{
			$area = array();
			$area['id'] = $v['id'];
			$area['name'] = $v['name']?$v['name']:"";
			$area['quan_sub']	= array(
					array(
							"id"	=>	0,
							"pid"	=>	0,
							"name"	=>	"全部"
					)
			);
			foreach($all_quan_list as $kk=>$vv)
			{
				if($vv['pid']==$v['id'])
				{
					$quan = array();
					$quan['id'] = $vv['id'];
					$quan['name'] = $vv['name']?$vv['name']:"";
					$quan['pid'] = $vv['pid'];
					$area['quan_sub'][] = $quan;
				}
			}
			$quan_list[] = $area;
		}
		
	}
	return $quan_list;
}

/**
 * 获取品牌
 * @param unknown_type $shop_cate_id
 */
function getBrandList($shop_cate_id)
{
	//获取品牌
	$brand_list = array( array("id"=>0,"name"=>"全部") );
	if($shop_cate_id>0)
	{
		$cate_key = load_auto_cache("shop_cate_key",array("cid"=>$shop_cate_id));
		$brand_list_rs = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."brand where match(tag_match) against('".$cate_key."' IN BOOLEAN MODE)  order by sort limit 100");
		foreach($brand_list_rs as $k=>$v)
		{
			$row['id'] = $v['id'];
			$row['name'] = $v['name'];
			$brand_list[]=$row;
		}
	}
	return $brand_list;
}


/**
 * 格式化列表的商品
 * @param unknown_type $v
 * @return unknown
 */
function format_deal_list_item($v)
{
	$deal['id'] = $v['id'];
	$deal['distance']= floatval($v['distance']);
	$deal['ypoint'] = floatval($v['ypoint']);
	$deal['xpoint'] = floatval($v['xpoint']);
	$deal['name'] = $v['name'];
	$deal['sub_name'] = $v['sub_name'];
	$deal['brief'] = $v['brief'];
	$deal['buy_count'] = $v['buy_count'];
	$deal['current_price']=round($v['current_price'],2);
	$deal['origin_price']=round($v['origin_price'],2);
	$deal['icon']=get_abs_img_root(get_spec_image($v['icon'],92,82,1));
	$deal['end_time_format']=to_date($v['end_time']);
	$deal['begin_time_format']=to_date($v['begin_time']);
	$deal['begin_time'] = $v['begin_time'];
	$deal['end_time'] = $v['end_time'];
	$deal['auto_order'] = $v['auto_order'];
	$deal['is_lottery'] = $v['is_lottery'];
	$deal['is_refund'] = $v['is_refund'];
	$deal['deal_score'] = abs($v['return_score']);
	$deal['buyin_app'] = intval($v['buyin_app']);
	
	if (empty($v['brief'])){
		$deal['brief'] = $v['name'];
		$deal['name'] = $v['sub_name'];
	}
		
	$today_begin = to_timespan(to_date(NOW_TIME,'Y-m-d'));
	$today_end = $today_begin*24*60*60;	
	if(($v['begin_time']>0) && ($today_begin<$v['begin_time'] && $v['begin_time']<$today_end))
	{
		$deal['is_today']=1;
	}
	else
	{
		$deal['is_today']=0;
	}
	return $deal;
}

/**
 * 格式化商家列表的商家数据
 * @param unknown_type $v
 * @return unknown
 */
function format_store_list_item($v)
{	
	$store['preview']=get_abs_img_root(get_spec_image($v['preview'],92,82,1));
	$store['id'] = $v['id'];
	$store['is_verify'] = $v['is_verify'];
	$store['avg_point'] = $v['avg_point'];
	$store['address'] = $v['address'];
	$store['name'] = $v['name'];
	$store['distance']= floatval($v['distance']);
	$store['xpoint'] = floatval($v['xpoint']);
	$store['ypoint'] = floatval($v['ypoint']);
	$store['tel'] =$v['tel'];
	return $store;
}


function format_store_item($v)
{

	$store['xpoint'] = floatval($v['xpoint']);
	$store['ypoint'] = floatval($v['ypoint']);
	$store['preview']=get_abs_img_root(get_spec_image($v['preview'],92,82));
	$store['id'] = $v['id'];
	$store['supplier_id'] = $v['supplier_id'];
	$store['is_verify'] = $v['is_verify'];
	$store['avg_point'] = round($v['avg_point'],1);
	$store['address'] = $v['address'];
	$store['name'] = $v['name'];
	$store['tel'] = $v['tel'];
	$store['brief'] = get_abs_img_root(format_html_content_image($v['brief'],150));//get_abs_url_root($v['brief']);
	$store['store_images'] = $v['store_images'];
	$store['share_url'] = $v['share_url'];
	return $store;
}

function format_event_item($v){
    $event['id']= $v['id'];
    $event['name']= $v['name'];
    $event['icon']=get_abs_img_root(get_spec_image($v['icon'],300,182,1));
    $event['event_begin_time'] = $v['event_begin_time'];
    $event['event_end_time'] = $v['event_end_time'];
    $event['event_begin_time_format'] = to_date($v['event_begin_time'],"Y-m-d");
    $event['event_end_time_format'] = to_date($v['event_end_time'],"Y-m-d");
    $event['submit_begin_time_format']= to_date($v['submit_begin_time']);
    $event['submit_end_time_format']= to_date($v['submit_end_time']);
    $event['submit_count'] = $v['submit_count'];
    $event['total_count'] = $v['total_count'];
    $event['score_limit'] = $v['score_limit'];
    $event['point_limit'] = $v['point_limit'];
    $event['now_time']= NOW_TIME;
    $event['submit_begin_time'] = $v['submit_begin_time'];
    $event['submit_end_time'] = $v['submit_end_time'];
    $event['supplier_info_name'] = $v['supplier_info']['name'];
    $event['content'] = get_abs_img_root(format_html_content_image($v['content'],150));
    $event['address'] = $v['address'];
    $event['avg_point'] = round($v['avg_point'],1);
    $event['ypoint'] = floatval($v['ypoint']);
    $event['xpoint'] = floatval($v['xpoint']);
    if ($v['submitted_data']){
        $submitted_data['is_verify'] = $v['submitted_data']['is_verify'];
        $event['submitted_data'] = $submitted_data;
    }
    
    $event['event_fields'] = $v['event_fields'];
    $event['share_url'] = $v['share_url'];
    return $event;
}

/**
 * 格式化活动列表的活动数据
 * @param unknown_type $v
 * @return string
 */
function format_event_list_item($v)
{
	$event['id']= $v['id'];
	$event['name']= $v['name'];
	$event['distance']= floatval($v['distance']);
	$event['icon']=get_abs_img_root(get_spec_image($v['icon'],300,182,1));
	$event['submit_begin_time_format']= to_date($v['submit_begin_time']);
	$event['submit_end_time_format']= to_date($v['submit_end_time']);
	$event['submit_count'] = $v['submit_count'];
	$event['xpoint'] = floatval($v['xpoint']);
	$event['ypoint'] = floatval($v['ypoint']);
	if($v['submit_end_time']==0)
		$event['sheng_time_format']= "永不过期";
	else
		$event['sheng_time_format']= to_date($v['submit_end_time']-NOW_TIME,"d天h小时i分");
	return $event;
}

function format_youhui_item($v){
    $item['id'] = $v['id'];
    $item['name'] = $v['name'];
    $item['icon'] = get_abs_img_root(get_spec_image($v['icon'],300,182));
    $item['now_time'] = NOW_TIME;
    $item['begin_time'] = $v['begin_time'];
    $item['end_time'] = $v['end_time'];
    $item['last_time'] = intval($v['end_time'])>NOW_TIME?(intval($v['end_time'])-NOW_TIME):0;
    $item['last_time_format'] = $item['last_time']%86400>0?intval($item['last_time']/86400)."天以上":$item['last_time']/86400;
    $item['expire_day'] = $v['expire_day'];
    $item['total_num'] = $v['total_num'];
    $item['is_effect'] = $v['is_effect'];
    $item['user_count'] = $v['user_count'];
    $item['user_limit'] = $v['user_limit'];
    $item['score_limit'] = $v['score_limit'];
    $item['point_limit'] = $v['point_limit'];
    $item['supplier_info_name'] = $v['supplier_info']['name'];
    $item['avg_point'] = round($v['avg_point'],1);
    $item['ypoint'] = floatval($v['ypoint']);
    $item['xpoint'] = floatval($v['xpoint']);
    $item['description'] = get_abs_img_root(format_html_content_image($v['description'],150));
    $item['use_notice'] = get_abs_img_root(format_html_content_image($v['use_notice'],150));
    $item['share_url'] = $v['share_url'];
    $item['less'] = $v['less'];
    
    return $item;
}

/**
 * 格式化优惠券列表优惠券数据
 * @param unknown_type $v
 * @return string
 */
function format_youhui_list_item($v)
{
	$youhui['id'] = $v['id'];
	$youhui['distance']= floatval($v['distance']);
	$youhui['name'] = $v['name'];
	$youhui['list_brief'] = $v['list_brief'];
	$youhui['icon']=get_abs_img_root(get_spec_image($v['icon'],92,82,1));
	$youhui['down_count'] = $v['user_count'];
	$youhui['youhui_type'] = $v['youhui_type'];
	$youhui['xpoint'] = floatval($v['xpoint']);
	$youhui['ypoint'] = floatval($v['ypoint']);
	$begin_time = to_date($v['begin_time'],"Y-m-d");
	$end_time = to_date($v['end_time'],"Y-m-d");
	if($begin_time&&$end_time)
		$time_str = $begin_time."至".$end_time;
	elseif($begin_time&&!$end_time)
	$time_str = $begin_time."开始";
	elseif(!$begin_time&&$end_time)
	$time_str = $end_time."结束";
	else
		$time_str = "无限期";
	$youhui['begin_time'] = $time_str;
	return $youhui;
}


/**
 * 格式化商品的返回数据
 * @param unknown_type $v
 */
function format_deal_item($deal)
{
//     echo 90572744%86400>0?intval(90572744/86400)."天以上":90572744/86400;exit;
	$data['id'] = $deal['id'];
	$data['name'] = $deal['name'];
	$data['sub_name'] = $deal['sub_name'];
	$data['brief'] = $deal['brief'];
	$data['current_price'] = round($deal['current_price'],2);
	$data['origin_price'] = round($deal['origin_price'],2);
	$data['icon'] = get_abs_img_root(get_spec_image($deal['icon'],300,182,1));
	$data['begin_time'] = $deal['begin_time'];
	$data['end_time'] = $deal['end_time'];
	$data['time_status'] = $deal['time_status'];
	$data['now_time'] = NOW_TIME;
	$data['buy_count'] = $deal['buy_count'];
	$data['buy_type'] = $deal['buy_type'];
	$data['is_shop'] = $deal['is_shop'];	
	if($data['buy_type']==1)
		$data['return_score_show'] = abs($deal['return_score']);
	$data['deal_attr'] = $deal['deal_attr'];
	$data['avg_point'] = round($deal['avg_point'],2);
	$data['dp_count'] = $deal['dp_count'];
	$data['supplier_location_count'] = $deal['supplier_location_count'];
// 	[less_time] => 90576716
// 	[less_time_format] => 1048天以上
	$data['last_time'] = intval($deal['end_time'])>NOW_TIME?(intval($deal['end_time'])-NOW_TIME):0;
	$data['last_time_format'] = $data['last_time']%86400>0?intval($data['last_time']/86400)."天以上":$data['last_time']/86400;
	
	$deal_tags = $deal['deal_tags'];	
	$deal_tags_txt = array("0元抽奖","免预约","多套餐","可订座","折扣券","过期退","随时退","七天退","免运费","满立减");
	$data['deal_tags'] = array();
	foreach($deal_tags as $k=>$v)
	{
		$tag['k'] = $v;
		$tag['v'] = $deal_tags_txt[$v];
		$data['deal_tags'][$k] = $tag;
	}
	$images = array();
	$oimages = array();
	foreach ($deal['image_list'] as $k=>$v){
	    $images[] = get_abs_img_root(get_spec_image($v['img'],230,140,1));
	    $oimages[] = get_abs_img_root($v['img']);
	}
	$data['images'] = $images;
	$data['oimages'] = $oimages;
	$data['description']=get_abs_img_root(format_html_content_image($deal['description'],150));
	$data['notes']=format_html_content_image($deal['notes'], 220,220);
	$data['share_url'] = $deal['share_url'];
	$data['ypoint'] = floatval($deal['ypoint']);
	$data['xpoint'] = floatval($deal['xpoint']);
	$data['buyin_app'] = intval($deal['buyin_app']);
	$data['is_fx'] = intval($deal['is_fx']);
	return $data;
}

/**
 * 登入状态检测
 * @param 
 * @return int  0表示未登录  1表示已登录 2表示临时登录
 */
function check_login(){
	require_once APP_ROOT_PATH."system/model/user.php";
	return check_save_login();
}

function format_dp_list($dp_list){
    require_once APP_ROOT_PATH."system/model/user.php";
    $format_dp_list = array();
     
    foreach($dp_list['list'] as $k=>$v){
         
        $temp_arr = array();
    
        $temp_arr['id'] = $v['id'];
        $temp_arr['create_time'] = $v['create_time'] > 0 ?to_date($v['create_time'],'Y-m-d'):'';
        $temp_arr['content'] = $v['content'];
        $temp_arr['reply_content']= $v['reply_content']?$v['reply_content']:'';
        $temp_arr['point'] = $v['point'];
         
        $uinfo = load_user($v['user_id']);
        $temp_arr['user_name'] = $uinfo['user_name'];
         
         
         
        $images = array();
        $oimages = array();
         
        if($v['images']){
            foreach ($v['images'] as $ik=>$iv){
                $images[] = get_abs_img_root(get_spec_image($iv,60,60,1));
                $oimages[] = get_abs_img_root($iv);
            }
    
        }
        $temp_arr['images'] = $images;
        $temp_arr['oimages'] = $oimages;
         
         
        $format_dp_list[] = $temp_arr;
    }
    return $format_dp_list;
}

/**
 * 获取商户权限
 * @return boolean|Ambigous <mixed, multitype:>
 */
function get_biz_account_auth(){
    $s_account_info = $GLOBALS["account_info"];
    if(es_session::get("biz_account_auth")){
        $biz_account_auth = unserialize(base64_decode(es_session::get("biz_account_auth")));
    }else{
        $nav_list = require APP_ROOT_PATH."system/biz_cfg/".APP_TYPE."/biznav_cfg.php";
        if(OPEN_WEIXIN)
        {
        	if($weixin_conf['platform_status']==1)
        	{
        	$config_file = APP_ROOT_PATH."system/biz_cfg/".APP_TYPE."/wxbiznav_cfg.php";
        	$nav_list = array_merge_biznav($nav_list, $config_file);
        	}
        }
        if(OPEN_FX)
        {
        	$config_file = APP_ROOT_PATH."system/biz_cfg/".APP_TYPE."/fxbiznav_cfg.php";
        	$nav_list = array_merge_biznav($nav_list, $config_file);
        }
        if(OPEN_DC)
        {
        	$config_file = APP_ROOT_PATH."system/biz_cfg/".APP_TYPE."/dcbiznav_cfg.php";
        	$nav_list = array_merge_biznav($nav_list, $config_file);
        }
        if($s_account_info['is_main']){//管理员
            foreach($nav_list as $k=>$v)
            {
                $module_name = $k;
                foreach($v['node'] as $kk=>$vv)
                {
                    $module_name = $vv['module'];
                    $biz_account_auth[] = $module_name;
                }
            }
        }else{

            $result = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."supplier_account_auth WHERE supplier_account_id=".$s_account_info['id']." order by id asc");

            if(empty($result)){
                return false;
            }
            foreach($result as $k=>$v){
                $has_module[] = $v['module'];
            }
            $biz_account_auth = array_unique($has_module);
        }
        es_session::set("biz_account_auth", base64_encode(serialize($biz_account_auth)));
    }
    return $biz_account_auth;
}

/**
 * 验证商户权限
 * @param unknown $module
 * @return boolean
 */
function check_module_auth($module)
{
    //获取权限进行判断
    $biz_account_auth = get_biz_account_auth();
    if(!in_array($module, $biz_account_auth)){
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * 分享点评的上传，上传到comment目录，按日期划分
 * 错误返回 error!=0,message错误消息
 * 正确时返回 error=0, url: ./public格式的文件相对路径  path:物理路径 name:文件名
 * thumb->preview 60x60的小图 url,path
 */
function upload_topic($_files)
{


	//上传处理
	//创建comment目录
	if (!is_dir(APP_ROOT_PATH."public/comment")) {
		@mkdir(APP_ROOT_PATH."public/comment");
		@chmod(APP_ROOT_PATH."public/comment", 0777);
	}

	$dir = to_date(NOW_TIME,"Ym");
	if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
		@mkdir(APP_ROOT_PATH."public/comment/".$dir);
		@chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
	}
		
	$dir = $dir."/".to_date(NOW_TIME,"d");
	if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
		@mkdir(APP_ROOT_PATH."public/comment/".$dir);
		@chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
	}

	$dir = $dir."/".to_date(NOW_TIME,"H");
	if (!is_dir(APP_ROOT_PATH."public/comment/".$dir)) {
		@mkdir(APP_ROOT_PATH."public/comment/".$dir);
		@chmod(APP_ROOT_PATH."public/comment/".$dir, 0777);
	}
		
	if(app_conf("IS_WATER_MARK")==1)
		$img_result = save_image_upload($_files,"file","comment/".$dir,$whs=array('preview'=>array(60,60,1,0)),1,1);
	else
		$img_result = save_image_upload($_files,"file","comment/".$dir,$whs=array('preview'=>array(60,60,1,0)),0,1);
	if(intval($img_result['error'])!=0)
	{
		return $img_result;
	}
	else
	{
		if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		{
			syn_to_remote_image_server($img_result['file']['url']);
			syn_to_remote_image_server($img_result['file']['thumb']['preview']['url']);
		}

	}

	$data_result['error'] = 0;
	$data_result['url'] = $img_result['file']['url'];
	$data_result['path'] = $img_result['file']['path'];
	$data_result['name'] = $img_result['file']['name'];
	$data_result['thumb'] = $img_result['file']['thumb'];

	require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
	$image = new es_imagecls();
	$info = $image->getImageInfo($img_result['file']['path']);

	$image_data['width'] = intval($info[0]);
	$image_data['height'] = intval($info[1]);
	$image_data['name'] = valid_str($_FILES['file']['name']);
	$image_data['filesize'] = filesize($img_result['file']['path']);
	$image_data['create_time'] = NOW_TIME;
	$image_data['user_id'] = intval($GLOBALS['user_info']['id']);
	$image_data['user_name'] = strim($GLOBALS['user_info']['user_name']);
	$image_data['path'] = $img_result['file']['thumb']['preview']['url'];
	$image_data['o_path'] = $img_result['file']['url'];
	$GLOBALS['db']->autoExecute(DB_PREFIX."topic_image",$image_data);

	$data_result['id'] = intval($GLOBALS['db']->insert_id());

	return $data_result;

}

/**
 * 
 * @param unknown_type $_files 上传的头像文件数据 file
 * @param unknown_type $id 会员ID
 * @return data: error:0无错，1错误 message:消息
 * error:0时
 * small_url 小图
 * middle_url 中图
 * big_url 大图
 */
function upload_avatar($_files,$id){

	//创建avatar临时目录
	if (!is_dir(APP_ROOT_PATH."public/avatar")) {
		@mkdir(APP_ROOT_PATH."public/avatar");
		@chmod(APP_ROOT_PATH."public/avatar", 0777);
	}
	if (!is_dir(APP_ROOT_PATH."public/avatar/temp")) {
		@mkdir(APP_ROOT_PATH."public/avatar/temp");
		@chmod(APP_ROOT_PATH."public/avatar/temp", 0777);
	}
	$upd_id = $id;

	if (is_animated_gif($_files['file']['tmp_name']))
	{
		$rs = save_image_upload($_files,"file","avatar/temp",$whs=array());

		$im = get_spec_gif_anmation($rs['file']['path'],48,48);
		$file_name = APP_ROOT_PATH."public/avatar/temp/".md5(get_gmtime().$upd_id)."_small.jpg";
		file_put_contents($file_name,$im);
		$img_result['file']['thumb']['small']['path'] = $file_name;

		$im = get_spec_gif_anmation($rs['file']['path'],120,120);
		$file_name = APP_ROOT_PATH."public/avatar/temp/".md5(get_gmtime().$upd_id)."_middle.jpg";
		file_put_contents($file_name,$im);
		$img_result['file']['thumb']['middle']['path'] = $file_name;

		$im = get_spec_gif_anmation($rs['file']['path'],200,200);
		$file_name = APP_ROOT_PATH."public/avatar/temp/".md5(get_gmtime().$upd_id)."_big.jpg";
		file_put_contents($file_name,$im);
		$img_result['file']['thumb']['big']['path'] = $file_name;
	}
	else{
		$img_result = save_image_upload($_files,"file","avatar/temp",$whs=array('small'=>array(48,48,1,0),'middle'=>array(120,120,1,0),'big'=>array(200,200,1,0)));
	}


	if(intval($img_result['error'])!=0)
	{
		$data['error'] = 1;
		$data['message'] = "上传失败";
		return $data;
	}
		
	//开始移动图片到相应位置

	$uid = sprintf("%09d", $id);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$path = $dir1.'/'.$dir2.'/'.$dir3;

	//创建相应的目录
	if (!is_dir(APP_ROOT_PATH."public/avatar/".$dir1)) {
		@mkdir(APP_ROOT_PATH."public/avatar/".$dir1);
		@chmod(APP_ROOT_PATH."public/avatar/".$dir1, 0777);
	}
	if (!is_dir(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2)) {
		@mkdir(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2);
		@chmod(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2, 0777);
	}
	if (!is_dir(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2.'/'.$dir3)) {
		@mkdir(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2.'/'.$dir3);
		@chmod(APP_ROOT_PATH."public/avatar/".$dir1.'/'.$dir2.'/'.$dir3, 0777);
	}

	$id = str_pad($id, 2, "0", STR_PAD_LEFT);
	$id = substr($id,-2);
	$avatar_file_big = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_big.jpg";
	$avatar_file_middle = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_middle.jpg";
	$avatar_file_small = APP_ROOT_PATH."public/avatar/".$path."/".$id."virtual_avatar_small.jpg";


	@file_put_contents($avatar_file_big, file_get_contents($img_result['file']['thumb']['big']['path']));
	@file_put_contents($avatar_file_middle, file_get_contents($img_result['file']['thumb']['middle']['path']));
	@file_put_contents($avatar_file_small, file_get_contents($img_result['file']['thumb']['small']['path']));
	@unlink($img_result['file']['thumb']['big']['path']);
	@unlink($img_result['file']['thumb']['middle']['path']);
	@unlink($img_result['file']['thumb']['small']['path']);
	@unlink($img_result['file']['path']);

	if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	{
		syn_to_remote_image_server("./public/avatar/".$path."/".$id."virtual_avatar_big.jpg");
		syn_to_remote_image_server("./public/avatar/".$path."/".$id."virtual_avatar_middle.jpg");
		syn_to_remote_image_server("./public/avatar/".$path."/".$id."virtual_avatar_small.jpg");
	}

	//上传成功更新用户头像的动态缓存
	$data['error'] = 0;
	$data['small_url'] = get_muser_avatar($upd_id,"small");
	$data['middle_url'] = get_muser_avatar($upd_id,"middle");
	$data['big_url'] = get_muser_avatar($upd_id,"big");
	return $data;
}

/**
 * 验证当前版本是否正在升级审核中，是否允许显示第三方的支付接口与登录接口
 * 返回true/false
 */
function allow_show_api()
{
	if($GLOBALS['request']['from']=="ios")
	{
		if($GLOBALS['request']['version']==IOS_CLIENT_VERSION&&IS_IOS_UPGRADING)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	else
	{
		if($GLOBALS['request']['version']==ANDROID_CLIENT_VERSION&&IS_ANDROID_UPGRADING)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}


/**
 * 加载首页专题
 */
function load_zt()
{
	$city_id = $GLOBALS['city']['id'];
	
	if(APP_INDEX=="wap")
	$sql = " select * from ".DB_PREFIX."m_zt where mobile_type = '1' and city_id in (0,".intval($city_id).") and status = 1 order by sort asc ";
	else
	$sql = " select * from ".DB_PREFIX."m_zt where mobile_type = '0' and city_id in (0,".intval($city_id).") and status = 1 order by sort asc ";
	$zt_list = $GLOBALS['db']->getAll($sql);
	
	$html = $GLOBALS['cache']->get("MOBILE_INDEX_ZT_".intval($city_id)."_".APP_INDEX);
	
	if($html===false)
	{
		$html = "";
		if($zt_list)
		$html .= $GLOBALS['tmpl']->fetch("inc/".APP_INDEX."_header.html");
		foreach($zt_list as $k=>$v)
		{
			if(APP_INDEX=="wap")
				$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '1' and position = 2 and city_id in (0,".intval($city_id).") and status = 1 and zt_id = ".$v['id'];
			else
				$sql = " select * from ".DB_PREFIX."m_adv where mobile_type = '0' and position = 2 and city_id in (0,".intval($city_id).") and status = 1 and zt_id = ".$v['id'];
			$zt_layout_list = $GLOBALS['db']->getAll($sql);
		
			//先输出推荐位的变量
			$v['data'] = unserialize($v['data']);
			$GLOBALS['tmpl']->assign("url",getHtmlUrl($v));
			$GLOBALS['tmpl']->assign("title",$v['zt_title']);
		
			//开始输出每个广告位的变量
			foreach($zt_layout_list as $kk=>$vv)
			{
				$vv['data'] = unserialize($vv['data']);
				$GLOBALS['tmpl']->assign($vv['zt_position']."_a",getHtmlUrl($vv));
				$GLOBALS['tmpl']->assign($vv['zt_position']."_img",$vv['img']);
			}
		
			$html .= $GLOBALS['tmpl']->fetch($v['zt_moban']);
			foreach($zt_layout_list as $kk=>$vv)
			{
				$GLOBALS['tmpl']->assign($vv['zt_position']."_a","");
				$GLOBALS['tmpl']->assign($vv['zt_position']."_img","");
			}
		}	
		$GLOBALS['cache']->set("MOBILE_INDEX_ZT_".intval($city_id)."_".APP_INDEX,$html);
	}
		
	return $html;
}

function getHtmlUrl($data){
	//2:URL广告;9:团购列表;10:商品列表;11:活动列表;12:优惠列表;14:团购明细;15:商品明细;17:优惠明细;22:商家列表;23：商家明细; 24:门店自主下单

	if($data['ctl']=="url")
	{
		$url = $data['data']['url'];
		if(empty($url))
		{
			$url = "javascript:void(0);";
		}
	}
	else
	{
		if(APP_INDEX=="wap")
			$url = SITE_DOMAIN.wap_url("index",$data['ctl'],$data['data']);
		else
		{
			static $nav_cfg;
			if($nav_cfg===null)
			$nav_cfg = require_once APP_ROOT_PATH."system/mobile_cfg/".APP_TYPE."/webnav_cfg.php";
			
			if(OPEN_FX)
			{
				$config_file = APP_ROOT_PATH."system/mobile_cfg/".APP_TYPE."/fxwebnav_cfg.php";
				$nav_cfg = array_merge_mobile_cfg($nav_cfg, $config_file);
			}
			if(OPEN_WEIXIN)
			{
				if($weixin_conf['platform_status']==1)
				{
				$config_file = APP_ROOT_PATH."system/mobile_cfg/".APP_TYPE."/wxwebnav_cfg.php";
				$nav_cfg = array_merge_mobile_cfg($nav_cfg, $config_file);
				}
			}
			if(OPEN_DC)
			{
				$config_file = APP_ROOT_PATH."system/mobile_cfg/".APP_TYPE."/dcwebnav_cfg.php";
				$nav_cfg = array_merge_mobile_cfg($nav_cfg, $config_file);
			}
			$key = $nav_cfg[APP_INDEX]['nav'][$data['ctl']]['field'];
			$id = intval($data['data'][$key]);
			$url = "javascript:App.app_detail(".$data['type'].",".$id.")";
		}
	}

	return $url;

}

?>