<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


//获取真实路径
function get_real_path()
{
	return APP_ROOT_PATH;
}

//获取GMTime
function get_gmtime()
{
	$now = (time() - date('Z'));
	return $now;
}

function to_date($utc_time, $format = 'Y-m-d H:i:s') {
	if (empty ( $utc_time )) {
		return '';
	}
	$timezone = intval(app_conf('TIME_ZONE'));
	$time = $utc_time + $timezone * 3600; 
	return date ($format, $time );
}

function to_timespan($str, $format = 'Y-m-d H:i:s')
{
	$timezone = intval(app_conf('TIME_ZONE'));
	//$timezone = 8; 
	$time = intval(strtotime($str));
	if($time!=0)
	$time = $time - $timezone * 3600;
    return $time;
}

/**
 *
 * 获取商品是否为当天上线商品
 */
function get_is_today($deal)
{
	if($deal['begin_time']==0) return 0;
	$day_begin =  to_timespan(to_date(NOW_TIME,"Y-m-d"),"Y-m-d");
	$day_end  = $day_begin+3600*24-1;
	if($deal['begin_time']>=$day_begin&&$deal['begin_time']<$day_end)
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

//获取客户端IP
function get_client_ip() {
	//使用wap时，是通过中转方式，所以要在wap/index.php获取客户ip,转入到:sjmapi上 chenfq by add 2014-11-01
	if (isset($GLOBALS['request']['client_ip']) && !empty($GLOBALS['request']['client_ip']))
		$ip = $GLOBALS['request']['client_ip'];
	else if (isset($_REQUEST['client_ip']) && !empty($_REQUEST['client_ip']))
		$ip = $_REQUEST['client_ip'];	
	else if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
		$ip = getenv ( "HTTP_CLIENT_IP" );
	else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
		$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
	else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
		$ip = getenv ( "REMOTE_ADDR" );
	else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
		$ip = $_SERVER ['REMOTE_ADDR'];
	else
		$ip = "0.0.0.0";
	if(!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)/", $ip))
		$ip = "0.0.0.0";
	return strim($ip);
}

//过滤注入
function filter_injection(&$request)
{
	$pattern = "/(select[\s])|(insert[\s])|(update[\s])|(delete[\s])|(from[\s])|(where[\s])/i";
	foreach($request as $k=>$v)
	{
				if(preg_match($pattern,$k,$match))
				{
						die("SQL Injection denied!");
				}
		
				if(is_array($v))
				{					
					filter_injection($v);
				}
				else
				{					
					
					if(preg_match($pattern,$v,$match))
					{
						die("SQL Injection denied!");
					}					
				}
	}
	
}

//过滤请求
function filter_request(&$request)
{
		if(MAGIC_QUOTES_GPC)
		{
			foreach($request as $k=>$v)
			{
				if(is_array($v))
				{
					filter_request($request[$k]);
				}
				else
				{
					$request[$k] = stripslashes(trim($v));
				}
			}
		}
		
}

function adddeepslashes(&$request)
{

			foreach($request as $k=>$v)
			{
				if(is_array($v))
				{
					adddeepslashes($v);
				}
				else
				{
					$request[$k] = addslashes(trim($v));
				}
			}		
}

//request转码
function convert_req(&$req)
{
	foreach($req as $k=>$v)
	{
		if(is_array($v))
		{
			convert_req($req[$k]);
		}
		else
		{
			if(!is_u8($v))
			{
				$req[$k] = iconv("gbk","utf-8",$v);
			}
		}
	}
}

function is_u8($string)
{
	if(strlen($string)>255)
	$tag = true;
	else
	$tag = preg_match('%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $string);

   return $tag;
// 	$encode = mb_detect_encoding($string,array("GB2312","GBK","UTF-8"));
// 	if($encode=="UTF-8")
// 		return true;
// 	else
// 		return false;
}

//清除缓存
function clear_cache()
{
		//系统后台缓存
		syn_dealing();
		clear_dir_file(get_real_path()."public/runtime/admin/Cache/");	
		clear_dir_file(get_real_path()."public/runtime/admin/Data/_fields/");		
		clear_dir_file(get_real_path()."public/runtime/admin/Temp/");	
		clear_dir_file(get_real_path()."public/runtime/admin/Logs/");	
		@unlink(get_real_path()."public/runtime/admin/~app.php");
		@unlink(get_real_path()."public/runtime/admin/~runtime.php");
		@unlink(get_real_path()."public/runtime/admin/lang.js");
		@unlink(get_real_path()."public/runtime/app/config_cache.php");	
		
		
		//数据缓存
		clear_dir_file(get_real_path()."public/runtime/app/data_caches/");				
		clear_dir_file(get_real_path()."public/runtime/app/db_caches/");
		$GLOBALS['cache']->clear();
		clear_dir_file(get_real_path()."public/runtime/data/");

		//模板页面缓存
		clear_dir_file(get_real_path()."public/runtime/app/tpl_caches/");		
		clear_dir_file(get_real_path()."public/runtime/app/tpl_compiled/");
		@unlink(get_real_path()."public/runtime/app/lang.js");	
		
		//脚本缓存
		clear_dir_file(get_real_path()."public/runtime/statics/");		
			
				
		
}
function clear_dir_file($path,$include_path=true)
{
   if ( $dir = opendir( $path ) )
   {
            while ( $file = readdir( $dir ) )
            {
                $check = is_dir( $path. $file );
                if ( !$check )
                {
                    @unlink( $path . $file );                       
                }
                else 
                {
                 	if($file!='.'&&$file!='..')
                 	{
                 		clear_dir_file($path.$file."/");              			       		
                 	} 
                 }           
            }
            closedir( $dir );
            if($include_path)
            rmdir($path);
            return true;
   }
}

//同步未过期团购的状态
function syn_dealing()
{
	$deals = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."deal where time_status <> 2");
	foreach($deals as $v)
	{
		syn_deal_status($v['id']);
	}
}

function check_install()
{
	if(!file_exists(get_real_path()."public/install.lock"))
	{
	    clear_cache();
		header('Location:'.APP_ROOT.'/install');
		exit;
	}
}

function syn_brand_match($brand_id)
{
	$brand = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."brand where id = ".$brand_id);
	if($brand)
	{
		$brand['tag_match'] = "";
		$brand['tag_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."brand", $brand, $mode = 'UPDATE', "id=".$brand_id, $querymode = 'SILENT');

		//标签
		$tags = preg_split("/[ ,]/i",$brand['tag']);
		foreach($tags as $row)
		{
			$tag = trim($row);
			if(trim($tag)!="")
				insert_match_item($tag,"brand",$brand_id,"tag_match");

		}
		
		//关于分类
		$cate_id = $brand['shop_cate_id'];
		require_once APP_ROOT_PATH."system/utils/child.php";
		$ids_util = new child("shop_cate");
		$ids = $ids_util->getChildIds($cate_id);
		$ids[] = $cate_id;
			
		$deal_cate = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."shop_cate where id in (".implode(",", $ids).") and is_effect = 1 and is_delete = 0");
		
		foreach($deal_cate as $k=>$item)
		{
			$name_words = div_str($item['name']);
			foreach($name_words as $kk=>$vv)
			{
				if(trim($vv)!="")
				insert_match_item(trim($vv),"brand",$brand_id,"tag_match");
			}
		}

	}
}
function syn_brand_status($id)
{
	//同步品牌状态
	$brand_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."brand where id = ".$id);
	//1 无开始与结束时间
	if($brand_info['begin_time']==0&&$brand_info['end_time']==0)
	{
		if($brand_info['time_status']!=0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 0 where id =".$id);
		}
		return 0;
	}
	
	//2 无开始时间，有结束时间
	if($brand_info['begin_time']==0&&$brand_info['end_time']!=0)
	{
		
		//进行中
		if($brand_info['end_time']>NOW_TIME)
		{
			if($brand_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 0 where id =".$id);
			}
			return 0;
		}
		//过期
		if($brand_info['end_time']<=NOW_TIME)
		{
			if($brand_info['time_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 2 where id =".$id);
			}
			return 2;
		}
	}
	
	//3 有开始时间，无结束时间
	if($brand_info['begin_time']!=0&&$brand_info['end_time']==0)
	{
		//进行中
		if($brand_info['begin_time']<=NOW_TIME)
		{
			if($brand_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 0 where id =".$id);
			}
			return 0;
		}
		//未开始
		if($brand_info['begin_time']>NOW_TIME)
		{
			if($brand_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 1 where id =".$id);
			}
			return 1;
		}
	}
	
	//4 开始结束都有时间
	if($brand_info['begin_time']!=0&&$brand_info['end_time']!=0)
	{
		//未开始
		if($brand_info['begin_time']>NOW_TIME)
		{
			if($brand_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 1 where id =".$id);
			}
			return 1;
		}
		//进行中
		if($brand_info['begin_time']<=NOW_TIME&&$brand_info['end_time']>NOW_TIME)
		{
			if($brand_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 0 where id =".$id);
			}
			return 0;
		}
		//过期

		if($brand_info['end_time']<=NOW_TIME)
		{
			if($brand_info['time_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."brand set time_status = 2 where id =".$id);
			}
			return 2;
		}		
	}
}

//同步XXID的团购商品的状态,time_status,buy_status
function syn_deal_status($id,$dynamic = false)
{
	if(!$dynamic)
	{
		static $cache_goods_list;
		if($cache_goods_list[$id])return $cache_goods_list[$id];
	}
	$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)." or uname = '".$id."'");
	//时间状态
	//1 无开始与结束时间
	if($deal_info['begin_time']==0&&$deal_info['end_time']==0)
	{
		if($deal_info['time_status']!=1)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".intval($deal_info['id']));
			$deal_info['time_status'] = 1;
		}
	}
	//2 无开始时间，有结束时间
	if($deal_info['begin_time']==0&&$deal_info['end_time']!=0)
	{
		
		//进行中
		if($deal_info['end_time']>NOW_TIME)
		{
			if($deal_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 1;
			}
		}
		//过期
		if($deal_info['end_time']<=NOW_TIME)
		{
			if($deal_info['time_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 2 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 2;
			}
		}
	}
	
	//3 有开始时间，无结束时间
	if($deal_info['begin_time']!=0&&$deal_info['end_time']==0)
	{
		//进行中
		if($deal_info['begin_time']<=NOW_TIME)
		{
			if($deal_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 1;
			}
		}
		//未开始
		if($deal_info['begin_time']>NOW_TIME)
		{
			if($deal_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 0 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 0;
			}
		}
	}
	
	//4 开始结束都有时间
	if($deal_info['begin_time']!=0&&$deal_info['end_time']!=0)
	{
		//未开始
		if($deal_info['begin_time']>NOW_TIME)
		{
			if($deal_info['time_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 0 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 0;
			}
		}
		//进行中
		if($deal_info['begin_time']<=NOW_TIME&&$deal_info['end_time']>NOW_TIME)
		{
			if($deal_info['time_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 1 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 1;
			}
		}
		//过期

		if($deal_info['end_time']<=NOW_TIME)
		{
			if($deal_info['time_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set time_status = 2 where id =".intval($deal_info['id']));
				$deal_info['time_status'] = 2;
			}
		}		
	}
	
	//开始更新 buy_status
	
		//未成功
		if($deal_info['buy_count']<$deal_info['min_bought'])
		{
			if($deal_info['buy_status']!=0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 0,success_time = 0 where id =".intval($deal_info['id']));
				$deal_info['buy_status'] = 0;
				$deal_info['success_time'] = 0;
			}
		}
		//成功未卖光
		if($deal_info['buy_count']>=$deal_info['min_bought']&&(($deal_info['buy_count']<$deal_info['max_bought']&&$deal_info['max_bought']>=0)||$deal_info['max_bought']==-1))
		{
			if($deal_info['buy_status']!=1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 1,success_time=".NOW_TIME." where id =".intval($deal_info['id']));
				$deal_info['buy_status'] = 1;
				$deal_info['success_time'] = NOW_TIME;
			}
		}
		//卖光
		if($deal_info['buy_count']>=$deal_info['max_bought']&&$deal_info['max_bought']>=0) //库存-1表示不限
		{
			if($deal_info['buy_status']!=2)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set buy_status = 2 where id =".intval($deal_info['id']));
				$deal_info['buy_status'] = 2;
			}
		}

		//同步成功后，发相应的团购券发券
		$buy_status = $deal_info['buy_status'];
		if($buy_status > 0)
		{
			//成功后发券, 将user_id <> 0 且 is_valid = 0的发放出去
			$deal_coupons = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_coupon where user_id <> 0 and is_valid = 0 and deal_id = ".intval($deal_info['id']));
			foreach($deal_coupons as $deal_coupon)
			{
				send_deal_coupon($deal_coupon['id']);	
			}			
		}
		
		if($deal_info['time_status']!=2&&$deal_info['reopen']!=0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal set reopen = 0 where id = ".intval($deal_info['id'])." and time_status <> 2");
			$deal_info['reopen'] = 0;
		}
		$cache_goods_list[$id] = $deal_info;
		return $deal_info;
}

//发放团购券
function send_deal_coupon($deal_coupon_id)
{
	$GLOBALS['db']->query("update ".DB_PREFIX."deal_coupon set is_valid = 1 where id = ".$deal_coupon_id." and user_id <> 0 and is_delete = 0 and is_valid = 0");
	$rs = $GLOBALS['db']->affected_rows();
	if($rs)
	{
		//发邮件团购券
		send_deal_coupon_mail($deal_coupon_id);	
		//发短信团购券
		send_deal_coupon_sms($deal_coupon_id);			
	}
}

function send_user_withdraw_sms($user_id,$money)
{
	
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
		
		$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_USER_WITHDRAW_SMS'");
		
		//chenfq by add 添加支持：app,微信 推送模板
		if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1)
		{			
				
				$tmpl_content = $tmpl['content'];
	
				$GLOBALS['tmpl']->assign("user_name",$user_info['user_name']);
				$GLOBALS['tmpl']->assign("money_format",round($money,2)."元");
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['mobile'];				
				$msg_data['content'] = addslashes($msg);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['is_html'] = $tmpl['is_html'];
				
				send_msg_item_add($tmpl,$user_info,$msg_data);
		}
	
}

function send_user_withdraw_mail($user_id,$money)
{
	if(app_conf("MAIL_ON")==1)
	{
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);
		if($user_info['email'])
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_USER_WITHDRAW_MAIL'");
			$tmpl_content = $tmpl['content'];
	
			$GLOBALS['tmpl']->assign("user_name",$user_info['user_name']);
			$GLOBALS['tmpl']->assign("money_format",round($money,2)."元");
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = NOW_TIME;
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			
			if(APP_INDEX!="index")
			{
				$msg_data['id'] = $GLOBALS['db']->insert_id();
				send_msg_item($msg_data);
			}
	
		}
	}
}
function send_supplier_withdraw_sms($supplier_id,$money)
{
	if(app_conf("SUPPLIER_ORDER_NOTIFY")==1)
	{
		$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where supplier_id = ".$supplier_id." and is_main = 1");

		$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SUPPLIER_WITHDRAW_SMS'");
		
		if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1)
		{
			$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);
			
			$tmpl_content = $tmpl['content'];

			$GLOBALS['tmpl']->assign("supplier_name",$supplier_info['name']);
			$GLOBALS['tmpl']->assign("money_format",round($money,2)."元");
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $account['mobile'];
			$msg_data['send_type'] = 0;
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = NOW_TIME;
			$msg_data['is_html'] = $tmpl['is_html'];
			
			send_msg_item_add($tmpl,$account,$msg_data);
		}		
	}
}

//新订单发送短信通知商户
function send_supplier_order($supplier_id,$order_id)
{
	if(app_conf("SUPPLIER_ORDER_NOTIFY")==1)
	{
		$order_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		if($order_data)
		{

			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_SUPPLIER_ORDER'");
			
			if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1)
			{
				$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);
				$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where supplier_id = ".$supplier_id." and is_main = 1");
				
				$tmpl_content = $tmpl['content'];
				
				$GLOBALS['tmpl']->assign("supplier_name",$supplier_info['name']);
				$GLOBALS['tmpl']->assign("order_sn",$order_data['order_sn']);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $account['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['is_html'] = $tmpl['is_html'];
				
				send_msg_item_add($tmpl,$account,$msg_data);
			}
				
		}
	}
}

//发邮件团购券
function send_deal_coupon_mail($deal_coupon_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_COUPON")==1)
	{
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);			
		if($coupon_data)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_COUPON'");
			$tmpl_content = $tmpl['content'];
			$coupon_data['begin_time_format'] = $coupon_data['begin_time']==0?$GLOBALS['lang']['NO_BEGIN_TIME']:to_date($coupon_data['begin_time'],'Y-m-d');
			$coupon_data['end_time_format'] = $coupon_data['end_time']==0?$GLOBALS['lang']['NO_END_TIME']:to_date($coupon_data['end_time'],'Y-m-d');			
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
			$coupon_data['user_name'] = $user_info['user_name'];
			$coupon_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
			$coupon_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
			$coupon_data['supplier_tel']=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where supplier_id = ".$coupon_data['supplier_id']);
			$coupon_data['supplier_address']=$GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$coupon_data['supplier_id']);
				
			$deal_id = $coupon_data['deal_id'];
					if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
					{
						$deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
						if(!$coupon_data['deal_name'])
						$coupon_data['deal_name'] = $deal_info['name'];
						if(!$coupon_data['deal_sub_name'])
						$coupon_data['deal_sub_name'] = $deal_info['sub_name'];
					}	
			$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
			$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
			if($deal_type == 1&&$order_item)
			{
					$coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
					$coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
			}
			
			$GLOBALS['tmpl']->assign("coupon",$coupon_data);
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['title'] = $GLOBALS['lang']['YOU_GOT_COUPON'];
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = NOW_TIME;
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //鎻掑叆
			
			if(APP_INDEX!="index")
			{
				$msg_data['id'] = $GLOBALS['db']->insert_id();
				send_msg_item($msg_data);
			}
			
		}
	}
}

//发活动邮件
function send_event_sn_mail($submit_id)
{
	if(app_conf("MAIL_ON")==1)
	{
		$submit_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where id = ".$submit_id);
		if($submit_data)
		{
				
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$submit_data['user_id']);
			if($user_info['email']!='')
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_EVENT_SN'");
				$tmpl_content = $tmpl['content'];
				$event['begin_time_format'] = $submit_data['event_begin_time']==0?$GLOBALS['lang']['NO_BEGIN_TIME']:to_date($submit_data['event_begin_time'],'Y-m-d');
				$event['end_time_format'] = $submit_data['event_end_time']==0?$GLOBALS['lang']['NO_END_TIME']:to_date($submit_data['event_end_time'],'Y-m-d');
				$event['user_name'] = $user_info['user_name'];
				$event['sn'] = $submit_data['sn'];
				$event['name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event where id = ".$submit_data['event_id']);

				$GLOBALS['tmpl']->assign("event",$event);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}
			}
				
		}
	}
}

//发积分邮件通知
function send_score_mail($order_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("SEND_SCORE_MAIL")==1)
	{
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);			
		if($order_info)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_SCORE'");
			$tmpl_content = $tmpl['content'];
			
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);			
			$GLOBALS['tmpl']->assign("username",$user_info['user_name']);
			$GLOBALS['tmpl']->assign("order_sn",$order_info['order_sn']);
			
			if($order_info['return_total_score']>0)
			{
				$GLOBALS['tmpl']->assign("score_value","鑾峰緱".format_score(abs($order_info['return_total_score'])));
			}
			else
			{
				$GLOBALS['tmpl']->assign("score_value","娑堣垂".format_score(abs($order_info['return_total_score'])));
			}
			
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['title'] = "绉垎鍙樻洿閫氱煡";
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = NOW_TIME;
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //鎻掑叆
			
			if(APP_INDEX!="index")
			{
				$msg_data['id'] = $GLOBALS['db']->insert_id();
				send_msg_item($msg_data);
			}
			
		}
	}
}

