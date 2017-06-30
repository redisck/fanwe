<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH."system/model/uc_center_service.php";
class uc_collectModule extends MainBaseModule
{
	
	/**
	 * 	 会员中心我的商品团购收藏列表接口
	 * 
	 * 	  输入：
	 *  page:int 当前的页数
	 *  
	 *  输出：
	 * user_login_status:int   0表示未登录   1表示已登录  2表示临时登录
	 * login_info:string 未登录状态的提示信息，已登录时无此项
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * goods_list:array:array 商品和团购收藏列表，结构如下
	 *  Array
        (
            [0] => Array
                (
                    [id] => 67  [int] 收藏对应的商品或团购id
                    [cid] => 27 [int] 收藏记录id                    
                    [sub_name] => 精油开背套餐  [string]商品或团购简短名称
                    [origin_price] => 236.0000  [int]原价
                    [current_price] => 158.0000 [int]当前销售价
                    [buy_count] => 0 [int]销售量
                    [brief] => 【五一广场】爱丁堡尊贵养生会所 [string]简介
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/16/54ed8ed63ee25_280x170.jpg  [string]图片路径 140*85像素
                )
         )     
	 */
	public function index()
	{
		$root = array();		
			
		$user_data = $GLOBALS['user_info'];		
		$user_id = intval($user_data['id']);
		$page = intval($GLOBALS['request']['page'])?intval($GLOBALS['request']['page']):1; //当前分页
		
		$user_login_status = check_login();
		if($user_login_status!=LOGIN_STATUS_LOGINED){			
			$root['user_login_status'] = $user_login_status;
		
		}else{
			$root['user_login_status'] = 1;
			
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;			
			$result = get_collect_list($limit,$user_id);
			$page_total = ceil($result['count']/$page_size);
			$list=array();
			foreach($result['list'] as $k=>$v)
			{	
				$list[$k]['id']=$v['id'];
				$list[$k]['cid']=$v['cid'];
				$list[$k]['icon'] = get_abs_img_root(get_spec_image($v['icon'],140,85,1));
				$list[$k]['sub_name']=$v['sub_name'];
				$list[$k]['origin_price']= round($v['origin_price'],2);
				$list[$k]['current_price']=round($v['current_price'],2);
				$list[$k]['buy_count']=$v['buy_count'];
				$list[$k]['brief']=$v['brief'];
			}
			
			$root['goods_list'] = $list?$list:array();			
			
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$result['count']);
			
			$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
			$root['page_title'].="商品团购收藏";

		}
		
