<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_youhuiModule extends MainBaseModule
{
	
	/**
	 * 会员中心我的团购券
	 * 输入：
	 * tag:int 优惠券的状态(1 即将过期 /2 未使用 /3 已失效) 不传默认全部
	 * page [int] 分页所在的页数
	 * 
	 * 
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * tag:int 优惠券的状态(1 即将过期 /2 未使用 /3 已失效) 不传默认全部
	 * item:array 
	 * [0] => Array
                (
                    [id]=>1 [int] 优惠券ID 
                    [name] => 华莱士30元抵用券 [string] 优惠券名称
                    [youhui_sn] => 91723490 [string] 优惠券SN
                    [expire_time] => 2015-04-17 [string] 有效日期
                    [confirm_time] => 2015-04-2 [string] 使用时间
                    [icon] => http://localhost/o2onew/public/attachment/201502/26/11/54ee8fc5497f9_320x320.jpg [string] 优惠券ICON 140X85
                    [qrcode] => http://localhost/o2onew/public/images/qrcode/cc/f627508892a154946f4a7ce3a56d4110.png [string]
                )
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * page_title:string 页面标题
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
				$ext_condition = " and confirm_time = 0 and expire_time > 0 and expire_time > ".$now." and expire_time - ".$now." < ".(72*3600);				
			}
			if($tag==2)//未使用
			{
				$ext_condition = " and confirm_time = 0 and (expire_time = 0 or (expire_time>0 and expire_time > $now))";
			}
			if($tag==3)//已失效
			{
				$ext_condition = " and (confirm_time <> 0 or (expire_time < $now and expire_time > 0))";
			}
			
			$root['tag']=$tag;

			//分页
			$page = intval($GLOBALS['request']['page']);
			$page=$page==0?1:$page;
				
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
			
            $sql = "select youhui_id,youhui_sn,total_fee,confirm_time,expire_time from ".DB_PREFIX."youhui_log  where  ".
			" user_id = ".$user_id.$ext_condition." order by  create_time desc limit ".$limit;
		    $sql_count = "select count(*) from ".DB_PREFIX."youhui_log  where  ".
				" user_id = ".$user_id.$ext_condition;
			
       
			$list = $GLOBALS['db']->getAll($sql);
			$count = $GLOBALS['db']->getOne($sql_count);
	
			
			
			$page_total = ceil($count/$page_size);
			//end 分页

			//要返回的字段
			$data = array();
			foreach($list as $k=>$v)
			{
			    $youhui_item = array();
			    $youhui_item = load_auto_cache("youhui",array("id"=>$v['youhui_id']));
			    $temp_arr = array();
			    $temp_arr['id'] = $youhui_item['id'];
			    $temp_arr['name'] = $youhui_item['name'];
			    $temp_arr['youhui_sn'] = $v['youhui_sn'];
			    $temp_arr['expire_time'] = $v['expire_time']>0?to_date($v['expire_time'],"Y-m-d"):"无限时"; //过期时间
			    $temp_arr['confirm_time'] = $v['confirm_time']>0?to_date($v['confirm_time'],"Y-m-d"):'';//验证使用时间
				$temp_arr['icon'] = get_abs_img_root(get_spec_image($youhui_item['icon'],140,85,1));
				$temp_arr['qrcode'] =  get_abs_img_root(gen_qrcode('youhui'.$v['youhui_sn']));
			     
				$data[] = $temp_arr;
			}
			
			$root['item'] = $data;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		}	

		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="我的优惠券";
		output($root);
	}	
	
}
?>