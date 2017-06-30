<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------



class uc_myinfoModule extends MainBaseModule
{
	public function index()
	{
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			app_redirect(url("index","user#login"));
		}
		
		$GLOBALS['tmpl']->assign("page_title","我的信息");
		$user_info = $GLOBALS['user_info'];
		
		$conditions = " where user_id = ".$user_info['id'];
		
		$uc_query_data = array();
		if($_REQUEST['query_type']=='score'){
			$query_type = "score";	//积分信息
			$conditions.=" and score<>0 ";
			$uc_query_data['cur_score'] = $user_info['score'];
			$cur_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id=".$user_info['group_id']);
			$uc_query_data['cur_gourp'] = $cur_group['id'];
			$uc_query_data['cur_gourp_name'] = $cur_group['name'];
			$uc_query_data['cur_discount'] = floatval(sprintf('%.2f', $cur_group['discount']*10));
		}else{
			$query_type = "point";	//经验信息
			$conditions.=" and point<>0 ";
			//取出等级信息
			$level_data = load_auto_cache("cache_user_level");			
			$cur_level = $level_data[$GLOBALS['user_info']['level_id']];
			
			//游标移动获取下一个等级
			reset($level_data);
			do{
				$current_data = current($level_data);
				
				if($current_data['id']==$cur_level['id'])
				{
					
					$next_data = next($level_data);
					break;
				}
			}while(next($level_data));
			
			$uc_query_data['cur_level'] = $cur_level['level']; //当前等级
			$uc_query_data['cur_point'] = $user_info['point'];
			$uc_query_data['cur_level_name'] = $cur_level['name'];
			if($next_data){
				$uc_query_data['next_level'] = $next_data['id'];
				$uc_query_data['next_point'] =$next_data['point'] - $user_info['point']; //我再增加：100 经验值，就可以升级为：青铜五
				$uc_query_data['next_level_name'] = $next_data['name'];
			}
			
		}

		
		//取出多少条数据
		$limit = " limit 0,10 ";
		
		$list = $GLOBALS['db']->getAll(" select * from ".DB_PREFIX."user_log ".$conditions." order by log_time desc ".$limit);
		$uc_query_count = $GLOBALS['db']->getOne(" select count(*) from ".DB_PREFIX."user_log ".$conditions." order by id desc ".$limit);
		foreach($list as $k=>$v){
			$v['log_time'] = to_date($v['log_time']);
			$uc_query_list[] = $v;
		}

		//左侧导航菜单	
		assign_uc_nav_list();
		$GLOBALS['tmpl']->assign("uc_query_data",$uc_query_data);
		$GLOBALS['tmpl']->assign("uc_query_list",$uc_query_list);
		$GLOBALS['tmpl']->assign("uc_query_count",$uc_query_count);
		$GLOBALS['tmpl']->assign("query_type",$query_type);
		$GLOBALS['tmpl']->assign("user_info",$user_info);
		$GLOBALS['tmpl']->display("uc/uc_myinfo.html");
	}
	
	
}
?>