<?php
/**
 * 团购券单独验证
 * @param array $s_account_info 
 * @param string $pwd //验证码
 * @param int $location_id //门店编号
 * @return string|number
 */
function biz_check_coupon($s_account_info,$pwd,$location_id)
{

		$now = NOW_TIME;

		$supplier_id = intval($s_account_info['supplier_id']);
		
		$sql = "select c.refund_status, c.id as id,c.deal_id, c.order_id,c.order_deal_id, c.is_valid,doi.sub_name, doi.name as name,doi.number as number, c.sn as sn,c.supplier_id as supplier_id,c.confirm_time as confirm_time,c.deal_type from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where  c.password = '".$pwd."' and c.is_valid in(1,2) and c.is_delete = 0  and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>".$now.")";
		$coupon_data = $GLOBALS['db']->getRow($sql);
		if(empty($coupon_data)){
			$result['status'] = 0;
			$result['msg'] = "没有团购券数据";
			return $result;
		}
		
		// 查询是否有符合：密码正确，有效性 为 1:已发放给用户 2：退款被禁用',，没有过期，
		if($coupon_data)
		{
			if($coupon_data['confirm_time'] > 0)	//验证时间
			{
				$result['status'] = 0;
				$result['msg'] = "该券已于".to_date($coupon_data['confirm_time'])."使用过";
			
				return $result;
			}
			if($coupon_data['is_valid'] == 2){ //退款被禁用
				$result['status'] = 0;
				$result['msg'] = "该团购券已经失效无法验证";
				return $result;
			}

			if($coupon_data['refund_status'] == 1 || $coupon_data['refund_status'] == 2){	//退款状态 0:无  1:用户申请退款 2:已确认 3:拒绝，但是可以再使用
				$result['status'] = 0;
				$result['msg'] = "团购券提交了退款申请，无法验证";
				return $result;
			}
			if (!in_array($location_id,$s_account_info['location_ids'])){
			    $result['status'] = 0;
			    $result['msg'] = "没有门店权限验证该团购券";
			    return $result;
			}
			//查询门店对商品是否有权限
			$sql = "select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.deal_id = ".$coupon_data['deal_id']." and l.location_id =".$location_id;
			$deal_info = $GLOBALS['db']->getRow($sql);
			if(!$deal_info)		
			{
				$result['status'] = 0;
				$result['msg'] = "没有门店权限验证该团购券";					
				return $result;
			}
			if(!$coupon_data['name'])
				$coupon_data['name'] = $deal_info['name'];
			if(!$coupon_data['sub_name'])
				$coupon_data['sub_name'] = $deal_info['sub_name'];

			if($coupon_data['supplier_id']!=$supplier_id)	//是否是该商户的商品
			{
				$result['status'] = 0;
				$result['msg'] = "该券为其他团购商户的团购券，不能确认";

				return $result;
			}
			
			$result['status'] = 1;
			$result['coupon_data'] = $coupon_data;
			$result['location_id'] = $location_id;
			$result['coupon_pwd'] = $pwd;
			//'发券方式 0:按件发送 1:按单发券(同类商品买多件只发放一张团购券,用于一次性验证)',
			if($coupon_data['deal_type'] == 1) 
			{
				$result['msg'] = $coupon_data['name']."(购买数量：".$coupon_data['number']."), 团购券有效";
				$result['sub_msg'] = $coupon_data['sub_name']."(购买数量：".$coupon_data['number']."), 团购券有效";
				$result['number'] = $coupon_data['number'];
			}
			else
			{
				$result['msg'] = $coupon_data['name'].", 团购券有效";
				$result['sub_msg'] = $coupon_data['sub_name'].", 团购券有效";
				$result['number'] = 1;
			}
			return $result;
		}
}	
	
/**
 * 团购券单独使用
 * @param unknown_type $s_account_info
 * @param unknown_type $pwd
 * @param unknown_type $location_id
 * @return Ambigous <string, number, unknown, number>
 */
