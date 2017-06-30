<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 获取某个点评商品、优惠券、活动、商户、门店的基本信息
 * @param unknown_type $param array("deal_id"=>"","youhui_id"=>"","event_id"=>"","supplier_id"=>"","location_id"=>"");
 */
function load_dp_info($param)
{
	$dp_data = load_auto_cache("cache_dp_info",$param);	
	return $dp_data;
}


/**
 * 加载点评配置
 * @param unknown_type $param  cate_id,scate_id,ecate_id
 * @return Ambigous <boolean, void>
 */
function load_dp_cfg($param)
{
	$dp_cfg = load_auto_cache("cache_dp_cfg",$param);
	return $dp_cfg;
}


/**
 * 验证用户是否有对某个商品点评的权限
 * 
 * 
 * deal_id: 商品ID
 * youhui_id: 优惠券ID
 * event_id: 活动ID
 * location_id: 门店ID
 * order_item_id: 针对商品的订单产品编号
 * youhui_log_id: 针对优惠券下载的ID
 * event_submit_id: 针对活动报名的ID
 * 返回 array("status"=>bool,"info"=>"消息","supplier_location_id"=>所从属的点评门店ID,"avg_price"=>只有商点的点评返回购物金额作为点评均价)
 * 
 * 
 * 规则：
 * 1.商品
 * 直接传order_item_id时，以deal_order_item的相关数据为标准，并获取deal_id
 * 直接传deal_id时，获取未点评的相关订单商品deal_order_item
 * 标准：团购券：确认过未点评  实体：已收货未点评  无券无发货：已支付未点评
 * 商品点评表示为购物点评，同步平均价
 * 点评完需同步商品的评分
 *
 * 2.优惠券
 * 直接传youhui_log_id时，以youhui_log的相关数据为标准，获取youhui_id
 * 直接传youhui_id时，获取未点评的相关优惠券记录youhui_log
 * 标准：已验证未点评
 * 点评完需同步优惠券的评分
 *
 * 3.活动
 * 直接传event_submit_id时，以event_submit的相关数据为标准，获取event_id
 * 直接传event_id时，获取相关未点评的报名记录event_submit
 * 标准：已验证未点评
 * 点评完需同步活动的评分
 *
 * 4.商家
 * 直接传location_id获取点评的商家
 * 标准，每个用户每日只允许发表一则点评
 *
 * 关于商家ID
 * 商品：团购券：验证时会生成验证的门店ID, 实体：配货时会生成配货的门店ID 如ID无法获取，以相关商品支持的门店列表中取总店获随机其中之一
 * 优惠券： 验证时会生成验证的门店ID, 以相关优惠券支持的门店列表中取总店获随机其中之一
 * 活动： 验证时会生成验证的门店ID, 以相关活动支持的门店列表中取总店获随机其中之一
 *
 * 除了传参为location_id时，商家ID允许为0，即最终无法查询出商家ID
 * 无商家ID时不同步计算商家的点评评分
 * 否则需同步商家评分
 */
