<?php

// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



/**
 * 获取餐厅列表
 * @param unknown_type $limit  限制查询条数
 * @param unknown_type $param  地区商圈查询条件
 * @param unknown_type $tag	         促销规则查询条件
 * @param unknown_type $join   联表查询条件
 * @param unknown_type $where  额外查询条件
 * @param unknown_type $orderby 排列条件
 * @param unknown_type $append_field 额外查询内容
 * @return array('list'=>$dc_location_list_new,'condition'=>$condition2,'id_arr'=>$id_arr); 返回查询到的门店信息，包括1.门店列表，2.不带分页limit的查询语句，用于查询门店总数，3，门店距离信息，二维数组
 */
function get_dc_location_list($type='is_dc',$limit='',$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0),$tag=array(), $where='',$orderby = '',$append_field='')
{

	if(empty($param)){
		$param=array("cid"=>0,"aid"=>0,"qid"=>0,"city_id"=>0);
	}
	$time=date('H',time())*3600+date('i',time())*60;
	$tname='sl';
	
	$field=array('id','name','address','xpoint','ypoint','supplier_id','open_time','city_id','locate_match',
			'name_match','preview','tags_match','avg_point','total_point','dp_count','is_dc','dc_cate_match',
			'is_reserve','dc_online_pay','is_close','open_time_cfg_str','dc_allow_cod','max_delivery_scale',
			'dc_location_notice','dc_buy_count','dc_allow_invoice','is_payonlinediscount','dc_allow_ecv',
			'is_firstorderdiscount','dc_ptag');
	foreach($field as $k=>$v){
		$field[$k]=$tname.".".$v;
	}
	
	$field_str=implode(',',$field);
	
	if($type=='is_dc'){
		
		$condition =$tname.'.is_dc=1';
		$param_condition = build_dc_filter_condition($param,$tname);
		
		$condition.=' '.$param_condition;
		if($where != '')
		{
			$condition.=$where;
		}

		 $sql = "select aa.* , max(aa.in_opentime) as in_opentime from (select ".$field_str.$append_field.", case when b.begin_time_h is null then 1 when (b.begin_time_h*3600 + b.begin_time_m*60) < ".$time." and (b.end_time_h*3600 + b.end_time_m*60)  >".$time." then 1 else 0 end as in_opentime from ".DB_PREFIX."supplier_location as ".$tname." left join ".DB_PREFIX."dc_supplier_location_open_time as b on ".$tname.".id=b.location_id where  ".$condition." ) aa where aa.distance <= aa.max_delivery_scale or aa.max_delivery_scale=0 group by aa.id";
		
	
		
	}elseif($type=='is_res'){
		
		$condition =$tname.'.is_reserve=1';
		$param_condition = build_dc_filter_condition($param,$tname);
		
		$condition.=' '.$param_condition;
		if($where != '')
		{
			$condition.=$where;
		}
		$sql = "select aa.* , max(aa.in_opentime) as in_opentime from (select ".$field_str.$append_field.", case when b.begin_time_h is null then 1 when (b.begin_time_h*3600 + b.begin_time_m*60) < ".$time." and (b.end_time_h*3600 + b.end_time_m*60)  >".$time." then 1 else 0 end as in_opentime from ".DB_PREFIX."supplier_location as ".$tname." left join ".DB_PREFIX."dc_supplier_location_open_time as b on ".$tname.".id=b.location_id where  ".$condition." ) aa group by aa.id";
		
		
	}

	$condition2=$sql;
	if($orderby==''){
		$sql.=" order by aa.is_close , in_opentime desc, aa.id desc";
	}else{
		$sql.=" order by aa.is_close , in_opentime desc ,aa.".$orderby;		
	}
	if($limit!=''){
		$sql.=" limit ".$limit;	
	}

	$dc_location_list=$GLOBALS['db']->getAll($sql,false);
	
	$dc_location_list_new=array();
	$id_arr=array();
	foreach($dc_location_list as $k=>$v){
		
		$dc_location_list_new[$v['id']]=$v;
		$id_arr[$v['id']]['id']=$v['id'];
		if($type=='is_dc'){
		$id_arr[$v['id']]['distance']=$v['distance'];
		}
	}
	
	return array('list'=>$dc_location_list_new,'condition'=>$condition2,'id_arr'=>$id_arr);
}


/**
 * 构建地区商圈查询条件
 * @param unknown_type $param 地区商圈数组
 * @return string 返回地区商圈查询语句
 */
function build_dc_filter_condition($param,$tname="")
{

	$area_id = intval($param['aid']);
	$quan_id = intval($param['qid']);
	$cate_id = intval($param['cid']);
	$city_id = intval($param['city_id']);
	$condition = "";
	if($city_id>0)
	{
		
		$city_pid=$GLOBALS['db']->getOne("select pid from ".DB_PREFIX."deal_city where id=".$city_id);
		
		if($city_pid)
		{
			if($tname)
				$condition .= " and ".$tname.".city_id in (".$city_id.",".$city_pid.")";
			else
				$condition .= " and city_id in (".$city_id.",".$city_pid.")";
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
			require_once APP_ROOT_PATH."app/Lib/common.php";
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
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."dc_cate where id = ".$cate_id);
		$cate_name_unicode = str_to_unicode_string($cate_name);

			if($tname)
				$condition .= " and (match(".$tname.".dc_cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";
			else
				$condition .= " and (match(dc_cate_match) against('".$cate_name_unicode."' IN BOOLEAN MODE)) ";

	}
	return $condition;
}

/**
 * 从数据库获取门店信息
 * @param unknown_type $tname  表缩写
 * @param unknown_type $location_id 门店ID
 * @param unknown_type $field_append 要追加查询的语句
 * @return $location_info 返回门店信息，一维数组
 */
function get_location_info($tname,$location_id,$field_append){
	
	$location_info=$GLOBALS['db']->getRow("select ".$tname.".*".$field_append." from ".DB_PREFIX."supplier_location as ".$tname." where id=".$location_id);	
	return $location_info;
	
}


/**
 * 根据距离 获取门店配送信息
 * @param unknown_type $id_arr二维关联数据，储存一个或多个门店ID以及距离
 * 
	例如	$id_arr=array(
			21=>array('id'=>21,'distance'=>1.33),
			22=>array('id'=>22,'distance'=>8.43)
		);
		
 * @return $delivery_conf 返回门店配送信息,其中is_free_delivery=0为不免运费，1为免运费，2为超过配送范围，不配送,
 * 例如 
 * $delivery_conf=array(
    [21] => Array
        (
            [id] => 456
            [location_id] => 21
            [start_price] => 10.0000
            [scale] => 10
            [delivery_price] => 1.0000
            [is_free_delivery] => 1
        )
    )
 * 
 */
function get_location_delivery_info($id_arr=array()){

	if(count($id_arr)>0){
		$id_str=get_id_str($id_arr);
		$delivery_info=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_delivery where location_id in (".$id_str.") order by location_id , scale ");
	}
	$delivery_info_new=array();
	foreach($delivery_info as $kk=>$vv){
		$delivery_info_new[$vv['location_id']][]=$vv;
		
	}

	$delivery_conf=array();
	foreach($delivery_info_new as $m=>$n){

		if(count($delivery_info_new[$m])>0){
	
			foreach($delivery_info_new[$m] as $k=>$v){
			 $distance=$id_arr[$m]['distance'];
	
				if($k==0){
					if(0 <= $distance && $distance <= $v['scale']){
						$delivery_conf[$m]=$v;
					}
				}else{
					if($delivery_info_new[$m][$k-1]['scale'] <= $distance && $distance <= $delivery_info_new[$m][$k]['scale']){
						$delivery_conf[$m]=$v;
					}
				}

				
			}
			
			if($delivery_conf[$m]){
				$delivery_conf[$m]['is_free_delivery']=0;
				//0为不免运费
			}else{
				$delivery_conf[$m]['is_free_delivery']=2;
				//2为超过配送范围，不配送
			}
		
		}
	}
	return $delivery_conf;

	
}

/**
 * 获取门店外卖的打包费
 * @param unknown_type $location_id  门店ID
 * @param unknown_type $location_dc_cart  外卖购物车中的购物数据
 * $package_fee 返回打包费金额
 */

function get_location_package_fee($location_id,$location_dc_cart){
	
	
	/*  打包费计算*/
	$location_package_conf=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_package_conf where location_id=".$location_id);
	$dc_package_info=array();
	
	if($location_package_conf['package_start_price']==0){
	
		$dc_package_info['is_free_package']=1;
	}elseif($location_package_conf['package_start_price']==-1){
		$dc_package_info['is_free_package']=0;
		$dc_package_info['package_price']=$location_package_conf['package_price'];
		$dc_package_info['total_package_price']=$dc_package_info['package_price']*$location_dc_cart['total_data']['total_count'];
	}elseif($location_package_conf['package_start_price']>0){
	
		if($location_dc_cart['total_data']['total_price']>=$location_package_conf['package_start_price']){
			$dc_package_info['is_free_package']=1;
		}else{
			$dc_package_info['is_free_package']=0;
			$dc_package_info['package_price']=$location_package_conf['package_price'];
			$dc_package_info['total_package_price']=$dc_package_info['package_price']*$location_dc_cart['total_data']['total_count'];
		}
	
	}
	//$package_fee打包费
	$package_fee=isset($dc_package_info['total_package_price'])?$dc_package_info['total_package_price']:0;
	return $package_fee;
}



/**
 * 从数据库得到的二维索引数据集中得到id列表，形如 1,5,8,10,12 用于select in查询
 * @param unknown_type $data_info 从数据库得到的二维索引数据集
 * @return string 返回数据集中id的列表，用逗号隔开，形如 1,5,8,10,12 用于select in查询
 */

function get_id_str($data_info){
	
	$id_str='';
	foreach($data_info as $k=>$v){
		$id_str.=','.$v['id'];
	}
	return $id_str=ltrim($id_str,',');
}

/**
 * 把从数据库得到的二维索引数据集转换为以  指定键名  的二维关联数据集
 * @param unknown_type $data_info 从数据库得到的二维索引数据集
 * @param string $key指定的键名
 * @return $data_info_new 返回 指定键名的二维关联数据集
 */

function data_format_idkey($data_info,$key='id'){
	$data_info_new=array();
	foreach($data_info as $k=>$v){
		$data_info_new[$v[$key]]=$v;	
	}
	return $data_info_new;
	
}
		/**
		 * 外卖促销规则
		 * dc_online_pay：是否在线支付，值定为1
		 * dc_allow_cod：支持货到付款，值定为2
		 * is_firstorderdiscount：是否支持新单立减，值定为3
		 * is_payonlinediscount：是否支持在线支付优惠，值定为4
		 * dc_allow_ecv：支持代金卷，值定为5
		 * dc_allow_invoice：是否支持发票，值定为6
		 * 
		 * 
		 */
function dc_promote_rule($promote){
	$promote_new=array();
	foreach($promote as $k=>$v){
		
		if($v==1){
			switch($k){
				
				case 'dc_online_pay':
					$promote_new[$k]=1;
					break;
				case 'dc_allow_cod':
					$promote_new[$k]=2;
					break;	
				case 'is_firstorderdiscount':
					$promote_new[$k]=3;
					break;	
				case 'is_payonlinediscount':
					$promote_new[$k]=4;
					break;	
				case 'dc_allow_ecv':
					$promote_new[$k]=5;
					break;												
				case 'dc_allow_invoice':
					$promote_new[$k]=6;
					break;								
			}
			
		}
	}
	
	$ptag = 0;
	foreach($promote_new as $kk=>$t)
	{
		$t2 = pow(2,$t);
		$ptag = $ptag|$t2;
	}
	return $ptag;
}


function get_dc_promote_info(){
	$dc_promote_info=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_promote");
	$dc_promote_info_new=array();
	foreach($dc_promote_info as $k=>$v){
		if($v['class_name']=='FirstOrderDiscount'){
			$dc_promote_info_new['is_firstorderdiscount']=$v;
		}elseif($v['class_name']=='PayOnlineDiscount'){	
			$dc_promote_info_new['is_payonlinediscount']=$v;
		}
		
	}
	return $dc_promote_info_new;
}




/**
 * @param $location_id门店的id
 * @param unknown_type $date 要查询的日期
 * @return array('list'=>$rs_list); 1.计算出这天有多少桌子可以预定 或者今天 余下的时间还有多少桌子可以预定
 */