function biz_use_coupon($s_account_info,$pwd,$location_id)
{
	$result = biz_check_coupon($s_account_info,$pwd,$location_id);

	if ($result['status'] == 1){
		$coupon_data = $result['coupon_data'];
		$now = NOW_TIME;
		$supplier_id = intval($s_account_info['supplier_id']);
			
		//开始确认
		require_once  APP_ROOT_PATH."system/model/deal_order.php";
		use_coupon($pwd,$location_id,$s_account_info['id'],true,true);
		$deal_type = intval($GLOBALS['db']->getOne("select deal_type from ".DB_PREFIX."deal where id = ".intval($coupon_data['deal_id'])));
		if($deal_type == 1)
		{
			$result['msg'] = $coupon_data['name']."(购买数量：".$coupon_data['number'].")".sprintf($GLOBALS['lang']['COUPON_USED_OK'],to_date($now));
			$result['sub_msg'] = $coupon_data['sub_name']."(购买数量：".$coupon_data['number'].")".sprintf($GLOBALS['lang']['COUPON_USED_OK'],to_date($now));
		}
		else
		{
			$result['msg'] = $coupon_data['name'].sprintf($GLOBALS['lang']['COUPON_USED_OK'],to_date($now));;
			$result['sub_msg'] = $coupon_data['sub_name'].sprintf($GLOBALS['lang']['COUPON_USED_OK'],to_date($now));
		}
	}
	return $result;
}
/**
 * 团购券批量验证， 如果全部正确则直接使用
 * @param unknown_type $s_account_info
 * @param unknown_type $location_id
 * @param unknown_type $coupon_pwds
 * @return string
 */
