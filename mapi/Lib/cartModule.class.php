<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

$lang = array(
		'DEAL_ERROR_1'	=>	'团购进行中',
		'DEAL_ERROR_2'	=>	'已过期',
		'DEAL_ERROR_3'	=>	'未开始',
		'DEAL_ERROR_4'	=>	'产品剩余库存不足',
		'DEAL_ERROR_5'	=>	'用户最小购买数不足',
		'DEAL_ERROR_6'	=>	'用户最大购买数超出',
);

class cartModule extends MainBaseModule
{
	
	/**
	 * 获取购物车列表
	 * 
	 * 输入:
	 * 无
	 * 
	 * 输出:
	 * is_score: int 当前购物车中的商品类型 0:普通商品，展示时显示价格 1:积分商品，展示时显示积分
	 * cart_list: object 购物车列表内容，结构如下
	 * Array
        (
            [478] => Array key [int] 购物车表中的主键
                (
                    [id] => 478 [int] 同key
                    [return_score] => 0 [int] 当is_score为1时单价的展示
                    [return_total_score] => 0 [int] 当is_score为1时总价的展示
                    [unit_price] => 108 [float] 当is_score为0时单价的展示
                    [total_price] => 108 [float] 当is_score为0时总价的展示
                    [number] => 1 [int] 购买件数
                    [deal_id] => 57 [int] 商品ID
                    [attr] => 287,290 [string] 购买商品的规格ID组合，用逗号分隔的规格ID
                    [name] => 桥亭活鱼小镇 仅售88元！价值100元的代金券1张 [9点至18点,2-5人套餐] [string] 商品全名，包含属性
                    [sub_name] => 88元桥亭活鱼小镇代金券 [9点至18点,2-5人套餐] [string] 商品缩略名，包含属性
                    [max] => int 最大购买量 加减时用
                    [icon] => string 商品图标 140x85
                )
		)
		
	 * total_data: array 购物车总价统计,结构如下
	 *	Array
        (
            [total_price] => 108 [float] 当is_score为0时的总价显示
            [return_total_score] => 0 [int] 当is_score为1时的总价显示
        )
     *  user_login_status:int 用户登录状态(1 已经登录/0 用户未登录) 该接口不返回临时登录状态，未登录时使用手机短信验证自动注册登录，已登录时判断is_mobile
     *  has_mobile: int 是否有手机号 0无 1有
	 */
	public function index()
	{
		require_once APP_ROOT_PATH."system/model/cart.php";
		$cart_result = load_cart_list();
		
		$cart_list_o = $cart_result['cart_list'];
		$cart_list = array();
		
		$total_data_o = $cart_result['total_data'];		
		$is_score = 0;
		foreach($cart_list_o as $k=>$v)
		{
			$bind_data = array();
			$bind_data['id'] = $v['id'];
			if($v['buy_type']==1)
			{
				$is_score = 1;
				$bind_data['return_score'] = abs($v['return_score']);
				$bind_data['return_total_score'] = abs($v['return_total_score']);
				$bind_data['unit_price'] = 0;
				$bind_data['total_price'] = 0;
			}
			else
			{
				$bind_data['return_score'] = 0;
				$bind_data['return_total_score'] = 0;
				$bind_data['unit_price'] = round($v['unit_price'],2);
				$bind_data['total_price'] = round($v['total_price'],2);
			}
			$bind_data['number'] = $v['number'];
			$bind_data['deal_id'] = $v['deal_id'];
			$bind_data['attr'] = $v['attr'];
			$bind_data['name'] = $v['name'];
			$bind_data['sub_name'] = $v['sub_name'];
			$bind_data['max'] = 100;
			$bind_data['icon'] = get_abs_img_root(get_spec_image($v['icon'],140,85,1)) ;
			$cart_list[$v['id']] = $bind_data;
		}
		
		
		$root = array();
		$root['cart_list'] = $cart_list?$cart_list:null;
		
		$total_data = array();
		
		if($is_score)
		{
			$total_data['total_price'] = 0;
			$total_data['return_total_score'] = abs($total_data_o['return_total_score']);
		}		
		else
		{
			$total_data['total_price'] = round($total_data_o['total_price'],2);
			$total_data['return_total_score'] = 0;
		}		

		$root['total_data'] = $total_data;
		$root['is_score'] = $is_score;		
		
		$user_login_status = check_login();
		
		
		if($GLOBALS['user_info']['mobile']=="")
			$root['has_mobile'] = 0;
		else
			$root['has_mobile'] = 1;
		
		if($user_login_status==LOGIN_STATUS_TEMP)
		{
			$user_login_status = LOGIN_STATUS_LOGINED; //购物车页不存在临时状态
		}
		
		$root['user_login_status'] = $user_login_status;
		$root['page_title'] = "购物车";
		output($root);
	}
	
