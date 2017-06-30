<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


require_once APP_ROOT_PATH.'system/model/user.php';
class uc_moneyModule extends MainBaseModule
{
	public function index()
	{
		 app_redirect(url("index","uc_money#incharge"));
	}
	
	/**
	 * 提现
	 */
	public function withdraw()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
		init_app_page();
		$user_info = $GLOBALS['user_info'];
		//取出等级信息
		$level_data = load_auto_cache("cache_user_level");
		$cur_level = $level_data[$user_info['level_id']];
		 
		//游标移动获取下一个等级
		reset($level_data);
		do{
			$current_data = current($level_data);
			 
			if($current_data['id']==$cur_level['id'])
			{
			  
				$next_data = next($level_data);
				break;
			}
		}while(next($level_data));
		$uc_query_data = array();
		$uc_query_data['cur_level'] = $cur_level['level']; //当前等级
		$uc_query_data['cur_point'] = $user_info['point'];
		$uc_query_data['cur_level_name'] = $cur_level['name'];
		if($next_data){
			$uc_query_data['next_level'] = $next_data['id'];
			$uc_query_data['next_point'] =$next_data['point'] - $user_info['point']; //我再增加：100 经验值，就可以升级为：青铜五
			$uc_query_data['next_level_name'] = $next_data['name'];
		}
		 
		 
		$uc_query_data['cur_score'] = $user_info['score'];
		$cur_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id=".$user_info['group_id']);
		$uc_query_data['cur_gourp'] = $cur_group['id'];
		$uc_query_data['cur_gourp_name'] = $cur_group['name'];
		$uc_query_data['cur_discount'] = floatval(sprintf('%.2f', $cur_group['discount']*10));
		
		$GLOBALS['tmpl']->assign("uc_query_data",$uc_query_data);
		
		$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
		
		
		