function biz_check_coupon_batch($s_account_info,$location_id,$coupon_pwds)
{
	$now = NOW_TIME;
	$supplier_id = intval($s_account_info['supplier_id']);

	$check_data = array();	//检查已经验证过的数据
	$result_data = array();	//对应PWD 的验证过的数据
	$count_err = 0;
	foreach($coupon_pwds as $k=>$v){
		$is_err = 0;
		$pwd=$v;
		if(empty($pwd)){//为空的
			$result[$k]['msg'] = "";
			$result[$k]['status']=-1;
			continue;
		}

		if(!is_numeric($pwd)){
			$result[$k] = $result_data[$pwd];
			$result[$k]['msg'] = "验证码必须为数字";
			$result[$k]['status']=0;
			$is_err=1;
		}
		if(in_array($pwd, $check_data)){ //如果有重复数据
			$result[$k] = $result_data[$pwd];
			$result[$k]['msg'] = "重复的验证码";
			$result[$k]['status']=0;
			$is_err=1;
			$count_err++;
			continue;
		}
		if($is_err==0){
			$coupon_data = $GLOBALS['db']->getRow("select c.refund_status,c.begin_time,c.end_time, c.id as id,c.is_valid,c.deal_id,doi.name as name,doi.sub_name as sub_name,c.password as password,doi.number as number,c.sn as sn,c.supplier_id as supplier_id,c.confirm_time as confirm_time from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where c.password = '".$pwd."' and c.is_valid in(1,2) and c.is_delete = 0 ");
		}
		if($coupon_data)
		{
			if($coupon_data['confirm_time'] > 0  && $is_err ==0)
			{
				$result[$k]['msg'] = sprintf($GLOBALS['lang']['COUPON_INVALID_USED'],to_date($coupon_data['confirm_time']));
				$result[$k]['status']=0;
				$is_err=1;
			}
			if($coupon_data['is_valid'] == 2 && $is_err ==0){//改团购劵因为退款被锁定
				$result[$k]['msg'] = "该团购券已经失效无法验证";
				$result[$k]['status']=0;
				$is_err=1;
			}
				
			if($coupon_data['refund_status'] > 0 && $coupon_data['refund_status'] !=3){
				$result[$k]['msg'] = "团购券提交了退款申请，无法验证";
				$result[$k]['status']=0;
				$is_err=1;
			}
				
			if($coupon_data['begin_time']>0&&$coupon_data['begin_time']>get_gmtime()  && $is_err ==0){//未启用
				$result[$k]['msg'] = "团购券未生效";
				$result[$k]['status']=0;
				$is_err=1;
			}
			if($coupon_data['end_time']>0&&$coupon_data['end_time']<get_gmtime()  && $is_err ==0){//过期
				$result[$k]['msg'] = "团购券已过期";
				$result[$k]['status']=0;
				$is_err=1;
			}
			if (!in_array($location_id,$s_account_info['location_ids'])){
			    $result[$k]['msg'] = "没有门店权限验证该团购券";
			    $result[$k]['status']=0;
			    $is_err=1;
			}	
			$sql = "select d.* from ".DB_PREFIX."deal as d left join ".DB_PREFIX."deal_location_link as l on l.deal_id = d.id where l.deal_id = ".$coupon_data['deal_id']." and l.location_id =".$location_id;
			$deal_info = $GLOBALS['db']->getRow($sql);
			if(!$deal_info && $is_err ==0)
			{
				$result[$k]['msg'] = $GLOBALS['lang']['NO_AUTH'];
				$result[$k]['status']=0;
				$is_err=1;
			}
				
			if($coupon_data['supplier_id']!=$supplier_id  && $is_err ==0)
			{
				$result[$k]['msg'] = "该券为其他团购商户的团购券，不能确认";
				$result[$k]['status']=0;
				$is_err=1;
			}
			if($is_err==0){
				//开始确认
				$result[$k]['msg']= '验证成功,';
				$result[$k]['id'] = $coupon_data['id'];
				$result[$k]['name'] = $coupon_data['name'];
				$result[$k]['sub_name'] = $coupon_data['sub_name'];
				$result[$k]['password'] = $coupon_data['password'];
				$result[$k]['status']=1;
			}
		}
		else
		{
			$result[$k]['msg'] = '验证失败';//$GLOBALS['lang']['COUPON_INVALID'];
			$result[$k]['status']=0;
			$is_err=1;
		}


		$coupon_data['password'] = $coupon_data['password']?$coupon_data['password']:$pwd;
		$result[$k]['id'] = $coupon_data['id'];
		$result[$k]['name'] = $coupon_data['name'];
		$result[$k]['sub_name'] = $coupon_data['sub_name'];
		$result[$k]['password'] = $coupon_data['password'];


		//存放已经验证过的数据
		$check_data[] = $coupon_data['password'];
		//根据密码数据对应
		$result_data[$coupon_data['password']] = $result[$k];
		if($is_err)$count_err++;
	}
	if($count_err > 0) //如果有错误
		$data['is_err']=1;
	else
		$data['is_err']=0;

	if($count_err==0 && $result_data){ //如果都没有错误执行验证
		foreach($result as $k=>$v){
			//开始确认
			require_once  APP_ROOT_PATH."system/model/deal_order.php";
			$result[$k]['send_status'] = use_coupon($v['password'],$location_id,$s_account_info['id'],true,true);
			$result[$k]['msg'] = "使用成功，使用时间为：".to_date($now);
		}

	}
	
	$data['data'] = $result;

	return $data;
}	
	

/**
 * 团购券超级验证，返回可用条数
 * @param unknown_type $s_account_info
 * @param unknown_type $pwd
 * @param unknown_type $location_id
 * @return Ambigous <string, number, unknown, number>
 */