	/**
	 * 加入购物车接口
	 * 
	 * 输入： 
	 * id:int 商品id
	 * deal_attr: array 结构如下
	 * Array
	 * (
	 *     [属性组ID] => 11 int 属性值ID
	 * )
	 
	 
	 * =======新增两个参数============
	 * @param bool $outputReturn 是否以output返回
	 * @param array $param 该值不为空，则加入购物车的id,attr以此为准，否则取$_REQUEST
	 * @param
				 *$param = Array
				 * (
				 	   [id]	  =>	商品id int
				 *     [attr] => 	Array(
										[属性组ID] => 11 int 属性值ID
									)
				 * )
	 * =======//新增两个参数============ *
	 
	  
	 * 
	 * 输出：
	 * status: int 状态 0错误 1加入成功 -1未登录需要登录
	 * info: string 状态为1时该值为空，否则为出错的提示
	 */
	public function addcart($outputReturn=true,$param=array())
	{
	
		$root = array();
		
		
		
		//========
		require_once APP_ROOT_PATH.'system/model/cart.php';
		require_once APP_ROOT_PATH.'system/model/deal.php';
		
		if( !empty($param)&&!empty($param['id']) ){
			$id = intval($param['id']);
			$deal_attr_req = $param['attr'];
		}else{
			$id = intval($GLOBALS['request']['id']);
			$deal_attr_req = $GLOBALS['request']['deal_attr'];
		}		
		$deal_attr = array();
		foreach ($deal_attr_req as $k=>$v)
		{
			$sv = intval($v);
			if($sv)
			$deal_attr[$k] = intval($sv);
		}
		
		$user_login_status = check_login();
		
		$deal_info = get_deal($id);
		if(!$deal_info||($deal_info['buyin_app']==1&&APP_INDEX=="wap"))
		{
			if($outputReturn){
				output("",0,"没有可以购买的产品");
			}else{
				return array('status'=>0,'info'=>'没有可以购买的产品');
			}
			
		}
		
		if(($deal_info['is_lottery']==1||$deal_info['buy_type']==1))
		{
			if($user_login_status==LOGIN_STATUS_NOLOGIN)
			{
				if($outputReturn){
					output($root,-1,"请先登录");
				}else{
					return array('status'=>-1,'info'=>'请先登录');
				}
			}
		}
			
		$check = check_deal_time($id);
		if($check['status'] == 0)
		{
			$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
			if($outputReturn){
				output($root,0,$res['info']);
			}else{
				return array('status'=>0,'info'=>$res['info']);
			}
			
		}			
		
		if(count($deal_attr)!=count($deal_info['deal_attr']))
		{
			$res['info'] = "请选择商品规格";
			if($outputReturn){
				output($root,0,$res['info']);
			}else{
				return array('status'=>0,'info'=>'请选择商品规格');
			}
			
		}
		else
		{
			//加入购物车处理，有提交属性， 或无属性时
			$attr_str = '0';
			$attr_name = '';
			$attr_name_str = '';
			if($deal_attr)
			{				
				$attr_str = implode(",",$deal_attr);
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
			$add_number = $number = 1; //只加一件
		
		
			//开始运算购物车的验证
			if($cart_item)
			{
		
// 				$check = check_deal_number($cart_item['deal_id'],$add_number);
// 				if($check['status']==0)
// 				{
// 					$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];						
// 					output($root,0,$res['info']);
// 				}
		
				//属性库存的验证
				$attr_setting_str = '';
				if($cart_item['attr']!='')
				{
					$attr_setting_str = $cart_item['attr_str'];
				}
		
		
					
// 				if($attr_setting_str!='')
// 				{
// 					$check = check_deal_number_attr($cart_item['deal_id'],$attr_setting_str,$add_number);
// 					if($check['status']==0)
// 					{
// 						$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
// 						output($root,0,$res['info']);
// 					}
// 				}
				//属性库存的验证
			}
			else //添加时的验证
			{
// 				$check = check_deal_number($deal_info['id'],$add_number);
// 				if($check['status']==0)
// 				{
// 					$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
// 					output($root,0,$res['info']);
// 				}
		
				//属性库存的验证
				$attr_setting_str = '';
				if($attr_name_str!='')
				{
					$attr_setting_str =$attr_name_str;
				}
		
		
					
// 				if($attr_setting_str!='')
// 				{
// 					$check = check_deal_number_attr($deal_info['id'],$attr_setting_str,$add_number);
// 					if($check['status']==0)
// 					{
		
// 						$res['info'] = $check['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$check['data']];
// 						output("",0,$res['info']);
// 					}
// 				}
				//属性库存的验证
			}
		
			if($deal_info['return_score']<0)
			{
				//需要积分兑换
				$user_score = intval($GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id'])));
				if($user_score < abs(intval($deal_info['return_score'])*$add_number))
				{		
					$res['info'] = $check['info']." "."积分不足";
					if($outputReturn){
						output($root,0,$res['info']);
					}else{
						return array('status'=>0,'info'=>$res['info']);
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
			load_cart_list(true);
			if($outputReturn){
				output($root);
			}else{
				return $root;
			}
		}
		//========
		
		
	}
	
	
	/**
	 * 提交修改购物车，并生成会员接口
	 * 
	 * 输入
	 * num: 购物车列表的数量修改 array
	 * 结构如下
	 * Array(
	 * 	"123"=>1  key[int] 购物车主键   value[int] 数量
	 * )
	 * 
	 * mobile string 手机号
	 * sms_verify string 手机验证码
	 * 
	 * 输出
	 * status: int 状态 0失败 1成功
	 * info: string 消息
	 * user_data: 当前的会员信息，用于同步本地信息 array
	 * Array(
	 * 	id:int 会员ID
	 *  user_name:string 会员名
	 *  user_pwd:string 加密过的密码
	 *  email:string 邮箱
	 *  mobile:string 手机号
	 *  is_tmp: int 是否为临时会员 0:否 1:是
	 * )
	 */
	public function check_cart()
	{
		
		$root = array();
		
		$num_req = $GLOBALS['request']['num'];		
		$num = array();
		foreach ($num_req as $k=>$v)
		{
			$sv = intval($v);
			if($sv)
				$num[$k] = intval($sv);
		}
		$user_mobile = strim($GLOBALS['request']['mobile']);
		$sms_verify = strim($GLOBALS['request']['sms_verify']);
		$user_login_status = check_login();
	
		
		
		require_once APP_ROOT_PATH."system/model/cart.php";		
		if($user_login_status==LOGIN_STATUS_NOLOGIN)
		{
				//自动创建会员或手机登录
				if(app_conf("SMS_ON")==0)
				{
					output($root,0,"短信功能未开启");
				}
				if($user_mobile=="")
				{
					output($root,0,"请输入手机号");
				}
				if($sms_verify=="")
				{
					output($root,0,"请输入收到的验证码");
				}
				
				$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
				$GLOBALS['db']->query($sql);
				
				$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$user_mobile."'");
				
				if($mobile_data['code']==$sms_verify)
				{
					//开始登录
					//1. 有用户使用已有用户登录
					//2. 无用户产生一个用户登录
					require_once APP_ROOT_PATH."system/model/user.php";
					
					$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where mobile = '".$user_mobile."'");
					$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$user_mobile."'");
					if($user_info)
					{
						//使用已有用户
						$result = do_login_user($user_info['user_name'],$user_info['user_pwd']);
			
						if($result['status'])
						{
			
							$s_user_info = es_session::get("user_info");
							$userdata['id'] = $s_user_info['id'];
							$userdata['user_name'] = $s_user_info['user_name'];
							$userdata['user_pwd'] = $s_user_info['user_pwd'];
							$userdata['email'] = $s_user_info['email'];
							$userdata['mobile'] = $s_user_info['mobile'];
							$userdata['is_tmp'] = $s_user_info['is_tmp'];
								
						}
						else
						{
							if($result['data'] == ACCOUNT_NO_EXIST_ERROR)
							{
								$field = "";
								$err = "用户不存在";
							}
							if($result['data'] == ACCOUNT_PASSWORD_ERROR)
							{
								$field = "";
								$err = "密码错误";
							}
							if($result['data'] == ACCOUNT_NO_VERIFY_ERROR)
							{
								$field = "";
								$err = "用户未通过验证";
							}
							output($root,0,$err);
						}
					}
					else
					{
						//ip限制
						$ip = get_client_ip();
						$ip_nums = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where login_ip = '".$ip."'");
						if($ip_nums>intval(app_conf("IP_LIMIT_NUM"))&&intval(app_conf("IP_LIMIT_NUM"))>0)
						{
							output($root,0,"IP受限");
						}
			
						if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where user_name = '".$user_mobile."' or mobile = '".$user_mobile."' or email = '".$user_mobile."'")>0)
						{
							output($root,0,"手机号已被抢占");
						}
			
						//生成新用户
						$user_data = array();
						$user_data['mobile'] = $user_mobile;
			
			
						$rs_data = auto_create($user_data, 1);
						if(!$rs_data['status'])
						{
							output($root,0,$rs_data['info']);
						}
			
						$result = do_login_user($rs_data['user_data']['user_name'],$rs_data['user_data']['user_pwd']);
			
						if($result['status'])
						{
							$s_user_info = es_session::get("user_info");
							$userdata['id'] = $s_user_info['id'];
							$userdata['user_name'] = $s_user_info['user_name'];
							$userdata['user_pwd'] = $s_user_info['user_pwd'];
							$userdata['email'] = $s_user_info['email'];
							$userdata['mobile'] = $s_user_info['mobile'];
							$userdata['is_tmp'] = $s_user_info['is_tmp'];
			
						}
						else 
						{
							output($root,0,"登录失败");
						}
					}

				}
				else
				{
					output($root,0,"验证码错误");
				}
				
				//end 自动创建会员或手机登录
		
		}
		else 
		{
			if($GLOBALS['user_info']['mobile']=="")
			{
				//绑定手机号
				if(app_conf("SMS_ON")==0)
				{
					output($root,0,"短信功能未开启");
				}
				if($user_mobile=="")
				{
					output($root,0,"请输入手机号");
				}
				if($sms_verify=="")
				{
					output($root,0,"请输入收到的验证码");
				}
				
				$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
				$GLOBALS['db']->query($sql);
				
				$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$user_mobile."'");
				
				if($mobile_data['code']==$sms_verify)
				{
					//开始绑定
					//1. 未登录状态提示登录
					//2. 已登录状态绑定
					require_once APP_ROOT_PATH."system/model/user.php";
						
					$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where mobile = '".$user_mobile."'");
					if($user_info)
					{
						$supplier_user_origin = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_user where account_id = '".$GLOBALS['supplier_info']['id']."' and user_id = '".$GLOBALS['user_info']['id']."'");
						//output($root,0,"手机号已被抢占");
						$result = do_login_user($user_info['user_name'],$user_info['user_pwd']);
							
						if($result['status'])
						{
							$s_user_info = es_session::get("user_info");
							$userdata['id'] = $s_user_info['id'];
							$userdata['user_name'] = $s_user_info['user_name'];
							$userdata['user_pwd'] = $s_user_info['user_pwd'];
							$userdata['email'] = $s_user_info['email'];
							$userdata['mobile'] = $s_user_info['mobile'];
							$userdata['is_tmp'] = $s_user_info['is_tmp'];
							
							if($supplier_user_origin)
							{
								$supplier_user = array();
								$supplier_user['user_id'] = $s_user_info['id'];
								$supplier_user['account_id'] = $GLOBALS['supplier_info']['id'];
								$supplier_user['openid'] = $supplier_user_origin['openid']; //商户openid
								$supplier_user['nickname'] = $s_user_info['user_name'];
								$GLOBALS['db']->autoExecute(DB_PREFIX."weixin_user",$supplier_user);
								$supplier_user['id'] = $GLOBALS['db']->insert_id();
							}
						}
						else
						{
							output($root,0,"登录失败");
						}
					}
					else
					{				
						$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$user_mobile."'");
						$GLOBALS['db']->query("update ".DB_PREFIX."user set mobile = '".$user_mobile."' where id = ".$GLOBALS['user_info']['id']);
			
						$result = do_login_user($user_mobile,$GLOBALS['user_info']['user_pwd']);
							
						if($result['status'])
						{
							$s_user_info = es_session::get("user_info");
							$userdata['id'] = $s_user_info['id'];
							$userdata['user_name'] = $s_user_info['user_name'];
							$userdata['user_pwd'] = $s_user_info['user_pwd'];
							$userdata['email'] = $s_user_info['email'];
							$userdata['mobile'] = $s_user_info['mobile'];
							$userdata['is_tmp'] = $s_user_info['is_tmp'];								
						}
						else
						{
							output($root,0,"登录失败");
						}				
					}
				}
				else
				{
					output($root,0,"验证码错误");
				}
				
				//end 绑定手机号
			}
			else 
			{
				$s_user_info = es_session::get("user_info");
				$userdata['id'] = $s_user_info['id'];
				$userdata['user_name'] = $s_user_info['user_name'];
				$userdata['user_pwd'] = $s_user_info['user_pwd'];
				$userdata['email'] = $s_user_info['email'];
				$userdata['mobile'] = $s_user_info['mobile'];
				$userdata['is_tmp'] = $s_user_info['is_tmp'];
			}
		}
		
		$cart_result = load_cart_list();
		$cart_list = $cart_result['cart_list'];
		$total_score = 0;
		$total_money = 0;
		foreach ($num as $k=>$v)
		{
			$id = intval($k);
			$number = $v;
			$total_score+=$cart_list[$id]['return_score']*$number;
			$total_money+=$cart_list[$id]['return_money']*$number;
		}
		
		//验证积分
		// 		$total_score = $cart_result['total_data']['return_total_score'];
		if($GLOBALS['user_info']['score']+$total_score<0)
		{
			output($root,0,"积分不足");
		}
		//验证积分
		
		
		//关于现金的验证
		// 		$total_money = $cart_result['total_data']['return_total_money'];
		if($GLOBALS['user_info']['money']+$total_money<0)
		{
			output($root,0,"余额不足");
		}
		//关于现金的验证
		
		foreach ($num as $k=>$v)
		{
			$id = intval($k);
			$number = intval($v);
			$data = check_cart($id, $number);
			if(!$data['status'])
			{
				output($root,0,$data['info']);
			}
		}
		
		foreach ($num as $k=>$v)
		{
			$id = intval($k);
			$number = intval($v);
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set number =".$number.", total_price = ".$number."* unit_price, return_total_score = ".$number."* return_score, return_total_money = ".$number."* return_money where id =".$id." and session_id = '".es_session::id()."'");
			load_cart_list(true);
		}
		$root['user_data'] = $userdata;
		output($root);
	}
	
	
	/**
	 * 删除购物车
	 * 
	 * 输入
	 * id:int 购物车中的商品ID，该参数不传时表示为清空所有购物车内容 
	 * 
	 * 输出
	 * 无
	 */
	public function del()
	{
		$root = array();
		if(isset($GLOBALS['request']['id']))
		{
			$id = intval($GLOBALS['request']['id']);
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
		}
		output($root);
	}
	
	
	/**
	 * 购物车的提交页
	 * 输入:
	 * 无
	 * 
	 * 输出:
	 * status: int 状态 1:正常 -1未登录需要登录
	 * info:string 信息
	 * cart_list: object 购物车列表，如该列表为空数组则跳回首页,结构如下
	 * Array
        (
            [478] => Array key [int] 购物车表中的主键
                (
                    [id] => 478 [int] 同key
                    [return_score] => 0 [int] 当is_score为1时单价的展示
                    [return_total_score] => 0 [int] 当is_score为1时总价的展示
                    [unit_price] => 108 [float] 当is_score为0时单价的展示
                    [total_price] => 108 [float] 当is_score为0时总价的展示
                    [number] => 1 [int] 购买件数
                    [deal_id] => 57 [int] 商品ID
                    [attr] => 287,290 [string] 购买商品的规格ID组合，用逗号分隔的规格ID
                    [name] => 桥亭活鱼小镇 仅售88元！价值100元的代金券1张 [9点至18点,2-5人套餐] [string] 商品全名，包含属性
                    [sub_name] => 88元桥亭活鱼小镇代金券 [9点至18点,2-5人套餐] [string] 商品缩略名，包含属性
                    [max] => int 最大购买量 加减时用
                    [icon] => string 商品图标 140x85
                )
		)
		
	 * total_data: array 购物车总价统计,结构如下
	 *	Array
        (
            [total_price] => 108 [float] 当is_score为0时的总价显示
            [return_total_score] => 0 [int] 当is_score为1时的总价显示
        )
	 * is_score: int 当前购物车中的商品类型 0:普通商品，展示时显示价格 1:积分商品，展示时显示积分
	 * is_delivery: int 是否需要配送 0无需 1需要
     * consignee_count: int 预设的配送地址数量 0：提示去设置收货地址 1以及以上显示选择其他收货方式
     * consignee_info: object 当前配送地址信息，结构如下
     * Array
        (
            [id] => 19 int 配送方式的主键
            [user_id] => 71 int 当前会员ID
            [region_lv1] => 1 int 国ID
            [region_lv2] => 4 int 省ID
            [region_lv3] => 53 int 市ID
            [region_lv4] => 519 int 区ID
            [address] => 群升国际 string 详细地址
            [mobile] => 13555566666 string 手机号
            [zip] => 350001 string 邮编
            [consignee] => 李四 string 收货人姓名
            [is_default] => 1
            [region_lv1_name] => 中国 string 国名
            [region_lv2_name] => 福建 string 省名
            [region_lv3_name] => 福州 string 市名
            [region_lv4_name] => 台江区 string 区名
        )

	 * delivery_list: array 配送方式列表，结构如下
	 * Array
        (
            [0] => Array
                (
                    [id] => 8 [int] 主键
                    [name] => 顺风快递 [string] 名称
                    [description] => 顺风快递,福州地区2元 [string] 描述
                )

        )
	 * payment_list: array 支付方式列表，结构如下
     * Array
        (
            [0] => Array
                (
                    [id] => 20 [int] 支付方式主键
                    [code] => Walipay [string] 类名
                    [logo] => http://192.168.1.41/o2onew/public/attachment/sjmapi/4f2ce3d1827e4.jpg [string] 图标 40x40
                    [name] => 支付宝支付 [string] 显示的名称
                )
        )
     * is_coupon: int 是否为发券订单，0否 1:是
     * show_payment: int 是否要显示支付方式 0:否（0元抽奖类） 1:是
     * has_account: int 是否显示余额支付 0否  1是
     * has_ecv: int 是否显示代金券支付 0否  1是
     * voucher_list:array 可用的代金券列表
     * array(
     * array(
     * 	"sn"=>"xxxxx" string 代金券序列号,
     *  "name" => "红包名称" string
     * )
     * )
     * account_money:float 余额
     * 
	 */
	public function check()
	{		
		$root = array();
		if((check_login()==LOGIN_STATUS_TEMP&&$GLOBALS['user_info']['money']>0)||check_login()==LOGIN_STATUS_NOLOGIN)
		{
			output(array(),-1,"请先登录");
		}
		require_once APP_ROOT_PATH."system/model/cart.php";
		$cart_result = load_cart_list();
		$total_price = $cart_result['total_data']['total_price'];
		//处理购物车输出
		$cart_list_o = $cart_result['cart_list'];
		$cart_list = array();
		
		$total_data_o = $cart_result['total_data'];
		$is_score = 0;
		foreach($cart_list_o as $k=>$v)
		{
			$bind_data = array();
			$bind_data['id'] = $v['id'];
			if($v['buy_type']==1)
			{
				$is_score = 1;
				$bind_data['return_score'] = abs($v['return_score']);
				$bind_data['return_total_score'] = abs($v['return_total_score']);
				$bind_data['unit_price'] = 0;
				$bind_data['total_price'] = 0;
			}
			else
			{
				$bind_data['return_score'] = 0;
				$bind_data['return_total_score'] = 0;
				$bind_data['unit_price'] = round($v['unit_price'],2);
				$bind_data['total_price'] = round($v['total_price'],2);
			}
			$bind_data['number'] = $v['number'];
			$bind_data['deal_id'] = $v['deal_id'];
			$bind_data['attr'] = $v['attr'];
			$bind_data['name'] = $v['name'];
			$bind_data['sub_name'] = $v['sub_name'];
			$bind_data['max'] = 100;
			$bind_data['supplier_id'] = $v['supplier_id'];
			$bind_data['icon'] = get_abs_img_root(get_spec_image($v['icon'],140,85,1)) ;
			$cart_list[$v['id']] = $bind_data;
		}
		$root['cart_list'] = $cart_list?$cart_list:null;
		$cart_list_group = cart_list_group($cart_list);
		foreach($cart_list_group as $k=>$v)
		{
			$cart_list_group[$k]['supplier'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = '".$v['supplier_id']."'");
			$cart_list_group[$k]['supplier'] = $cart_list_group[$k]['supplier']?$cart_list_group[$k]['supplier']:app_conf("SHOP_TITLE")."直营";
		}
		$root['cart_list_group'] = $cart_list_group;
		
		$total_data = array();
		
		if($is_score)
		{
			$total_data['total_price'] = 0;
			$total_data['return_total_score'] = abs($total_data_o['return_total_score']);
		}
		else
		{
			$total_data['total_price'] = round($total_data_o['total_price'],2);
			$total_data['return_total_score'] = 0;
		}
		$root['total_data'] = $total_data;
		$root['is_score'] = $is_score;
		//end购物车输出
		
	
		$is_delivery = 0;
		foreach($cart_list_o as $k=>$v)
		{	
			if($v['is_delivery']==1)
			{
				$is_delivery = 1;
				break;
			}
		}
		$root['is_delivery'] = $is_delivery;
			
		if($is_delivery)
		{
			//输出配送方式
			$consignee_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign("consignee_count",intval($consignee_count));
			$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']." and is_default = 1");
			$GLOBALS['tmpl']->assign("consignee_id",intval($consignee_id));
		}
		$root['consignee_count'] = intval($consignee_count);
		$consignee_info = load_auto_cache("consignee_info",array("consignee_id"=>$consignee_id));
		$consignee_info = $consignee_info['consignee_info']?$consignee_info['consignee_info']:null;
		$root['consignee_info'] = $consignee_info;
		
		if($consignee_info)
			$region_id = intval($consignee_info['region_lv4']);

		$delivery_list = load_support_delivery($region_id,0);
		sort($delivery_list);
		$root['delivery_list'] = $delivery_list?$delivery_list:array();
		//配送方式由ajax由 consignee 中的地区动态获取
			
		//输出支付方式
		if ($GLOBALS['request']['from'] == 'wap')
		{
			//支付列表
			$sql = "select id, class_name as code, logo from ".DB_PREFIX."payment where (online_pay = 2 or online_pay = 4 or online_pay = 5) and is_effect = 1";
		}
		else
		{
			//支付列表
			$sql = "select id, class_name as code, logo from ".DB_PREFIX."payment where (online_pay = 3 or online_pay = 4 or online_pay = 5) and is_effect = 1";
		}
		if(allow_show_api())
		{
			$payment_list = $GLOBALS['db']->getAll($sql);
		}
		foreach($cart_list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
			{
				$define_payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_payment where deal_id = ".$v['deal_id']);
				$define_payment = array();
				foreach($define_payment_list as $kk=>$vv)
				{
					array_push($define_payment,$vv['payment_id']);
				}
				foreach($payment_list as $k=>$v)
				{
					if(in_array($v['id'],$define_payment))
					{
						unset($payment_list[$k]);
					}
				}
			}
		}
		
		
		foreach($payment_list as $k=>$v)
		{
			$directory = APP_ROOT_PATH."system/payment/";
			$file = $directory. '/' .$v['code']."_payment.php";
			if(file_exists($file))
			{
				require_once($file);
				$payment_class = $v['code']."_payment";
				$payment_object = new $payment_class();
				$payment_list[$k]['name'] = $payment_object->get_display_code();
			}
			
			if($v['logo']!="")
			$payment_list[$k]['logo'] = get_abs_img_root(get_spec_image($v['logo'],40,40,1));
		}
		
		sort($payment_list);
		$root['payment_list'] = $payment_list;
		
		$is_coupon = 0;
		foreach($cart_list_o as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select is_coupon from ".DB_PREFIX."deal where id = ".$v['deal_id']." and forbid_sms = 0")==1)
			{
				$is_coupon = 1;
				break;
			}
		}
		$root['is_coupon'] = $is_coupon;
		
			
		//查询总金额
		$delivery_count = 0;
		foreach($cart_list_o as $k=>$v)
		{
			if($v['is_delivery']==1)
			{
				$delivery_count++;
			}
		}
		if($total_price > 0 || $delivery_count > 0)
		    $show_payment = 1;
		else
		 	$show_payment = 0;
		$root['show_payment'] = $show_payment;
		
		if($show_payment)
		{
			$web_payment_list = load_auto_cache("cache_payment");
			foreach($cart_list as $k=>$v)
			{
				if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
				{
					$define_payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_payment where deal_id = ".$v['deal_id']);
					$define_payment = array();
					foreach($define_payment_list as $kk=>$vv)
					{
						array_push($define_payment,$vv['payment_id']);
					}
					foreach($web_payment_list as $k=>$v)
					{
						if(in_array($v['id'],$define_payment))
						{
							unset($web_payment_list[$k]);
						}
					}
				}
			}
			
			foreach($web_payment_list as $k=>$v)
			{
				if($v['class_name']=="Account"&&$GLOBALS['user_info']['money']>0)
				{
					$root['has_account'] = 1;					
				}
				if($v['class_name']=="Voucher")
				{
					$root['has_ecv'] = 1;
					$sql = "select e.sn as sn,t.name as name from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as t on e.ecv_type_id = t.id where ".
							" e.user_id = '".$GLOBALS['user_info']['id']."' and (e.begin_time < ".NOW_TIME.") and (e.end_time = 0 or e.end_time > ".NOW_TIME.") ".
							" and (e.use_limit = 0 or e.use_count<e.use_limit)";
					$root['voucher_list'] = $GLOBALS['db']->getAll($sql);
				}
			}			
			
		}
		else
		{
			$root['has_account'] = 0;
			$root['has_ecv'] = 0;
		}
		
		$root['page_title'] = "提交订单";
		$root['account_money'] = round($GLOBALS['user_info']['money'],2);
		output($root);
	}
	
	
	/**
	 * 计算购物车总价
	 * 
	 * 输入:
	 * delivery_id: int 配送方式主键
	 * ecvsn:string 代金券序列号
	 * ecvpassword: string 代金券密码
	 * payment:int 支付方式ID
	 * all_account_money:int 是否使用余额支付 0否 1是
	 * 
	 * 输出:
	 * pay_price:float 当前要付的余额，如为0表示不需要使用在线支付，则支付方式不让选中
	 * delivery_fee_supplier:商家的运费费用Array
	 * array(
	 * array(supplier_id=>delivery_fee)
	 * )
	 * feeinfo: array 费用清单，结构如下
	 * Array(
	 * 	    Array(
					"name" => "折扣", string 费用清单项名称
					"value" => "7折" string 费用清单项内容
			),
	 * )
	 * 
	 */
	public function count_buy_total()
	{
		require_once APP_ROOT_PATH."system/model/cart.php";
		$delivery_id =  intval($GLOBALS['request']['delivery_id']); //配送方式
		$region_id = 0; //配送地区
		if($delivery_id)
		{
			$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']." and is_default = 1");
			$consignee_info = load_auto_cache("consignee_info",array("consignee_id"=>$consignee_id));
			$consignee_info = $consignee_info['consignee_info']?$consignee_info['consignee_info']:array();			
			if($consignee_info)
				$region_id = intval($consignee_info['region_lv4']);
		}
		
		$ecvsn = $GLOBALS['request']['ecvsn']?strim($GLOBALS['request']['ecvsn']):'';
		$ecvpassword = $GLOBALS['request']['ecvpassword']?strim($GLOBALS['request']['ecvpassword']):'';
		$payment = intval($GLOBALS['request']['payment']);
		$all_account_money = intval($GLOBALS['request']['all_account_money']);
		$bank_id = '';
	
		$cart_result = load_cart_list();
		$goods_list = $cart_result['cart_list'];
		
		
		$result = count_buy_total($region_id,$delivery_id,$payment,0,$all_account_money,$ecvsn,$ecvpassword,$goods_list,0,0,$bank_id);
		$root = array();
		
		
		if($result['total_price']>0)
		{
			$feeinfo[] = array(
					"name" => "商品总价",
					"value" => round($result['total_price'],2)."元"
			);
		}
		
		//"value" => round($result['user_discount']*10,1)."折" 
		//$result['user_discount']这个算出来是折扣的钱,也就是减了多少钱
		if($result['user_discount']>0)
		{
			$feeinfo[] = array(
					"name" => "折扣",
					"value" => round(($result['total_price']-$result['user_discount'])/$result['total_price']*10,1)."折"
			);
		}
		
		if($result['delivery_info'])
		{
			$feeinfo[] = array(
					"name" => "配送方式",
					"value" => $result['delivery_info']['name']
			);
		}
		
		if($result['delivery_fee']>0)
		{
			$feeinfo[] = array(
				"name" => "运费",	
				"value" => round($result['delivery_fee'],2)."元"
			);
		}
		
		
		
		if($result['payment_info'])
		{
			$directory = APP_ROOT_PATH."system/payment/";
			$file = $directory. '/' .$result['payment_info']['class_name']."_payment.php";
			if(file_exists($file))
			{
				require_once($file);
				$payment_class = $result['payment_info']['class_name']."_payment";
				$payment_object = new $payment_class();
				$payment_name = $payment_object->get_display_code();
			}
			
			$feeinfo[] = array(
					"name" => "支付方式",
					"value" => $payment_name
			);
		}
		
		if($result['payment_fee']>0)
		{
			$feeinfo[] = array(
					"name" => "手续费",
					"value" => round($result['payment_fee'],2)."元"
			);
		}
		
		if($result['account_money']>0)
		{
			$feeinfo[] = array(
					"name" => "余额支付",
					"value" => round($result['account_money'],2)
			);
		}
		
		if($result['ecv_money']>0)
		{
			$feeinfo[] = array(
					"name" => "红包支付",
					"value" => round($result['ecv_money'],2)
			);
		}
		
		if($result['buy_type']==0)
		{
			if($result['return_total_score'])
			{
				$feeinfo[] = array(
						"name" => "返还积分",
						"value" => round($result['return_total_score'])
				);
			}
		}
		
		if($result['return_total_money'])
		{
			$feeinfo[] = array(
					"name" => "返现",
					"value" => round($result['return_total_money'],2)."元"
			);
		}
		
		if($result['paid_account_money']>0)
		{
			$feeinfo[] = array(
					"name" => "已付",
					"value" => round($result['paid_account_money'],2)."元"
			);
		}
		
		if($result['paid_ecv_money']>0)
		{
			$feeinfo[] = array(
					"name" => "红包已付",
					"value" => round($result['paid_ecv_money'],2)."元"
			);
		}
		
		
		
		if($result['buy_type']==0)
		{
			$feeinfo[] = array(
					"name" => "总计",
					"value" => round($result['pay_total_price'],2)."元"
			);
		}
		else
		{
			$feeinfo[] = array(
					"name" => "所需积分",
					"value" => abs(round($result['return_total_score']))
			);
		}
		
		if($result['pay_price'])
		{
			$feeinfo[] = array(
					"name" => "应付总额",
					"value" => round($result['pay_price'],2)."元"
			);
		}
		
		if($result['promote_description'])
		{
			foreach($result['promote_description'] as $row)
			{
				$feeinfo[] = array(
						"name" => "",
						"value" => $row
				);
			}
		}
		$root['feeinfo'] = $feeinfo;
		$root['delivery_fee_supplier'] = $result['delivery_fee_supplier'];
		$root['delivery_info'] = $result['delivery_info'];
		$root['pay_price'] = round($result['pay_price'],2);
		
		
	
		output($root);
	}
	
	
	/**
	 * 购物车提交订单接口
	 * 输入：
	 * delivery_id: int 配送方式主键
	 * ecvsn:string 代金券序列号
	 * ecvpassword: string 代金券密码
	 * payment:int 支付方式ID
	 * all_account_money:int 是否使用余额支付 0否 1是
	 * content:string 订单备注
	 * 
	 * 输出：
	 * status: int 状态 0:失败 1:成功 -1:未登录
	 * info: string 失败时返回的错误信息，用于提示
	 * 以下参数为status为1时返回
	 * pay_status:int 0未支付成功 1全部支付
	 * order_id:int 订单ID
	 * 
	 */
	public function done()
	{
		require_once APP_ROOT_PATH."system/model/cart.php";
		require_once APP_ROOT_PATH."system/model/deal.php";
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		$delivery_id =  intval($GLOBALS['request']['delivery_id']); //配送方式
		$region_id = 0; //配送地区
		if($delivery_id)
		{
			$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']." and is_default = 1");
			$consignee_info = load_auto_cache("consignee_info",array("consignee_id"=>$consignee_id));
			$consignee_info = $consignee_info['consignee_info']?$consignee_info['consignee_info']:array();
			if($consignee_info)
				$region_id = intval($consignee_info['region_lv4']);
		}
		
		
		$payment = intval($GLOBALS['request']['payment']);
		$all_account_money = intval($GLOBALS['request']['all_account_money']);
		$ecvsn = $GLOBALS['request']['ecvsn']?strim($GLOBALS['request']['ecvsn']):'';
		$ecvpassword = $GLOBALS['request']['ecvpassword']?strim($GLOBALS['request']['ecvpassword']):'';
		$memo = strim($GLOBALS['request']['content']);
		
		$cart_result = load_cart_list();
		$goods_list = $cart_result['cart_list'];
	
		if(!$goods_list)
		{			
			output(array(),0,"购物车为空");
		}
	
		//验证购物车
		if((check_login()==LOGIN_STATUS_TEMP&&$GLOBALS['user_info']['money']>0)||check_login()==LOGIN_STATUS_NOLOGIN)
		{
			output(array(),-1,"请先登录");
		}
		$deal_ids = array();
		foreach($goods_list as $k=>$v)
		{
			$data = check_cart($v['id'], $v['number']);
			if(!$data['status'])
				output(array(),0,$data['info']);
			$deal_ids[$v['deal_id']]['deal_id'] = $v['deal_id'];
		}
		foreach($deal_ids as $row)
		{
			//验证支付方式的支持
			if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$row['deal_id'])==1)
			{
				if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_payment where deal_id = ".$row['deal_id']." and payment_id = ".$payment))
				{
					output(array(),0,"支付方式不支持");
				}
			}
		}
			
			
		//结束验证购物车
		//开始验证订单接交信息
		$data = count_buy_total($region_id,$delivery_id,$payment,0,$all_account_money,$ecvsn,$ecvpassword,$goods_list);
		
	
		if($data['is_delivery'] == 1)
		{
			//配送验证
			if(!$data['delivery_info'])
			{
				output(array(),0,"请选择配送方式");
			}
				
			foreach($data['delivery_fee_supplier'] as $k=>$v)
			{
				if($v<0)
				{
					output(array(),0,"部份商品不支持".$data['delivery_info']['name']);
				}
			}
			
			if(!$consignee_info)
			{
				output(array(),0,"请设置收货方式");
			}
		}
	
		if(round($data['pay_price'],4)>0&&!$data['payment_info'])
		{
			output(array(),0,"请选择支付方式");
		}
		//结束验证订单接交信息
	
		$user_id = $GLOBALS['user_info']['id'];
		//开始生成订单
		$now = NOW_TIME;
		$order['type'] = 0; //普通订单
		$order['user_id'] = $user_id;
		$order['create_time'] = $now;
		$order['total_price'] = $data['pay_total_price'];  //应付总额  商品价 - 会员折扣 + 运费 + 支付手续费
		$order['pay_amount'] = 0;
		$order['pay_status'] = 0;  //新单都为零， 等下面的流程同步订单状态
		$order['delivery_status'] = $data['is_delivery']==0?5:0;
		$order['order_status'] = 0;  //新单都为零， 等下面的流程同步订单状态
		$order['return_total_score'] = $data['return_total_score'];  //结单后送的积分
		$order['return_total_money'] = $data['return_total_money'];  //结单后送的现金
		$order['memo'] = $memo;
		$order['region_lv1'] = intval($consignee_info['region_lv1']);
		$order['region_lv2'] = intval($consignee_info['region_lv2']);
		$order['region_lv3'] = intval($consignee_info['region_lv3']);
		$order['region_lv4'] = intval($consignee_info['region_lv4']);
		$order['address']	=	strim($consignee_info['address']);
		$order['mobile']	=	strim($consignee_info['mobile']);
		$order['consignee']	=	strim($consignee_info['consignee']);
		$order['zip']	=	strim($consignee_info['zip']);
		$order['deal_total_price'] = $data['total_price'];   //团购商品总价
		$order['discount_price'] = $data['user_discount'];
		$order['delivery_fee'] = $data['delivery_fee'];
		$order['ecv_money'] = 0;
		$order['account_money'] = 0;
		$order['ecv_sn'] = '';
		$order['delivery_id'] = $data['delivery_info']['id'];
		$order['payment_id'] = $data['payment_info']['id'];
		$order['payment_fee'] = $data['payment_fee'];
		$order['payment_fee'] = $data['payment_fee'];
		$order['bank_id'] = "";
	
		foreach($data['promote_description'] as $promote_item)
		{
			$order['promote_description'].=$promote_item."<br />";
		}
		//更新来路
		$order['referer'] =	$GLOBALS['referer'];
		$user_info = es_session::get("user_info");
		$order['user_name'] = $user_info['user_name'];
	
			
		do
		{
			$order['order_sn'] = to_date(NOW_TIME,"Ymdhis").rand(10,99);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'INSERT','','SILENT');
			$order_id = intval($GLOBALS['db']->insert_id());
		}while($order_id==0);
	
