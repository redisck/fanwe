<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

define("EVENT_OUT_OF_STOCK",4); //库存不足

define("EVENT_NOTICE",0); //未上线
define("EVENT_ONLINE",1); //进行中
define("EVENT_HISTORY",2); //过期




//获取活动详情
function get_event($id,$preview=false)
{
	static $events;
	$event = $events[$id];
	if($event)return $event;
	
	$event = load_auto_cache("event",array("id"=>$id));	
	
	if($event)
	{
		if(!$preview&&$event['is_effect']==0) 
			return false;
		
		//商户信息
		if($event['supplier_id']>0)
		{
			$event['supplier_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".intval($event['supplier_id']));
			$event['supplier_location_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event_location_link where event_id = ".$event['id']);
			//$deal['supplier_address_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".intval($deal['supplier_id'])." and is_main = 1");
		}
		$event['submit_begin_time_format'] = to_date($event['submit_begin_time'],"Y-m-d");
		$event['submit_end_time_format'] = to_date($event['submit_end_time'],"Y-m-d");
		$event['event_begin_time_format'] = to_date($event['event_begin_time'],"Y-m-d");
		$event['event_end_time_format'] = to_date($event['event_end_time'],"Y-m-d");
		
		
		$durl = $event['url'];
			
		$event['share_url'] = SITE_DOMAIN.$durl;
		if($GLOBALS['user_info'])
		{
			if(app_conf("URL_MODEL")==0)
			{
				$event['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
			}
			else
			{
				$event['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
			}
		}
		
		$events[$id] = $event;	
	}
	return $event;
}


function get_event_count($type=array(EVENT_NOTICE,EVENT_ONLINE,EVENT_HISTORY),$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0), $join='', $where='')
{
	if(empty($param))
		$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0);

	$tname = "e";
	$time = $GLOBALS['db']->getCacheTime(NOW_TIME);
	$condition = ' '.$tname.'.is_effect = 1 and  ( 1<>1 ';
	if(in_array(EVENT_ONLINE,$type))
	{
		//进行中的
		$condition .= " or ((".$time.">= ".$tname.".submit_begin_time or ".$tname.".submit_begin_time = 0) and (".$time."< ".$tname.".submit_end_time or ".$tname.".submit_end_time = 0) ) ";
	}

	if(in_array(EVENT_HISTORY,$type))
	{
		//往期团购
		$condition .= " or ((".$time.">=".$tname.".submit_end_time and ".$tname.".submit_end_time <> 0)) ";
	}
	if(in_array(EVENT_NOTICE,$type))
	{
		//预告
		$condition .= " or ((".$time." < ".$tname.".submit_begin_time and ".$tname.".submit_begin_time <> 0 )) ";
	}

	$condition .= ')';


	$param_condition = build_event_filter_condition($param,$tname);
	$condition.=" ".$param_condition;

	if($where != '')
	{
		$condition.=" and ".$where;
	}

	if($join)
		$sql = "select count(*) from ".DB_PREFIX."event as ".$tname." ".$join." where  ".$condition;
	else
		$sql = "select count(*) from ".DB_PREFIX."event as ".$tname." where  ".$condition;



	$count = $GLOBALS['db']->getOne($sql,false);
	return $count;
}
/**
 * 获取活动列表
 */
function get_event_list($limit,$type=array(EVENT_NOTICE,EVENT_ONLINE,EVENT_HISTORY),$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0), $join='', $where='',$orderby = '',$field_append="")
{
	if(empty($param))
		$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0);

	$tname = "e";
	$time = $GLOBALS['db']->getCacheTime(NOW_TIME);
	$condition = ' '.$tname.'.is_effect = 1 and  ( 1<>1 ';
	if(in_array(EVENT_ONLINE,$type))
	{
		//进行中的
		$condition .= " or ((".$time.">= ".$tname.".submit_begin_time or ".$tname.".submit_begin_time = 0) and (".$time."< ".$tname.".submit_end_time or ".$tname.".submit_end_time = 0) ) ";
	}

	if(in_array(EVENT_HISTORY,$type))
	{
		//往期团购
		$condition .= " or ((".$time.">=".$tname.".event_end_time and ".$tname.".event_end_time <> 0)) ";
	}
	if(in_array(EVENT_NOTICE,$type))
	{
		//预告
		$condition .= " or ((".$time." < ".$tname.".submit_begin_time and ".$tname.".submit_begin_time <> 0 )) ";
	}

	$condition .= ')';


	$param_condition = build_event_filter_condition($param,$tname);
	$condition.=" ".$param_condition;

	if($where != '')
	{
		$condition.=" and ".$where;
	}

	if($join)
		$sql = "select ".$tname.".*".$field_append." from ".DB_PREFIX."event as ".$tname." ".$join." where  ".$condition;
	else
		$sql = "select ".$tname.".*".$field_append." from ".DB_PREFIX."event as ".$tname." where  ".$condition;

	if($orderby=='')
		$sql.=" order by ".$tname.".sort desc limit ".$limit;
	else
		$sql.=" order by ".$orderby." limit ".$limit;


	$events = $GLOBALS['db']->getAll($sql,false);
	//		echo $count_sql;
	if($events)
	{
		foreach($events as $k=>$event)
		{
			//格式化数据
			$event['submit_begin_time_format'] = to_date($event['submit_begin_time'],"Y-m-d");
			$event['submit_end_time_format'] = to_date($event['submit_end_time'],"Y-m-d");
			$event['event_begin_time_format'] = to_date($event['event_begin_time'],"Y-m-d");
			$event['event_end_time_format'] = to_date($event['event_end_time'],"Y-m-d");

			$durl = url("index","event#".$event['id']);
			$event['share_url'] = SITE_DOMAIN.$durl;
			$event['url'] = $durl;


			if($GLOBALS['user_info'])
			{
				if(app_conf("URL_MODEL")==0)
				{
					$event['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
				else
				{
					$event['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
			}				


			$events[$k] = $event;
		}
		}
		return array('list'=>$events,'condition'=>$condition);
}


/**
 * 构建活动查询条件
 * @param unknown_type $param
 * @return string
 */
function build_event_filter_condition($param,$tname="")
{
	$area_id = intval($param['aid']);
	$quan_id = intval($param['qid']);
	$cate_id = intval($param['cid']);
	$city_id = intval($param['city_id']);
	$condition = "";
	if($city_id>0)
	{
		$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
		if($ids)
		{
			if($tname)
				$condition .= " and ".$tname.".city_id in (".implode(",",$ids).")";
			else
				$condition .= " and city_id in (".implode(",",$ids).")";
		}
	}
	if($area_id>0)
	{
		if($quan_id>0)
		{

			$area_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."area where id = ".$quan_id);
			$kw_unicodes[] = str_to_unicode_string($area_name);
				
			$kw_unicode = implode(" ",$kw_unicodes);
			//有筛选
			if($tname)
				$condition .=" and (match(".$tname.".locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";
			else
				$condition .=" and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";
		}
		else
		{
			$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$area_id));
			$quan_list = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
			$unicode_quans = array();
			foreach($quan_list as $k=>$v){
				$unicode_quans[] = str_to_unicode_string($v['name']);
			}
			$kw_unicode = implode(" ", $unicode_quans);
			if($tname)
				$condition .= " and (match(".$tname.".locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
			else
				$condition .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
		}
	}

	if($cate_id>0)
	{
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".$cate_id);
		$cate_name_unicode = str_to_unicode_string($cate_name);
		if($tname)
				$condition .= " and (match(".$tname.".cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";
			else
				$condition .= " and (match(cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";
	}
	return $condition;
}

/**
 * 审核活动报名：发序列号，以及相关短信邮件
 * @param unknown_type $submit_id
 */
function verify_event_submit($submit_id)
{
	$submit_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where id = ".$submit_id);
	if($submit_data['is_verify']==0)
	{
		do{
			$sn = rand(100,999).$submit_data['event_id'].rand(10,99);
			$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set sn = '".$sn."',is_verify = 1 where id = ".$submit_id);
		}while($GLOBALS['db']->affected_rows()==0);	
		
		send_event_sn_mail($submit_id);
		send_event_sn_sms($submit_id);
	}
	else
	{
		$sn = $submit_data['sn'];
	}
	return $sn;
}
/**
 * 拒绝审核
 * @param unknown_type $submit_id
 */
function refuse_event_submit($submit_id)
{
	$submit_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where id = ".$submit_id);
	if($submit_data)
	{
		$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set sn = null,is_verify = 2 where id = ".$submit_id);
		if($GLOBALS['db']->affected_rows())
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."event set submit_count = submit_count-1 where id=".$submit_data['event_id']." and submit_count>0");		
			rm_auto_cache("event",array("id"=>$submit_data['event_id']));
			return true;
		}		
	}
	return false;
}