function biz_super_check_coupon($s_account_info,$pwd,$location_id){
	//判断密码是否有效
	$result = biz_check_coupon($s_account_info,$pwd,$location_id);
	$now = NOW_TIME;
	if($result["status"]==1){//有效数据
		$coupon_data = $result['coupon_data'];
		$supplier_id = intval($s_account_info['supplier_id']); //商户编号
		
		//查询该密码下所有同一订单 和 同一商品的团购券数量
		$result['count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon as c left join ".DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id where c.deal_id=".$coupon_data['deal_id']." and c.order_id=".$coupon_data['order_id']." and c.order_deal_id = ".$coupon_data['order_deal_id']." and c.is_valid = 1 and c.refund_status in(0,3) and c.is_delete = 0 and c.confirm_time='' and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>'".$now."')");
	}
	return $result;
	
}

/**
 * 团购全超级验证的使用， 使用全的数量= 当前输入的券+随机取出的券
 * @param unknown_type $s_account_info
 * @param unknown_type $location_id
 * @param unknown_type $pwd
 * @param unknown_type $coupon_use_count
 * @return boolean
 */
function biz_super_use_coupon($s_account_info,$location_id,$pwd,$coupon_use_count){
   
	$now = NOW_TIME;
	require_once  APP_ROOT_PATH."system/model/biz_verify.php";
	$s_account_info = es_session::get("account_info");
	
	//$location_id = intval($_REQUEST['location_id']);
	//$pwd = strim($_REQUEST['coupon_pwd']);
	$result = biz_super_check_coupon($s_account_info,$pwd,$location_id);
	if($result['count'] == 0){
		$data['status'] = 0;
		$data['msg'] = "没有可以使用的团购券";
		ajax_return($data);
	}
	if(!$coupon_use_count >=1){
	    $data['status'] = 0;
	    $data['msg'] = "至少使用1条";
	    ajax_return($data);
	}
	if($coupon_use_count>$result['count']){
		$data['status'] = 0;
		$data['msg'] = "超出使用条数";
		ajax_return($data);
	}
	
	$coupon_pwd_list = $GLOBALS['db']->getAll("select c.password as password from ".DB_PREFIX."deal_coupon as c  where c.deal_id=".$result['coupon_data']['deal_id']." and c.order_id=".$result['coupon_data']['order_id']." and c.order_deal_id = ".$result['coupon_data']['order_deal_id']."  and c.is_valid = 1 and c.refund_status in(0,3) and c.is_delete = 0 and c.confirm_time='' and c.begin_time <".$now." and (c.end_time = 0 or c.end_time>'".$now."')");
	$f_coupon_pwd_list = array();
	require_once  APP_ROOT_PATH."system/model/deal_order.php";
	foreach ($coupon_pwd_list as $k=>$v){
	    if ($pwd == $v['password']){
	       unset($coupon_pwd_list[$k]);   
	    }else
		$f_coupon_pwd_list[] = $v['password'];
	}
	array_unshift($f_coupon_pwd_list,$pwd);


    for ($i=0;$i<$coupon_use_count;$i++){
        $v = $f_coupon_pwd_list[$i];
        $temp['pwd'] =$v;
        $temp['send_status'] = use_coupon($v,$location_id,$s_account_info['id'],true,true);
        if($temp['send_status']){
            $use_count_success++;
        }
        $send_log[] = $temp;
    }
    

	//已经成功执行
	$data['status'] = 1;
	$data['send_data'] = $send_log;
	$data['location_id'] = $location_id;
	$data['coupon_pwd'] = $pwd;
	$data['coupon_use_count'] = $use_count_success;
    if($use_count_success>1){
        $data['msg'] = "一共使用".$use_count_success."张，使用成功 \n 使用时间为：".to_date($now);
    }elseif($use_count_success==1){
        $coupon_price = $GLOBALS['db']->getOne("select coupon_price from ".DB_PREFIX."deal_coupon as c  where c.deal_id=".$result['coupon_data']['deal_id']);
        $data['msg'] = $result['coupon_data']['sub_name']." \n使用成功 ! 消费：".round($coupon_price,2);
    }else
        $data['msg'] = "使用失败";
	return $data;
}
	

/**************************************************************
 * 				                            优惠券验证代码
 **************************************************************/
function biz_check_youhui($s_account_info,$sn,$location_id)
{
	if(intval($s_account_info['id'])==0)
	{
		$result['status'] = 0;
		$result['msg'] = $GLOBALS['lang']['SUPPLIER_NOT_LOGIN'];
		return $result;
	}

	$now = NOW_TIME;
	$youhui_log = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_log where youhui_sn = '".$sn."'");
	if($youhui_log)
	{
	    if (!in_array($location_id,$s_account_info['location_ids'])){
	        $result['status'] = 0;
			$result['msg'] = "没有门店权限验证该优惠券";
			return $result;
	    }
		$sql = "select y.* from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where l.youhui_id = ".$youhui_log['youhui_id']." and l.location_id =".$location_id;
		$youhui_info = $GLOBALS['db']->getRow($sql);
		if(!$youhui_info)
		{
			$result['status'] = 0;
			$result['msg'] = $GLOBALS['lang']['NO_AUTH'];
			return $result;
		}
		if($youhui_log['expire_time']>0 && $youhui_log['expire_time'] < $now){
		    $result['status'] = 0;
		    $result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_END'],to_date($youhui_log['expire_time']));
		    return $result;
		}
		if($youhui_log['confirm_id']>0&&$youhui_log['confirm_time']>0)
		{
			$result['status'] = 0;
			$result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_USED'],to_date($youhui_log['confirm_time']));
			return $result;
		}
		else
		{
			$youhui_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$youhui_log['youhui_id']);
			if($youhui_data)
			{
				if($youhui_data['begin_time']>0&&$youhui_data['begin_time']>$now)
				{
					$result['status'] = 0;
					$result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_NOT_BEGIN'],to_date($youhui_data['begin_time']));
					return $result;
				}
				elseif($youhui_data['end_time']>0&&$youhui_data['end_time']<$now)
				{
					$result['status'] = 0;
					$result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_END'],to_date($youhui_data['end_time']));
					return $result;
				}
				else
				{
					$result['status'] = 1;
					$youhui_log['youhui_data'] = $youhui_data;
					$result['data'] = $youhui_log;
					$result['location_id'] = $location_id;
					$result['youhui_sn'] = $sn;
					$result['msg'] = $youhui_data['name']."[".$GLOBALS['lang']['YOUHUI_SN'].":".$youhui_log['youhui_sn']."]".$GLOBALS['lang']['IS_VALID_YOUHUI'];
					if($youhui_log['order_count']>0)
						$result['msg'].="\n".$GLOBALS['lang']['YOUHUI_ORDER_COUNT'].":".$youhui_log['order_count'].$GLOBALS['lang']['ORDER_COUNT_PERSON'];
					if($youhui_log['is_private_room'])
						$result['msg'].="(".$GLOBALS['lang']['IS_PRIVATE_ROOM'].")";
					if($youhui_log['date_time']>0)
						$result['msg'].="\n".$GLOBALS['lang']['ORDER_DATE_TIME'].":".to_date($youhui_log['date_time'],"Y-m-d H:i");
					$result['msg'].="\n".$GLOBALS['lang']['CONFIRM_USE_YOUHUI'];
				}
			}
			else
			{
				$result['status'] = 0;
				$result['msg'] = $GLOBALS['lang']['YOUHUI_INVALID'];
			}
		}
	}
	else
	{
		$result['status'] = 0;
		$result['msg'] = $GLOBALS['lang']['YOUHUI_SN_INVALID'];
	}
	return $result;
}