//发短信团购券
function send_deal_coupon_sms($deal_coupon_id)
{
	if(app_conf("SMS_SEND_COUPON")==1)
	{
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);				
		if($coupon_data)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_COUPON'");
			
			$forbid_sms = intval($GLOBALS['db']->getOne("select forbid_sms from ".DB_PREFIX."deal where id = ".$coupon_data['deal_id']));
			if($forbid_sms==0 && (app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1))
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
				
									
					$tmpl_content = $tmpl['content'];
					$coupon_data['begin_time_format'] = $coupon_data['begin_time']==0?$GLOBALS['lang']['NO_BEGIN_TIME']:to_date($coupon_data['begin_time'],'Y-m-d');
					$coupon_data['end_time_format'] = $coupon_data['end_time']==0?$GLOBALS['lang']['NO_END_TIME']:to_date($coupon_data['end_time'],'Y-m-d');			
					$coupon_data['user_name'] = $user_info['user_name'];
					$coupon_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
					$coupon_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
					$coupon_data['supplier_tel']=$GLOBALS['db']->getOne("select tel from ".DB_PREFIX."supplier_location where supplier_id = ".$coupon_data['supplier_id']);
				     $coupon_data['supplier_address']=$GLOBALS['db']->getOne("select address from ".DB_PREFIX."supplier_location where supplier_id = ".$coupon_data['supplier_id']);
					$deal_id = $coupon_data['deal_id'];
					if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
					{
						$deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
						if(!$coupon_data['deal_name'])
						$coupon_data['deal_name'] = $deal_info['name'];
						if(!$coupon_data['deal_sub_name'])
						$coupon_data['deal_sub_name'] = $deal_info['sub_name'];
					}					
					$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
					$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
					if($deal_type == 1&&$order_item)
					{
						$coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
						$coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
					}
					
	
					$GLOBALS['tmpl']->assign("coupon",$coupon_data);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					$msg_data['dest'] = $user_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = NOW_TIME;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					
					send_msg_item_add($tmpl,$user_info,$msg_data);
					/*
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入	
					if(APP_INDEX!="index")
					{
						$msg_data['id'] = $GLOBALS['db']->insert_id();
						send_msg_item($msg_data);
					}*/
				
			}
		}		
	}
}


//发活动短信
function send_event_sn_sms($submit_id)
{
	
		$submit_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where id = ".$submit_id);
		if($submit_data)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_EVENT_SN'");
				
				if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1)
				{
					$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$submit_data['user_id']);
					$tmpl_content = $tmpl['content'];
					$event['begin_time_format'] = $submit_data['event_begin_time']==0?$GLOBALS['lang']['NO_BEGIN_TIME']:to_date($submit_data['event_begin_time'],'Y-m-d');
					$event['end_time_format'] = $submit_data['event_end_time']==0?$GLOBALS['lang']['NO_END_TIME']:to_date($submit_data['event_end_time'],'Y-m-d');
					$event['user_name'] = $user_info['user_name'];
					$event['sn'] = $submit_data['sn'];
					$event['name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event where id = ".$submit_data['event_id']);

					$GLOBALS['tmpl']->assign("event",$event);
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					$msg_data['dest'] = $user_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = NOW_TIME;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					
					send_msg_item_add($tmpl,$user_info,$msg_data);
					
					/*
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
					
					if(APP_INDEX!="index")
					{
						$msg_data['id'] = $GLOBALS['db']->insert_id();
						send_msg_item($msg_data);
					}
					*/
				}
			
		}
	
}

//发积分短信通知
function send_score_sms($order_id)
{
	if(app_conf("SEND_SCORE_SMS")==1)
	{
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);			
		if($order_info)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_SCORE'");
			
			if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1)
			{		
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
				$tmpl_content = $tmpl['content'];
				$GLOBALS['tmpl']->assign("username",$user_info['user_name']);
				$GLOBALS['tmpl']->assign("order_sn",$order_info['order_sn']);
				
				if($order_info['return_total_score']>0)
				{
					$GLOBALS['tmpl']->assign("score_value","获得".format_score(abs($order_info['return_total_score'])));
				}
				else
				{
					$GLOBALS['tmpl']->assign("score_value","消费".format_score(abs($order_info['return_total_score'])));
				}
				
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				
				send_msg_item_add($tmpl,$user_info,$msg_data);
				/*
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}*/
			}
			
		}
	}
}


//发团购券确认使用的短信
function send_use_coupon_sms($deal_coupon_id)
{
	if(app_conf("SMS_USE_COUPON")==1)
	{
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);				
		if($coupon_data)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_USE_COUPON'");
			if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1)
			{
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
								
				$tmpl_content = $tmpl['content'];
				$coupon_data['confirm_time_format'] = to_date($coupon_data['confirm_time'],'Y-m-d H:i:s');
				$coupon_data['user_name'] = $user_info['user_name'];
				$coupon_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
				$coupon_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
					$deal_id = $coupon_data['deal_id'];
					if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
					{
						$deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
						if(!$coupon_data['deal_name'])
						$coupon_data['deal_name'] = $deal_info['name'];
						if(!$coupon_data['deal_sub_name'])
						$coupon_data['deal_sub_name'] = $deal_info['sub_name'];
					}					
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
				$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
				if($deal_type == 1&&$order_item)
				{
					$coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
					$coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
				}
				$GLOBALS['tmpl']->assign("coupon",$coupon_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				
				send_msg_item_add($tmpl,$user_info,$msg_data);
				/*
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}
				*/		
			}
		}		
	}
}


//发团购券确认使用的邮件
function send_use_coupon_mail($deal_coupon_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_USE_COUPON")==1)
	{
		$coupon_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where id = ".$deal_coupon_id);				
		if($coupon_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$coupon_data['user_id']);
			
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USE_COUPON'");				
				$tmpl_content = $tmpl['content'];
				$coupon_data['confirm_time_format'] = to_date($coupon_data['confirm_time'],'Y-m-d H:i:s');
				$coupon_data['user_name'] = $user_info['user_name'];
				$coupon_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
				$coupon_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal_order_item where id = ".$coupon_data['order_deal_id']);
					$deal_id = $coupon_data['deal_id'];
					if(!$coupon_data['deal_name']||!$coupon_data['deal_sub_name'])
					{
						$deal_info = $GLOBALS['db']->getRow("select name,sub_name from ".DB_PREFIX."deal where id = ".$deal_id);
						if(!$coupon_data['deal_name'])
						$coupon_data['deal_name'] = $deal_info['name'];
						if(!$coupon_data['deal_sub_name'])
						$coupon_data['deal_sub_name'] = $deal_info['sub_name'];
					}					
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".intval($coupon_data['order_deal_id']));
				$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($order_item['deal_id'])));
				if($deal_type == 1&&$order_item)
				{
					$coupon_data['deal_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
					$coupon_data['deal_sub_name'].= " ".$GLOBALS['lang']['BUY_NUMBER']."(".$order_item['number'].")";
				}
				$GLOBALS['tmpl']->assign("coupon",$coupon_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = '您的团购券已确认使用';
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}		
			
		}		
	}
}


//发短信抽奖
function send_lottery_sms($lottery_id)
{
	if(app_conf("LOTTERY_SN_SMS")==1&&$lottery_id>0)
	{
		$lottery_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."lottery where id = ".$lottery_id);				
		if($lottery_data)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_LOTTERY'");
			
			if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1){
				
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$lottery_data['user_id']);
				$tmpl_content = $tmpl['content'];
				$lottery_data['user_name'] = $user_info['user_name'];
				$lottery_data['deal_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal where id = ".$lottery_data['deal_id']);
				$lottery_data['deal_sub_name'] = $GLOBALS['db']->getOne("select sub_name from ".DB_PREFIX."deal where id = ".$lottery_data['deal_id']);
					
				$GLOBALS['tmpl']->assign("lottery",$lottery_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $lottery_data['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				
				send_msg_item_add($tmpl,$user_info,$msg_data);
				
				/*
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入		
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}	
				*/	
			}
		}		
	}
}

//发注册验证邮件
function send_user_verify_mail($user_id)
{
	if(app_conf("MAIL_ON")==1)
	{
		$verify_code = rand(111111,999999);
		$GLOBALS['db']->query("update ".DB_PREFIX."user set verify = '".$verify_code."' where id = ".$user_id);
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);			
		if($user_info)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USER_VERIFY'");
			$tmpl_content=  $tmpl['content'];
			$user_info['verify_url'] = SITE_DOMAIN.url("index","user#verify",array("id"=>$user_info['id'],"code"=>$user_info['verify']));			
			$GLOBALS['tmpl']->assign("user",$user_info);
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['title'] = $GLOBALS['lang']['REGISTER_SUCCESS'];
			$msg_data['content'] = addslashes($msg);;
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = NOW_TIME;
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			if(APP_INDEX!="index")
			{
				$msg_data['id'] = $GLOBALS['db']->insert_id();
				send_msg_item($msg_data);
			}
		}
	}
}


//发密码验证邮件
function send_user_password_mail($user_id)
{	
		$verify_code = rand(111111,999999);
		$GLOBALS['db']->query("update ".DB_PREFIX."user set password_verify = '".$verify_code."' where id = ".$user_id);
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$user_id);			
		if($user_info)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_USER_PASSWORD'");
			$tmpl_content=  $tmpl['content'];
			$user_info['password_url'] = SITE_DOMAIN.url("index","user#modify_password", array("code"=>$user_info['password_verify'],"id"=>$user_info['id']));			
			$GLOBALS['tmpl']->assign("user",$user_info);
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $user_info['email'];
			$msg_data['send_type'] = 1;
			$msg_data['title'] = $GLOBALS['lang']['RESET_PASSWORD'];
			$msg_data['content'] = addslashes($msg);
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = NOW_TIME;
			$msg_data['user_id'] = $user_info['id'];
			$msg_data['is_html'] = $tmpl['is_html'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			if(APP_INDEX!="index")
			{
				$msg_data['id'] = $GLOBALS['db']->insert_id();
				send_msg_item($msg_data);
			}
		}
}


//发短信收款单
function send_payment_sms($notice_id)
{
	if(app_conf("SMS_SEND_PAYMENT")==1)
	{
		$notice_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id);				
		if($notice_data)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_PAYMENT'");
			
			if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1){

				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);
											
				$tmpl_content = $tmpl['content'];
				$notice_data['user_name'] = $user_info['user_name'];
				$notice_data['order_sn'] = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);			
				$notice_data['pay_time_format'] = to_date($notice_data['pay_time']);
				$notice_data['money_format'] = round($notice_data['money'],2)."元";
				$GLOBALS['tmpl']->assign("payment_notice",$notice_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				if($user_info['mobile']!='')
				{
					$msg_data['dest'] = $user_info['mobile'];
				}
				else
				{
					$msg_data['dest'] = $order_info['mobile'];
				}
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				
				send_msg_item_add($tmpl,$user_info,$msg_data);
				
				/*
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}
				*/
				
			}
		}		
	}
}

//发邮件收款单
function send_payment_mail($notice_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_PAYMENT")==1)
	{
		$notice_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$notice_id);				
		if($notice_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
			if($user_info['email']!='')
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_PAYMENT'");				
				$tmpl_content = $tmpl['content'];
				$notice_data['user_name'] = $user_info['user_name'];
				$notice_data['order_sn'] = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$notice_data['order_id']);			
				$notice_data['pay_time_format'] = to_date($notice_data['pay_time']);
				$notice_data['money_format'] = round($notice_data['money'],2)."元";
				$GLOBALS['tmpl']->assign("payment_notice",$notice_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = $GLOBALS['lang']['PAYMENT_NOTICE'];
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}
			}
		}		
	}
}



//发邮件发货单
function send_delivery_mail($notice_sn,$deal_names = '',$order_id)
{
	if(app_conf("MAIL_ON")==1&&app_conf("MAIL_SEND_DELIVERY")==1)
	{
		$notice_data = $GLOBALS['db']->getRow("select dn.* from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."deal_order_item as doi on dn.order_item_id = doi.id where dn.notice_sn = '".$notice_sn."' and doi.order_id = ".$order_id);				
		if($notice_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
			if($user_info['email']!='')
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_DELIVERY'");				
				$tmpl_content = $tmpl['content'];
				$notice_data['user_name'] = $user_info['user_name'];
				$notice_data['order_sn'] = $GLOBALS['db']->getOne("select do.order_sn from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.id = ".$notice_data['order_item_id']);			
				$notice_data['delivery_time_format'] = to_date($notice_data['delivery_time']);
				$notice_data['deal_names'] = $deal_names;
				$GLOBALS['tmpl']->assign("delivery_notice",$notice_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = $GLOBALS['lang']['DELIVERY_NOTICE'];
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}
			}
		}		
	}
}

//发短信发货单
function send_delivery_sms($notice_sn,$deal_names = '',$order_id)
{
	if(app_conf("SMS_SEND_DELIVERY")==1)
	{
		$notice_data = $GLOBALS['db']->getRow("select dn.* from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."deal_order_item as doi on dn.order_item_id = doi.id where dn.notice_sn = '".$notice_sn."' and doi.order_id = ".$order_id);						
		if($notice_data)
		{
			$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_DELIVERY'");
			if(app_conf("SMS_ON") == 1 || $tmpl['is_allow_wx'] == 1 || $tmpl['is_allow_app'] == 1)
			{
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
				$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$notice_data['user_id']);
				
				$tmpl_content = $tmpl['content'];
				$notice_data['user_name'] = $user_info['user_name'];
				$notice_data['order_sn'] = $GLOBALS['db']->getOne("select do.order_sn from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.id = ".$notice_data['order_item_id']);			
				$notice_data['delivery_time_format'] = to_date($notice_data['delivery_time']);
				$notice_data['deal_names'] = $deal_names;
				$GLOBALS['tmpl']->assign("delivery_notice",$notice_data);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				if($user_info['mobile']!='')
				{
					$msg_data['dest'] = $user_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = NOW_TIME;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					
					send_msg_item_add($tmpl,$user_info,$msg_data);
					/*
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
					if(APP_INDEX!="index")
					{
						$msg_data['id'] = $GLOBALS['db']->insert_id();
						send_msg_item($msg_data);
					}
					*/
				}
				
				if($order_info['mobile']!=''&&$order_info['mobile']!=$user_info['mobile'])
				{
					$msg_data['dest'] = $order_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = NOW_TIME;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					send_msg_item_add($tmpl,$user_info,$msg_data);
					/*
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
					if(APP_INDEX!="index")
					{
						$msg_data['id'] = $GLOBALS['db']->insert_id();
						send_msg_item($msg_data);
					}
					*/
				}
			}
		}		
	}
}


//发短信验证码
function send_verify_sms($mobile,$code)
{
	if(app_conf("SMS_ON")==1)
	{
		
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_VERIFY_CODE'");				
				$tmpl_content = $tmpl['content'];
				$verify['mobile'] = $mobile;
				$verify['code'] = $code;
				$GLOBALS['tmpl']->assign("verify",$verify);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $mobile;
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = 0;
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入	

				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();	
					send_msg_item($msg_data);
				}	
	}
}


//发邮件退订验证
function send_unsubscribe_mail($email)
{
	if(app_conf("MAIL_ON")==1)
	{
		if($email)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."mail_list set code = '".rand(1111,9999)."' where mail_address='".$email."' and code = ''");
			$email_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mail_list where mail_address = '".$email."' and code <> ''");
			if($email_item)
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_UNSUBSCRIBE'");				
				$tmpl_content = $tmpl['content'];
				$mail = $email_item;
				$mail['url'] = SITE_DOMAIN.url("index","subscribe#dounsubscribe", array("code"=>base64_encode($mail['code']."|".$mail['mail_address'])));
				$GLOBALS['tmpl']->assign("mail",$mail);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $mail['mail_address'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = $GLOBALS['lang']['MAIL_UNSUBSCRIBE'];
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = 0;
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
		}		
	}
}

function get_deal_cate_name($cate_id)
{
	return $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id =".$cate_id);
}
	
function get_deal_city_name($city_id)
{
	return $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_city where id =".$city_id);
}

function format_price($price)
{
	if (defined("APP_INDEX")&&APP_INDEX == "app"){
		if($price >= 0){
			return "¥".(round($price,2));
		}else{
			return "-¥".(round(abs($price),2));
		}
	}else{
		if($price >= 0){
			return app_conf("CURRENCY_UNIT")."".(round($price,2));
		}else{
			return "-".app_conf("CURRENCY_UNIT")."".(round(abs($price),2));
		}
	}
}
function format_score($score)
{
	return intval($score)."".app_conf("SCORE_UNIT");	
}

//utf8 字符串截取
function msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true)
{
	if(function_exists("mb_substr"))
    {
        $slice =  mb_substr($str, $start, $length, $charset);
        if($suffix&$slice!=$str) return $slice."…";
    	return $slice;
    }
    elseif(function_exists('iconv_substr')) {
        return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix&&$slice!=$str) return $slice."…";
    return $slice;
}


//字符编码转换
if(!function_exists("iconv"))
{	
	function iconv($in_charset,$out_charset,$str)
	{
		require 'libs/iconv.php';
		$chinese = new Chinese();
		return $chinese->Convert($in_charset,$out_charset,$str);
	}
}

//JSON兼容
if(!function_exists("json_encode"))
{	
	function json_encode($data)
	{
		require_once 'libs/json.php';
		$JSON = new JSON();
		return $JSON->encode($data);
	}
}
if(!function_exists("json_decode"))
{	
	function json_decode($data)
	{
		require_once 'libs/json.php';
		$JSON = new JSON();
		return $JSON->decode($data,1);
	}
}

//邮件格式验证的函数
function check_email($email)
{
	if(!empty($email) && !preg_match("/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/",$email))
	{
		return false;
	}
	else
	return true;
}

//验证手机号码
function check_mobile($mobile)
{
	if(!empty($mobile) && !preg_match("/^(1[34578]\d{9})$/",$mobile))
	{
		return false;
	}
	else
	return true;
}

/**
 * 验证用户名格式
 * @param unknown_type $username
 */
function check_username($username)
{
	if(strlen($username)<4)
	{
		return false;
	}
	if(preg_match("/^(1[3458]\d{9})$/",$username))
	{
		return false;
	}
	if(preg_match("/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/",$username))
	{
		return false;
	}
	if(preg_match("/^游客_\d+$/",$username))
	{
		return false;
	}
	return true;
}

/**
 * 页面跳转
 */
function app_redirect($url,$time=0,$msg='')
{
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);    
    if (!headers_sent()) {
        // redirect
        if(0===$time&&$msg=="") {
        	if(substr($url,0,1)=="/")
        	{        		
        		if(defined("SITE_DOMAIN"))
        			header("Location:".SITE_DOMAIN.$url);
        		else
        			header("Location:".$url);
        	}
        	else
        	{
        		header("Location:".$url);
        	}
            
        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str   .=   $msg;
        exit($str);
    }
}



/**
 * 验证访问IP的有效性
 * @param ip地址 $ip_str
 * @param 访问页面 $module
 * @param 时间间隔 $time_span
 * @param 数据ID $id
 */