function check_dp_status($user_id,$param=array("deal_id"=>0,"youhui_id"=>0,"event_id"=>0,"location_id"=>0,"order_item_id"=>0,"youhui_log_id"=>0,"event_submit_id"=>0))
{
	$order_item_id = intval($param['order_item_id']);  //订单商品ID
	$youhui_log_id = intval($param['youhui_log_id']);  //优惠券领取日志ID
	$event_submit_id = intval($param['event_submit_id']); //活动报名日志ID
	$deal_id = intval($param['deal_id']);
	$youhui_id = intval($param['youhui_id']);
	$event_id = intval($param['event_id']);
	$location_id = intval($param['location_id']);
	
	
	
	if($deal_id>0)
	{
		require_once APP_ROOT_PATH."system/model/deal.php";
		$deal_info = get_deal($param['deal_id']);
		
		if($deal_info['is_coupon']==1)
		{
			if($order_item_id==0)
			{
				$sql = "select c.*,c.location_id as location_id,doi.total_price as avg_price,doi.id as doiid from ".DB_PREFIX."deal_coupon as c left join ".
						DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id left join ".
						DB_PREFIX."deal_order as do on doi.order_id = do.id ".
						" where c.deal_id = ".$deal_id." and c.confirm_time > 0 and doi.dp_id = 0 and do.user_id = ".$user_id;					
			}
			else
			{
				$sql = "select c.*,c.location_id as location_id,doi.total_price as avg_price,doi.id as doiid from ".DB_PREFIX."deal_coupon as c left join ".
						DB_PREFIX."deal_order_item as doi on c.order_deal_id = doi.id left join ".
						DB_PREFIX."deal_order as do on doi.order_id = do.id ".
						"where c.deal_id = ".$deal_id." and c.order_deal_id = ".$order_item_id." and doi.dp_id = 0 and c.confirm_time > 0 and do.user_id = ".$user_id;
			}
		}
		elseif($deal_info['is_delivery']==1)
		{
			if($order_item_id==0)
			{
				$sql = "select n.*,n.location_id as location_id,doi.total_price as avg_price,doi.id as doiid from ".DB_PREFIX."delivery_notice as n left join ".
						DB_PREFIX."deal_order_item as doi on n.order_item_id = doi.id left join ".
						DB_PREFIX."deal_order as do on doi.order_id = do.id ".
						"where doi.deal_id = ".$deal_id." and n.is_arrival = 1 and doi.dp_id = 0 and do.user_id = ".$user_id;
			}
			else
			{
				$sql = "select n.*,n.location_id as location_id,doi.total_price as avg_price,doi.id as doiid from ".DB_PREFIX."delivery_notice as n left join ".
						DB_PREFIX."deal_order_item as doi on n.order_item_id = doi.id left join ".
						DB_PREFIX."deal_order as do on doi.order_id = do.id ".
						"where doi.deal_id = ".$deal_id." and n.order_item_id = ".$order_item_id." and doi.dp_id = 0 and n.is_arrival = 1 and do.user_id = ".$user_id;
			}
		}
		else
		{
			if($order_item_id==0)
			{
				$sql = "select doi.*,doi.total_price as avg_price,doi.id as doiid from ".DB_PREFIX."deal_order_item as doi left join ".
						DB_PREFIX."deal_order as do on doi.order_id = do.id ".
						"where do.user_id = ".$user_id." and doi.dp_id = 0 and do.pay_status = 2";
			}
			else
			{
				$sql = "select doi.*,doi.total_price as avg_price,doi.id as doiid from ".DB_PREFIX."deal_order_item as doi left join ".
						DB_PREFIX."deal_order as do on doi.order_id = do.id ".
						"where do.user_id = ".$user_id." and doi.dp_id = 0 and do.pay_status = 2 and doi.id = ".$order_item_id;
			}
		}
		
		
		$rs = $GLOBALS['db']->getRow($sql);
		if(empty($rs))
		{
			return array("status"=>false,"info"=>"您没有可以点评的购物记录");
		}
		else
		{
			$supplier_location_id = intval($rs['location_id']);
			if($supplier_location_id==0)
				$supplier_location_id = intval($GLOBALS['db']->getOne("select sl.id from ".DB_PREFIX."supplier_location as sl left join ".DB_PREFIX."deal_location_link as dll on dll.location_id = sl.id where dll.deal_id = ".$deal_id." order by sl.is_main desc"));

			$avg_price = $rs['avg_price'];
			return array("status"=>true,"info"=>"","supplier_location_id"=>$supplier_location_id,"avg_price"=>$avg_price,"order_item_id"=>$rs['doiid']);
		}
		
	}
	elseif($youhui_id>0)
	{
		if($youhui_log_id==0)
		{
			$sql = "select location_id,id from ".DB_PREFIX."youhui_log where youhui_id = ".$youhui_id." and dp_id = 0 and confirm_time > 0 and user_id = ".$user_id;	
		}
		else
		{
			$sql = "select location_id,id from ".DB_PREFIX."youhui_log where id = ".$youhui_log_id." and user_id = ".$user_id." and dp_id = 0 and confirm_time > 0 ";
		}
		
		$rs = $GLOBALS['db']->getRow($sql);
		if(empty($rs))
		{
			return array("status"=>false,"info"=>"您没有可以点评的优惠券下载记录");
		}
		else
		{
			$supplier_location_id = intval($rs['location_id']);
			if($supplier_location_id==0)
				$supplier_location_id = intval($GLOBALS['db']->getOne("select sl.id from ".DB_PREFIX."supplier_location as sl left join ".DB_PREFIX."youhui_location_link as yll on yll.location_id = sl.id where yll.youhui_id = ".$youhui_id." order by sl.is_main desc"));
			
			return array("status"=>true,"info"=>"","supplier_location_id"=>$supplier_location_id,"youhui_log_id"=>$rs['id']);
		}
		
	}
	elseif($event_id>0)
	{		
		if($event_submit_id==0)
		{
			$sql = "select location_id,id from ".DB_PREFIX."event_submit where event_id = ".$event_id." and dp_id = 0 and confirm_time > 0 and user_id = ".$user_id;
		}
		else
		{
			$sql = "select location_id,id from ".DB_PREFIX."event_submit where id = ".$event_submit_id." and user_id = ".$user_id." and dp_id = 0 and confirm_time > 0 ";
		}
		
		$rs = $GLOBALS['db']->getRow($sql);
		if(empty($rs))
		{
			return array("status"=>false,"info"=>"您没有可以点评的活动参与记录");
		}
		else
		{
			$supplier_location_id = intval($rs['location_id']);
			if($supplier_location_id==0)
				$supplier_location_id = intval($GLOBALS['db']->getOne("select sl.id from ".DB_PREFIX."supplier_location as sl left join ".DB_PREFIX."event_location_link as ell on ell.location_id = sl.id where ell.event_id = ".$event_id." order by sl.is_main desc"));
		
			return array("status"=>true,"info"=>"","supplier_location_id"=>$supplier_location_id,"event_submit_id"=>$rs['id']);
		}
		
		
	}
	elseif($location_id>0)
	{
		$begin_time = to_timespan(to_date(NOW_TIME,"Y-m-d"),"Y-m-d");
		$end_time = to_timespan(to_date(NOW_TIME,"Y-m-d"),"Y-m-d")+24*3600-1;

		$sql = "select * from ".DB_PREFIX."supplier_location_dp ".
				" where user_id = ".$user_id." and (create_time between ".$begin_time." and ".$end_time.") and supplier_location_id = ".$location_id." and deal_id = 0 and youhui_id = 0 and event_id = 0";

		$rs = $GLOBALS['db']->getRow($sql);
		if($rs)
		{
			return array("status"=>false,"info"=>"您今天已经对该商家发表过点评，谢谢参与");
		}
		else
		{
			$supplier_location_id = $location_id;
			return array("status"=>true,"info"=>"","supplier_location_id"=>$supplier_location_id);
		}
	}
	else
	{
		return array("status"=>true,"info"=>"非法的数据");
	}
	
	
}