/**
 * 活动自动发布
 * @param int $id 商户提交数据ID
 */
function event_auto_publish($id){
    $submit_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_biz_submit where id = ".$id);
    if ($submit_data){
        $result = format_event_submit($submit_data);

        $event_id = $result['event_id'];
        $event_submit_id = $result['event_submit_id'];
         
        $data = array();
        $other_data = array();
         
        $data = $result['data'];
        $other_data = $result['other_data'];
    
        if($result['act_type']){ //更新操作
            $data['id'] = $result['event_id'];
            $GLOBALS['db']->autoExecute(DB_PREFIX."event",$data,'UPDATE'," id=".$result['event_id']);
            $list = $GLOBALS['db']->affected_rows();
        }else{//新增操作
            $GLOBALS['db']->autoExecute(DB_PREFIX."event",$data);
            $list = $GLOBALS['db']->insert_id();
            $data['id'] = $list;
        }
         
        if (false !== $list) {
                  
            //地区列表
            $GLOBALS['db']->query("delete from ".DB_PREFIX."event_area_link where event_id=".$data['id']);
            foreach($other_data['cache_event_area_link'] as $v)
            {
                $ins_data = array();
                $ins_data['event_id'] = $data['id'];
                $ins_data['area_id'] = $v;
                $GLOBALS['db']->autoExecute(DB_PREFIX."event_area_link",$ins_data);
            }
            
            //门店
            $GLOBALS['db']->query("delete from ".DB_PREFIX."event_location_link where event_id=".$data['id']);
            foreach($other_data['cache_event_location_link'] as $v)
            {
                $ins_data = array();
                $ins_data['event_id'] = $data['id'];
                $ins_data['location_id'] = $v;
                $GLOBALS['db']->autoExecute(DB_PREFIX."event_location_link",$ins_data);
            }
            
            
            
            //报名项配置
            $submit_ids = array(0);
			foreach($other_data['cache_event_field'] as $k=>$v)
			{
				$submit_ids[] = intval($v['id']);
				$event_field = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_field where id=".$v['id']);
				if($event_field)
				{
					$event_field['event_id'] = $data['id'];
					$event_field['field_show_name'] = $v['field_show_name'];
					$event_field['field_type'] = $v['field_type'];
					$event_field['value_scope'] = $v['value_scope'];
					$event_field['sort'] = $k;
					$GLOBALS['db']->autoExecute(DB_PREFIX."event_field",$event_field,"UPDATE","where id=".$v['id']);
				}
				else
				{		
					$event_field = array();		
					$event_field['event_id'] = $data['id'];
					$event_field['field_show_name'] = $v['field_show_name'];
					$event_field['field_type'] = $v['field_type'];
					$event_field['value_scope'] = $v['value_scope'];
					$event_field['sort'] = $k;
					$GLOBALS['db']->autoExecute(DB_PREFIX."event_field",$event_field);
					$submit_ids[] = $GLOBALS['db']->insert_id();
				}
			}
			
			$GLOBALS['db']->query("delete from ".DB_PREFIX."event_field where event_id=".$data['id']." and id not in (".implode(',', $submit_ids).")");
			$GLOBALS['db']->query("delete from ".DB_PREFIX."event_submit_field where event_id=".$data['id']." and field_id not in (".implode(',', $submit_ids).")");

                       
            $up_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."event where cate_id=".$data['cate_id']);
            $GLOBALS['db']->query("update ".DB_PREFIX."event_cate set count=".$up_count." where id=".$data['cate_id']);
            
            //成功提示
            syn_event_match($data['id']);
            //对于商户请求操作
            $GLOBALS['db']->autoExecute(DB_PREFIX."event_biz_submit",array("admin_check_status"=>1),"UPDATE","id=".$event_submit_id); // 1 通过 2 拒绝',
        }
    }
}

