<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

//查询会员邀请及返利列表
function get_invite_list($limit,$user_id)
{
	$user_id = intval($user_id);
	$sql = "select u.user_name as i_user_name,u.referral_count as i_referral_count,u.create_time as i_reg_time,o.order_sn as i_order_sn,r.create_time as i_referral_time, r.pay_time as i_pay_time,r.money as i_money,r.score as i_score from ".DB_PREFIX."user as u left join ".DB_PREFIX."referrals as r on u.id = r.rel_user_id and u.pid = r.user_id left join ".DB_PREFIX."deal_order as o on r.order_id = o.id where u.pid = ".$user_id." limit ".$limit;
	$sql_count = "select count(*) from ".DB_PREFIX."user where pid = ".$user_id;
	$list = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sql_count);
	return array("list"=>$list,'count'=>$count);
}

function get_collect_list($limit,$user_id)
{
	$user_id = intval($user_id);
	$sql = "select d.id,d.name,d.sub_name,d.origin_price,d.current_price,d.buy_count,d.brief,d.icon,c.create_time as add_time ,c.id as cid from ".DB_PREFIX."deal_collect as c left join ".DB_PREFIX."deal as d on d.id = c.deal_id where c.user_id = ".$user_id." order by c.create_time desc limit ".$limit;
	$sql_count = "select count(*) from ".DB_PREFIX."deal_collect where user_id = ".$user_id;
	$list = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sql_count);
	return array("list"=>$list,'count'=>$count);
}

function get_youhui_collect($limit,$user_id)
{
	$user_id = intval($user_id);
	$sql = "select y.id,y.name,y.icon,y.user_count,y.list_brief,y.begin_time,y.end_time,c.uid,c.add_time,c.id as cid  from ".DB_PREFIX."youhui_sc as c left join ".DB_PREFIX."youhui as y on y.id = c.youhui_id where c.uid = ".$user_id." order by y.end_time desc limit ".$limit;
	$sql_count = "select count(*) from ".DB_PREFIX."youhui_sc where uid = ".$user_id;
	$list = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sql_count);
	return array("list"=>$list,'count'=>$count);
}

function get_event_collect($limit,$user_id)
{
	$user_id = intval($user_id);
	$sql = "select e.id,e.name,e.icon,e.brief,e.submit_count,e.submit_end_time,c.uid,c.add_time,c.id as cid  from ".DB_PREFIX."event_sc as c left join ".DB_PREFIX."event as e on e.id = c.event_id where c.uid = ".$user_id." order by e.event_end_time desc limit ".$limit;
	$sql_count = "select count(*) from ".DB_PREFIX."event_sc where uid = ".$user_id;
	$list = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sql_count);
	return array("list"=>$list,'count'=>$count);
}

//查询代金券列表
function get_voucher_list($limit,$user_id)
{
	$user_id = intval($user_id);
	$sql = "select * from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.user_id = ".$user_id." order by e.id desc limit ".$limit;
	$sql_count = "select count(*) from ".DB_PREFIX."ecv where user_id = ".$user_id;
	
	$list = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sql_count);
	return array("list"=>$list,'count'=>$count);
}

//查询可兑换代金券列表
function get_exchange_voucher_list($limit)
{
	$sql = "select * from ".DB_PREFIX."ecv_type where send_type = 1 order by id desc limit ".$limit;
	$sql_count = "select count(*) from ".DB_PREFIX."ecv_type where send_type = 1";
	
	$list = $GLOBALS['db']->getAll($sql);
	$count = $GLOBALS['db']->getOne($sql_count);
	return array("list"=>$list,'count'=>$count);
}

?>