/**
 * 加载指定类型的点评列表
 */
function get_dp_list($limit,$param=array("deal_id"=>0,"youhui_id"=>0,"event_id"=>0,"location_id"=>0,"tag"=>""),$where="",$orderby="")
{
	if(empty($param))
		$param=array("deal_id"=>0,"youhui_id"=>0,"event_id"=>0,"location_id"=>0,"tag"=>"");
	
	$condition = " 1=1 ";
	
	if($param['deal_id']>0)
	{
		$condition.=" and deal_id = ".$param['deal_id']." ";
	}
	elseif($param['youhui_id']>0)
	{
		$condition.=" and youhui_id = ".$param['youhui_id']." ";
	}
	elseif($param['event_id']>0)
	{
		$condition.=" and event_id = ".$param['event_id']." ";
	}
	elseif($param['supplier_id']>0)
	{
		$condition.=" and supplier_id = ".$param['supplier_id']." ";
	}
	elseif($param['location_id']>0)
	{
		$condition.=" and supplier_location_id = ".$param['location_id']." ";
	}
	
	if($param['tag']!="")
	{
		$tag_unicode = str_to_unicode_string($param['tag']);
		$condition .= " and (match(tags_match) against('".$tag_unicode."' IN BOOLEAN MODE)) ";
	}
	
	if($where != '')
	{
		$condition.=" and ".$where;
	}
	
	$sql = "select * from ".DB_PREFIX."supplier_location_dp where  ".$condition;
	
	if($orderby=='')
		$sql.=" order by is_index desc,create_time desc limit ".$limit;
	else
		$sql.=" order by is_index desc,".$orderby." limit ".$limit;
	
	$dp_list = $GLOBALS['db']->getAll($sql);
	
	
	if($dp_list)
	{
		foreach($dp_list as $k=>$v)
		{
			if($v['is_img']==1)
			$dp_list[$k]['images'] = unserialize($v['images_cache']);
// 			$dp_list[$k]['images'] = array(
// 				"http://p0.meituan.net/deal/0bd31cc0be3a91f4ff1bc785c4cd5567100264.jpg",
// 				"./public/attachment/201202/16/11/4f3c7f1d37dea.jpg",
// 				"./public/attachment/201202/16/11/4f3c7ea394a90.jpg",
// 				"./public/attachment/201201/4f0110c586c48.jpg",
// 				"./public/attachment/201201/4f0113ce66cd4.jpg",
// 				"http://p1.meituan.net/deal/cdba2654c4b3493f45052c831166ee1e479253.jpg",
// 				"http://p0.meituan.net/deal/__49109589__8248077.jpg",
							
// 			);
			$dp_list[$k]['content'] = nl2br($v['content']);
			$dp_list[$k]['create_time_format'] = to_date($v['create_time'],"Y-m-d");
			$dp_list[$k]['point_percent'] = $v['point']/5*100;
		}
	}
	return array('list'=>$dp_list,'condition'=>$condition);
}


