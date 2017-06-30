<?php 
/**
 * 活动报名
 */
require APP_ROOT_PATH.'app/Lib/page.php';
class eventoModule extends BizBaseModule
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
		require_once APP_ROOT_PATH."system/model/user.php";
		
		$name = strim($_REQUEST['name']);
		$begin_time = strim($_REQUEST['begin_time']);
		$end_time = strim($_REQUEST['end_time']);
		
		$begin_time_s = to_timespan($begin_time,"Y-m-d H:i");
		$end_time_s = to_timespan($end_time,"Y-m-d H:i");
		
		$condition = "";
		if($name!=""){
			$event_ids=$GLOBALS['db']->getRow("select group_concat(id SEPARATOR ',') as ids  from ".DB_PREFIX."event where name  like '%".$name."%'");
			$condition .=" and es.event_id in (".$event_ids['ids'].") ";			
		}
			
		if($begin_time_s)
			$condition .=" and es.create_time > ".$begin_time_s." ";
		if($end_time_s)
			$condition .=" and es.create_time < ".$end_time_s." ";
		
		$GLOBALS['tmpl']->assign("name",$name);
		$GLOBALS['tmpl']->assign("begin_time",$begin_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);		
		
		
		
	    //分页
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if($page==0) $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
	   
	    $list = $GLOBALS['db']->getAll("select distinct(es.id),es.* from ".DB_PREFIX."event_submit  as es left join ".DB_PREFIX."event_location_link as l on l.event_id = es.event_id where l.location_id in (".implode(",",$s_account_info['location_ids']).") ".$condition." order by es.is_verify asc,es.create_time desc limit ".$limit);
	   
	    $event_id=0;
	    foreach($list as $k=>$v){
	    	$list[$k]['user_name']=load_user($v['user_id']);
	    	$list[$k]['user_name']=$list[$k]['user_name']['user_name'];
	    	$location_info=load_auto_cache("store",array('id'=>$v['location_id']));	    	
	    	$list[$k]['location_name']=$location_info['name'];
	    		    	
	    	if($event_id!=$v['event_id']){
	    		$event_fields = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."event_field where event_id = ".$v['event_id']." order by sort asc");
				$event_info=load_auto_cache("event",array("id"=>$v['event_id']));
				$event_id=$v['id'];
	    	}
	    	
	    	foreach($event_fields as $kk=>$vv){
				$event_fields[$kk]['result'] = $GLOBALS['db']->getOne("select result from ".DB_PREFIX."event_submit_field where submit_id = ".$v['id']." and field_id = ".$vv['id']." and event_id = ".$v['event_id']);
			}
			$list[$k]['fields']=	$event_fields;	    	
	    	$list[$k]['event_name']=$event_info['name'];	    	
	    	$list[$k]['url']=url('index','event#'.$v['event_id']);
	    	$list[$k]['go_url']=url('biz','evento#approval',array('id'=>$v['id'],'ajax'=>1));
	    	$list[$k]['refuse_url']=url('biz','evento#refuse',array('id'=>$v['id'],'ajax'=>1));
	    }
	    //print_r($list);
	    $total = $GLOBALS['db']->getOne("select count(distinct(es.id)) from ".DB_PREFIX."event_submit  as es  left join ".DB_PREFIX."event_location_link as l on l.event_id = es.event_id where l.location_id in (".implode(",",$s_account_info['location_ids']).") ".$condition);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);


	    $GLOBALS['tmpl']->assign("list",$list);
	    		
		
		$GLOBALS['tmpl']->assign("head_title","活动报名");
		$GLOBALS['tmpl']->display("pages/evento/index.html");	
	
	}
	
	
	public function approval()	{
		
		require_once APP_ROOT_PATH.'system/model/event.php';
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);	
		$id=intval($_REQUEST['id']);
		
		$event_ids=$GLOBALS['db']->getRow("select group_concat(event_id SEPARATOR ',') as ids  from ".DB_PREFIX."event_location_link where location_id in (".implode(",",$s_account_info['location_ids']).")");
		$event_ids=explode(',',$event_ids['ids']);
		
		$auth_id=$GLOBALS['db']->getOne("select event_id from ".DB_PREFIX."event_submit where id=".$id);			
		
		if(!in_array($auth_id,$event_ids)){
			$result['status'] = 2;
			ajax_return($result);
		}
		
		//$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set is_verify=1 where id=".$id." and event_id in (".$event_ids['ids'].")");	
		verify_event_submit($id);
		if($GLOBALS['db']->affected_rows())
		{
			$result['status'] = 1;
			$result['show_code'] = "已审核";			
			ajax_return($result);
		}
		else
		{
			showErr("操作失败",1);
		}	
	
	}
	
	public function refuse()	{
	
		require_once APP_ROOT_PATH.'system/model/event.php';
		$s_account_info = $GLOBALS["account_info"];
		$supplier_id = intval($s_account_info['supplier_id']);
		$id=intval($_REQUEST['id']);
	
		$event_ids=$GLOBALS['db']->getRow("select group_concat(event_id SEPARATOR ',') as ids  from ".DB_PREFIX."event_location_link where location_id in (".implode(",",$s_account_info['location_ids']).")");
		$event_ids=explode(',',$event_ids['ids']);
	
		$auth_id=$GLOBALS['db']->getOne("select event_id from ".DB_PREFIX."event_submit where id=".$id);
	
		if(!in_array($auth_id,$event_ids)){
			$result['status'] = 2;
			ajax_return($result);
		}
	
		//$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set is_verify=1 where id=".$id." and event_id in (".$event_ids['ids'].")");
		refuse_event_submit($id);
		if($GLOBALS['db']->affected_rows())
		{
			$result['status'] = 1;
			$result['show_code'] = "已拒绝";
			ajax_return($result);
		}
		else
		{
			showErr("操作失败",1);
		}
	
	}

}
?>