		//生成商户的运费记录
		$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order_supplier_fee where order_id = ".$order_id);
		foreach($data['delivery_fee_supplier'] as $key=>$fee)
		{
			$sp_id = str_replace("sid_","",$key);
			$fee_data = array();
			$fee_data['order_id'] = $order_id;
			$fee_data['supplier_id'] = $sp_id;
			$fee_data['delivery_fee'] = $fee;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_supplier_fee",$fee_data);
				
		}
		//生成订单商品
		foreach($goods_list as $k=>$v)
		{
			$deal_info = load_auto_cache("deal",array("id"=>$v['deal_id']));
			$goods_item = array();
			
			//关于fx
			if($deal_info['is_fx'])
			{
				$fx_user_id = intval($GLOBALS['ref_uid']);
				if($fx_user_id)
				{
					$user_deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_deal where deal_id = '".$deal_info['id']."' and user_id = '".$fx_user_id."'");
					if($user_deal||$deal_info['is_fx'])
						$goods_item['fx_user_id'] =  $fx_user_id;
				}
			}
			//关于fx
			
			$goods_item['deal_id'] = $v['deal_id'];
			$goods_item['number'] = $v['number'];
			$goods_item['unit_price'] = $v['unit_price'];
			$goods_item['total_price'] = $v['total_price'];
			$goods_item['name'] = $v['name'];
			$goods_item['sub_name'] = $v['sub_name'];
			$goods_item['attr'] = $v['attr'];
			$goods_item['verify_code'] = $v['verify_code'];
			$goods_item['order_id'] = $order_id;
			$goods_item['return_score'] = $v['return_score'];
			$goods_item['return_total_score'] = $v['return_total_score'];
			$goods_item['return_money'] = $v['return_money'];
			$goods_item['return_total_money'] = $v['return_total_money'];
			$goods_item['buy_type']	=	$v['buy_type'];
			$goods_item['attr_str']	=	$v['attr_str'];
			$goods_item['add_balance_price'] = $v['add_balance_price'];
			$goods_item['add_balance_price_total'] = $v['add_balance_price'] * $v['number'];
			$goods_item['balance_unit_price'] = $deal_info['balance_price'];
			$goods_item['balance_total_price'] = $deal_info['balance_price'] * $v['number'];
			$goods_item['delivery_status'] = $deal_info['is_delivery']==1?0:5;
			$goods_item['is_coupon'] = $deal_info['is_coupon'];
			$goods_item['deal_icon'] = $deal_info['icon'];
			$goods_item['supplier_id'] = $deal_info['supplier_id'];
			$goods_item['is_refund'] = $deal_info['is_refund'];
			$goods_item['user_id'] = $user_id;
			$goods_item['order_sn'] = $order['order_sn'];
			$goods_item['is_shop'] = $deal_info['is_shop'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_item",$goods_item,'INSERT','','SILENT');
		}
	
		//开始更新订单表的deal_ids
		$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_order set deal_ids = '".$deal_ids."' where id = ".$order_id);
	
		$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_cart where session_id = '".es_session::id()."'");
		load_cart_list(true);
	
	
		//生成order_id 后
		//1. 代金券支付
		$ecv_data = $data['ecv_data'];
		if($ecv_data)
		{
			$ecv_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Voucher'");
			if($ecv_data['money']>$order['total_price'])$ecv_data['money'] = $order['total_price'];
			$payment_notice_id = make_payment_notice($ecv_data['money'],$order_id,$ecv_payment_id,"",$ecv_data['id']);
			require_once APP_ROOT_PATH."system/payment/Voucher_payment.php";
			$voucher_payment = new Voucher_payment();
			$voucher_payment->direct_pay($ecv_data['sn'],$ecv_data['password'],$payment_notice_id);
		}
	
		//2. 余额支付
		$account_money = $data['account_money'];
		if(floatval($account_money) > 0)
		{
			$account_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Account'");
			$payment_notice_id = make_payment_notice($account_money,$order_id,$account_payment_id);
			require_once APP_ROOT_PATH."system/payment/Account_payment.php";
			$account_payment = new Account_payment();
			$account_payment->get_payment_code($payment_notice_id);
		}
	
// 		//3. 相应的支付接口
// 		$payment_info = $data['payment_info'];
// 		if($payment_info&&$data['pay_price']>0)
// 		{
// 			$payment_notice_id = make_payment_notice($data['pay_price'],$order_id,$payment_info['id']);
// 			//创建支付接口的付款单
// 		}
	
		$rs = order_paid($order_id);
		update_order_cache($order_id);
		if($rs)
		{
			$root['pay_status'] = 1;
			$root['order_id'] = $order_id;
		}
		else
		{
			distribute_order($order_id);
			$root['pay_status'] = 0;
			$root['order_id'] = $order_id;
		}
		output($root);
	}
	
