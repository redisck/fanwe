<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 获取短信发送的倒计时
 */
function load_sms_lesstime()
{
	$data	=	es_session::get("send_sms_code_0_ip");
	$lesstime = SMS_TIMESPAN -(NOW_TIME - $data['time']);  //剩余时间
	if($lesstime<0)$lesstime=0;
	return $lesstime;
}
/**
 * 同一IP的短信验证码发送量，用于判断是否显示验证码
 */
function load_sms_ipcount()
{
	$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
	$GLOBALS['db']->query($sql);
	$ipcount = $GLOBALS['db']->getOne("select sum(send_count) from ".DB_PREFIX."sms_mobile_verify where ip = '".CLIENT_IP."'");
	return intval($ipcount);
}

function get_help()
{
	return load_auto_cache("get_help_cache");
}


//获取指定的文章分类列表
function get_acate_tree($pid = 0,$type_id=0,$act_name)
{
	return load_auto_cache("cache_shop_acate_tree",array("pid"=>$pid,"type_id"=>$type_id,"act_name"=>$act_name));
}

/**
 * 获取商城公告
 * //$notice_page 公告显示位置 0:全部 1:首页 2:商城 3:推荐
 */
function get_notice($limit=0,$notice_page=array(0))
{
	if($limit == 0)
		$limit = app_conf("INDEX_NOTICE_COUNT");
	if($limit>0)
	{
		$limit_str = "limit ".$limit;
	}
	else
	{
		$limit_str = "";
	}
	$list = $GLOBALS['db']->getAll("select a.*,ac.type_id from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.notice_page in (".implode(",",$notice_page).") and ac.type_id = 2 and ac.is_effect = 1 and ac.is_delete = 0 and a.is_effect = 1 and a.is_delete = 0 order by a.sort desc ".$limit_str);

	foreach($list as $k=>$v)
	{
		if($v['rel_url']!="")
		{
			$aurl = parse_url_tag($v['rel_url']);
		}
		else
		{
			if($v['type_id']==0){
				$module = 'article';
			}elseif($v['type_id']==1){
				$module = 'help';
			}elseif($v['type_id']==2){
				$module = 'notice';
			}elseif($v['type_id']==3){
				$module = 'sys_article';
			}
			
			if($v['uname']!='')
				$aurl = url("index",$module."#".$v['uname']);
			else
				$aurl = url("index",$module."#".$v['id']);
		}
		$list[$k]['url'] = $aurl;
	}
	return $list;
}


/**
 * 针对模板进行配置的布局总宽度
 * @param unknown_type $type 0:默认宽度 1:首页宽度...
 */
function load_wrap($type=0)
{

	if(intval($type)==0)return "wrap_full main_layout";
	if(intval($type)==1)return "wrap_full_w main_layout";
}

/**
 * 加载png图片，主要用于模板端调用
 * @param unknown_type $img
 * @return boolean
 */
function load_page_png($img)
{
	return load_auto_cache("page_image",array("img"=>$img));
}

function get_nav_list()
{
	return load_auto_cache("cache_nav_list");
}

function init_nav_list($nav_list)
{
	foreach($nav_list as $k=>$v)
	{
		if($v['url']=='')
		{
			if($v['app_index']=="")$v['app_index']="index";
			if($v['u_module']=="")$v['u_module']="index";
			if($v['u_action']=="")$v['u_action']="index";
			
			$route = $v['u_module'];
			if($v['u_action']!='')
				$route.="#".$v['u_action'];

			$app_index = $v['app_index'];

			$str = "u:".$app_index."|".$route."|".$v['u_param'];
			$nav_list[$k]['url'] =  parse_url_tag($str);
			if(ACTION_NAME==$v['u_action']&&MODULE_NAME==$v['u_module']&&APP_INDEX==$v['app_index'])
			{
				$nav_list[$k]['current'] = 1;
			}
		}
	}
	return $nav_list;
}

/**
 * 获取导航菜单
 */
function format_nav_list($nav_list)
{
	foreach($nav_list as $k=>$v)
	{
		if($v['url']!='')
		{
			if(substr($v['url'],0,7)!="http://")
			{
				//开始分析url
				$nav_list[$k]['url'] = APP_ROOT."/".$v['url'];
			}
		}
	}
	return $nav_list;
}

/**
 * 加载下拉菜单的模板展示
 * count:允许显示的大类个数
 * @param unknown_type $type 0：生活服务分类  1:商城分类
 */
function load_cate_tree($count=0,$type=0)
{
	$navs = load_auto_cache("cache_cate_tree",array("type"=>$type));
	
	$GLOBALS['tmpl']->assign("count",$count);
	$GLOBALS['tmpl']->assign("cate_tree",$navs);
	return $GLOBALS['tmpl']->fetch("inc/cate_tree.html");
}

