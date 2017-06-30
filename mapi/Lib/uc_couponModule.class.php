<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_couponModule extends MainBaseModule
{
	
	/**
	 * 会员中心我的团购券
	 * 输入：
	 * tag:int 团购券的状态(1 即将过期 /2 未使用 /3 已失效) 不传默认全部
	 * page [int] 分页所在的页数
	 * 
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * tag:int 团购券的状态(1 即将过期 /2 未使用 /3 已失效) 不传默认全部
	 * page_title:string 页面标题
	 * item:array 团购券数据列表
	 * [0] => Array
                (
                    [sub_name] => 泰宁大金湖 [string] 简短团购名
                    [name] => 仅售758元！价值838元的福建春秋国际旅行社提供的泰宁大金团人数 [string] 完整团购名
                    [number] => 1   [int] 团购券数量
                    [password] => 66353664  [string] 团购券密码
                    [end_time] => 2015-04-30    [string] 团购券过期时间
                    [confirm_time] => 2015-04-21    [string] 团购券使用时间
                    [deal_id] => 65 [int] 团购商品ID
                    [order_id] => 33    [int] 团购订单ID
                    [order_deal_id] => 87   [int] 团购订单商品ID
                    [supplier_id] => 31 [int] 团购商户ID
                    [couponSn] => 65930923 [string] 团购序列号
                    [less_time] => 2429473 [string] 即将到期时间
                    [dealIcon] => http://localhost/o2onew/public/attachment/201502/25/16/54ed84087507c_140x85.jpg [string] 团购商品缩略图 140x85
                    [spAddress] => 鼓楼区五一中路18号正大广场御景台1623 [string] 商户门店地址
                    [spTel] => 0591-88592106/88592109 [string] 商户门店电话
                    [spName] => 国际旅游社   [string]//商户门店名称
                    [qrcode] => http://localhost/o2onew/public/images/qrcode/c8/6cd2a7d7e724977bf18e835c4e573fc1.png  [string] 团购密码验证 二维码图片
                )
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 */
	public function index()
	{
		$root = array();		
		/*参数初始化*/
		$tag = intval($GLOBALS['request']['tag']);
		
		//检查用户,用户密码
		$user = $GLOBALS['user_info'];
		$user_id  = intval($user['id']);			
        $user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){
		    $root['user_login_status'] = $user_login_status;
		}
		else
		{
			$root['user_login_status'] = $user_login_status;		
			
			
			$ext_condition = '';
			$now = NOW_TIME;
			if($tag==1)//即将过期
			{
				$ext_condition = " and c.confirm_time = 0 and c.end_time > 0 and c.end_time > ".$now." and c.end_time - ".$now." < ".(72*3600);				
			}
			if($tag==2)//未使用
			{
				$ext_condition = " and c.is_valid = 1 and c.refund_status = 0 and c.confirm_time = 0 and (c.end_time = 0 or (c.end_time>0 and c.end_time > $now))";
			}
			if($tag==3)//已失效
			{
				$ext_condition = " and (c.is_valid = 2 or c.refund_status = 1 or (c.confirm_time <> 0 or (c.end_time < $now and c.end_time > 0)))";
			}
			
			$root['tag']=$tag;

           
			
			//分页
			$page = intval($GLOBALS['request']['page']);
			$page=$page==0?1:$page;
			
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
			
			
		    $sql = "select doi.id as did,doi.sub_name,doi.name,doi.number,c.sn,c.password,c.end_time,c.confirm_time,c.deal_id,c.order_id,c.order_deal_id,c.supplier_id from ".DB_PREFIX."deal_coupon as c left join ".
		        DB_PREFIX."deal_order_item as doi on doi.id = c.order_deal_id where c.is_valid > 0 and ".
		        " c.user_id = ".$user_id.$ext_condition." order by c.id desc limit ".$limit;
		    $sql_count = "select count(*) from ".DB_PREFIX."deal_coupon as c where c.is_valid > 0 and ".
		        " c.user_id = ".$user_id.$ext_condition;
			

			$list = $GLOBALS['db']->getAll($sql);
			$count = $GLOBALS['db']->getOne($sql_count);
	
			
			
			$page_total = ceil($count/$page_size);
			//end 分页

			//要返回的字段
			$data = array();
			foreach($list as $k=>$v)
			{
			    $temp_arr = array();
			    $temp_arr['sub_name'] = $v['sub_name'];
			    $temp_arr['name'] = $v['name'];
			    $temp_arr['number'] = $v['number'];
			    $temp_arr['password'] = $v['password'];
			    $temp_arr['end_time'] =  $v['end_time']>0?to_date($v['end_time'],"Y-m-d"):"无限时"; //过期时间
			    $temp_arr['confirm_time'] = $v['confirm_time']>0?to_date($v['confirm_time'],"Y-m-d"):'';//验证使用时间
			    $temp_arr['deal_id'] = $v['deal_id'];
			    $temp_arr['order_id'] = $v['order_id'];
			    $temp_arr['order_deal_id'] = $v['order_deal_id'];
			    $temp_arr['supplier_id'] = $v['supplier_id'];
			    $temp_arr['couponSn'] = $v['sn'];
			    $temp_arr['less_time'] = $v['end_time']>0?$v['end_time']-NOW_TIME:"永久";
			    
				//商品信息
				$deal = array();
				$deal = load_auto_cache("deal",array("id"=>$v['deal_id']));
				
				$temp_arr['dealIcon'] = get_abs_img_root(get_spec_image($deal['icon'],140,85,1));
				
				//获取商户数据
				$supplier_id = intval($GLOBALS['db']->getOne("select supplier_id from ".DB_PREFIX."deal where id = ".$v['deal_id']));
				$supplier_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where supplier_id = ".$supplier_id." and is_main = 1");
				
				$temp_arr['spName'] = $supplier_info['name']?$supplier_info['name']:"";	
				$temp_arr['spAddress'] = $supplier_info['address']?$supplier_info['address']:"";
				$temp_arr['spTel'] = $supplier_info['tel']?$supplier_info['tel']:"";
				
				$temp_arr['qrcode'] =  get_abs_img_root(gen_qrcode("tuan".$v['password']));// str_replace('sjmapi', '', get_domain().gen_qrcode($v['password']));
			     
				$data[] = $temp_arr;
			}
			
			$root['item'] = $data;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		}	

		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="我的团购券";
		output($root);
	}	
	
}
?>