function get_rs_item($location_id,$date){

	$sql = "select * from ".DB_PREFIX."dc_rs_item where is_effect = 1 and location_id = ".$location_id." order by sort desc";

	$rs_list = $GLOBALS['db']->getAll($sql,false);

	$rs_list=data_format_idkey($rs_list,$key='id');

	$id_arr=array();
	foreach($rs_list as $kk=>$vv){
		if(!in_array($vv['id'], $id_arr)){
			$id_arr[]=$vv['id'];
		}
	}

	$rs_time_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_rs_item_time where is_effect = 1 and item_id in  (".implode(',',$id_arr).")");	//营业时间 和现在时间 分割对比

	$rs_time_list=data_format_idkey($rs_time_list,$key='id');


	$arr_d=array();
	foreach($rs_time_list as $kk=>$vv){
		if(!in_array($vv['id'], $arr_d)){
			$arr_d[]=$vv['id'];
		}
	}
	//库存
	$day=$GLOBALS['db']->getAll("select id,buy_count,time_id,rs_time,rs_date from ".DB_PREFIX."dc_rs_item_day where time_id in (".implode(',',$arr_d).") and rs_date='".$date."'");

	$day=data_format_idkey($day,$key='time_id');

	foreach($rs_time_list as $kd=>$vd){
			
	 	$rs_time_list[$kd]['rs_time']=to_date(to_timespan($vd['rs_time']),"H:i");
			
		$rs_time=to_timespan($vd['rs_time']);

		$rs_time_list[$kd]['rs_num']=$rs_time;
			
		$now_num=NOW_TIME+3600;//延后1个小时
		
		$date_num=to_timespan($date);//判断日期是否是今天 ，对今天过去的时间段做判断，给予餐桌状态
		$todoy=to_date(NOW_TIME,"Y-m-d");
		//当天时间
		if($todoy==$date)
		{
				
			if($now_num < $rs_time)
			{
				//可以预订
				$rs_time_list[$kd]['today_ef']=2;

			}
			else
			{
				//不可以预订
				$rs_time_list[$kd]['today_ef']=1;

			}
				

		}else{   //今天之后的时间
				$rs_time_list[$kd]['today_ef']=0;
		}

		if($vd['total_count']>$day[$kd]['buy_count']) //判断是否还有库存
		{
				
			$rs_time_list[$kd]['count_status']=1;
		}
		else
		{
			$rs_time_list[$kd]['count_status']=0;
		}
			
	}

	$arr_table=array();


	foreach($rs_time_list as $vv=>$xx){
		$arr_table[$xx['item_id']]['table'][]=$xx;

	}

	foreach($arr_table as $m=>$n){
		$arr_table[$m]=$rs_list[$m];
			
		$sor = array(
				'direction' => 'SORT_ASC',
				'field'     => 'rs_num',
		);
		$arrSort = array();
		foreach($n['table'] as $ka=>$va){

			$rs_num[$ka]=$va['rs_num'];
			foreach($va as $key=>$value){
				$arrSort[$key][$ka] = $value;
			}
		}
		array_multisort($arrSort[$sor['field']], constant($sor['direction']), $n['table']);
		$arr_table[$m]['table']=$n['table'];
	}

		
	$sora = array(
			'direction' => 'SORT_DESC',
			'field'     => 'sort',
	);
	$arrSort = array();
	foreach($arr_table as $ka=>$va){

		$sora[$ka]=$va['sort'];
		foreach($va as $key=>$value){
			$arrSort[$key][$ka] = $value;
		}
	}
	array_multisort($arrSort[$sora['field']], constant($sora['direction']), $arr_table);


	//print_r($arr_table);die;

	return array('list'=>$arr_table);
}



//菜单列表
function get_dcmenu_list($limit,$menu_tag_id,$lid_list,$type){
	
		//支持外卖的商家
	foreach($lid_list as $k=>$v){
		
		$lid_list[$k]=$v['id'];
	}
	if($menu_tag_id)
	{	
	$sql = "select * from ".DB_PREFIX."dc_menu where is_effect = 1 and location_id in (".implode(",",$lid_list).") and FIND_IN_SET('".$menu_tag_id."',tags)";	
	
	$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_menu where is_effect = 1 and location_id in (".implode(",",$lid_list).") and  FIND_IN_SET('".$menu_tag_id."',tags)",false);
	}
	else
	{
	$sql = "select * from ".DB_PREFIX."dc_menu where is_effect = 1 and location_id in (".implode(",",$lid_list).")";
	
	$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_menu where is_effect = 1 and location_id in (".implode(",",$lid_list).")",false);		
	}
	
	$sql.=" order by buy_count desc limit ".$limit;
	$dc_menus = $GLOBALS['db']->getAll($sql,false);

	return array('list'=>$dc_menus,'count'=>$total);
	
	
}	



function get_dcmenu_count($limit){
	
	$sql = "select count(*) from ".DB_PREFIX."dc_menu where is_effect = 1";
	
	$count = $GLOBALS['db']->getOne($sql,false);
	
	return $count;
	
	
}
/**
 * 判断商家是否在营业时间段中
 * @param unknown_type $location_id
 * @return number
 */
function is_in_open_time($location_id){
	
	$location_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id=".$location_id);
	$is_in_open_time=0;
	if($location_info['is_close']==0){

	$time=date('H',time())*3600+date('i',time())*60;	
	$open_time_info=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$location_id);

	if(count($open_time_info)==0){
		$is_in_open_time=1;
	}else{
		foreach($open_time_info as $kp=>$vp){
			if(($vp['begin_time_h']*3600 + $vp['begin_time_m']*60) < $time && ($vp['end_time_h']*3600 +$vp['end_time_m']*60) > $time){
	
				$is_in_open_time=1;
				break;
			}
				
		}
	}
	}
	return $is_in_open_time;
}

/**
 * 获取搜索地理位置最新搜索名 和所在的城市
 * @return string 返回最新搜索名和所在的城市
 */
function get_lastest_search_name(){
	
	$dc_search_history=es_cookie::get('dc_search_history');
	$dc_search_history=json_decode($dc_search_history,true);

	$first_search=array_slice($dc_search_history,0,1);
	foreach($first_search as $k=>$v){
		$s_info['dc_title']=$v['dc_title'];
		$s_info['city_name']=$v['city_name'];
	}
	return $s_info;

}


/**
 * 获取当前用户门店收藏信息
 * @param unknown_type $limit  分页查询条件
 * @return array('list'=>$collect_location,'count'=>$count,'id'=>$idarr); 返回当前用户门店收藏信息，包括1，收藏门店列表，2，当前用户收藏总数3，门店距离信息
 */
function get_user_location_collect($limit='0,12'){
	
	global_run();
	$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
	$collect_location=$GLOBALS['db']->getAll("select sl.id ,sl.name,sl.preview,sl.avg_point,sl.dp_count , sc.add_time,sc.id as link_id from ".DB_PREFIX."supplier_location as sl left join ".DB_PREFIX."dc_location_sc as sc on sl.id= sc.location_id where sc.user_id=".$user_id." order by sc.add_time desc limit ".$limit);
	$count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where user_id=".$user_id);
	$idarr=array();
	foreach($collect_location as $k=>$v){
		$collect_location[$k]['url']=url('index','dcbuy',array('lid'=>$v['id']));
		$idarr[]=$v['id'];
	}
	return array('list'=>$collect_location,'count'=>$count,'id'=>$idarr);
	
}



//原dc_cart中的函数

/**
 * 获取外卖购物车数据
 * 
 * @param unknown_type $reload 是否从数据库中重新读取，true重新读取，false不重新读取
 * @param unknown_type $location_id 门店ID
 * @param unknown_type $type 购物车类型，$type=0为餐桌类型，$type=1为商品类型
 * @return array("cart_list"=>$cart_list,"total_data"=>$total_data);  返回购物车数据，包括1，购物车列表;2,购物车总数，金额总数和数量总数
 */
function load_dc_cart_list($reload=false,$location_id='',$type=0)
{
	global_run();
	$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
	$rows=array("日","一","二","三","四","五","六");

	if(!$reload)
	{
		/*
		static $result;
		if($result)
		{
			return $result;
		}
		*/
		$result = es_session::get("dc_cart_result");
		if($result&&is_array($result)&&count($result)>0)
		{
			
			return $result;
		}
		if($location_id==''){
			$cart_list_res = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."dc_cart as c where c.session_id = '".es_session::id()."' and c.user_id=".$user_id." and c.is_effect=1 and c.cart_type=".$type." order by c.add_time");
			$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and is_effect=1 and cart_type=".$type." and  user_id=".$user_id);
		}else{
			$cart_list_res = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."dc_cart as c where c.session_id = '".es_session::id()."' and c.user_id=".$user_id." and c.is_effect=1 and c.cart_type=".$type." and c.location_id=".$location_id." order by c.add_time");
			$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and user_id=".$user_id." and is_effect=1 and cart_type=".$type." and location_id=".$location_id);
		}
		$cart_list = array();
		foreach($cart_list_res as $k=>$v)
		{
			if($type==0){
				$v['url'] = url("index","dctable&lid=".$v['location_id']);
				$v['table_time_format']='<span class="time_span">'.to_date($v['table_time'],"Y-m-d").'</span><span class="time_span">星期'.$rows[to_date($v['table_time'],"w")].'</span><span class="time_span">'.to_date($v['table_time'],"H:i").'</span>';
							
			}else{
				$v['url'] = url("index","dcbuy&lid=".$v['location_id']);
			}		
			$cart_list[$v['id']] = $v;
		}
		if(count($cart_list)>0){
			$result = array("cart_list"=>$cart_list,"total_data"=>$total_data);
			es_session::set("dc_cart_result", $result);
			return $result;
		}
	}
	else
	{

		if($location_id==''){
			$cart_list_res = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."dc_cart as c where c.session_id = '".es_session::id()."' and c.is_effect=1 and c.cart_type=".$type." and c.user_id=".$user_id." order by c.add_time");
			$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and is_effect=1 and cart_type=".$type." and user_id=".$user_id);
		}else{
			
			$cart_list_res = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."dc_cart as c where c.session_id = '".es_session::id()."' and c.user_id=".$user_id." and c.is_effect=1 and c.cart_type=".$type." and c.location_id=".$location_id." order by c.add_time");
			$total_data = $GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and user_id=".$user_id." and is_effect=1 and cart_type=".$type." and location_id=".$location_id);
		}

		$cart_list = array();
		foreach($cart_list_res as $k=>$v)
		{
			if($type==0){
				$v['url'] = url("index","dctable&lid=".$v['location_id']);
				$v['table_time_format']='<span class="time_span">'.to_date($v['table_time'],"Y-m-d").'</span><span class="time_span">星期'.$rows[to_date($v['table_time'],"w")].'</span><span class="time_span">'.to_date($v['table_time'],"H:i").'</span>';
			}else{
				$v['url'] = url("index","dcbuy&lid=".$v['location_id']);
			}
				
			$cart_list[$v['id']] = $v;
		}

		//有操作程序就更新购物车状态
		/*
		 $GLOBALS['db']->query("update ".DB_PREFIX."dc_cart set update_time=".NOW_TIME.",user_id = ".intval($GLOBALS['user_info']['id'])." where session_id = '".es_session::id()."'");
		*/
		if(count($cart_list)>0){
			$result = array("cart_list"=>$cart_list,"total_data"=>$total_data);
			es_session::set("dc_cart_result", $result);
			return $result;
		}

	}
}

/**
 * session过期 清空外卖购物车
 */

function refresh_dccart_list(){

	if(!es_session::get("dc_cart_result"))
	{
		//session过期清空购物车
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where add_time < ".NOW_TIME."-".SESSION_TIME);
	}

}




//end dc_cart



//以下是原system/common.php中的订餐函数



/**
 * 同步外卖商品标签全文索引
 * @param unknown_type $menu_id  商品ID
 */
function syn_supplier_location_menu_match($menu_id){

	$menu = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id = ".$menu_id);
	if($menu)
	{
		$menu['tags_match'] = "";
		$menu['tags_match_row'] = "";
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu", $menu, $mode = 'UPDATE', "id=".$menu_id, $querymode = 'SILENT');
	}
	//同步名称
	$tagarr=explode(',',$menu['tags']);
	foreach($tagarr as $tag){
		$tagname=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."dc_menu_cate where id=".$tag);
		insert_match_item($tagname,"dc_menu",$menu_id,"tags_match");
			
	}

}




/**
 * 同步餐厅分类全文索引
 * @param unknown_type $id 门店ID
 */
function syn_supplier_location_dc_cate_match($id){

	$dc_cate_list=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cate where is_effect=1");
	$dc_cate_location_list=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_cate_supplier_location_link where location_id=".$id);
	$supplier_location=array();

	$supplier_location['dc_cate_match'] = "";
	$supplier_location['dc_cate_match_row'] = "";
	$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location", $supplier_location, $mode = 'UPDATE', "id=".$id, $querymode = 'SILENT');

	foreach ($dc_cate_location_list as $k=>$v){
		$dc_cate_name=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."dc_cate where id=".$v['dc_cate_id']);
		insert_match_item(trim($dc_cate_name),"supplier_location",$id,"dc_cate_match");
	}

}




/**
 * 同步菜单经纬度为门店经纬度
 * @param unknown_type $menu  菜单数据,储存门店ID和门店经纬度
 * 
 * 			$menu['xpoint']=$data['xpoint'];
			$menu['ypoint']=$data['ypoint'];
			$menu['location_id']=$id;
 */
function sys_location_menu_xypoint($menu){
	$GLOBALS['db']->autoExecute(DB_PREFIX."dc_menu",$menu,$mode='UPDATE','location_id='.$menu['location_id'],$querymode = 'SILENT');
}



/**
 * 同步餐厅营业时间全文索引
 * @param unknown_type $opentime 为餐厅营业时间数据
 * @param unknown_type $location_id 为餐厅ID
 */
function syn_supplier_location_open_time_match($opentime,$location_id){

	$open_time_arr=array();
	$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$location_id);
	foreach($opentime['begin_time_h'] as $k=>$begin_time_h_item)
	{
		$open_time_data['begin_time_h'] = $opentime['begin_time_h'][$k];
		$open_time_data['begin_time_m'] = $opentime['begin_time_m'][$k];
		$open_time_data['end_time_h'] = $opentime['end_time_h'][$k];
		$open_time_data['end_time_m'] = $opentime['end_time_m'][$k];
		$open_time_data['location_id'] = $location_id;
		$open_time_arr[]=$open_time_data;
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_location_open_time",$open_time_data);
	}


	$opentime=array();
	foreach($open_time_arr as $k=>$v){

		$open_time_cfg[$k]=date("H:i",$open_time_arr[$k]['begin_time_h']*3600+$open_time_arr[$k]['begin_time_m']*60-3600*8).'-'.date("H:i",$open_time_arr[$k]['end_time_h']*3600+$open_time_arr[$k]['end_time_m']*60-3600*8);


		if($v['begin_time_m']<=30 && $v['begin_time_m']>0){
			$open_time_arr[$k]['begin_time_m']=30;
		}elseif($v['begin_time_m']>=30 && $v['begin_time_m']!=0){
			$open_time_arr[$k]['begin_time_m']=0;
			$open_time_arr[$k]['begin_time_h']+=1;
		}
		if($v['end_time_m']<30 && $v['end_time_m'] > 0){
			$open_time_arr[$k]['end_time_m']=0;
		}elseif($v['end_time_m']>=30 && $v['end_time_m']!=0){
			$open_time_arr[$k]['end_time_m']=30;
		}
		$opentime[]=get_half_time($open_time_arr[$k]);
	}
	sort($open_time_cfg);

	$opentimetmp=array();
	foreach($opentime as $k=>$v){
		foreach($v as $v2){
			$opentimetmp[]=$v2;
		}
	}
	asort($opentimetmp);
	$open_time_match['open_time_cfg_str']=implode(',',$open_time_cfg);
	$open_time_match['open_time_match']=implode(',',$opentimetmp);
	$open_time_match['open_time_match_row']=implode(',',$opentimetmp);
	$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location",$open_time_match,$mode='UPDATE','id='.$location_id,$querymode = 'SILENT');
	$GLOBALS['db']->query("UPDATE ".DB_PREFIX."dc_menu set open_time_cfg_str='".$open_time_match['open_time_cfg_str']."' where location_id=".$location_id);
	
}



