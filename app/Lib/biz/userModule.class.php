<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class userModule extends BizBaseModule{
	public function login(){
		init_app_page();
		$GLOBALS['tmpl']->display("login.html");
	}
	public function register(){
		init_app_page();
		if($GLOBALS['user_info']){
			$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		}
		$step = intval($_REQUEST['step']);
		if(empty($step)){
			$step=1;
		}
		if ($step==2){
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete = 0 order by sort desc");
			$GLOBALS['tmpl']->assign("cate_list",$cate_list);
			
			$deal_city_list = load_auto_cache("city_list_result");
			$GLOBALS['tmpl']->assign("city_list",$deal_city_list['ls']);
		}elseif ($step == 3){
			if($_POST)
			{
				$data['name'] =  addslashes(htmlspecialchars(trim($_REQUEST['name'])));
				$data['deal_cate_id'] = intval($_REQUEST['deal_cate_id']);
				foreach($_REQUEST['deal_cate_type_id'] as $type)
				{
					$data['deal_cate_type_id'][] = intval($type);
				}
				foreach($_REQUEST['area_id'] as $area)
				{
					$data['area_id'][] = intval($area);
				}
				$data['address'] = strim($_REQUEST['address']);
				$data['xpoint'] = floatval($_REQUEST['xpoint']);
				$data['ypoint'] = floatval($_REQUEST['ypoint']);
				$data['tel'] = strim($_REQUEST['tel']);
				$data['open_time'] = strim($_REQUEST['open_time']);
				$data['location_id'] = 0;
				$data['city_id'] = intval($_REQUEST['city_id']);
			}
			else
			{
				app_redirect(url("biz","user#register"));
			}
			
			$GLOBALS['tmpl']->assign("base_data",base64_encode(serialize($data)));	
			$user_id = intval($GLOBALS['user_info']['id']);
			if($user_id)
			{
				$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
			}	
		}elseif($step==4){
			$sid = $_REQUEST['sid'];
			if($sid>0){
				$supplier_data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."supplier_submit WHERE id=".$sid);
				$supplier_data['h_bank_info'] = preg_replace("/(\d{4})(?=\d)/","$1 ", $supplier_data['h_bank_info']);
				$GLOBALS['tmpl']->assign("supplier_data",$supplier_data);
			}
		}
		
		$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
		$GLOBALS['tmpl']->assign("sms_ipcount",load_sms_ipcount());
		$GLOBALS['tmpl']->assign("step",$step);
		$GLOBALS['tmpl']->display("register.html");
	}
	
	function do_login(){
		$account_name = strim($_POST['account_name']);
		$account_password = strim($_POST['account_password']);
		
		$data = array();
		//验证
		
		//验证码
		$verify = md5(strim($_POST['verify_code']));
		$session_verify = es_session::get('verify');
		
		if($verify!=$session_verify)
		{
			$data['status'] = false;
			$data['info']	=	"图片验证码错误";
			$data['field'] = "verify_code";
			ajax_return($data);
		}
		if($account_name == ''){
			$data['status'] = false;
			$data['info'] = "请输入用户名";
			$data['field'] = "account_user";
			ajax_return($data);
		}
		if($account_password == ''){
			$data['status'] = false;
			$data['info'] = "请输入密码";
			$data['field'] = "account_password";
			ajax_return($data);
		}
		$account_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."supplier_account WHERE account_name='".$account_name."' AND is_delete=0");
		
		require_once APP_ROOT_PATH."system/libs/biz_user.php";
		if(check_ipop_limit(CLIENT_IP,"biz_dologin",intval(app_conf("SUBMIT_DELAY"))))
			$result = do_login_biz($account_name,$account_password);
		else
		{
			showErr("提交太快了",1);
		}
		

		if($result['status'])
		{
// 			$s_account_info = es_session::get("account_info");
			
// 			if(intval($_POST['auto_login'])==1)
// 			{
// 				//自动登录，保存cookie
// 				$account_info = $s_account_info;
// 				es_cookie::set("account_name",$account_info['account_name'],3600*24*30);
// 				es_cookie::set("account_pwd",md5($account_info['account_password']."_EASE_COOKIE"),3600*24*30);
// 			}
// 			if(strim($_REQUEST['form_prefix'])=="ajax")
// 			{
// 				$GLOBALS['account_info'] = $s_account_info;
// 				if($GLOBALS['account_info'])
// 				{
// 					$GLOBALS['tmpl']->assign("account_info",$s_account_info);
// 				}
// 				$tip = $GLOBALS['tmpl']->fetch("inc/insert/load_biz_user_tip.html");
// 			}

			//获取权限
			$biz_account_auth = get_biz_account_auth();
			if(empty($biz_account_auth)){
			    showBizErr("请更换帐号登录，此账户还没有分配权限",1);
			}else{
			    $jump_url = url("biz",$biz_account_auth[0]);
			}
			$return['status'] = true;
			$return['info'] = "登录成功";
			$return['data'] = $result['msg'];
			$return['jump'] = $jump_url;
			$return['tip'] = $tip;

			ajax_return($return);
		}
		else
		{
			if($result['data'] == ACCOUNT_NO_EXIST_ERROR)
			{
				$field = "account_name";
				$err = $GLOBALS['lang']['USER_NOT_EXIST'];
			}
			if($result['data'] == ACCOUNT_PASSWORD_ERROR)
			{
				$field = "account_password";
				$err = $GLOBALS['lang']['PASSWORD_ERROR'];
			}
			if($result['data'] == ACCOUNT_NO_VERIFY_ERROR)
			{
				$field = "account_name";
				$err = $GLOBALS['lang']['USER_NOT_VERIFY'];
			}
			$data['status'] = false;
			$data['info']	=	$err;
			$data['field'] = $field;
			ajax_return($data);
		}
		
	}
	
	function do_register(){
		$post_data = $_POST;
        
		$account_name = strim($_POST['account_name']);
		$account_mobile = strim($_POST['account_mobile']);
		$sms_verify = strim($_POST['sms_verify']);
		$account_password = strim($_POST['account_password']);
		$account_password_confirm = strim($_POST['account_password_confirm']);
		$h_name = strim($_POST['h_name']);
		$h_faren = strim($_POST['h_faren']);
		$h_tel = strim($_POST['h_tel']);
		
		$h_bank_info = strim(str_replace(" ", "", $_POST['h_bank_info']));
		$h_bank_user = strim($_POST['h_bank_user']);
		$h_bank_name = strim($_POST['h_bank_name']);
		
		if($account_password!=$account_password_confirm)
		{
			$data['status'] = false;
			$data['info'] = "您两次输入的密码不匹配";
			$data['field'] = "account_password_confirm";
			ajax_return($data);
		}
		
		if($account_password=='')
		{
			$data['status'] = false;
			$data['info'] = "请输入密码";
			$data['field'] = "account_password";
			ajax_return($data);
		}
		
		if($account_mobile=="")
		{
			$data['status'] = false;
			$data['info']	=	"请输入手机号";
			$data['field'] = "account_mobile";
			ajax_return($data);
		}
		if(!check_mobile($account_mobile)){
		    $data['status'] = false;
		    $data['info']	=	"手机号格式不正确";
		    $data['field'] = "account_mobile";
		    ajax_return($data);
		}

		//手机号不能重复	
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_account  where  mobile=".$account_mobile)>0)
		{
			$data['status'] = false;
			$data['field'] = "account_mobile";
			$data['info']	=	"手机号已被注册";
			ajax_return($data);
		}		
		
		if($sms_verify=="" && app_conf("SMS_ON") == 1 &&  app_conf("BIZ_REGISTER_SMS") ==1)
		{
			$data['status'] = false;
			$data['info']	=	"请输入收到的验证码";
			$data['field'] = "sms_verify";
			ajax_return($data);
		}
		
		if($h_name=="")
		{
			$data['status'] = false;
			$data['info']	=	"请输入企业名称";
			$data['field'] = "h_name";
			ajax_return($data);
		}
		if($h_faren=="")
		{
			$data['status'] = false;
			$data['info']	=	"请输入法人名称";
			$data['field'] = "h_faren";
			ajax_return($data);
		}
		if($_POST['h_license']=="")
		{
			$data['status'] = false;
			$data['info']	=	"营业执照不能为空";
			$data['field'] = "h_license";
			ajax_return($data);
		}
		
		if($h_bank_info=="")
		{
			$data['status'] = false;
			$data['info']	=	"请输入开户银行帐号";
			$data['field'] = "h_bank_info";
			ajax_return($data);
		}
		if($h_bank_user=="")
		{
			$data['status'] = false;
			$data['info']	=	"请输入开户银行户名";
			$data['field'] = "h_bank_user";
			ajax_return($data);
		}
		if($h_bank_name=="")
		{
			$data['status'] = false;
			$data['info']	=	"请输入开户银行名称";
			$data['field'] = "h_bank_name";
			ajax_return($data);
		}
		if(app_conf("SMS_ON") == 1){
    		//短信码验证
    		$sql = "DELETE FROM ".DB_PREFIX."sms_mobile_verify WHERE add_time <=".(NOW_TIME-SMS_EXPIRESPAN);
    		$GLOBALS['db']->query($sql);
    		
		    $mobile_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$account_mobile."'");
		    
		    if($mobile_data['code']!=$sms_verify)
		    {
		        $data['status'] = false;
		        $data['info']	=  "验证码错误";
		        $data['field'] = "sms_verify";
		        ajax_return($data);
		    }
		}
		

		$check_data = $this->check_register_field($data);
		
		if(!$check_data['status'])
		{
			ajax_return($check_data);
		}
		
		es_session::delete("verify");
		//验证成功
		$ins_data = array();
		
		$base_data = unserialize(base64_decode($_REQUEST['base_data']));
		$ins_data['name'] = $base_data['name'];
		$ins_data['cate_config'] = serialize(array('deal_cate_id'=>$base_data['deal_cate_id'],'deal_cate_type_id'=>$base_data['deal_cate_type_id']));
		$ins_data['location_config'] = serialize($base_data['area_id']);
		$ins_data['address'] = $base_data['address'];
		$ins_data['tel'] = $base_data['tel'];
		$ins_data['open_time'] = $base_data['open_time'];
		$ins_data['xpoint'] = $base_data['xpoint'];
		$ins_data['ypoint'] = $base_data['ypoint'];
		$ins_data['location_id'] = $base_data['location_id'];
		$ins_data['city_id'] = $base_data['city_id'];
		$ins_data['location_id'] = 0;
		
		$ins_data['account_name'] = $account_name;
		$ins_data['account_password'] = md5($account_password);
		$ins_data['account_mobile'] = $account_mobile;
		$ins_data['h_name'] = $h_name;
		$ins_data['h_faren'] = $h_faren;
		$ins_data['h_tel'] = $h_tel;
		//图片
		$ins_data['h_license'] = strim($post_data['h_license']);
		$ins_data['h_other_license'] = strim($post_data['h_other_license']);
		$ins_data['h_supplier_logo'] = strim($post_data['h_supplier_logo']);
		$ins_data['h_supplier_image'] = strim($post_data['h_supplier_image']);
		
		//银行信息
		$ins_data['h_bank_info'] = $h_bank_info;
		$ins_data['h_bank_user'] = $h_bank_user;
		$ins_data['h_bank_name'] = $h_bank_name;
		
		$ins_data['create_time'] = NOW_TIME;
        //会员信息
	    $user_info = es_session::get('user_info');
		if($user_info){ //用户已经存在了就不再绑定
			if(intval($GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".DB_PREFIX."supplier_submit WHERE user_id=".$GLOBALS['user_info']['id']))==0){
				$ins_data['user_id'] = $user_info['id'];
				$ins_data['h_user_name']= $user_info['user_name'];
			}
			
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_submit",$ins_data,'INSERT');
		$insert_id = $GLOBALS['db']->insert_id();
		if($insert_id){
			$GLOBALS['db']->query("delete from ".DB_PREFIX."sms_mobile_verify where mobile_phone = '".$account_mobile."'");
			$data['status'] = true;
			$data['info']	=	"申请成功，等待审核!";
			$data['jump'] = url("biz","user#register",array('step'=>'4','sid'=>$insert_id));
			ajax_return($data);
		}
		
	}
	
	/**
	 * 验证会员字段的有效性
	 * @param array $data  字段名称/值
	 * @return array
	 */
	function check_register_field($data)
	{
		$data = array();
		$data['status'] = true;
		$data['info'] = "";
		
		if(strim($data['account_name']))
		{
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_account where account_name = '".$data['account_name']."'");
			if(intval($rs)>0)
			{
				$data['status'] = false;
				$data['info'] = "账户已被注册";
				$data['field'] = "account_name";
				return $data;
			}
		}
		
		if(strim($data['account_mobile']))
		{
			if(!check_mobile($data['account_mobile']))
			{
				$data['status'] = false;
				$data['info'] = "手机号格式不正确";
				$data['field'] = "account_mobile";
				return $data;
			}
			$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_account where account_mobile = '".$data['account_mobile']."'");
			if(intval($rs)>0)
			{
				$data['status'] = false;
				$data['info'] = "手机号已被注册";
				$data['field'] = "account_mobile";
				return $data;
			}
		}

		if(strim($data['verify_code']) && app_conf("SMS_ON") == 1)
		{
	
			$verify = md5($data['verify_code']);
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
	
	public function logout()
	{
		require_once APP_ROOT_PATH."system/libs/biz_user.php";
		loginout_biz();
		es_cookie::delete("account_name");
		es_cookie::delete("account_pwd");
		es_session::delete("biz_nav_list");
		es_session::delete("biz_account_auth");
		$jump = url("biz","user#login");
		app_redirect($jump);
	}
	
	public function edit_password(){
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->display("edit_password.html");
	}
	
	public function do_edit_password(){
		global_run();
		$data = array();
		$data['status'] = 1;
		
		$account_password = strim($_POST['account_password']);
		$new_account_password = strim($_POST['new_account_password']);
		$rnew_account_password = strim($_POST['rnew_account_password']);
		if($account_password == ''){
			$data['status'] = 0;
			$data['info'] = "原密码不能为空";
			ajax_return($data);
		}
		if($new_account_password == ''){
			$data['status'] = 0;
			$data['info'] = "新密码不能为空";
			ajax_return($data);
		}
		if(strlen($new_account_password)<6){
			$data['status'] = 0;
			$data['info'] = "新密码长度不能小于6位";
			ajax_return($data);
		}
		if($rnew_account_password == ''){
			$data['status'] = 0;
			$data['info'] = "请确认新密码";
			ajax_return($data);
		}
		if($new_account_password != $rnew_account_password){
			$data['status'] = 0;
			$data['info'] = "请确认两次输入的新密码";
			ajax_return($data);
		}
		$account_info = $GLOBALS['account_info'];

		if($account_info){//用户必须登录存在

			if(md5($account_password) != $account_info['account_password']){
				$data['status'] = 0;
				$data['info'] = "原密码错误";
				ajax_return($data);
			}else{
				$GLOBALS['db']->query("update ".DB_PREFIX."supplier_account set account_password = '".md5($new_account_password)."' where id = ".intval($account_info['id']));
				$data['jump'] = url("biz","user#logout");
			}
		}else{
			$data['status'] = 0;
			$data['info'] = "请登录后修改！";
			ajax_return($data);
			
		}
		ajax_return($data);
		
	}
	
	public function getpassword(){
	
	}
	
	public function load_sub_cate()
	{
		$cate_id = intval($_REQUEST['id']);
		$type_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where l.cate_id = ".$cate_id);
		$html = "";
		foreach($type_list as $item)
		{
			$html.='<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_cate_type_id[]" value="'.$item['id'].'" />'.$item['name'].'</label>';
		}
	
		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	}
	
	public function load_city_area()
	{
		$city_id = intval($_REQUEST['id']);
		$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id." and pid = 0 order by sort desc");
		$html = "";
		if($area_list)
		{
			$html = "<select name='area_id[]'  class='ui-select'>";
			foreach($area_list as $item)
			{
				$html .= "<option value='".$item['id']."'>".$item['name']."</option>";
			}
			$html.="</select>";
		}
		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	
	}
	
	public function load_quan_list()
	{
		$area_id = intval($_REQUEST['id']);
		$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where pid = ".$area_id." order by sort desc");
		$html = "";
		foreach($area_list as $item)
		{
			$html.='<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="area_id[]" value="'.$item['id'].'" />'.$item['name'].'</label>';
		}
	
		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	}
	
}
function check_issupplier()
{
	$account_name = $GLOBALS['user_info']['merchant_name'];
	$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$account_name."' and is_effect = 1 and is_delete = 0");
	if($account)
	{
		$s_account_info = es_session::get("account_info");
		if(intval($s_account_info['id'])==0)
		{
			showErr("您已经是商家会员，请登录",0,url("biz"));
		}
		else
			app_redirect(url("biz"));
	}

}
?>