function check_ipop_limit($ip_str,$module,$time_span=0,$id=0)
{
		$op = es_session::get($module."_".$id."_ip");
    	if(empty($op))
    	{
    		$check['ip']	=	 CLIENT_IP;
    		$check['time']	=	NOW_TIME;
    		es_session::set($module."_".$id."_ip",$check);    		
    		return true;  //不存在session时验证通过
    	}
    	else 
    	{   
    		$check['ip']	=	 CLIENT_IP;
    		$check['time']	=	NOW_TIME;    
    		$origin	=	es_session::get($module."_".$id."_ip");
    		
    		if($check['ip']==$origin['ip'])
    		{
    			if($check['time'] - $origin['time'] < $time_span)
    			{
    				return false;
    			}
    			else 
    			{
    				es_session::set($module."_".$id."_ip",$check);
    				return true;  //不存在session时验证通过    				
    			}
    		}
    		else 
    		{
    			es_session::set($module."_".$id."_ip",$check);
    			return true;  //不存在session时验证通过
    		}
    	}
    }

//发放返利的函数
function pay_referrals($id)
{
	$referrals_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."referrals where id = ".$id);
	if($referrals_data)
	{
		$sql = "update ".DB_PREFIX."referrals set pay_time = ".NOW_TIME." where id = ".$id." and pay_time = 0 ";
		$GLOBALS['db']->query($sql);
		$rs = $GLOBALS['db']->affected_rows();
		if($rs)
		{
			//开始发放返利
			require_once APP_ROOT_PATH."system/model/user.php";
			$order_sn = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$referrals_data['order_id']);
			$user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$referrals_data['user_id']);
			$rel_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$referrals_data['rel_user_id']);
			$referral_amount = $referrals_data['money']>0?format_price($referrals_data['money']):format_score($referrals_data['score']);
			$msg = sprintf($GLOBALS['lang']['REFERRALS_LOG'],$order_sn,$rel_user_name,$referral_amount);
			modify_account(array('money'=>$referrals_data['money'],'score'=>$referrals_data['score']),$referrals_data['user_id'],$msg);	
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}
//扣除返利的函数
function return_referrals($id)
{
	$referrals_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."referrals where order_id = ".$id);
	if($referrals_data)
	{
			$sql = "update ".DB_PREFIX."referrals set money = 0,score=0 where order_id = ".$id;
			$GLOBALS['db']->query($sql);
			$referrals_data['money'] = -($referrals_data['money']);
			$referrals_data['score'] = -($referrals_data['score']);
			//开始扣除返利
			require_once APP_ROOT_PATH."system/model/user.php";
			$order_sn = $GLOBALS['db']->getOne("select order_sn from ".DB_PREFIX."deal_order where id = ".$referrals_data['order_id']);
			$user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$referrals_data['user_id']);
			$rel_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$referrals_data['rel_user_id']);
			$referral_amount = $referrals_data['money']<0?format_price($referrals_data['money']):format_score($referrals_data['score']);
			$msg = sprintf($GLOBALS['lang']['REFERRALS_RETURN_LOG'],$order_sn,$rel_user_name,$referral_amount);
			modify_account(array('money'=>$referrals_data['money'],'score'=>$referrals_data['score']),$referrals_data['user_id'],$msg);	
			return true;

	}
	else
	{
		return false;
	}
}
//发货的通用函数
/**
 * 
 * @param $order_id 订单ID
 * @param $order_deal_id  发货的订单商品ID
 * @param $delivery_sn  发货号
 */
function make_delivery_notice($order_id,$order_deal_id,$delivery_sn,$memo='',$express_id = 0,$location_id=0)
{
	//先删除原先相关的发货单号
	$GLOBALS['db']->query("delete from ".DB_PREFIX."delivery_notice where order_item_id = ".$order_deal_id);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	$delivery_notice['notice_sn'] = $delivery_sn;
	$delivery_notice['delivery_time'] = NOW_TIME;
	$delivery_notice['order_item_id'] = $order_deal_id;
	$delivery_notice['delivery_supplier_id'] = $GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."deal_order_item where id = ".$order_deal_id);
	$delivery_notice['order_id'] = $order_info['id'];
	$delivery_notice['user_id'] = $order_info['user_id'];	
	$delivery_notice['deal_id'] = $GLOBALS['db']->getOne("select deal_id from ".DB_PREFIX."deal_order_item where id = ".$order_deal_id);
	$delivery_notice['location_id'] = $location_id;
	$adm_session = es_session::get(md5(app_conf("AUTH_KEY")));
	$adm_id = intval($adm_session['adm_id']);
	$delivery_notice['admin_id'] = $adm_id;	
	$delivery_notice['memo'] = $memo;
	$delivery_notice['express_id'] = $express_id;
	$GLOBALS['db']->autoExecute(DB_PREFIX."delivery_notice",$delivery_notice,'INSERT','','SILENT');
	return $GLOBALS['db']->insert_id();
}



function trim_bom($contents)
{
	$charset[1] = substr($contents, 0, 1);
	$charset[2] = substr($contents, 1, 1);
	$charset[3] = substr($contents, 2, 1);
	if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191)
	{
		$contents = substr($contents, 3);
		return $contents;
	}
	else
	{
		return $contents;
	}
}

function gzip_out($content)
{
	if($GLOBALS['refresh_page']&&!IS_DEBUG)
	{
		echo "<script>location.reload();</script>";
		exit;
	}
	
	if($distribution_cfg["CACHE_TYPE"]!="File")
	{
		if(preg_match_all("/href=\"([^\"]+)\"/i", $content, $matches))
		{
			foreach($matches[1] as $k=>$v)
			{
				$content = str_replace($v, trim_bom($v), $content);
			}
		}
	}
	
	header("Content-type: text/html; charset=utf-8");
    header("Cache-control: private");  //支持页面回跳
	$gzip = app_conf("GZIP_ON");
	if( intval($gzip)==1 )
	{
		if(!headers_sent()&&extension_loaded("zlib")&&preg_match("/gzip/i",$_SERVER["HTTP_ACCEPT_ENCODING"]))
		{
			$content = gzencode($content,9);	
			header("Content-Encoding: gzip");
			header("Content-Length: ".strlen($content));
			echo $content;
		}
		else
		echo $content;
	}else{
		echo $content;
	}
	
}

function order_log($log_info,$order_id)
{
	$data['id'] = 0;
	$data['log_info'] = $log_info;
	$data['log_time'] = NOW_TIME;
	$data['order_id'] = $order_id;
	$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_log", $data);
}


/**
	 * 保存图片
	 * @param array $upd_file  即上传的$_FILES数组
	 * @param array $key $_FILES 中的键名 为空则保存 $_FILES 中的所有图片
	 * @param string $dir 保存到的目录
	 * @param array $whs
	 	可生成多个缩略图
		数组 参数1 为宽度，
			 参数2为高度，
			 参数3为处理方式:0(缩放,默认)，1(剪裁)，
			 参数4为是否水印 默认为 0(不生成水印)
	 	array(
			'thumb1'=>array(300,300,0,0),
			'thumb2'=>array(100,100,0,0),
			'origin'=>array(0,0,0,0),  宽与高为0为直接上传
			...
		)，
	 * @param array $is_water 原图是否水印
	 * @return array
	 	array(
			'key'=>array(
				'name'=>图片名称，
				'url'=>原图web路径，
				'path'=>原图物理路径，
				有略图时
				'thumb'=>array(
					'thumb1'=>array('url'=>web路径,'path'=>物理路径),
					'thumb2'=>array('url'=>web路径,'path'=>物理路径),
					...
				)
			)
			....
		)
	 */
//$img = save_image_upload($_FILES,'avatar','temp',array('avatar'=>array(300,300,1,1)),1);
function save_image_upload($upd_file, $key='',$dir='temp', $whs=array(),$is_water=false,$need_return = false)
{
		require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
		$image = new es_imagecls();
		$image->max_size = intval(app_conf("MAX_IMAGE_SIZE"));
		
		$list = array();

		if(empty($key))
		{
			foreach($upd_file as $fkey=>$file)
			{
				$list[$fkey] = false;
				$image->init($file,$dir);
				if($image->save())
				{
					$list[$fkey] = array();
					$list[$fkey]['url'] = $image->file['target'];
					$list[$fkey]['path'] = $image->file['local_target'];
					$list[$fkey]['name'] = $image->file['prefix'];
				}
				else
				{
					if($image->error_code==-105)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'上传的图片太大');
						}
						else
						echo "上传的图片太大";
					}
					elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'非法图像');
						}
						else
						echo "非法图像";
					}
					exit;
				}
			}
		}
		else
		{
			$list[$key] = false;
			$image->init($upd_file[$key],$dir);
			if($image->save())
			{
				$list[$key] = array();
				$list[$key]['url'] = $image->file['target'];
				$list[$key]['path'] = $image->file['local_target'];
				$list[$key]['name'] = $image->file['prefix'];
			}
			else
				{
					if($image->error_code==-105)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'上传的图片太大');
						}
						else
						echo "上传的图片太大";
					}
					elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101)
					{
						if($need_return)
						{
							return array('error'=>1,'message'=>'非法图像');
						}
						else
						echo "非法图像";
					}
					exit;
				}
		}

		$water_image = APP_ROOT_PATH.app_conf("WATER_MARK");
		$alpha = app_conf("WATER_ALPHA");
		$place = app_conf("WATER_POSITION");
		
		foreach($list as $lkey=>$item)
		{
				//循环生成规格图
				foreach($whs as $tkey=>$wh)
				{
					$list[$lkey]['thumb'][$tkey]['url'] = false;
					$list[$lkey]['thumb'][$tkey]['path'] = false;
					if($wh[0] > 0 || $wh[1] > 0)  //有宽高度
					{
						$thumb_type = isset($wh[2]) ? intval($wh[2]) : 0;  //剪裁还是缩放， 0缩放 1剪裁
						if($thumb = $image->thumb($item['path'],$wh[0],$wh[1],$thumb_type))
						{
							$list[$lkey]['thumb'][$tkey]['url'] = $thumb['url'];
							$list[$lkey]['thumb'][$tkey]['path'] = $thumb['path'];
							if(isset($wh[3]) && intval($wh[3]) > 0)//需要水印
							{
								$paths = pathinfo($list[$lkey]['thumb'][$tkey]['path']);
								$path = $paths['dirname'];
				        		$path = $path."/origin/";
				        		if (!is_dir($path)) { 
						             @mkdir($path);
						             @chmod($path, 0777);
					   			}   	    
				        		$filename = $paths['basename'];
								@file_put_contents($path.$filename,@file_get_contents($list[$lkey]['thumb'][$tkey]['path']));      
								$image->water($list[$lkey]['thumb'][$tkey]['path'],$water_image,$alpha, $place);
							}
						}
					}
				}
			if($is_water)
			{
				$paths = pathinfo($item['path']);
				$path = $paths['dirname'];
        		$path = $path."/origin/";
        		if (!is_dir($path)) { 
		             @mkdir($path);
		             @chmod($path, 0777);
	   			}   	    
        		$filename = $paths['basename'];
				@file_put_contents($path.$filename,@file_get_contents($item['path']));        		
				$image->water($item['path'],$water_image,$alpha, $place);
			}
		}			
		return $list;
}

function empty_tag($string)
{	
	$string = preg_replace(array("/\[img\]\d+\[\/img\]/","/\[[^\]]+\]/"),array("",""),$string);
	if(strim($string)=='')
	return $GLOBALS['lang']['ONLY_IMG'];
	else 
	return $string;
	//$string = str_replace(array("[img]","[/img]"),array("",""),$string);
}

//验证是否有非法字汇，未完成
function valid_str($string)
{
	$string = msubstr($string,0,5000);
	if(app_conf("FILTER_WORD")!='')
	$string = preg_replace("/".app_conf("FILTER_WORD")."/","*",$string);
	return $string;
}


/**
 * utf8字符转Unicode字符
 * @param string $char 要转换的单字符
 * @return void
 */
function utf8_to_unicode($char)
{
	switch(strlen($char))
	{
		case 1:
			return ord($char);
		case 2:
			$n = (ord($char[0]) & 0x3f) << 6;
			$n += ord($char[1]) & 0x3f;
			return $n;
		case 3:
			$n = (ord($char[0]) & 0x1f) << 12;
			$n += (ord($char[1]) & 0x3f) << 6;
			$n += ord($char[2]) & 0x3f;
			return $n;
		case 4:
			$n = (ord($char[0]) & 0x0f) << 18;
			$n += (ord($char[1]) & 0x3f) << 12;
			$n += (ord($char[2]) & 0x3f) << 6;
			$n += ord($char[3]) & 0x3f;
			return $n;
	}
}

/**
 * utf8字符串分隔为unicode字符串
 * @param string $str 要转换的字符串
 * @param string $depart 分隔,默认为空格为单字
 * @return string
 */
function str_to_unicode_word($str,$depart=' ')
{
	$arr = array();
	$str_len = mb_strlen($str,'utf-8');
	for($i = 0;$i < $str_len;$i++)
	{
		$s = mb_substr($str,$i,1,'utf-8');
		if($s != ' ' && $s != '　')
		{
			$arr[] = 'ux'.utf8_to_unicode($s);
		}
	}
	return implode($depart,$arr);
}


/**
 * utf8字符串分隔为unicode字符串
 * @param string $str 要转换的字符串
 * @return string
 */
function str_to_unicode_string($str)
{
	$string = str_to_unicode_word($str,'');
	return $string;
}

//分词
function div_str($str)
{
	require_once APP_ROOT_PATH."system/libs/words.php";
	$words = words::segment($str);
	$words[] = $str;	
	return $words;
}

/**
 * 
 * @param $tag  //要插入的关键词
 * @param $table  //表名
 * @param $id  //数据ID
 * @param $field		// tag_match/name_match/cate_match/locate_match
 */
function insert_match_item($tag,$table,$id,$field)
{
	if($tag=='')
	return;
	
	$unicode_tag = str_to_unicode_string($tag);
	$sql = "select count(*) from ".DB_PREFIX.$table." where match(".$field.") against ('".$unicode_tag."' IN BOOLEAN MODE) and id = ".$id;
	$rs = $GLOBALS['db']->getOne($sql);
	if(intval($rs) == 0)
	{
		$match_row = $GLOBALS['db']->getRow("select * from ".DB_PREFIX.$table." where id = ".$id);
		if($match_row[$field]=="")
		{
				$match_row[$field] = $unicode_tag;
				$match_row[$field."_row"] = $tag;
		}
		else
		{
				$match_row[$field] = $match_row[$field].",".$unicode_tag;
				$match_row[$field."_row"] = $match_row[$field."_row"].",".$tag;
		}
		$GLOBALS['db']->autoExecute(DB_PREFIX.$table, $match_row, $mode = 'UPDATE', "id=".$id, $querymode = 'SILENT');	
		
	}	
}

function get_all_parent_id($id,$table,&$arr = array())
{
	if(intval($id)>0)
	{
		$arr[] = $id;
		$pid = $GLOBALS['db']->getOne("select pid from ".$table." where id = ".$id);
		if($pid>0)
		{
			get_all_parent_id($pid,$table,$arr);
		}
	}
}

/**
 * 
 * @param $title_name 标题名称
 * @param $type  类型 0:话题 1:活动
 */
function syn_topic_title($title_name,$type=0)
{
	$data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_title where name = '".$title_name."'");
	if(!$data)
	{
		$data = array("name"=>$title_name);
		$GLOBALS['db']->autoExecute(DB_PREFIX."topic_title", $data, $mode = 'INSERT', "", $querymode = 'SILENT');	
	}
	$topic_group = intval($type)==0?"share":"event";
	$count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where title like '%".$title_name."%' and topic_group = '".$topic_group."'"));	
	$GLOBALS['db']->query("update ".DB_PREFIX."topic_title set count = ".$count);
}

function syn_deal_match($deal_id)
{
	$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id);
	if($deal)
	{
		$deal['name_match'] = "";
		$deal['name_match_row'] = "";
		$deal['deal_cate_match'] = "";
		$deal['deal_cate_match_row'] = "";
		$deal['shop_cate_match'] = "";
		$deal['shop_cate_match_row'] = "";
		$deal['tag_match'] = "";
		$deal['tag_match_row'] = "";
		$deal['locate_match'] = "";
		$deal['locate_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal", $deal, $mode = 'UPDATE', "id=".$deal_id, $querymode = 'SILENT');	
		
		//同步商品的全文索引标签
		//获取筛选属性
		$deal_filters = $GLOBALS['db']->getAll("select filter from ".DB_PREFIX."deal_filter where deal_id = ".$deal_id);		
		foreach($deal_filters as $row)
		{
			$tags = preg_split("/[ ,]/i",$row['filter']);
			foreach($tags as $tag)
			{
				if(trim($tag)!="")
				insert_match_item($tag,"deal",$deal_id,"tag_match");
			}
		}
		
		//属性
		$deal_attrs = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_attr where deal_id = ".$deal_id);
		foreach($deal_attrs as $row)
		{
			$tag = trim($row['name']);
			if(trim($tag)!="")
			insert_match_item($tag,"deal",$deal_id,"tag_match");

		}
		
		//同步名称
		$name_arr = div_str(trim($deal['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"deal",$deal_id,"name_match");
		}
		$brand_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."brand where id = ".$deal['brand_id']);
		insert_match_item($brand_name,"deal",$deal_id,"name_match");
		
		//分类类别
		$deal_cate =array();		
		get_all_parent_id(intval($deal['cate_id']),DB_PREFIX."deal_cate",$deal_cate);
		if(count($deal_cate)>0)
		{
			$deal_cates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_cate where id in (".implode(",",$deal_cate).")");
			foreach ($deal_cates as $row)
			{
				insert_match_item(trim($row['name']),"deal",$deal_id,"deal_cate_match");
			}
		}
		$goods_cate =array();
		get_all_parent_id(intval($deal['shop_cate_id']),DB_PREFIX."shop_cate",$goods_cate);
		if(count($goods_cate)>0)
		{
			$goods_cates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."shop_cate where id in (".implode(",",$goods_cate).")");
			foreach ($goods_cates as $row)
			{
				insert_match_item(trim($row['name']),"deal",$deal_id,"shop_cate_match");
			}
		}
		//获取所有子类
		$sub_cate = $GLOBALS['db']->getAll("select t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_deal_link as l on l.deal_cate_type_id = t.id where l.deal_id = ".$deal['id']);
		foreach ($sub_cate as $row)
		{
			insert_match_item(trim($row['name']),"deal",$deal_id,"deal_cate_match");
		}
		
		//地址
		$deal_city_arr = array();
		get_all_parent_id($deal['city_id'],DB_PREFIX."deal_city",$deal_city_arr);
		if(count($deal_city_arr)>0)
		{
			$deal_citys_arr = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_city where id in (".implode(",",$deal_city_arr).")");
			foreach ($deal_citys_arr as $row)
			{
				insert_match_item(trim($row['name']),"deal",$deal_id,"locate_match");
			}
		}
		$supplier_locations = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."supplier_location as a left join ".DB_PREFIX."deal_location_link as b on a.id = b.location_id where a.supplier_id = ".intval($deal['supplier_id'])." and b.deal_id = ".$deal['id']);
		foreach($supplier_locations as $locate)
		{		
			$address_arr = div_str(trim($locate['address']));
			foreach($address_arr as $address_item)
			{
				insert_match_item($address_item,"deal",$deal_id,"locate_match");
			}
			
			$areas = $GLOBALS['db']->getAll("select a.name,a.pid from ".DB_PREFIX."area as a left join ".DB_PREFIX."supplier_location_area_link as l on l.area_id = a.id where l.location_id = ".$locate['id']);
			foreach($areas as $area)
			{
				if($area['pid']>0)
				{
					$parent_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area['pid']);
					insert_match_item(trim($parent_name),"deal",$deal_id,"locate_match");
				}
				insert_match_item($area['name'],"deal",$deal_id,"locate_match");
			}
			
			//获取默认门店的坐标
			if($locate['is_main']==1)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."deal set xpoint = '".$locate['xpoint']."',ypoint = '".$locate['ypoint']."' where id = ".$deal_id);
			}
		}
	}	
}
/**
 * 同步库存索引的key
 */