function biz_use_youhui($s_account_info,$sn,$location_id)
{
	if(intval($s_account_info['id'])==0)
	{
		$result['status'] = 0;
		$result['msg'] = $GLOBALS['lang']['SUPPLIER_LOGIN_FIRST'];
	}
	else
	{
		$now = NOW_TIME;
			
		$youhui_log = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui_log where youhui_sn = '".$sn."'");
		if($youhui_log)
		{
			$sql = "select y.* from ".DB_PREFIX."youhui as y left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = y.id where l.youhui_id = ".$youhui_log['youhui_id']." and l.location_id =".$location_id;
			$youhui_info = $GLOBALS['db']->getRow($sql);
			if(!$youhui_info)
			{
				$result['status'] = 0;
				$result['msg'] = $GLOBALS['lang']['NO_AUTH'];
				//ajax_return($result);
				return $result;
			}
			if($youhui_log['confirm_id']>0&&$youhui_log['confirm_time']>0)
			{
				$result['status'] = 0;
				$result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_USED'],to_date($youhui_log['confirm_time']));
			}
			else
			{
				$youhui_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$youhui_log['youhui_id']);
				if($youhui_data)
				{
					if($youhui_data['begin_time']>0&&$youhui_data['begin_time']>$now)
					{
						$result['status'] = 0;
						$result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_NOT_BEGIN'],to_date($youhui_data['begin_time']));
					}
					elseif($youhui_data['end_time']>0&&$youhui_data['end_time']<$now)
					{
						$result['status'] = 0;
						$result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_END'],to_date($youhui_data['end_time']));
					}
					else
					{
					    
						$youhui_log['confirm_id'] = $s_account_info['id'];
						$youhui_log['confirm_time'] = $now;
						require_once APP_ROOT_PATH.'system/model/deal_order.php';
						$youhui_log['send_status'] = use_youhui($sn,$location_id,$s_account_info['id'],true,true);
						$result['status'] = 1;
						$result['location_id'] = $location_id;
						$result['youhui_sn'] = $sn;
						$result['msg'] = $youhui_info['name']."使用成功。\n 使用时间为：".to_date($now);
						$youhui_log['youhui_data'] = $youhui_data;
						$result['data'] = $youhui_log;
					}
				}
				else
				{
					$result['status'] = 0;
					$result['msg'] = $GLOBALS['lang']['YOUHUI_INVALID'];
				}
			}
		}
		else
		{
			$result['status'] = 0;
			$result['msg'] = $GLOBALS['lang']['YOUHUI_SN_INVALID'];
		}
	}
	return $result;
}
	
