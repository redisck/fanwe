<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class uc_reviewModule extends MainBaseModule
{
	
	/**
	 * 会员中心我的点评
	 * 输入：
	 * page [int] 分页所在的页数
	 * 
	 * 
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * item:array 
	 *[1] => Array
                (
                    [type] => "deal"  [string] 点评的数据类型 (1.团购或者商品  deal/2.优惠券  youhui /3.活动  event/4.门店 store)
                    [data_id] => 5 [int] 商品/优惠券/活动/门店 id (手机端根据类型去跳转)
                    [user_name]=>fanwe [string]用户名
                    [content] => 不错不错 [string] 点评内容
                    [create_time] => 2015-04-07 [string] 点评时间
                    [reply_time] => 2015-04-07 [string] 管理员回复时间
                    [reply_content] => 那是不错的了，可以信任的品牌  [string] 管理员回复内容
                    [name] => 仅售388元！价值899元的福州明视眼镜单人配镜套餐，含全场599元以内镜框1次+全场300元以内镜片1次。    [string] 点评对象标题
                    [point] => 5.0000 [float] 点评平均分
                    [images] => Array [array] 图集
                        (
                            [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36_120x120.jpg [string] 点评图片 60X60
                            [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986_120x120.jpg [string] 点评图片 60X60
                            [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061_120x120.jpg [string] 点评图片 60X60
                        )
                     [oimages] => Array [array] 图集
                        (
                            [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36_120x120.jpg [string] 点评图片 原图
                            [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986_120x120.jpg [string] 点评图片 原图
                            [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061_120x120.jpg [string] 点评图片 原图
                        )   

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
			require_once APP_ROOT_PATH."system/model/review.php";
			

			//分页
			$page = intval($GLOBALS['request']['page']);
			$page=$page==0?1:$page;
				
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;
			
			$dp_res = get_dp_list($limit,""," user_id = ".$GLOBALS['user_info']['id']);
			$dp_list = $dp_res['list'];
			
			$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp  where ".$dp_res['condition']);


			$page_total = ceil($count/$page_size);
			//end 分页

			foreach($dp_list as $k=>$v)
			{
			    $temp_arr = array();
			    
			    if($v['deal_id']>0){
			        $data_info = load_auto_cache("deal",array("id"=>$v['deal_id']));
			     
			        $temp_arr['type'] = "deal";
			        $temp_arr['data_id'] = $v['deal_id'];
			    }elseif($v['youhui_id']>0){
			        $data_info = load_auto_cache("youhui",array("id"=>$v['youhui_id']));
			        
			        $temp_arr['type'] = "youhui";
			        $temp_arr['data_id'] = $v['youhui_id'];
			    }elseif($v['event_id']>0){
			        $data_info = load_auto_cache("event",array("id"=>$v['event_id']));
			        $temp_arr['type'] = "event";
			        $temp_arr['data_id'] = $v['event_id'];
			    }
			    if(empty($data_info)){
			        $data_info = load_auto_cache("store",array("id"=>$v['supplier_location_id']));
			        $temp_arr['type'] = "store";
			        $temp_arr['data_id'] = $v['supplier_location_id'];
			    }
			        
		
			    
			    
			    $temp_arr['user_name'] = $user['user_name'];
			    $temp_arr['content'] = $v['content'];
			    $temp_arr['create_time'] = $v['create_time'] > 0 ?to_date($v['create_time'],'Y-m-d'):'';
			    $temp_arr['reply_time'] = $v['reply_time'] > 0 ?to_date($v['reply_time'],'Y-m-d'):'';  
			    $temp_arr['reply_content'] = $v['reply_content']?$v['reply_content']:'';
			    
			    $temp_arr['name'] = $data_info['name'];
			    $temp_arr['point'] = $v['point']>0?$v['point']:0;

			    $images = array();
			    $oimages = array();
			    if($v['images']){
			        foreach ($v['images'] as $ik=>$iv){
			            $images[] = get_abs_img_root(get_spec_image($iv,60,60,1));
			            $oimages[] = get_abs_img_root($iv);
			        }
			        
			    }
			    $temp_arr['images'] = $images;
			    $temp_arr['oimages'] = $oimages;
			    $data[] = $temp_arr;
			}


			
			$root['item'] = $data;
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
		
		}	

		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="我的点评";
		output($root);
	}	
	
}
?>