/**
 * 提交保存点评
 * @param unknown_type $user_id 提交点评的会员
 * @param unknown_type $param 参数 详细规则见 check_dp_status函数说明
 * @param unknown_type $content 点评文字内容
 * @param unknown_type $dp_point 总评分
 * @param unknown_type $dp_image 点评的图片数组 array("./public/...","./public/.....");
 * @param unknown_type $tag_group 点评标签(二维数组)，格式如下
 * array(
 * 		"group_id" = array("tag","tag")
 * ); 其中group_id为分组的ID,第二维为每个分组中的tag
 * @param unknown_type $point_group 点评评分分组数据，格式如下
 * array(
 * 		"group_id" 	=>	"point"
 * ); 其中group_id为分组的ID,point为对应分组的评分
 * 
 * 返回 array("status"=>bool, "info"=>"消息","location_id"=>"门店的ID","deal_id"=>"","youhui_id"=>"","event_id"=>"");
 */
function save_review($user_id,$param=array("deal_id"=>0,"youhui_id"=>0,"event_id"=>0,"location_id"=>0,"order_item_id"=>0,"youhui_log_id"=>0,"event_submit_id"=>0),$content,$dp_point,$dp_image=array(),$tag_group=array(),$point_group=array())
{
	//获取参数
	$order_item_id = intval($param['order_item_id']);  //订单商品ID
	$youhui_log_id = intval($param['youhui_log_id']);  //优惠券领取日志ID
	$event_submit_id = intval($param['event_submit_id']); //活动报名日志ID
	
	if($order_item_id>0)
	{
		$deal_id = intval($GLOBALS['db']->getOne("select deal_id from ".DB_PREFIX."deal_order_item where id = ".$order_item_id));
	}
	else
	{
		$deal_id = intval($param['deal_id']);
	}
	
	if($youhui_log_id>0)
	{
		$youhui_id = intval($GLOBALS['db']->getOne("select youhui_id from ".DB_PREFIX."youhui_log where id = ".$youhui_log_id));
	}
	else
	{
		$youhui_id = intval($param['youhui_id']);
	}
	
	if($event_submit_id>0)
	{
		$event_id = intval($GLOBALS['db']->getOne("select event_id from ".DB_PREFIX."event_submit where id = ".$event_submit_id));
	}
	else
	{
		$event_id = intval($param['event_id']);
	}
	
	$location_id = intval($param['location_id']);
	
	//部份初始化的变量	
	$is_buy = 0; //默认的点评为非购物点评
	$avg_price = 0; //均价为0
	
	if($deal_id>0)
	{
		require_once APP_ROOT_PATH."system/model/deal.php";
		$deal_info = get_deal($deal_id);
		if($deal_info)
		{
			//验证是否可以点评
			$checker = check_dp_status($GLOBALS['user_info']['id'],array("deal_id"=>$deal_id,"order_item_id"=>$order_item_id));
			if(!$checker['status'])
			{
				return array("status"=>false,"info"=>$checker['info']);
			}
			else
				$supplier_location_id = $checker['supplier_location_id'];
	
			$is_buy = 1;
			$avg_price = $checker['avg_price'];
		}
		else
		{
			return array("status"=>false,"info"=>"你要点评的商品不存在");
		}
	}
	elseif($youhui_id>0)
	{
		require_once APP_ROOT_PATH."system/model/youhui.php";
		$youhui_info = get_youhui($youhui_id);
		if($youhui_info)
		{
			//验证是否可以点评
			$checker = check_dp_status($GLOBALS['user_info']['id'],array("youhui_id"=>$youhui_id,"youhui_log_id"=>$youhui_log_id));
			if(!$checker['status'])
			{
				return array("status"=>false,"info"=>$checker['info']);
			}
			else
				$supplier_location_id = $checker['supplier_location_id'];
	
		}
		else
		{
			return array("status"=>false,"info"=>"你要点评的优惠券不存在");
		}
	}
	elseif($event_id>0)
	{
		require_once APP_ROOT_PATH."system/model/event.php";
		$event_info = get_event($event_id);
		if($event_info)
		{
			//验证是否可以点评
			$checker = check_dp_status($GLOBALS['user_info']['id'],array("event_id"=>$event_id,"event_submit_id"=>$event_submit_id));
			if(!$checker['status'])
			{
				return array("status"=>false,"info"=>$checker['info']);
			}
			else
				$supplier_location_id = $checker['supplier_location_id'];
	
		}
		else
		{
			return array("status"=>false,"info"=>"你要点评的活动不存在");
		}
	}
	elseif($location_id>0)
	{
		require_once APP_ROOT_PATH."system/model/supplier.php";
		$location_info = get_location($location_id);
		if($location_info)
		{
			//验证是否可以点评
			$checker = check_dp_status($GLOBALS['user_info']['id'],array("location_id"=>$location_id));
			if(!$checker['status'])
			{
				return array("status"=>false,"info"=>$checker['info']);
			}
			else
				$supplier_location_id = $checker['supplier_location_id'];
		}
		else
		{
			return array("status"=>false,"info"=>"你要点评的商家不存在");
		}
	}
	
	if($deal_id==0&&$youhui_id==0&&$event_id==0&&$location_id==0)
	{
		return array("status"=>false,"info"=>"非法的数据");
	}
	
	
	//点评入库
	$supplier_info = $GLOBALS['db']->getRow("select name,id,new_dp_count_time,supplier_id from ".DB_PREFIX."supplier_location where id = ".intval($supplier_location_id));
	$supplier_id = $supplier_info['supplier_id'];
	$dp_data = array();
	if($content!="")
	{
		$dp_data['is_content'] = 1;
		$dp_data['content'] = $content;
	}
	$dp_data['create_time'] = NOW_TIME;
	$dp_data['point'] = $dp_point;
	$dp_data['user_id'] = $user_id;
	$dp_data['supplier_location_id'] = $supplier_location_id;
	$dp_data['youhui_id'] = $youhui_id;
	$dp_data['event_id'] = $event_id;
	$dp_data['deal_id'] = $deal_id;
	$dp_data['images_cache'] = serialize($dp_image);
	$dp_data['supplier_id'] = $supplier_id;
	$dp_data['status'] = 1;
	if(count($dp_image)>0)
	{
		$dp_data['is_img'] = 1;
	}
	$dp_data['avg_price'] = floatval($avg_price);
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp", $dp_data ,"INSERT");
	$dp_id = $GLOBALS['db']->insert_id();
	if($dp_id>0)
	{	
	
		if($checker['order_item_id'])
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set dp_id = ".$dp_id." where id = '".$checker['order_item_id']."'");
			$order_id = intval($GLOBALS['db']->getOne("select order_id from ".DB_PREFIX."deal_order_item where id = ".$checker['order_item_id']));
			update_order_cache($order_id);
			require_once APP_ROOT_PATH."system/model/deal_order.php";
			distribute_order($order_id);
		}	
		if($checker['youhui_log_id'])
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."youhui_log set dp_id = ".$dp_id." where id = '".$checker['youhui_log_id']."'");
		}
		if($checker['event_submit_id'])
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set dp_id = ".$dp_id." where id = '".$checker['event_submit_id']."'");
		}
		
		increase_user_active($user_id,"发表了一则点评");
		$GLOBALS['db']->query("update ".DB_PREFIX."user set dp_count = dp_count + 1 where id = ".$user_id);
		//创建点评图库
		if(count($dp_image) > 0)
		{
			foreach($dp_image as $pkey => $photo)
			{
				//点评图片不入商户图片库
// 				$c_data = array();
// 				$c_data['image'] = $photo;
// 				$c_data['sort'] = 10;
// 				$c_data['create_time'] = NOW_TIME;
// 				$c_data['user_id'] = $user_id;
// 				$c_data['supplier_location_id'] = $supplier_location_id;
// 				$c_data['dp_id'] = $dp_id;
// 				$c_data['status'] = 0;
// 				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_images", $c_data,"INSERT");
				
				$c_data = array();
				$c_data['image'] = $photo;
				$c_data['dp_id'] = $dp_id;
				$c_data['create_time'] = NOW_TIME;
				$c_data['location_id'] = $supplier_location_id;
				$c_data['supplier_id'] = $supplier_id;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_images", $c_data,"INSERT");
			}
		}
	
		//创建点评评分
		foreach($point_group as $group_id => $point)
		{
			$point_data = array();
			$point_data['group_id'] = $group_id;
			$point_data['dp_id'] = $dp_id;
			$point_data['supplier_location_id'] = $supplier_location_id;
			$point_data['point'] = $point;
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_point_result", $point_data,"INSERT");
			
			//创建商品点评数据
			if($dp_data['deal_id']>0)
			{
				$point_data = array();
				$point_data['group_id'] = $group_id;
				$point_data['dp_id'] = $dp_id;
				$point_data['deal_id'] = $dp_data['deal_id'];
				$point_data['point'] = $point;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_dp_point_result", $point_data,"INSERT");
			}
			
			//创建优惠券点评数据
			if($dp_data['youhui_id']>0)
			{
				$point_data = array();
				$point_data['group_id'] = $group_id;
				$point_data['dp_id'] = $dp_id;
				$point_data['youhui_id'] = $dp_data['youhui_id'];
				$point_data['point'] = $point;
				$GLOBALS['db']->autoExecute(DB_PREFIX."youhui_dp_point_result", $point_data,"INSERT");
			}
			
			//创建活动点评数据
			if($dp_data['event_id']>0)
			{
				$point_data = array();
				$point_data['group_id'] = $group_id;
				$point_data['dp_id'] = $dp_id;
				$point_data['event_id'] = $dp_data['event_id'];
				$point_data['point'] = $point;
				$GLOBALS['db']->autoExecute(DB_PREFIX."event_dp_point_result", $point_data,"INSERT");
			}
		}
	
		//创建点评分组的标签
		foreach($tag_group as $group_id => $tag_row_arr)
		{	
			foreach ($tag_row_arr as $tag_row)
			{
				$tag_row_data = array();
				$tag_row_data['tags'] = $tag_row;
				$tag_row_data['dp_id'] = $dp_id;
				$tag_row_data['supplier_location_id'] = $supplier_location_id;
				$tag_row_data['group_id'] = $group_id;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_tag_result", $tag_row_data, "INSERT");
	
				insert_match_item($tag_row,"supplier_location_dp",$dp_id,"tags_match"); //更新点评的索引
				review_supplier_location_match($supplier_location_id,$tag_row,$group_id);
				
				//创建商品点评数据
				if($dp_data['deal_id']>0)
				{
					$tag_row_data = array();
					$tag_row_data['tags'] = $tag_row;
					$tag_row_data['dp_id'] = $dp_id;
					$tag_row_data['deal_id'] = $dp_data['deal_id'];
					$tag_row_data['group_id'] = $group_id;					
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_dp_tag_result", $tag_row_data, "INSERT");
				}
					
				//创建优惠券点评数据
				if($dp_data['youhui_id']>0)
				{
					$tag_row_data = array();
					$tag_row_data['tags'] = $tag_row;
					$tag_row_data['dp_id'] = $dp_id;
					$tag_row_data['youhui_id'] = $dp_data['youhui_id'];
					$tag_row_data['group_id'] = $group_id;
					$GLOBALS['db']->autoExecute(DB_PREFIX."youhui_dp_tag_result", $tag_row_data, "INSERT");
				}
					
				//创建活动点评数据
				if($dp_data['event_id']>0)
				{
					$tag_row_data = array();
					$tag_row_data['tags'] = $tag_row;
					$tag_row_data['dp_id'] = $dp_id;
					$tag_row_data['event_id'] = $dp_data['event_id'];
					$tag_row_data['group_id'] = $group_id;
					$GLOBALS['db']->autoExecute(DB_PREFIX."event_dp_tag_result", $tag_row_data, "INSERT");
				}
			}
		}		
	
		//更新统计
		syn_supplier_locationcount($supplier_info);
		cache_store_point($supplier_info['id']);
		
		
		//统计商品点评数据
		if($dp_data['deal_id']>0)
		{
			//计算总点评1-5星人数
			$item_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$dp_data['deal_id']);
			$sql = "select count(*) as total,point from ".DB_PREFIX."supplier_location_dp  where deal_id = ".$item_data['id']." group by point ";
			$data_result = $GLOBALS['db']->getAll($sql);
			foreach($data_result as $k=>$v)
			{
				$item_data['dp_count_'.$v['point']] = $v['total'];
			}			
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal", $item_data, "UPDATE"," id = ".$item_data['id']." ");
			syn_deal_review_count($item_data['id']);
		}
		
		//创建优惠券点评数据
		if($dp_data['youhui_id']>0)
		{
			//计算总点评1-5星人数
			$item_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."youhui where id = ".$dp_data['youhui_id']);
			$sql = "select count(*) as total,point from ".DB_PREFIX."supplier_location_dp  where youhui_id = ".$item_data['id']." group by point ";
			$data_result = $GLOBALS['db']->getAll($sql);
			foreach($data_result as $k=>$v)
			{
				$item_data['dp_count_'.$v['point']] = $v['total'];
			}
	
			$GLOBALS['db']->autoExecute(DB_PREFIX."youhui", $item_data, "UPDATE"," id = ".$item_data['id']." ");
			syn_youhui_review_count($item_data['id']);
		}
		
		//创建活动点评数据
		if($dp_data['event_id']>0)
		{
			//计算总点评1-5星人数
			$item_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event where id = ".$dp_data['event_id']);
			$sql = "select count(*) as total,point from ".DB_PREFIX."supplier_location_dp where event_id = ".$item_data['id']." group by point ";;
			$data_result = $GLOBALS['db']->getAll($sql);
			foreach($data_result as $k=>$v)
			{
				$item_data['dp_count_'.$v['point']] = $v['total'];
			}
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."event", $item_data, "UPDATE"," id = ".$item_data['id']." ");
			syn_event_review_count($item_data['id']);
		}
		
		rm_auto_cache("cache_dp_info",$param);
		$return['location_id'] = $supplier_location_id;
		$return['deal_id'] = $dp_data['deal_id'];		
		$return['youhui_id'] = $dp_data['youhui_id'];		
		$return['event_id'] = $dp_data['event_id'];
		
		$return['status'] = 1;
		$return['info'] = "发表成功";
		return $return;
	}
	else{
		$return['status'] = 0;
		$return['info'] = "数据库异常，提交失败";
		return $return;
	}
}