function syn_attr_stock_key($id)
{
    $attr_stock_list =$GLOBALS['db']->getAll("select * from ".DB_PREFIX."attr_stock where deal_id = ".$id);
    foreach($attr_stock_list as $row)
    {
        $attr_ids = array();
        $attr_cfg = unserialize($row['attr_cfg']);
        foreach($attr_cfg as $goods_type_attr_id=>$deal_attr_name)
        {
            $attr_ids[] = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_attr where deal_id = ".$id." and goods_type_attr_id = ".$goods_type_attr_id." and name='".$deal_attr_name."'");
        }
        sort($attr_ids);
        $attr_ids = implode($attr_ids, "_");
        $GLOBALS['db']->query("update ".DB_PREFIX."attr_stock set attr_key = '".$attr_ids."' where id =".$row['id']);
    }
}

function syn_event_match($event_id)
{
	$event = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$event_id);
	if($event)
	{
		$event['name_match'] = "";
		$event['name_match_row'] = "";
		$event['cate_match'] = "";
		$event['cate_match_row'] = "";
		$event['locate_match'] = "";
		$event['locate_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."event", $event, $mode = 'UPDATE', "id=".$event_id, $querymode = 'SILENT');	
				
		//同步名称
		$name_arr = div_str(trim($event['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"event",$event_id,"name_match");
		}
		$brief_arr = div_str(trim($event['brief'])); 
		foreach($brief_arr as $name_item)
		{
			insert_match_item($name_item,"event",$event_id,"name_match");
		}
		
		//分类类别
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".$event['cate_id']);
		insert_match_item(trim($cate_name),"event",$event_id,"cate_match");

		
		//地址
		$deal_city_arr = array();
		get_all_parent_id($event['city_id'],DB_PREFIX."deal_city",$deal_city_arr);
		if(count($deal_city_arr)>0)
		{
			$deal_citys_arr = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_city where id in (".implode(",",$deal_city_arr).")");
			foreach ($deal_citys_arr as $row)
			{
				insert_match_item(trim($row['name']),"event",$event_id,"locate_match");
			}
		}
		
		$address_arr = div_str(trim($event['address']));
		foreach($address_arr as $address_item)
		{
				insert_match_item($address_item,"event",$event_id,"locate_match");
		}
		
		$area_list = $GLOBALS['db']->getAll("select a.name,a.pid from ".DB_PREFIX."area as a left join ".DB_PREFIX."event_area_link as l on l.area_id = a.id where l.event_id = ".$event_id);
		
		foreach($area_list as $area)
		{
			if($area['pid']>0)
			{
				$parent_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area['pid']);
				insert_match_item(trim($parent_name),"event",$event_id,"locate_match");
			}
			insert_match_item(trim($area['name']),"event",$event_id,"locate_match");
		}
	}	
}

function syn_supplier_location_match($location_id)
{
	$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
	if($location)
	{
		$location['name_match'] = "";
		$location['name_match_row'] = "";
		$location['deal_cate_match'] = "";
		$location['deal_cate_match_row'] = "";
		$location['locate_match'] = "";
		$location['locate_match_row'] = "";
		$location['tags_match'] = "";
		$location['tags_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location", $location, $mode = 'UPDATE', "id=".$location_id, $querymode = 'SILENT');	
		
		
		//同步名称
		$name_arr = div_str(trim($location['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"supplier_location",$location_id,"name_match");
		}
		
		$brands = $GLOBALS['db']->getAll("select b.name from ".DB_PREFIX."brand as b left join ".DB_PREFIX."supplier_location_brand_link as l on l.brand_id = b.id where l.location_id = ".$location_id);
		foreach($brands as $brand)
		{
			insert_match_item($brand['name'],"supplier_location",$location_id,"name_match");
		}		
		
		//分类类别
		$deal_cate =array();		
		get_all_parent_id(intval($location['deal_cate_id']),DB_PREFIX."deal_cate",$deal_cate);
		if(count($deal_cate)>0)
		{
			$deal_cates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_cate where id in (".implode(",",$deal_cate).")");
			foreach ($deal_cates as $row)
			{
				insert_match_item(trim($row['name']),"supplier_location",$location_id,"deal_cate_match");
			}
		}
		//获取所有子类
		$sub_cate = $GLOBALS['db']->getAll("select t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_location_link as l on l.deal_cate_type_id = t.id where l.location_id = ".$location['id']);
		foreach ($sub_cate as $row)
		{
			insert_match_item(trim($row['name']),"supplier_location",$location_id,"deal_cate_match");
		}
		
		//地址
		$address_arr = div_str(trim($location['address'])); 
		foreach($address_arr as $add)
		{
			insert_match_item($add,"supplier_location",$location_id,"locate_match");
		}
		
		//标签
		$tags_arr = explode(" ",$location["tags"]);
		foreach($tags_arr as $tgs){
			insert_match_item(trim($tgs),"supplier_location",$location_id,"tags_match");
		}
		
		$tags_all = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_tag where supplier_location_id = ".$location_id);
		foreach($tags_all as $kk=>$vv)
		{
			insert_match_item(trim($vv['tag_name']),"supplier_location",$location_id,"tags_match");
		}
		
		$area_list = $GLOBALS['db']->getAll("select a.name,a.pid from ".DB_PREFIX."area as a left join ".DB_PREFIX."supplier_location_area_link as l on l.area_id = a.id where l.location_id = ".$location_id);
		
		foreach($area_list as $area)
		{
			if($area['pid']>0)
			{
				$parent_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area['pid']);
				insert_match_item(trim($parent_name),"supplier_location",$location_id,"locate_match");
			}
			insert_match_item(trim($area['name']),"supplier_location",$location_id,"locate_match");
		}
	}	
}

function syn_supplier_match($supplier_id)
{
	$supplier = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);
	if($supplier)
	{
		$supplier['name_match'] = "";
		$supplier['name_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier", $supplier, $mode = 'UPDATE', "id=".$supplier_id, $querymode = 'SILENT');	
		
		
		//同步名称
		$name_arr = div_str(trim($supplier['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"supplier",$supplier_id,"name_match");
		}
		
	}
}




function syn_topic_match($topic_id)
{
	$topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic_id);
	if(preg_match_all("/@([^\f\n\r\t\v: ]+)/i",$topic['content'],$name_matches))
	{
		$name_matches[1] = array_unique($name_matches[1]);
		foreach($name_matches[1] as $match_item)
		{
			$uinfo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_name = '".$match_item."' and is_effect = 1 and is_delete = 0");			
			if($uinfo)
			{
				insert_match_item($match_item,"topic",$topic_id,"user_name_match");		
			}
			
		}
	}
	$tags = explode(" ",$topic['tags']);
	foreach($tags as $tag)
	{
		insert_match_item(trim($tag),"topic",$topic_id,"keyword_match");
		syn_topic_cate(trim($tag),$topic_id);
	}
	
	require_once APP_ROOT_PATH."system/libs/words.php";
	$segments = words::segment($topic['content']);
	foreach($segments as $segment)
	{
		insert_match_item($segment,"topic",$topic_id,"keyword_match");
		syn_topic_cate($segment,$topic_id);
	}
	$segments = div_str($topic['title']);
	foreach($segments as $segment)
	{
		insert_match_item($segment,"topic",$topic_id,"keyword_match");
	}
	
	$cate_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic_tag_cate as t left join ".DB_PREFIX."topic_cate_link as l on l.cate_id = t.id where l.topic_id = ".$topic_id);
	foreach($cate_list as $k=>$v)
	{
		insert_match_item($v['name'],"topic",$topic_id,"cate_match");
	}
	
	$image_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where topic_id = ".$topic_id);
	$has_image = intval($image_count)>0?1:0;
	$GLOBALS['db']->query("update ".DB_PREFIX."topic set has_image = ".$has_image." where id = ".$topic_id);
	
}

function syn_topic_cate($tag,$topic_id)
{
	$tag_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."topic_tag where name = '".$tag."'");
	
	if($tag_id>0)
	{
		$cate_ids = $GLOBALS['db']->getAll("select cate_id from ".DB_PREFIX."topic_tag_cate_link where tag_id = ".$tag_id);
		foreach($cate_ids as $row)
		{
			if($row['cate_id']>0)
			{
				$link_data = array();
				$link_data['topic_id'] = $topic_id;
				$link_data['cate_id'] = $row['cate_id'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."topic_cate_link",$link_data,"INSERT","","SILENT");
			}
		}
	}
}

function syn_youhui_match($youhui_id)
{
	$youhui = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$youhui_id);
	if($youhui)
	{
		$youhui['name_match'] = "";
		$youhui['name_match_row'] = "";
		$youhui['deal_cate_match'] = "";
		$youhui['deal_cate_match_row'] = "";
		$youhui['locate_match'] = "";
		$youhui['locate_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."youhui", $youhui, $mode = 'UPDATE', "id=".$youhui_id, $querymode = 'SILENT');	
		
		
		//同步名称
		$name_arr = div_str(trim($youhui['name'])); 
		foreach($name_arr as $name_item)
		{
			insert_match_item($name_item,"youhui",$youhui_id,"name_match");
		}
		
		$brand_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."brand where id = ".$youhui['brand_id']);
		insert_match_item($brand_name,"youhui",$youhui_id,"name_match");
			
		
		//分类类别
		$deal_cate =array();		
		get_all_parent_id(intval($youhui['deal_cate_id']),DB_PREFIX."deal_cate",$deal_cate);
		if(count($deal_cate)>0)
		{
			$deal_cates = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_cate where id in (".implode(",",$deal_cate).")");
			foreach ($deal_cates as $row)
			{
				insert_match_item(trim($row['name']),"youhui",$youhui_id,"deal_cate_match");
			}
		}
		//获取所有子类
		$sub_cate = $GLOBALS['db']->getAll("select t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_youhui_link as l on l.deal_cate_type_id = t.id where l.youhui_id = ".$youhui['id']);
		foreach ($sub_cate as $row)
		{
			insert_match_item(trim($row['name']),"youhui",$youhui_id,"deal_cate_match");
		}
		
		//地址
		$deal_city_arr = array();
		get_all_parent_id($youhui['city_id'],DB_PREFIX."deal_city",$deal_city_arr);
		if(count($deal_city_arr)>0)
		{
			$deal_citys_arr = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_city where id in (".implode(",",$deal_city_arr).")");
			foreach ($deal_citys_arr as $row)
			{
				insert_match_item(trim($row['name']),"youhui",$youhui_id,"locate_match");
			}
		}

		$supplier_locations = $GLOBALS['db']->getAll("select a.* from ".DB_PREFIX."supplier_location as a left join ".DB_PREFIX."youhui_location_link as b on a.id = b.location_id where a.supplier_id = ".intval($youhui['supplier_id'])." and b.youhui_id = ".$youhui['id']);
		
		foreach($supplier_locations as $locate)
		{		
			$address_arr = div_str(trim($locate['address']));
			foreach($address_arr as $address_item)
			{
				insert_match_item($address_item,"youhui",$youhui_id,"locate_match");
			}
			
			$areas = $GLOBALS['db']->getAll("select a.name,a.pid from ".DB_PREFIX."area as a left join ".DB_PREFIX."supplier_location_area_link as l on l.area_id = a.id where l.location_id = ".$locate['id']);
			foreach($areas as $area)
			{
				if($area['pid']>0)
				{
					$parent_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$area['pid']);
					insert_match_item(trim($parent_name),"youhui",$youhui_id,"locate_match");
				}
				insert_match_item($area['name'],"youhui",$youhui_id,"locate_match");
			}
		}
	
	}
}
/**
 * 格式化点评内容
 */
function sys_get_dp_detail($data)
{

	$data['user_name'] = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$data['user_id']);
		
	$data['group_point'] = $GLOBALS['db']->getAll("select g.id,g.name,p.point from ".DB_PREFIX."supplier_location_dp_point_result as p left join ".DB_PREFIX."point_group as g on p.group_id = g.id where p.dp_id = ".$data['id']);
	$data['point_lang'] = $GLOBALS['lang']["dp_point_".$data['point']];
			
	$data['imgs'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location_dp_images where dp_id = ".$data['id']);
	$data['img_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp_images where dp_id = ".$data['id']);

	if($data['from_data']!="")
	{
		$data['rel_url'] = parse_url_tag("u:".$data['rel_app_index']."|".$data['rel_route']."|".$data['rel_param']);	
		$data['rel_name'] = $GLOBALS['lang']['FROM_DATA_'.strtoupper($data['from_data'])];
	}
	//标签组
	$data['group_tag'] = $GLOBALS['db']->getAll("select g.id,g.name,t.tags from ".DB_PREFIX."supplier_location_dp_tag_result as t left join ".DB_PREFIX."tag_group as g on t.group_id = g.id where t.dp_id = ".$data['id']);
			//print_r($data['group_tag']);
	foreach($data['group_tag'] as $kk=>$vv)
	{
		$tags_arr = explode(" ",$vv['tags']);
		foreach($tags_arr as $kkk=>$vvv)
		{
			$vvv = trim($vvv);
			if($vvv!="")
			{
				$tags_item = array("name"=>$vvv,"url"=>url("index","stores",array("tag"=>$vvv)));
				$data['group_tag'][$kk]['tags_arr'][] = $tags_item;
			}
					
		}
	}
	
	return $data;
	
}
/**
 * 更新商户统计
 */
function syn_supplier_locationcount($supplier_locationinfo)
{
	$supplier_locationinfo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$supplier_locationinfo['id']);
	$supplier_locationinfo['new_dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".$supplier_locationinfo['id']." and create_time > ".$supplier_locationinfo['new_dp_count_time'])); 
	$supplier_locationinfo['dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['image_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_images where status = 1 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['ref_avg_price'] = floatval($GLOBALS['db']->getOne("select avg(avg_price) from ".DB_PREFIX."supplier_location_dp where status=1 and deal_id > 0 and supplier_location_id = ".$supplier_locationinfo['id']));
	$supplier_locationinfo['good_dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and point>=4 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['common_dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and point=3 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['bad_dp_count'] = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp where status = 1 and point<=2 and supplier_location_id = ".$supplier_locationinfo['id'])); 
	$supplier_locationinfo['good_rate'] = floatval($supplier_locationinfo['good_dp_count']/$supplier_locationinfo['dp_count']);
	$supplier_locationinfo['common_rate'] = floatval($supplier_locationinfo['common_dp_count']/$supplier_locationinfo['dp_count']);
	$supplier_locationinfo['bad_rate'] = floatval($supplier_locationinfo['bad_dp_count']/$supplier_locationinfo['dp_count']);
	$supplier_locationinfo['total_point'] = intval($GLOBALS['db']->getOne("select sum(point) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".$supplier_locationinfo['id'])); 
// 	+ intval($GLOBALS['db']->getOne("select sum(point) from ".DB_PREFIX."supplier_location_sign_log where location_id = ".$supplier_locationinfo['id'])); 签到弃用
	$dp_avg = floatval($GLOBALS['db']->getOne("select avg(point) from ".DB_PREFIX."supplier_location_dp where status = 1 and supplier_location_id = ".$supplier_locationinfo['id']));
	//$sign_avg = floatval($GLOBALS['db']->getOne("select avg(point) from ".DB_PREFIX."supplier_location_sign_log where location_id = ".$supplier_locationinfo['id']));  签到弃用	

	$supplier_locationinfo['avg_point'] = $dp_avg;

	//弃用签到
// 	if($dp_avg>0&&$sign_avg>0)
// 	$supplier_locationinfo['avg_point'] = ($dp_avg+$sign_avg)/2;
// 	elseif ($dp_avg>0)
// 	$supplier_locationinfo['avg_point'] = $dp_avg;
// 	else
// 	$supplier_locationinfo['avg_point'] = $sign_avg;
	
	//计算总点评1-5星人数
	$sql = "select count(*) as total,point from ".DB_PREFIX."supplier_location_dp where supplier_location_id = ".$supplier_locationinfo['id']." group by point";
	
	$data_result = $GLOBALS['db']->getAll($sql);
	foreach($data_result as $k=>$v)
	{
		$supplier_locationinfo['dp_count_'.$v['point']] = $v['total'];
	}
	
	
	 $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$supplier_locationinfo,"UPDATE"," id= ".$supplier_locationinfo['id']);

	 //同步分组评分
	$point_group_result = $GLOBALS['db']->getAll("select supplier_location_id,group_id,sum(point) as total_point,avg(point) as avg_point from ".DB_PREFIX."supplier_location_dp_point_result where supplier_location_id = ".$supplier_locationinfo['id']." group by group_id");
	foreach($point_group_result as $k=>$v)
	{
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_point_result where supplier_location_id=".intval($v['supplier_location_id'])." and group_id=".$v['group_id'])==0)
		{
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_point_result", $v, "INSERT");
		}
		else
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."supplier_location_point_result set avg_point = ".$v['avg_point'].",total_point = ".$v['total_point']." where supplier_location_id =".$v['supplier_location_id']." and group_id=".$v['group_id']);
		}
	}
	if(!$supplier_locationinfo['dp_count'])
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_location_point_result set avg_point = 0,total_point = 0 where supplier_location_id =".$supplier_locationinfo['id']);
	}
}

function syn_deal_review_count($id)
{
	$sql = "select sum(point) as total_point,avg(point) as avg_point,count(*) as dp_count from ".DB_PREFIX."supplier_location_dp where deal_id = ".$id;
	$data_result = $GLOBALS['db']->getRow($sql);
	$item_data['total_point'] = $data_result['total_point'];
	$item_data['avg_point'] = $data_result['avg_point'];
	$item_data['dp_count'] = $data_result['dp_count'];
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$item_data,"UPDATE","id=".$id);
}
function syn_youhui_review_count($id)
{
	$sql = "select sum(point) as total_point,avg(point) as avg_point,count(*) as dp_count from ".DB_PREFIX."supplier_location_dp where youhui_id = ".$id;
	$data_result = $GLOBALS['db']->getRow($sql);
	$item_data['total_point'] = $data_result['total_point'];
	$item_data['avg_point'] = $data_result['avg_point'];
	$item_data['dp_count'] = $data_result['dp_count'];
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."youhui",$item_data,"UPDATE","id=".$id);
}
function syn_event_review_count($id)
{
	$sql = "select sum(point) as total_point,avg(point) as avg_point,count(*) as dp_count from ".DB_PREFIX."supplier_location_dp where event_id = ".$id;
	$data_result = $GLOBALS['db']->getRow($sql);
	$item_data['total_point'] = $data_result['total_point'];
	$item_data['avg_point'] = $data_result['avg_point'];
	$item_data['dp_count'] = $data_result['dp_count'];

	$GLOBALS['db']->autoExecute(DB_PREFIX."event",$item_data,"UPDATE","id=".$id);
}


//封装url