function format_event_submit($data){
    $temp_data = array();
    $temp_data['name'] = $data['name'];
    $temp_data['icon'] = $data['icon'];
    $temp_data['event_begin_time'] = $data['event_begin_time'];
    $temp_data['event_end_time'] = $data['event_end_time'];
    $temp_data['submit_begin_time'] = $data['submit_begin_time'];
    $temp_data['submit_end_time'] = $data['submit_end_time'];
    $temp_data['content'] = $data['content'];
    $temp_data['cate_id'] = $data['cate_id'];
    $temp_data['city_id'] = $data['city_id'];
    $temp_data['address'] = $data['address'];
    $temp_data['xpoint'] = $data['xpoint'];
    $temp_data['ypoint'] = $data['ypoint'];
    $temp_data['submit_count'] = $data['submit_count'];
    $temp_data['brief'] = $data['brief'];
    $temp_data['sort'] = $data['sort'];
    $temp_data['supplier_id'] = $data['supplier_id'];
    $temp_data['publish_wait'] = $data['publish_wait'];
    $temp_data['return_score'] = $data['return_score'];
    $temp_data['return_point'] = $data['return_point'];
    $temp_data['return_money'] = $data['return_money'];
    $temp_data['score_limit'] = $data['score_limit'];
    $temp_data['point_limit'] = $data['point_limit'];
    $temp_data['is_effect'] = 1;
    
    $temp_other = array();
   
    //地区列表
    $temp_other['cache_event_area_link'] = unserialize($data['cache_event_area_link']);
    //门店
    $temp_other['cache_event_location_link'] = unserialize($data['cache_event_location_link']);
    //报名项配置
    $temp_other['cache_event_field'] = unserialize($data['cache_event_field']);
     
    
    $act_type = 0; //0:新增，1更新

    if($data['event_id']>0){
        $act_type = 1;
    }

    $result_data = array("data"=>$temp_data,"other_data"=>$temp_other,"act_type"=>$act_type,"event_submit_id"=>$data['id'],"event_id"=>$data['event_id']);

    return $result_data;

}

function event_auto_downline($id){
    $deal_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_biz_submit where id = ".$id);
    if($deal_submit_info && $deal_submit_info['biz_apply_status']==3){
        //更新商户表状态为拒绝
        $GLOBALS['db']->autoExecute(DB_PREFIX."event_biz_submit",array("admin_check_status"=>1),"UPDATE","id=".$id);
        //更新团购数据表
        $GLOBALS['db']->autoExecute(DB_PREFIX."event",array("is_effect"=>0),"UPDATE","id=".$deal_submit_info['event_id']);
        return true;
    }else{
        return false;
    }
}

?>