/**
 * 同步餐厅配送信息
 * @param unknown_type $delivery  为餐厅配送数据
 * @param unknown_type $location_id 餐厅ID
 */
function syn_supplier_location_delivery_price($delivery,$location_id){

	$delivery_data=array();
	$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_delivery where location_id=".$location_id);
	foreach($delivery['scale'] as $k=>$v){

		$delivery_data['scale'] = $delivery['scale'][$k];
		$delivery_data['start_price'] = $delivery['start_price'][$k];
		$delivery_data['delivery_price'] = $delivery['delivery_price'][$k];
		$delivery_data['location_id'] = $location_id;
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_delivery",$delivery_data);
	}
}




/**
 * 对一个给定的二维数组按照指定的键名进行排序
 * @param unknown_type $arr  二维数组
 * @param unknown_type $keys 按$keys这个键名进行排序
 * @param unknown_type $type $type规定是升序还是降序，默认是升序
 * @return $new_array  返回的是  按$keys这个键名和$type进行排序后的新的二维数组
 */
function array_sort($arr,$keys,$type='asc'){
	$keysvalue = $new_array = array();
	foreach ($arr as $k=>$v){
		$keysvalue[$k] = $v[$keys];
	}
	if($type == 'asc'){
		asort($keysvalue);
	}else{
		arsort($keysvalue);
	}
	reset($keysvalue);
	foreach ($keysvalue as $k=>$v){
		$new_array[] = $arr[$k];
	}
	return $new_array;
}


/**
 * 获取一个时间段内半小时的时间段
 * @param unknown_type $timedata 一维数组，里面存有begin_time_h，begin_time_m，end_time_h，end_time_m四个时间数据
 * @return array 返回半小时的时间段数组,形如 array('02:30','03:00','03:30');
 */
function get_half_time($timedata){
	$timearr=array();
	$begintime=3600*($timedata['begin_time_h'])+($timedata['begin_time_m'])*60;
	$endtime=3600*($timedata['end_time_h'])+($timedata['end_time_m'])*60;
	$num=($endtime-$begintime)/1800+1;

	for($i=0;$i<$num;$i++){
		$timearr[]=date("H:i",$begintime+$i*1800-3600*8);
	}
	return $timearr;

}


/**
 *
 * 创建外卖订餐的付款单号
 * @param $money 付款金额
 * @param $order_id 订单ID
 * @param $payment_id 付款方式ID
 * @param $memo 付款单备注
 * @param $ecv_id 如为代金券支付，则指定代金券ID
 * return payment_notice_id 付款单ID
 *
 */
function make_dcpayment_notice($money,$order_id,$payment_id,$memo='',$ecv_id=0)
{
	$notice['create_time'] = NOW_TIME;
	$notice['order_id'] = $order_id;
	$notice['user_id'] = $GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."dc_order where id = ".$order_id);
	$notice['payment_id'] = $payment_id;
	$notice['memo'] = $memo;
	$notice['money'] = $money;
	$notice['ecv_id'] = $ecv_id;
	$notice['order_type'] = 1;
	do{
		$notice['notice_sn'] = to_date(NOW_TIME,"Ymdhis").rand(10,99);
		$GLOBALS['db']->autoExecute(DB_PREFIX."payment_notice",$notice,'INSERT','','SILENT');
		$notice_id = intval($GLOBALS['db']->insert_id());
	}while($notice_id==0);
	return $notice_id;
}


/**
 * 外卖预定付款单的支付
 * @param unknown_type $payment_notice_id
 * 当超额付款时在此进行退款处理
 */
function dcpayment_paid($payment_notice_id)
{
	
	require_once APP_ROOT_PATH."system/model/user.php";
	$payment_notice_id = intval($payment_notice_id);
	$now = NOW_TIME;
	$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set pay_time = ".$now.",is_paid = 1 where id = ".$payment_notice_id." and is_paid = 0");
	$rs_row = $GLOBALS['db']->affected_rows();
	$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$payment_notice['order_id']);
	$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment_notice['payment_id']);
	$pay_price=$order_info['total_price']-$order_info['pay_amount']-$order_info['promote_amount'];  //剩余未付金额
	if($rs_row){

		if($pay_price >= $payment_notice['money']){			
			$add_price=$payment_notice['money'];
		}else{
			//超额支付
			$add_price=$pay_price;			
		}
		
		
		if($pay_price > 0){
			if($payment_info['class_name'] == 'Voucher')
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set pay_amount = pay_amount + ".$add_price.",ecv_money = ".$add_price.",ecv_id=".$payment_notice['ecv_id']." where id = ".$payment_notice['order_id']." and type_del = 0 and order_status = 0 ");
				$order_incharge_rs = $GLOBALS['db']->affected_rows();
			}
			elseif($payment_info['class_name'] == 'Account')
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set pay_amount = pay_amount + ".$add_price.",account_money = account_money + ".$add_price." where id = ".$payment_notice['order_id']." and type_del = 0 and order_status = 0 ");
				$order_incharge_rs = $GLOBALS['db']->affected_rows();
			}
			else
			{
				
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set pay_amount = pay_amount + ".$add_price." , online_pay = online_pay + ".$add_price." where id = ".$payment_notice['order_id']." and type_del = 0 and order_status = 0");
				$order_incharge_rs = $GLOBALS['db']->affected_rows();
					
			}
			
			$GLOBALS['db']->query("update ".DB_PREFIX."payment set total_amount = total_amount + ".$add_price." where class_name = '".$payment_info['class_name']."'");
			
		}
		
		if($order_incharge_rs && $pay_price < $payment_notice['money'])//订单支付超出
		{	//超额支付,超过部分充入会员余额，代金劵超出部分不充入余额
			
			$incharge_price=$payment_notice['money']-$pay_price;
			if($payment_info['class_name'] != 'Voucher'){
				if($order_info['type_del']==1||$order_info['order_status']==1)
					$msg = sprintf($GLOBALS['lang']['DELETE_INCHARGE'],$payment_notice['notice_sn']);
				else
					$msg = sprintf($GLOBALS['lang']['PAYMENT_INCHARGE'],$payment_notice['notice_sn']);
					
				modify_account(array('money'=>$incharge_price,'score'=>0),$payment_notice['user_id'],$msg);
				dc_modify_statements($incharge_price, 2, $order_info['order_sn']."订单超额支付"); //订单超额充值
				
				dc_order_log($order_info['order_sn']."订单超额支付，".format_price($incharge_price)."已退到会员余额", $order_info['id']);
				
			}
			$rs=2;	//超额支付
			
		}elseif($order_incharge_rs && $pay_price >= $payment_notice['money']){
			
			$rs=1;  //正常支付
		}elseif($order_incharge_rs==0){
			$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set pay_time = 0,is_paid = 0 where id = ".$payment_notice_id);
			$rs=0;  //支付失败
		}

		if($order_incharge_rs){
		//在此处开始生成付款的短信及邮件
		send_payment_sms($payment_notice_id);
		send_payment_mail($payment_notice_id);
		}
	}else{
		//已经支付过，重复支付,重复支付费用退回账户余额
		$rs=1;	//重复支付
		/*
		if($payment_info['class_name'] != 'Voucher'){
			if($order_info['type_del']==1||$order_info['order_status']==1)
				$msg = sprintf($GLOBALS['lang']['DELETE_INCHARGE'],$payment_notice['notice_sn']);
			else
				$msg = sprintf($GLOBALS['lang']['PAYMENT_INCHARGE'],$payment_notice['notice_sn']);
				
			modify_account(array('money'=>$payment_notice['money'],'score'=>0),$payment_notice['user_id'],$msg);
			dc_modify_statements($payment_notice['money'], 2, $order_info['order_sn']."订单重复支付"); //订单超额充值
		
			dc_order_log($order_info['order_sn']."订单重复支付，".format_price($payment_notice['money'])."已退到会员余额", $order_info['id']);
		
		}
		*/	
	}
	return $rs;
}

/**
 * 当付款单支付成功后，为订单进行偿试支付
 */
function dcorder_paid($order_id)
{
	$order_id  = intval($order_id);
	$order = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
	if($order['pay_amount'] + $order['promote_amount'] >=$order['total_price'])
	{
		$now = NOW_TIME;
		$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set pay_status = 1 , pay_time =". $now ." where id =".$order_id." and pay_status <> 1");
		$rs = $GLOBALS['db']->affected_rows();
		if($rs)
		{
			//支付完成
			dcorder_paid_done($order_id);
			$result = true;
		}else{
			$result = false;
		}
		//超额支付，退回会员帐户
		if($order['pay_amount'] + $order['promote_amount'] > $order['total_price']){
			$return_price=$order['pay_amount'] + $order['promote_amount'] - $order['total_price'];  //剩余未付金额
			modify_account(array('money'=>$return_price ,'score'=>0),$order['user_id'],'订单超额支付，退回用户');
			dc_order_log($order['order_sn']."订单超额支付，".format_price($return_price)."已退到会员余额", $order['id']);
			$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set pay_amount = pay_amount - " .$return_price." where id=".$order['id']);
		}
		
	}
	else
	{
		//by hc 0507
		$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set pay_status = 0 where id =".$order_id);
		$result = false;  //订单未支付成功
	}

	return $result;
	
}

/**
 * 订单关闭，退还已付金额到会员帐户，如果是预订，更新库存
 */
 function dc_order_close($order_id,$is_cancel,$close_reason){
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
	if($order_info['is_cancel']==0 && $order_info['refund_status']==0){
		//在线支付，并有手续费，手续费不退回
		if($order_info['online_pay'] > 0){
			$return_money=$order_info['pay_amount']-$order_info['ecv_money'] - $order_info['payment_fee'];
				
		}else{
			$return_money=$order_info['pay_amount']-$order_info['ecv_money'];
		}
		$data['is_cancel']=$is_cancel;
		$data['refund_price']=$return_money;
		if($is_cancel==1){
			$data['refund_memo']=$close_reason;
		}else{
			$data['refuse_memo']=$close_reason;
		}
		$data['refund_time']=NOW_TIME;
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_order", $data, $mode = 'UPDATE', "id=".$order_id, $querymode = 'SILENT');
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_order", $data, $mode = 'UPDATE', "order_id=".$order_id, $querymode = 'SILENT');
		
		//如果是预订，更新库存
		if($order_info['is_rs']==1 && $order_info['pay_status']==1){
			$order_menu=unserialize($order_info['order_menu']);
			$location_dc_table_cart=$order_menu['rs_list'];
			foreach($location_dc_table_cart['cart_list'] as $kk=>$vv){
				$rs_date=date("Y-m-d",$vv['table_time']);
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_rs_item_day set buy_count = buy_count - ".$vv['num']." where time_id=".$vv['table_time_id']." and rs_date='".$rs_date."'");
			}
			
		}
		//退款
		
		if($order_info['is_rs']==1){
			$msg='预订订单'.$order_info['order_sn'].'关闭,已付金额退回,关闭原因：'.$close_reason;
		}else{
			$msg='外卖订单'.$order_info['order_sn'].'关闭,已付金额退回,关闭原因：'.$close_reason;
		}

		
		if($return_money > 0){
		require_once APP_ROOT_PATH."system/model/user.php";
		modify_account(array('money'=>$return_money,'score'=>0),$order_info['user_id'],$msg);
		dc_order_log($order_info['order_sn']."订单关闭, ".format_price($return_money)."已退到会员余额".", 关闭原因：".$close_reason, $order_id);
		}
		$s_order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_order where order_id = ".$order_id);
		dc_modify_supplier_account($s_order_info['total_price'],$s_order_info['supplier_id'],4,$msg,$order_id);
	
		
		if($order_info['ecv_money'] > 0){
		//退回代金劵
		$GLOBALS['db']->query("update ".DB_PREFIX."ecv set use_count = use_count -1 where id=".$order_info['ecv_id']);
		dc_order_log($order_info['order_sn']."订单关闭, 代金劵退还给用户".", 关闭原因：".$close_reason, $order_id);
		}
	}


}

/**
 * 给预订用户发送电子劵
 * @param unknown_type $order_id  要发送的订单ID
 * 
 */

