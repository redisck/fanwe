<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';


class discoverModule extends MainBaseModule
{
	public function index()
	{		
		require_once APP_ROOT_PATH."system/model/topic.php";
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("wrap_type","1"); //宽屏展示
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		
		convert_req($_REQUEST);
		$title = $GLOBALS['lang']['DISCOVER'];
		$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
		$site_nav[] = array('name'=>$title,'url'=>url("index", "discover"));
			
		$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			
		
		
		$cid = intval($_REQUEST['cid']);
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."topic_tag_cate where id = ".$cid);
		$GLOBALS['tmpl']->assign("cid",$cid);
		
		$tag = strim($_REQUEST['tag']);
			
		if($GLOBALS['kw'])
			$tag = $GLOBALS['kw'];
		
		$GLOBALS['tmpl']->assign("tag",$tag);
		if($cate_name)
			$title = $title.$cate_name;
		
		if($tag)
			$title = $title.$tag;
		
		$GLOBALS['tmpl']->assign("page_title",$title);
		$GLOBALS['tmpl']->assign("page_keyword",$title.",");
		$GLOBALS['tmpl']->assign("page_description",$title.",");
		
		if($cid==0)
			$tag_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_tag where is_recommend = 1 order by sort desc limit 10");
		else
			$tag_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."topic_tag as t left join ".DB_PREFIX."topic_tag_cate_link as l on l.tag_id = t.id where l.cate_id = ".$cid." order by t.sort desc limit 10");
		$GLOBALS['tmpl']->assign("tag_list",$tag_list);
		
		
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."topic_tag_cate where showin_web = 1 order by sort desc limit 7");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$GLOBALS['tmpl']->assign("page",$page);
		
		
		$condition = ' is_effect = 1 and is_delete = 0 ';
		$param['cid'] = $cid;
		$param['tag'] = $tag;
		$param_condition = build_topic_filter_condition($param);
		$condition.=" ".$param_condition;
		$condition.=" and fav_id = 0 and relay_id = 0 and has_image = 1 and type in ('share','sharedeal','shareyouhui','shareevent') ";
		
		$sql = "select count(*) from ".DB_PREFIX."topic where ".$condition;
		
		$count = $GLOBALS['db']->getOne($sql);
		$page_size = PIN_PAGE_SIZE;
		$page = new Page($count,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$remain_count = $count-($page-1)*$page_size;  //从当前页算起剩余的数量
		$remain_page = ceil($remain_count/$page_size); //剩余的页数
		if($remain_page == 1)
		{
			//末页
			$step_size = ceil($remain_count/PIN_SECTOR);
		}
		else
		{
			$step_size = ceil(PIN_PAGE_SIZE/PIN_SECTOR);
		}
		$GLOBALS['tmpl']->assign('step_size',$step_size);		
		$GLOBALS['tmpl']->display("discover.html");
	}
	
	
}
?>