/**
 * 关于页面初始化时需要输出的信息
 * 全属使用的模板信息输出
 * 1. seo 基本信息
 * $GLOBALS['tmpl']->assign("shop_info",get_shop_info());
 * 2. 当前城市名称, 单城市不显示
 * 3. 输出APP_ROOT
 */
function init_app_page()
{
	//输出根路径
	$GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);
	
	//定义当前语言包
	$GLOBALS['tmpl']->assign("LANG",$GLOBALS['lang']);
	
	$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);

	$GLOBALS['tmpl']->assign("deal_city",$GLOBALS['city']);
	
	//开始输出site_seo
	$site_seo['keyword']	=	$GLOBALS['city']['seo_keyword']==''?app_conf('SHOP_KEYWORD'):$GLOBALS['city']['seo_keyword'];
	$site_seo['description']	= $GLOBALS['city']['seo_description']==''?app_conf('SHOP_DESCRIPTION'):$GLOBALS['city']['seo_description'];
	$site_seo['title']  = app_conf("SHOP_TITLE");
	$seo_title =	$GLOBALS['city']['seo_title']==''?app_conf('SHOP_SEO_TITLE'):$GLOBALS['city']['seo_title'];
	if($seo_title!="")$site_seo['title'].=" - ".$seo_title;
	$GLOBALS['tmpl']->assign("site_seo",$site_seo);
	
	
	//输出导航菜单
	$nav_list = get_nav_list();
	$nav_list= init_nav_list($nav_list);
	$GLOBALS['tmpl']->assign("nav_list",$nav_list);
	
	//输出热门关键词
	$hot_kw = app_conf("SHOP_SEARCH_KEYWORD");
	$hot_kw = preg_split("/[ ,]/i",$hot_kw);	
	$hot_kws = array();
	foreach($hot_kw as $k=>$v)
	{
		$hot_kws[$k]['url'] = url("index","search#jump",array("kw"=>$v));
		$hot_kws[$k]['txt'] = $v;
	}
	$GLOBALS['tmpl']->assign("hot_kw",$hot_kws);
	
	//输出接收到的关键词
	global $kw;
	$kw = strim($_REQUEST['kw']);
	$GLOBALS['tmpl']->assign("kw",$kw);
	
	//输出帮助
	$deal_help = get_help();
	$GLOBALS['tmpl']->assign("deal_help",$deal_help);

	//输出城市列表
	$city_list = load_auto_cache("city_list_result");
	$GLOBALS['tmpl']->assign("city_count",count($city_list['ls']));
	$GLOBALS['tmpl']->assign("city_list",$city_list['ls']);
	
	//定义展示的下拉菜单类型
	if(
		MODULE_NAME=="mall"||
		MODULE_NAME=="cate"
	)
	{
		$cate_tree_type = 1;
	}
	elseif(
		MODULE_NAME=="scores"
	)
	{
		$cate_tree_type = 2;
	}
	elseif(
			MODULE_NAME=="youhuis"||
			MODULE_NAME=="youhui"
	)
	{
		$cate_tree_type = 3;
	}
	elseif(
			MODULE_NAME=="stores"||
			MODULE_NAME=="store"
	)
	{
		$cate_tree_type = 5;
	}
	else
	{
		$cate_tree_type = 0;
	}
	$GLOBALS['tmpl']->assign("cate_tree_type",$cate_tree_type);
	//定义search_type的默认项
	if(
		MODULE_NAME=="tuan"
	)
	{
		$search_type = 0;
	}
	elseif(
			MODULE_NAME=="youhuis"||
			MODULE_NAME=="youhui"
	)
	{
		$search_type = 2;
	}
	elseif(
			MODULE_NAME=="events"||
			MODULE_NAME=="event"
	)
	{
		$search_type = 3;
	}
	elseif(
			MODULE_NAME=="store"||
			MODULE_NAME=="stores"
	)
	{
		$search_type = 4;
	}
	elseif(
			MODULE_NAME=="cate"||
			MODULE_NAME=="mall"
	)
	{
		$search_type = 5;
	}
	elseif(
		MODULE_NAME=="discover"||MODULE_NAME=="topic"||MODULE_NAME=="group"||MODULE_NAME=="daren"
	)
	{
		$search_type = 6;
	}
	else
	{
		$search_type = 0;
	}
	$GLOBALS['tmpl']->assign("search_type",$search_type);
	
	//输出在线客服与时间
	$qq = explode("|",app_conf("ONLINE_QQ"));
	$GLOBALS['tmpl']->assign("online_qq",$qq);
	/*
	
	//输出系统文章
	$system_article = get_article_list(8,0,"ac.type_id = 3","",true);
	$GLOBALS['tmpl']->assign("system_article",$system_article['list']);
	
	
	
	
	
	
	*/
	
}


