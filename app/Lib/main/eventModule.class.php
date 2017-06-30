<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class eventModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();
		
		$GLOBALS['tmpl']->assign("no_nav",true);
		$id = intval($_REQUEST['act']);
		require_once APP_ROOT_PATH."system/model/event.php";
		$event = get_event($id);
		
		
		if($event)
		{
			
			set_view_history("event", $event['id']);
			$history_ids = get_view_history("event");
			
			//浏览历史
			if($history_ids)
			{
				$ids_conditioin = " e.id in (".implode(",", $history_ids).") ";
				$history_deal_list = get_event_list(app_conf("SIDE_DEAL_COUNT"),array(EVENT_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
						
				//重新组装排序
				$history_list = array();
				foreach($history_ids as $k=>$v)
				{
					foreach($history_deal_list['list'] as $history_item)
					{
						if($history_item['id']==$v)
						{
							$history_list[] = $history_item;
						}
					}
				}
				$GLOBALS['tmpl']->assign("history_deal_list",$history_list);
			}
			
			$event['content'] = format_html_content_image($event['content'],720);
			$event['submitted_data'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where event_id = ".$event['id']." and user_id = '".$GLOBALS['user_info']['id']."'");

			$GLOBALS['tmpl']->assign("event",$event);
			$GLOBALS['tmpl']->assign("NOW_TIME",NOW_TIME);
				
			//输出右侧的其他优惠券
			$side_event_list = get_event_list(app_conf("SIDE_DEAL_COUNT"),array(EVENT_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",""," e.submit_count desc ");
			$GLOBALS['tmpl']->assign("side_event_list",$side_event_list['list']);
			
			//关于分类信息与seo
			$page_title = "";
			$page_keyword = "";
			$page_description = "";
			if($event['supplier_info']['name'])
			{
				$page_title.="[".$event['supplier_info']['name']."]";
				$page_keyword.=$event['supplier_info']['name'].",";
				$page_description.=$event['supplier_info']['name'].",";
			}
			$page_title.= $event['name'];
			$page_keyword.=$event['name'].",";
			$page_description.=$event['name'].",";
				
			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
				
			if($event['cate_id'])
			{
				$event['cate_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."event_cate where id = ".$event['cate_id']);
				$event['cate_url'] = url("index","events",array("cid"=>$event['cate_id']));
			}
			
			if($event['cate_name'])
			{
				$page_title.=" - ".$event['cate_name'];
				$page_keyword.=$event['cate_name'].",";
				$page_description.=$event['cate_name'].",";
				$site_nav[] = array('name'=>$event['cate_name'],'url'=>$event['cate_url']);
			}
			$site_nav[] = array('name'=>$event['name'],'url'=>$event['url']);
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
				

			$GLOBALS['tmpl']->assign("page_title",$page_title);
			$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
			$GLOBALS['tmpl']->assign("page_description",$page_description);
				
			$GLOBALS['tmpl']->display("event.html");
			
		}
		else
		{
			app_redirect_preview();
		}
	}
	
	
	public function do_submit()
	{
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{
			$data['status'] = 1000;
			ajax_return($data);
		}
		
		$event_id = intval($_REQUEST['event_id']);
		require_once APP_ROOT_PATH."system/model/event.php";
		$event = get_event($event_id);
		if(!$event)
		{
			$data['status'] = 0;
			$data['info'] = "活动不存在";
			ajax_return($data);
		}
		if($event['submit_begin_time']>NOW_TIME)
		{
			$data['status'] = 0;
			$data['info'] = "活动报名未开始";
			ajax_return($data);
		}
		if($event['submit_end_time']>0&&$event['submit_end_time']<NOW_TIME)
		{
			$data['status'] = 0;
			$data['info'] = "活动报名已结束";
			ajax_return($data);
		}
		if($event['submit_count']>=$event['total_count']&&$event['total_count']>0)
		{
			$data['status'] = 0;
			$data['info'] = "活动名额已满";
			ajax_return($data);
		}
		
		$user_id = intval($GLOBALS['user_info']['id']);
		$user_submit = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."event_submit where user_id = ".$user_id." and event_id = ".$event_id);
		if($user_submit)
		{
			if($user_submit['is_verify']==1)
			{
				$data['status'] = 0;
				$data['info'] = "您已经报名";
				ajax_return($data);
			}
			elseif($user_submit['is_verify']==2)
			{
				$data['status'] = 0;
				$data['info'] = "您的报名审核不通过";
				ajax_return($data);
			}
			else
			{
				//已经报名，仅作修改
				$GLOBALS['db']->query("delete from ".DB_PREFIX."event_submit_field where submit_id = ".$user_submit['id']);
				$field_ids = $_REQUEST['field_id'];
				foreach($field_ids as $field_id)
				{
					$current_result =  strim($_REQUEST['result'][$field_id]);
					$field_data = array();
					$field_data['submit_id'] = $user_submit['id'];
					$field_data['field_id'] = $field_id;
					$field_data['event_id'] = $event_id;
					$field_data['result'] = $current_result;
					$GLOBALS['db']->autoExecute(DB_PREFIX."event_submit_field",$field_data,"INSERT");
				}
				$result['status'] = 1;
				$result['info'] = "报名修改成功";
				ajax_return($result);
			}
		}
		else
		{
			
			$GLOBALS['db']->query("update ".DB_PREFIX."event set submit_count = submit_count+1 where id=".$event_id." and submit_count + 1 <= total_count and total_count > 0");			
			if(!$GLOBALS['db']->affected_rows())
			{
				$data['status'] = 0;
				$data['info'] = "活动名额已满";
				ajax_return($data);
			}
			
			
			if($event['score_limit']>0||$event['point_limit']>0)
			{
				$c_user_info = $GLOBALS['user_info'];
			
				if($c_user_info['score']<$event['score_limit'])
				{
					$data['status'] = 0;
					$data['info'] = "积分不足，不能报名";
					ajax_return($data);
				}
			
				if($c_user_info['point']<$event['point_limit'])
				{
					$data['status'] = 0;
					$data['info'] = "经验不足，不能报名";
					ajax_return($data);
				}
			}
			
			$submit_data = array();
			$submit_data['user_id'] = $user_id;
			$submit_data['event_id'] = $event_id;
			$submit_data['create_time'] = NOW_TIME;
			$submit_data['event_begin_time'] = $event['event_begin_time'];
			$submit_data['event_end_time'] = $event['event_end_time'];
			$submit_data['return_money'] = $event['return_money'];
			$submit_data['return_score'] = $event['return_score'];
			$submit_data['return_point'] = $event['return_point'];
			$GLOBALS['db']->autoExecute(DB_PREFIX."event_submit",$submit_data,"INSERT");
			$submit_id = $GLOBALS['db']->insert_id();
			if($submit_id)
			{
				$field_ids = $_REQUEST['field_id'];
				foreach($field_ids as $field_id)
				{
					$current_result =  strim($_REQUEST['result'][$field_id]);
					$field_data = array();
					$field_data['submit_id'] = $submit_id;
					$field_data['field_id'] = $field_id;
					$field_data['event_id'] = $event_id;
					$field_data['result'] = $current_result;
					$GLOBALS['db']->autoExecute(DB_PREFIX."event_submit_field",$field_data,"INSERT");
				}
				
				if($event['is_auto_verify']==1)
				{
					//自动审核，发券
					$sn = verify_event_submit($submit_id);
				}
					
				//同步分享
				$title = "报名参加了".$event['name'];
				$content = "报名参加了".$event['name'];
				$url_route = array(
						'rel_app_index'	=>	'index',
						'rel_route'	=>	'event#'.$event['id'],
						'rel_param' => ''
				);
					
				require_once APP_ROOT_PATH."system/model/topic.php";
				$tid = insert_topic($content,$title,$type="eventsubmit",$group="", $relay_id = 0, $fav_id = 0,$group_data ="",$attach_list=array(),$url_route);
				if($tid)
				{
					$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
				}
					
				require_once APP_ROOT_PATH."system/model/user.php";
				modify_account(array("score"=>"-".$event['score_limit']), $user_id,"活动报名：".$event['name']);
				
				$data['status'] = 1;
				$data['info'] = "报名成功";
				if($sn)
					$data['info'].="，验证码：".$sn;
				
				rm_auto_cache("event",array("id"=>$event['id']));
				ajax_return($data);
			}
			else
			{
				$data['status'] = 0;
				$data['info'] = "报名失败";
				ajax_return($data);
			}
		}
		
		
	}
	
}
?>