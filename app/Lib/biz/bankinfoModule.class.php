<?php 
/**
 * 银行卡绑定
 */


class bankinfoModule extends BizBaseModule
{
    
	function __construct()
	{
        parent::__construct();
        global_run();
        $this->check_auth();
    }
	
    
	public function index()
	{		
				
		init_app_page();
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		

		
		$supplier_info=$GLOBALS['db']->getRow("select * from  ".DB_PREFIX."supplier where id=".$supplier_id);


	    $GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
	    $GLOBALS['tmpl']->assign("sms_ipcount",load_sms_ipcount());

	    
	    $GLOBALS['tmpl']->assign("supplier_info",$supplier_info);		
		
		$GLOBALS['tmpl']->assign("head_title","银行卡绑定");
		$GLOBALS['tmpl']->display("pages/bankinfo/index.html");	
	
	}
	
	
	public function update()
	{
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);	
		
		$bank_num=strim($_REQUEST['bank_num']);	
		$bank_name=strim($_REQUEST['bank_name']);	
		$bank_account_name=strim($_REQUEST['bank_user']);
		
		if($bank_num == ''){
				$data['status'] = false;
				$data['info'] = "请输入银行账号";			
				ajax_return($data);
		}
		if($bank_name == ''){
				$data['status'] = false;
				$data['info'] = "请输入银行名称";			
				ajax_return($data);
		}
		if($bank_account_name == ''){
				$data['status'] = false;
				$data['info'] = "请输入开户人姓名";			
				ajax_return($data);
		}	
		
		if(app_conf("SMS_ON")==1){
			//短信码验证
			$sms_verify = strim($_REQUEST['sms_verify']);
			$mobile_phone=$GLOBALS['db']->getOne("select mobile from ".DB_PREFIX."supplier_account where supplier_id=".$s_account_info['supplier_id']." and is_main=1");
			if($sms_verify == ''){
				$data['status'] = false;
				$data['info'] = "请输入手机验证码";			
				ajax_return($data);
			}
			$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
			$GLOBALS['db']->query($sql);
			
			$mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");			

			if($mobile_data['code']!=$sms_verify)
			{
				$data['status'] = false;
				$data['info']	=  "手机验证码错误";
				$data['field'] = "sms_verify";
				ajax_return($data);
			}
		}else{
			$account_password = strim($_REQUEST['pwd']);			
			if($account_password == ''){
				$data['status'] = false;
				$data['info'] = "请输入密码";			
				ajax_return($data);
			}
			if(md5($account_password)!=$s_account_info['account_password']){
				$data['status'] = false;
				$data['info'] = "密码不正确";			
				ajax_return($data);
			}
		}
		
		$supplier_info=$GLOBALS['db']->getRow("select * from  ".DB_PREFIX."supplier where id=".$supplier_id);
		
		
		$datas = array();
		$datas['bank_info'] = $bank_num;
		$datas['bank_name'] = $bank_name;
		$datas['bank_user'] = $bank_account_name;


		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier",$datas,"UPDATE","id=".$supplier_id);		

		$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
			
		$data['status'] = 1;
		$data['info'] = "银行卡信息修改成功";
		ajax_return($data);		
		
	}
	

	/**
	 * 发送商家提现手机验证码
	 */
	public function biz_sms_code()
	{
		$s_account_info = $GLOBALS["account_info"];
		$verify_code = strim($_REQUEST['verify_code']);
	
	
		$sms_ipcount = load_sms_ipcount();
		if($sms_ipcount>1)
		{
			//需要图形验证码
			if(es_session::get("verify")!=md5($verify_code))
			{
				$data['status'] = false;
				$data['info'] = "图形验证码错误";
				$data['field'] = "verify_code";
				ajax_return($data);
			}
		}
	
		if(!check_ipop_limit(CLIENT_IP, "send_sms_code",SMS_TIMESPAN))
		{
			showErr("请勿频繁发送短信",1);
		}
	
		$mobile_phone=$GLOBALS['db']->getOne("select mobile from ".DB_PREFIX."supplier_account where supplier_id=".$s_account_info['supplier_id']." and is_main=1");
		
		if(empty($mobile_phone))
		{
			$data['status'] = false;
			$data['info'] = "商户未提供验证手机号，请联系管理员";
			ajax_return($data);
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
		send_verify_sms($mobile_phone,$code);
		es_session::delete("verify"); //删除图形验证码
		$data['status'] = true;
		$data['info'] = "发送成功";
		$data['lesstime'] = SMS_TIMESPAN -(NOW_TIME - $mobile_data['add_time']);  //剩余时间
		$data['sms_ipcount'] = load_sms_ipcount();
		ajax_return($data);	
	
	}		
	

}
?>