function dc_send_user_coupon_sms($order_id)
{
	
	if(app_conf("SMS_ON")==1)
	{	$order_info = $GLOBALS['db']->getRow("select do.is_rs as is_rs ,do.consignee as consignee,do.mobile as mobile, do.user_id as user_id , do.sn_id as sn_id , do.order_menu as order_menu , sl.name as location_name , sl.tel as location_tel , sl.address as location_address from ".DB_PREFIX."dc_order as do left join ".DB_PREFIX."supplier_location as sl on do.location_id=sl.id where do.id = ".$order_id);
		

		if($order_info['is_rs']==1){
/*
			$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$order_info['user_id']);
			
*/			
			if($order_info['mobile'])
			{
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_DC_USER_COUPON_SMS'");
				$tmpl_content = $tmpl['content'];
	
				$GLOBALS['tmpl']->assign("user_name",$order_info['consignee']);
				$order_info['order_menu']=unserialize($order_info['order_menu']);
				$rs_list=$order_info['order_menu']['rs_list']['cart_list'];
				foreach($rs_list as $k=>$v){
					$order_info['table_name']=$v['name'];
					$order_info['table_time_format']=to_date($v['table_time']);	
				}
				
				$order_info['sn']=$GLOBALS['db']->getOne("select sn from ".DB_PREFIX."dc_coupon where is_used=0 and is_valid <> 2 and id =".$order_info['sn_id']." and user_id=".$order_info['user_id']);
				$GLOBALS['tmpl']->assign("order_info",$order_info);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $order_info['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['content'] = addslashes($msg);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_coupon set is_valid=1 where id=".$order_info['sn_id']);
				
				if(APP_INDEX!="index")
				{
					$msg_data['id'] = $GLOBALS['db']->insert_id();
					send_msg_item($msg_data);
				}
	
			}
		}
	}
}

/**
 * 收货操作：收货后，更新商家的结算
 * @param unknown_type $delivery_sn
 * @param unknown_type $order_item_id 订单商品ID，将会确认相关的所有订单的同序号发货号。
 */
function dc_confirm_delivery($order_id)
{
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
	if($order_info)
	{
		if($order_info['confirm_status']==1){
			if($order_info['is_rs']==1){
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set confirm_status=2 , confirm_time=".NOW_TIME." where id=".$order_id);
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_order set confirm_status=2 , confirm_time=".NOW_TIME." where order_id=".$order_id);
			}else{
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set confirm_status=2 , arrival_time=". NOW_TIME." , confirm_time=".NOW_TIME." where id=".$order_id);
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_order set confirm_status=2 , confirm_time=".NOW_TIME." where order_id=".$order_id);
				if($order_info['payment_id']==1){
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set pay_status=1 , pay_time=". NOW_TIME." where id=".$order_id);
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_order set pay_status=1 , pay_time=". NOW_TIME." where order_id=".$order_id);
					
				}
			}
			$rs=$GLOBALS['db']->affected_rows();
			if($rs)
			{	$log=$order_info['order_sn']."订单已确认";
				require_once APP_ROOT_PATH."system/model/dc.php";
				dc_modify_supplier_account("-".$order_info['balance_price'], $order_info['supplier_id'], 1, $log,$order_id);  //解冻资金
				dc_order_log($log,$order_id);
				//如果是预定位置，则更新电子劵使用状态
				if($order_info['is_rs']==1){
				$data['is_used']=1;
				$data['confirm_time']=NOW_TIME;
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_coupon",$data, $mode = 'UPDATE', "id=".$order_info['sn_id'], $querymode = 'SILENT');
				}
				//dc_auto_over_status($order_id); //检测自动结单
				$result['status']=1;
				$result['dp_url']=url('index','review',array('location_id'=>$order_info['location_id']));
				$result['info']='订单确认成功';
			}else{
				$result['status']=0;
				$result['info']='订单确认失败';
				
			}
		}elseif($order_info['confirm_status']==2){
				$result['status']=0;
				$result['info']='订单已经确认,不能重复确认';
			
		}elseif($order_info['confirm_status']==0){
				$result['status']=0;
				$result['info']='订单未接单,不能确认';
			
		}
	}else{
		$result['status']=0;
		$result['info']='订单不存在';
	}
	
		return $result;
}


/**
 * 当订单付款成功后执行的函数
 * @param unknown_type $order_id
 */
function dcorder_paid_done($order_id)
{
	//处理支付成功后的操作
	/**
	 * 1. 发货
	 * 2. 超量发货的存到会员中心
	 * 3. 发券
	 * 4. 发放抽奖
	 */
	require_once APP_ROOT_PATH."system/model/deal.php";
	require_once APP_ROOT_PATH."system/model/supplier.php";
	require_once APP_ROOT_PATH."system/model/deal_order.php";
	
	require_once APP_ROOT_PATH."system/model/dc.php";
	$order_id = intval($order_id);
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
	if($order_info)
	{

		$order_menu=unserialize($order_info['order_menu']);
		$location_dc_table_cart=$order_menu['rs_list'];
		$location_dc_cart=$order_menu['menu_list'];

		
		//更新商品的总销售量
		if($location_dc_cart['total_data']['total_price']>0){
		$total_menu_count=0;
		foreach($location_dc_cart['cart_list'] as $k=>$v){
			$total_menu_count+=$v['num'];
			$GLOBALS['db']->query("update ".DB_PREFIX."dc_menu set buy_count = buy_count + ".$v['num']." where id=".$v['menu_id']);
		}
		//更新商家的外卖总销售量
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_location set dc_buy_count = dc_buy_count + ".$total_menu_count." where id=".$order_info['location_id']);
		}
		
		

		$is_in_open_time=is_in_open_time($order_info['location_id']);   //判断是否在营业时间段内
		if($is_in_open_time==0 && $order_info['is_rs']==0 && $order_info['is_cancel']==0){
			//订单关闭
			dc_order_close($order_info['id'] , 2 ,'商家已暂停营业，订单关闭');
		}
			
		if($order_info['is_rs']==1 && $order_info['is_cancel']==0){
			//验证预订位置的库存
			foreach($location_dc_table_cart['cart_list'] as $k=>$v){
		
				$rs_date=date("Y-m-d",$v['table_time']);
				$total_count=$GLOBALS['db']->getOne("select total_count from ".DB_PREFIX."dc_rs_item_time where  id=".$v['table_time_id']);
				$rs_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_day where time_id=".$v['table_time_id']." and rs_date='".$rs_date."'");
				if(!$rs_info && $total_count==0){
					//订单关闭
					dc_order_close($order_info['id'] , 2,'预订库存不足，订单关闭');
				}elseif($rs_info && $rs_info['buy_count'] > $total_count ){
					//订单关闭
					dc_order_close($order_info['id'] , 2,'预订库存不足，订单关闭');
				}else{
					//预订库存量保存
					$rs_info['location_id']= $v['location_id'];
					$rs_info['supplier_id']= $v['supplier_id'];
					$rs_info['time_id']= $v['table_time_id'];
					$rs_info['item_id']= $v['menu_id'];
					$rs_info['rs_time']= to_date($v['table_time'],"H:i:s");
					$rs_info['rs_date']= to_date($v['table_time'],"Y-m-d");
					$rs_info['buy_count']= $v['num'];

					$rs_data=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_day where time_id=".$rs_info['time_id']." and rs_date='".$rs_info['rs_date']."'");
					if($rs_data){
						$GLOBALS['db']->query("update ".DB_PREFIX."dc_rs_item_day set buy_count = buy_count + ".$rs_info['buy_count']." where id=".$rs_data['id']);					
					}else{				
						$GLOBALS['db']->autoExecute(DB_PREFIX."dc_rs_item_day",$rs_info,'INSERT','','SILENT');
					}

					//生成预订电子劵		
					$data['begin_time']=NOW_TIME;
					$data['end_time']=to_timespan($rs_info['rs_date'])+3600*24-1;  //预订的，到预订当天24点才算过期
					$data['is_valid']=0;
					$data['user_id']=$order_info['user_id'];
					$data['supplier_id']=$order_info['supplier_id'];
					$data['location_id']=$order_info['location_id'];	
					$data['order_id']=$order_id;
					$data['is_used']=0;
					$data['sn']=$order_id.rand(100000,999999);

					$GLOBALS['db']->autoExecute(DB_PREFIX."dc_coupon",$data,'INSERT','','SILENT');
					
					$coupon_id=$GLOBALS['db']->insert_id();
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set sn_id=".$coupon_id." where id=".$order_id);
					/*
					if($GLOBALS['db']->affected_rows() > 0){
						//发送电子劵给预订客户
						dc_send_user_coupon_sms($order_id);	
					}
					*/
					//发送站内信给会员
					send_msg($order_info['user_id'], "订单".$order_info['order_sn']."付款成功", "notify", $order_id);
	
				}
			}
		}
		
		$order_info['promote_str']=unserialize($order_info['promote_str']);
		$first_order_discount=0;
		foreach($order_info['promote_str'] as $k=>$v){
			
			if($v['class_name']=='FirstOrderDiscount'){
				$first_order_discount=1;
				break;
			}
		}
		
		//更新用户外卖订单首单立减状态
		//首单是货到付款，之后就没有机会享受些优惠了
		if($first_order_discount==1 || $order_info['payment_id']==1){	
			if($order_info['is_rs']==0){
				$first_order_status=$GLOBALS['db']->getOne("select dc_is_share_first from ".DB_PREFIX."user where id=".$order_info['user_id']);
				if(!$first_order_status){
					$GLOBALS['db']->query("update ".DB_PREFIX."user set dc_is_share_first=1 where id=".$order_info['user_id']);
			
				}
					
			}	
		}
		
		

		
		//	通知商户有新订单
		dc_send_supplier_order($order_info['supplier_id'], $order_id);
		$supplier_id=$order_info['supplier_id'];
		if($order_info['payment_id']==0){  //在线支付			
			$info=$order_info['order_sn']."订单付款完成";
		}elseif($order_info['payment_id']==1){  //货到付款
			$info=$order_info['order_sn']."用户货到付款";
		}
		//	更新商户销售记录

		// 已经全额付款，或者货到货款的订单同步到商户订单表，这样，平台删除已全部完成的订单，不会影响到商户的明细查询
		$s_order_info['order_id']=$order_info['id'];
		$s_order_info['order_sn']=$order_info['order_sn'];
		$s_order_info['supplier_id']=$order_info['supplier_id'];
		$s_order_info['location_id']=$order_info['location_id'];
		$s_order_info['create_time']=$order_info['create_time'];
		$s_order_info['pay_status']=$order_info['pay_status'];
		$s_order_info['total_price']=$order_info['total_price'];
		$s_order_info['pay_amount']=$order_info['pay_amount'];
		$s_order_info['pay_time']=$order_info['pay_time'];
		$s_order_info['online_pay']=$order_info['online_pay'];
		$s_order_info['ecv_money']=$order_info['ecv_money'];
		$s_order_info['account_money']=$order_info['account_money'];
		$s_order_info['payment_id']=$order_info['payment_id'];
		$s_order_info['user_id']=$order_info['user_id'];
		if($order_info['payment_id']==0){
			$s_order_info['balance_price']=$order_info['balance_price'];
		}elseif($order_info['payment_id']==1){  //货到付款，商户线下收钱
			$s_order_info['balance_price']=0;
			
		}
		$s_order_info['location_name']=$order_info['location_name'];
		$s_order_info['promote_amount']=$order_info['promote_amount'];

		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_order",$s_order_info,'INSERT','','SILENT');
		
		dc_modify_supplier_account($order_info['total_price'],$supplier_id,0,$info,$order_id); //商户销售额增加
		dc_modify_supplier_account($order_info['balance_price'],$supplier_id,1,$info,$order_id); //商户冻结资金增加
		//订单完成日志
		dc_order_log($info, $order_id);
		
		//  更新平台报表
		

	}

}


function dc_order_log($log_info,$order_id)
{
	$data['id'] = 0;
	$data['log_info'] = $log_info;
	$data['log_time'] = NOW_TIME;
	$data['order_id'] = $order_id;
	$GLOBALS['db']->autoExecute(DB_PREFIX."dc_order_log", $data);
}

/**
 * 商家结算，把未结算金额变为可提现金额
 * @param unknown_type $supplier_id
 */
function dc_supplier_balance($supplier_id){
	
	$balance_total=$GLOBALS['db']->getOne("select sum(balance_price) as balance_total from ".DB_PREFIX."dc_supplier_order where order_status=0 and confirm_status=2 and refund_status=0 and is_cancel=0 and supplier_id=".$supplier_id);

	if($balance_total > 0){
	$supplier_info=$GLOBALS['db']->getAll("select balance_price , order_id from ".DB_PREFIX."dc_supplier_order where order_status=0 and confirm_status=2 and refund_status=0 and is_cancel=0 and supplier_id=".$supplier_id);
		$order_id_arr=array();  //结单成功的订单ID
		foreach($supplier_info as $k=>$v){	
			$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set order_status = 1 , balance_time=". NOW_TIME." where order_status=0 and confirm_status=2 and refund_status=0 and is_cancel=0 and id = ".$v['order_id']);
			$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_order set order_status = 1 , balance_time=". NOW_TIME." where order_status=0 and confirm_status=2 and refund_status=0 and is_cancel=0 and order_id = ".$v['order_id']);
			$rs=$GLOBALS['db']->affected_rows();
			if($rs>0){
				$order_id_arr[]=$v['order_id'];
			}	
		}
		$count=count($order_id_arr);
		$info='外卖账单结算'.$count.'笔';
		$total_balance=$GLOBALS['db']->getOne("select sum(balance_price) as balance_price from ".DB_PREFIX."dc_supplier_order where order_id in(".implode(',',$order_id_arr).")");
		dc_modify_supplier_account($total_balance,$supplier_id,3,$info,0);  //商户余额增加
		
	}else{
		$count=0;
	}
	return $count;
}



/**
 * 变更商家账户资金,同时生成商户日报表
 * @param unknown_type $money
 * @param unknown_type $supplier_id 商家ID
 * @param unknown_type $type 0:销售额增加 1:资金冻结 2.待结算增加 3.已结算增加 4.退款增加 5.提现增加
 * $type=2,已不使用
 * @param unknown_type $info 日志内容
 * @param unknown_type $order_id 订单的ID，只有当 $type=0或者 $type=1时，才需要传$order_id这个参数
 *`sale_money` '销售总额',
 *`lock_money` '冻结资金(即已销售，未验证，未收货的金额)',
 *`balance_money` '待结算金额（即每验证，收货一个，增加此金额，同时扣除冻结金额）,
 *`money` '商户余额(可提现余额,已结算金额，结算后，待结算减少，已结算增加)',
 *`refund_money` '已退款金额（退款后增加此金额，同时减少lock_money冻结金额）,
 *`wd_money` '已提现金额：（已提走的金额,提现成功后，增加，同时减少money）';
 */
function dc_modify_supplier_account($money,$supplier_id,$type,$info,$order_id=0)
{
	if($type>=0&&$type<6)
	{
		if($type==2){  //$type=2,已不使用
			return;
		}

		$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = '".$supplier_id."'");
		if($supplier_info)
		{
			$field_array = array('sale_money','lock_money','balance_money','money','refund_money','wd_money');
			//只把外卖的结算金额增加到商户帐户余额中，其他都不加
			if($type==3 && $money>0){
			$GLOBALS['db']->query("update ".DB_PREFIX."supplier set ".$field_array[$type]." = ".$field_array[$type]." + ".floatval($money)." where id =".$supplier_id);
			}
			$date = to_date(NOW_TIME,"Y-m-d");
			$date_month = to_date(NOW_TIME,"Y-m");
			/* 
			 *  1.订单下单完成   营业额增加 ，未完成增加， 营业额=未完成=在线支付+活动补贴+代金劵   type=0
				2.确认收货或者验证成功   未完成减少，已完成增加，待结算增加，佣金增加---冻结资金减少  type=1 , $money<0为解冻资金， $money>0时为增加冻结资金
				3.退款和取消订单 这两者都属于订单未完成时操作，退款和取消订单额增加   type=4
				4.商户结算  待结算减少，已结算增加   余额增加   type=3 ,$money>0为结算,$money<0为提现
			 */
			if($type==0||($type==3&&$money>0)||$type==4||($type==1 && $money<0))
			{
				
				$supplier_stat = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_statements where supplier_id = ".$supplier_id." and stat_time = '".$date."'");
				if($supplier_stat)
				{
					if($type==0){  //订单下单完成   营业额增加 ，未完成增加， 营业额=未完成=在线支付+活动补贴+代金劵
						$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_order where order_id = ".$order_id);
						$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_statements set sale_money = sale_money + ".$order_info['total_price']
								." , unconfirm_money = unconfirm_money + ".$order_info['total_price']
								." , online_pay_money = online_pay_money + ".$order_info['online_pay'] ." + ". $order_info['account_money']
								." , promote_money = promote_money + ".$order_info['promote_amount']
								." , ecv_money = ecv_money + ".$order_info['ecv_money']
								." where supplier_id =".$supplier_id." and stat_time = '".$date."'");
					}elseif($type==1){  //确认收货或者验证成功   未完成减少，已完成增加，待结算增加，佣金增加---冻结资金减少
						$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_order where order_id = ".$order_id);
						if($order_info['payment_id']==0){  //在线支付
						$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_statements set confirm_money = confirm_money + ".$order_info['total_price']
								." , unconfirm_money = unconfirm_money - ".$order_info['total_price']
								." , admin_charges = admin_charges + ".$order_info['total_price'] ." - ". $order_info['balance_price']
								." , unbalance_money = unbalance_money + ".$order_info['balance_price']
								." where supplier_id =".$supplier_id." and stat_time = '".$date."'");	
						}elseif($order_info['payment_id']==1){  //货到付款，佣金,结算都为0
						$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_statements set confirm_money = confirm_money + ".$order_info['total_price']
									." , unconfirm_money = unconfirm_money - ".$order_info['total_price']
									." , admin_charges = admin_charges  + 0 , unbalance_money = unbalance_money + 0 where supplier_id =".$supplier_id." and stat_time = '".$date."'");
							
						}
					}elseif($type==3){  //结算后，商户余额增加，待结算减少，已结算增加 
						$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_statements set balance_money = balance_money + ".floatval($money)
								." , unbalance_money = unbalance_money - ".floatval($money)
								." where supplier_id =".$supplier_id." and stat_time = '".$date."'");
					
					}elseif($type==4){  //退款和取消订单,未完成的订单减少，退款和取消订单额增加 
						$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_order where order_id = ".$order_id);
						$GLOBALS['db']->query("update ".DB_PREFIX."dc_supplier_statements set refund_money = refund_money + ".floatval(abs($money))
								." , unconfirm_money = unconfirm_money - ".$order_info['total_price']
								." where supplier_id =".$supplier_id." and stat_time = '".$date."'");
					}
				}
				else
				{
					
					if($type==0){  //订单下单完成   营业额增加 ，未完成增加， 营业额=未完成=在线支付+活动补贴+代金劵
						$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_order where order_id = ".$order_id);
						$supplier_stat = array();
						$supplier_stat['sale_money'] = $order_info['total_price'];
						$supplier_stat['unconfirm_money'] = $order_info['total_price'];
						$supplier_stat['online_pay_money'] = $order_info['online_pay'] + $order_info['account_money'];
						$supplier_stat['promote_money'] = $order_info['promote_amount'];
						$supplier_stat['ecv_money'] = $order_info['ecv_money'];					
						$supplier_stat['stat_time'] = $date;
						$supplier_stat['stat_month'] = $date_month;
						$supplier_stat['supplier_id'] = $supplier_id;
						$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_statements",$supplier_stat);
					}elseif($type==1){  //确认收货或者验证成功   未完成减少，已完成增加，待结算增加，佣金增加---冻结资金减少
						$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_order where order_id = ".$order_id);

						$supplier_stat = array();
						$supplier_stat['confirm_money'] = $order_info['total_price'];
						$supplier_stat['unconfirm_money'] = "-".$order_info['total_price'];
						
						
						if($order_info['payment_id']==0){  //在线支付
							$supplier_stat['admin_charges'] = $order_info['total_price'] - $order_info['balance_price'];
							$supplier_stat['unbalance_money'] = $order_info['balance_price'];	
						}elseif($order_info['payment_id']==1){ //货到付款		
							$supplier_stat['admin_charges'] = 0;
							$supplier_stat['unbalance_money'] =0;
						}
						
						$supplier_stat['stat_time'] = $date;
						$supplier_stat['stat_month'] = $date_month;
						$supplier_stat['supplier_id'] = $supplier_id;
						$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_statements",$supplier_stat);
					}elseif($type==3){  //结算后，商户余额增加，待结算减少，已结算增加 
						$supplier_stat = array();
						$supplier_stat['balance_money'] = floatval($money);
						$supplier_stat['unbalance_money'] = "-".floatval($money);					
						$supplier_stat['stat_time'] = $date;
						$supplier_stat['stat_month'] = $date_month;
						$supplier_stat['supplier_id'] = $supplier_id;
						$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_statements",$supplier_stat);
					}elseif($type==4){  //退款和取消订单，未完成的订单减少，退款和取消订单额增加 
						$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_supplier_order where order_id = ".$order_id);
						$supplier_stat = array();
						$supplier_stat['refund_money'] = floatval(abs($money));	
						$supplier_stat['unconfirm_money'] = "-".$order_info['total_price'];
						$supplier_stat['stat_time'] = $date;
						$supplier_stat['stat_month'] = $date_month;
						$supplier_stat['supplier_id'] = $supplier_id;
						$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_statements",$supplier_stat);
					}
					
				}
			}
	
			//保存商户资金日志
			dc_supplier_money_log($money,$supplier_id ,$type,$info);
			
			
			/*
			 *  1.订单下单完成   平台营业额增加 ，营业额=在线支付+活动补贴+代金劵   type=0
				2.确认收货或者验证成功   佣金增加  type=1 , $money<0为解冻资金， $money>0时为增加冻结资金
				3.退款和取消订单 这两者都属于订单未完成时操作，退款和取消订单额增加   type=4
				4.商户结算  已结算增加    type=3 ,$money>0为结算,$money<0为提现
			*/
			if($type==0||($type==3&&$money>0)||$type==4||($type==1 && $money<0))
			{
				//当商家余额增加时，即表示结算
				dc_modify_statements($money, $type, $info,$order_id);
			}

		}
	}
}