	/**
	 * 订单的继续支付提交页
	 * 输入:
	 * id:int 订单ID
	 * 
	 * 输出:
	 * status: int 状态 1:正常 -1未登录需要登录 0失败
	 * info:string 信息
	 * cart_list: object 订单商品
	 * Array
        (
            [478] => Array key [int] 购物车表中的主键
                (
                    [id] => 478 [int] 同key
                    [return_score] => 0 [int] 当is_score为1时单价的展示
                    [return_total_score] => 0 [int] 当is_score为1时总价的展示
                    [unit_price] => 108 [float] 当is_score为0时单价的展示
                    [total_price] => 108 [float] 当is_score为0时总价的展示
                    [number] => 1 [int] 购买件数
                    [deal_id] => 57 [int] 商品ID
                    [attr] => 287,290 [string] 购买商品的规格ID组合，用逗号分隔的规格ID
                    [name] => 桥亭活鱼小镇 仅售88元！价值100元的代金券1张 [9点至18点,2-5人套餐] [string] 商品全名，包含属性
                    [sub_name] => 88元桥亭活鱼小镇代金券 [9点至18点,2-5人套餐] [string] 商品缩略名，包含属性
                    [max] => int 最大购买量 加减时用
                    [icon] => string 商品图标 140x85
                )
		)
		
	 * total_data: array 购物车总价统计,结构如下
	 *	Array
        (
            [total_price] => 108 [float] 当is_score为0时的总价显示
            [return_total_score] => 0 [int] 当is_score为1时的总价显示
        )
	 * is_score: int 当前购物车中的商品类型 0:普通商品，展示时显示价格 1:积分商品，展示时显示积分
	 * is_delivery: int 是否需要配送 0无需 1需要
     * consignee_count: int 预设的配送地址数量 0：提示去设置收货地址 1以及以上显示选择其他收货方式
     * consignee_info: object 当前配送地址信息，结构如下
     * Array
        (
            [id] => 19 int 配送方式的主键
            [user_id] => 71 int 当前会员ID
            [region_lv1] => 1 int 国ID
            [region_lv2] => 4 int 省ID
            [region_lv3] => 53 int 市ID
            [region_lv4] => 519 int 区ID
            [address] => 群升国际 string 详细地址
            [mobile] => 13555566666 string 手机号
            [zip] => 350001 string 邮编
            [consignee] => 李四 string 收货人姓名
            [is_default] => 1
            [region_lv1_name] => 中国 string 国名
            [region_lv2_name] => 福建 string 省名
            [region_lv3_name] => 福州 string 市名
            [region_lv4_name] => 台江区 string 区名
        )

	 * delivery_list: array 配送方式列表，结构如下
	 * Array
        (
            [0] => Array
                (
                    [id] => 8 [int] 主键
                    [name] => 顺风快递 [string] 名称
                    [description] => 顺风快递,福州地区2元 [string] 描述
                )

        )
	 * payment_list: array 支付方式列表，结构如下
     * Array
        (
            [0] => Array
                (
                    [id] => 20 [int] 支付方式主键
                    [code] => Walipay [string] 类名
                    [logo] => http://192.168.1.41/o2onew/public/attachment/sjmapi/4f2ce3d1827e4.jpg [string] 图标 40x40
                    [name] => 支付宝支付 [string] 显示的名称
                )
        )
     * is_coupon: int 是否为发券订单，0否 1:是
     * show_payment: int 是否要显示支付方式 0:否（0元抽奖类） 1:是
     * has_account: int 是否显示余额支付 0否  1是
     * has_ecv: int 是否显示代金券支付 0否  1是
     * order_id:int 订单ID
     * order_memo:string 订单备注
     * account_money:float 余额
	 */
	public function order()
	{
		$root = array();
		if((check_login()==LOGIN_STATUS_TEMP&&$GLOBALS['user_info']['money']>0)||check_login()==LOGIN_STATUS_NOLOGIN)
		{
			output(array(),-1,"请先登录");
		}
		$order_id = intval($GLOBALS['request']['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id." and is_delete = 0 and user_id = ".$GLOBALS['user_info']['id']);	
		if(empty($order_info))
		{
			output(array(),0,"订单不存在");
		}
		if($order_info['pay_status']==2)
		{
			output(array(),0,"订单已付款");
		}
		
		require_once APP_ROOT_PATH."system/model/cart.php";		
		$total_price = $order_info['total_price'];
		
		//处理购物车输出
		$cart_list_o = $GLOBALS['db']->getAll("select doi.*,d.id as did,d.icon,d.uname as duname from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal as d on doi.deal_id = d.id where doi.order_id = ".$order_info['id']);
	
		$cart_list = array();	
		$is_score = 0;
		foreach($cart_list_o as $k=>$v)
		{
			$bind_data = array();
			$bind_data['id'] = $v['id'];
			if($v['buy_type']==1)
			{
				$is_score = 1;
				$bind_data['return_score'] = abs($v['return_score']);
				$bind_data['return_total_score'] = abs($v['return_total_score']);
				$bind_data['unit_price'] = 0;
				$bind_data['total_price'] = 0;
			}
			else
			{
				$bind_data['return_score'] = 0;
				$bind_data['return_total_score'] = 0;
				$bind_data['unit_price'] = round($v['unit_price'],2);
				$bind_data['total_price'] = round($v['total_price'],2);
			}
			$bind_data['number'] = $v['number'];
			$bind_data['deal_id'] = $v['deal_id'];
			$bind_data['attr'] = $v['attr'];
			$bind_data['name'] = $v['name'];
			$bind_data['sub_name'] = $v['sub_name'];
			$bind_data['max'] = 100;
			$bind_data['supplier_id'] = $v['supplier_id'];
			$bind_data['icon'] = get_abs_img_root(get_spec_image($v['deal_icon'],140,85,1)) ;
			$cart_list[$v['id']] = $bind_data;
		}
		$root['cart_list'] = $cart_list?$cart_list:null;
		
		$cart_list_group = cart_list_group($cart_list);
		foreach($cart_list_group as $k=>$v)
		{
			$cart_list_group[$k]['supplier'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = '".$v['supplier_id']."'");
			$cart_list_group[$k]['supplier'] = $cart_list_group[$k]['supplier']?$cart_list_group[$k]['supplier']:app_conf("SHOP_TITLE")."直营";
		}
		$root['cart_list_group'] = $cart_list_group;
		
		$total_data = array();
	
		if($is_score)
		{
			$total_data['total_price'] = 0;
			$total_data['return_total_score'] = abs($order_info['return_total_score']);
		}
		else
		{
			$total_data['total_price'] = round($order_info['total_price'],2);
			$total_data['return_total_score'] = 0;
		}
		$root['total_data'] = $total_data;
		$root['is_score'] = $is_score;
		//end购物车输出
	
	
		$is_delivery = 0;
		foreach($cart_list_o as $k=>$v)
		{
			if($v['delivery_status']!=5)
			{
				$is_delivery = 1;
				break;
			}
		}
		$root['is_delivery'] = $is_delivery;
			
		if($is_delivery)
		{
			//输出配送方式
			$consignee_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']);
			$GLOBALS['tmpl']->assign("consignee_count",intval($consignee_count));
			$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']." and is_default = 1");
			$GLOBALS['tmpl']->assign("consignee_id",intval($consignee_id));
		}
		$root['consignee_count'] = $consignee_count;
		$consignee_info = load_auto_cache("consignee_info",array("consignee_id"=>$consignee_id));
		$consignee_info = $consignee_info['consignee_info']?$consignee_info['consignee_info']:null;
		$root['consignee_info'] = $consignee_info;
	
		if($consignee_info)
			$region_id = intval($consignee_info['region_lv4']);
	
		$delivery_list = load_support_delivery($region_id,$order_id);
		$root['delivery_list'] = $delivery_list?$delivery_list:array();
		//配送方式由ajax由 consignee 中的地区动态获取
			
		//输出支付方式
		if ($GLOBALS['request']['from'] == 'wap')
		{
			//支付列表
			$sql = "select id, class_name as code, logo from ".DB_PREFIX."payment where (online_pay = 2 or online_pay = 4 or online_pay = 5) and is_effect = 1";
		}
		else
		{
			//支付列表
			$sql = "select id, class_name as code, logo from ".DB_PREFIX."payment where (online_pay = 3 or online_pay = 4 or online_pay = 5) and is_effect = 1";
		}
	
		if(allow_show_api())
		{	
			$payment_list = $GLOBALS['db']->getAll($sql);
		}
		foreach($cart_list as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
			{
				$define_payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_payment where deal_id = ".$v['deal_id']);
				$define_payment = array();
				foreach($define_payment_list as $kk=>$vv)
				{
					array_push($define_payment,$vv['payment_id']);
				}
				foreach($payment_list as $k=>$v)
				{
					if(in_array($v['id'],$define_payment))
					{
						unset($payment_list[$k]);
					}
				}
			}
		}
		foreach($payment_list as $k=>$v)
		{
			$directory = APP_ROOT_PATH."system/payment/";
			$file = $directory. '/' .$v['code']."_payment.php";
			if(file_exists($file))
			{
				require_once($file);
				$payment_class = $v['code']."_payment";
				$payment_object = new $payment_class();
				$payment_list[$k]['name'] = $payment_object->get_display_code();
			}
				
			if($v['logo']!="")
				$payment_list[$k]['logo'] = get_abs_img_root(get_spec_image($v['logo'],40,40,1));
		}
		
		sort($payment_list);
		$root['payment_list'] = $payment_list;
	
		$is_coupon = 0;
		foreach($cart_list_o as $k=>$v)
		{
			if($GLOBALS['db']->getOne("select is_coupon from ".DB_PREFIX."deal where id = ".$v['deal_id']." and forbid_sms = 0")==1)
			{
				$is_coupon = 1;
				break;
			}
		}
		$root['is_coupon'] = $is_coupon;
	
			
		//查询总金额
		$delivery_count = 0;
		foreach($cart_list_o as $k=>$v)
		{
			if($v['is_delivery']==1)
			{
				$delivery_count++;
			}
		}
		if($total_price > 0 || $delivery_count > 0)
			$show_payment = 1;
		else
			$show_payment = 0;
		$root['show_payment'] = $show_payment;
	
		if($show_payment)
		{
			$web_payment_list = load_auto_cache("cache_payment");
				
			foreach($cart_list as $k=>$v)
			{
				if($GLOBALS['db']->getOne("select define_payment from ".DB_PREFIX."deal where id = ".$v['deal_id'])==1)
				{
					$define_payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_payment where deal_id = ".$v['deal_id']);
					$define_payment = array();
					foreach($define_payment_list as $kk=>$vv)
					{
						array_push($define_payment,$vv['payment_id']);
					}
					foreach($web_payment_list as $k=>$v)
					{
						if(in_array($v['id'],$define_payment))
						{
							unset($web_payment_list[$k]);
						}
					}
				}
			}
			foreach($web_payment_list as $k=>$v)
			{
				if($v['class_name']=="Account"&&$GLOBALS['user_info']['money']>0)
					$root['has_account'] = 1;	
			}
				
		}
		else
		{
			$root['has_account'] = 0;
			$root['has_ecv'] = 0;
		}
	
		$root['page_title'] = "提交订单";
		$root['order_id'] = $order_id;
		$root['order_memo'] = $order_info['memo'];
		$root['account_money'] = round($GLOBALS['user_info']['money'],2);
		output($root);
	}
	
	
	
	/**
	 * 计算订单总价
	 *
	 * 输入:
	 * id:int 订单ID
	 * delivery_id: int 配送方式主键
	 * payment:int 支付方式ID
	 * all_account_money:int 是否使用余额支付 0否 1是
	 *
	 * 输出:
	 * pay_price:float 当前要付的余额，如为0表示不需要使用在线支付，则支付方式不让选中
	 * feeinfo: array 费用清单，结构如下
	 * Array(
	 * 	    Array(
			 "name" => "折扣", string 费用清单项名称
			 "value" => "7折" string 费用清单项内容
			 ),
	 * )
	 *
	 */	
	public function count_order_total()
	{
		require_once APP_ROOT_PATH."system/model/cart.php";
		$order_id = intval($GLOBALS['request']['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id);
		
		
		$delivery_id =  intval($GLOBALS['request']['delivery_id']); //配送方式
		$region_id = 0; //配送地区
		if($delivery_id)
		{
			$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']." and is_default = 1");
			$consignee_info = load_auto_cache("consignee_info",array("consignee_id"=>$consignee_id));
			$consignee_info = $consignee_info['consignee_info']?$consignee_info['consignee_info']:array();
			if($consignee_info)
				$region_id = intval($consignee_info['region_lv4']);
		}
		
		$ecvsn = '';
		$ecvpassword = '';
		$payment = intval($GLOBALS['request']['payment']);
		$all_account_money = intval($GLOBALS['request']['all_account_money']);
		$bank_id = '';
		
		$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
		
		$result = count_buy_total($region_id,$delivery_id,$payment,0,$all_account_money,$ecvsn,$ecvpassword,$goods_list,$order_info['account_money'],$order_info['ecv_money'],$bank_id);
		
		$root = array();
		
		
		if($result['total_price']>0)
		{
			$feeinfo[] = array(
					"name" => "商品总价",
					"value" => round($result['total_price'],2)."元"
			);
		}
		
		
		if($result['user_discount']>0)
		{
			$feeinfo[] = array(
					"name" => "折扣",
					"value" => round(($result['total_price']-$result['user_discount'])/$result['total_price']*10,1)."折"
			);
		}
		
		if($result['delivery_info'])
		{
			$feeinfo[] = array(
					"name" => "配送方式",
					"value" => $result['delivery_info']['name']
			);
		}
		
		if($result['delivery_fee']>0)
		{
			$feeinfo[] = array(
					"name" => "运费",
					"value" => round($result['delivery_fee'],2)."元"
			);
		}
		
		
		
		if($result['payment_info'])
		{
			$directory = APP_ROOT_PATH."system/payment/";
			$file = $directory. '/' .$result['payment_info']['class_name']."_payment.php";
			if(file_exists($file))
			{
				require_once($file);
				$payment_class = $result['payment_info']['class_name']."_payment";
				$payment_object = new $payment_class();
				$payment_name = $payment_object->get_display_code();
			}
				
			$feeinfo[] = array(
					"name" => "支付方式",
					"value" => $payment_name
			);
		}
		
		if($result['payment_fee']>0)
		{
			$feeinfo[] = array(
					"name" => "手续费",
					"value" => round($result['payment_fee'],2)."元"
			);
		}
		
		if($result['account_money']>0)
		{
			$feeinfo[] = array(
					"name" => "余额支付",
					"value" => round($result['account_money'],2)
			);
		}
		
		if($result['ecv_money']>0)
		{
			$feeinfo[] = array(
					"name" => "红包支付",
					"value" => round($result['ecv_money'],2)
			);
		}
		
		if($result['buy_type']==0)
		{
			if($result['return_total_score'])
			{
				$feeinfo[] = array(
						"name" => "返还积分",
						"value" => round($result['return_total_score'])
				);
			}
		}
		
		if($result['return_total_money'])
		{
			$feeinfo[] = array(
					"name" => "返现",
					"value" => round($result['return_total_money'],2)."元"
			);
		}
		
		if($result['paid_account_money']>0)
		{
			$feeinfo[] = array(
					"name" => "已付",
					"value" => round($result['paid_account_money'],2)."元"
			);
		}
		
		if($result['paid_ecv_money']>0)
		{
			$feeinfo[] = array(
					"name" => "红包已付",
					"value" => round($result['paid_ecv_money'],2)."元"
			);
		}
		
		
		
		if($result['buy_type']==0)
		{
			$feeinfo[] = array(
					"name" => "总计",
					"value" => round($result['pay_total_price'],2)."元"
			);
		}
		else
		{
			$feeinfo[] = array(
					"name" => "所需积分",
					"value" => abs(round($result['return_total_score']))
			);
		}
		
		if($result['pay_price'])
		{
			$feeinfo[] = array(
					"name" => "应付总额",
					"value" => round($result['pay_price'],2)."元"
			);
		}
		
		if($result['promote_description'])
		{
			foreach($result['promote_description'] as $row)
			{
				$feeinfo[] = array(
						"name" => "",
						"value" => $row
				);
			}
		}
		$root['feeinfo'] = $feeinfo;
		$root['delivery_fee_supplier'] = $result['delivery_fee_supplier'];
		$root['delivery_info'] = $result['delivery_info'];
		$root['pay_price'] = round($result['pay_price'],2);
		
		
		
		output($root);
	}
	
	
	/**
	 * 订单继续支付接口
	 * 输入：
	 * order_id:int 订单ID
	 * delivery_id: int 配送方式主键
	 * payment:int 支付方式ID
	 * all_account_money:int 是否使用余额支付 0否 1是
	 * content:string 订单备注
	 * 
	 * 输出：
	 * status: int 状态 0:失败 1:成功 -1:未登录
	 * info: string 失败时返回的错误信息，用于提示
	 * 以下参数为status为1时返回
	 * pay_status:int 0未支付成功 1全部支付
	 * order_id:int 订单ID
	 * 
	 */
	public function order_done()
	{
		require_once APP_ROOT_PATH."system/model/cart.php";
		require_once APP_ROOT_PATH."system/model/deal.php";
		require_once APP_ROOT_PATH."system/model/deal_order.php";
		//验证购物车
		if((check_login()==LOGIN_STATUS_TEMP&&$GLOBALS['user_info']['money']>0)||check_login()==LOGIN_STATUS_NOLOGIN)
		{
			output(array(),-1,"请先登录");
		}
		
		$order_id = intval($GLOBALS['request']['order_id']);
		$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_order where id = ".$order_id." and is_delete = 0 and user_id = ".$GLOBALS['user_info']['id']);
		if(empty($order))
		{
			output(array(),0,"订单不存在");
		}
		if($order['refund_status'] == 1)
		{
			output(array(),0,"订单退款中");
		}
		if($order['refund_status'] == 2)
		{
			output(array(),0,"订单已退款");
		}		
		
		$delivery_id =  intval($GLOBALS['request']['delivery_id']); //配送方式
		$region_id = 0; //配送地区
		if($delivery_id)
		{
			$consignee_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_consignee where user_id = ".$GLOBALS['user_info']['id']." and is_default = 1");
			$consignee_info = load_auto_cache("consignee_info",array("consignee_id"=>$consignee_id));
			$consignee_info = $consignee_info['consignee_info']?$consignee_info['consignee_info']:array();
			if($consignee_info)
				$region_id = intval($consignee_info['region_lv4']);
		}
		
		
		$payment = intval($GLOBALS['request']['payment']);
		$all_account_money = intval($GLOBALS['request']['all_account_money']);
		$ecvsn = $GLOBALS['request']['ecvsn']?strim($GLOBALS['request']['ecvsn']):'';
		$ecvpassword = $GLOBALS['request']['ecvpassword']?strim($GLOBALS['request']['ecvpassword']):'';
		$memo = strim($GLOBALS['request']['content']);
		
		$goods_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order['id']);
		
		//结束验证购物车
		$deal_s = $GLOBALS['db']->getAll("select distinct(deal_id) as deal_id from ".DB_PREFIX."deal_order_item where order_id = ".$order['id']);
		
		//如果属于未支付的
		if($order['pay_status'] == 0)
		{
			foreach($deal_s as $row)
			{
				$checker = check_deal_number($row['deal_id'],0);
				if($checker['status']==0)
				{
					output(array(),0,$checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']]);		
				}
			}
				
			foreach($goods_list as $k=>$v)
			{
				$checker = check_deal_number_attr($v['deal_id'],$v['attr_str'],0);
				if($checker['status']==0)
				{
					output(array(),0,$checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']]);
				}
			}
				
			//验证商品是否过期
			foreach($deal_s as $row)
			{
				$checker = check_deal_time($row['deal_id']);
				if($checker['status']==0)
				{
					output(array(),0,$checker['info']." ".$GLOBALS['lang']['DEAL_ERROR_'.$checker['data']]);
				}
			}
		}
			
			
		//结束验证购物车
		//开始验证订单接交信息
		$data = count_buy_total($region_id,$delivery_id,$payment,0,$all_account_money,$ecvsn,$ecvpassword,$goods_list,$order['account_money'],$order['ecv_money']);
		
	
		if($data['is_delivery'] == 1)
		{
			foreach($data['delivery_fee_supplier'] as $k=>$v)
			{
				if($v<0)
				{
					output(array(),0,"部份商品不支持".$data['delivery_info']['name']);
				}
			}
			//配送验证
			if(!$data['delivery_info'])
			{
				output(array(),0,"请选择配送方式");
			}
			
			if(!$consignee_info)
			{
				output(array(),0,"请设置收货方式");
			}
		}
	
		if(round($data['pay_price'],4)>0&&!$data['payment_info'])
		{
			output(array(),0,"请选择支付方式");
		}
		//结束验证订单接交信息
	
		$user_id = $GLOBALS['user_info']['id'];
		//开始生成订单
		$now = NOW_TIME;
		$order['total_price'] = $data['pay_total_price'];  //应付总额  商品价 - 会员折扣 + 运费 + 支付手续费
		$order['memo'] = $memo;
		$order['region_lv1'] = intval($consignee_info['region_lv1']);
		$order['region_lv2'] = intval($consignee_info['region_lv2']);
		$order['region_lv3'] = intval($consignee_info['region_lv3']);
		$order['region_lv4'] = intval($consignee_info['region_lv4']);
		$order['address']	=	strim($consignee_info['address']);
		$order['mobile']	=	strim($consignee_info['mobile']);
		$order['consignee']	=	strim($consignee_info['consignee']);
		$order['zip']	=	strim($consignee_info['zip']);
		$order['deal_total_price'] = $data['total_price'];   //团购商品总价
		$order['discount_price'] = $data['user_discount'];
		$order['delivery_fee'] = $data['delivery_fee'];
		$order['delivery_id'] = $data['delivery_info']['id'];
		$order['payment_id'] = $data['payment_info']['id'];
		$order['payment_fee'] = $data['payment_fee'];
		$order['bank_id'] = "";
	
		foreach($data['promote_description'] as $promote_item)
		{
			$order['promote_description'].=$promote_item."<br />";
		}
		//更新来路
	
			
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'UPDATE','id='.$order['id'],'SILENT');
	
		//生成商户的运费记录
		$GLOBALS['db']->query("delete from ".DB_PREFIX."deal_order_supplier_fee where order_id = ".$order['id']);
		foreach($data['delivery_fee_supplier'] as $key=>$fee)
		{
			$sp_id = str_replace("sid_","",$key);
			$fee_data = array();
			$fee_data['order_id'] = $order['id'];
			$fee_data['supplier_id'] = $sp_id;
			$fee_data['delivery_fee'] = $fee;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order_supplier_fee",$fee_data);
		}
	
		//生成order_id 后		
	
		//2. 余额支付
		$account_money = $data['account_money'];
		if(floatval($account_money) > 0)
		{
			$account_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Account'");
			$payment_notice_id = make_payment_notice($account_money,$order_id,$account_payment_id);
			require_once APP_ROOT_PATH."system/payment/Account_payment.php";
			$account_payment = new Account_payment();
			$account_payment->get_payment_code($payment_notice_id);
		}
	
// 		//3. 相应的支付接口
// 		$payment_info = $data['payment_info'];
// 		if($payment_info&&$data['pay_price']>0)
// 		{
// 			$payment_notice_id = make_payment_notice($data['pay_price'],$order_id,$payment_info['id']);
// 			//创建支付接口的付款单
// 		}
	
		$rs = order_paid($order_id);
		update_order_cache($order_id);
		if($rs)
		{
			$root['pay_status'] = 1;
			$root['order_id'] = $order_id;
		}
		else
		{
			distribute_order($order_id);
			$root['pay_status'] = 0;
			$root['order_id'] = $order_id;
		}
		output($root);
	}
	
	
	/**
	 * 多商品合并购买
	 * ids:array 结构如下
		Array(
			[0] => 64	int 商品id
			[1] => 85
			[2] => 87
		)
		
	 * deal_attr: array 结构如下
		 Array(
			[64] => Array(	//商品属性(与购买单个商品的属性结构一样)
				[19] => 335
				[20] => 337
			)
		)
	*
	 * 输出：
	 * status: int 状态 0有错误 1加入成功 -1未登录需要登录 -2没有可以购买的产品
	 * 当status=0时 表示有部分商品加入出错 返回格式 
		Array(
			[商品id] => Array(	//这里的结构跟单条加入购物车的错误提示一样
				
					[status] => 0
					[info] => 请选择商品规格
				)
			[84] => Array(
					[status] => 0
					[info] => 没有可以购买的商品
				)
		)
	 *		
	*/
	public function addcartByRelate(){
		//商品id数组
		$ids 		= $GLOBALS['request']['ids'];
		//商品属性数组
		$deal_attr  = $GLOBALS['request']['deal_attr'];
		if( empty($ids)||!is_array($ids) ){
			output("",-2,"没有可以购买的产品");
		}
		
		$result = array();
		foreach($ids as $id){
			$id = intval($id);
			if( $id>0 ){
				$param = array(
					'id'	=>	$id,
				);
				if(!empty($deal_attr[$id])){
					$param['attr'] = $deal_attr[$id];
				}
				
				$tmpData = $this->addcart(false,$param);
				if( $tmpData['status']==-1 ){
					output('',-1,'请先登录');
				}else if( !empty($tmpData['info']) ){
					$result[$id] = $tmpData;
				}
			}	
		}
		if( empty($result) ){
			output($result);
		}else{
			output($result,0);
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
?>