function url($app_index,$route="index",$param=array())
{
	$key = md5("URL_KEY_".$app_index.$route.serialize($param));
	if(isset($GLOBALS[$key]))
	{
		$url = $GLOBALS[$key];
		return $url;
	}
	
	$url = load_dynamic_cache($key);
	if($url!==false)
	{
		$GLOBALS[$key] = $url;
		return $url;
	}
	
	$show_city = intval($GLOBALS['city_count'])>1?true:false;  //有多个城市时显示城市名称到url
	$route_array = explode("#",$route);
	
	if(isset($param)&&$param!=''&&!is_array($param))
	{
		$param['id'] = $param;
	}

	$module = strtolower(trim($route_array[0]));
	$action = strtolower(trim($route_array[1]));

	if(!$module||$module=='index')$module="";
	if(!$action||$action=='index')$action="";

	if(app_conf("URL_MODEL")==0 || $GLOBALS['request']['from']=="wap")//fwb改过
	{
		//过滤主要的应用url
		if($app_index==app_conf("MAIN_APP"))
		$app_index = "index";
		
		//原始模式
		$url = APP_ROOT."/".$app_index.".php";
		if($module!=''||$action!=''||count($param)>0||$show_city) //有后缀参数
		{
			$url.="?";
		}

		if(isset($param['city']))
		{
			$url .= "city=".$param['city']."&";
			unset($param['city']);
		}		
		if($module&&$module!='')
		$url .= "ctl=".$module."&";
		if($action&&$action!='')
		$url .= "act=".$action."&";
		if(count($param)>0)
		{
			foreach($param as $k=>$v)
			{
				if($k&&$v)
				$url =$url.$k."=".urlencode($v)."&";
			}
		}
		if(substr($url,-1,1)=='&'||substr($url,-1,1)=='?') $url = substr($url,0,-1);
		$GLOBALS[$key] = $url;
		set_dynamic_cache($key,$url);
		return $url;
	}
	else
	{
		//重写的默认
		$url = APP_ROOT;
	
		if($app_index!='index')
		$url .= "/".$app_index;

		if($module&&$module!='')
		$url .= "/".$module;
		if($action&&$action!='')
		$url .= "/".$action;
		
		if(count($param)>0)
		{
			$url.="/";
			foreach($param as $k=>$v)
			{
				if($k!='city')
				$url =$url.$k."-".urlencode($v)."-";
			}
		}
		
		//过滤主要的应用url
		if($app_index==app_conf("MAIN_APP"))
		$url = str_replace("/".app_conf("MAIN_APP"),"",$url);
		
		$route = $module."#".$action;
		switch ($route)
		{
				case "xxx":
					break;
				default:
					break;
		}
				
		if(substr($url,-1,1)=='/'||substr($url,-1,1)=='-') $url = substr($url,0,-1);
		
		
		
		if(isset($param['city']))
		{
			$city_uname = $param['city'];

			if($GLOBALS['distribution_cfg']['DOMAIN_ROOT']!="")
			{
				$domain = "http://".$city_uname.".".$GLOBALS['distribution_cfg']['DOMAIN_ROOT'];	
				return $domain.$url;
			}
			else
			{
				return $url."/city/".$city_uname;
			}	

		}
		if($url=='')$url="/";
		$GLOBALS[$key] = $url;
		set_dynamic_cache($key,$url);
		return $url;
	}
	
	
}

function wap_url($app_index,$route="index",$param=array())
{
	global $page_type;
	if($page_type)
	{
		$param['page_type'] = $page_type;
	}
	global $spid;
	if($spid)
	{
		if(!isset($param['spid']))
		$param['spid'] = $spid;
	}
	
	$key = md5("WAP_URL_KEY_".$app_index.$route.serialize($param));
	if(isset($GLOBALS[$key]))
	{
		$url = $GLOBALS[$key];
		return $url;
	}

	$url = load_dynamic_cache($key);
	if($url!==false)
	{
		$GLOBALS[$key] = $url;
		return $url;
	}

	$show_city = intval($GLOBALS['city_count'])>1?true:false;  //有多个城市时显示城市名称到url
	$route_array = explode("#",$route);

	if(isset($param)&&$param!=''&&!is_array($param))
	{
		$param['id'] = $param;
	}

	$module = strtolower(trim($route_array[0]));
	$action = strtolower(trim($route_array[1]));

	if(!$module||$module=='index')$module="";
	if(!$action||$action=='index')$action="";

	//原始模式
	$url = APP_ROOT."/wap/".$app_index.".php";
	if($module!=''||$action!=''||count($param)>0||$show_city) //有后缀参数
	{
		$url.="?";
		/** 关闭url传输自定义session到url中，很重要，如有遇到浏览器不支持cookie的再议
		if($GLOBALS['define_sess_id'])
		{
			$url.="sess_id=".$GLOBALS['sess_id']."&";
		}*/
	}
	else
	{
		/** 关闭url传输自定义session到url中，很重要，如有遇到浏览器不支持cookie的再议
		if($GLOBALS['define_sess_id'])
		{
			$url.="?sess_id=".$GLOBALS['sess_id']."&";
		}
		*/
	}

	if(isset($param['city']))
	{
		$url .= "city=".$param['city']."&";
		unset($param['city']);
	}
	if($module&&$module!='')
		$url .= "ctl=".$module."&";
	if($action&&$action!='')
		$url .= "act=".$action."&";
	if(count($param)>0)
	{
		foreach($param as $k=>$v)
		{
			if($k&&$v)
				$url =$url.$k."=".urlencode($v)."&";
		}
	}
	if(substr($url,-1,1)=='&'||substr($url,-1,1)=='?') $url = substr($url,0,-1);
	$GLOBALS[$key] = $url;
	set_dynamic_cache($key,$url);
	return $url;
}

function unicode_encode($name) {//to Unicode
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for($i = 0; $i < $len - 1; $i = $i + 2) {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0) {// 两个字节的字
            $cn_word = '\\'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
            $str .= strtoupper($cn_word);
        } else {
            $str .= $c2;
        }
    }
    return $str;
}

function unicode_decode($name) {//Unicode to
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches)) {
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++) {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0) {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            } else {
                $name .= $str;
            }
        }
    }
    return $name;
}

//生成短信发送的优惠券
/**
 * 
 * @param $youhui_id 优惠券ID
 * @param $mobile 手机号
 * @param $user_id 会员ID
 * 以下参数仅供 send_type = 2 预订验证券使用
 * @param $order_count 预订的人数
 * @param $is_private_room  预订是否包间
 * @param $date_time  预订时间
 */
function gen_verify_youhui($youhui_id,$mobile,$user_id,$order_count=0,$is_private_room=0,$date_time=0)
{
	
	$data = array();
	$data['youhui_id'] = intval($youhui_id);
	$data['user_id'] = intval($user_id);
	$data['user_id'] = intval($user_id);
	$data['mobile'] = $mobile;
	$data['order_count'] = intval($order_count);
	$data['order_count'] = intval($order_count);
	$data['is_private_room'] = intval($is_private_room);
	$data['date_time'] = intval($date_time);
	$data['create_time'] = NOW_TIME;
	$data['youhui_sn'] = rand(10000000,99999999);
	do{
		$GLOBALS['db']->autoExecute(DB_PREFIX."youhui_log", $data, $mode = 'INSERT', "", $querymode = 'SILENT');		
		$rs = $GLOBALS['db']->insert_id();	
	}while(intval($rs)==0);
	return $rs;
}

//生成短信发送的优惠券
/**
 * 
 * @param $youhui_id 优惠券ID
 * @param $mobile 手机号
 * @param $user_id 会员ID
 * 以下参数仅供 send_type = 2 预订验证券使用
 * @param $order_count 预订的人数
 * @param $is_private_room  预订是否包间
 * @param $date_time  预订时间
 */
function gen_verify_youhui_to_mobile($youhui_id,$mobile,$user_id,$order_count=0,$is_private_room=0,$date_time=0)
{
	
	$data = array();
	$data['youhui_id'] = intval($youhui_id);
	$data['user_id'] = intval($user_id);
	$data['mobile'] = $mobile;
	$data['order_count'] = intval($order_count);
	$data['order_count'] = intval($order_count);
	$data['is_private_room'] = intval($is_private_room);
	$data['date_time'] = intval($date_time);
	$data['create_time'] = NOW_TIME;
	$data['youhui_sn'] = rand(10000000,99999999);
	$data['send_method']=1;
	do{
		$GLOBALS['db']->autoExecute(DB_PREFIX."youhui_log", $data, $mode = 'INSERT', "", $querymode = 'SILENT');		
		$rs = $GLOBALS['db']->insert_id();	
	}while(intval($rs)==0);
	if($rs>0){
		$rs=$data['youhui_sn'] ;
	}
	return $rs;
}



//发送优惠券短信(验证类型), 函数不验证发送次数是否超限，前台发送时验证
function send_youhui_log_sms($log_id)
{
	if(app_conf("SMS_ON")==1)
	{	
		$log_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_log where id = ".$log_id);	
		$youhui_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$log_data['youhui_id']);				
		if($youhui_data)
		{
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$log_data['user_id']);
			if($user_info)
			{
				$msg_data['dest'] = $user_info['mobile'];
				$msg_data['send_type'] = 0;
				if($youhui_data['sms_content']!="")
					$msg_data['content'] = $youhui_data['sms_content']." - 验证码:".$log_data['youhui_sn'];
				else
					$msg_data['content'] = $youhui_data['name']." - 验证码:".$log_data['youhui_sn'];
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = 0;
				$msg_data['is_youhui'] = 1;
				$msg_data['youhui_id'] = $youhui_data['id'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				$id = $GLOBALS['db']->insert_id();
				if($id)
				{
					return $id;
				}
				else 
				return false;
				
			}
			else
			return false;
		}
		else
		return false;		
	}
	else
	{
		return false;
	}
}

//载入动态缓存数据
function load_dynamic_cache($name)
{
	if(isset($GLOBALS['dynamic_cache'][$name]))
	{
		return $GLOBALS['dynamic_cache'][$name];
	}
	else
	{
		return false;
	}
}

function set_dynamic_cache($name,$value)
{
	if(!isset($GLOBALS['dynamic_cache'][$name]))
	{
		if(count($GLOBALS['dynamic_cache'])>MAX_DYNAMIC_CACHE_SIZE)
		{
			array_shift($GLOBALS['dynamic_cache']);
		}
		$GLOBALS['dynamic_cache'][$name] = $value;		
	}
}


//同步一张图片到分享图片表(图片可以为本地获远程。 远程需要开启file_get_contents()的远程权限)
function syn_image_to_topic($image)
{
    $image = str_replace("./public", APP_ROOT_PATH."public", $image);
	$image_str = @file_get_contents($image);
	$file_name = md5(microtime(true)).rand(10,99).".jpg";
	
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
	   
	   $file_url = "./public/comment/".$dir."/".$file_name;	  
	   $file_path = APP_ROOT_PATH."public/comment/".$dir."/".$file_name;
	   @file_put_contents($file_path,$image_str);
	   $filesize = intval(@filesize($file_path));

	   if($filesize>0)
	   {
		   	if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		   	{
		   		syn_to_remote_image_server($file_url);
		   	}
		   	
		    $icon_url = get_spec_image($file_url,100,100,1);		   
		    require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
			$image = new es_imagecls();

			$info = $image->getImageInfo($file_path);
			$image_data['width'] = intval($info[0]);
			$image_data['height'] = intval($info[1]);
			$image_data['name'] =$file_name;
			$image_data['filesize'] = $filesize;
			$image_data['create_time'] = NOW_TIME;
			$image_data['user_id'] = intval($GLOBALS['user_info']['id']);
			$image_data['user_name'] = addslashes($GLOBALS['user_info']['user_name']);
			$image_data['path'] = $icon_url;
			$image_data['o_path'] = $file_url;
			$GLOBALS['db']->autoExecute(DB_PREFIX."topic_image",$image_data);				
			$data['id'] = intval($GLOBALS['db']->insert_id());
			$data['url'] = $icon_url;
	   }
	   return $data;
	
}

function load_auto_cache($key,$param=array())
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".APP_TYPE."/".$key.".auto_cache.php";
	if(!file_exists($file))
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$result = $obj->load($param);
	}
	else
	$result = false;
	return $result;
}

function rm_auto_cache($key,$param=array())
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$obj->rm($param);
	}
}


function clear_auto_cache($key)
{
	require_once APP_ROOT_PATH."system/libs/auto_cache.php";
	$file =  APP_ROOT_PATH."system/auto_cache/".$key.".auto_cache.php";
	if(file_exists($file))
	{
		require_once $file;
		$class = $key."_auto_cache";
		$obj = new $class;
		$obj->clear_all();
	}
}

//获取随机会员提供关注
function get_rand_user($count,$is_daren=0,$uid=0)
{
	//第0阶梯达人，10个会员
	$danren_result_0 = $GLOBALS['cache']->get("RAND_USER_CACHE_DAREN_0");
	if($danren_result_0===false)
	{
		$sql = "select id,user_name,province_id,city_id from ".DB_PREFIX."user where is_daren = 1 and is_effect = 1 order by is_merchant desc,is_daren desc,topic_count desc limit 10";	
		$danren_result_0 = $GLOBALS['db']->getAll($sql);
		if($danren_result_0)
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_0",$danren_result_0,3600);
		else
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_0",array(),3600);
	}	
	
	//第1阶梯达人，50个会员
	$danren_result_1 = $GLOBALS['cache']->get("RAND_USER_CACHE_DAREN_1");
	if($danren_result_1===false)
	{
		$sql = "select id,user_name,province_id,city_id from ".DB_PREFIX."user where is_daren = 1 and is_effect = 1 order by is_merchant desc,is_daren desc,topic_count desc limit 10,50";	
		$danren_result_1 = $GLOBALS['db']->getAll($sql);
		if($danren_result_1)
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_1",$danren_result_1,3600);
		else
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_1",array(),3600);
	}
	
	//第2阶梯达人，2000个会员
	$danren_result_2 = $GLOBALS['cache']->get("RAND_USER_CACHE_DAREN_2");
	if($danren_result_2===false)
	{
		$sql = "select id,user_name,province_id,city_id from ".DB_PREFIX."user where is_daren = 1 and is_effect = 1 order by is_merchant desc,is_daren desc,topic_count desc limit 50,2000";	
		$danren_result_2 = $GLOBALS['db']->getAll($sql);
		if($danren_result_2)
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_2",$danren_result_2,3600);
		else
		$GLOBALS['cache']->set("RAND_USER_CACHE_DAREN_2",array(),3600);
	}
	
	$danren_list[] = $danren_result_0;
	$danren_list[] = $danren_result_1;
	$danren_list[] = $danren_result_2;
	
	//非达人 , 2000个活跃会员
	$nodanren_result = $GLOBALS['cache']->get("RAND_USER_CACHE_NODAREN");
	if($nodanren_result===false)
	{
		$sql = "select id,user_name,province_id,city_id from ".DB_PREFIX."user where is_daren = 0 and is_effect = 1 order by is_merchant desc,is_daren desc,topic_count desc limit 2000";	
		$nodanren_result = $GLOBALS['db']->getAll($sql);
		if($nodanren_result)
		$GLOBALS['cache']->set("RAND_USER_CACHE_NODAREN",$nodanren_result,3600);
		else
		$GLOBALS['cache']->set("RAND_USER_CACHE_NODAREN",array(),3600);
	}	
	
	$user_list = array();
	if($uid==0)
	{
		$user_group = 0; //阶梯数		
		while(count($user_list)<$count&&$user_group<3)
		{
			$current_count = count($user_list);
			for($loop=0;$loop<$count-$current_count;$loop++)
			{				
				$i = rand(0,count($danren_list[$user_group])-1);				
				$user_item = $danren_list[$user_group][$i];
				unset($danren_list[$user_group][$i]);
				if($danren_list[$user_group]){
					sort($danren_list[$user_group]);
				}
				if($user_item)
				$user_list[] = $user_item;
			}
			$user_group++;			
		}
		
		if(count($user_list)<$count&&$is_daren==0)
		{
			//人数还不足，并允许非达人
			$current_count = count($user_list);
			for($loop=0;$loop<$count-$current_count;$loop++)
			{				
				$i = rand(0,count($nodanren_result)-1);				
				$user_item = $nodanren_result[$i];
				unset($nodanren_result[$i]);
				sort($nodanren_result);
				if($user_item)
				$user_list[] = $user_item;
			}
		}

	}
	else
	{
		
		
		$user_group = 0; //阶梯数		
		while(count($user_list)<$count&&$user_group<3)
		{
			$current_count = count($user_list);
			//$loop_count 用于限制循环上限, $c用于计算个数, $i标识当前位置
			for($loop_count=0,$c=0;$c<$count-$current_count&&$loop_count<100;$loop_count++,$c++)
			{				
				$i = rand(0,count($danren_list[$user_group])-1);				
				$user_item = $danren_list[$user_group][$i];
				unset($danren_list[$user_group][$i]);
				sort($danren_list[$user_group]);
				if($user_item)
				{
					if($user_item['id']!=$uid&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$uid." and focused_user_id = ".intval($user_item['id']))==0)
					$user_list[] = $user_item;							
					else					
					$c--;									
				}
							
			}
			$user_group++;			
		}
		
		if(count($user_list)<$count&&$is_daren==0)
		{
			//人数还不足，并允许非达人
			
			$current_count = count($user_list);
			for($loop_count=0,$c=0;$c<$count-$current_count&&$loop_count<100;$loop_count++,$c++)
			{
				$i = rand(0,count($nodanren_result)-1);				
				$user_item = $nodanren_result[$i];
				unset($nodanren_result[$i]);
				sort($nodanren_result);
				if($user_item)
				{
					if($user_item['id']!=$uid&&$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$uid." and focused_user_id = ".intval($user_item['id']))==0)
					$user_list[] = $user_item;							
					else					
					$c--;									
				}		
			}
		}		
		
	}
	return $user_list;
	
}

/*ajax返回*/
function ajax_return($data,$jsonp=false)
{
	if($jsonp)
	{
			$json = json_encode($data);
			header("Content-Type:text/html; charset=utf-8");
			echo $_GET['callback']."(".$json.")";exit;
			

	}
	else
	{
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($data));
        exit;	
	}
}


//增加会员活跃度
function increase_user_active($user_id,$log)
{
	$t_begin_time = to_timespan(to_date(NOW_TIME,"Y-m-d"));  //今天开始
	$t_end_time = to_timespan(to_date(NOW_TIME,"Y-m-d"))+ (24*3600 - 1);  //今天结束
	$y_begin_time = $t_begin_time - (24*3600); //昨天开始
	$y_end_time = $t_end_time - (24*3600);  //昨天结束
	
	$point = intval(app_conf("USER_ACTIVE_POINT"));
	$score = intval(app_conf("USER_ACTIVE_SCORE"));
	$money = floatval(app_conf("USER_ACTIVE_MONEY"));
	$point_max = intval(app_conf("USER_ACTIVE_POINT_MAX"));
	$score_max = intval(app_conf("USER_ACTIVE_SCORE_MAX"));
	$money_max = floatval(app_conf("USER_ACTIVE_MONEY_MAX"));
	
	$sum_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."user_active_log where user_id = ".$user_id." and create_time between ".$t_begin_time." and ".$t_end_time));
	$sum_score = intval($GLOBALS['db']->getOne("select sum(score) from ".DB_PREFIX."user_active_log where user_id = ".$user_id." and create_time between ".$t_begin_time." and ".$t_end_time));
	$sum_point = intval($GLOBALS['db']->getOne("select sum(point) from ".DB_PREFIX."user_active_log where user_id = ".$user_id." and create_time between ".$t_begin_time." and ".$t_end_time));
	
	if($sum_money>=$money_max)$money = 0;
	if($sum_score>=$score_max)$score = 0;
	if($sum_point>=$point_max)$point = 0;
	
	if($money>0||$score>0||$point>0)
	{
		require_once  APP_ROOT_PATH."system/model/user.php";
		modify_account(array("money"=>$money,"score"=>$score,"point"=>$point),$user_id,$log);
		$data['user_id'] = $user_id;
		$data['create_time'] = NOW_TIME;
		$data['money'] = $money;
		$data['score'] = $score;
		$data['point'] = $point;
		$GLOBALS['db']->autoExecute(DB_PREFIX."user_active_log",$data);
	}
}

/**
 * 
 * @param $location_id 店铺ID
 * @param $data_type  tuan/event/youhui/shop
 */