/**
 * 商户外卖帐户日志
 * @param unknown_type $supplier_id  商户ID
 * @param unknown_type $money       金额
 * @param unknown_type $type  类型 0:销售额增加 1:资金冻结 2.待结算增加 3.已结算增加 4.退款增加 5.提现增加
 */
function dc_supplier_money_log($money,$supplier_id ,$type,$info){
	$log_data = array();
	$log_data['log_info'] = $info;
	$log_data['supplier_id'] = $supplier_id;
	$log_data['create_time'] = NOW_TIME;
	$log_data['money'] = floatval($money);
	$log_data['type'] = $type;

	if($type==3 && $money>0){  //外卖结算到商户余额时，保存记录
		$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_money_log",$log_data);
	}
	    //外卖自已的商户日志
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_supplier_money_log",$log_data);
}


/**
 * 变更平台外卖财务报表
 * @param unknown_type $money
 * @param unknown_type $type 0:销售额增加 1:资金冻结 2.待结算增加 3.已结算增加 4.退款增加 5.提现增加
 * @param unknown_type $info 日志内容
 * @param unknown_type $order_id 订单的ID，只有当 $type=0或者 $type=1时，才需要传$order_id这个参数
 `order_num` '订单数',
 `sale_money` '营业额',
 `balance_money` '结算额',
 `online_pay_money` '在线支付额',
 `promote_money` '活动补贴',
 `ecv_money` '代金劵',
 `refund_money` '退款,取消订单金额',
 `admin_charges` '佣金',
 */
function dc_modify_statements($money,$type,$info,$order_id=0)
{
		/*
		 *  1.订单下单完成   平台营业额增加 ，营业额=在线支付+活动补贴+代金劵   type=0
			2.确认收货或者验证成功   佣金增加  type=1 , $money<0为解冻资金， $money>0时为增加冻结资金
			3.退款和取消订单 这两者都属于订单未完成时操作，退款和取消订单额增加   type=4
			4.商户结算  已结算增加    type=3 ,$money>0为结算,$money<0为提现
		*/
		if($type==0||($type==3&&$money>0)||$type==4||($type==1 && $money<0))
		{

		$stat_time = to_date(NOW_TIME,"Y-m-d");
		$stat_month = to_date(NOW_TIME,"Y-m");
		$state_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_statements where stat_time = '".$stat_time."'");
		if($state_data)
		{
			if($type==0){  //订单下单完成   平台营业额增加 ，营业额=在线支付+活动补贴+代金劵   type=0
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_statements set order_num = order_num + 1 , sale_money = sale_money + ".$order_info['total_price']
						." , online_pay_money = online_pay_money + ".$order_info['online_pay'] ." + ". $order_info['account_money']
						." , promote_money = promote_money + ".$order_info['promote_amount']
						." , ecv_money = ecv_money + ".$order_info['ecv_money']
						." where stat_time = '".$stat_time."'");
			}elseif($type==1){  //确认收货或者验证成功   佣金增加  type=1 , $money<0为解冻资金， $money>0时为增加冻结资金
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_statements set admin_charges = admin_charges + ".$order_info['total_price'] ." - ". $order_info['balance_price'] ." where stat_time = '".$stat_time."'");
			}elseif($type==3){  //商户结算  已结算增加    type=3 ,$money>0为结算,$money<0为提现
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_statements set balance_money = balance_money + ".floatval($money)	." where stat_time = '".$stat_time."'");
					
			}elseif($type==4){  //退款和取消订单 这两者都属于订单未完成时操作，退款和取消订单额增加   type=4
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_statements set refund_money = refund_money + ".floatval($money) ." where stat_time = '".$stat_time."'");
			}
			$rs = $GLOBALS['db']->affected_rows();
		}
		else
		{
			
			if($type==0){  //订单下单完成   平台营业额增加 ，营业额=在线支付+活动补贴+代金劵   type=0
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
				$supplier_stat = array();
				$supplier_stat['order_num'] = 1;
				$supplier_stat['sale_money'] = $order_info['total_price'];
				$supplier_stat['online_pay_money'] = $order_info['online_pay'] + $order_info['account_money'];
				$supplier_stat['promote_money'] = $order_info['promote_amount'];
				$supplier_stat['ecv_money'] = $order_info['ecv_money'];
				$supplier_stat['stat_time'] = $stat_time;
				$supplier_stat['stat_month'] = $stat_month;
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_statements",$supplier_stat);
			}elseif($type==1){  //确认收货或者验证成功   佣金增加  type=1 , $money<0为解冻资金， $money>0时为增加冻结资金
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
				$supplier_stat = array();
				$supplier_stat['admin_charges'] = $order_info['total_price'] - $order_info['balance_price'];
				$supplier_stat['stat_time'] = $stat_time;
				$supplier_stat['stat_month'] = $stat_month;
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_statements",$supplier_stat);
			}elseif($type==3){  //商户结算  已结算增加    type=3 ,$money>0为结算,$money<0为提现
				$supplier_stat = array();
				$supplier_stat['balance_money'] = floatval($money);
				$supplier_stat['stat_time'] = $stat_time;
				$supplier_stat['stat_month'] = $stat_month;
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_statements",$supplier_stat);
			}elseif($type==4){  //退款和取消订单 这两者都属于订单未完成时操作，退款和取消订单额增加   type=4
				$supplier_stat = array();
				$supplier_stat['refund_money'] = floatval($money);
				$supplier_stat['stat_time'] = $stat_time;
				$supplier_stat['stat_month'] = $stat_month;
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_statements",$supplier_stat);
			}
			$rs = $GLOBALS['db']->insert_id();
		}

		if($rs)
		{
			$log_data = array();
			$log_data['log_info'] = $info;
			$log_data['create_time'] = NOW_TIME;
			$log_data['money'] = floatval($money);
			$log_data['type'] = $type;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_statements_log",$log_data);
		}

	}
}

