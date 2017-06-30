<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
require_once APP_ROOT_PATH."system/model/dc.php";
class dcreminderModule extends BizBaseModule
{
   public function index(){
   	
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		
		$account_info = $GLOBALS['account_info'];
		$supplier_id = $account_info['supplier_id'];
		$account_id = $account_info['id'];
		
		$begin_time = to_timespan(strim($_REQUEST['begin_time']));
	 	$end_time = to_timespan(strim($_REQUEST['end_time']));
		$keywords = strim($_REQUEST['keywords']);
		require_once APP_ROOT_PATH."app/Lib/page.php";
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		

		if($keywords==''){
			
			$sql="select * from ".DB_PREFIX."dc_reminder where supplier_id=".$supplier_id;
			if($begin_time > 0){
				$sql.=" and create_time >= $begin_time";
			}
			if($end_time > 0){
				$sql.=" and create_time <= $end_time";
			}
			$condition=$sql;
			$sql.=" order by id desc limit ".$limit;
			
		}else{
			
			$sql="select * from ".DB_PREFIX."dc_reminder where order_sn like '%".$keywords."%' or mobile like '%".$keywords."%' and supplier_id=".$supplier_id;
			$condition=$sql;
		}
		


   		$list=$GLOBALS['db']->getAll($sql);
   		$count=intval(count($GLOBALS['db']->getAll($condition)));
   		$page = new Page($count, $page_size); // 初始化分页对象
   		$p = $page->show();
   		$GLOBALS['tmpl']->assign('pages', $p);
   		$GLOBALS['tmpl']->assign("list", $list);
   		$GLOBALS['tmpl']->assign("keywords", $keywords);
   		$GLOBALS['tmpl']->assign("supplier_id", $supplier_id);
   		$form_url=url('biz','dcreminder');
   		$GLOBALS['tmpl']->assign("form_url", $form_url);
   		/* 系统默认 */
   		$GLOBALS['tmpl']->assign("page_title", "外卖催单记录");
   		$GLOBALS['tmpl']->display("pages/dc/dcreminder_index.html");
   }	
}
?>