function recount_supplier_data_count($location_id,$data_type,$store='')
{
	switch ($data_type)
	{
		case "tuan":
			if(empty($store))
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
			require_once APP_ROOT_PATH."system/model/deal.php";
			$count = get_deal_count(array(DEAL_ONLINE,DEAL_NOTICE),array()," left join ".DB_PREFIX."deal_location_link as l  on d.id = l.deal_id "," d.buy_type <> 1 and d.is_shop = 0 and l.location_id =".$location_id);

			$store['tuan_count'] = $count;
// 			$tuan_youhui_cache = unserialize($store['tuan_youhui_cache']);
// 			$tuan_youhui_cache['tuan'] = $GLOBALS['db']->getRow("select d.name,d.id from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where d.is_effect = 1 and d.is_delete = 0 and d.time_status <> 2 and d.is_shop <> 1 and d.buy_type = 0 and l.location_id = ".$location_id." limit 1");
// 			$store['tuan_youhui_cache'] = serialize($tuan_youhui_cache);
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);
			return $store;
			

			
		case "shop":
			if(empty($store))
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
			require_once APP_ROOT_PATH."system/model/deal.php";
			$count = get_goods_count(array(DEAL_ONLINE,DEAL_NOTICE),array()," left join ".DB_PREFIX."deal_location_link as l on d.id = l.deal_id "," d.buy_type <> 1 and d.is_shop = 1 and l.location_id = ".$location_id);
	
			$store['shop_count'] = $count;
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);		
			return $store;
			
		case "event":
			if(empty($store))
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);

			require_once APP_ROOT_PATH."system/model/event.php";
			$count = get_event_count(array(EVENT_NOTICE,EVENT_ONLINE),array()," left join ".DB_PREFIX."event_location_link as l on e.id = l.event_id "," l.location_id = ".$location_id);
			$store['event_count'] = $count;
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);		
			return $store;
		
		case "youhui":
			if(empty($store))
			$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);

			require_once APP_ROOT_PATH."system/model/youhui.php";			
			$count = get_youhui_count(array(YOUHUI_NOTICE,YOUHUI_ONLINE),array(), ' left join '.DB_PREFIX."youhui_location_link as l on y.id = l.youhui_id "," l.location_id = ".$location_id);
			$store['youhui_count'] = $count;	
// 			$tuan_youhui_cache = unserialize($store['tuan_youhui_cache']);
// 			$tuan_youhui_cache['youhui'] =  $GLOBALS['db']->getRow("select y.name,y.id from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where y.is_effect = 1 and (y.end_time = 0 or y.end_time > ".NOW_TIME.") and l.location_id = ".$location_id." limit 1");
// 			$store['tuan_youhui_cache'] = serialize($tuan_youhui_cache);
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$location_id);
			return $store;
		
	}
	
}



function is_animated_gif($filename){
 $fp=fopen($filename, 'rb');
 $filecontent=fread($fp, filesize($filename));
 fclose($fp);
 return strpos($filecontent,chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===FALSE?0:1;
}


function make_deal_cate_js()
{
	$js_file = APP_ROOT_PATH."public/runtime/app/deal_cate_conf.js";
	if(!file_exists($js_file))
	{
		$js_str = "var deal_cate_conf = [";
		$deal_cates = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."deal_cate where is_delete = 0 and is_effect = 1 order by sort desc");
		foreach($deal_cates as $k=>$v)
		{
			$js_str.='{"n":"'.$v['name'].'","i":"'.$v['id'].'","s":[';
			$deal_cate_types = $GLOBALS['db']->getAll("select t.id,t.name from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where l.cate_id = ".$v['id']." order by t.sort desc");
			foreach($deal_cate_types as $kk=>$vv)
			{
				$js_str .= '{"n":"'.$vv['name'].'","i":"'.$vv['id'].'"},';
			}
			if($deal_cate_types)
			$js_str = substr($js_str,0,-1);
			$js_str .= ']},';
		}
		if($deal_cates)
		$js_str = substr($js_str,0,-1);
		$js_str.="];";
		@file_put_contents($js_file,$js_str);
	}
}

function make_deal_region_js()
{
	$dir = APP_ROOT_PATH."public/runtime/app/deal_region_conf/";
	if (!is_dir($dir))
    {
             @mkdir($dir);
             @chmod($dir, 0777);
    }  
	$js_file = $dir.intval($GLOBALS['deal_city']['id']).".js";
	if(!file_exists($js_file))
	{
		$js_str = "var deal_region_conf = [";
		$areas = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area where city_id = ".intval($GLOBALS['deal_city']['id'])." and pid = 0 order by sort desc");
		foreach($areas as $k=>$v)
		{
			$js_str.='{"n":"'.$v['name'].'","i":"'.$v['id'].'","s":[';
			$regions = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area where city_id = ".intval($GLOBALS['deal_city']['id'])." and pid = ".$v['id']." order by sort desc");
			foreach($regions as $kk=>$vv)
			{
				$js_str .= '{"n":"'.$vv['name'].'","i":"'.$vv['id'].'"},';
			}
			if($regions)
			$js_str = substr($js_str,0,-1);
			$js_str .= ']},';
		}
		if($areas)
		$js_str = substr($js_str,0,-1);
		$js_str.="];";
		@file_put_contents($js_file,$js_str);
	}
}


function make_delivery_region_js()
{
	$path = APP_ROOT_PATH."public/runtime/region.js"; 
	if(!file_exists($path))
	{
		$jsStr = "var regionConf = ".get_delivery_region_js();		
		@file_put_contents($path,$jsStr);
	}
}
function get_delivery_region_js($pid = 0)
{

		$jsStr = "";
		$childRegionList = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_region where pid = ".$pid." order by id asc");
		foreach($childRegionList as $childRegion)
		{
			if(empty($jsStr))
				$jsStr .= "{";
			else
				$jsStr .= ",";
				
			$childStr = get_delivery_region_js($childRegion['id']);
			$jsStr .= "\"r$childRegion[id]\":{\"i\":$childRegion[id],\"n\":\"$childRegion[name]\",\"c\":$childStr}";
		}
		
		if(!empty($jsStr))
			$jsStr .= "}";
		else
			$jsStr .= "\"\"";
				
		return $jsStr;

}

function update_sys_config()
{
	$filename = APP_ROOT_PATH."public/sys_config.php";
	if(!file_exists($filename))
	{
		//定义DB
		require APP_ROOT_PATH.'system/db/db.php';
		$dbcfg = require APP_ROOT_PATH."public/db_config.php";
		define('DB_PREFIX', $dbcfg['DB_PREFIX']); 
		if(!file_exists(APP_ROOT_PATH.'public/runtime/app/db_caches/'))
			mkdir(APP_ROOT_PATH.'public/runtime/app/db_caches/',0777);
		$pconnect = false;
		$db = new mysql_db($dbcfg['DB_HOST'].":".$dbcfg['DB_PORT'], $dbcfg['DB_USER'],$dbcfg['DB_PWD'],$dbcfg['DB_NAME'],'utf8',$pconnect);
		//end 定义DB

		$sys_configs = $db->getAll("select * from ".DB_PREFIX."conf");
		$config_str = "<?php\n";
		$config_str .= "return array(\n";
		foreach($sys_configs as $k=>$v)
		{
			$config_str.="'".$v['name']."'=>'".addslashes($v['value'])."',\n";
		}
		$config_str.=");\n ?>";	
		file_put_contents($filename,$config_str);
		$url = APP_ROOT."/";
		app_redirect($url);
	}
}

/**
 * 更新结算状态
 * @param unknown_type $rel_id  相关的数据ID(团购券ID或订单商品ID)
 * @param unknown_type $deal_id
 */
function update_balance($rel_id,$deal_id)
{
	$deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$deal_id);
	if($deal_info['is_coupon']==1)
	{
		//团购券
		$sql = "update ".DB_PREFIX."deal_coupon set is_balance = 1 where id = ".$rel_id." and is_balance = 0";
		$GLOBALS['db']->query($sql);	
	}
	else
	{
		//订单商品
		$sql = "update ".DB_PREFIX."deal_order_item set is_balance = 1 where id = ".$rel_id." and is_balance = 0";		
		$GLOBALS['db']->query($sql);
		$order_id = intval($GLOBALS['db']->getOne("select order_id from ".DB_PREFIX."deal_order_item where id = ".$rel_id));
		update_order_cache($order_id);
	}
}

function get_dstatus($status,$id)
{
		if($status)
		{
			$delivery_notice = $GLOBALS['db']->getRow("select dn.notice_sn,dn.delivery_time,de.name,dn.memo from ".DB_PREFIX."delivery_notice as dn left join ".DB_PREFIX."express as de on dn.express_id = de.id where dn.order_item_id = ".$id);	
			return "已发货，发货单号：".$delivery_notice['name'].$delivery_notice['notice_sn']."，发货时间：".to_date($delivery_notice['delivery_time'])." 发货备注：<span title='".$delivery_notice['memo']."'>".msubstr($delivery_notice['memo'])."</span>";
		}
		else
		return "未发货";
}


function gen_qrcode($str,$size = 5,$img=false)
{

	require_once APP_ROOT_PATH."system/phpqrcode/qrlib.php";

	if($img)
	{
		QRcode::png($str, false, 'Q', $size, 2); 
		return;
	}
	
	$root_dir = APP_ROOT_PATH."public/images/qrcode/";
 	if (!is_dir($root_dir)) {
            @mkdir($root_dir);               
            @chmod($root_dir, 0777);
     }
     
     $filename = md5($str."|".$size);
     $hash_dir = $root_dir. '/c' . substr(md5($filename), 0, 1)."/";
     if (!is_dir($hash_dir))
     {
        @mkdir($hash_dir);
        @chmod($hash_dir, 0777);
     }   
	
	$filesave = $hash_dir.$filename.'.png';

	$fileurl =  "./public/images/qrcode/c". substr(md5($filename), 0, 1)."/".$filename.".png";
	if(!file_exists($filesave))
	{
		QRcode::png($str, $filesave, 'Q', $size, 2); 
		if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		syn_to_remote_image_server($fileurl);
	}	
	return $fileurl;       
}


function valid_tag($str)
{
	
	return preg_replace("/<(?!div|ol|ul|li|sup|sub|span|br|img|p|h1|h2|h3|h4|h5|h6|\/div|\/ol|\/ul|\/li|\/sup|\/sub|\/span|\/br|\/img|\/p|\/h1|\/h2|\/h3|\/h4|\/h5|\/h6|blockquote|\/blockquote|strike|\/strike|b|\/b|i|\/i|u|\/u)[^>]*>/i","",$str);
}

//显示语言
// lang($key,p1,p2......) 用于格式化 sprintf %s
function lang($key)
{
	$args = func_get_args();//取得所有传入参数的数组
	$key = strtoupper($key);
	if(isset($GLOBALS['lang'][$key]))
	{
		if(count($args)==1)
			return $GLOBALS['lang'][$key];
		else
		{
			$result = $key;
			$cmd = '$result'." = sprintf('".$GLOBALS['lang'][$key]."'";
			for ($i=1;$i<count($args);$i++)
			{
				$cmd .= ",'".$args[$i]."'";
			}
			$cmd.=");";
			eval($cmd);
			return $result;
		}
	}
	else
		return $key;
}

//缓存下商户
function cache_store_point($store_id)
{
	$store = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$store_id);
	
	if($store)
	{
		$group_point = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."point_group as pg left join ".DB_PREFIX."point_group_link as pgl on pg.id = pgl.point_group_id  where pgl.category_id = ".$store['deal_cate_id']." order by sort asc" );
		foreach($group_point as $kk=>$vv)
		{
			$group_point[$kk]['avg_point'] =  round(floatval($GLOBALS['db']->getOne("select avg_point from ".DB_PREFIX."supplier_location_point_result where supplier_location_id = ".$store['id']." and group_id = ".$vv['id'])),1);
		}
		$store['dp_group_point'] = serialize($group_point);
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$store,"UPDATE","id=".$store['id'],"SILENT");
	}
	return $store;
}

function filter_ctl_act_req($str){
	$search = array("../","\n","\r","\t","\r\n","'","<",">","\"","%");
		
	return str_replace($search,"",$str);
}
function isMobile() {
     $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';  
	 	$mobile_browser = '0';  
	 if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))  
	 	$mobile_browser++;  
	 if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))  
	 	 $mobile_browser++;  
	 if(isset($_SERVER['HTTP_X_WAP_PROFILE']))  
	  	$mobile_browser++;  
	 if(isset($_SERVER['HTTP_PROFILE']))  
	  	$mobile_browser++;  
	 $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));  
	 $mobile_agents = array(  
	    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
	    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
	    'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
	    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
	    'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
	    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
	    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
	    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
	    'wapr','webc','winw','winw','xda','xda-'
	 );  
	 if(in_array($mobile_ua, $mobile_agents))  
	  	$mobile_browser++;  
	 if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)  
	 	 $mobile_browser++;  
	 // Pre-final check to reset everything if the user is on Windows  
	 if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)  
	  	$mobile_browser=0;  
	 // But WP7 is also Windows, with a slightly different characteristic  
	 if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)  
	  	$mobile_browser++;  
	 if($mobile_browser>0)  
	  	return true;  
	 else
	  	return false;
}


/**
 * 转义html编码去空格
 */
function strim($str)
{
	return quotes(htmlspecialchars(trim($str)));
}

/**
 * 转义去空格
 */
function btrim($str)
{
	return quotes(trim($str));
}

function quotes($content)
{
	//if $content is an array
	if (is_array($content))
	{
		foreach ($content as $key=>$value)
		{
			//$content[$key] = mysql_real_escape_string($value);
			$content[$key] = addslashes($value);
		}
	} else
	{
		//if $content is not an array
		$content = addslashes($content);
		//mysql_real_escape_string($content);
	}
	return $content;
}


/**
 *
 * @param $coupon_sn    团购券序列号
 * @param $msg			日志内容
 * @param $query_id		第三方验证时提供的对帐ID
 */
function log_coupon($coupon_sn,$msg,$query_id = '')
{
	$data = array();
	$data['coupon_sn'] = $coupon_sn;
	$data['msg'] = $msg;
	$data['query_id'] = $query_id;
	$data['create_time'] = NOW_TIME;
	if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."coupon_log where query_id = '".$query_id."'")==0)
	{
		$GLOBALS['db']->autoExecute(DB_PREFIX."coupon_log",$data); //插入
		return true;
	}
	else
	{
		return false;
	}
}





/**
 * 计算价格阶梯
 * @param float $max_price
 * 返回 array(array("min"=>"","max"=>""))
 */
function build_price_level($max_price)
{
	return array(
			array("min"=>"0","max"=>"100"),
			array("min"=>"100","max"=>"200"),
			array("min"=>"200","max"=>"400"),
			array("min"=>"40","max"=>"1000"),
			array("min"=>"1000","max"=>"10000"),
	);
}





/**
 * 变更平台财务报表
 * @param unknown_type $money
 * @param unknown_type $type 0.收入 1.订单支付收入 2.会员充值收入 3.支出 4.会员提现支出 5.商户提现支出 6.退款金额 7.退款中的成本 8.销售额,所有支付成功的订单面额(不含在线充值) 9.销售额中成本(即将结算给商家的部份) 10.商家结算额 11.消费额 12.消费额中的成本
 * @param unknown_type $info 日志内容
  `income_money` '收入',
  `income_order` '收入中用于订单支付',
  `income_incharge` '收入用于会员充值(含超额充值)',
  `out_money` '支出',
  `out_uwd_money` '会员提现支出',
  `out_swd_money` '商户提现支出',
  `refund_money` '退款金额',
  `refund_cost_money` decimal(20,4) NOT NULL,
  `sale_money` '销售额,所有支付成功的订单面额(不含在线充值)',
  `sale_cost_money` '销售额中成本(即将结算给商家的部份)',
  `balance_money` '商家结算额',
  `verify_money` '消费的数量',
  `verify_cost_money` '消费额中的成本',
 */
function modify_statements($money,$type,$info)
{
	if($type>=0&&$type<13)
	{
		
		$field_array = array(
				'income_money',
				'income_order',
				'income_incharge',
				'out_money',
				'out_uwd_money',
				'out_swd_money',
				'refund_money',
				'refund_cost_money',
				'sale_money',
				'sale_cost_money',
				'balance_money',
				'verify_money',
				'verify_cost_money');
		
		$stat_time = to_date(NOW_TIME,"Y-m-d");
		$stat_month = to_date(NOW_TIME,"Y-m");
		$state_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."statements where stat_time = '".$stat_time."'");
		if($state_data)
		{
			$state_data[$field_array[$type]] = $state_data[$field_array[$type]]+floatval($money);
			$GLOBALS['db']->autoExecute(DB_PREFIX."statements",$state_data, $mode = 'UPDATE', "id=".$state_data['id'], $querymode = 'SILENT');
			$rs = $GLOBALS['db']->affected_rows();
		}
		else
		{
			$state_data[$field_array[$type]] = floatval($money);
			$state_data["stat_time"] = $stat_time;
			$state_data["stat_month"] = $stat_month;
			$GLOBALS['db']->autoExecute(DB_PREFIX."statements",$state_data, $mode = 'INSERT', "", $querymode = 'SILENT');
			$rs = $GLOBALS['db']->insert_id();
		}
		
		if($rs)
		{
			$log_data = array();
			$log_data['log_info'] = $info;
			$log_data['create_time'] = NOW_TIME;
			$log_data['money'] = floatval($money);
			$log_data['type'] = $type;
				
			$GLOBALS['db']->autoExecute(DB_PREFIX."statements_log",$log_data);
		}				

	}
}


/**
 * 重新缓存订单的缓存，订单商品
 * @param unknown_type $order_id
 */
function update_order_cache($order_id)
{
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	if($order_info)
	{
		$order_info['deal_order_item'] = serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id));
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order_info,'UPDATE','id='.$order_id,'SILENT');
	}
}


/**
 * 发送消息函数
 * @param unknown_type $user_id
 * @param unknown_type $content
 * @param unknown_type $type
 * @param unknown_type $id
 */
function send_msg($user_id,$content,$type,$id)
{
	$interface_file = APP_ROOT_PATH."system/msg/". $type."_msg.php";	
	$class_name = $type."_msg";
	if(file_exists($interface_file))
	{
		require_once $interface_file;		
		if(class_exists($class_name))
		{
			$obj = new $class_name;
			$obj->send_msg($user_id,$content,$id);
			require_once APP_ROOT_PATH."system/model/user.php";
			load_user($user_id,true);
		}
	}
}

/**
 * 加载消息内容
 * @param unknown_type $type
 * @param unknown_type $id
 * @return NULL
 */
function load_msg($type,$msg)
{
	$result = null;
	$interface_file = APP_ROOT_PATH."system/msg/". $type."_msg.php";
	$class_name = $type."_msg";
	if(file_exists($interface_file))
	{
		require_once $interface_file;
		if(class_exists($class_name))
		{
			$obj = new $class_name;
			$result = $obj->load_msg($msg);
		}
	}
	return $result;
}

function get_msg_box_type($type)
{
	$result = null;
	$interface_file = APP_ROOT_PATH."system/msg/". $type."_msg.php";
	$class_name = $type."_msg";
	if(file_exists($interface_file))
	{
		require_once $interface_file;
		if(class_exists($class_name))
		{
			$obj = new $class_name;
			$result = $obj->load_type();
		}
	}
	return $result;
}

function isios() {
	//判断手机发送的客户端标志,兼容性有待提高
	if (isset ($_SERVER['HTTP_USER_AGENT'])) {
		$clientkeywords = array (
				'iphone',
				'ipod',
				'mac',
		);
		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
		if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
			return true;
		}
	}
}