//新订单发送短信通知商户
function dc_send_supplier_order($supplier_id,$order_id)
{

		$order_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
		if($order_data)
		{
			$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = ".$supplier_id);
			$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where supplier_id = ".$supplier_id." and is_main = 1");

				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_SUPPLIER_ORDER'");
				$tmpl_content = $tmpl['content'];

				$GLOBALS['tmpl']->assign("supplier_name",$supplier_info['name']);
				$GLOBALS['tmpl']->assign("order_sn",$order_data['order_sn']);
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['send_type'] = 0;
				$msg_data['dest'] = $account['mobile'];
			
				$msg_data['content'] = addslashes($msg);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = NOW_TIME;
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入

				if(app_conf("SMS_ON")==1&&app_conf("SUPPLIER_ORDER_NOTIFY")==1 && $account['mobile'])
				{
				
					if(APP_INDEX!="index")
					{
						
						$msg_data['id'] = $GLOBALS['db']->insert_id();
					
						send_msg_item($msg_data);
					}
					
				}

				$msg_data['id']=0;
				if( !empty($account['dev_type']) && !empty($account['device_token'])){
					if ($account['dev_type'] == 'ios'){
						$msg_data['send_type'] = 4;//发送类型 0:短信 1:邮件;2:微信;3:android,4:ios
					}else{
						$msg_data['send_type'] = 3;//发送类型 0:短信 1:邮件;2:微信;3:android,4:ios
					}
					$msg_data['dest'] = $account['device_token'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				
					if(APP_INDEX!="index")
					{
						$msg_data['id'] = $GLOBALS['db']->insert_id();
						send_msg_item($msg_data);
					}
				}

		}
	
}

/**
 * 获取餐单页正在经营的餐厅id
 * @param unknown_type $orderby 排列条件
 * @param unknown_type $append_field 额外查询内容
 */
function get_dc_location_id($type='is_dc',$orderby = '',$append_field='')
{


	$tname='sl';

	if($type=='is_dc'){
		
		$condition =$tname.'.is_dc=1 and '.$tname.".city_id=".$GLOBALS['city']['id'];


		$sql = "select aa.id from (select ".$tname.".*".$append_field." from ".DB_PREFIX."supplier_location as ".$tname." where  ".$condition." ) aa where aa.distance <= aa.max_delivery_scale or aa.max_delivery_scale=0";
		
		
	}elseif($type=='is_res'){
		
		$condition =$tname.'.is_reserve=1 and '.$tname.".city_id=".$GLOBALS['city']['id'];

		$sql = "select aa.id from (select ".$tname.".*".$append_field." from ".DB_PREFIX."supplier_location as ".$tname." where  ".$condition." ) aa ";
		
		
	}

	$condition2=$sql;
	if($orderby=='')
		$sql.=" order by aa.is_close , aa.id desc";
	else
		$sql.=" order by aa.is_close , ".$orderby;

	$dc_id_list=$GLOBALS['db']->getAll($sql,false);
	
	
	return array('list'=>$dc_id_list);
}



/**
 * region_id      //配送最终地区
 * delivery_id    //配送方式
 * payment        //支付ID
 * account_money  //支付余额
 * all_account_money  //是否全额支付
 * ecvsn  //代金券帐号
 * ecvpassword  //代金券密码
 * goods_list   //统计的商品列表
 * $paid_account_money 已支付过的余额
 * $paid_ecv_money 已支付过的代金券
 *
 * 返回 array(
 'total_price'	=>	$total_price,	商品总价
 'pay_price'		=>	$pay_price,     支付费用
 'pay_total_price'		=>	$total_price+$delivery_fee+$payment_fee-$user_discount,  应付总费用
 'delivery_fee'	=>	$delivery_fee,  运费
 'delivery_info' =>  $delivery_info, 配送方式
 'payment_fee'	=>	$payment_fee,   支付手续费
 'payment_info'  =>	$payment_info,  支付方式
 'user_discount'	=>	$user_discount, 会员折扣
 'account_money'	=>	$account_money, 余额支付
 'ecv_money'		=>	$ecv_money,		代金券金额
 'ecv_data'		=>	$ecv_data,      代金券数据
 'region_info'	=>	$region_info,	地区数据
 'is_delivery'	=>	$is_delivery,   是否要配送
 'return_total_score'	=>	$return_total_score,   购买返积分
 'return_total_money'	=>	$return_total_money    购买返现
 'buy_type'	=>	0,1 //1为积分商品

 */
function dc_count_buy_total($location_id,$location_dc_table_cart,$location_dc_cart,$payment_id,$dc_type,$consignee_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword, $bank_id,$paid_account_money,$paid_ecv_money,$paid_promote_amount){
	
	$total_price=0;
	$pay_price=0;
	$delivery_fee=0;
	$final_total_price=0;
	$payment_fee=0;
	$package_fee=0;
	$ecv_use_money=0;
	
	if($payment_id==1 || $dc_type==1){
		$account_money=0;
		$all_account_money=0;
		$ecvsn='';
		$ecvpassword=='';
	}
	

	//global_run();
	require_once APP_ROOT_PATH."system/model/dc.php";
	$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;

	// 外卖情况,货到付款
	if($location_dc_table_cart['total_data']['total_price']==0 && $location_dc_cart['total_data']['total_price']>0){
	
		
		$consignee_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_consignee where id=".$consignee_id);

		$xpoint = $consignee_info['xpoint'];
		$ypoint = $consignee_info['ypoint'];

		$tname='sl';
		/* 配送费  */
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = "(ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r)/1000 as distance ";

			$distance=$GLOBALS['db']->getOne("select ".$field_append." from ".DB_PREFIX."supplier_location as ". $tname." where ".$tname.".id=".$location_id);

			require_once APP_ROOT_PATH."system/model/dc.php";
				
			$id_arr=array($location_id=>array('id'=>$location_id,'distance'=>$distance));
			$location_delivery_info=get_location_delivery_info($id_arr);
			$location_delivery=array();
			foreach($location_delivery_info as $kk=>$vv){
				$location_delivery=$vv;
			}
				
			//$delivery_fee配送费
			
			if($location_delivery['is_free_delivery']==0){
				$delivery_fee=$location_delivery['delivery_price']?$location_delivery['delivery_price']:0;
			}
			


		}
		/*  打包费计算*/
		$location_package_conf=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_package_conf where location_id=".$location_id);
		$dc_package_info=array();

		if($location_package_conf['package_start_price']==0){

			$dc_package_info['is_free_package']=1;
		}elseif($location_package_conf['package_start_price']==-1){
			$dc_package_info['is_free_package']=0;
			$dc_package_info['package_price']=$location_package_conf['package_price'];
			$dc_package_info['total_package_price']=$dc_package_info['package_price']*$location_dc_cart['total_data']['total_count'];
		}elseif($location_package_conf['package_start_price']>0){

			if($location_dc_cart['total_data']['total_price']>=$location_package_conf['package_start_price']){
				$dc_package_info['is_free_package']=1;
			}else{
				$dc_package_info['is_free_package']=0;
				$dc_package_info['package_price']=$location_package_conf['package_price'];
				$dc_package_info['total_package_price']=$dc_package_info['package_price']*$location_dc_cart['total_data']['total_count'];
			}

		}
		//$package_fee打包费
		$package_fee=isset($dc_package_info['total_package_price'])?$dc_package_info['total_package_price']:0;

	}

	/*总金额计算*/
	if($location_dc_table_cart['total_data']['total_count']==0 && $location_dc_cart['total_data']['total_count']>0){
		/*只点菜*/
		$final_total_price=$location_dc_cart['total_data']['total_price']+$package_fee+$delivery_fee;
		//$total_price商品总价
		$total_price=$location_dc_cart['total_data']['total_price'];
	}elseif($location_dc_table_cart['total_data']['total_count']>0 && $location_dc_cart['total_data']['total_count']==0){
		/*只预订餐桌*/
		//$total_price预订定金
		$total_price=$final_total_price=$location_dc_table_cart['total_data']['total_price'];
	}else{
		/*既有预订餐桌也有点菜，这里点菜的金额必须超过预订餐桌的定金，总金额为点菜金额*/
		$total_price=$final_total_price=$location_dc_cart['total_data']['total_price'];
	}

	$pay_price=$final_total_price;
	
	$pay_price = $pay_price - $paid_account_money - $paid_ecv_money - $paid_promote_amount;
	
	//开始计算代金券
	$now = NOW_TIME;
	$ecv_sql = "select e.* from ".DB_PREFIX."ecv as e left join ".
			DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.sn = '".
			$ecvsn ."' and ((e.begin_time <> 0 and e.begin_time < ".$now.") or e.begin_time = 0) and ".
			"((e.end_time <> 0 and e.end_time > ".$now.") or e.end_time = 0) and ((e.use_limit <> 0 and e.use_limit > e.use_count) or (e.use_limit = 0)) ".
			"and (e.user_id = ".$user_id." or e.user_id = 0)";
	$ecv_data = $GLOBALS['db']->getRow($ecv_sql);
	$ecv_use_money = $ecv_data['money'];
	
	//$payment_id==0,代表在线支付，参与促销活动
	if($payment_id==0){

	if($all_account_money==1 && ($account_money+$ecv_use_money >= $pay_price)){
		$payment=0;
	
	}
	//支付手续费
	if($payment!=0)
	{	
		if($pay_price>0 && $account_money < $pay_price && $ecv_use_money < $pay_price)
		{
			$payment_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where id = ".$payment);
			$directory = APP_ROOT_PATH."system/dc_payment/";
			$file = $directory. '/Dc_' .$payment_info['class_name']."_payment.php";
			if(file_exists($file))
			{
				require_once($file);
				$payment_class = 'Dc_'.$payment_info['class_name']."_payment";
				$payment_object = new $payment_class();
				if(method_exists($payment_object,"get_name"))
				{
					$payment_info['name'] = $payment_object->get_name($bank_id);
				}
			}
	
	
				
			if($payment_info['fee_type']==0) //定额
			{
				$payment_fee = $payment_info['fee_amount'];
			}
			else //比率
			{
				$payment_fee = $pay_price * $payment_info['fee_amount'];
			}
			$pay_price = $pay_price + $payment_fee;
		}
	}
	else
	{
		$payment_fee = 0;
	}
	$final_total_price+=$payment_fee;
	


	

	$tmp_pay_price=$pay_price;
	$pay_price = $pay_price - $ecv_use_money;
	if($pay_price >= 0){
	
		//余额支付
		$user_money = $GLOBALS['db']->getOne("select money from ".DB_PREFIX."user where id = ".$user_id);
		if($all_account_money == 1)
		{
			if($pay_price > $user_money ){
				$account_money=$user_money;
			}else{
				$account_money=$pay_price;
			}
		}
		$ecv_pass=0;

	}else{
		//使用超过应付金额的代金劵，代金劵剩余部分不退还
		$account_money=0;
		$ecv_use_money=$tmp_pay_price;
		$pay_price=0;
		$ecv_pass=1;
	}
	$tmp_pay_price_xx=$pay_price;
	$pay_price = $pay_price - $account_money;
	
	if($pay_price < 0){
		$account_money=$tmp_pay_price_xx;
		$pay_price=0;
	}
	$ecv_use_money=$ecv_use_money?$ecv_use_money:0;
	$result = array(
			'total_price'	=>	$total_price,  //商品总价
			'pay_price'		=>	$pay_price,   //应付金额
			'pay_total_price'		=>	$final_total_price,  //总价
			'delivery_fee'	=>	$delivery_fee,
			'payment_fee'	=>	$payment_fee,
			'payment_info'  =>	$payment_info,
			'package_fee'   =>	$package_fee,
			'account_money'	=>	$account_money,
			'ecv_money'		=>	$ecv_use_money,
			'ecv_data'		=>	$ecv_data,
			'ecv_pass'		=>	$ecv_pass,
			'paid_account_money'	=>	$paid_account_money,
			'paid_ecv_money'	=>	$paid_ecv_money,
			'paid_promote_amount'	=>	$paid_promote_amount,

	);
	
	//$dc_type大等于0为预订方式，不享受促销优惠，-1代表享受促销优惠,$paid_promote_amount>0时，代表已经享受过优惠，不再享受优惠
		if($dc_type==-1 && $paid_promote_amount ==0){
			$promote_list = load_auto_cache("cache_dc_promote");
			if(count($promote_list)>0){
				//以下对促销接口进行实现
				$sl_promote=$GLOBALS['db']->getRow("select is_firstorderdiscount,is_payonlinediscount from ".DB_PREFIX."supplier_location where id=".$location_id);
				$is_ordered=$GLOBALS['db']->getOne("select dc_is_share_first from ".DB_PREFIX."user where id=".$user_id);
				//判断是否是首单
				$user_order_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_order where user_id=".$user_id);
				
				$is_installed_first=0;
			
					foreach($promote_list as $kk=>$vv){
						if($vv['class_name']=='FirstOrderDiscount'){  //判断是否安装首单立减促销规则
							$is_installed_first=1;  
							break;
						}
						
					}
					$promote_list_new=array(); //首单立减促销规则最高，不与其他促销规则叠加
					if($sl_promote['is_firstorderdiscount']==1 && $is_ordered==0 && $is_installed_first==1 && $user_order_count==0){
						$promote_list_new['FirstOrderDiscount']=$promote_list['FirstOrderDiscount'];
					}elseif($sl_promote['is_payonlinediscount']==1){
						unset($promote_list['FirstOrderDiscount']);
						$promote_list_new=$promote_list;
					}
					
							foreach($promote_list_new as $k=>$v)
							{
								$directory = APP_ROOT_PATH."system/dc_promote/";
								$file = $directory. '/' .$v['class_name']."_promote.php";
								if(file_exists($file))
								{
									require_once($file);
									$promote_class = $v['class_name']."_promote";
									$promote_object = new $promote_class();
									$result = $promote_object->dc_count_buy_total(
											$location_id,
											$user_id,
											$payment_id,
											$payment,
											$account_money,
											$all_account_money,
											$ecvsn,
											$ecvpassword,
											$result,
											$result);
						
								}
						
							}
					
			}
		}
		$result['location_delivery_info']=$location_delivery;
		$result['dc_package_info']=$dc_package_info;
	}else{
	
		$result = array(
				'total_price'	=>	$total_price,  //商品总价
				'pay_price'		=>	$pay_price,   //应付金额
				'pay_total_price'		=>	$final_total_price,  //总价
				'delivery_fee'	=>	$delivery_fee,
				'payment_fee'	=>	$payment_fee,
				//'payment_info'  =>	$payment_info,
				'package_fee'   =>	$package_fee,
				'account_money'	=>	$account_money,
				'ecv_money'		=>	$ecv_use_money,
				'paid_account_money'	=>	$paid_account_money,
				'paid_ecv_money'	=>	$paid_ecv_money,
				'paid_promote_amount'	=>	$paid_promote_amount,
				'location_delivery_info'=>	$location_delivery,
				'dc_package_info'=>$dc_package_info,
		
		);
	}	
		return $result;

}


