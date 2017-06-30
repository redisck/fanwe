<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class ajaxModule extends MainBaseModule
{
	/**
	 * 发送手机验证码
	 */
	public function send_sms_code()
	{		
		$verify_code = strim($_REQUEST['verify_code']);
		$mobile_phone = strim($_REQUEST['mobile']);
		$account = intval($_REQUEST['account']);
		$no_verify = intval($_REQUEST['no_verify']); //是否图形验证
		$get_password = intval($_REQUEST['get_password']); //取回密码用
		if($account==1)
		{
			global_run();
			$mobile_phone = $GLOBALS['user_info']['mobile'];
			if($mobile_phone=="")
			{
				$data['status'] = false;
				$data['info'] = "请先绑定手机号";
				$data['jump'] = url("index","uc_account");
				$data['field'] = "user_mobile";
				ajax_return($data);
			}
		}
		if($mobile_phone=="")
		{
			$data['status'] = false;
			$data['info'] = "请输入手机号";
			$data['field'] = "user_mobile";
			ajax_return($data);
		}
		if(!check_mobile($mobile_phone))
		{
			$data['status'] = false;
			$data['info'] = "手机号格式不正确";
			$data['field'] = "user_mobile";
			ajax_return($data);
		}
		
		if(intval($_REQUEST['unique'])==1)
		{
			if(intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile = '".$mobile_phone."'"))>0)
			{
				$data['status'] = false;
				$data['info'] = "手机号已被注册";
				$data['field'] = "user_mobile";
				ajax_return($data);
			}
		}
		
		if($get_password==1)
		{
			$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where mobile = '".$mobile_phone."'");
			if(!$user_data)
			{
				$data['status'] = false;
				$data['info'] = "手机号未在本站注册过";
				$data['field'] = "user_mobile";
				ajax_return($data);
			}
		}
				
				
		$sms_ipcount = load_sms_ipcount();
		if($sms_ipcount>1&&$no_verify==0)
		{
			//需要图形验证码
			if(es_session::get("verify")!=md5($verify_code))
			{
				$data['status'] = false;
				$data['info'] = "验证码错误";
				$data['field'] = "verify_code";
				ajax_return($data);
			}
		}
		
		if(!check_ipop_limit(CLIENT_IP, "send_sms_code",SMS_TIMESPAN))
		{
			showErr("请勿频繁发送短信",1);
		}
		
		
		
		//删除失效验证码
		$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
		$GLOBALS['db']->query($sql);
		
		$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
		if($mobile_data)
		{
			//重新发送未失效的验证码
			$code = $mobile_data['code'];	
			$mobile_data['add_time'] = NOW_TIME;			
			$GLOBALS['db']->query("update ".DB_PREFIX."sms_mobile_verify set add_time = '".$mobile_data['add_time']."',send_count = send_count + 1 where mobile_phone = '".$mobile_phone."'");		
		}
		else
		{
			$code = rand(100000,999999);
			$mobile_data['mobile_phone'] = $mobile_phone;
			$mobile_data['add_time'] = NOW_TIME;
			$mobile_data['code'] = $code;
			$mobile_data['ip'] = CLIENT_IP;
			$GLOBALS['db']->autoExecute(DB_PREFIX."sms_mobile_verify",$mobile_data,"INSERT","","SILENT");
			
		}
		if($get_password==1)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set password_verify = '".$code."' where id = ".$user_data['id']);
		}
		send_verify_sms($mobile_phone,$code);
		es_session::delete("verify"); //删除图形验证码
		$data['status'] = true;
		$data['info'] = "发送成功";
		$data['lesstime'] = SMS_TIMESPAN -(NOW_TIME - $mobile_data['add_time']);  //剩余时间
		$data['sms_ipcount'] = load_sms_ipcount();
		ajax_return($data);
		
		
	}
	
	
	/**
	 * 验证会员字段
	 */
	public function check_field()
	{
		$field = strim($_REQUEST['field']);
		$value = strim($_REQUEST['value']);
		$user_id = intval($_REQUEST['user_id']);
		
		$data = check_field($field, $value, $user_id);
		ajax_return($data);
		
	}
	
	
	public function discover()
	{
		require_once APP_ROOT_PATH.'app/Lib/page.php';
		require_once APP_ROOT_PATH."system/model/topic.php";
		
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		$cid = intval($_REQUEST['cid']);
		$tag = strim($_REQUEST['tag']);
		$page = intval($_REQUEST['page']);
		if($page==0)$page = 1;
		$step = intval($_REQUEST['step']);
		$step_size = intval($_REQUEST['step_size']);
		$limit = (($page - 1)*PIN_PAGE_SIZE + ($step - 1)*PIN_SECTOR).",".PIN_SECTOR;
		if($step==0||$step>$step_size)
		{
			//超出
			$result['doms'] = array();
			$result['step'] = 0;
			$result['status'] = 0;
			$result['info'] = 'end';
			ajax_return($result);
		}
		
		$excondition = " fav_id = 0 and relay_id = 0 and has_image = 1 and type in ('share','sharedeal','shareyouhui','shareevent') ";
		$result_list = get_topic_list($limit,array("cid"=>$cid,"tag"=>$tag),"",$excondition);
		$result_list = $result_list['list'];
		if($result_list)
		{
			$result['doms'] = array();
			foreach($result_list as $k=>$v)
			{				
				$GLOBALS['tmpl']->assign("message_item",$v);
				$result['doms'][] = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/pin_box.html"));
			}
				
			if($step==0||$step>=$step_size)
			{
				//超出
				$result['step'] = 0;
				$result['status'] = 0;
				$result['info'] = 'end';
				ajax_return($result);
			}
			else
			{
				$result['status'] = 1;
				$result['step'] = $step + 1;
				$result['info'] = 'next';
				ajax_return($result);
			}
				
		}
		else
		{
			$result['doms'] = array();
			$result['step'] = 0;
			$result['status'] = 0;
			$result['info'] = 'end';
			//			$result['sql'] = $sql;
			ajax_return($result);
		}
	}
	public function uc_home_index()
	{
		require_once APP_ROOT_PATH.'app/Lib/page.php';
		require_once APP_ROOT_PATH."system/model/topic.php";
		global_run();
		$id = intval($_REQUEST['id']);
		$is_why = 0; //1 自己，2其它登录用户看，3未登录用户看
		if($id)
		{
			if($id == $GLOBALS['user_info']['id'])
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
			else
			{
				$is_why = 3;
				$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$id);
				$GLOBALS['tmpl']->assign("id",$id);
				if($GLOBALS['user_info']){
					$is_why = 2;
					$my_info =$GLOBALS['user_info'];
				}
			}
		}
		else
		{
			if(check_save_login()==LOGIN_STATUS_NOLOGIN)
			{
				app_redirect(url("index","user#login"));
			}
			else
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
		}
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		
		$page = intval($_REQUEST['page']);
		if($page==0)$page = 1;
		$step = intval($_REQUEST['step']);
		$step_size = intval($_REQUEST['step_size']);
		$limit = (($page - 1)*PIN_PAGE_SIZE + ($step - 1)*PIN_SECTOR).",".PIN_SECTOR;
		if($step==0||$step>$step_size)
		{
			//超出
			$result['doms'] = array();
			$result['step'] = 0;
			$result['status'] = 0;
			$result['info'] = 'end';
			ajax_return($result);
		}
		//我关注的用户
		$focus_user_list = load_auto_cache("cache_focus_user",array("uid"=>$home_user_info['id']));
		$t_ids[] = $home_user_info['id'];
		foreach($focus_user_list as $k=>$v){
			$t_ids[] = $v['id'];
		}
		$excondition =" user_id in (".implode(",", $t_ids).") and is_effect = 1 and is_delete = 0  and fav_id = 0  and type in ('share','dealcomment','youhuicomment','eventcomment','slocationcomment','eventsubmit','sharedeal','shareyouhui','shareevent') ";
		
		$result_list = get_topic_list($limit,null,"",$excondition);
		$result_list = $result_list['list'];
		if($result_list)
		{
			$result['doms'] = array();
			foreach($result_list as $k=>$v)
			{
				$GLOBALS['tmpl']->assign("message_item",$v);
				$result['doms'][] = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/pin_box.html"));
			}
	
			if($step==0||$step>=$step_size)
			{
				//超出
				$result['step'] = 0;
				$result['status'] = 0;
				$result['info'] = 'end';
				ajax_return($result);
			}
			else
			{
				$result['status'] = 1;
				$result['step'] = $step + 1;
				$result['info'] = 'next';
				ajax_return($result);
			}
	
		}
		else
		{
			$result['doms'] = array();
			$result['step'] = 0;
			$result['status'] = 0;
			$result['info'] = 'end';
			//			$result['sql'] = $sql;
			ajax_return($result);
		}
	}
	
	public function uc_home_fav()
	{
		require_once APP_ROOT_PATH.'app/Lib/page.php';
		require_once APP_ROOT_PATH."system/model/topic.php";
		global_run();
		$is_why = 0; //1 自己，2其它登录用户看，3未登录用户看
		$id = intval($_REQUEST['id']);
		if($id)
		{
			if($id == $GLOBALS['user_info']['id'])
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
			else
			{
				$is_why = 3;
				$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$id);
				$GLOBALS['tmpl']->assign("id",$id);
				if($GLOBALS['user_info']){
					$is_why = 2;
					$my_info =$GLOBALS['user_info'];
				}
			}
		}
		else
		{
			if(check_save_login()==LOGIN_STATUS_NOLOGIN)
			{
				app_redirect();
				showErr("请先登录",url("index","user#login"));
			}
			else
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
		}
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
	
		$page = intval($_REQUEST['page']);
		if($page==0)$page = 1;
		$step = intval($_REQUEST['step']);
		$step_size = intval($_REQUEST['step_size']);
		$limit = (($page - 1)*PIN_PAGE_SIZE + ($step - 1)*PIN_SECTOR).",".PIN_SECTOR;
		if($step==0||$step>$step_size)
		{
			//超出
			$result['doms'] = array();
			$result['step'] = 0;
			$result['status'] = 0;
			$result['info'] = 'end';
			ajax_return($result);
		}

		//我关注的用户
		$focus_user_list = load_auto_cache("cache_focus_user",array("uid"=>$home_user_info['id']));
		$t_ids[] = $home_user_info['id'];
		foreach($focus_user_list as $k=>$v){
			$t_ids[] = $v['id'];
		}

		$excondition =" user_id in (".implode(",", $t_ids).") and is_effect = 1 and is_delete = 0  and fav_id <> 0 and relay_id = 0  and type in ('share','dealcomment','youhuicomment','eventcomment','slocationcomment','eventsubmit','sharedeal','shareyouhui','shareevent') ";
		
		$result_list = get_topic_list($limit,null,"",$excondition);
		$result_list = $result_list['list'];
		if($result_list)
		{
			$result['doms'] = array();
			foreach($result_list as $k=>$v)
			{
				$GLOBALS['tmpl']->assign("message_item",$v);
				$result['doms'][] = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/pin_box.html"));
			}
	
			if($step==0||$step>=$step_size)
			{
				//超出
				$result['step'] = 0;
				$result['status'] = 0;
				$result['info'] = 'end';
				ajax_return($result);
			}
			else
			{
				$result['status'] = 1;
				$result['step'] = $step + 1;
				$result['info'] = 'next';
				ajax_return($result);
			}
	
		}
		else
		{
			$result['doms'] = array();
			$result['step'] = 0;
			$result['status'] = 0;
			$result['info'] = 'end';
			//			$result['sql'] = $sql;
			ajax_return($result);
		}
	}
	
	public function usercard()
	{
		global_run();
		init_app_page();
		$uid = intval($_REQUEST['uid']);		
		$uinfo = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$uid." and is_delete = 0 and is_effect = 1");		
		if($uinfo)
		{
		$user_id = intval($GLOBALS['user_info']['id']);
		$focused_uid = intval($uid);
		$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focused_uid);
		if($focus_data)
		$uinfo['focused'] = 1; 		
		$uinfo['point_level'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_level where id = ".intval($uinfo['level_id']));
		$uinfo['medal_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_medal where is_delete = 0 and user_id = ".$uid." order by create_time desc");
		$GLOBALS['tmpl']->assign("card_info",$uinfo);		
		$GLOBALS['tmpl']->display("inc/usercard.html");
		}
		else 
		{
			header("Content-Type:text/html; charset=utf-8");
			echo "<div class='load'>该会员已被删除或者已被禁用</div>";
		}
	}
	
	
	public function focus()
	{
		global_run();
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$data['tag'] = 4;
			$data['html'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		$focus_uid = intval($_REQUEST['uid']);
		if($user_id==$focus_uid)
		{
			$data['tag'] = 3;
			$data['html'] = $GLOBALS['lang']['FOCUS_SELF'];
			ajax_return($data);
		}
	
		$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
		//刷新用户列表
		rm_auto_cache("cache_focus_user",array("id"=>$user_id));
		rm_auto_cache("cache_focus_user",array("id"=>$focus_uid));
		
		if(!$focus_data&&$user_id>0&&$focus_uid>0)
		{
			$focused_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$focus_uid);
			$focus_data = array();
			$focus_data['focus_user_id'] = $user_id;
			$focus_data['focused_user_id'] = $focus_uid;
			$focus_data['focus_user_name'] = $GLOBALS['user_info']['user_name'];
			$focus_data['focused_user_name'] = $focused_user_name;
			
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_focus",$focus_data,"INSERT");
			//判断是否互相关注
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id = ".$focus_uid." and focused_user_id=".$user_id)){
				$GLOBALS['db']->query("update ".DB_PREFIX."user_focus set to_focus = 1 where (focus_user_id = ".$focus_uid." and focused_user_id=".$user_id.") or (focus_user_id = ".$user_id." and focused_user_id=".$focus_uid.")");
			}
			
			$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count + 1 where id = ".$user_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count + 1 where id = ".$focus_uid);
			//刷新用户缓存
			load_user($user_id,true);
			load_user($focus_uid,true);
			$data['tag'] = 1;
			$data['html'] = $GLOBALS['lang']['CANCEL_FOCUS'];
			ajax_return($data);
		}
		elseif($focus_data&&$user_id>0&&$focus_uid>0)
		{
			$GLOBALS['db']->query("delete from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set focus_count = focus_count - 1 where id = ".$user_id);
			$GLOBALS['db']->query("update ".DB_PREFIX."user set focused_count = focused_count - 1 where id = ".$focus_uid);
			$GLOBALS['db']->query("update ".DB_PREFIX."user_focus set to_focus = 0 where (focus_user_id = ".$focus_uid." and focused_user_id=".$user_id.") or (focus_user_id = ".$user_id." and focused_user_id=".$focus_uid.")");
			//刷新用户缓存
			load_user($user_id,true);
			load_user($focus_uid,true);
			$data['tag'] =2;
			$data['html'] = $GLOBALS['lang']['FOCUS_THEY'];
			ajax_return($data);
		}
	
	}
	public function check_login_status()
	{
		global_run();
		if(check_save_login()==LOGIN_STATUS_NOLOGIN)
			$result['status'] = 0;
		else
			$result['status'] = 1;
		ajax_return($result);
	}
	
	
	
	public function do_fav_topic()
	{
		global_run();
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		else
		{
			$id = intval($_REQUEST['id']);
			$topic = $GLOBALS['db']->getRow("select id,user_id from ".DB_PREFIX."topic where id = ".$id);
			if(!$topic)
			{
				$result['status'] = 0;
				$result['info'] = $GLOBALS['lang']['TOPIC_NOT_EXIST'];
			}
			else
			{
				if($topic['user_id']==intval($GLOBALS['user_info']['id']))
				{
					$result['status'] = 0;
					$result['info'] = $GLOBALS['lang']['TOPIC_SELF'];
				}
				else
				{
					$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where (fav_id = ".$id." or (origin_id = ".$id." and fav_id <> 0)) and user_id = ".intval($GLOBALS['user_info']['id']));
					if($count>0)
					{
						$result['status'] = 0;
						$result['info'] = $GLOBALS['lang']['TOPIC_FAVED'];
					}
					else
					{
						$result['status'] = 1;
						require_once APP_ROOT_PATH."system/model/topic.php";
						$tid = insert_topic($content,$title="",$type="",$group="", $relay_id = 0, $id);
						if($tid)
						{
							increase_user_active(intval($GLOBALS['user_info']['id']),"喜欢了一则分享");
							$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
						}
						$result['info'] = $GLOBALS['lang']['FAV_SUCCESS'];
					}
				}
			}
		}
		ajax_return($result);
	}
	
	
	public function do_relay_topic()
	{
		global_run();
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		else
		{
			$id = intval($_REQUEST['id']);
			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where (relay_id = ".$id." or (origin_id = ".$id." and relay_id <> 0)) and user_id = ".intval($GLOBALS['user_info']['id']));
				
			if($count>0)
			{
				$result['status'] = 0;
				$result['info'] = "您已转载过该分享";
			}
			else
			{
				$result['status'] = 1;
				//$content = strim(valid_str($_REQUEST['content']));
				require_once APP_ROOT_PATH."system/model/topic.php";
				$tid = insert_topic("",$title="",$type="",$group="", $id, $fav_id=0);
				if($tid)
				{
					increase_user_active(intval($GLOBALS['user_info']['id']),"转载了一则分享");
					$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
				}
				$result['info'] = $GLOBALS['lang']['RELAY_SUCCESS'];
			}
		}
		ajax_return($result);
	}
	
	
	//添加到购物车
	public function cart_tip()
	{
		global_run();
		$data['html'] = load_cart_tip();
		ajax_return($data);
	}
	
	/**
	 * 
	 * @param bool $ajaxReturn 是否以ajax返回
	 * @param array $param 该值不为空，则加入购物车的id,attr以此为准，否则取$_REQUEST
	*/
	
	public function addcart($ajaxReturn=true,$param=array())
	{
		global_run();
		require_once APP_ROOT_PATH.'system/model/cart.php';
		require_once APP_ROOT_PATH.'system/model/deal.php';
		$id = !empty($param['id'])?intval($param['id']):intval($_REQUEST['id']);
		$deal_info = get_deal($id);
		if(!$deal_info||$deal_info['buyin_app']==1)
		{
			$res['status'] = 0;
			$res['info'] = "没有可以购买的产品";
			if($ajaxReturn){
				ajax_return($res);
			}else{
				return $res;
			}	
		}		
		
		if(($deal_info['is_lottery']==1||$deal_info['buy_type']==1))
		{
			if(check_save_login()==LOGIN_STATUS_NOLOGIN)
			{
				$res['status'] = -1;
				$res['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
				if($ajaxReturn){
					ajax_return($res);
				}else{
					return $res;
				}	
			}
		}
			
		$check = check_deal_time($id);
		if($check['status'] == 0)
		{
			$res['status'] = 0;
			$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
			if($ajaxReturn){
				ajax_return($res);
			}else{
				return $res;
			}	
		}
		
		$attr = !empty($param['attr'])?$param['attr']:$_REQUEST['attr'];
		//trace($attr);
		//if( $id==84 ){trace($attr);}
		if(count($attr)!=count($deal_info['deal_attr']))
		{
			$res['status'] = 0;
			$res['info'] = "请选择商品规格";
			if($ajaxReturn){
				ajax_return($res);
			}else{
				return $res;
			}	
		}
		else
		{
			//加入购物车处理，有提交属性， 或无属性时
			$attr_str = '0';
			$attr_name = '';
			$attr_name_str = '';
			if($attr)
			{
				foreach($attr as $kk=>$vv)
				{
					$attr[$kk] = intval($vv[0]);
				}
				$attr_str = implode(",",$attr);
				$attr_names = $GLOBALS['db']->getAll("select name from ".DB_PREFIX."deal_attr where id in(".$attr_str.")");
				$attr_name = '';
				foreach($attr_names as $attr)
				{
					$attr_name .=$attr['name'].",";
					$attr_name_str.=$attr['name'];
				}
				$attr_name = substr($attr_name,0,-1);
			}
			$verify_code = md5($id."_".$attr_str);
			$session_id = es_session::id();
				
			if(app_conf("CART_ON")==0)
			{
				$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cart where session_id = '".$session_id."'");
				load_cart_list(true);
			}
	
			$cart_result = load_cart_list();
			foreach($cart_result['cart_list'] as $k=>$v)
			{
				if($v['verify_code']==$verify_code)
				{
					$cart_item = $v;
				}
			}
			
			$_REQUEST['number'] = !empty($param['number'])?intval($param['number']):intval($_REQUEST['number']);

			$add_number = $number = intval($_REQUEST['number'])<=0?1:intval($_REQUEST['number']);
	
				
			//开始运算购物车的验证
			if($cart_item)
			{	
				//属性库存的验证
				$attr_setting_str = '';
				if($cart_item['attr']!='')
				{
					$attr_setting_str = $cart_item['attr_str'];
				}
	
	
					
				if($attr_setting_str!='')
				{
					$check = check_deal_number_attr($cart_item['deal_id'],$attr_setting_str,$add_number);					
					if($check['status']==0)
					{
						$res['status'] = 0;
						$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
					
						if($ajaxReturn){
							ajax_return($res);
						}else{
							return $res;
						}	
					}
				}
				
				$check = check_deal_number($cart_item['deal_id'],$add_number);
				if($check['status']==0)
				{
					$res['status'] = 0;
					$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
						
					if($ajaxReturn){
						ajax_return($res);
					}else{
						return $res;
					}
				}
				//属性库存的验证
			}
			else //添加时的验证
			{	
				//属性库存的验证
				$attr_setting_str = '';
				if($attr_name_str!='')
				{
					$attr_setting_str =$attr_name_str;
				}
	
	
					
				if($attr_setting_str!='')
				{
					$check = check_deal_number_attr($deal_info['id'],$attr_setting_str,$add_number);
					if($check['status']==0)
					{

						$res['status'] = 0;
						$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
	
						if($ajaxReturn){
							ajax_return($res);
						}else{
							return $res;
						}	
					}
				}
				
				$check = check_deal_number($deal_info['id'],$add_number);
				if($check['status']==0)
				{
					$res['status'] = 0;
					$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
				
					if($ajaxReturn){
						ajax_return($res);
					}else{
						return $res;
					}
				}
				//属性库存的验证
			}
				
			if($deal_info['return_score']<0)
			{
				//需要积分兑换
				$user_score = intval($GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id'])));
				if($user_score < abs(intval($deal_info['return_score'])*$add_number))
				{

					$res['status'] = 0;
					$res['info'] = $check['info']." ".$GLOBALS['lang']['NOT_ENOUGH_SCORE'];
		
					if($ajaxReturn){
						ajax_return($res);
					}else{
						return $res;
					}	
				}
			}
				
			//验证over
				
			if(!$cart_item)
			{
				$attr_price = $GLOBALS['db']->getOne("select sum(price) from ".DB_PREFIX."deal_attr where id in($attr_str)");
				$add_balance_price = $GLOBALS['db']->getOne("select sum(add_balance_price) from ".DB_PREFIX."deal_attr where id in($attr_str)");
				$cart_item['session_id'] = $session_id;
				$cart_item['user_id'] = intval($GLOBALS['user_info']['id']);
				$cart_item['deal_id'] = $id;
				//属性
				if($attr_name != '')
				{
					$cart_item['name'] = $deal_info['name']." [".$attr_name."]";
					$cart_item['sub_name'] = $deal_info['sub_name']." [".$attr_name."]";
				}
				else
				{
					$cart_item['name'] = $deal_info['name'];
					$cart_item['sub_name'] = $deal_info['sub_name'];
				}
				$cart_item['name'] = strim($cart_item['name']);
				$cart_item['sub_name'] = strim($cart_item['sub_name']);
				$cart_item['attr'] = $attr_str;
				$cart_item['add_balance_price'] = $add_balance_price;
				$cart_item['unit_price'] = $deal_info['current_price'] + $attr_price;
				$cart_item['number'] = $number;
				$cart_item['total_price'] = $cart_item['unit_price'] * $cart_item['number'];
				$cart_item['verify_code'] = $verify_code;
				$cart_item['create_time'] = NOW_TIME;
				$cart_item['update_time'] = NOW_TIME;
				$cart_item['return_score'] = $deal_info['return_score'];
				$cart_item['return_total_score'] = $deal_info['return_score'] * $cart_item['number'];
				$cart_item['return_money'] = $deal_info['return_money'];
				$cart_item['return_total_money'] = $deal_info['return_money'] * $cart_item['number'];
				$cart_item['buy_type']	=	$deal_info['buy_type'];
				$cart_item['supplier_id']	=	$deal_info['supplier_id'];
				$cart_item['attr_str'] = $attr_name_str;
	
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_cart",$cart_item);
				
			}
			else
			{
				if($number>0)
				{
					$cart_item['number'] += $number;
					$cart_item['total_price'] = $cart_item['unit_price'] * $cart_item['number'];
					$cart_item['return_total_score'] = $deal_info['return_score'] * $cart_item['number'];
					$cart_item['return_total_money'] = $deal_info['return_money'] * $cart_item['number'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_cart",$cart_item,"UPDATE","id=".$cart_item['id']);
				}
			}
			
				
			
			syn_cart(); //同步购物车中的状态 cart_type	
			$cart_result = load_cart_list(true);
			$cart_total = count($cart_result['cart_list']);
			$GLOBALS['tmpl']->assign("cart_total",$cart_total);
			
			$relate_list = get_deal_list(4,array(DEAL_ONLINE),array("cid"=>$deal_info['cate_id'],"city_id"=>$GLOBALS['city']['id']),"","d.id<>".$deal_info['id']);

			$GLOBALS['tmpl']->assign("relate_list",$relate_list['list']);
			
			$res['html'] = $GLOBALS['tmpl']->fetch("inc/pop_cart.html");
			$res['status'] = 1;

			if($ajaxReturn){
				ajax_return($res);
			}else{
				return $res;
			}	
		}
	}
	
	
	//加载某个商家的其他团购
	public function load_supplier_deal()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		$supplier_id = intval($_REQUEST['supplier_id']);
		$supplier_name = strim($_REQUEST['supplier_name']);
		$page_size = 5;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		require_once APP_ROOT_PATH."system/model/deal.php";
		//输出同商家的其他团购
		$supplier_tuan_list = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array(),""," d.buy_type <> 1 and d.is_shop = 0 and d.supplier_id =".$supplier_id." and d.id <> ".$deal_id);
		//$supplier_tuan_list = get_deal_list($page_size,array(DEAL_ONLINE));
		$GLOBALS['tmpl']->assign("supplier_tuan_list",$supplier_tuan_list['list']);
		$GLOBALS['tmpl']->assign("supplier_name",$supplier_name);
		
		//分页
		require_once APP_ROOT_PATH."app/Lib/page.php";
		
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d where ".$supplier_tuan_list['condition']);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('total',$total);
		$GLOBALS['tmpl']->assign('page_size',$page_size);
		
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/supplier_deal.html");
		ajax_return($data);
	}
	
	
	//加载某个商家的门店列表
	public function load_business_address()
	{
		$city_id = intval($_REQUEST['city_id']); //城市ID
		
		require_once APP_ROOT_PATH."system/model/city.php";
		$city = City::locate_city();
		if($city_id==0)$city_id = $city['id'];
		
		$aid = intval($_REQUEST['aid']);	//行政区ID
		$qid = intval($_REQUEST['qid']);	//商圈ID
		
		$deal_id = intval($_REQUEST['deal_id']); //商品ID
		$event_id = intval($_REQUEST['event_id']); //活动ID
		$youhui_id = intval($_REQUEST['youhui_id']); //优惠券ID
		
		$supplier_id = intval($_REQUEST['supplier_id']); //商家ID
		
		if($deal_id>0)
		{
			$join = " left join ".DB_PREFIX."deal_location_link as l on sl.id = l.location_id ";
			$where = " sl.supplier_id = ".$supplier_id." and l.deal_id = ".$deal_id." ";
		}
		elseif($event_id>0)
		{
			$join = " left join ".DB_PREFIX."event_location_link as l on sl.id = l.location_id ";
			$where = " sl.supplier_id = ".$supplier_id." and l.event_id = ".$event_id." ";
		}
		elseif($youhui_id>0)
		{
			$join = " left join ".DB_PREFIX."youhui_location_link as l on sl.id = l.location_id ";
			$where = " sl.supplier_id = ".$supplier_id." and l.youhui_id = ".$youhui_id." ";
		}
		else
		{
			$join = "";
			$where = " sl.supplier_id = ".$supplier_id." ";
		}

		//$join = "";
		//$where = "";
		$page_size = 3;
		
		require_once APP_ROOT_PATH."system/model/supplier.php";
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$location_list = get_location_list($limit,array("city_id"=>$city_id,"aid"=>$aid,"qid"=>$qid),$join,$where);
		
		if($join)
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location as sl ".$join." where ".$location_list['condition']);
		else
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location as sl where ".$location_list['condition']);
		$page = new Page($total,$page_size,"","short");   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		//获取城市列表
		$city_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_city where is_effect = 1 order by sort asc");
		foreach($city_list as $k=>$v)
		{
			if($v['id']==$city_id)
			{
				$city_list[$k]['current'] = true;
			}
		}
		$GLOBALS['tmpl']->assign("city_list",$city_list);
		
		//获取地区
		if($city_id>0)
		$area_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area WHERE pid=0 AND city_id=".$city_id." ORDER BY `sort` asc ");
		foreach($area_list as $k=>$v)
		{
			if($v['id']==$aid)
			{
				$area_list[$k]['current'] = true;
			}
		}
		$GLOBALS['tmpl']->assign("area_list",$area_list);
		
		//获取商圈
		if($city_id>0&&$aid>0)
		$quan_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."area WHERE pid=".$aid." AND city_id=".$city_id." ORDER BY `sort` asc ");
		foreach($quan_list as $k=>$v)
		{
			if($v['id']==$qid)
			{
				$quan_list[$k]['current'] = true;
			}
		}
		$GLOBALS['tmpl']->assign("quan_list",$quan_list);
		
		$GLOBALS['tmpl']->assign("supplier_id",$supplier_id);
		$GLOBALS['tmpl']->assign("location_list",$location_list['list']);
		$GLOBALS['tmpl']->assign("sellall",url("index","stores",array("supplier_id"=>$supplier_id)));
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/business_address.html");		
		ajax_return($data);
	}
	
	
	/**
	 * 加载点评详细列表
	 */
	public function load_review_list()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		$youhui_id = intval($_REQUEST['youhui_id']);
		$event_id = intval($_REQUEST['event_id']);
		$supplier_id = intval($_REQUEST['supplier_id']);
		$location_id = intval($_REQUEST['location_id']);
		
		$item_array = array("deal_id"=>$deal_id,"youhui_id"=>$youhui_id,"event_id"=>$event_id,"supplier_id"=>$supplier_id,"location_id"=>$location_id);
		
		require_once APP_ROOT_PATH."system/model/review.php";
		require_once APP_ROOT_PATH."app/Lib/page.php";		
		$dp_data = load_dp_info($item_array);
				
		$GLOBALS['tmpl']->assign("dp_data",$dp_data);
		
		
		//排序行
		$sort_field = intval($_REQUEST['sort_field']);
		$sort_type = strim($_REQUEST['sort_type']);
		$filter = intval($_REQUEST['filter']);
		$is_img = intval($_REQUEST['is_img']);
		$is_content = intval($_REQUEST['is_content']);
		$sort_data['sort_field'] = $sort_field;
		$sort_data['sort_type'] = $sort_type;
		$sort_data['filter'] = $filter;
		$sort_data['is_img'] = $is_img;
		$sort_data['is_content'] = $is_content;
		$GLOBALS['tmpl']->assign("sort_data",$sort_data);
		
		$ext_condition = "";
		if($filter==1)//好评
		{
			$ext_condition = " point >= 4 ";
		}
		elseif($filter==2)//中评
		{
			$ext_condition = " point >= 2 and point < 4 ";
		}
		elseif($filter==3)//差评
		{
			$ext_condition = " point < 2 ";
		}
		
		
		if($is_img==1)
		{
			if($ext_condition!="")$ext_condition.=" and ";
			$ext_condition.="  is_img = 1 ";
		}
		
		if($is_content==1)
		{
			if($ext_condition!="")$ext_condition.=" and ";
			$ext_condition.="  is_content = 1 ";
		}
		
		//排序
		$orderby = "";
		if($sort_field>0)
		{
			if($sort_field==1) //好评
			{
				if($sort_type=="asc")
				{
					$orderby = " point asc ";
				}
				else 
				{
					$orderby = " point desc ";
				}
			}
			elseif($sort_field==2) //差评
			{
				if($sort_type=="asc")
				{
					$orderby = " point desc ";
				}
				else 
				{
					$orderby = " point asc ";
				}
			}
		}
		
		//tag行
		$tag = strim($_REQUEST['tag']);
		$GLOBALS['tmpl']->assign("filter_tag",$tag);
		$gid = intval($_REQUEST['gid']);
		$GLOBALS['tmpl']->assign("gid",$gid);
		//分页
		$page_size = 5;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$list_item_array = $item_array;
		$list_item_array['tag'] = $tag;
		$dp_res = get_dp_list($limit,$list_item_array,$ext_condition,$orderby);
		$dp_list = $dp_res['list'];
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp  where ".$dp_res['condition']);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign('dp_list',$dp_list);
		require_once APP_ROOT_PATH."system/model/topic.php";
		$data['html'] = decode_topic_without_img($GLOBALS['tmpl']->fetch("inc/review_list.html"));		
		ajax_return($data);
	}
	
	
	/**
	 * 加载购物车中的商品
	 */
	public function load_cart_list()
	{
		global_run();
				
		require_once APP_ROOT_PATH."system/model/cart.php";
		$cart_result = load_cart_list();
		
		$cart_list = $cart_result['cart_list'];
		$total_data = $cart_result['total_data'];
		$is_score = false;
		foreach($cart_list as $k=>$v)
		{
			$bind_data = array();
			$bind_data['id'] = $v['id'];
			if($v['buy_type']==1)
			{
				$is_score = true;
				$bind_data['unit_price'] = abs($v['return_score']);
				$bind_data['total_price'] = abs($v['return_total_score']);
			}	
			else
			{
				$bind_data['unit_price'] = $v['unit_price'];
				$bind_data['total_price'] = $v['total_price'];
			}			
			$bind_data['number'] = $v['number'];
			$jsondata[$v['id']] = $bind_data;			
		}
		
		$GLOBALS['tmpl']->assign("jsondata",json_encode($jsondata));
		$GLOBALS['tmpl']->assign("cart_list",$cart_list);
		
		if($is_score)
			$GLOBALS['tmpl']->assign('total_price',abs($total_data['return_total_score']));
		else
			$GLOBALS['tmpl']->assign('total_price',$total_data['total_price']);
		
		$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/cart_list.html");
		ajax_return($data);
	}
	
	//删除指定的购物车项
	public function del_cart()
	{
		global_run();
		if(isset($_REQUEST['id']))
		{
			$id = intval($_REQUEST['id']);	
			$sql = "delete from ".DB_PREFIX."deal_cart  where session_id = '".es_session::id()."' and id = ".$id;
		}
		else
		{
			$sql = "delete from ".DB_PREFIX."deal_cart  where session_id = '".es_session::id()."'";
		}
		$GLOBALS['db']->query($sql);

		require_once APP_ROOT_PATH."system/model/cart.php";
		
		if($GLOBALS['db']->affected_rows()>0)
		{
			load_cart_list(true);  //重新刷新购物车
			ajax_return(array("status"=>true));
		}
		else
		{
			ajax_return(array("status"=>false));
		}
		
	}
	
	/**
	 * 提交验证购物车
	 */
	public function check_cart()
	{
		require_once APP_ROOT_PATH."system/model/cart.php";
		global_run();
		if((check_save_login()!=LOGIN_STATUS_LOGINED&&$GLOBALS['user_info']['money']>0)||check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			$data['status'] = -1;
			ajax_return($data);
		}
		
		$cart_result = load_cart_list();
		$cart_list = $cart_result['cart_list'];
		$total_score = 0;
		$total_money = 0;
		foreach ($_REQUEST['id'] as $k=>$id)
		{
			$id = intval($id);
			$number = intval($_REQUEST['number'][$k]);
			$total_score+=$cart_list[$id]['return_score']*$number;
			$total_money+=$cart_list[$id]['return_money']*$number;
		}
		
		//验证积分
// 		$total_score = $cart_result['total_data']['return_total_score'];
		if($GLOBALS['user_info']['score']+$total_score<0)
		{
			$data['info'] = $GLOBALS['lang']['SCORE_NOT_ENOUGHT'];
			$data['status'] = 0;
			ajax_return($data);
		}
		//验证积分
		
		
		//关于现金的验证
// 		$total_money = $cart_result['total_data']['return_total_money'];
		if($GLOBALS['user_info']['money']+$total_money<0)
		{
			$data['info'] = $GLOBALS['lang']['MONEY_NOT_ENOUGHT'];
			$data['status'] = 0;
			ajax_return($data);
		}
		//关于现金的验证		
		
		foreach ($_REQUEST['id'] as $k=>$id)
		{
			$id = intval($id);
			$number = intval($_REQUEST['number'][$k]);
			$data = check_cart($id, $number);
			if(!$data['status'])
			{
				$data['id'] = $id;
				ajax_return($data);
			}
		}		
		
		foreach ($_REQUEST['id'] as $k=>$id)
		{
			$id = intval($id);
			$number = intval($_REQUEST['number'][$k]);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set number =".$number.", total_price = ".$number."* unit_price, return_total_score = ".$number."* return_score, return_total_money = ".$number."* return_money where id =".$id." and session_id = '".es_session::id()."'");
			load_cart_list(true);
		}	
		$data = array();
		$data['status'] = 1;
		$data['jump'] = url("index","cart#check");
		ajax_return($data);
	}
	
	/**
	 * 加载购物车中的配送地区
	 */
	public function load_consignee()
	{
		global_run();
		$consignee_id = intval($_REQUEST['id']);
		$order_id = intval($_REQUEST['order_id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id." and is_delete = 0 and user_id =".intval($GLOBALS['user_info']['id']));
		
		if($consignee_id>0)
		{
			$consignee_data = load_auto_cache("consignee_info",array("consignee_id"=>$consignee_id));
			$consignee_info = $consignee_data['consignee_info'];
			$region_lv1 = $consignee_data['region_lv1'];
			$region_lv2 = $consignee_data['region_lv2'];
			$region_lv3 = $consignee_data['region_lv3'];
			$region_lv4 = $consignee_data['region_lv4'];
			$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
			$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
			$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
			$GLOBALS['tmpl']->assign("region_lv4",$region_lv4);
			$GLOBALS['tmpl']->assign("consignee_info",$consignee_info);
		}
		elseif($order_info)
		{
			//关于订单的地区输出
				
			$consignee_data['consignee_info'] = $order_info;
			$region_lv1 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
			foreach($region_lv1 as $k=>$v)
			{
				if($v['id'] == $order_info['region_lv1'])
				{
					$region_lv1[$k]['selected'] = 1;
					break;
				}
			}
			$consignee_data['region_lv1'] = $region_lv1;
		
			$region_lv2 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_region where pid = ".$order_info['region_lv1']);  //二级地址
			foreach($region_lv2 as $k=>$v)
			{
				if($v['id'] == $order_info['region_lv2'])
				{
					$region_lv2[$k]['selected'] = 1;
					break;
				}
			}
			$consignee_data['region_lv2'] = $region_lv2;
		
			$region_lv3 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_region where pid = ".$order_info['region_lv2']);  //三级地址
			foreach($region_lv3 as $k=>$v)
			{
				if($v['id'] == $order_info['region_lv3'])
				{
					$region_lv3[$k]['selected'] = 1;
					break;
				}
			}
			$consignee_data['region_lv3'] = $region_lv3;
		
			$region_lv4 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_region where pid = ".$order_info['region_lv3']);  //四级地址
			foreach($region_lv4 as $k=>$v)
			{
				if($v['id'] == $order_info['region_lv4'])
				{
					$region_lv4[$k]['selected'] = 1;
					break;
				}
			}
			$consignee_data['region_lv4'] = $region_lv4;
				
			$region_lv1 = $consignee_data['region_lv1'];
			$region_lv2 = $consignee_data['region_lv2'];
			$region_lv3 = $consignee_data['region_lv3'];
			$region_lv4 = $consignee_data['region_lv4'];
			$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
			$GLOBALS['tmpl']->assign("region_lv2",$region_lv2);
			$GLOBALS['tmpl']->assign("region_lv3",$region_lv3);
			$GLOBALS['tmpl']->assign("region_lv4",$region_lv4);
			unset($order_info['id']);
			$GLOBALS['tmpl']->assign("consignee_info",$order_info);
		}
		else
		{
			$region_lv1 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery_region where pid = 0");  //一级地址
			$GLOBALS['tmpl']->assign("region_lv1",$region_lv1);
		}
	
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/cart_consignee.html");
		ajax_return($data);
	}
	
	/**
	 * 加载配送方式
	 */
	public function load_delivery()
	{
		global_run();
		$region_id = intval($_REQUEST['id']);
		$order_id = intval($_REQUEST['order_id']);
		require_once APP_ROOT_PATH."system/model/cart.php";
		$delivery_list = load_support_delivery($region_id,$order_id);
		$GLOBALS['tmpl']->assign("delivery_list",$delivery_list);
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/cart_delivery.html");
		ajax_return($data);
	}
	
	/**
	 * 计算购物车总价
	 */
	public function count_buy_total()
	{
		global_run();
		require_once APP_ROOT_PATH."system/model/cart.php";
		$region_id = intval($_REQUEST['region_id']); //配送地区
		$delivery_id =  intval($_REQUEST['delivery_id']); //配送方式
		$account_money =  floatval($_REQUEST['account_money']); //余额
		$ecvsn = $_REQUEST['ecvsn']?addslashes(trim($_REQUEST['ecvsn'])):'';
		$ecvpassword = $_REQUEST['ecvpassword']?addslashes(trim($_REQUEST['ecvpassword'])):'';
		$payment = intval($_REQUEST['payment']);
		$all_account_money = intval($_REQUEST['all_account_money']);
		$bank_id = strim(trim($_REQUEST['bank_id']));
	
		$user_id = intval($GLOBALS['user_info']['id']);
		$session_id = es_session::id();
		
		$cart_result = load_cart_list();
		$goods_list = $cart_result['cart_list'];

		$result = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list,0,0,$bank_id);
	
	
		$GLOBALS['tmpl']->assign("result",$result);
		$html = $GLOBALS['tmpl']->fetch("inc/cart_total.html");
		$data = $result;
		$data['html'] = $html;
		$data['expire'] = empty($goods_list)?true:false;
		if($data['expire'])$data['jump'] = url("index","cart");
		ajax_return($data);
	}
	
	
	
	public function count_order_total()
	{
		global_run();
		require_once APP_ROOT_PATH."system/model/cart.php";
		$order_id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
	
	
		$region_id = intval($_REQUEST['region_id']); //配送地区
		$delivery_id =  intval($_REQUEST['delivery_id']); //配送方式
		$account_money =  floatval($_REQUEST['account_money']); //余额
	
		$ecvsn = $_REQUEST['ecvsn']?strim($_REQUEST['ecvsn']):'';
		$ecvpassword = $_REQUEST['ecvpassword']?strim($_REQUEST['ecvpassword']):'';
	
		$payment = intval($_REQUEST['payment']);
		$all_account_money = intval($_REQUEST['all_account_money']);
		$bank_id = strim($_REQUEST['bank_id']);
	
		$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
	
		$result = count_buy_total($region_id,$delivery_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$goods_list,$order_info['account_money'],$order_info['ecv_money'],$bank_id);
			
		$GLOBALS['tmpl']->assign("result",$result);
		$html = $GLOBALS['tmpl']->fetch("inc/cart_total.html");
		$data = $result;
		$data['html'] = $html;
	
		ajax_return($data);
	}
	
	/**
	 * 验证优惠券
	 */
	public function verify_ecv()
	{
		global_run();
		$ecvsn = strim($_REQUEST['ecvsn']);
		$ecvpassword = strim($_REQUEST['ecvpassword']);
		$user_id = intval($GLOBALS['user_info']['id']);
		$now = NOW_TIME;
		$ecv_sql = "select e.*,et.name from ".DB_PREFIX."ecv as e left join ".
				DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.sn = '".
				$ecvsn."' and e.password = '".
				$ecvpassword."' and ((e.begin_time <> 0 and e.begin_time < ".$now.") or e.begin_time = 0) and ".
				"((e.end_time <> 0 and e.end_time > ".$now.") or e.end_time = 0) and ((e.use_limit <> 0 and e.use_limit > e.use_count) or (e.use_limit = 0)) ".
				"and (e.user_id = ".$user_id." or e.user_id = 0)";

		$ecv_data = $GLOBALS['db']->getRow($ecv_sql);
		if($ecv_data)
			$data['info'] = "[".$ecv_data['name']."] ".$GLOBALS['lang']['IS_VALID'];
		else
			$data['info'] = $GLOBALS['lang']['IS_INVALID_ECV'];
		ajax_return($data);
	}
	
	/*发布面板HTML*/
	public function publish_box(){
		global_run();
		$data = array();
		$data['status'] = 1;
		$data['html'] = '';
		if(check_save_login()!=LOGIN_STATUS_NOLOGIN){
			$data['html'] = $GLOBALS['tmpl']->fetch("inc/publish_box.html");
		}else{
			$data['status'] = 0;
		}
		ajax_return($data);
	}
	
	/*快捷发布宝贝面板*/
	public function publish_goods(){
		$data = array();
		$data['status'] = 1;
		$data['html'] = '';
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/publish_goods.html");
		ajax_return($data);
	}
	
	public function do_fetch(){
		$class_name = addslashes(trim($_REQUEST['class_name']));
		$url = trim($_REQUEST['url']);
		$result['status'] = 0;
		if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$class_name."_fetch_topic.php"))
		{
			require_once APP_ROOT_PATH."system/fetch_topic/".$class_name."_fetch_topic.php";
			$class = $class_name."_fetch_topic";
			if(class_exists($class))
			{
				$api = new $class;
				$rs = $api->fetch($url);
				if($rs['status']==0)
				{
					$result['info'] = $rs['info'];
				}
				else
				{
					$result['status'] = 1;
					$result['group'] = $class_name;
					$result['group_data'] = $rs['group_data'];
					$result['content'] = $rs['content'];
					$result['type'] = $rs['type'];
					$result['tags'] = $rs['tags'];
					$result['images'] = $rs['images'];
				}
			}
			else
			{
				$result['info'] = "接口不存在";
			}
		}
		else
		{
			$result['info'] = "接口不存在";
		}
		
		ajax_return($result);
	}
	/**
	 * 商品编辑面板
	 */
	public function publish_goods_info(){
		$data = array();
		$data['status'] = 1;
		$result_data = $_REQUEST['data'];

		$group_data = unserialize(base64_decode($_REQUEST['data']['group_data']));

		$content = $_REQUEST['data']['content'];
		
		//输出表情数据html
		$result = $GLOBALS['db']->getAll("select `type`,`title`,`emotion`,`filename` from ".DB_PREFIX."expression order by type");
		$expression = array();
		foreach($result as $k=>$v)
		{
			$v['filename'] = "./public/expression/".$v['type']."/".$v['filename'];
			$v['emotion'] = str_replace(array('[',']'),array('',''),$v['emotion']);
			$expression[$v['type']][] = $v;
		}
		if($result_data['tags']){
			$tags = $result_data['tags'];
		}
		
		$GLOBALS['tmpl']->assign("tags",$tags);
		$GLOBALS['tmpl']->assign("expression",$expression);
		
		$GLOBALS['tmpl']->assign("content",$content);
		$GLOBALS['tmpl']->assign("result_data",$result_data);
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/publish_goods_edit.html");
		ajax_return($data);
	}
	/**
	 * 文章编辑面板
	 */
	public function publish_article_edit(){
		$data = array();
		$data['status'] = 1;
		$group_id = intval($_REQUEST['group_id']);
		if($group_id>0){
			$group_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."topic_group WHERE id = ".$group_id);
			$GLOBALS['tmpl']->assign("group_info",$group_info);
		}

		//输出表情数据html 和标签数据
		$result = $GLOBALS['db']->getAll("select `type`,`title`,`emotion`,`filename` from ".DB_PREFIX."expression order by type");
		$expression = array();
		foreach($result as $k=>$v)
		{
			$v['filename'] = "./public/expression/".$v['type']."/".$v['filename'];
			$v['emotion'] = str_replace(array('[',']'),array('',''),$v['emotion']);
			$expression[$v['type']][] = $v;
		}
		$tag_list =$GLOBALS['db']->getAll("select name from ".DB_PREFIX."topic_tag where is_preset = 1 order by count desc");
		//小组分类
		$cate_list=  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group_cate where is_effect = 1 order by sort desc");
		
		$GLOBALS['tmpl']->assign("tag_list",$tag_list);
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->assign("expression",$expression);	

		$data['html'] = $GLOBALS['tmpl']->fetch("inc/publish_article_edit.html");

		ajax_return($data);
	}
	
	/**
	 * 打开加载面板
	 */
	public function publish_loading_box(){
		$data = array();
		$data['status'] = 1;
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/publish_loading_box.html");
		ajax_return($data);
	}
	
	public function publish_img_edit(){
		$data = array();
		$data['status'] = 1;
		$data['html'] = '';
		
		$img_ids = $_POST['img_ids'];
		$img_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."topic_image WHERE id in(".implode(",", $img_ids).")");
		//输出表情数据html 和标签数据
		$result = $GLOBALS['db']->getAll("select `type`,`title`,`emotion`,`filename` from ".DB_PREFIX."expression order by type");
		$expression = array();
		foreach($result as $k=>$v)
		{
			$v['filename'] = "./public/expression/".$v['type']."/".$v['filename'];
			$v['emotion'] = str_replace(array('[',']'),array('',''),$v['emotion']);
			$expression[$v['type']][] = $v;
		}
		$tag_list =$GLOBALS['db']->getAll("select name from ".DB_PREFIX."topic_tag where is_preset = 1 order by count desc");
		
		$GLOBALS['tmpl']->assign("img_list",$img_list);
		$GLOBALS['tmpl']->assign("tag_list",$tag_list);
		$GLOBALS['tmpl']->assign("expression",$expression);
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/publish_img_edit.html");
		ajax_return($data);
	}
	
	/**
	 * 快捷发布保存
	 */
	public function publish_save(){
		global_run();
		$ajax = intval($_REQUEST['ajax']);
		
		if(check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		}
		if($_REQUEST['content']=='')
		{
			showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY'],$ajax);
		}
		
		if(!check_ipop_limit(get_client_ip(),"message",intval(app_conf("SUBMIT_DELAY")),0))
		{
			showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST'],$ajax);
		}

		$forum_title = strim(valid_str($_REQUEST['forum_title']));
		$group_id = intval($_REQUEST['group_id']);
        $syn_weibo = intval($_REQUEST['syn_weibo']);
		if($group_id>0)
		{
			if($forum_title=='')
			showErr("请输出发表的主题",$ajax);
			
			$user_id = intval($GLOBALS['user_info']['id']);
			$group_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$group_id);
			if($group_info['user_id']!=$user_id) //不是组长进行验证
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_topic_group where group_id=".$group_id." and user_id = ".$user_id)==0)
				{
					//不是会员自动加入小组
					$ins_user_topic = array(
							'group_id'=>$group_id,
							'user_id'=>$user_id,
							'create_time'=>NOW_TIME,
							'type'=>0
							);					
					$GLOBALS['db']->autoExecute(DB_PREFIX."user_topic_group",$ins_user_topic);
					$id = $GLOBALS['db']->insert_id();
					if($id){
						$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set user_count = user_count + 1 where id=".$group_id);
					}
				}
			}
		}
		
		
		$title = strim(valid_str($_REQUEST['forum_title']));
		
		$content = strim(valid_str($_REQUEST['content']));
		$group = strim($_REQUEST['group']);
		$group_data = strim($_REQUEST['group_data']);
		$type = strim($_REQUEST['type']);
		$tags_data = $_REQUEST['tags'];
		$tags = array();
		if($tags_data){
			$tag_row_arr = explode(" ",$tags_data);
			foreach($tag_row_arr as $tag_item)
			{
				$tag_item = strim($tag_item);
				if(!in_array($tag_item,$tags))
				{
					$tags[] = strim($tag_item);
				}
			}
		}elseif($forum_title){
			$tags = div_str($forum_title);
			if(count($tags)>5){
				$tags = array_slice($tags,0,4);
			}
		}

		$ungroup_date = unserialize(base64_decode($group_data));
		$url_route = array(
				'rel_app_index'=>$ungroup_date['url']['app_index'],
				'rel_route'=>$ungroup_date['url']['route'],
				'rel_param'=>''
				);
					
		$attach_list=get_topic_attach_list(); 	
		require_once APP_ROOT_PATH.'/system/model/topic.php';
		$id = insert_topic($content,$title,$type,$group, $relay_id = 0, $fav_id = 0,$group_data,$attach_list,$url_route,$tags,'','',$forum_title,$group_id,$syn_weibo);	
		
		if($id)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($id));
			increase_user_active(intval($GLOBALS['user_info']['id']),"发表了一则分享");
		}		

		if($ajax==1)
		{
			$result['info'] = $GLOBALS['lang']['MESSAGE_POST_SUCCESS'];
			$result['data'] = intval($id);
			$result['status'] = 1;
			ajax_return($result);
		}
		else
		{
			if($group_id>0)
				$url = url("index","group#forum",array("id"=>$group_id));
			showSuccess($GLOBALS['lang']['MESSAGE_POST_SUCCESS'],$ajax,$url);	
		}
	}

	
	/**
	 * 根据小组分类获取小组信息
	 * parme cate_id
	 */
	public function get_group_by_cateid(){
		$cate_id =  intval($_POST['cate_id']);
		if($cate_id>0){
			$group_list = $GLOBALS['db']->getAll("SELECT id,name FROM ".DB_PREFIX."topic_group WHERE cate_id=".$cate_id);
		}
		ajax_return($group_list);
	}
		
	
	public function add_collect()
	{
		global_run();
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = -1;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($result);
		}
		else
		{
			$goods_id = intval($_REQUEST['id']);
			$goods_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$goods_id." and is_effect = 1 and is_delete = 0");
			if($goods_info)
			{
				$sql = "INSERT INTO `".DB_PREFIX."deal_collect` (`id`,`deal_id`, `user_id`, `create_time`) select '0','".$goods_info['id']."','".intval($GLOBALS['user_info']['id'])."','".get_gmtime()."' from dual where not exists (select * from `".DB_PREFIX."deal_collect` where `deal_id`= '".$goods_info['id']."' and `user_id` = ".intval($GLOBALS['user_info']['id']).")";
				$GLOBALS['db']->query($sql);
				if($GLOBALS['db']->affected_rows()>0){
					$result['info'] = $GLOBALS['lang']['COLLECT_SUCCESS'];
					$result['status'] = 1;
					ajax_return($result);					
				}else{
					$result['info'] = $GLOBALS['lang']['GOODS_COLLECT_EXIST'];
					$result['status'] = 0;
					ajax_return($result);					
				}
			}
			else
			{
				$result['status'] = 0;
				$result['info'] = $GLOBALS['lang']['INVALID_GOODS'];
				ajax_return($result);
			}
		}
	}
	
	
	/**
	 * 下载优惠券
	 */
	public function download_youhui()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);	
		}
		
		$id = intval($_REQUEST['id']);
		require_once APP_ROOT_PATH."system/model/youhui.php";
		$youhui_info = get_youhui($id);
		
		$result = download_youhui(intval($youhui_info['id']),$GLOBALS['user_info']['id']);
		
		if($result['status']>=0)
		{
			if($result['status']==YOUHUI_OUT_OF_STOCK||$result['status']==YOUHUI_USER_OUT_OF_STOCK)
			{
				$data['status'] = 0;
				$data['info'] = $result['info'];
				if($result['log'])
				{
					$data['jump']  = url("index","uc_youhui");
				}
				ajax_return($data);
			}
			else if($result['status']==YOUHUI_DOWNLOAD_SUCCESS)
			{
				if(app_conf("SMS_ON")==1&&$result['log']['mobile']!=""&&$youhui_info['is_sms']==1)
				{					
					//发送短信
					send_youhui_log_sms($result['log']['id']);
				}
				$GLOBALS['tmpl']->assign("youhui_info",$youhui_info);
				$GLOBALS['tmpl']->assign("result",$result);
				$data['status'] = 1;
				$data['info'] = $result['info'];
				$data['html'] = $GLOBALS['tmpl']->fetch("inc/youhui_win.html");
				ajax_return($data);
			}
			else 
			{
				$data['status'] = 0;
				$data['info'] = $result['info'];
				ajax_return($data);
			}
		}
		else
		{
			$data['status'] = 0;
			$data['info'] = $result['info'];
			ajax_return($data);
		}
		

		
	}
	
	
	/**
	 * 收藏优惠券
	 */
	public function collect_youhui()
	{
		global_run();
		if(check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
				
		$id = intval($_REQUEST['id']);
		require_once APP_ROOT_PATH."system/model/youhui.php";
		$youhui_info = get_youhui($id);
		if($youhui_info)
		{
			$sc_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_sc where uid = ".$GLOBALS['user_info']['id']." and youhui_id = ".$id);
			if($sc_data)
			{
				$data['status'] = 0;
				$data['info'] = "您已经收藏过该优惠券";
				ajax_return($data);
			}
			else
			{
				$sc_data = array();
				$sc_data['uid'] = $GLOBALS['user_info']['id'];
				$sc_data['youhui_id'] = $id;
				$sc_data['add_time'] =NOW_TIME;				
				$GLOBALS['db']->autoExecute(DB_PREFIX."youhui_sc",$sc_data); //插入
				$data['status'] = 1;
				$data['info'] = "收藏成功";
				ajax_return($data);
			}
		}
		else
		{
			$data['status'] = 0;
			$data['info'] = "优惠券不存在";
			ajax_return($data);
		}
	}
	
	
	/**
	 * 收藏活动
	 */
	public function collect_event()
	{
		global_run();
		if(check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
	
		$id = intval($_REQUEST['id']);
		require_once APP_ROOT_PATH."system/model/event.php";
		$event_info = get_event($id);
		if($event_info)
		{
			$sc_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_sc where uid = ".$GLOBALS['user_info']['id']." and event_id = ".$id);
			if($sc_data)
			{
				$data['status'] = 0;
				$data['info'] = "您已经收藏过该活动";
				ajax_return($data);
			}
			else
			{
				$sc_data = array();
				$sc_data['uid'] = $GLOBALS['user_info']['id'];
				$sc_data['event_id'] = $id;
				$sc_data['add_time'] = NOW_TIME;
				$GLOBALS['db']->autoExecute(DB_PREFIX."event_sc",$sc_data); //插入
				$data['status'] = 1;
				$data['info'] = "收藏成功";
				ajax_return($data);
			}
		}
		else
		{
			$data['status'] = 0;
			$data['info'] = "活动不存在";
			ajax_return($data);
		}
	}
	
	/**
	 * 载入主题评论
	 */
	public function load_topic_reply_list(){
		global_run();
		if($GLOBALS['user_info']){
			$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		}
		$topic_id = $_POST['topic_id'];
		require_once APP_ROOT_PATH.'system/model/topic.php';
		require_once APP_ROOT_PATH."app/Lib/page.php";
		//分页
		$page_size = 5;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		$reply_list = get_topic_reply_list($topic_id,$limit);
		
		foreach($reply_list as $k=>$v)
		{
			$reply_list[$k]['content'] = decode_topic_without_img($v['content']);
		}

		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_reply where is_effect=1 and is_delete=0 and topic_id=".$topic_id);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();

		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('reply_list',$reply_list);
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/topic_reply_list.html");
		ajax_return($data);
	}

	public function app_download()
	{	
		$type=strim($_REQUEST['t']);
		$GLOBALS['tmpl']->assign("type",$type);
		if($type=="android"){
			//$app_url=SITE_DOMAIN.url("index",app_conf("ANDROID_PATH"));		
			$app_url = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'android_filename'");
		}elseif($type=="ios"){
			//$app_url=SITE_DOMAIN.url("index",app_conf("APPLE_PATH"));	
			$app_url = $GLOBALS['db']->getOne("select val from ".DB_PREFIX."m_config where code = 'ios_down_url'");
		}
		$qrcode_url=SITE_DOMAIN.url("index","app_download");		
//		if(app_conf("QRCODE_SIZE")==1){
//			$qrcode_size=3;
//		}elseif(app_conf("QRCODE_SIZE")==3){
//			$qrcode_size=5;
//		}else{
//			$qrcode_size=4;
//		}
	   $GLOBALS['tmpl']->assign("qrcode_size",app_conf("QRCODE_SIZE"));
		$GLOBALS['tmpl']->assign("qrcode_url",$qrcode_url);
		$GLOBALS['tmpl']->assign("app_url",$app_url);
		$GLOBALS['tmpl']->display("app_download_box.html");
	}	

	/**
	 * 删除主题评论
	 */
	public function delete_topic_reply(){
		$id = intval($_POST['id']);
		require_once APP_ROOT_PATH.'system/model/topic.php';
		ajax_return(del_topic_reply($id));
	}
	
	public function do_del_topic(){

		global_run();
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		}
		else
		{
			$id = intval($_REQUEST['id']);
			$topic = $GLOBALS['db']->getRow("select id,user_id from ".DB_PREFIX."topic where id = ".$id);
			if(!$topic)
			{
				$result['status'] = 0;
				$result['info'] = $GLOBALS['lang']['TOPIC_NOT_EXIST'];
			}
			else
			{
		
				$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where (fav_id = ".$id." or (origin_id = ".$id." and fav_id <> 0)) and user_id = ".intval($GLOBALS['user_info']['id']));
				if($count>0)
				{
					$result['status'] = 1;
					require_once APP_ROOT_PATH."system/model/topic.php";
					$del_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."topic where (fav_id = ".$id." or (origin_id = ".$id." and fav_id <> 0)) and user_id = ".intval($GLOBALS['user_info']['id']));
					$tid = delete_topic($del_id);
				}
				else
				{
					$result['status'] = 0;
					$result['info'] = "你还没喜欢";
						
				}
		
			}
		}
		ajax_return($result);
	}


	/**
	 * 加载商户弹出地址
	 */
	public function load_store_map()
	{
		$store_id = intval($_REQUEST['id']);
		require_once APP_ROOT_PATH."system/model/supplier.php";
		$store_info = get_location($store_id);
		if(empty($store_info))
		{
			$data['status'] = false;
			$data['info'] = "商家不存在";
			ajax_return($data);
		}
		$GLOBALS['tmpl']->assign("store_info",$store_info);
		$data['status'] = true;
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/store_pop_map.html");
		$data['info'] = $store_info['name'];
		ajax_return($data);
	}
	
	
	/**
	 * 以下store_load_xxx函数为门店详细页加载相关数据的ajax请求
	 */

	public function store_load_supplier_deal()
	{
		$store_id = intval($_REQUEST['store_id']);
		$page_size = 5;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		require_once APP_ROOT_PATH."system/model/deal.php";

		$supplier_data_result = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array()," left join ".DB_PREFIX."deal_location_link as l on d.id = l.deal_id "," d.buy_type <> 1 and d.is_shop = 0 and l.location_id =".$store_id);
		$GLOBALS['tmpl']->assign("supplier_data_list",$supplier_data_result['list']);
	
		//分页
		require_once APP_ROOT_PATH."app/Lib/page.php";	
	
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on d.id = l.deal_id where ".$supplier_data_result['condition']);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('total',$total);
		$GLOBALS['tmpl']->assign('page_size',$page_size);
	
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/store_page/store_supplier_deal.html");
		ajax_return($data);
	}
	
	public function store_load_supplier_shop()
	{
		$store_id = intval($_REQUEST['store_id']);
		$page_size = 5;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		require_once APP_ROOT_PATH."system/model/deal.php";
	
		$supplier_data_result = get_goods_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array()," left join ".DB_PREFIX."deal_location_link as l on d.id = l.deal_id "," d.buy_type <> 1 and d.is_shop = 1 and l.location_id =".$store_id);
		$GLOBALS['tmpl']->assign("supplier_data_list",$supplier_data_result['list']);
	
		//分页
		require_once APP_ROOT_PATH."app/Lib/page.php";
	
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on d.id = l.deal_id where ".$supplier_data_result['condition']);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('total',$total);
		$GLOBALS['tmpl']->assign('page_size',$page_size);
	
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/store_page/store_supplier_shop.html");
		ajax_return($data);
	}
	
	public function store_load_supplier_youhui()
	{
		$store_id = intval($_REQUEST['store_id']);
		$page_size = 5;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		require_once APP_ROOT_PATH."system/model/youhui.php";
	
		
		$supplier_data_result = get_youhui_list($limit,array(YOUHUI_NOTICE,YOUHUI_ONLINE),array(), ' left join '.DB_PREFIX."youhui_location_link as l on y.id = l.youhui_id "," l.location_id = ".$store_id);
		$GLOBALS['tmpl']->assign("supplier_data_list",$supplier_data_result['list']);
	
		//分页
		require_once APP_ROOT_PATH."app/Lib/page.php";
	
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on y.id = l.youhui_id where ".$supplier_data_result['condition']);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('total',$total);
		$GLOBALS['tmpl']->assign('page_size',$page_size);
	
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/store_page/store_supplier_youhui.html");
		ajax_return($data);
	}
	
	
	public function store_load_supplier_event()
	{
		$store_id = intval($_REQUEST['store_id']);
		$page_size = 5;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		require_once APP_ROOT_PATH."system/model/event.php";
	
	
		$supplier_data_result = get_event_list($limit,array(EVENT_NOTICE,EVENT_ONLINE),array()," left join ".DB_PREFIX."event_location_link as l on e.id = l.event_id "," l.location_id = ".$store_id);
	
		$GLOBALS['tmpl']->assign("supplier_data_list",$supplier_data_result['list']);
	
		//分页
		require_once APP_ROOT_PATH."app/Lib/page.php";
	
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event as e left join ".DB_PREFIX."event_location_link as l on e.id = l.event_id where ".$supplier_data_result['condition']);
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		$GLOBALS['tmpl']->assign('total',$total);
		$GLOBALS['tmpl']->assign('page_size',$page_size);
	
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/store_page/store_supplier_event.html");
		ajax_return($data);
	}
	
	/**
	 * 加载推荐用户
	 */
	public function load_best_user(){
		global_run();
		$count = intval($_REQUEST['count']);
		if($GLOBALS['user_info'])
			$uid = $GLOBALS['user_info']['id'];
		else
			$uid = 0;
		
		$rand_user_list = get_rand_user($count,0,$uid);	
		$GLOBALS['tmpl']->assign("uc_u_list",$rand_user_list);
		$data = $GLOBALS['tmpl']->fetch("inc/best_user_list.html");
		ajax_return($data);
	}
	
	
	/**
	 * 清空浏览历史
	 */
	public function clear_history()
	{
		global_run();
		$type = strim($_REQUEST['type']);		
		if($type=="alldeal")
		{
			rm_auto_cache("cache_history",array("type"=>"deal","city_id"=>$GLOBALS['city']['id']));
			rm_auto_cache("cache_history",array("type"=>"shop","city_id"=>$GLOBALS['city']['id']));
		}
		else
		rm_auto_cache("cache_history",array("type"=>$type,"city_id"=>$GLOBALS['city']['id']));
		$data['status'] = 1;
		ajax_return($data);
	}
	
	
	/**
	 * 加载报名弹出项
	 */
	public function load_event_submit()
	{
		global_run();
		if(check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		
		$event_id = intval($_REQUEST['id']);
		require_once APP_ROOT_PATH."system/model/event.php";
		$event = get_event($event_id);
		if(!$event)
		{
			$data['status'] = 0;
			$data['info'] = "活动不存在";
			ajax_return($data);
		}
		if($event['submit_begin_time']>NOW_TIME)
		{
			$data['status'] = 0;
			$data['info'] = "活动报名未开始";
			ajax_return($data);
		}
		if($event['submit_end_time']>0&&$event['submit_end_time']<NOW_TIME)
		{
			$data['status'] = 0;
			$data['info'] = "活动报名已结束";
			ajax_return($data);
		}
		if($event['submit_count']>=$event['total_count']&&$event['total_count']>0)
		{
			$data['status'] = 0;
			$data['info'] = "活动名额已满";
			ajax_return($data);
		}
		
		$GLOBALS['tmpl']->assign("event_id",$event_id);
		$user_id = intval($GLOBALS['user_info']['id']);
		$user_submit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where user_id = ".$user_id." and event_id = ".$event_id);
		if($user_submit)
		{
			if($user_submit['is_verify']==1)
			{
				$data['status'] = 0;
				$data['info'] = "您已经报名";
				ajax_return($data);
			}
			else 
			{
				$event_fields = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$event_id." order by sort asc");
				foreach($event_fields as $k=>$v)
				{
					$event_fields[$k]['result'] = $GLOBALS['db']->getOne("select result from ".DB_PREFIX."event_submit_field where submit_id = ".$user_submit['id']." and field_id = ".$v['id']." and event_id = ".$event_id);
					$event_fields[$k]['value_scope'] = explode(" ",$v['value_scope']);
				}
				$GLOBALS['tmpl']->assign("event_fields",$event_fields);
				$GLOBALS['tmpl']->assign("user_submit",$user_submit);  //表示修改已报名记录
				$GLOBALS['tmpl']->assign("btn_name","修改报名");
			}
		}
		else
		{
			$event_fields = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$event_id." order by sort asc");
			foreach($event_fields as $k=>$v)
			{
				$event_fields[$k]['value_scope'] = explode(" ",$v['value_scope']);
			}
			$GLOBALS['tmpl']->assign("event_fields",$event_fields);
			$GLOBALS['tmpl']->assign("btn_name","立即报名");
		}
		
		$data['status'] = 1;
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/event_submit.html");
		ajax_return($data);
	}
	/**
	 * UC 兑换
	 */
	function doexchange()
	{
	    global_run();
	    if(!$GLOBALS['user_info']){
	        $data = array("status"=>-1000,"message"=>"用户未登录");
	        ajax_return($data);
	    }
	    /**
	     * 验证是否可以兑换
	     */
	    $allow_exchange = false;
	    if(file_exists(APP_ROOT_PATH."public/uc_config.php"))
	    {
	        require_once APP_ROOT_PATH."public/uc_config.php";
	    }
	    if(app_conf("INTEGRATE_CODE")=='Ucenter'&&UC_CONNECT=='mysql')
	    {
	        if(file_exists(APP_ROOT_PATH."public/uc_data/creditsettings.php"))
	        {
	            require_once APP_ROOT_PATH."public/uc_data/creditsettings.php";
	            $creditsettings = $_CACHE['creditsettings'];
	            if(count($creditsettings)>0)
	            {
	                $allow_exchange = true;
	            }
	        }
	    }
	    $credits_CFG = array(
	        '1' => array('title'=>'经验', 'unit'=>'' ,'field'=>'point'),
	        '2' => array('title'=>'积分', 'unit'=>'' ,'field'=>'score'),
	        '3' => array('title'=>'资金', 'unit'=>'' ,'field'=>'money'),
	    );
	    if($allow_exchange)
	    {
	        $user_pwd = md5(strim($_REQUEST['password']));
	        $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));
	        if($user_info['user_pwd']=="")
	        {
	            //判断是否为初次整合
	            //载入会员整合
	            $integrate_code = strim(app_conf("INTEGRATE_CODE"));
	            if($integrate_code!='')
	            {
	                $integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
	                if(file_exists($integrate_file))
	                {
	                    require_once $integrate_file;
	                    $integrate_class = $integrate_code."_integrate";
	                    $integrate_obj = new $integrate_class;
	                }
	            }
	            if($integrate_obj)
	            {
	                $result = $integrate_obj->login($user_info['user_name'],$user_pwd);
	                if($result['status'])
	                {
	                    $GLOBALS['db']->query("update ".DB_PREFIX."user set user_pwd = '".$user_pwd."' where id = ".$user_info['id']);
	                    $user_info['user_pwd'] = $user_pwd;
	                }
	            }
	        }
	        if($user_info['user_pwd']==$user_pwd)
	        {
	            $cfg = $creditsettings[strim($_REQUEST['key'])];
	            if($cfg)
	            {
	                $amount = floor($_REQUEST['amountdesc']);
	                $use_amount = floor($amount*$cfg['ratio']); //消耗的本系统积分

	                $field = $credits_CFG[$cfg['creditsrc']]['field'];

	                if($user_info[$field]<$use_amount)
	                {
	                    $data = array("status"=>false,"message"=>$cfg['srctitle']."不足，不能兑换");
	                    ajax_return($data);
	                }
	                	
	                include_once(APP_ROOT_PATH . 'uc_client/client.php');
	                $res = call_user_func_array("uc_credit_exchange_request", array(
	                    $user_info['integrate_id'],  //uid(整合的UID)
	                    $cfg['creditsrc'],  //原积分ID
	                    $cfg['creditdesc'],  //目标积分ID
	                    $cfg['appiddesc'],  //toappid目标应用ID
	                    $amount,  //amount额度(计算过的目标应用的额度)
	                ));
	                if($res)
	                {
	                    //兑换成功
	                    $use_amount = 0 - $use_amount;
	                    $credit_data = array($field=>$use_amount);
	                    require_once APP_ROOT_PATH."system/model/user.php";
	                    modify_account($credit_data,$user_info['id'],"ucenter兑换支出");
	                    $data = array("status"=>true,"message"=>"兑换成功");
	                    ajax_return($data);
	                }
	                else
	                {
	                    $data = array("status"=>false,"message"=>"兑换失败");
	                    ajax_return($data);
	                }
	            }
	            else
	            {
	                $data = array("status"=>false,"message"=>"非法的兑换请求");
	                ajax_return($data);
	            }
	        }
	        else
	        {
	            $data = array("status"=>false,"message"=>"登录密码不正确");
	            ajax_return($data);
	        }
	    }
	    else
	    {
	        $data = array("status"=>false,"message"=>"未开启兑换功能");
	        ajax_return($data);
	    }
	}
	
	public function modify_consignee()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		$user_id=intval($GLOBALS['user_info']['id']);
		//输出所有配送方式
		$consignee_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_consignee where user_id = ".$user_id);
		if($consignee_list)
		{
			foreach($consignee_list as $k=>$v){
				$consignee_list[$k]['region_lv2']  = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv2']);
				$consignee_list[$k]['region_lv3']  = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv3']);
				$consignee_list[$k]['region_lv4']  = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."delivery_region where id = ".$v['region_lv4']);
			}
			
			
			$GLOBALS['tmpl']->assign("consignee_list",$consignee_list);
			
			$data['html'] = $GLOBALS['tmpl']->fetch("inc/uc_consignee.html");
			$data['status'] = 1;
			ajax_return($data);
		}
		else
		{
			$data['info'] = "没有预设的配送地址";
			$data['status'] = 0;
			ajax_return($data);
		}
	}
	/**
	 * 解除API绑定
	 */
	public function unset_bind_api(){
	    global_run();
	    if(!$GLOBALS['user_info']){
	        $result['status'] = -1;
	        $result['info'] = "请先登录后操作！";
	        ajax_return($result);
	    }
	    
	    $class_name = strim($_REQUEST['class_name']);
	    $apis = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");
	    foreach($apis as $k=>$v)
	    {
	        $api_class[$v['class_name']] = strtolower($v['class_name']);
	    }
	    if(!in_array(strtolower($class_name), $api_class)){
	       $result['status'] = 0;
	       $result['info'] = "参数错误";
	       ajax_return($result);   
	    }
	    //引入接口
	    if(file_exists(APP_ROOT_PATH."system/api_login/".$class_name."_api.php"))
	    {
	        require_once APP_ROOT_PATH."system/api_login/".$class_name."_api.php";
	        $class_name_obj = $class_name."_api"; 
	        $api_obj = new $class_name_obj($api_class[$class_name]);
	        $api_obj->unset_api();
	        $result['status'] = 1;
	        $result['info'] = "解除绑定";
	        require_once APP_ROOT_PATH."system/model/user.php";
	        load_user($GLOBALS['user_info']['id'],true);
	        ajax_return($result);
	    }else{
	        $result['status'] = 0;
	        $result['info'] = "接口不存在";
	        ajax_return($result);
	    }
	}
	
	public function set_syn_weibo(){
	    global_run();
	    if(!$GLOBALS['user_info']){
	        $result['status'] = -1;
	        $result['info'] = "请先登录后操作！";
	        ajax_return($result);
	    }
	    $class_name = strim($_REQUEST['class_name']);
	    $apis = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login where is_weibo = 1");
	    foreach($apis as $k=>$v)
	    {
	        $api_class[$v['class_name']] = strtolower($v['class_name']);
	    }
	    if(!in_array(strtolower($class_name), $api_class)){
	       $result['status'] = 0;
	       $result['info'] = "参数错误";
	       ajax_return($result);   
	    }
	    //引入接口
	    if(file_exists(APP_ROOT_PATH."system/api_login/".$class_name."_api.php"))
	    {
	        require_once APP_ROOT_PATH."system/api_login/".$class_name."_api.php";
	        $class_name_obj = $class_name."_api"; 
	        $api_obj = new $class_name_obj($api_class[$class_name]);
	        $result = $api_obj->set_syn_weibo();
	        require_once APP_ROOT_PATH."system/model/user.php";
	        load_user($GLOBALS['user_info']['id'],true);
	        ajax_return($result);
	    }else{
	        $result['status'] = 0;
	        $result['info'] = "接口不存在";
	        ajax_return($result);
	    }
	    
	    
	}
	
	public function change_favdeal()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		require_once APP_ROOT_PATH."system/model/deal.php";
		$deal = get_deal($deal_id);
		//输出右侧的其他团购
		if($deal['is_shop']==0)
			$side_deal_list = get_deal_list(5,array(DEAL_ONLINE,DEAL_NOTICE),array("cid"=>$deal['cate_id'],"city_id"=>$GLOBALS['city']['id']),"","  d.buy_type <> 1 and d.is_shop = 0 and d.id<>".$deal['id']," rand() ");
		elseif($deal['is_shop']==1)
		{
			if($deal['buy_type']==1)
				$side_deal_list = get_goods_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE,DEAL_NOTICE),array("cid"=>$deal['shop_cate_id'],"city_id"=>$GLOBALS['city']['id']),"","  d.buy_type = 1 and d.is_shop = 1 and d.id<>".$deal['id']," rand() ");
			else
				$side_deal_list = get_goods_list(app_conf("SIDE_DEAL_COUNT"),array(DEAL_ONLINE,DEAL_NOTICE),array("cid"=>$deal['shop_cate_id'],"city_id"=>$GLOBALS['city']['id']),"","  d.buy_type <> 1 and d.is_shop = 1 and d.id<>".$deal['id']," rand() ");
		}
	
	
		//$side_deal_list = get_deal_list(4,array(DEAL_ONLINE));
		$GLOBALS['tmpl']->assign("side_deal_list",$side_deal_list['list']);
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/side_favdeal_list.html");
		ajax_return($data);
	}
	
	public function check_payment_notice()
	{
		$id = intval($_REQUEST['notice_id']);
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$id);
		if($payment_notice['is_paid']==1)
		{
			$data['status'] = 1;
		}
		else
		{
			$data['status'] = 0;
		}
		ajax_return($data);
	}
	
	public function deal_shere(){
	    $id = intval($_REQUEST['id']);
	    require_once APP_ROOT_PATH.'system/model/topic.php';
	    $result = get_format_share_data("deal",$id);
	    ajax_return($result);
	}
	
	public function youhui_shere(){
	    $id = intval($_REQUEST['id']);
	    require_once APP_ROOT_PATH.'system/model/topic.php';
	    $result = get_format_share_data("youhui",$id);
	    ajax_return($result);
	}
	
	public function order_shere(){
	    $id = intval($_REQUEST['id']);
	    require_once APP_ROOT_PATH.'system/model/topic.php';
        order_share($id);

        $result['info'] = $GLOBALS['lang']['MESSAGE_POST_SUCCESS'];
        $result['status'] = 1;
        ajax_return($result);
        
	}
	
	//加载某个商品的关联购买
	public function load_relate_goods()
	{
		$deal_id = intval($_REQUEST['deal_id']);
		$relate_goods = $GLOBALS['db']->getOne("select relate_ids from ".DB_PREFIX."relate_goods where good_id = ".$deal_id);
		if($relate_goods){
			
			require_once APP_ROOT_PATH.'system/model/deal.php';
			
			//把主产品加入 result
			$result = getDetailedList($relate_goods.','.$deal_id);
			foreach( $result['goodsList'] as $k=>$item ){
				if( intval($item['id'])==$deal_id ){
					unset($result['goodsList'][$k]);
					break;
				}
			}
			$deal = get_deal($deal_id);
			
			$GLOBALS['tmpl']->assign("goodsList",$result['goodsList']);
			$GLOBALS['tmpl']->assign("jsonDeal",json_encode($result['dealArray']));
			$GLOBALS['tmpl']->assign("jsonAttr",json_encode($result['attrArray']));
			$GLOBALS['tmpl']->assign("jsonStock",json_encode($result['stockArray']));
			
			$GLOBALS['tmpl']->assign("mainDeal",$deal);

		}
		$data['html'] = $GLOBALS['tmpl']->fetch("inc/relate_goods.html");
		ajax_return($data);
	}
	
	/**
	 * 组合购买，多商品一起购买
	*/
	public function relate_add_cart(){
		$mainId    = intval($_REQUEST['mainId']);
		$idArray   = $_REQUEST['id'];
		$attrArray = $_REQUEST['attr_1'];
		if( count($idArray)<=0 ){
// 			$res['status'] = 0;
// 			$res['info'] = "没有可以购买的产品";
// 			ajax_return($rs);
			exit();
		}
	
		
		
		
		foreach( $idArray as $key=>$id ){
			$param = array();
			$param['id'] = $id;
			if( $attrArray[$key]!=0 ){
				$attr = explode('_',$attrArray[$key]);
				sort( $attr );
				foreach( $attr as $itemAttr ){
					$param['attr'][][] = $itemAttr;
				}
			}
			$rs = $this->addcart(false,$param);
			if( $rs['status']==0 ){
				ajax_return($rs);
				exit();
			}
		}
		
		require_once APP_ROOT_PATH.'system/model/cart.php';
		require_once APP_ROOT_PATH.'system/model/deal.php';
		
		$deal_info = get_deal($mainId);
		
		$cart_result = load_cart_list(true);
		$cart_total = count($cart_result['cart_list']);
		$GLOBALS['tmpl']->assign("cart_total",$cart_total);
			
		$relate_list = get_deal_list(4,array(DEAL_ONLINE),array("cid"=>$deal_info['cate_id'],"city_id"=>$GLOBALS['city']['id']),"","d.id<>".$deal_info['id']);
		
		$GLOBALS['tmpl']->assign("relate_list",$relate_list['list']);
			
		$res['html'] = $GLOBALS['tmpl']->fetch("inc/pop_cart.html");
		$res['status'] = 1;
		
		ajax_return($res);
		
	}
	
	
	
	
}
?>