/**
 * 前端全运行函数，生成系统前台使用的全局变量
 * 1. 定位城市 GLOBALS['city'];
 * 2. 加载会员 GLOBALS['user_info'];
 * 3. 生成语言包
 * 4. 加载推荐人与来路
 * 5. 更新购物车
 */
function global_run()
{
	if(app_conf("SHOP_OPEN")==0)  //网站关闭时跳转到站点关闭页
	{
		app_redirect(url("index","close"));
	}
	
	
	
	//处理城市
	global $city;
	require_once APP_ROOT_PATH."system/model/city.php";
	$city = City::locate_city();
	
	global $geo;
	$geo = City::locate_geo(floatval($_REQUEST['xpoint']),floatval($_REQUEST['ypoint']));
	
	//输出语言包的js
	if(!file_exists(get_real_path()."public/runtime/app/lang.js"))
	{
		$str = "var LANG = {";
		foreach($GLOBALS['lang'] as $k=>$lang_row)
		{
			$str .= "\"".$k."\":\"".str_replace("nbr","\\n",addslashes($lang_row))."\",";
		}
		$str = substr($str,0,-1);
		$str .="};";
		@file_put_contents(get_real_path()."public/runtime/app/lang.js",$str);
	}
	
	//会员自动登录及输出
	global $user_info;
	global $user_logined; 
	require_once APP_ROOT_PATH."system/model/user.php";
	$user_info = es_session::get('user_info');
	if(empty($user_info))
	{
		$cookie_uname = es_cookie::get("user_name")?es_cookie::get("user_name"):'';
		$cookie_upwd = es_cookie::get("user_pwd")?es_cookie::get("user_pwd"):'';
		if($cookie_uname!=''&&$cookie_upwd!=''&&!es_session::get("user_info"))
		{
			$cookie_uname = strim($cookie_uname);
			$cookie_upwd = strim($cookie_upwd);			
			auto_do_login_user($cookie_uname,$cookie_upwd);
			$user_info = es_session::get('user_info');
		}	
	}
	refresh_user_info();
	
	//刷新购物车
	require_once APP_ROOT_PATH."system/model/cart.php";
	refresh_cart_list();

	global $ref_uid;
	
	//保存返利的cookie
	if($_REQUEST['r'])
	{
		$rid = intval(base64_decode($_REQUEST['r']));
		$ref_uid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval($rid)));
		es_cookie::set("REFERRAL_USER",intval($ref_uid));
	}
	else
	{
		//获取存在的推荐人ID
		if(intval(es_cookie::get("REFERRAL_USER"))>0)
			$ref_uid = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where id = ".intval(es_cookie::get("REFERRAL_USER"))));
	}
	
	global $referer;
	//保存来路
// 	es_cookie::delete("referer_url");
	if(!es_cookie::get("referer_url"))
	{
		if(!preg_match("/".urlencode(SITE_DOMAIN.APP_ROOT)."/",urlencode($_SERVER["HTTP_REFERER"])))
		{
			$ref_url = $_SERVER["HTTP_REFERER"];
			if(substr($ref_url, 0,7)=="http://"||substr($ref_url, 0,8)=="https://")
			{
				preg_match("/http[s]*:\/\/[^\/]+/", $ref_url,$ref_url);		
				$referer = $ref_url[0];
				if($referer)
				es_cookie::set("referer_url",$referer);
			}
		}			
	}
	else
	{
		$referer = es_cookie::get("referer_url");
	}
	$referer = strim($referer);
	
	//关于PC与WAP的自动跳转
	pc_wap_jump();

}

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
 * 验证会员字段的有效性
 * @param unknown_type $field  字段名称
 * @param unknown_type $value	字段内容
 * @param unknown_type $user_id	会员ID
 */