/**
 * $location_id      //门店id
 * $date        	//日期
 * $item_id  		//项目id
 * $table_time_id  //项目时间段id
 * $total_count  //项目时间段库存
*/	
	
function item_day($location_id,$date,$item_id,$table_time_id,$total_count){
		
		$item_day_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_day where time_id = ".$table_time_id." and item_id = ".$item_id." and rs_date = '".$date."' and location_id = ".$location_id);
		
		if(!$item_day_info)
		{
			
			return true;
		}
		else
		{
			if($item_day_info['buy_count']<$total_count)
			{
				
				return true;
			}
			else
			{
				return false;
			}
				
		}
		
}



function biz_check_dcverify($s_account_info,$sn,$location_id)
{	
	if(intval($s_account_info['id'])==0)
	{
		$result['status'] = 0;
		$result['msg'] = $GLOBALS['lang']['SUPPLIER_NOT_LOGIN'];
		return $result;
	}

	$now = NOW_TIME;
	$dcverify_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_coupon where sn = '".$sn."'");
	if($dcverify_data)
	{		
	    if (!in_array($location_id,$s_account_info['location_ids'])){
	        $result['status'] = 0;
			$result['msg'] = "没有门店权限验证该优惠券";
			return $result;
	    }
		$sql = "select * from ".DB_PREFIX."dc_order where sn_id = ".$dcverify_data['id']." and location_id =".$location_id;
		$order_info = $GLOBALS['db']->getRow($sql);
		if(!$order_info)
		{	
			$result['status'] = 0;
			$result['msg'] = $GLOBALS['lang']['NO_AUTH'];
			return $result;
		}
		if($dcverify_data['end_time']>0 && $dcverify_data['end_time'] < $now){
		    $result['status'] = 0;
		    $result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_END'],to_date($dcverify_data['end_time']));
		    return $result;
		}
		if($dcverify_data['is_used']>0&&$dcverify_data['confirm_time']>0)
		{
			$result['status'] = 0;
			$result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_USED'],to_date($dcverify_data['confirm_time']));
			return $result;
		}
		if($order_info['is_cancel']>0 || $order_info['refund_status'] >0 ){
			$result['status'] = 0;
			$result['msg'] = "此订单已关闭";
			return $result;
		}
		else
		{		$order_menu=unserialize($order_info['order_menu']);
				
				$rs_list=$order_menu['rs_list'];
				$cart_list=$rs_list['cart_list'];
				
				 foreach($cart_list as $kc=>$vc){ //定餐桌时间
					

					$order_info['table_time_format']=strip_tags($vc['table_time_format']);
					$order_info['table_name']=$vc['name'];
				}	
					$result['status'] = 1;
					$result['data'] = $order_info;
					$result['location_id'] = $location_id;
					$result['youhui_sn'] = $sn;
					$result['msg'] = $order_info['location_name']."[".$order_info['table_name'].",".$order_info['table_time_format']."]电子券".$GLOBALS['lang']['IS_VALID_YOUHUI'];
	
		}
	}
	else
	{
		$result['status'] = 0;
		$result['msg'] = "电子券序列号不存在";
	}
	return $result;
}

function biz_use_dcverify($s_account_info,$sn,$location_id)
{		
	if(intval($s_account_info['id'])==0)
	{
		$result['status'] = 0;
		$result['msg'] = $GLOBALS['lang']['SUPPLIER_NOT_LOGIN'];
		return $result;
	}

	$now = NOW_TIME;
	$dcverify_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_coupon where sn = '".$sn."'");
	
	
	if($dcverify_data)
	{		
	    if (!in_array($location_id,$s_account_info['location_ids'])){
	        $result['status'] = 0;
			$result['msg'] = "没有门店权限验证该优惠券";
			return $result;
	    }
		$sql = "select * from ".DB_PREFIX."dc_order where sn_id = ".$dcverify_data['id']." and location_id =".$location_id;
		$order_info = $GLOBALS['db']->getRow($sql);
		if(!$order_info)
		{	
			$result['status'] = 0;
			$result['msg'] = $GLOBALS['lang']['NO_AUTH'];
			return $result;
		}
		if($dcverify_data['end_time']>0 && $dcverify_data['end_time'] < $now){
		    $result['status'] = 0;
		    $result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_END'],to_date($dcverify_data['end_time']));
		    return $result;
		}
		if($dcverify_data['is_used']>0&&$dcverify_data['confirm_time']>0)
		{
			$result['status'] = 0;
			$result['msg'] = sprintf($GLOBALS['lang']['YOUHUI_HAS_USED'],to_date($dcverify_data['confirm_time']));
			return $result;
		}
		if($order_info['is_cancel']>0){
			  $result['status'] = 0;
			$result['msg'] = "此订单已关闭";
		}
		
		$begin_time=$dcverify_data['end_time']-3600*24;
		//判断是否是当天消费
		if($dcverify_data['end_time']>0 && $begin_time > $now){
		    $result['status'] = 0;
		    $result['msg'] = '未到消费时间，请'.to_date($dcverify_data['end_time'],'Y-m-d').'来消费';
		    return $result;
		}
		else
		{				
			
				$order_menu=unserialize($order_info['order_menu']);
				
				$rs_list=$order_menu['rs_list'];
				$cart_list=$rs_list['cart_list'];
				
			 foreach($cart_list as $kc=>$vc)
			 { //定餐桌时间
			
			$order_info['table_time_format']=strip_tags($vc['table_time_format']);
			$order_info['table_name']=$vc['name'];
			}	
					$result['status'] = 1;
					$result['data'] = $order_info;
					$result['location_id'] = $location_id;
					$result['youhui_sn'] = $sn;
				//	$GLOBALS['db']->query("update ".DB_PREFIX."dc_coupon set is_used=1,confirm_time=".NOW_TIME." where sn = '".$sn."' and location_id = '".$location_id."'");
				//	$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set order_status=1,confirm_status=2 where sn_id = '".$dcverify_data['id']."' and location_id = '".$location_id."'");
					 dc_confirm_delivery($order_info['id']);
					$result['msg'] = $order_info['location_name']."[".$order_info['table_name'].",".$order_info['table_time_format']."]电子券使用成功。\n 使用时间为：".to_date($now);
				
		}
	}
	else
	{
		$result['status'] = 0;
		$result['msg'] = "电子券序列号不存在";
	}
	return $result;
}