/*********************************************************
 *                  活动报名验证
 ********************************************************/

/**
 * 活动验证
 * @param unknown $s_account_info
 * @param unknown $sn
 * @param unknown $location_id
 * @return string|unknown
 */
function biz_check_event($s_account_info,$sn,$location_id){
    $supplier_id = intval($s_account_info['supplier_id']);
    if($s_account_info['id']<=0){
        $result['status'] = 0;
        $result['msg'] = $GLOBALS['lang']['SUPPLIER_LOGIN_FIRST'];
    }else{
        //查询是否存在报名
        $event_submit = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."event_submit WHERE sn = '".$sn."' and (event_begin_time<=".NOW_TIME." or event_begin_time=0) and (event_end_time>".NOW_TIME." or event_end_time=0)");
        if(empty($event_submit)){
            $result['status'] = 0;
            $result['msg'] = "不存在活动报名信息或已经过期";
            return $result;
        }else{
            //是否审核
            if ($event_submit['is_verify'] == 0){
                $result['status'] = 0;
                $result['msg'] = "活动报名信息未审核";
                return $result;
            }
            if($event_submit['confirm_id']>0){
                $result['status'] = 0;
                $result['msg'] = "已经验证使用过";
                return $result;
            }
        }
        if (!in_array($location_id,$s_account_info['location_ids'])){
            $result['status'] = 0;
            $result['msg'] =  "活动不支持该门店验证";
            return $result;
        }
        //查询报名的活动信息
        $sql = "SELECT e.* FROM ".DB_PREFIX."event e left join ".DB_PREFIX."event_location_link el on el.event_id = e.id where id = ".$event_submit['event_id']." and el.location_id = ".$location_id;
        $event = $GLOBALS['db']->getRow($sql);
        if(empty($event)){//门店关联查询，门店是否支持验证
            $result['status'] = 0;
            $result['msg'] = "活动不支持该门店验证";
            return $result;
        }
        if($event['supplier_id'] != $supplier_id){//是否为该商户的活动
            $result['status'] = 0;
            $result['msg'] = "活动不是该商户的";
            return $result;
        }
        
        $result['status'] = 1;
        $result['location_id'] = $location_id;
        $result['event_sn'] = $sn;
        $result['msg'] = $event['name'];
        $result['event_data'] = $event;
        if ($event['event_end_time']){
            $result['msg'].= "，该活动的结束时间为:".to_date($event['event_end_time']);
        }
        $result['event_submit'] = $event_submit;
    }
    return $result;
    
}
/**
 * 活动使用
 * @param unknown $s_account_info
 * @param unknown $sn
 * @param unknown $location_id
 * @return unknown
 */
function biz_use_event($s_account_info,$sn,$location_id){
    $supplier_id = intval($s_account_info['supplier_id']);
    $result = biz_check_event($s_account_info,$sn,$location_id);
    $event_submit = $result['event_submit'];
    if($result['status']){
        require_once APP_ROOT_PATH.'system/model/deal_order.php';
        $result['send_status'] = use_event($sn,$location_id,$s_account_info['id'],true,true);
        $result['status'] = 1;
        $result['location_id'] = $location_id;
        $result['event_sn'] = $sn;
        $result['msg'] = $result['event_data']['name']."使用成功。\n 使用时间为：".to_date(NOW_TIME);
    }
    return $result;
}
?>