function ofc_max($max_value)
{
	$max_value = floor($max_value);
	$begin_val = substr($max_value,0,1);
	$max_length = strlen($max_value)-1;
	$begin_val = intval($begin_val)+1;

	$multi = "1";
	for($i=0;$i<$max_length;$i++)
	{
	$multi.="0";
	}
	$multi = intval($multi);
	$max_value = $begin_val*$multi;

	if($max_value<=10)$max_value = 10;
	if($max_value>10&&$max_value<=200)$max_value = 200;

	return $max_value;
}

/**
 * 散列算法
 * @param unknown_type $value  计算散列的基础值
 * @param unknown_type $count  散列的总基数
 * @return number
 */

function hash_table($value,$count)
{
	$pid = intval(round(hexdec(md5($value))/pow(10,32))%$count);
	return $pid;
}

/**
 * 获取快递查询api的内容
 * @param unknown_type $url
 */
function get_delivery_api_content($url)
{
	$content = file_get_contents($url);
	$json_data = json_decode($content,true);
	$html = "查询失败";
	$status = false;
	if($json_data['status']==1)
	{
		$status = true;
		$html = "";
		foreach($json_data['data'] as $row)
		{
			$html.="<div style='margin-bottom:5px;'><span style='color:#f30;'>".$row['time']."</span> ".$row['context']."</div>";
		}
	}
	
	return array("status"=>$status,"html"=>$html);
}


//获取相应规格的图片地址
//gen=0:保持比例缩放，不剪裁,如高为0，则保证宽度按比例缩放  gen=1：保证长宽，剪裁
function get_spec_image($img_path,$width=0,$height=0,$gen=0,$is_preview=true)
{
	if(defined("IMAGE_ZOOM"))
	{
		$width*=IMAGE_ZOOM;
		$height*=IMAGE_ZOOM;
	}
	//关于ALIOSS的生成
	if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']=="ALI_OSS")
	{
		$pathinfo = pathinfo($img_path);
		$file = $pathinfo['basename'];
		$dir = $pathinfo['dirname'];
		$dir = str_replace("./public/", "/public/", $dir);

		if($width==0)
		{
			$file_name = $GLOBALS['distribution_cfg']['OSS_DOMAIN'].$dir."/".$file;
		}
		else if($height==0)
		{
			$file_name = $GLOBALS['distribution_cfg']['OSS_DOMAIN'].$dir."/".$file."@".$width."w_1l_1x.jpg";
		}
		else if($gen==0)
			$file_name = $GLOBALS['distribution_cfg']['OSS_DOMAIN'].$dir."/".$file."@".$width."w_".$height."h_0c_1e_1x.jpg"; //以短边缩放 1e 不剪裁
		else
			$file_name = $GLOBALS['distribution_cfg']['OSS_DOMAIN'].$dir."/".$file."@".$width."w_".$height."h_1c_1e_1x.jpg"; //以短边缩放 1e 剪裁
		return $file_name;
	}

	if($width==0||substr($img_path, 0,2)!="./")
		$new_path = $img_path;
	else
	{
		//$img_name = substr($img_path,0,-4);
		//$img_ext = substr($img_path,-3);
		$fileinfo = pathinfo($img_path);
		$img_ext = $fileinfo['extension'];
		$len = strlen($img_ext) + 1;
		$img_name =substr($img_path,0,-$len);

		if($is_preview)
			$new_path = $img_name."_".$width."x".$height.".jpg";
		else
			$new_path = $img_name."o_".$width."x".$height.".jpg";
		if(!file_exists(APP_ROOT_PATH.$new_path))
		{
			require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
			$imagec = new es_imagecls();
			$thumb = $imagec->thumb(APP_ROOT_PATH.$img_path,$width,$height,$gen,true,"",$is_preview);

			if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']=="ES_FILE")
			{
				$paths = pathinfo($new_path);
				$path = str_replace("./","",$paths['dirname']);
				$filename = $paths['basename'];
				$pathwithoupublic = str_replace("public/","",$path);

				$file_array['path'] = $pathwithoupublic;
				$file_array['file'] = get_domain().APP_ROOT."/".$path."/".$filename;
				$file_array['name'] = $filename;
				$GLOBALS['curl_param']['images'][] = $file_array;
			}

		}
	}
	//return APP_ROOT."/test.php?path=".$new_path."&rand=".rand(1000000,9999999);
	return $new_path;
}


function get_spec_gif_anmation($url,$width,$height)
{
	require_once APP_ROOT_PATH."system/utils/gif_encoder.php";
	require_once APP_ROOT_PATH."system/utils/gif_reader.php";
	require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
	$gif = new GIFReader();
	$gif->load($url);
	$imagec = new es_imagecls();
	foreach($gif->IMGS['frames'] as $k=>$img)
	{
		$im = imagecreatefromstring($gif->getgif($k));
		$im = $imagec->make_thumb($im,$img['FrameWidth'],$img['FrameHeight'],"gif",$width,$height,$gen=1);
		ob_start();
		imagegif($im);
		$content = ob_get_contents();
		ob_end_clean();
		$frames [ ] = $content;
		$framed [ ] = $img['frameDelay'];
	}

	$gif_maker = new GIFEncoder (
			$frames,
			$framed,
			0,
			2,
			0, 0, 0,
			"bin"   //bin为二进制   url为地址
	);
	return $gif_maker->GetAnimation ( );
}
function isWeixin(){
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$is_weixin = strpos($agent, 'micromessenger') ? true : false ;
	if($is_weixin){
		return true;
	}else{
		return false;
	}
}

function getMConfig(){


	$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/mapi/");
	$m_config = $GLOBALS['cache']->get("m_config_sj");

	if($m_config===false)
	{
		$m_config = array();
		$sql = "select code,val from ".DB_PREFIX."m_config";
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $item){
			$m_config[$item['code']] = $item['val'];
		}
		$catalog_id = intval($m_config['catalog_id']);
		$event_cate_id = intval($m_config['event_cate_id']);
		$shop_cate_id = intval($m_config['shop_cate_id']);

		if ($catalog_id == 0){
			$m_config["catalog_id_name"] = "全部分类";
		}else{
			$m_config["catalog_id_name"] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$catalog_id);
		}

		if ($event_cate_id == 0){
			$m_config["event_cate_id_name"] = "全部分类";
		}else{
			$m_config["event_cate_id_name"] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".$event_cate_id);
		}

		if ($shop_cate_id == 0){
			$m_config["shop_cate_id_name"] = "全部分类";
		}else{
			$m_config["shop_cate_id_name"] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."shop_cate where id = ".$shop_cate_id);
		}

		/*
		 //支付列表
		$sql = "select pay_id as id, code, title as name, has_calc from ".DB_PREFIX."m_config_list where `group` = 1 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$payment_list = array();
		foreach($list as $item){
		$payment_list[] = array("id"=>$item['id'],"code"=>$item['code'],"name"=>$item['name'],"has_calc"=>$item['has_calc']);
		}
		$m_config['payment_list'] = $payment_list;
		*/

		$m_config['payment_list'] = array();

		//配置方式
		$sql = "select id, id as code, name, 1 as has_calc from ".DB_PREFIX."delivery";
		$list = $GLOBALS['db']->getAll($sql);
		$delivery_list = array();
		foreach($list as $item){
			$delivery_list[] = array("id"=>$item['id'],"code"=>$item['code'],"name"=>$item['name'],"has_calc"=>$item['has_calc']);
		}
		$m_config['delivery_list'] = $delivery_list;
		//$order_parm['delivery_list'] = $MConfig['delivery_list'];//$GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."delivery");

		//发票内容
		$sql = "select id, title as name from ".DB_PREFIX."m_config_list where `group` = 6 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$invoice_list = array();
		foreach($list as $item){
			$invoice_list[] = array("id"=>$item['id'],"name"=>$item['name']);
		}
		$m_config['invoice_list'] = $invoice_list;

		//配送日期选择
		$sql = "select code, title as name from ".DB_PREFIX."m_config_list where `group` = 2 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$delivery_time_list = array();
		foreach($list as $item){
			$delivery_time_list[] = array("id"=>$item['code'],"name"=>$item['name']);
		}
		$m_config['delivery_time_list'] = $delivery_time_list;



		//购物车信息提示
		$sql = "select code, title as name,money from ".DB_PREFIX."m_config_list where `group` = 3 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$yh = array();
		foreach($list as $item){
			$yh[] = array("info"=>$item['name'],"money"=>$item['money']);
		}
		$m_config['yh'] = $yh;


		//新闻公告
		$sql = "select code as title, title as content from ".DB_PREFIX."m_config_list where `group` = 4 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$newslist = array();
		foreach($list as $item){
			$newslist[] = array("title"=>$item['title'],"content"=>$item['content']);
		}
		$m_config['newslist'] = $newslist;


		//地址标题
		$sql = "select code, title from ".DB_PREFIX."m_config_list where `group` = 5 and is_verify = 1";
		$list = $GLOBALS['db']->getAll($sql);
		$addrtlist = array();
		foreach($list as $item){
			$addrtlist[] = array("code"=>$item['code'],"title"=>$item['title']);
		}
		$m_config['addr_tlist'] = $addrtlist;

		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/mapi/");
		$GLOBALS['cache']->set("m_config_sj",$m_config,3600);

	}
	return $m_config;
}



function send_msg_item($msg_item)
{

	if($msg_item)
	{
		//优先改变发送状态,不论有没有发送成功
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_send = 1,send_time='".NOW_TIME."' where id =".intval($msg_item['id']));
		if($msg_item['send_type']==0)
		{
			//短信
			require_once APP_ROOT_PATH."system/utils/es_sms.php";
			$sms = new sms_sender();
			$result = $sms->sendSms($msg_item['dest'],$msg_item['content']);
			//发送结束，更新当前消息状态
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = ".intval($result['status']).",result='".$result['msg']."' where id =".intval($msg_item['id']));
			if(!$result)
				return false;
		}

		if($msg_item['send_type']==1)
		{
			//邮件
			require_once APP_ROOT_PATH."system/utils/es_mail.php";
			$mail = new mail_sender();

			$mail->AddAddress($msg_item['dest']);
			$mail->IsHTML($msg_item['is_html']); 				  // 设置邮件格式为 HTML
			$mail->Subject = $msg_item['title'];   // 标题
			$mail->Body = $msg_item['content'];  // 内容

			$is_success = $mail->Send();
			$result = $mail->ErrorInfo;

			//发送结束，更新当前消息状态
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = ".intval($is_success).",result='".$result."' where id =".intval($msg_item['id']));
			if(!$is_success)
				return false;
		}
		
		
		if($msg_item['send_type']==2)
		{			
			
			$msg_result = unserialize($msg_item['content']);
			$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where id = '".$msg_result['account_id']."'");
			$openid = $msg_item['dest'];			
			
			require_once APP_ROOT_PATH."system/wechat/platform_wechat.class.php";
		
			$option = array();
			$option['authorizer_access_token']=$wx_account['authorizer_access_token'];
			$option['authorizer_access_token_expire']=$wx_account['expires_in'];
			$option['authorizer_appid']=$wx_account['authorizer_appid'];
			$option['authorizer_refresh_token']=$wx_account['authorizer_refresh_token'];
			$platform= new PlatformWechat($option);
			$platform->check_platform_authorizer_token();
		
		
		
		
			$info=array(
					'touser'=>$openid,
					'template_id'=>$msg_result['template_id'],
					'url'=>$msg_result['url'],
					'topcolor'=>'#000000',
					'data'=>$msg_result['data']
			);
			$result=$platform->sendTemplateMessage($info);
			if($result){
				if(isset($result['errcode'])&&$result['errcode']>0){
					$is_success = 0;
					$err = $result['errMsg'];
				}else{
					$is_success = 1;
					$err = "";
				}
			}else{
				$is_success = 0;
				$err = "通讯失败";
			}
			
			//发送结束，更新当前消息状态
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = ".intval($is_success).",result='".$err."' where id =".intval($msg_item['id']));
			if(!$is_success)
				return false;
		}

		//发送类型 0:短信 1:邮件;2:微信;3:android,4:ios
		if($msg_item['send_type']==3 && !empty($msg_item['dest']))
		{
			require_once(APP_ROOT_PATH. 'system/umeng/notification/android/AndroidUnicast.php');
			try {
				$appMasterSecret = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'android_biz_master_secret'");
				$appkey = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'android_biz_app_key'");

				$title = $msg_item['title'];
				if (empty($title)){
					$title = app_conf("SHOP_TITLE");
				}

				$unicast = new AndroidUnicast();
				$unicast->setAppMasterSecret($appMasterSecret);
				$unicast->setPredefinedKeyValue("appkey",           $appkey);
				$unicast->setPredefinedKeyValue("timestamp",        strval(time()));// 必填 时间戳，10位或者13位均可，时间戳有效期为10分钟 NOW_TIME
				// Set your device tokens here
				$unicast->setPredefinedKeyValue("device_tokens",    trim($msg_item['dest']));
				$unicast->setPredefinedKeyValue("ticker",           $msg_item['content']);//必填 通知栏提示文字
				$unicast->setPredefinedKeyValue("title",            $title);// 必填 通知标题
				$unicast->setPredefinedKeyValue("text",             $msg_item['content']);// 必填 通知文字描述
				$unicast->setPredefinedKeyValue("after_open",       "go_app");//"go_app": 打开应用;"go_url": 跳转到URL;"go_activity": 打开特定的activity;"go_custom": 用户自定义内容。
				// Set 'production_mode' to 'false' if it's a test device.
				// For how to register a test device, please see the developer doc.
				$unicast->setPredefinedKeyValue("production_mode", "true");//可选 正式/测试模式。测试模式下，只会将消息发给测试设备。
				// Set extra fields
				//$unicast->setExtraField("test", "helloworld");
				//print("Sending unicast notification, please wait...\r\n");
				//json_decode($data) {"ret":"SUCCESS","data":{"msg_id":"uu05362143574400482600"}}
				$result = $unicast->send();
				//print_r($result);
				$res = json_decode($result,1);
				//print("Sent SUCCESS\r\n");
				if ($res['ret'] == 'SUCCESS'){
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = 1,result='".addslashes(print_r($result,true))."' where id =".intval($msg_item['id']));
				}else{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = 0,result='".addslashes(print_r($result,true))."' where id =".intval($msg_item['id']));
					return false;
				}

			} catch (Exception $e) {
				//print("Caught exception: " . $e->getMessage());
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = 0,result='".addslashes($e->getMessage())."' where id =".intval($msg_item['id']));
				return false;
			}
				
		}


		//发送类型 0:短信 1:邮件;2:微信;3:android,4:ios
		if($msg_item['send_type']==4)
		{
			require_once(APP_ROOT_PATH. 'system/umeng/notification/ios/IOSUnicast.php');
				
			try {
				$appMasterSecret = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'ios_biz_master_secret'");
				$appkey = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'ios_biz_app_key'");


				$unicast = new IOSUnicast();
				$unicast->setAppMasterSecret($appMasterSecret);
				$unicast->setPredefinedKeyValue("appkey",           $appkey);
				$unicast->setPredefinedKeyValue("timestamp",        strval(time()));
				// Set your device tokens here
				$unicast->setPredefinedKeyValue("device_tokens",    $msg_item['dest']);
				$unicast->setPredefinedKeyValue("alert", $msg_item['content']);
				$unicast->setPredefinedKeyValue("badge", 1);
				$unicast->setPredefinedKeyValue("sound", "chime");
				// Set 'production_mode' to 'true' if your app is under production mode
				$unicast->setPredefinedKeyValue("production_mode", "true");
				$result = $unicast->send();

				$res = json_decode($result,1);
				//print("Sent SUCCESS\r\n");
				if ($res['ret'] == 'SUCCESS'){
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = 1,result='".addslashes(print_r($result,true))."' where id =".intval($msg_item['id']));
				}else{
					$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = 0,result='".addslashes(print_r($result,true))."' where id =".intval($msg_item['id']));
					return false;
				}
					
			} catch (Exception $e) {
				//print("Caught exception: " . $e->getMessage());
				$GLOBALS['db']->query("update ".DB_PREFIX."deal_msg_list set is_success = 0,result='".addslashes($e->getMessage())."' where id =".intval($msg_item['id']));
				return false;
			}

		}
	}
	else
	{
		return false;
	}
	
	return true;
}




/**
 * 按宽度格式化html内容中的图片
 * @param unknown_type $content
 * @param unknown_type $width
 * @param unknown_type $height
 */
function format_html_content_image($content,$width,$height=0)
{
    $res = preg_match_all("/<img.*?src=[\"|\']([^\"|\']*)[\"|\'][^>]*>/i", $content, $matches);
    if($res)
    {
        foreach($matches[0] as $k=>$match)
        {
            $old_path = $matches[1][$k];
            if(preg_match("/\.\/public\//i", $old_path))
            {
            	$origin_path = $matches[1][$k];
                $new_path = get_spec_image($matches[1][$k],$width,$height,0);
                $content = str_replace($match, "<a href='".$origin_path."'><img src='".$new_path."' lazy='true' /></a>", $content);
            }
        }
    }

    return $content;
}
/**
 * 带域名连接替换成public
 * @param unknown $str
 * @return mixed
 */
function replace_domain_to_public($str){
    //对图片路径的修复
    if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
    {
        $domain = $GLOBALS['distribution_cfg']['OSS_DOMAIN'];
    }
    else
    {
        $domain = SITE_DOMAIN.APP_ROOT;
    }

    return str_replace($domain."/public/","./public/",$str);
}

function check_remote_file_exists($url)
{
	$curl = curl_init($url);
	// 不取回数据
	curl_setopt($curl, CURLOPT_NOBODY, true);
	// 发送请求
	$result = curl_exec($curl);
	$found = false;
	// 如果请求没有发送失败
	if ($result !== false) {
		// 再检查http响应码是否为200
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($statusCode == 200) {
			$found = true;
		}
	}
	curl_close($curl);

	return $found;
}

/**
 * 通过curl下载文件到指定位置
 * @param unknown_type $file 远程文件
 * @param unknown_type $dest 存储位置
 */
function curl_download($file,$dest)
{
	$ch = curl_init($file);
	$fp = fopen($dest, "wb");
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$res=curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	return $res;
}

function gen_scan_qrcode($url,$size=3)
{
	if(substr($url, 0,1)=="/")
	{
		$url = SITE_DOMAIN.$url;
	}
	return gen_qrcode($url,$size);
}


