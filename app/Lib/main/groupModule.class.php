<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
require_once APP_ROOT_PATH.'system/model/topic.php';
class groupModule extends MainBaseModule
{
	public function index()
	{	
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$title = $GLOBALS['lang']['GROUP_FORUM'];
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
		$site_nav[] = array('name'=>$title,'url'=>url("index", "group"));		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);
	
		//输出热门小组
		$hot_group = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group where is_effect = 1 order by topic_count desc,user_count desc limit 4");
		$GLOBALS['tmpl']->assign("hot_group",$hot_group);	
		
		if(intval($_REQUEST['id'])>0){
			$page_size = 20;
			$page = intval($_REQUEST['p']);
			if($page<=0)	$page = 1;
			$limit = (($page-1)*$page_size).",".$page_size;
			$cate_condition = " and id = ".intval($_REQUEST['id']);
		}else{
			$limit =6;
			$cate_condition=" and 1=1 ";
		}
		//输出分类列表
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group_cate where is_effect = 1 ".$cate_condition." order by sort desc");		
		foreach($cate_list as $k=>$v){
			$sql = " select * from ".DB_PREFIX."topic_group where is_effect = 1  and cate_id = ".$v['id']." order by sort asc limit ".$limit;
			$cate_list[$k]['list']=$GLOBALS['db']->getAll($sql);
			if(intval($_REQUEST['id'])>0){
				$sql_count = "select count(*) from ".DB_PREFIX."topic_group where is_effect = 1 and cate_id = ".$v['id']." order by sort asc";
				$count = $GLOBALS['db']->getOne($sql_count);
			}			
		}
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);//print_r($cate_list);
		
		if(intval($_REQUEST['id'])>0){
			$page = new Page($count,$page_size);   //初始化分页对象 		
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		
		//输出优秀小组长
		$group_adm_list = $GLOBALS['db']->getAll("select id,name,user_count,user_id from ".DB_PREFIX."topic_group  where is_effect = 1 and user_id <> 0 group by user_id order by topic_count desc limit 5");
		$GLOBALS['tmpl']->assign("group_adm_list",$group_adm_list);
		


		//输出推荐的主题
		$rec_topic_list = load_auto_cache("recommend_forum_topic");
		$GLOBALS['tmpl']->assign("rec_topic_list",$rec_topic_list);
		
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");			

		$GLOBALS['tmpl']->display("group_index.html");
		
	}
	

	public function create()
	{  
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$user_id =intval($GLOBALS['user_info']['id']);		
		if($user_id==0){
			app_redirect(url("index","user#login"));	
		}		
		
		$title = "申请创建小组";
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
		$site_nav[] = array('name'=>$GLOBALS['lang']['GROUP_FORUM'],'url'=>url("index", "group"));
		$site_nav[] = array('name'=>$title,'url'=>url("index", "group#create"));
		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");
		
		
		$cate_list=  $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_group_cate where is_effect = 1 order by sort asc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		$GLOBALS['tmpl']->display("group_create.html");
	}
	

	public function do_create_group()
	{
		global_run();
		$user_id =intval($GLOBALS['user_info']['id']);
		
		if($user_id==0){
			$result['status'] = 2;
			ajax_return($result);
		}
		
		$cate_id = intval($_REQUEST['cate_id']);
		$name = htmlspecialchars(strim($_REQUEST['name']));
		$memo = htmlspecialchars(strim($_REQUEST['memo']));
		$icon = htmlspecialchars(strim($_REQUEST['icon']));
		$image = htmlspecialchars(strim($_REQUEST['image']));
		if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_group where user_id=".$user_id." and is_effect = 2")){
			$result['status'] = 0;
			$result['info'] = "您还有申请未审核通过的小组";
			ajax_return($result);
		}
		$group['name'] = $name;
		$group['icon'] = $icon;
		$group['image'] = $image;
		$group['memo'] = $memo;
		$group['cate_id'] = $cate_id;
		$group['user_id'] = $user_id;
		$group['create_time'] = get_gmtime();
		$group['is_effect'] =2;
		$GLOBALS['db']->autoExecute(DB_PREFIX."topic_group",$group);
		$group_id = intval($GLOBALS['db']->insert_id());
		if($group_id>0)
		{			
			$GLOBALS['db']->query("delete from ".DB_PREFIX."user_auth where user_id = ".$user_id." and m_name = 'group' and rel_id = ".$group_id);
			
			//为组长加权限
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "del";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
					
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "replydel";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
					
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "settop";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
					
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "setbest";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
					
			$auth_data = array();
			$auth_data['m_name'] = "group";
			$auth_data['a_name'] = "setmemo";
			$auth_data['user_id'] = $user_id;
			$auth_data['rel_id'] = $group_id;
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_auth",$auth_data);
			
			$result['status'] = 1;
			$result['url'] = url("index","group");
			ajax_return($result);
		}
		else
		{
			$result['status'] = 0;
			$result['info'] = "申请失败";
			ajax_return($result);
		}
	}	
	
	
	public function forum()
	{
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$group_id = intval($_REQUEST['id']);
		$group_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where is_effect = 1 and id = ".$group_id);
		if(!$group_item)showErr("不存在的小组");
		$GLOBALS['tmpl']->assign("group_info",$group_item);
		
		$title = $group_item['name'];
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
		$site_nav[] = array('name'=>$GLOBALS['lang']['GROUP_FORUM'],'url'=>url("index", "group"));
		$site_nav[] = array('name'=>$title,'url'=>url("index", "group#forum",array("id"=>$group_id)));
		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");		
		
		
		//输出是否加入组
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0){
			$is_join = 0;
		}else{
			$is_admin = 0;
			if($group_item['user_id']==$user_id){
				$is_admin = 1;
			}
			if($is_admin==0){
				$join_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_topic_group where user_id = ".$user_id." and group_id = ".$group_item['id']);
				if($join_data){
					$is_join = 1;
					$is_admin = $join_data['type'];
				}else{
					$is_join = 0;
				}
			}else{
				$is_join = 1;
			}			
		}
		$GLOBALS['tmpl']->assign('is_join',$is_join);
		$GLOBALS['tmpl']->assign('is_admin',$is_admin);
		
		//输出列表
		$page_size = app_conf("PAGE_SIZE");
		$page = intval($_REQUEST['p']);
		if($page<=0)	$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;		
		
		$condition = " group_id = ".$group_item['id'];
		$sortby = "is_top desc,create_time desc";
		$sortkey = "ordery_sort";
		
		$filter = intval($_REQUEST['filter']); //0全部 1推荐
		$sort = intval($_REQUEST['sort']); //0创建时间 1回复时间
		$url_param = array("filter"=>$filter,"sort"=>$sort,"p"=>$page,"id"=>$group_id);
		
		if($filter==1){
			$condition.=" and is_best = 1 ";			
		}
		if($sort==1){
			$sortby = " is_top desc,last_time desc ";
			$sortkey = "last_time_sort";
		}
		
		$tmp_url_param = $url_param;
		$tmp_url_param['filter'] = 0;
		$urls['all'] = url("index","group#forum",$tmp_url_param);
		
		$tmp_url_param = $url_param;
		$tmp_url_param['filter'] = 1;
		$urls['is_best'] = url("index","group#forum",$tmp_url_param);
		
		$tmp_url_param = $url_param;
		$tmp_url_param['sort'] = 0;
		$urls['create_time'] = url("index","group#forum",$tmp_url_param);
		
		$tmp_url_param = $url_param;
		$tmp_url_param['sort'] = 1;
		$urls['last_time'] = url("index","group#forum",$tmp_url_param);
		
		$GLOBALS['tmpl']->assign("urls",$urls);
		