		require_once APP_ROOT_PATH."system/model/user_center.php";
		require_once APP_ROOT_PATH."app/Lib/page.php";
		//输出充值订单
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$result = get_user_withdraw($limit,$GLOBALS['user_info']['id']);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		//通用模版参数定义
		assign_uc_nav_list();//左侧导航菜单
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$GLOBALS['tmpl']->assign("page_title","会员提现"); //title
		$GLOBALS['tmpl']->display("uc/uc_money_withdraw.html"); //title
	} 
	
	public function del_withdraw()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		else
		{
			$id = intval($_REQUEST['id']);
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."withdraw where id = ".$id." and is_delete = 0 and user_id = ".$GLOBALS['user_info']['id']);
			if($order_info)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."withdraw set is_delete = 1 where is_delete = 0 and user_id = ".$GLOBALS['user_info']['id']." and id = ".$id);
				if($GLOBALS['db']->affected_rows())
				{
					$data['status'] = 1;
					$data['info'] = "删除成功";
					ajax_return($data);
				}
				else
				{
					$data['status'] = 0;
					$data['info'] = "删除失败";
					ajax_return($data);
				}
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = "提现单不存在";
				ajax_return($data);
			}
		}
	}
	
	
	public function withdraw_done()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		
		$bank_name = strim($_REQUEST['bank_name']);
		$bank_account = strim($_REQUEST['bank_account']);
		$bank_user = strim($_REQUEST['bank_user']);
		$money = floatval($_REQUEST['money']);
		$mobile = $GLOBALS['user_info']['mobile'];
		
		$sms_verify = strim($_REQUEST['sms_verify']);
		if($bank_name=="")
		{
			$data['status'] = 0;
			$data['info'] = "请输入开户行全称";
			ajax_return($data);
		}
		if($bank_account=="")
		{
			$data['status'] = 0;
			$data['info'] = "请输入开户行账号";
			ajax_return($data);
		}
		if($bank_user=="")
		{
			$data['status'] = 0;
			$data['info'] = "请输入开户人真实姓名";
			ajax_return($data);
		}
		if($money<=0)
		{
			$data['status'] = 0;
			$data['info'] = "请输入正确的提现金额";
			ajax_return($data);
		}
		
		if(app_conf("SMS_ON")==1)
		{
			if($mobile=="")
			{
				$data['status'] = 0;
				$data['info'] = "请先完善会员的手机号码";
				$data['jump'] = url("index","uc_account");
				ajax_return($data);
			}
			
			
			
		
			if($sms_verify=="")
			{
				$data['status'] = 0;
				$data['info']	=	"请输入收到的验证码";
				ajax_return($data);
			}
		
			//短信码验证
			$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
			$GLOBALS['db']->query($sql);
		
			$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile."'");
		
			if($mobile_data['code']!=$sms_verify)
			{
				$data['status'] = 1;
				$data['info']	=  "验证码错误";
				ajax_return($data);
			}
		
			
		}
		
		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."withdraw where user_id = ".$GLOBALS['user_info']['id']." and is_delete = 0 and is_paid = 0"));
		if($submitted_money+$money>$GLOBALS['user_info']['money'])
		{
			$data['status'] = 0;
			$data['info'] = "提现超额";
			ajax_return($data);
		}
		
		$withdraw_data = array();
		$withdraw_data['user_id'] = $GLOBALS['user_info']['id'];
		$withdraw_data['money'] = $money;
		$withdraw_data['create_time'] = NOW_TIME;
		$withdraw_data['bank_name'] = $bank_name;
		$withdraw_data['bank_account'] = $bank_account;
		$withdraw_data['bank_user'] = $bank_user;
		$GLOBALS['db']->autoExecute(DB_PREFIX."withdraw",$withdraw_data);
		
		$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile."'");
		$data['status'] = 1;
		$data['info'] = "提现申请提交成功，请等待审核";
		ajax_return($data);
	}
    
    /**
     * 充值
     */
	public function incharge()
	{
	    global_run();
	    if(check_save_login()!=LOGIN_STATUS_LOGINED)
	    {
	        app_redirect(url("index","user#login"));
	    }
	    init_app_page();
	    $user_info = $GLOBALS['user_info'];
	    //取出等级信息
	    $level_data = load_auto_cache("cache_user_level");
	    $cur_level = $level_data[$user_info['level_id']];
	    
	    //游标移动获取下一个等级
	    reset($level_data);
	    do{
	    	$current_data = current($level_data);
	    	 
	    	if($current_data['id']==$cur_level['id'])
	    	{
	    
	    		$next_data = next($level_data);
	    		break;
	    	}
	    }while(next($level_data));
	    $uc_query_data = array();
	    $uc_query_data['cur_level'] = $cur_level['level']; //当前等级
	    $uc_query_data['cur_point'] = $user_info['point'];
	    $uc_query_data['cur_level_name'] = $cur_level['name'];
	    if($next_data){
	    	$uc_query_data['next_level'] = $next_data['id'];
	    	$uc_query_data['next_point'] =$next_data['point'] - $user_info['point']; //我再增加：100 经验值，就可以升级为：青铜五
	    	$uc_query_data['next_level_name'] = $next_data['name'];
	    }
	    
	    
	    $uc_query_data['cur_score'] = $user_info['score'];
	    $cur_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id=".$user_info['group_id']);
	    $uc_query_data['cur_gourp'] = $cur_group['id'];
	    $uc_query_data['cur_gourp_name'] = $cur_group['name'];
	    $uc_query_data['cur_discount'] = floatval(sprintf('%.2f', $cur_group['discount']*10));

	    $GLOBALS['tmpl']->assign("uc_query_data",$uc_query_data);
	    
	    
	    //输出支付方式
		$payment_list = load_auto_cache("cache_payment");
		$icon_paylist = array(); //用图标展示的支付方式
		//$disp_paylist = array(); //特殊的支付方式(Voucher,Account,Otherpay)
		$bank_paylist = array(); //网银直连
		foreach($payment_list as $k=>$v)
		{
			if($v['class_name']=="Voucher"||$v['class_name']=="Account"||$v['class_name']=="Otherpay"||$v['class_name']=="tenpayc2c")
			{
				//$disp_paylist[] = $v;
			}
			else
			{
				if($v['class_name']=="Alipay")
				{
					$cfg = unserialize($v['config']);
					if($cfg['alipay_service']==2)
					{
						if($v['is_bank']==1)
							$bank_paylist[] = $v;
						else
							$icon_paylist[] = $v;
					}
				}
				else
				{
					if($v['is_bank']==1)
					$bank_paylist[] = $v;	
					else
					$icon_paylist[] = $v;
				}
			}
		}
	
		$GLOBALS['tmpl']->assign("icon_paylist",$icon_paylist);
		//$GLOBALS['tmpl']->assign("disp_paylist",$disp_paylist);
		$GLOBALS['tmpl']->assign("bank_paylist",$bank_paylist);
		
		require_once APP_ROOT_PATH."system/model/user_center.php";
		require_once APP_ROOT_PATH."app/Lib/page.php";
		//输出充值订单
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$result = get_user_incharge($limit,$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
	    
	    //通用模版参数定义
		assign_uc_nav_list();//左侧导航菜单
	    $GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
	    $GLOBALS['tmpl']->assign("page_title","会员充值"); //title
	    $GLOBALS['tmpl']->display("uc/uc_money_incharge.html"); //title
	}
	
	
	public function incharge_done()
	{
		global_run();
		init_app_page();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
		
		$payment_id = intval($_REQUEST['payment']);
		$money = floatval($_REQUEST['money']);
		if($money<=0)
		{
			showErr($GLOBALS['lang']['PLEASE_INPUT_CORRECT_INCHARGE']);
		}
		
		$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_id);
		if(!$payment_info)
		{
			showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT']);
		}
		
		if($payment_info['fee_type']==0) //定额
		{
			$payment_fee = $payment_info['fee_amount'];
		}
		else //比率
		{
			$payment_fee = $money * $payment_info['fee_amount'];
		}
		
		//开始生成订单
		$now = NOW_TIME;
		$order['type'] = 1; //充值单
		$order['user_id'] = $GLOBALS['user_info']['id'];
		$order['create_time'] = $now;
		$order['total_price'] = $money + $payment_fee;
		$order['deal_total_price'] = $money;
		$order['pay_amount'] = 0;  
		$order['pay_status'] = 0;  
		$order['delivery_status'] = 5;  
		$order['order_status'] = 0; 
		$order['payment_id'] = $payment_id;
		$order['payment_fee'] = $payment_fee;
		$order['bank_id'] = strim($_REQUEST['bank_id']);
		
	
		do
		{
			$order['order_sn'] = to_date(get_gmtime(),"Ymdhis").rand(100,999);
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_order",$order,'INSERT','','SILENT'); 
			$order_id = intval($GLOBALS['db']->insert_id());
		}while($order_id==0);
		
		require_once APP_ROOT_PATH."system/model/cart.php";
		$payment_notice_id = make_payment_notice($order['total_price'],$order_id,$payment_info['id']);
		//创建支付接口的付款单
	
		$rs = order_paid($order_id);  
		if($rs)
		{
			app_redirect(url("index","payment#incharge_done",array("id"=>$order_id))); //充值支付成功
		}
		else
		{
			app_redirect(url("index","payment#pay",array("id"=>$payment_notice_id))); 
		}
	}
}
?>