/**
 * 后台订单删除变为历史类型
 * 
*/
function dc_del_order($order_id)
{
	$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id." and (order_status = 1 or is_cancel > 0 or refund_status > 1)");
	if($order_info)
	{
		unset($order_info['id']);
		
		$order_info['history_dc_coupon'] = serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_coupon where order_id = ".$order_id));
		$order_info['history_dc_order_menu'] =  serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_order_menu where order_id = ".$order_id));
		$payment_notice=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment_notice where order_id = ".$order_id." and order_type = 1");
		foreach($payment_notice as $k=>$v){
			$payment_notice[$k]['name']=$GLOBALS['db']->getOne("select name from ".DB_PREFIX."payment where id = ".$v['payment_id']);
		}
		$order_info['history_payment_notice'] = serialize($payment_notice);
		$order_info['history_dc_order_log'] =  serialize($GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_order_log where order_id = ".$order_id));
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_order_history",$order_info,'INSERT','','SILENT');
		if($GLOBALS['db']->insert_id())
		{
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_order where id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_order_menu where order_id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_coupon where order_id = ".$order_id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."payment_notice where order_id = ".$order_id." and order_type = 1");
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_order_log where order_id = ".$order_id);
			

			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}




/**
 *
 * @param array $order_info  订单信息，数组
 * @param string $type ，当$type等于wap时，输出wap需要的数据，否则输出app端需要的数据
 * @param string $page ，是什么页面，有两个页面，一个是index:订单列表页，一个是view:订单详细页
 * @return  $order_state返回订单的状态
 *  1、待支付
	2、待接单
	3、已接单
	4、已完成，未点评
	5、订单关闭
	6.退款申请中
	7.已退款
	8.退款驳回
	9.已点评
	10.支付超时
 */

function get_order_state($order_info,$type='',$page=''){

	$order_state=array();
	if($order_info['is_rs']==0){
		$module='dc_dcorder';
		$other_link=wap_url('index','dc');	
	}else{
		$module='dc_rsorder';
		$other_link=wap_url('index','dcres');
	}

	if($order_info['is_cancel'] > 0){
		$order_state['state']=5;
		$order_state['state_format']='订单关闭';
		if($type=='wap'){
			if($page=='view'){
				$act=array();
				$act['name']='去别家看看';
				$act['url']=$other_link;
				$act['is_a']=1;
				$order_state['act'][]=$act;
			}
				
		}

	}elseif($order_info['refund_status'] == 1){
		$order_state['state']=6;
		$order_state['state_format']='退款申请中';
		if($type=='wap'){
			if($page=='view'){
				$act=array();
				$act['name']='去别家看看';
				$act['url']=$other_link;
				$act['is_a']=1;
				$order_state['act'][]=$act;
			}
		
		}

	}elseif($order_info['refund_status'] == 2){
		$order_state['state']=7;
		$order_state['state_format']='已退款';
		if($type=='wap'){
			if($page=='view'){
				$act=array();
				$act['name']='去别家看看';
				$act['url']=$other_link;
				$act['is_a']=1;
				$order_state['act'][]=$act;
			}
		
		}

	}elseif($order_info['refund_status'] ==3){
		$order_state['state']=8;
		$order_state['state_format']='退款驳回';
		if($type=='wap'){
			if($page=='view'){
				$act=array();
				$act['name']='去别家看看';
				$act['url']=$other_link;
				$act['is_a']=1;
				$order_state['act'][]=$act;
			}
		
		}

	}else{
		if($order_info['payment_id']==0){  //在线支付
				
			if($order_info['pay_status']==0){
				$time=900;//15分钟支付超时
				if(NOW_TIME-$order_info['create_time'] < $time ){
					$order_state['state']=1;
					$order_state['state_format']='待支付';
					
					if($type=='wap'){
						$act=array();
						$act['name']='继续支付';
						$act['url']=wap_url('index','dcorder#order',array('id'=>$order_info['id']));
						$act['is_a']=1;
						$order_state['act'][]=$act;
						if($page=='view'){
							$act=array();
							$act['name']='取消订单';
							$act['url']=wap_url('index',$module.'#cancel',array('id'=>$order_info['id']));
							$act['is_a']=0;
							$order_state['act'][]=$act;
						}
							
					}
				}else{
					$order_state['state']=10;
					$order_state['state_format']='支付超时';
						
					if($type=='wap'){
						if($page=='view'){
							$act=array();
							$act['name']='取消订单';
							$act['url']=wap_url('index',$module.'#cancel',array('id'=>$order_info['id']));
							$act['is_a']=0;
							$order_state['act'][]=$act;
						}
							
					}
					
				}

				
			}else{
				if($order_info['confirm_status']==0){
					$order_state['state']=2;
					$order_state['state_format']='待接单';
					
					if($type=='wap'){

						if($page=='view'){
							if($order_info['is_rs']==0){
							$act=array();
							$act['name']='催单';
							$act['url']=wap_url('index',$module.'#dc_reminder',array('id'=>$order_info['id']));
							$act['is_a']=0;
							$order_state['act'][]=$act;
							}
							$act=array();
							$act['name']='取消订单';
							$act['url']=wap_url('index',$module.'#cancel',array('id'=>$order_info['id']));
							$act['is_a']=0;
							$order_state['act'][]=$act;
						}	
					}

				}elseif($order_info['confirm_status']==1){
					$order_state['state']=3;
					$order_state['state_format']='已接单';
					if($type=='wap'){
					
						if($page=='view'){
							if($order_info['is_rs']==0){
								$act=array();
								$act['name']='催单';
								$act['url']=wap_url('index',$module.'#dc_reminder',array('id'=>$order_info['id']));
								$act['is_a']=0;
								$order_state['act'][]=$act;
								$act=array();
								$act['name']='确认完成';
								$act['url']=wap_url('index',$module.'#verify_delivery',array('id'=>$order_info['id']));
								$act['is_a']=0;
								$order_state['act'][]=$act;	
							}
							$act=array();
							$act['name']='取消订单';
							$act['url']=wap_url('index',$module.'#cancel',array('id'=>$order_info['id']));
							$act['is_a']=0;
							$order_state['act'][]=$act;
						}
							
					}

				}elseif($order_info['confirm_status']==2 && $order_info['is_dp']==0){
					$order_state['state']=4;
					$order_state['state_format']='已完成';
					if($type=='wap'){
							if($order_info['is_dp']==0){
								$act=array();
								$act['name']='点评';
								$act['url']=wap_url('index','dcreview',array('id'=>$order_info['id']));
								$act['is_a']=1;
								$order_state['act'][]=$act;
							}		
					}

				}elseif($order_info['confirm_status']==2 && $order_info['is_dp']==1){
					$order_state['state']=9;
					$order_state['state_format']='已点评';

				}
			}
		}elseif($order_info['payment_id']==1){  //货到付款
			if($order_info['confirm_status']==0){
				$order_state['state']=2;
				$order_state['state_format']='待接单';
				if($type=='wap'){
				
					if($page=='view'){
						if($order_info['is_rs']==0){
							$act=array();
							$act['name']='催单';
							$act['url']=wap_url('index',$module.'#dc_reminder',array('id'=>$order_info['id']));
							$act['is_a']=0;
							$order_state['act'][]=$act;
						}
						$act=array();
						$act['name']='取消订单';
						$act['url']=wap_url('index',$module.'#cancel',array('id'=>$order_info['id']));
						$act['is_a']=0;
						$order_state['act'][]=$act;
					}
				}
			}elseif($order_info['confirm_status']==1){
				$order_state['state']=3;
				$order_state['state_format']='已接单';
				if($type=='wap'){
						
					if($page=='view'){
						if($order_info['is_rs']==0){
							$act=array();
							$act['name']='催单';
							$act['url']=wap_url('index',$module.'#dc_reminder',array('id'=>$order_info['id']));
							$act['is_a']=0;
							$order_state['act'][]=$act;
							$act=array();
							$act['name']='确认完成';
							$act['url']=wap_url('index',$module.'#verify_delivery',array('id'=>$order_info['id']));
							$act['is_a']=0;
							$order_state['act'][]=$act;
						}
						$act=array();
						$act['name']='取消订单';
						$act['url']=wap_url('index',$module.'#cancel',array('id'=>$order_info['id']));
						$act['is_a']=0;
						$order_state['act'][]=$act;
					}
						
				}
				
				
			}elseif($order_info['confirm_status']==2 && $order_info['is_dp']==0){
				$order_state['state']=4;
				$order_state['state_format']='已完成';
				if($type=='wap'){
					if($order_info['is_dp']==0){
						$act=array();
						$act['name']='点评';
						$act['url']=wap_url('index','dcreview',array('id'=>$order_info['id']));
						$act['is_a']=1;
						$order_state['act'][]=$act;
					}
				}
				
				
			}elseif($order_info['confirm_status']==2 && $order_info['is_dp']==1){
					$order_state['state']=9;
					$order_state['state_format']='已点评';

			}
				
		}


	}
	return $order_state;
}



/**
 * 商家端订单状态和操作动作
 * @param array $order_info  订单信息，数组
 * @param string $type ，当$type等于wap时，输出wap需要的数据，否则输出app端需要的数据
 * @param string $page ，是什么页面，有两个页面，一个是index:订单列表页，一个是view:订单详细页
 * @return  $order_state返回订单的状态
 *  1、待支付
 2、待接单
 3、已接单
 4、已完成，未点评
 5、订单关闭
 6.退款申请中
 7.已退款
 8.退款驳回
 9.已点评
 */

function get_biz_order_state($order_info,$type='mapi'){

	$order_state=array();
	if($order_info['is_rs']==0){
		$module='dc_biz_order';
	}else{
		$module='dc_biz_resorder';
	}

	if($order_info['is_cancel'] > 0){
		$order_state['state']=5;
		$order_state['state_format']='订单关闭';

	}elseif($order_info['refund_status'] == 1){
		$order_state['state']=6;
		$order_state['state_format']='退款申请中';

	}elseif($order_info['refund_status'] == 2){
		$order_state['state']=7;
		$order_state['state_format']='已退款';

	}elseif($order_info['refund_status'] ==3){
		$order_state['state']=8;
		$order_state['state_format']='退款驳回';

	}else{
		if($order_info['payment_id']==0){  //在线支付

			if($order_info['pay_status']==0){
				$order_state['state']=1;
				$order_state['state_format']='待支付';

				if($type=='wap'){
						$act=array();
						$act['name']='关闭订单';
						$act['url']=wap_url('index',$module.'#close_order',array('id'=>$order_info['id']));
						$act['has_reason']=1;
						$order_state['act'][]=$act;
						
				}else{
					$act=array();
					$act['name']='关闭订单';
					$act['ctl']=$module;
					$act['act']='close_order';
					$act['has_reason']=1;
					$act['id']=$order_info['id'];
					$order_state['act'][]=$act;
				}

			}else{
				if($order_info['confirm_status']==0){
					$order_state['state']=2;
					$order_state['state_format']='待接单';
						
					if($type=='wap'){

							$act=array();
							$act['name']='接单';
							$act['url']=wap_url('index',$module.'#accept_order',array('id'=>$order_info['id']));
							$order_state['act'][]=$act;
							
							$act=array();
							$act['name']='关闭订单';
							$act['url']=wap_url('index',$module.'#close_order',array('id'=>$order_info['id']));
							$act['has_reason']=1;
							$order_state['act'][]=$act;
						
					}else{
						
						$act=array();
						$act['name']='接单';
						$act['ctl']=$module;
						$act['act']='accept_order';
						$act['id']=$order_info['id'];
						$order_state['act'][]=$act;
						
						$act=array();
						$act['name']='关闭订单';
						$act['ctl']=$module;
						$act['act']='close_order';
						$act['has_reason']=1;
						$act['id']=$order_info['id'];
						$order_state['act'][]=$act;
					}

				}elseif($order_info['confirm_status']==1){
					$order_state['state']=3;
					$order_state['state_format']='已接单';
					
					if($order_info['order_delivery_time']==1){
						//立即送达，商户从下单时间后4小时，可以确认订单
						$over_time=$order_info['create_time']+3600*4;
					}elseif($order_info['order_delivery_time']>1){
						//有具体配送时间，商户从送达时间后4小时，可以确认订单
						$over_time=$order_info['order_delivery_time']+3600*4;
					}
					if($type=='wap'){

							if($order_info['is_rs']==0 && NOW_TIME > $over_time){
									$act=array();
									$act['name']='确认完成';
									$act['url']=wap_url('index',$module.'#over_order',array('id'=>$order_info['id']));
									$order_state['act'][]=$act;	
							}
							$act=array();
							$act['name']='关闭订单';
							$act['has_reason']=1;
							$act['url']=wap_url('index',$module.'#close_order',array('id'=>$order_info['id']));
							$order_state['act'][]=$act;		
					}else{
						if($order_info['is_rs']==0 && NOW_TIME > $over_time){
							$act=array();
							$act['name']='确认完成';
							$act['ctl']=$module;
							$act['act']='over_order';
							$act['id']=$order_info['id'];
							$order_state['act'][]=$act;
						}
						$act=array();
						$act['name']='关闭订单';
						$act['ctl']=$module;
						$act['act']='close_order';
						$act['has_reason']=1;
						$act['id']=$order_info['id'];
						$order_state['act'][]=$act;
						
					}

				}elseif($order_info['confirm_status']==2 && $order_info['is_dp']==0){
					$order_state['state']=4;
					$order_state['state_format']='已完成';

				}elseif($order_info['confirm_status']==2 && $order_info['is_dp']==1){
					$order_state['state']=9;
					$order_state['state_format']='已点评';

				}
			}
		}elseif($order_info['payment_id']==1){  //货到付款
			if($order_info['confirm_status']==0){
					$order_state['state']=2;
					$order_state['state_format']='待接单';
						
					if($type=='wap'){

							$act=array();
							$act['name']='接单';
							$act['url']=wap_url('index',$module.'#accept_order',array('id'=>$order_info['id']));
							$order_state['act'][]=$act;
							
							$act=array();
							$act['name']='关闭订单';
							$act['has_reason']=1;
							$act['url']=wap_url('index',$module.'#close_order',array('id'=>$order_info['id']));
							$order_state['act'][]=$act;
						
					}else{

						$act=array();
						$act['name']='接单';
						$act['ctl']=$module;
						$act['act']='accept_order';
						$act['id']=$order_info['id'];
						$order_state['act'][]=$act;
						
						$act=array();
						$act['name']='关闭订单';
						$act['ctl']=$module;
						$act['act']='close_order';
						$act['has_reason']=1;
						$act['id']=$order_info['id'];
						$order_state['act'][]=$act;
						
					}
			}elseif($order_info['confirm_status']==1){
					$order_state['state']=3;
					$order_state['state_format']='已接单';
					
					if($order_info['order_delivery_time']==1){
						//立即送达，商户从下单时间后4小时，可以确认订单
						$over_time=$order_info['create_time']+3600*4;
					}elseif($order_info['order_delivery_time']>1){
						//有具体配送时间，商户从送达时间后4小时，可以确认订单
						$over_time=$order_info['order_delivery_time']+3600*4;
					}
					if($type=='wap'){

							if($order_info['is_rs']==0 && NOW_TIME > $over_time){
									$act=array();
									$act['name']='确认完成';
									$act['url']=wap_url('index',$module.'#over_order',array('id'=>$order_info['id']));
									$order_state['act'][]=$act;	
							}
							$act=array();
							$act['name']='关闭订单';
							$act['has_reason']=1;
							$act['url']=wap_url('index',$module.'#close_order',array('id'=>$order_info['id']));
							$order_state['act'][]=$act;		
					}else{
						if($order_info['is_rs']==0 && NOW_TIME > $over_time){
							$act=array();
							$act['name']='确认完成';
							$act['ctl']=$module;
							$act['act']='over_order';
							$act['id']=$order_info['id'];
							$order_state['act'][]=$act;
						}
						$act=array();
						$act['name']='关闭订单';
						$act['ctl']=$module;
						$act['has_reason']=1;
						$act['act']='close_order';
						$act['id']=$order_info['id'];
						$order_state['act'][]=$act;
						
					}

			}elseif($order_info['confirm_status']==2 && $order_info['is_dp']==0){
				$order_state['state']=4;
				$order_state['state_format']='已完成';


			}elseif($order_info['confirm_status']==2 && $order_info['is_dp']==1){
				$order_state['state']=9;
				$order_state['state_format']='已点评';

			}

		}


	}
	return $order_state;
}

/**
 * 
 * 超时接单的订单处理，把商家超时接单的订单，自动关闭，执行者可以是用户或者商家
 * @param $role 执行者，$role等于biz时，执行者为商家;$role等于user时，执行者为会员
 * @param $id 执行者的ID，$role等于biz时，$id为商家ID;$role等于user时，$id为会员ID
 */
function timeout_accept_order_process($role,$id){
	if($role=='biz'){
		$order_info_all=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_order where confirm_status=0 and ((pay_status=1 and payment_id=0) or payment_id=1) and is_cancel=0 and refund_status=0 and location_id=".$id);
	}elseif($role=='user'){
		$order_info_all=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_order where confirm_status=0 and ((pay_status=1 and payment_id=0) or payment_id=1) and is_cancel=0 and refund_status=0 and user_id=".$id);
	}

	foreach($order_info_all as $k=>$order_info){
		if($order_info['is_rs']==0){  //外卖订单处理
			if($order_info['order_delivery_time']==1){
				//立即送达超过两小时不接单，直接关闭订单
				if(NOW_TIME-$order_info['create_time'] > 3600 * 2){
					$close_reason='商家接单超时，订单关闭';
					dc_order_close($order_info['id'],3,$close_reason);
					//output($root,0,"请在用户下单后2小时内接单，接单超时，订单关闭");
				}
			}elseif($order_info['order_delivery_time'] > 10000){
				//有具体送达时间，超过送达时间，直接关闭订单
				if(NOW_TIME > $order_info['order_delivery_time']){
					$close_reason='商家接单超时，订单关闭';
					dc_order_close($order_info['id'],3,$close_reason);
			
					//output($root,0,"超过用户配送时间，接单超时，订单关闭");
				}
			}
		}else{  //预订订单处理
		
			//商家接单时间，要在用户预订时间前半小时
			$order_info['order_menu']=unserialize($order_info['order_menu']);
			foreach($order_info['order_menu']['rs_list']['cart_list'] as $k=>$v){
				$rs_time=$v['table_time'];
			}
			
			if($rs_time < NOW_TIME + 1800){  //商家接单时间，要在用户预订时间前半小时，否则关闭订单
				//订单关闭
				$close_reason='商家接单超时，订单关闭';
				dc_order_close($order_info['id'],3,$close_reason);
				//output($root,0,"请在用户预订时间前半小时接单，接单超时，订单关闭");
			
			}
			
		}
	}
	
}


/**
 * 
 * 超时支付的订单处理，把用户超时支付的订单，自动关闭，执行者可以是用户或者商家，超时支付的时间为15分钟
 * @param $role 执行者，$role等于biz时，执行者为商家;$role等于user时，执行者为会员
 * @param $id 执行者的ID，$role等于biz时，$id为商家ID;$role等于user时，$id为会员ID
 */
function timeout_pay_order_process($role,$id){
	if($role=='biz'){
		$order_info_all=$GLOBALS['db']->getAll("select id from ".DB_PREFIX."dc_order where confirm_status=0 and pay_status=0 and payment_id=0 and is_cancel=0 and refund_status=0 and ".NOW_TIME." - create_time > 900 and location_id=".$id);
	}elseif($role=='user'){
		$order_info_all=$GLOBALS['db']->getAll("select id from ".DB_PREFIX."dc_order where confirm_status=0 and pay_status=0 and payment_id=0 and is_cancel=0 and refund_status=0 and ".NOW_TIME." - create_time > 900 and user_id=".$id);
	}

	foreach($order_info_all as $k=>$order_info){

		$close_reason='支付超时，订单关闭，请在下单后15分钟内支付完成';
		dc_order_close($order_info['id'],3,$close_reason);
	}
	
}
