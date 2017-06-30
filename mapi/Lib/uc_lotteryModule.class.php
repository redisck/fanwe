<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_lotteryModule extends MainBaseModule
{
	
	/**
	 * 会员中心我的抽奖
	 * 输入：
	 * page [int] 分页所在的页数
	 * 
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * item:array 
	 * 
            [0] => Array
                (
                    [name] => 美梦成真：香奈儿COCO小姐，美团网免费送 [string] 抽奖券名
                    [lottery_sn] => 000001  [string] 抽奖券序号
                    [create_time] => 2015-04-03 [string] 抽奖日期
                    [icon] => http://localhost/o2onew/public/attachment/201504/03/17/551e5f39d9876_280x170.jpg [string] 抽奖券缩略图 140X85
                )
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * page_title:string 页面标题
	 */
	public function index()
	{
		$root = array();		
		/*参数初始化*/
		
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
			
			
			//分页
			$page = intval($GLOBALS['request']['page']);
			$page=$page==0?1:$page;
				
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
			
    		$sql = "select * from ".DB_PREFIX."lottery  where  ".
    			" user_id = ".$user_id." order by  create_time desc limit ".$limit;
    		$sql_count = "select count(*) from ".DB_PREFIX."lottery  where  ".
    				" user_id = ".$user_id;
    	
    		$list = $GLOBALS['db']->getAll($sql);
			$count = $GLOBALS['db']->getOne($sql_count);
	
			
			
			$page_total = ceil($count/$page_size);
			//end 分页

			//要返回的字段
			$data = array();
			foreach($list as $k=>$v)
			{
			    $lotter_item = array();
			    $lotter_item = load_auto_cache("deal",array("id"=>$v['deal_id']));
			    $temp_arr = array();
			    $temp_arr['name'] = $lotter_item['name'];
			    $temp_arr['lottery_sn'] = $v['lottery_sn'];
			    $temp_arr['create_time'] = $v['create_time']>0?to_date($v['create_time'],"Y-m-d"):'';
				$temp_arr['icon'] = get_abs_img_root(get_spec_image($lotter_item['icon'],140,85,1));   
				$data[] = $temp_arr;
			}
			
			$root['item'] = $data;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		}	

		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="我的抽奖";
		output($root);
	}	
	
}
?>