function check_field($field,$value,$user_id)
{
	require_once APP_ROOT_PATH."system/model/user.php";
	$data = array();
	$data['status'] = true;
	$data['info'] = "";
	$user_data['id'] = $user_id;
	if($field=="email")
	{		
		$check_rs = check_user("email",$value,$user_data);
		if(!$check_rs['status'])
		{
			$check_data = $check_rs['data'];
			if($check_data['error']==FORMAT_ERROR)
			{
				$data['status'] = false;
				$data['info'] = "邮箱格式不正确";
				$data['field'] = "email";
				return $data;
			}
			if($check_data['error']==EXIST_ERROR)
			{
				$data['status'] = false;
				$data['info'] = "邮箱已被注册";
				$data['field'] = "email";
				return $data;
			}
		}		
	}
	
	if($field=="getpassword_email")
	{
		if(!check_email($value))
		{
			$data['status'] = false;
			$data['info'] = "邮箱格式不正确";
			$data['field'] = "getpassword_email";
			return $data;
		}
		$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where email = '".$value."' and id <> ".$user_id);
		if(intval($rs)==0)
		{
			$data['status'] = false;
			$data['info'] = "邮箱未在本站注册过";
			$data['field'] = "getpassword_email";
			return $data;
		}
	
	}
	
	if($field=="getpassword_mobile")
	{
		if(!check_mobile($value))
		{
			$data['status'] = false;
			$data['info'] = "手机号码格式不正确";
			$data['field'] = "user_mobile";
			return $data;
		}
		$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile = '".$value."' and id <> ".$user_id);
		if(intval($rs)==0)
		{
			$data['status'] = false;
			$data['info'] = "手机号未在本站注册过";
			$data['field'] = "user_mobile";
			return $data;
		}
	
	}
	
	if($field=="user_name")
	{
		$check_rs = check_user("user_name",$value,$user_data);
		if(!$check_rs['status'])
		{
			$check_data = $check_rs['data'];
			if($check_data['error']==FORMAT_ERROR)
			{
				$data['status'] = false;
				$data['info'] = "用户名格式不正确";
				$data['field'] = "user_name";
				return $data;
			}
			if($check_data['error']==EXIST_ERROR)
			{
				$data['status'] = false;
				$data['info'] = "用户名已被注册";
				$data['field'] = "user_name";
				return $data;
			}
		}
	}
	if($field=="mobile")
	{
		$check_rs = check_user("mobile",$value,$user_data);
		if(!$check_rs['status'])
		{
			$check_data = $check_rs['data'];
			if($check_data['error']==FORMAT_ERROR)
			{
				$data['status'] = false;
				$data['info'] = "手机号格式不正确";
				$data['field'] = "user_mobile";
				return $data;
			}
			if($check_data['error']==EXIST_ERROR)
			{
				$data['status'] = false;
				$data['info'] = "手机号已被注册";
				$data['field'] = "user_mobile";
				return $data;
			}
		}		
	}
	
	if($field=="verify_code")
	{
		
		$verify = md5($value);
		$session_verify = es_session::get('verify');
		if($verify!=$session_verify)
		{
			$data['status'] = false;
			$data['info']	=	"图片验证码错误";
			$data['field'] = "verify_code";
			return $data;
		}
	}
	return $data;
}


/**
 * 获取前台的用户权限
 * @return array
 */
function get_user_auth()
{
	$user_auth = array();
	//定义用户权限
	$user_auth_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_auth where user_id = ".intval($GLOBALS['user_info']['id']));
	foreach($user_auth_rs as $k=>$row)
	{
		$user_auth[$row['m_name']][$row['a_name']][$row['rel_id']] = true;
	}
	return $user_auth;
}
function check_user_auth($m_name,$a_name,$rel_id)
{
	$rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_auth where m_name = '".$m_name."' and a_name = '".$a_name."' and user_id = ".intval($GLOBALS['user_info']['id']));
	foreach($rs as $row)
	{
		if($row['rel_id']==0||$row['rel_id']==$rel_id)
		{
			return true;
		}
	}
	return false;
}
function get_op_change_show($m_name,$a_name)
{
	if($a_name=="replydel"||$a_name=='del')
	{
		//删除
		$money = floatval(app_conf("USER_DELETE_MONEY"));
		$money_f = "-".format_price(0-$money);
		$score = intval(app_conf("USER_DELETE_SCORE"));
		$score_f = "-".format_score(0-$score);
		$point = intval(app_conf("USER_DELETE_POINT"));
		$point_f = "-".(0-$point)."经验";
	}
	else
	{
		//增加
		$money = floatval(app_conf("USER_ADD_MONEY"));
		$money_f = "+".format_price($money);
		$score = intval(app_conf("USER_ADD_SCORE"));
		$score_f = "+".format_score($score);
		$point = intval(app_conf("USER_ADD_POINT"));
		$point_f = "+".$point."经验";
	}
	$str = "";
	if($money!=0)$str .= $money_f;
	if($score!=0)$str .= $score_f;
	if($point!=0)$str .= $point_f;
	return $str;

}

function get_op_change($m_name,$a_name)
{
	if($a_name=="replydel"||$a_name=='del')
	{
		//删除
		$money = floatval(app_conf("USER_DELETE_MONEY"));

		$score = intval(app_conf("USER_DELETE_SCORE"));

		$point = intval(app_conf("USER_DELETE_POINT"));

	}
	else
	{
		//增加
		$money = floatval(app_conf("USER_ADD_MONEY"));

		$score = intval(app_conf("USER_ADD_SCORE"));

		$point = intval(app_conf("USER_ADD_POINT"));

	}
	return array("money"=>$money,"score"=>$score,"point"=>$point);

}