//添加发送消息到列队
function send_msg_item_add($tmpl,$user_info,$msg_data){
	
	if($tmpl['is_allow_wx'] == 1){
		$msg_data['send_type'] = 2;//发送类型 0:短信 1:邮件;2:微信;3:app
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
		if(APP_INDEX!="index")
		{
			$msg_data['id'] = $GLOBALS['db']->insert_id();
			send_msg_item($msg_data);
		}
	}
	else
	{
		if(app_conf("SMS_ON")==1 && !empty($msg_data['dest'])){
			$msg_data['send_type'] = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
			if(APP_INDEX!="index")
			{
				$msg_data['id'] = $GLOBALS['db']->insert_id();
				send_msg_item($msg_data);
			}
		}
	
		
	
		if($tmpl['is_allow_app'] == 1 && !empty($user_info['dev_type']) && !empty($user_info['device_token'])){
			if ($user_info['dev_type'] == 'ios'){
				$msg_data['send_type'] = 4;//发送类型 0:短信 1:邮件;2:微信;3:android,4:ios
			}else{
				$msg_data['send_type'] = 3;//发送类型 0:短信 1:邮件;2:微信;3:android,4:ios
			}
			$msg_data['dest'] = $user_info['device_token'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
			if(APP_INDEX!="index")
			{
				$msg_data['id'] = $GLOBALS['db']->insert_id();
				send_msg_item($msg_data);
			}
		}
	}
}

/**
 * 合并adm_cfg中的配置文件
 * @param unknown_type $config  //原配置
 * @param unknown_type $config_file  //新配置文件
 */
function array_merge_admnav($config,$config_file)
{
	if(!file_exists($config_file))
	{
		return $config;
	}
	$new_config = require $config_file;
	foreach($new_config as $k=>$v)
	{
		if($config[$k])
		{
			foreach($v['groups'] as $kk=>$vv)
			{
				if($config[$k]['groups'][$kk])
				{
					foreach($vv['nodes'] as $kkk=>$vvv)
					{
						$config[$k]['groups'][$kk]['nodes'][] = $vvv;
					}
				}
				else
				{
					$config[$k]['groups'][$kk] = $vv;
				}
			}
		}
		else
		{
			$config[$k] = $v;
		}
	}
	return $config;
}


/**
 * 合并adm_node中的配置文件
 * @param unknown_type $config  //原配置
 * @param unknown_type $config_file  //新配置文件
 */
function array_merge_admnode($config,$config_file)
{
	if(!file_exists($config_file))
	{
		return $config;
	}
	$new_config = require $config_file;
	foreach($new_config as $k=>$v)
	{
		if($config[$k])
		{
			foreach($v['node'] as $kk=>$vv)
			{
				$config[$k]['node'][$kk] = $vv;
			}
		}
		else
		{
			$config[$k] = $v;
		}
	}
	return $config;
}

/**
 * 合并biz_nav中的配置文件
 * @param unknown_type $config  //原配置
 * @param unknown_type $config_file  //新配置文件
 */
function array_merge_biznav($config,$config_file)
{
	if(!file_exists($config_file))
	{
		return $config;
	}
	$new_config = require $config_file;
	foreach($new_config as $k=>$v)
	{
		if($config[$k])
		{
			foreach($v['node'] as $kk=>$vv)
			{
				$config[$k]['node'][$kk] = $vv;
			}
		}
		else
		{
			$config[$k] = $v;
		}
	}
	return $config;
}

/**
 * 合并biz_node中的配置文件
 * @param unknown_type $config  //原配置
 * @param unknown_type $config_file  //新配置文件
 */
function array_merge_biznode($config,$config_file)
{
	if(!file_exists($config_file))
	{
		return $config;
	}
	$new_config = require $config_file;
	foreach($new_config as $k=>$v)
	{
		if($config[$k])
		{
			foreach($v['node'] as $kk=>$vv)
			{
				$config[$k]['node'][$kk] = $vv;
			}
		}
		else
		{
			$config[$k] = $v;
		}
	}
	return $config;
}

/**
 * 合并mobile_cfg中的配置文件
 * @param unknown_type $config  //原配置
 * @param unknown_type $config_file  //新配置文件
 */
function array_merge_mobile_cfg($config,$config_file)
{
	if(!file_exists($config_file))
	{
		return $config;
	}
	$new_config = require $config_file;
	foreach($new_config as $k=>$v)
	{
		if($config[$k])
		{
			foreach($v['nav'] as $kk=>$vv)
			{
				$config[$k]['nav'][$kk] = $vv;
			}
		}
		else
		{
			$config[$k] = $v;
		}
	}
	return $config;
}

/**
 * 合并web_cfg_web_nav中的配置文件
 * @param unknown_type $config  //原配置
 * @param unknown_type $config_file  //新配置文件
 */
function array_merge_web_nav($config,$config_file)
{
	if(!file_exists($config_file))
	{
		return $config;
	}
	$new_config = require $config_file;
	foreach($new_config as $k=>$v)
	{
		if($config[$k])
		{
			foreach($v['acts'] as $kk=>$vv)
			{
				$config[$k]['acts'][$kk] = $vv;
			}
		}
		else
		{
			$config[$k] = $v;
		}
	}
	return $config;
}


/**
 * 合并uc_node中的配置文件
 * @param unknown_type $config  //原配置
 * @param unknown_type $config_file  //新配置文件
 */
function array_merge_ucnode($config,$config_file)
{
	if(!file_exists($config_file))
	{
		return $config;
	}
	$new_config = require $config_file;
	foreach($new_config as $k=>$v)
	{
		if($config[$k])
		{
			foreach($v['node'] as $kk=>$vv)
			{
				$config[$k]['node'][$kk] = $vv;
			}
		}
		else
		{
			$config[$k] = $v;
		}
	}
	return $config;
}



//以下是微信公众平台的消息发送函数

/**
 * 获取微信消息模板的内容
 * @param unknown_type $template_id 模板ID，详见wx_template_cfg.php
 * @param unknown_type $tmpl  对应的DB中的模板数据集
 * @param unknown_type $param 对应ID传入的参数
 * 
 * 返回
 * array(
 * 	status=>必返回   info=>status为false时返回  url=>可为空，表示消息的跳转页  data=>必返回，为指定模板的实际内容
 * )
 * 
 */
function get_wx_msg_content($template_id,$tmpl,$user_id,$wx_account,$param=array())
{	
	$data=unserialize($tmpl['msg']);
	
	switch ($template_id)
	{
		case "OPENTM201490080": //订单支付成功模板
			if(empty($param))
			{
// 				{{first.DATA}}
// 				订单编号：{{keyword1.DATA}}
// 				商品详情：{{keyword2.DATA}}
// 				订单金额：{{keyword3.DATA}}
// 				{{remark.DATA}}
				$data['keyword1']=array('value'=>'00000000','color'=>'#000000');
				$data['keyword2']=array('value'=>'这是一款测试的商品','color'=>'#000000');
				$data['keyword3']=array('value'=>'100元','color'=>'#000000');
				$url = SITE_DOMAIN.wap_url("index");
				return array("status"=>true,"url"=>$url,"data"=>$data);
			}
			else
			{
				$order_id = intval($param['order_id']);
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
				if(empty($order_info))
				{
					return array("status"=>false,"info"=>"订单不存在");
				}
				$deal_order_items = $GLOBALS['db']->getAll("select name,sub_name from ".DB_PREFIX."deal_order_item where order_id = ".$order_id." and supplier_id = ".$wx_account['user_id']);
				if(empty($deal_order_items))
				{
					return array("status"=>false,"info"=>"订单不存在");
				}
				$order_price = round($order_info['total_price'],2)."元";
				if(count($deal_order_items)>1)
				{
					$item_name = $deal_order_items[0]['sub_name']."等";
				}
				else
				{
					$item_name = $deal_order_items[0]['sub_name'];
				}
				
				$data['keyword1']=array('value'=>$order_info['order_sn'],'color'=>'#000000');
				$data['keyword2']=array('value'=>$item_name,'color'=>'#000000');
				$data['keyword3']=array('value'=>$order_price,'color'=>'#000000');
				$url = SITE_DOMAIN.wap_url("index","uc_order",array("pay_status"=>2,"id"=>$order_id));
				return array("status"=>true,"url"=>$url,"data"=>$data);
			}
			break;
		case "TM00430": //退款成功通知
			if(empty($param))
			{
// 				{{first.DATA}}
				
// 				退款金额：{{orderProductPrice.DATA}}
// 				商品详情：{{orderProductName.DATA}}
// 				订单编号：{{orderName.DATA}}
// 				{{remark.DATA}}
				$data['orderProductPrice']=array('value'=>'100元','color'=>'#000000');
				$data['orderProductName']=array('value'=>'这是一款测试的商品','color'=>'#000000');
				$data['orderName']=array('value'=>'00000000','color'=>'#000000');
				$url = SITE_DOMAIN.wap_url("index");
				return array("status"=>true,"url"=>$url,"data"=>$data);
			}
			else
			{
				$order_id = intval($param['order_id']);
				$refund_price = round($param['refund_price'],2)."元";
				$deal_name = strim($param['deal_name']);
				$order_sn = strim($param['order_sn']);
				
				$data['orderProductPrice']=array('value'=>$refund_price,'color'=>'#000000');
				$data['orderProductName']=array('value'=>$deal_name,'color'=>'#000000');
				$data['orderName']=array('value'=>$order_sn,'color'=>'#000000');
				$url = SITE_DOMAIN.wap_url("index","uc_order",array("pay_status"=>2,"id"=>$order_id));
				return array("status"=>true,"url"=>$url,"data"=>$data);
			}
			break;
		case "OPENTM200565259": //订单发货提醒
			if(empty($param))
			{
// 				{{first.DATA}}
// 				订单编号：{{keyword1.DATA}}
// 				物流公司：{{keyword2.DATA}}
// 				物流单号：{{keyword3.DATA}}
// 				{{remark.DATA}}
				$data['keyword1']=array('value'=>'00000000','color'=>'#000000');
				$data['keyword2']=array('value'=>'顺风快递','color'=>'#000000');
				$data['keyword3']=array('value'=>'00000000','color'=>'#000000');
				$url = SITE_DOMAIN.wap_url("index");
				return array("status"=>true,"url"=>$url,"data"=>$data);
			}
			else
			{
				$order_id = intval($param['order_id']);
				$order_sn = strim($param['order_sn']);
				$company_name = strim($param['company_name']);
				$delivery_sn = strim($param['delivery_sn']);
				$order_item_id = intval($param['order_item_id']);
				
				$data['keyword1']=array('value'=>$order_sn,'color'=>'#000000');
				$data['keyword2']=array('value'=>$company_name,'color'=>'#000000');
				$data['keyword3']=array('value'=>$delivery_sn,'color'=>'#000000');
				$url = SITE_DOMAIN.wap_url("index","uc_order#check_delivery",array("item_id"=>$order_item_id));
				return array("status"=>true,"url"=>$url,"data"=>$data);
			}
			break;
		case "OPENTM202314085": //订单确认收货通知
			if(empty($param))
			{
// 				{{first.DATA}}
// 				订单号：{{keyword1.DATA}}
// 				商品名称：{{keyword2.DATA}}
// 				下单时间：{{keyword3.DATA}}
// 				发货时间：{{keyword4.DATA}}
// 				确认收货时间：{{keyword5.DATA}}
// 				{{remark.DATA}}
				$data['keyword1']=array('value'=>'00000000','color'=>'#000000');
				$data['keyword2']=array('value'=>'这是一款测试商品','color'=>'#000000');
				$data['keyword3']=array('value'=>'2015-07-01 12:00:00','color'=>'#000000');
				$data['keyword4']=array('value'=>'2015-07-01 14:00:00','color'=>'#000000');
				$data['keyword5']=array('value'=>'2015-07-05 14:00:00','color'=>'#000000');
				$url = SITE_DOMAIN.wap_url("index");
				return array("status"=>true,"url"=>$url,"data"=>$data);
			}
			else
			{
				$order_item_id = intval($param['order_item_id']);
				$order_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$order_item_id);
				$order_id = $order_item['order_id'];
				if(empty($order_item))
				{
					return array("status"=>false,"info"=>"订单不存在");
				}
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
				if(empty($order_info))
				{
					return array("status"=>false,"info"=>"订单不存在");
				}
				$delivery_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where order_item_id = ".$order_item_id);
				if(empty($delivery_notice))
				{
					return array("status"=>false,"info"=>"订单不存在");
				}
				
				
				$total_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."delivery_notice where notice_sn = '".$delivery_notice['notice_sn']."' and order_id=".$order_id." and is_arrival = 1");
				if($total_count>1)
				{
					$deal_name = $order_item['sub_name']."等";
				}
				else
				{
					$deal_name = $order_item['sub_name'];
				}
				$data['keyword1']=array('value'=>$order_info['order_sn'],'color'=>'#000000');
				$data['keyword2']=array('value'=>$deal_name,'color'=>'#000000');
				$data['keyword3']=array('value'=>to_date($order_info['create_time']),'color'=>'#000000');
				$data['keyword4']=array('value'=>to_date($delivery_notice['delivery_time']),'color'=>'#000000');
				$data['keyword5']=array('value'=>to_date(NOW_TIME),'color'=>'#000000');
				$url = SITE_DOMAIN.wap_url("index","uc_order",array("pay_status"=>2,"id"=>$order_id));
				return array("status"=>true,"url"=>$url,"data"=>$data);
				
			}
			break;
		case "OPENTM200738546": //电子凭证验证成功通知
			if(empty($param))
			{
// 				{{first.DATA}}
// 				凭证类型：{{keyword1.DATA}}
// 				凭证属性：{{keyword2.DATA}}
// 				验证时间：{{keyword3.DATA}}
// 				{{remark.DATA}}
				$data['keyword1']=array('value'=>'xxxxx电子凭证','color'=>'#000000');
				$data['keyword2']=array('value'=>'电子券','color'=>'#000000');
				$data['keyword3']=array('value'=>'2015-07-01 12:00:00','color'=>'#000000');
				$url = "";
				return array("status"=>true,"url"=>$url,"data"=>$data);
			}
			else
			{
				$coupon_sn = strim($param['coupon_sn']);
				$coupon = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_coupon where password ='".$coupon_sn."'");
				if(empty($coupon))
				{
					return array("status"=>false,"info"=>"电子券不存在");
				}
				if($coupon['confirm_time']==0)
				{
					return array("status"=>false,"info"=>"电子券未使用");
				}
				$deal_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order_item where id = ".$coupon['order_deal_id']);
				
				if(empty($deal_item))
				{
					return array("status"=>false,"info"=>"订单不存在");
				}
				
				$data['keyword1']=array('value'=>$deal_item['sub_name'].'电子凭证['.$coupon_sn.']','color'=>'#000000');
				$data['keyword2']=array('value'=>'电子券','color'=>'#000000');
				$data['keyword3']=array('value'=>to_date($coupon['confirm_time']),'color'=>'#000000');
				$url = "";
				return array("status"=>true,"url"=>$url,"data"=>$data);
					
			}
			break;
		default:
			return array("status"=>false,"info"=>"模板编号不存在");
			break;
	}
	
}


/**
 * 发送微信消费
 * @param unknown_type $template_id_short 模板类型 的ID，即模板编号
 * @param unknown_type $user_id  会员ID
 * @param unknown_type $wx_account 公众平台授权帐号
 * @param unknown_type $param 不同模板类型传入的参数，在get_wx_msg_content函数中细分，不传为演示
 * @return array(status,info);
 */
function send_wx_msg($template_id_short,$user_id,$wx_account,$param=array())
{
	
	$weixin_conf = load_auto_cache("weixin_conf");
	if(!$weixin_conf['platform_status'])
	{
		return array(
			"status" => false,
			"info" => "平台功能未开通"
		);
	}

	$openid =  $GLOBALS['db']->getOne("select openid from ".DB_PREFIX."weixin_user where user_id = '".$user_id."' and account_id = ".$wx_account['user_id']);		
	if(!$openid)
	{
		return array(
				"status" => false,
				"info" => "微信用户未授权"
		);
	}
	if(!$wx_account)
	{
		return array(
				"status" => false,
				"info" => "公众号未授权"
		);
	}
	
	$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_tmpl where account_id = '".$wx_account['user_id']."' and template_id_short = '".$template_id_short."'");
	if(!$tmpl)
	{
		return array(
				"status" => false,
				"info" => "未安装该消息模板"
		);
	}
	
	$result = get_wx_msg_content($template_id_short,$tmpl,$user_id,$wx_account,$param);
	if(!$result['status'])
	{
		return $result;
	}
	
	if($param)
	{
		require_once APP_ROOT_PATH."system/model/user.php";
		$user_info = load_user($user_id);
		$tmpl['is_allow_wx'] = 1;
		$msg_data = array();
		$msg_result = array();
		$msg_data['dest'] = $openid;
		$msg_data['send_type'] = 2;		
		$msg_result['url'] = $result['url'];
		$msg_result['data'] = $result['data'];
		$msg_result['template_id'] = $tmpl['template_id'];
		$msg_result['account_id'] = $wx_account['id'];
		$msg_data['content'] = serialize($msg_result);
		send_msg_item_add($tmpl, $user_info, $msg_data);
	}
	else
	{
		require_once APP_ROOT_PATH."system/wechat/platform_wechat.class.php";
		
		$option = array();
		$option['authorizer_access_token']=$wx_account['authorizer_access_token'];
		$option['authorizer_access_token_expire']=$wx_account['expires_in'];
		$option['authorizer_appid']=$wx_account['authorizer_appid'];
		$option['authorizer_refresh_token']=$wx_account['authorizer_refresh_token'];
		$platform= new PlatformWechat($option);
		$platform->check_platform_authorizer_token();
			
		
		
			
		$info=array(
				'touser'=>$openid,
				'template_id'=>$tmpl['template_id'],
				'url'=>$result['url'],
				'topcolor'=>'#000000',
				'data'=>$result['data']
		);
		$result=$platform->sendTemplateMessage($info);
		if($result){
			if(isset($result['errcode'])&&$result['errcode']>0){
				return array(
						"status" => false,
						"info" => $result['errMsg']
				);
			}else{
				return array(
						"status" => true,
						"info" => "发送成功"
				);
			}
		}else{
			return array(
					"status" => false,
					"info" => "通讯失败"
			);
		}
	}
}



//end微信公众平台消息发送
/**
 * 验证关键词的是否重复
 * @param unknown_type $keywords
 * @param unknown_type $reply_id
 * @param unknown_type $match_type
 */
function word_check($keywords,$reply_id = 0,$match_type = 0,$supplier_id = 0)
{
	if($match_type == 0){
		$keywords = preg_split("/[ ,]/i",$keywords);
		$exists_keywords = array();
		foreach($keywords as $tag){
			$tag = trim($tag);
			if($tag != ''){
				$unicode_tag =  str_to_unicode_string(trim($tag));
					
				$condition =" account_id=".$supplier_id."  and id <> ".$reply_id." ";
				if($unicode_tag){
					$condition .= " and (match(keywords_match) AGAINST ('".$unicode_tag."' IN BOOLEAN MODE) or keywords = '".$tag."')";
					//$where['keywords_match'] = array('match',$unicode_tag);
				}
				$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."weixin_reply where ".$condition);
				if($count > 0){
					$exists_keywords[] = trim($tag);
					break;
				}
			}
		}
	}else{
		$keywords = trim($keywords);
		if($keywords != ''){
	
			
			$unicode_tag =  str_to_unicode_string(trim($keywords));
				
			$condition =" account_id=".$supplier_id."  and id <> ".$reply_id." ";
			if($unicode_tag){
				$condition .= " and (match(keywords_match) AGAINST ('".$unicode_tag."' IN BOOLEAN MODE) or keywords = '".$keywords."')";
			}
			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."weixin_reply where ".$condition);
			
				
			if($count > 0){
				$exists_keywords[] = $keywords;
			}
		}
	}
	return $exists_keywords;
}


/**
 * 同步公众号回复的索引
 * @param unknown_type $reply_id
 */
function syncMatch($reply_id){

	$reply_data['keywords_match'] = "";
	$reply_data['keywords_match_row'] = "";
	$GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply", $reply_data, $mode = 'UPDATE', "id=".$reply_id, $querymode = 'SILENT');

	$reply_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_reply where id = ".$reply_id);

	$keywords = $reply_data['keywords'];
	$keywords = preg_split("/[ ,]/i",$keywords);
	foreach($keywords as $tag)
	{
		insert_match_item(trim($tag),"weixin_reply",$reply_id,"keywords_match");
	}

}

function csrf_gate()
{
	$http_referer = $_SERVER['HTTP_REFERER'];
	if($http_referer)
	{
		if(strpos($http_referer, SITE_DOMAIN)!==0)
		{
			header("Content-Type:text/html; charset=utf-8");
			die("非法的操作访问");
		}
	}
	else
	{
		header("Content-Type:text/html; charset=utf-8");
		die("非法的操作访问");
	}
}
?>