//同步门店索引
function review_supplier_location_match($location_id,$tags,$group_id){
	$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
	if($location)
	{
		$location['tags_match'] = "";
		$location['tags_match_row'] = "";

		//标签
		$tags_arr = explode(" ",$tags);
		foreach($tags_arr as $tgs){
			//同步 supplier_tag 表
			$tag_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_tag where tag_name = '".trim($tgs)."' and supplier_location_id = ".$location_id." and group_id = ".$group_id);
			if($tag_data)
			{
				$tag_data['total_count'] = intval($tag_data['total_count'])+1 ;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_tag", $tag_data,"UPDATE", "tag_name = '".trim($tgs)."' and supplier_location_id = ".$location_id." and group_id = ".$group_id);

			}
			else
			{
				$tag_data['tag_name'] = trim($tgs);
				$tag_data['supplier_location_id'] = $location_id;
				$tag_data['group_id'] = $group_id;
				$tag_data['total_count'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_tag", $tag_data, "INSERT");
			}
			insert_match_item(trim($tgs),"supplier_location",$location_id,"tags_match");
		}
	}
}

function biz_get_dp_conditions($supplier_id,$dp_type,$point=0,$is_img=0){
    //获取商户下所有
    $conditions = "where  supplier_id =".$supplier_id;
    switch ($dp_type){
        case 'deal':
            $conditions.=" and deal_id >0 ";
            break;
        case 'youhui':
            $conditions.=" and youhui_id > 0 ";
            break;
        case 'event':
            $conditions.=" and event_id > 0 ";
            break;
        case 'location':
            $conditions.=" and deal_id = 0 and youhui_id = 0 and event_id = 0 ";
            break;
        default:
            break;
    }
    if($point>0){
        switch ($point){
            case 1: //好评
                $conditions.=" and point in(4,5) ";
                break;
            case 2: //中评
                $conditions.=" and point = 3  ";
                break;
            case 3: //差评
                $conditions.=" and point in(1,2) ";
                break;
            default:
                break;
        }
    }
    if($is_img>0){
        if($is_img == 1){
            $conditions.=" and is_img = 1 ";
        }else{
            $conditions.=" and is_img = 0 ";
        }
    }
    
    return $conditions;
}

/**
 * 商户中心获取点评数据
 * @param unknown $supplier_id
 * @param unknown $conditions
 * @param unknown $dp_type  商品：deal 优惠券：youhui 活动：event 商户门店：location
 * @param unknown $limit
 * @return mixed
 */
function biz_get_dp_list($conditions,$dp_type,$limit){

    $orderby = " order by create_time desc ";
    $limit = " limit ".$limit;
    $sql = "SELECT * FROM ".DB_PREFIX."supplier_location_dp ".$conditions.$orderby.$limit;
    
    $data = $GLOBALS['db']->getAll($sql);
    
    //取出等级信息
    $level_data = load_auto_cache("cache_user_level");

    if($data){
        foreach ($data as $k=>$v){
            //格式化好 中 差 评价
            if($v['point'] == 5 || $v['point'] == 4){
                $v['fpoint'] = 1;
            }
            if($v['point'] == 3){
                $v['fpoint'] = 2;
            }
            if($v['point'] == 1 || $v['point'] == 2){
                $v['fpoint'] = 3;
            }
            
            //获取用户信息
            $user_info = $GLOBALS['db']->getRow("select id,user_name,level_id from ".DB_PREFIX."user where id = ".$v['user_id']);
            $user_info['level'] = $level_data[$user_info['level_id']]['level'];
            $v['user_info'] = $user_info;
            
            switch ($dp_type){
                case 'deal':
                    $filter_data = load_auto_cache("deal",array("id"=>$v['deal_id']));
                    break;
                case 'youhui':
                    $filter_data = load_auto_cache("youhui",array("id"=>$v['youhui_id']));
                    break;
                case 'event':
                    $filter_data = load_auto_cache("event",array("id"=>$v['event_id']));
                    break;
                case 'location':
                    $filter_data = load_auto_cache("store",array("id"=>$v['supplier_location_id']));
                    break;
                default:
                    break;
            }
            $v['filter_data']['name'] = $filter_data['name'];
            $v['filter_data']['url'] = $filter_data['url'];
            $v['filter_data']['dp_type'] = $dp_type;
            
            //获取条件关联信息
            $v['create_time_format'] = to_date($v['create_time']);
            $images = unserialize($v['images_cache']);
            $v['images'] = $images;
            $result[] = $v;
//             print_r($result);exit;
        }
    }
    return $result;
}

function biz_reply_dp_html($dp_id){
    $dp = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_dp where id = ".$dp_id);
    $reply_content = '';
    if($dp['reply_content']){
        $reply_content = $dp['reply_content'];
    }
    $html = '<div class="reply_weebox"><div class="reply_cnt_box"><textarea class="ui-textbox" name="reply_content">'.$reply_content.'</textarea></div>
            <div class="reply_submit"><button class="ui-button reply_submit_btn" type="button" rel="orange">确认</button></div></div>';

    return $html;
}

function biz_do_reply_dp($account_id,$dp_id,$reply_content){
    $dp = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_dp where id = ".$dp_id);
    if($dp){
        if($dp['reply_content'] != $reply_content){
            $update_data = array();
            $update_data['reply_content'] = $reply_content;
            $update_data['reply_supplier_account_id'] = $account_id;
            $update_data['reply_time'] = NOW_TIME;
            $GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp", $update_data, 'UPDATE', ' id = '.$dp_id);
        }
        $result['status'] = 1;
        $result['msg'] = '[回复]'.$reply_content;
    }else{
        $result['status'] = 0;
        $result['msg'] = "点评数据不存在";
    }
    return $result;
}

?>