function show_avatar($u_id,$type="middle",$is_card=true)
{
	$key = md5("AVATAR_".$u_id.$type);
	if(isset($GLOBALS[$key]))
	{
		return $GLOBALS[$key];
	}
	else
	{
		$avatar_key = md5("USER_AVATAR_".$u_id);
		$avatar_data = $GLOBALS['dynamic_avatar_cache'][$avatar_key];// 当前用户所有头像的动态缓存
		if(!isset($avatar_data)||!isset($avatar_data[$key]))
		{
			$avatar_file = get_user_avatar($u_id,$type);
			if($is_card){
				$avatar_str = "<a href='".url("index","uc_home",array("id"=>$u_id))."' style='text-align:center; display:inline-block;'  onmouseover='userCard.load(this,\"".$u_id."\");'>".
						"<img src='".$avatar_file."'  />".
						"</a>";
			}else{
				$avatar_str = "<img src='".$avatar_file."'  />";
			}
			
			$avatar_data[$key] = $avatar_str;
			if(count($GLOBALS['dynamic_avatar_cache'])<500) //保存500个用户头像缓存
			{
				$GLOBALS['dynamic_avatar_cache'][$avatar_key] = $avatar_data;
			}
		}
		else
		{
			$avatar_str = $avatar_data[$key];
		}
		$GLOBALS[$key]= $avatar_str;
		return $GLOBALS[$key];
	}
}
function update_avatar($u_id)
{
	$avatar_key = md5("USER_AVATAR_".$u_id);
	unset($GLOBALS['dynamic_avatar_cache'][$avatar_key]);
	$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
	$GLOBALS['cache']->set("AVATAR_DYNAMIC_CACHE",$GLOBALS['dynamic_avatar_cache']); //头像的动态缓存
}

//获取用户头像的文件名
function get_user_avatar($id,$type)
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
	//@file_put_contents($avatar_check_file,@file_get_contents(APP_ROOT_PATH."public/avatar/noavatar_".$type.".gif"));
}


function get_user_name($id,$show_tag=true)
{

	$key = md5("USER_NAME_LINK_".$id);
	if(isset($GLOBALS[$key]))
	{
		return $GLOBALS[$key];
	}
	else
	{
		$uname = load_dynamic_cache($key);
		if($uname===false)
		{
			$u = $GLOBALS['db']->getRow("select id,user_name,is_merchant,is_daren,daren_title from ".DB_PREFIX."user where id = ".intval($id));
			$uname = "<a href='".url("index","uc_home",array("id"=>$id))."'  class='user_name'  onmouseover='userCard.load(this,\"".$u['id']."\");' >".$u['user_name']."</a>";
			if($show_tag)
			{
				
				$uname = "<a href='".url("index","uc_home",array("id"=>$id))."' onmouseover='userCard.load(this,\"".$u['id']."\");'>".msubstr($u['user_name'],0,5)."</a>";
				if($u['is_merchant'])
				{
					$uname = $uname."<font class='is_merchant' title='认证商家'></font>";
				}
				if($u['is_daren'])
				{
					$uname = $uname."<font class='is_daren' title='".$u['daren_title']."'></font>";
				}
			}
			else
			{
				$uname = "<a href='".url("index","uc_home",array("id"=>$id))."' onmouseover='userCard.load(this,\"".$u['id']."\");'>".$u['user_name']."</a>";
			}
			set_dynamic_cache($key,$uname);
		}
		$GLOBALS[$key] = $uname;
		return $GLOBALS[$key];
	}
}


//获取已过时间
function pass_date($time)
{
	$time_span = get_gmtime() - $time;
	if($time_span>3600*24*365)
	{
		//一年以前
		//			$time_span_lang = round($time_span/(3600*24*365)).$GLOBALS['lang']['SUPPLIER_YEAR'];
		//$time_span_lang = to_date($time,"Y".$GLOBALS['lang']['SUPPLIER_YEAR']."m".$GLOBALS['lang']['SUPPLIER_MON']."d".$GLOBALS['lang']['SUPPLIER_DAY']);
		$time_span_lang = to_date($time,"Y-m-d");
	}
	elseif($time_span>3600*24*30)
	{
		//一月
		//			$time_span_lang = round($time_span/(3600*24*30)).$GLOBALS['lang']['SUPPLIER_MON'];
		//$time_span_lang = to_date($time,"Y".$GLOBALS['lang']['SUPPLIER_YEAR']."m".$GLOBALS['lang']['SUPPLIER_MON']."d".$GLOBALS['lang']['SUPPLIER_DAY']);
		$time_span_lang = to_date($time,"Y-m-d");
	}
	elseif($time_span>3600*24)
	{
		//一天
		//$time_span_lang = round($time_span/(3600*24)).$GLOBALS['lang']['SUPPLIER_DAY'];
		$time_span_lang = to_date($time,"Y-m-d");
	}
	elseif($time_span>3600)
	{
		//一小时
		$time_span_lang = round($time_span/(3600)).$GLOBALS['lang']['SUPPLIER_HOUR'];
	}
	elseif($time_span>60)
	{
		//一分
		$time_span_lang = round($time_span/(60)).$GLOBALS['lang']['SUPPLIER_MIN'];
	}
	else
	{
		//一秒
		$time_span_lang = $time_span.$GLOBALS['lang']['SUPPLIER_SEC'];
	}
	return $time_span_lang;
}