		output($root);

	}

	
	
	
	
	/**
	 * 	 会员中心我的优惠券收藏列表接口
	 * 
	 * 	  输入：
	 *  page:int 当前的页数
	 *  
	 *  输出：
	 * user_login_status:int   0表示未登录   1表示已登录 2表示临时登录
	 * login_info:string 未登录状态的提示信息，已登录时无此项
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * youhui_list:array:array 优惠券收藏列表，结构如下
	 *  Array
        (
            [0] => Array
                (
                    [id] => 67  [int] 收藏对应的优惠券id
                    [cid] => 27 [int] 收藏记录id                    
                    [name] => 精油开背套餐  [string]优惠券名称
                    [down_count] => 0 [int]下载数量
                    [begin_time]=>2015-02-01至2021-02-26[string]  起止时间
                    [list_brief] => 【五一广场】爱丁堡尊贵养生会所 [string]简介
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/16/54ed8ed63ee25_280x170.jpg  [string]图片路径140*85像素
                )
         )     
	 */
	public function youhui_collect()
	{
		$root = array();		
			
		$user_data = $GLOBALS['user_info'];		
		$user_id = intval($user_data['id']);
		$page = intval($GLOBALS['request']['page'])?intval($GLOBALS['request']['page']):1; //当前分页
		
		$user_login_status = check_login();
		if(check_login()!=1){			
			$root['user_login_status'] = $user_login_status;
		
		}else{
			$root['user_login_status'] = 1;
			
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;			
			$result = get_youhui_collect($limit,$user_id);
			$page_total = ceil($result['count']/$page_size);
			$list=array();
			foreach($result['list'] as $k=>$v)
			{	
				$list[$k]['id']=$v['id'];
				$list[$k]['cid']=$v['cid'];
				$list[$k]['icon'] = get_abs_img_root(get_spec_image($v['icon'],140,85,1));
				$list[$k]['name']=$v['name'];
				$list[$k]['down_count']=$v['user_count'];
				$list[$k]['list_brief']=$v['list_brief'];
				
				$begin_time = to_date($v['begin_time'],"Y-m-d");
				$end_time = to_date($v['end_time'],"Y-m-d");
				if($begin_time&&$end_time)
					$time_str = $begin_time."至".$end_time;
				elseif($begin_time&&!$end_time)
				$time_str = $begin_time."开始";
				elseif(!$begin_time&&$end_time)
				$time_str = $end_time."结束";
				else
					$time_str = "无限期";
				$list[$k]['begin_time'] = $time_str;
			}
			
			$root['youhui_list'] = $list?$list:array();			
			
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$result['count']);
			
			$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
			$root['page_title'].="优惠券收藏";

		}
		
		output($root);

	}	
	
	

	
	/**
	 * 	 会员中心我的活动收藏列表接口
	 * 
	 * 	  输入：
	 *  page:int 当前的页数
	 *  
	 *  输出：
	 * user_login_status:int   0表示未登录   1表示已登录  2表示临时登录
	 * login_info:string 未登录状态的提示信息，已登录时无此项
	 * page_title:string 页面标题
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * event_list:array:array 活动收藏列表，结构如下
	 *  Array
        (
            [0] => Array
                (
                    [id] => 67  [int] 收藏对应的活动id
                    [cid] => 27 [int] 收藏记录id                    
                    [name] => 精油开背套餐  [string]活动名称
                    [submit_count] => 0 [int]已报名人数
                    ['sheng_time_format']=>已过期/永不过期/16天03小时12分[string]  活动剩余时间
                    [brief] => 【五一广场】爱丁堡尊贵养生会所 [string]简介
                    [icon] => http://localhost/o2onew/public/attachment/201502/25/16/54ed8ed63ee25_280x170.jpg  [string]图片路径140*85像素
                )
         )     
	 */
	public function event_collect()
	{
		$root = array();		
			
		$user_data = $GLOBALS['user_info'];		
		$user_id = intval($user_data['id']);
		$page = intval($GLOBALS['request']['page'])?intval($GLOBALS['request']['page']):1; //当前分页

		$user_login_status = check_login();
		if(check_login()!=1){			
			$root['user_login_status'] = $user_login_status;

		}else{
			$root['user_login_status'] = 1;
			
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;			
			$result = get_event_collect($limit,$user_id);
			$page_total = ceil($result['count']/$page_size);
			$list=array();
			foreach($result['list'] as $k=>$v)
			{	
				$list[$k]['id']=$v['id'];
				$list[$k]['cid']=$v['cid'];
				$list[$k]['icon'] = get_abs_img_root(get_spec_image($v['icon'],140,85,1));
				$list[$k]['name']=$v['name'];
				$list[$k]['submit_count']=$v['submit_count'];
				$list[$k]['brief']=$v['brief'];				
				if($v['submit_end_time']==0)
				$list[$k]['sheng_time_format']= "永不过期";
				elseif($v['submit_end_time']-NOW_TIME<0)
				$list[$k]['sheng_time_format']="已过期";
				else
				$list[$k]['sheng_time_format']= to_date($v['submit_end_time']-NOW_TIME,"d天h小时i分");
			}
			
			$root['event_list'] = $list?$list:array();			
			
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$result['count']);
			
			$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
			$root['page_title'].="活动收藏";

		}
		
		output($root);

	}		
	
}
?>