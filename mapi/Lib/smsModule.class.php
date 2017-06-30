<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class smsModule extends MainBaseModule
{
	
	/**
	 * 	 短信验证码发送接口
	 * 
	 * 	 输入:  
	 *  mobile:string 手机号
	 *  unique:int 是否需要检测被占用 0:不检测 1:要检测是否被抢占（用于注册，绑定时使用）2:要检测是否存在（取回密码）3 检测会员是否绑定手机
	 *  
	 *  输出:
	 *  status:int 发送结果状态 0失败 1成功
	 *  info:信息返回
	 *  lesstime: int 剩余时间，秒
	 */
	public function send_sms_code()
	{
		$root = array();	

		if(app_conf("SMS_ON")==0)
		{
			output("",0,"短信功能未开启");
		}
		
		$mobile_phone = strim($GLOBALS['request']['mobile']);
		$unique = intval($GLOBALS['request']['unique']);
		if($unique==3)
		{
			if($GLOBALS['user_info']['mobile']=="") output("",0,"请先完善手机号");
		}
		if($mobile_phone=="")
		{		
			output("",0,"请输入手机号");
		}
		if(!check_mobile($mobile_phone))
		{
			output("",0,"手机号格式不正确");
		}			
		if($unique==1)
		{
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile = '".$mobile_phone."'")>0)
			{
				output("",0,"手机号已被占用");
			}
		}
		
		if($unique==2)
		{
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where mobile = '".$mobile_phone."'")==0)
			{
				output("",0,"手机号未注册");
			}
		}
		
		if(!check_ipop_limit(get_client_ip(), "send_sms_code",SMS_TIMESPAN))
		{
			output("",0,"请勿频繁发送短信");
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
			$mobile_data['ip'] = get_client_ip();
			$GLOBALS['db']->autoExecute(DB_PREFIX."sms_mobile_verify",$mobile_data,"INSERT","","SILENT");
				
		}

		send_verify_sms($mobile_phone,$code);
		$data['lesstime'] = SMS_TIMESPAN -(NOW_TIME - $mobile_data['add_time']);  //剩余时间
		output($data,1,"发送成功");
	}
	
}
?>