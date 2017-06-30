<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



//获取门店详情
function get_location($id,$preview=false)
{
	static $stores;
	$store = $stores[$id];
	if($store)return $store;

	$store = load_auto_cache("store",array("id"=>$id));

	if($store)
	{
// 		if($store['deal_cate_id']==0)
// 			return false;
		
		if(!$preview&&$store['is_effect']==0)
			return false;
		
		$durl = $store['url'];
			
		$store['share_url'] = SITE_DOMAIN.$durl;
		if($GLOBALS['user_info'])
		{
			if(app_conf("URL_MODEL")==0)
			{
				$store['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
			}
			else
			{
				$store['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
			}
		}
		
		//商户信息
		if($store['supplier_id']>0)
		{
			$store['supplier_info'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".intval($store['supplier_id']));
			$store['supplier_location_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location where supplier_id = ".$store['supplier_id']);
		}

		$stores[$id] = $store;
	}
	return $store;
}

/**
 * 获取门店列表
 */
function get_location_list($limit, $param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0,"supplier_id"=>0), $join = '', $where='',$orderby = '',$field_append="")
{
	if(empty($param))
		$param=array("cid"=>0,"tid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0,"supplier_id"=>0);	

	$tname = "sl";
	
	$condition = ' '.$tname.'.is_effect = 1 and '.$tname.'.deal_cate_id > 0 ';	
	
	$param_condition = build_location_filter_condition($param,$tname);
	$condition.=" ".$param_condition;
	
	if($where != '')
	{
		$condition.=" and ".$where;
	}
	
	if($join)
		$sql = "select ".$tname.".*".$field_append." from ".DB_PREFIX."supplier_location as ".$tname." use index (search_idx1, is_verify) ".$join." where  ".$condition;
	else
		$sql = "select ".$tname.".*".$field_append." from ".DB_PREFIX."supplier_location as ".$tname." use index (search_idx1, is_verify) where  ".$condition;
	
	if($orderby=='')
		$sql.=" order by ".$tname.".sort desc limit ".$limit;
	else
		$sql.=" order by ".$orderby." limit ".$limit;
	
	$location_list = $GLOBALS['db']->getAll($sql);
	
	if($location_list)
	{
		foreach($location_list as $k=>$store)
		{
	
			$durl = url("index","store#".$store['id']);
			$store['share_url'] = SITE_DOMAIN.$durl;
			$store['url'] = $durl;
			$store['good_rate_precent'] = round($store['good_rate']*100,1);
			$store['ref_avg_price'] = round($store['ref_avg_price'],2);
	
			if($GLOBALS['user_info'])
			{
				if(app_conf("URL_MODEL")==0)
				{
					$store['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
				else
				{
					$store['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
				}
			}
	
			$location_list[$k] = $store;
		}
	}
	
	return array('list'=>$location_list,'condition'=>$condition);
}

/**
 * 构建商家查询条件
 * @param unknown_type $param
 * @return string
 */
function build_location_filter_condition($param,$tname="")
{
	$area_id = intval($param['aid']);
	$quan_id = intval($param['qid']);
	$cate_id = intval($param['cid']);
	$deal_type_id = intval($param['tid']);
	$city_id = intval($param['city_id']);
	$supplier_id = intval($param['supplier_id']);
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
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate where id = ".$cate_id);
		$cate_name_unicode = str_to_unicode_string($cate_name);
			
		if($deal_type_id>0)
		{
			$deal_type_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."deal_cate_type where id = ".$deal_type_id);
			$deal_type_name_unicode = str_to_unicode_string($deal_type_name);
			if($tname)
				$condition .= " and (match(".$tname.".deal_cate_match) against('+".$cate_name_unicode." +".$deal_type_name_unicode."' IN BOOLEAN MODE)) ";
			else
				$condition .= " and (match(deal_cate_match) against('+".$cate_name_unicode." +".$deal_type_name_unicode."' IN BOOLEAN MODE)) ";
		}
		else
		{
			if($tname)
				$condition .= " and (match(".$tname.".deal_cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";
			else
				$condition .= " and (match(deal_cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";
		}
	}
	
	if($supplier_id>0)
	{
		if($tname)
			$condition .= " and ".$tname.".supplier_id = ".$supplier_id;
		else
			$condition .= " and supplier_id = ".$supplier_id;
	}
	return $condition;
}

/**
 * 变更商家账户资金
 * @param unknown_type $money
 * @param unknown_type $supplier_id 商家ID
 * @param unknown_type $type 0:销售额增加 1:资金冻结 2.待结算增加 3.已结算增加 4.退款增加 5.提现增加
 * 修改：放弃3类型，所有2类型表示已结算，即变为可提现金额，字段功能不变，传参时，2,3都变为3
 * @param unknown_type $info 日志内容
 *`sale_money` '销售总额',
 *`lock_money` '冻结资金(即已销售，未验证，未收货的金额)',
 *`balance_money` '待结算金额（即每验证，收货一个，增加此金额，同时扣除冻结金额）,
 *`money` '商户余额(可提现余额,已结算金额，结算后，待结算减少，已结算增加)',
 *`refund_money` '已退款金额（退款后增加此金额，同时减少lock_money冻结金额）,
 *`wd_money` '已提现金额：（已提走的金额,提现成功后，增加，同时减少money）';
 */
function modify_supplier_account($money,$supplier_id,$type,$info)
{
	if($type>=0&&$type<6)
	{	
		if($type==2)$type=3; //直接结算
		
		$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = '".$supplier_id."'");
		if($supplier_info)
		{
			$field_array = array('sale_money','lock_money','balance_money','money','refund_money','wd_money');
			$GLOBALS['db']->query("update ".DB_PREFIX."supplier set ".$field_array[$type]." = ".$field_array[$type]." + ".floatval($money)." where id =".$supplier_id);
			
			$date = to_date(NOW_TIME,"Y-m-d");
			$date_month = to_date(NOW_TIME,"Y-m");
			

			//商家日报只记录 销售额，消费额，退款，提现(money为消费，与财务总报不一样，提现不减少)
			if($type==0||($type==3&&$money>0)||$type==4||$type==5)
			{
				$supplier_stat = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_statements where supplier_id = ".$supplier_id." and stat_time = '".$date."'");				
				if($supplier_stat)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."supplier_statements set ".$field_array[$type]." = ".$field_array[$type]." + ".floatval($money)." where supplier_id =".$supplier_id." and stat_time = '".$date."'");
				}
				else
				{
					$supplier_stat = array();
					$supplier_stat[$field_array[$type]] = floatval($money);
					$supplier_stat['stat_time'] = $date;
					$supplier_stat['stat_month'] = $date_month;
					$supplier_stat['supplier_id'] = $supplier_id;				
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_statements",$supplier_stat);				
				}
			}
			
			$log_data = array();
			$log_data['log_info'] = $info;
			$log_data['supplier_id'] = $supplier_id;
			$log_data['create_time'] = NOW_TIME;
			$log_data['money'] = floatval($money);
			$log_data['type'] = $type;
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_money_log",$log_data);
			
			if($type==3&&$money>0)
			{
				//当商家余额增加时，即表示结算
				modify_statements($money, 10, $info);
			}			
						
		}
	}
}
?>