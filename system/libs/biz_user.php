<?php 

// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

define("EMPTY_ERROR",1);  //未填写的错误
define("FORMAT_ERROR",2); //格式错误
define("EXIST_ERROR",3); //已存在的错误

define("ACCOUNT_NO_EXIST_ERROR",1); //帐户不存在
define("ACCOUNT_PASSWORD_ERROR",2); //帐户密码错误
define("ACCOUNT_NO_VERIFY_ERROR",3); //帐户未激活


function auto_do_login_biz($account_user,$account_md5_password,$from_cookie = true){
	$result = array();
	$result['status'] = 1;
	$result['data'] = "";

	$biz_data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."supplier_account WHERE account_name='".$account_user."' AND is_delete = 0");

	if($biz_data){
		$pwdOK = false;
		if($from_cookie)
		{
			$pwdOK = md5($biz_data['account_password']."_EASE_COOKIE")==$account_md5_password;
		}
		else
		{
			$pwdOK = $biz_data['account_password']==$account_md5_password;
		}
		if($pwdOK){
			$GLOBALS['db']->query("update ".DB_PREFIX."supplier_account set login_ip = '".CLIENT_IP."' where id=".$biz_data['id']);
			es_session::set("account_info",$biz_data);
			$GLOBALS['account_info'] = $biz_data;
		}
	}
}

function do_login_biz($account_user,$account_password){
	$biz_data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."supplier_account WHERE account_name='".$account_user."' AND is_delete = 0");
	
	$result = array();
	$result['status'] =1;
	$result['data'] = '';
	if(!$biz_data)
	{
		$result['status'] = 0;
		$result['data'] = ACCOUNT_NO_EXIST_ERROR;
		return $result;
	}else{
		$biz_data['platform_status'] = $GLOBALS['db']->getOne("select platform_status from ".DB_PREFIX."supplier where id = ".$biz_data['supplier_id']);
		$result['account_info'] = $biz_data;
		if(strlen($account_password)==32)
		{			
			if($account_password!=$biz_data['account_password'])
			{
				$result['data'] = ACCOUNT_PASSWORD_ERROR;
				return $result;
			}
		}
		elseif($biz_data['account_password'] != md5($account_password)){
			$result['status'] = 0;
			$result['data'] = ACCOUNT_PASSWORD_ERROR;
			return $result;
		}
		elseif($biz_data['is_effect'] != 1)
		{
			$result['status'] = 0;
			$result['data'] = ACCOUNT_NO_VERIFY_ERROR;
			return $result;
		}
		
		
	    $account_locations = $GLOBALS['db']->getAll("select location_id from ".DB_PREFIX."supplier_account_location_link where account_id = ".$biz_data['id']);
	    $account_location_ids = array();
	    foreach($account_locations as $row)
	    {
	        $account_location_ids[] = $row['location_id'];
	    }
	    $biz_data['location_ids'] =  $account_location_ids;
		es_session::set("account_info",$biz_data);
		$GLOBALS['account_info'] = $biz_data;
		

		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_account set login_ip = '".CLIENT_IP."',login_time= ".NOW_TIME." where id =".$biz_data['id']);
		return $result;
	}
	
}

/**
 * 登出,返回 array('status'=>'',data=>'',msg=>'') msg存放整合接口返回的字符串
 */
function loginout_biz()
{
	$account_info = es_session::get("account_info");
	if(!$account_info)
	{
		return false;
	}
	else
	{
		es_session::delete("account_info");
		es_session::delete("biz_account_auth");
	}
}

?>