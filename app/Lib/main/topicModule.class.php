<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class topicModule extends MainBaseModule
{
	public function index()
	{
			
			global_run();
			init_app_page();
			$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
			$id = intval($_REQUEST['id']);
			require_once APP_ROOT_PATH.'system/model/topic.php';
			$topic = get_topic_item($id);
			if($id >0 && !empty($topic)){
				//
			}else{
				app_redirect(url("index"));
			}
			if($topic['group_id']>0){
				$GLOBALS['tmpl']->assign('topic_group',get_topic_group($topic['group_id']));
			}

			$title = $topic['forum_title'];
			$content = decode_topic($topic['content']);
			$is_fav = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where  (fav_id = ".$id." or (origin_id = ".$id." and fav_id <> 0))  and user_id = ".intval($GLOBALS['user_info']['id']));
			$GLOBALS['tmpl']->assign("topic",$topic);
			$GLOBALS['tmpl']->assign("title",$title);
			$GLOBALS['tmpl']->assign("content",$content);
			$GLOBALS['tmpl']->assign("is_fav",$is_fav);
			$GLOBALS['tmpl']->assign("page_title",$title);
			$GLOBALS['tmpl']->assign("page_keyword",$title.",");
			$GLOBALS['tmpl']->assign("page_description",$title.",");
			$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
			
			$GLOBALS['tmpl']->display("topic_index.html");
	}
	
	public function reply()
	{
		$ajax = 1;
		global_run();
		if(!$GLOBALS['user_info'])
		{
		    $result['status'] = -1000;
		    $result['info'] = "未登录";
		    ajax_return($result);
		}
		if($_REQUEST['content']=='')
		{
			showErr($GLOBALS['lang']['MESSAGE_CONTENT_EMPTY'],$ajax);
		}
		
		/*验证每天只允许评论5次*/
 		$day_send_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_reply where create_time>".to_timespan(to_date(NOW_TIME,"Y-m-d"),"Y-m-d")." and create_time<".NOW_TIME);
		if($day_send_count>=8){
			showErr('今天你已经发很多了哦~',$ajax);
		}
		if(!check_ipop_limit(get_client_ip(),"message",intval(app_conf("SUBMIT_DELAY")),0))
		{
			showErr($GLOBALS['lang']['MESSAGE_SUBMIT_FAST'],$ajax);
		}
		$topic_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".intval($_REQUEST['topic_id']));
		if(!$topic_info)
			showErr("主题不存在",$ajax);
		
		$reply_data = array();
		$reply_data['topic_id'] = intval($_REQUEST['topic_id']);
		$reply_data['user_id'] = intval($GLOBALS['user_info']['id']);
		$reply_data['user_name'] = $GLOBALS['user_info']['user_name'];
		$reply_data['reply_id'] = intval($_REQUEST['reply_id']);
		$reply_data['create_time'] = NOW_TIME;
		$reply_data['is_effect'] = 1;
		$reply_data['is_delete'] = 0;
		$reply_data['content'] = strim(valid_str(addslashes($_REQUEST['content'])));
		require_once APP_ROOT_PATH.'system/model/topic.php';
		$reply_id = insert_topic_reply($reply_data);
		//返回页面的数据
		$reply_data['reply_id'] = $reply_id;
		$reply_data['create_time'] = to_date(NOW_TIME,"Y-m-d H:i");
		$reply_data['avatar'] =  show_avatar($reply_data['user_id'],"small");
		$reply_data['user_url'] = url("index","uc_home#index",array("id"=>$reply_data['user_id']));
		$reply_data['status'] = 1;
		ajax_return($reply_data);
	}
	
}
?>