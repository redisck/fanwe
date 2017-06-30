<?php 
/**
 * 优惠券下载记录
 */
require APP_ROOT_PATH.'app/Lib/page.php';
require_once APP_ROOT_PATH."system/model/user.php";
class youhuioModule extends BizBaseModule
{
    
	function __construct()
	{
        parent::__construct();
        global_run();
        $this->check_auth();
    }
	
    
	public function index()
	{		
				
		init_app_page();
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		
		
		$name = strim($_REQUEST['name']);
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);
		
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i");
		$end_time_s = to_timespan($end_time,"Y-m-d H:i");
		
		$condition = "";
		if($name!=""){
			$youhui_ids=$GLOBALS['db']->getRow("select group_concat(id SEPARATOR ',') as ids  from ".DB_PREFIX."youhui where name  like '%".$name."%'");
			$condition .=" and log.youhui_id in (".$youhui_ids['ids'].") ";			
		}
			
		if($begin_time_s)
			$condition .=" and log.create_time > ".$begin_time_s." ";
		if($end_time_s)
			$condition .=" and log.create_time < ".$end_time_s." ";
		
		$GLOBALS['tmpl']->assign("name",$name);
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
	    //分页
	    $page_size = 15;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
	   
	    $list = $GLOBALS['db']->getAll("select distinct(log.id),log.* from ".DB_PREFIX."youhui_log as log  left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = log.youhui_id where l.location_id in (".implode(",",$s_account_info['location_ids']).") ".$condition." order by log.create_time desc limit ".$limit);
	    foreach($list as $k=>$v){
	    	$list[$k]['user_name']=load_user($v['user_id']);
	    	$list[$k]['user_name']=$list[$k]['user_name']['user_name'];
	    	$youhui_info=load_auto_cache("youhui",array('id'=>$v['youhui_id']));
	    	$list[$k]['youhui_name']=$youhui_info['name'];
	    	$location_info=load_auto_cache("store",array('id'=>$v['location_id']));	    	
	    	$list[$k]['location_name']=$location_info['name'];	    	
	    	if($list[$k]['expire_time']!=0 && $list[$k]['expire_time']<NOW_TIME){
	    		$list[$k]['expire_time']="已过期";
	    	}elseif($list[$k]['expire_time']==0){
	    		$list[$k]['expire_time']="永久有效";
	    	}else{
	    		$list[$k]['expire_time']=to_date($list[$k]['expire_time']);
	    	}
	    	$list[$k]['url']=url('index','youhui#'.$v['youhui_id']);
	    }
	    
	    $total = $GLOBALS['db']->getOne("select count(distinct(log.id)) from ".DB_PREFIX."youhui_log as log  left join ".DB_PREFIX."youhui_location_link as l on l.youhui_id = log.youhui_id where l.location_id in (".implode(",",$s_account_info['location_ids']).") ".$condition);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);


	    $GLOBALS['tmpl']->assign("list",$list);
	    		
		
		$GLOBALS['tmpl']->assign("head_title","优惠券下载记录");
		$GLOBALS['tmpl']->display("pages/youhuio/index.html");	
	
	}
	
	

}
?>