//		$sql = "select * from ".DB_PREFIX."topic use index($sortkey) where is_effect = 1 and is_delete = 0  $condition order by $sortby limit ".$limit;
//		$sql_count = "select count(*) from ".DB_PREFIX."topic use index($sortkey) where is_effect = 1 and is_delete = 0 $condition ";		
//		$list = $GLOBALS['db']->getAll($sql);
//		$count = $GLOBALS['db']->getOne($sql_count);

		$list=get_topic_list($limit,array("cid"=>0,"tag"=>""),"",$condition,$sortby);
		$c_condition=$list['condition'];
		$list=$list['list'];
		
		//$count=count($list);
		$sql_c="select count(*) from ".DB_PREFIX."topic as t  where ".$c_condition;
		$count=$GLOBALS['db']->getOne($sql_c);
		
		$GLOBALS['tmpl']->assign("list",$list);
		$page = new Page($count,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);		
		
		$join_url = url("index","group#joingroup",array("id"=>$group_id));
		$exit_url = url("index","group#exitgroup",array("id"=>$group_id));
		$edit_url = url("index","group#edit",array("id"=>$group_id));
		$GLOBALS['tmpl']->assign("join_url",$join_url);		
		$GLOBALS['tmpl']->assign("exit_url",$exit_url);
		$GLOBALS['tmpl']->assign("edit_url",$edit_url);
		
		//输出组员
		$user_list = $GLOBALS['db']->getAll("select user_id as id,type from ".DB_PREFIX."user_topic_group where group_id = ".$group_item['id']." order by type desc limit 10 ");
		$GLOBALS['tmpl']->assign('user_list',$user_list);
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
		$GLOBALS['tmpl']->display("group_forum.html");
	}

	
	public function joingroup()
	{	
		global_run();
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$result['status'] = 2;
			ajax_return($result);
		}
		$group_id = intval($_REQUEST['id']);
		$group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$group_id);
		if($group['user_id']!=$user_id)
		{
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_topic_group where group_id = ".$group_id." and user_id = ".$user_id)==0)
			{
				$data['group_id'] = $group_id;
				$data['user_id'] = $user_id;
				$data['create_time'] = get_gmtime();
				$GLOBALS['db']->autoExecute(DB_PREFIX."user_topic_group",$data,"INSERT","","SILENT");
				$id = $GLOBALS['db']->insert_id();
				if($id){
					$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set user_count = user_count + 1 where id=".$group_id);
					$result['status']= 1;
					ajax_return($result);
				}else{
					$result['status']= 0;
					ajax_return($result);
				}
			}else{
				//已加入小组
				$result['status']= 0;
				ajax_return($result);
			}
		}else{
			//组长不用加入
			$result['status']= 0;
			ajax_return($result);
		}
	}	
	

	public function exitgroup()
	{	
		global_run();
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			$result['status'] = 2;
			ajax_return($result);
		}
		$group_id = intval($_REQUEST['id']);
		$group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$group_id);
		if($group['user_id']!=$user_id)
		{
			if($GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_topic_group where group_id = ".$group_id." and user_id = ".$user_id)>0)
			{
				$GLOBALS['db']->query("delete from ".DB_PREFIX."user_topic_group where group_id = ".$group_id." and user_id = ".$user_id);
				if($GLOBALS['db']->affected_rows()>0){
					$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set user_count = user_count - 1 where id=".$group_id);
					$result['status']= 1;
					ajax_return($result);
				}else{
					$result['status']= 0;
					ajax_return($result);
				}
			}else{
				//未加入小组
				$result['status']= 0;
				ajax_return($result);
			}
		}else{
			//组长不能退出
			$result['status']= 3;
			ajax_return($result);
		}
	}
	
	
	public function edit()
	{
		$id = intval($_REQUEST['id']);
		$group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$id);
		$GLOBALS['tmpl']->assign("group",$group);	
		$GLOBALS['tmpl']->display("group_edit.html");
	}	
	
	public function submit()
	{	
		global_run();
		$id = intval($_REQUEST['id']);					
		$reason = strim($_REQUEST['reason']);			
		$group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where id = ".$id);

		if($this->check_user_auth("group","setmemo",$group['id']))
		{			
			$sql = "update ".DB_PREFIX."topic_group set memo = '".$reason."' where id = ".$id;	
			$GLOBALS['db']->query($sql);			
			$result['status'] = 1;			
		}else{
			$result['status'] = 0;
			$result['info'] = "没有权限";
		}
		ajax_return($result);
	}
	
	
	
	function check_user_auth($m_name,$a_name,$rel_id)
	{
		$rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_auth where m_name = '".$m_name."' and a_name = '".$a_name."' and user_id = ".intval($GLOBALS['user_info']['id']));
		
		foreach($rs as $row)
		{
			if($row['rel_id']==0||$row['rel_id']==$rel_id) return true;
		}
		return false;
	}
	
	
	
	
	public function user_list()
	{
		global_run();
		init_app_page();	
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		$user_id =intval($GLOBALS['user_info']['id']);
		$group_id = intval($_REQUEST['id']);
		$group_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where is_effect = 1 and id = ".$group_id);
		if(!$group_item)	showErr("不存在的小组");
		$GLOBALS['tmpl']->assign("group_info",$group_item);		
		
		$title = $group_item['name']."组员";
		
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index","index"));
		$site_nav[] = array('name'=>$GLOBALS['lang']['GROUP_FORUM'],'url'=>url("index", "group"));
		$site_nav[] = array('name'=>$title,'url'=>url("index", "group#forum",array("id"=>$group_id)));
		$site_nav[] = array('name'=>"组员",'url'=>url("index", "group#user_list",array("id"=>$group_id)));
		
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);			
			
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");		
		
		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where is_effect=1 and  id = ".intval($GLOBALS['user_info']['id']));
		$user_leader = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where is_effect=1 and  id = ".$group_item['user_id']);
		$focus_leader = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$user_leader['id']);
		if($focus_leader) $user_leader['focused'] = 1;
		$GLOBALS['tmpl']->assign("user_leader",$user_leader);
		
		$page_size = 24;		
		$page = intval($_REQUEST['p']);
		if($page<=0)	$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;	
		
		//输出粉丝
		$user_list = $GLOBALS['db']->getAll("select user_id as id from ".DB_PREFIX."user_topic_group where group_id = ".$group_id."  limit ".$limit);
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_topic_group where group_id = ".$group_id);
		
		foreach($user_list as $k=>$v)
		{			
			$focus_uid = intval($v['id']);
			$focus_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_focus where focus_user_id = ".$user_id." and focused_user_id = ".$focus_uid);
			if($focus_data) $user_list[$k]['focused'] = 1;
		}
		$GLOBALS['tmpl']->assign("user_list",$user_list);

		$page = new Page($total,$page_size);   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);		
		
		$GLOBALS['tmpl']->display("group_user_list.html");
	}	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}



?>