//编译生成css文件
function parse_css($urls)
{
	$color_cfg = require_once APP_ROOT_PATH."app/Tpl/".APP_TYPE."/".app_conf("TEMPLATE")."/color_cfg.php";
	$showurl = $url = md5(implode(',',$urls).SITE_DOMAIN);	
	$css_url = 'public/runtime/statics/'.$url.'.css';
	$pathwithoupublic = 'runtime/statics/';
	$url_path = APP_ROOT_PATH.$css_url;
	if(!file_exists($url_path)||IS_DEBUG)
	{
		if(!file_exists(APP_ROOT_PATH.'public/runtime/statics/'))
			mkdir(APP_ROOT_PATH.'public/runtime/statics/',0777);
		$tmpl_path = $GLOBALS['tmpl']->_var['TMPL'];

		$css_content = '';
		foreach($urls as $url)
		{
			$css_content .= @file_get_contents($url);
		}
		$css_content = preg_replace("/[\r\n]/",'',$css_content);
		$css_content = str_replace("../images/",$tmpl_path."/images/",$css_content);
		
		if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		{
			curl_download($GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN']."/public/iconfont/iconfont.css", APP_ROOT_PATH."public/iconfont/iconfont.css");
			curl_download($GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN']."/public/iconfont/iconfont.eot", APP_ROOT_PATH."public/iconfont/iconfont.eot");
			curl_download($GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN']."/public/iconfont/iconfont.svg", APP_ROOT_PATH."public/iconfont/iconfont.svg");
			curl_download($GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN']."/public/iconfont/iconfont.ttf", APP_ROOT_PATH."public/iconfont/iconfont.ttf");
			curl_download($GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN']."/public/iconfont/iconfont.woff", APP_ROOT_PATH."public/iconfont/iconfont.woff");
			//	$css_content = str_replace("./public/iconfont/",$GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN']."/public/iconfont/",$css_content);
		}
		
		$css_content = str_replace("./public/",SITE_DOMAIN.APP_ROOT."/public/",$css_content);
		$css_content = str_replace("@rand",time(),$css_content);
		foreach($color_cfg as $k=>$v)
		{
			$css_content = str_replace($k,$v,$css_content);
		}
		//		@file_put_contents($url_path, unicode_encode($css_content));
		@file_put_contents($url_path, $css_content);
		if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		{
			syn_to_remote_file_server($css_url);
			$GLOBALS['refresh_page'] = true;
		}		
	}
	if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	{
		$domain = $GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN'];
	}
	else
	{
		$domain = SITE_DOMAIN.APP_ROOT;
	}
	return $domain."/".$css_url."?v=".app_conf("DB_VERSION").".".app_conf("APP_SUB_VER");
}

/**
 *
 * @param $urls 载入的脚本
 * @param $encode_url 需加密的脚本
 */
function parse_script($urls,$encode_url=array())
{
	$showurl = $url = md5(implode(',',$urls));
	$js_url = 'public/runtime/statics/'.$url.'.js';
	$pathwithoupublic = 'runtime/statics/';
	$url_path = APP_ROOT_PATH.$js_url;
	if(!file_exists($url_path)||IS_DEBUG)
	{
		if(!file_exists(APP_ROOT_PATH.'public/runtime/statics/'))
			mkdir(APP_ROOT_PATH.'public/runtime/statics/',0777);

		if(count($encode_url)>0)
		{
			require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
		}

		$js_content = '';
		foreach($urls as $url)
		{
			$append_content = @file_get_contents($url)."\r\n";
			if(in_array($url,$encode_url))
			{
				$packer = new JavaScriptPacker($append_content);
				$append_content = $packer->pack();
			}
			$js_content .= $append_content;
		}
		//		require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
		//	    $packer = new JavaScriptPacker($js_content);
		//		$js_content = $packer->pack();
		@file_put_contents($url_path,$js_content);
		if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
		{
			syn_to_remote_file_server($js_url);
			$GLOBALS['refresh_page'] = true;
		}
	}
	if($GLOBALS['distribution_cfg']['CSS_JS_OSS']&&$GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	{
		$domain = $GLOBALS['distribution_cfg']['OSS_FILE_DOMAIN'];
	}
	else
	{
		$domain = SITE_DOMAIN.APP_ROOT;
	}
	return $domain."/".$js_url."?v=".app_conf("DB_VERSION").".".app_conf("APP_SUB_VER");
}


function load_cart_tip()
{
	require_once APP_ROOT_PATH."system/model/cart.php";
	$cart_result = load_cart_list();
	$count = 0;
	foreach($cart_result['cart_list'] as $k=>$v)
	{
		$count+=intval($v['number']);
	}
	$GLOBALS['tmpl']->assign("cart_count",$count);
	$GLOBALS['tmpl']->assign("head_cart_data",load_cart_list());
	return $GLOBALS['tmpl']->fetch("inc/cart_tip.html");
	
}


/**
 * 会员中心左侧菜单
 */
function assign_uc_nav_list(){
	$nav_list = require APP_ROOT_PATH."system/web_cfg/".APP_TYPE."/ucnode_cfg.php";
	if(OPEN_FX)
	{
		$config_file =  APP_ROOT_PATH."system/web_cfg/".APP_TYPE."/fxucnode_cfg.php";
		$nav_list = array_merge_ucnode($nav_list, $config_file);
	}
	if(OPEN_WEIXIN)
	{
		$weixin_conf = load_auto_cache("weixin_conf");
		if($weixin_conf['platform_status']==1)
		{
		$config_file =  APP_ROOT_PATH."system/web_cfg/".APP_TYPE."/wxucnode_cfg.php";
		$nav_list = array_merge_ucnode($nav_list, $config_file);
		}
	}
	if(OPEN_DC)
	{
		$config_file =  APP_ROOT_PATH."system/web_cfg/".APP_TYPE."/dcucnode_cfg.php";
		$nav_list = array_merge_ucnode($nav_list, $config_file);
	}
	
	foreach($nav_list as $k=>$v)
	{

		foreach($v['node'] as $kk=>$vv)
		{
			if($vv['module'] == MODULE_NAME){
				$nav_list[$k]['node'][$kk]['current'] = 1;
			}
			$module_name = $vv['module'];
			$action_name = $vv['action'];
			$nav_list[$k]['node'][$kk]['url'] = url("index",$module_name."#".$action_name);
		}
	}
	//用户信息
	if($GLOBALS['user_info'])
	{
		$user_id = intval($GLOBALS['user_info']['id']);
		$c_user_info = $GLOBALS['user_info'];
		$c_user_info['user_group'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_group where id = ".intval($GLOBALS['user_info']['group_id']));
		$GLOBALS['tmpl']->assign("user_info",$c_user_info);

		//签到数据
		$t_begin_time = to_timespan(to_date(get_gmtime(),"Y-m-d"));  //今天开始
		$t_end_time = to_timespan(to_date(get_gmtime(),"Y-m-d"))+ (24*3600 - 1);  //今天结束
		$y_begin_time = $t_begin_time - (24*3600); //昨天开始
		$y_end_time = $t_end_time - (24*3600);  //昨天结束

		$t_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$t_begin_time." and ".$t_end_time);
		if($t_sign_data)
		{
			$GLOBALS['tmpl']->assign("t_sign_data",$t_sign_data);
		}
		else
		{
			$y_sign_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_sign_log where user_id = ".$user_id." and sign_date between ".$y_begin_time." and ".$y_end_time);
			$total_signcount = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_sign_log where user_id = ".$user_id);
			if($y_sign_data&&$total_signcount>=3)
			{
				$tip = "";
				if(floatval(app_conf("USER_LOGIN_KEEP_MONEY"))>0)
					$tip .= "资金+".format_price(app_conf("USER_LOGIN_KEEP_MONEY"));
				if(intval(app_conf("USER_LOGIN_KEEP_SCORE"))>0)
					$tip .= "积分+".format_score(app_conf("USER_LOGIN_KEEP_SCORE"));
				if(intval(app_conf("USER_LOGIN_KEEP_POINT"))>0)
					$tip .= "经验+".(app_conf("USER_LOGIN_KEEP_POINT"));
				$GLOBALS['tmpl']->assign("sign_tip",$tip);
			}
			else
			{
				if(!$y_sign_data)
					$GLOBALS['db']->query("delete from ".DB_PREFIX."user_sign_log where user_id = ".$user_id);
				$tip = "";
				if(floatval(app_conf("USER_LOGIN_MONEY"))>0)
					$tip .= "资金+".format_price(app_conf("USER_LOGIN_MONEY"));
				if(intval(app_conf("USER_LOGIN_SCORE"))>0)
					$tip .= "积分+".format_score(app_conf("USER_LOGIN_SCORE"));
				if(intval(app_conf("USER_LOGIN_POINT"))>0)
					$tip .= "经验+".(app_conf("USER_LOGIN_POINT"));
				$GLOBALS['tmpl']->assign("sign_tip",$tip);
			}
			$GLOBALS['tmpl']->assign("sign_day",$total_signcount);
			$GLOBALS['tmpl']->assign("y_sign_data",$y_sign_data);
		}


	}
	$GLOBALS['tmpl']->assign("uc_nav_list",$nav_list);
}


/**
 * 获取指定的流览历史ID
 * @param unknown_type $type deal/shop/youhui/event/store
 * 以下两个函数，需要开启user登录，即在页面端action，需要执行global_run
 */
function get_view_history($type)
{
	$ids = load_auto_cache("cache_history",array("type"=>$type,"session_id"=>es_session::id(),"uid"=>$GLOBALS['user_info']['id'],"city_id"=>$GLOBALS['city']['id']));
	return $ids;
}

function set_view_history($type,$id)
{
	load_auto_cache("cache_history",array("type"=>$type,"rel_id"=>$id,"session_id"=>es_session::id(),"uid"=>$GLOBALS['user_info']['id'],"city_id"=>$GLOBALS['city']['id']));
}

/**
 * 同步发微博
 * @param unknown $topic_id
 * @param unknown $class_name 首字母大写如： Sina ,Qqv2 ,Tencent
 */
function syn_to_weibo($topic_id,$api_class_name)
{
    $user_info = $GLOBALS['user_info'];
   
    set_time_limit(0);
    $user_id = $user_info['id'];

    es_session::close();
    $topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic_id);
    if($topic['topic_group']!="share")
    {
        $group = $topic['topic_group'];
        
        if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php"))
        {
            require_once APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php";
            $class_name = $group."_fetch_topic";
            if(class_exists($class_name))
            {
                $fetch_obj = new $class_name;
                $data = $fetch_obj->decode_weibo($topic);
            }
        }
    }
    else
    {
        $data['content'] =  msubstr($topic['content'],0,140);
        	
        //图片
        $topic_image = $GLOBALS['db']->getRow("select o_path from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']);
        if($topic_image)
            $data['img'] = APP_ROOT_PATH.$topic_image['o_path'];
    }

    $api = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where is_weibo = 1 and class_name = '".$api_class_name."'");

    if($user_info["is_syn_".strtolower($api['class_name'])]==1)
    {
        //发送本微博
        require_once APP_ROOT_PATH."system/api_login/".$api_class_name."_api.php";        
        $api_class = $api_class_name."_api";
        $api_obj = new $api_class($api);
        $api_obj->send_message($data);
    }
}




//自动切换跳转到相关的pc与wap页
function pc_wap_jump()
{
	$is_pc = intval(es_cookie::get("is_pc"));
	if($is_pc==0)
	{
		$is_pc = intval($_REQUEST['is_pc']);
		if($is_pc==1)es_cookie::set("is_pc","1",24*3600*30);
	}

	if($is_pc==0&&isMobile())
	{
		//开始自动跳转
		if(MODULE_NAME!="app_download")
		{
			//除了手机端下载以外全部需要跳转
			if(MODULE_NAME=="deal")
			{
				if(intval($GLOBALS['ref_uid'])>0)
				{
					$url = wap_url("index","deal",array("data_id"=>strim($_REQUEST['act']),"r"=>base64_encode(intval($GLOBALS['ref_uid']))));
				}
				else
				$url = wap_url("index","deal",array("data_id"=>strim($_REQUEST['act'])));
			}
			elseif(MODULE_NAME=="event"||MODULE_NAME=="youhui"||MODULE_NAME=="store")
			{
				$url = wap_url("index",MODULE_NAME,array("data_id"=>intval($_REQUEST['act'])));
			}
			elseif(MODULE_NAME=="tuan")
			{
				$url = wap_url("index","tuan",array("cate_id"=>intval($_REQUEST['cid'])));
			}
			elseif(MODULE_NAME=="cate")
			{
				$url = wap_url("index","goods",array("cate_id"=>intval($_REQUEST['cid'])));
			}
			elseif(MODULE_NAME=="events"||MODULE_NAME=="youhuis"||MODULE_NAME=="stores"||MODULE_NAME=="scores")
			{
				$url = wap_url("index",MODULE_NAME,array("cate_id"=>intval($_REQUEST['cid'])));
			}
			elseif(MODULE_NAME=="user")
			{
				$url = wap_url("index",MODULE_NAME."#".ACTION_NAME);
			}
			elseif(MODULE_NAME=="uc_home"&&ACTION_NAME=="mall")
			{
				$url = wap_url("index","uc_fx#".ACTION_NAME,array("r"=>strim($_REQUEST['r']),"type"=>intval($_REQUEST['type'])));
			}
			elseif(MODULE_NAME=="discover")
			{
				$url = wap_url("index",MODULE_NAME);
			}
			elseif(MODULE_NAME=="uc_voucher")
			{
			    $url = wap_url("index","uc_ecv");
			}
			elseif(MODULE_NAME=="uc_voucher"&&ACTION_NAME=='exchange')
			{
			    $url = wap_url("index","uc_ecv#exchange");
			}
			else
			{
				$url = wap_url("index");
			}
			app_redirect($url);
		}
	}

}
?>