<?php 
/**
 * 提现
 */
require APP_ROOT_PATH.'app/Lib/page.php';
//require_once APP_ROOT_PATH."system/model/supplier.php";
class withdrawalModule extends BizBaseModule
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
		
		
	    //分页
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
	   
	    $list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_money_submit  where supplier_id=".$supplier_id." order by status asc,create_time desc limit ".$limit);
	    foreach($list as $k=>$v){
			if($v['status']==1){
				$list[$k]['status']="已确认提现";
			}else if($v['status']==2){
				$list[$k]['status']="已拒绝";
				if($v['reason'])
					$list[$k]['status'].=":".$v['reason'];
			}	    	
			else
			{
				$list[$k]['status']="待审核";
			}
	    }
	    
	    $total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_money_submit  where supplier_id=".$supplier_id);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);

	    $GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
	    $GLOBALS['tmpl']->assign("sms_ipcount",load_sms_ipcount());

	    $GLOBALS['tmpl']->assign("list",$list);
	    $GLOBALS['tmpl']->assign("supplier_info",$supplier_info);		
		
		$GLOBALS['tmpl']->assign("head_title","商户提现");
		$GLOBALS['tmpl']->display("pages/withdrawal/index.html");	
	
	}
	
	
	public function withdraw_done()
	{
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);		
		
	
		
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
		
		$money = floatval($_REQUEST['money']);
		if($money<=0)
		{
			$data['status'] = 0;
			$data['info'] = "请输入正确的提现金额";
			ajax_return($data);
		}
		
		$submitted_money = floatval($GLOBALS['db']->getOne("select sum(money) from ".DB_PREFIX."supplier_money_submit where supplier_id = ".$supplier_id." and status = 0"));
		if($submitted_money+$money>$supplier_info['money'])
		{
			$data['status'] = 0;
			$data['info'] = "提现超额";
			ajax_return($data);
		}
		
		$withdraw_data = array();
		$withdraw_data['supplier_id'] = $supplier_id;
		$withdraw_data['money'] = $money;
		$withdraw_data['create_time'] = NOW_TIME;
		$withdraw_data['status'] = 0;

		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_money_submit",$withdraw_data);		

		$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$mobile_phone."'");
			
		$data['status'] = 1;
		$data['info'] = "提现申请提交成功，请等待管理员审核";
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