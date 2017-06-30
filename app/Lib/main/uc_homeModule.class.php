<?php
require APP_ROOT_PATH.'app/Lib/page.php';
class uc_homeModule extends MainBaseModule{
	public function index(){
		global_run();
		init_app_page();

		$id = intval($_REQUEST['id']);
		$is_why = 0; //1 自己，2其它登录用户看，3未登录用户看
		if($id)
		{
			if($id == $GLOBALS['user_info']['id'])
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
			else
			{
				$is_why = 3;
				$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$id);
				$GLOBALS['tmpl']->assign("id",$id);
				if($home_user_info){
					$is_why = 2;
					$my_info =$GLOBALS['user_info'];
					$is_fav = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$my_info['id']." and focused_user_id=".$id)) ;
					$GLOBALS['tmpl']->assign("is_fav",$is_fav);
					$GLOBALS['tmpl']->assign("my_info",$my_info);
				}
			}
		}
		else
		{
			if(empty($GLOBALS['user_info']))
			{
				showErr("请先登录",url("index","user#login"));
			}
			else
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
		}

		$GLOBALS['tmpl']->assign("is_why",$is_why);
		$GLOBALS['tmpl']->assign("home_user_info",$home_user_info);
		
		$uc_nav = get_uc_nav($id);
		$GLOBALS['tmpl']->assign("uc_nav",$uc_nav);

		$region_list = load_auto_cache("cache_region_conf");
			
		$province_str = $region_list[$home_user_info['province_id']]['name'];
		$city_str = $region_list[$home_user_info['city_id']]['name'];

		if($province_str.$city_str=='')
			$user_location = '';
		else
			$user_location = $province_str.$city_str;
		
		$home_user_info['medal_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_medal where is_delete = 0 and user_id = ".$home_user_info['id']." order by create_time desc");
		
		//瀑布流分页
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$GLOBALS['tmpl']->assign("page",$page);
		//我关注的用户
		$focus_user_list = load_auto_cache("cache_focus_user",array("uid"=>$home_user_info['id']));
		$t_ids[] = $home_user_info['id'];
		foreach($focus_user_list as $k=>$v){
			$t_ids[] = $v['id'];
		}
		$condition =" user_id in (".implode(",", $t_ids).") and is_effect = 1 and is_delete = 0  and fav_id = 0 and relay_id = 0  and type in ('share','dealcomment','youhuicomment','eventcomment','slocationcomment','eventsubmit','sharedeal','shareyouhui','shareevent') ";
		
		$sql = "select count(*) from ".DB_PREFIX."topic where ".$condition;

		$count = $GLOBALS['db']->getOne($sql);
		
		if($count==0){
			$GLOBALS['tmpl']->assign("is_best_user",1);
		}
		
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
		$GLOBALS['tmpl']->assign("user_location",$user_location);
		$GLOBALS['tmpl']->display("uc_home_index.html");
	}
	
	public function myfav(){
		global_run();
		init_app_page();
		$id = intval($_REQUEST['id']);
		$is_why = 0; //1 自己，2其它登录用户看，3未登录用户看
		if($id)
		{
			if($id == $GLOBALS['user_info']['id'])
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
			else
			{
				$is_why = 3;
				$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$id);
				$GLOBALS['tmpl']->assign("id",$id);
				if($home_user_info){
					$is_why = 2;
					$my_info =$GLOBALS['user_info'];
					$is_fav = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$my_info['id']." and focused_user_id=".$id)) ;
					$GLOBALS['tmpl']->assign("is_fav",$is_fav);
					$GLOBALS['tmpl']->assign("my_info",$my_info);
				}
			}
		}
		else
		{
			if(empty($GLOBALS['user_info']))
			{
				app_redirect();
				showErr("请先登录",url("index","user#login"));
			}
			else
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
		}
		
		$GLOBALS['tmpl']->assign("is_why",$is_why);
		$GLOBALS['tmpl']->assign("home_user_info",$home_user_info);
		
		$uc_nav = get_uc_nav($id);
		$GLOBALS['tmpl']->assign("uc_nav",$uc_nav);
		
		$region_list = load_auto_cache("cache_region_conf");
			
		$province_str = $region_list[$home_user_info['province_id']]['name'];
		$city_str = $region_list[$home_user_info['city_id']]['name'];
		
		if($province_str.$city_str=='')
			$user_location = '';
		else
			$user_location = $province_str.$city_str;
		
		$home_user_info['medal_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_medal where is_delete = 0 and user_id = ".$home_user_info['id']." order by create_time desc");
		
		//瀑布流分页
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$GLOBALS['tmpl']->assign("page",$page);
		
		//我关注的用户
		$focus_user_list = load_auto_cache("cache_focus_user",array("uid"=>$home_user_info['id']));
		$t_ids[] = $home_user_info['id'];
		foreach($focus_user_list as $k=>$v){
			$t_ids[] = $v['id'];
		}

		$condition =" user_id in (".implode(",", $t_ids).") and is_effect = 1 and is_delete = 0  and fav_id <> 0 and relay_id = 0  and type in ('share','dealcomment','youhuicomment','eventcomment','slocationcomment','eventsubmit','sharedeal','shareyouhui','shareevent') ";
		
		$sql = "select count(*) from ".DB_PREFIX."topic where ".$condition;

		$count = $GLOBALS['db']->getOne($sql);
		
		if($count==0){
			$GLOBALS['tmpl']->assign("is_best_user",1);
		}
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
		
		
		
		$GLOBALS['tmpl']->assign("user_location",$user_location);
		$GLOBALS['tmpl']->assign("home_user_info",$home_user_info);
		$GLOBALS['tmpl']->display("uc_home_fav.html");
	}
	
	
	/**
	 * 小店
	 */
	public function mall()
	{
		global_run();
		init_app_page();
		
		$id = $GLOBALS['ref_uid'];
		$is_why = 0; //1 自己，2其它登录用户看，3未登录用户看
		if($id)
		{
			if($id == $GLOBALS['user_info']['id'])
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
			else
			{
				$is_why = 3;
				$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$id);
				$GLOBALS['tmpl']->assign("id",$id);
				if($home_user_info){
					$is_why = 2;
					$my_info =$GLOBALS['user_info'];
					$is_fav = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$my_info['id']." and focused_user_id=".$id)) ;
					$GLOBALS['tmpl']->assign("is_fav",$is_fav);
					$GLOBALS['tmpl']->assign("my_info",$my_info);
				}
			}
		}
		else
		{
			app_redirect(url("index"));
		}
		
		$GLOBALS['tmpl']->assign("is_why",$is_why);
		$GLOBALS['tmpl']->assign("home_user_info",$home_user_info);
		
		$uc_nav = get_uc_nav($id);
		$GLOBALS['tmpl']->assign("uc_nav",$uc_nav);
		
		$region_list = load_auto_cache("cache_region_conf");
			
		$province_str = $region_list[$home_user_info['province_id']]['name'];
		$city_str = $region_list[$home_user_info['city_id']]['name'];
		
		if($province_str.$city_str=='')
			$user_location = '';
		else
			$user_location = $province_str.$city_str;
		
		$home_user_info['medal_list'] = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user_medal where is_delete = 0 and user_id = ".$home_user_info['id']." order by create_time desc");
		
		//列表
		$type = intval($_REQUEST['type']); //0团购类 1商城类
		$GLOBALS['tmpl']->assign("type",$type);
		$GLOBALS['tmpl']->assign("tuan_url",url("index","uc_home#mall",array("r"=>base64_encode($id))));
		$GLOBALS['tmpl']->assign("mall_url",url("index","uc_home#mall",array("r"=>base64_encode($id),"type"=>1)));
		
		require_once APP_ROOT_PATH."system/model/deal.php";		
		
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;		
		$page_size = app_conf("DEAL_PAGE_SIZE");
		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		if($type==0)
		{
			$join = " left join ".DB_PREFIX."user_deal as ud on d.id = ud.deal_id and ud.user_id = ".$id." ";
			$ext_condition = " d.buy_type <> 1 and d.is_shop = 0 and (d.is_fx = 1 or (d.is_fx = 2 and ud.is_effect = 1)) ";
			$sort_field = " ud.add_time desc ";
			$deal_result  = get_deal_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array("city_id"=>$GLOBALS['city']['id']),$join,$ext_condition,$sort_field);
			
			$deal_list = $deal_result['list'];		
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d ".$join." where ".$deal_result['condition'],false);

			
			$page = new Page($total,$page_size);   //初始化分页对象
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		else
		{
			$join = " left join ".DB_PREFIX."user_deal as ud on d.id = ud.deal_id and ud.user_id = ".$id." ";
			$ext_condition = " d.buy_type <> 1 and d.is_shop = 1 and (d.is_fx = 1 or (d.is_fx = 2 and ud.is_effect = 1)) ";
			$sort_field = " ud.add_time desc ";
			$deal_result  = get_goods_list($limit,array(DEAL_ONLINE,DEAL_NOTICE),array(),$join,$ext_condition,$sort_field);
				
			$deal_list = $deal_result['list'];
			$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal as d ".$join." where ".$deal_result['condition'],false);
			
				
			$page = new Page($total,$page_size);   //初始化分页对象
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);
		}
		
		foreach($deal_list as $k=>$deal)
		{
			if($deal['uname']!='')
				$durl = url("index","deal#".$deal['uname'],array("r"=>base64_encode($id)));
			else
				$durl = url("index","deal#".$deal['id'],array("r"=>base64_encode($id)));
			$deal_list[$k]['url'] = $durl;
		}
		$GLOBALS['tmpl']->assign('deal_list',$deal_list);
		
		
		$GLOBALS['tmpl']->assign("user_location",$user_location);
		$GLOBALS['tmpl']->display("uc_home_mall.html");
	}
	
	/**
	 * 关注列表
	 */
	public function uc_follow_list(){
		global_run();
		init_app_page();
		
		$id = intval($_REQUEST['id']);
		$is_why = 0; //1 自己，2其它登录用户看，3未登录用户看
		if($id)
		{
			if($id == $GLOBALS['user_info']['id'])
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
			else
			{
				$is_why = 3;
				$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$id);
				$GLOBALS['tmpl']->assign("id",$id);
				if($GLOBALS['user_info']){
					$is_why = 2;
					$my_info =$GLOBALS['user_info'];
					$is_fav = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$my_info['id']." and focused_user_id=".$id)) ;
					$GLOBALS['tmpl']->assign("is_fav",$is_fav);
					$GLOBALS['tmpl']->assign("my_info",$my_info);
				}
			}
		}
		else
		{
			if(empty($GLOBALS['user_info']))
			{
				app_redirect(url("index","user#login"));
			}
			else
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
		}
		
		
		
		$GLOBALS['tmpl']->assign("is_why",$is_why);
		
		$GLOBALS['tmpl']->assign("home_user_info",$home_user_info);
		
		$uc_nav = get_uc_nav($id);
		$GLOBALS['tmpl']->assign("uc_nav",$uc_nav);
		
		if(empty($home_user_info)){
			showErr("用户不存在",0,url("index","uc_home#index"));
		}
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id = ".$home_user_info['id']);

		require_once APP_ROOT_PATH."app/Lib/page.php";
		
		//分页
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);

		if($is_why == 1){
			$sql = "select focused_user_id as id,focused_user_name as user_name,'1' as focused from ".DB_PREFIX."user_focus  where focus_user_id = ".$home_user_info['id']." order by focused_user_id desc limit ".$limit;	
		}else{
			$sql = "select focused_user_id as id,focused_user_name as user_name from ".DB_PREFIX."user_focus  where focus_user_id = ".$home_user_info['id']." order by focused_user_id desc limit ".$limit;
		}
		$uc_u_list = $GLOBALS['db']->getAll($sql);
		
		if($is_why == 2){
			$uc_u_list = $this->format_focus_data($uc_u_list, $my_info['id']);
		}

// echo "select u.id,u.user_name from ".DB_PREFIX."user_focus uf left join ".DB_PREFIX."user u on u.id = uf.focused_user_id where uf.focus_user_id = ".$home_user_info['id']." order by u.id desc limit ".$limit;exit;
		//没有关注推荐用户
		if(count($uc_u_list)==0){
			$GLOBALS['tmpl']->assign("is_best_user",1);
		}
		$GLOBALS['tmpl']->assign("uc_u_list",$uc_u_list);
		$GLOBALS['tmpl']->display("uc_follow_fans_list.html");
	}
	/**
	 * 粉丝列表
	 */
	public function uc_fans_list(){
		global_run();
		init_app_page();
		
		$id = intval($_REQUEST['id']);
		
		$is_why = 0; //1 自己，2其它登录用户看，3未登录用户看
		if($id)
		{
			if($id == $GLOBALS['user_info']['id'])
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
			else
			{
				$is_why = 3;
				$home_user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id=".$id);
				$GLOBALS['tmpl']->assign("id",$id);
				if($GLOBALS['user_info']){
					$is_why = 2;
					$my_info =$GLOBALS['user_info'];
					$is_fav = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focus_user_id=".$my_info['id']." and focused_user_id=".$id)) ;
					$GLOBALS['tmpl']->assign("is_fav",$is_fav);
					$GLOBALS['tmpl']->assign("my_info",$my_info);
				}
			}
		}
		else
		{
			if(empty($GLOBALS['user_info']))
			{
				app_redirect(url("index","user#login"));
			}
			else
			{
				$is_why = 1;
				$home_user_info = $GLOBALS['user_info'];
			}
		}
		
		$GLOBALS['tmpl']->assign("is_why",$is_why);
		$GLOBALS['tmpl']->assign("home_user_info",$home_user_info);
		
		$uc_nav = get_uc_nav($id);
		$GLOBALS['tmpl']->assign("uc_nav",$uc_nav);
		
		if(empty($home_user_info)){
			
			showErr("用户不存在",0,url("index","uc_home#index"));
		}
		
		$total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_focus where focused_user_id = ".$home_user_info['id']);
		
		require_once APP_ROOT_PATH."app/Lib/page.php";
		
		//分页
		$page_size = 10;
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		
		$page = new Page($total,$page_size);   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		if($is_why == 1){
			
			$sql = "select focus_user_id as id,focus_user_name as user_name,to_focus as focused from ".DB_PREFIX."user_focus  where focused_user_id = ".$home_user_info['id']." order by focus_user_id desc limit ".$limit;
		}else{
			$sql = "select focus_user_id as id,focus_user_name as user_name from ".DB_PREFIX."user_focus  where focused_user_id = ".$home_user_info['id']." order by focus_user_id desc limit ".$limit;
		}
		
		$uc_u_list = $GLOBALS['db']->getAll($sql);

		if($is_why == 2){
			$uc_u_list = $this->format_focus_data($uc_u_list, $my_info['id']);
		}
		//没有关注推荐用户
		if(count($uc_u_list)==0){
			$GLOBALS['tmpl']->assign("is_best_user",1);
		}
		$GLOBALS['tmpl']->assign("uc_u_list",$uc_u_list);
		$GLOBALS['tmpl']->display("uc_follow_fans_list.html");
	}
	
	/**
	 * 格式化，关注或粉丝数据是否被看的用户关注了
	 * @param array $data
	 * @param int $uid
	 */
	function format_focus_data($data,$uid){
		
		foreach($data as $k=>$v){
			$ids[] = $v['id'];
		}
		$result_data = $GLOBALS['db']->getAll("select focused_user_id from ".DB_PREFIX."user_focus where focused_user_id in( ".implode(",", $ids)." ) and focus_user_id = ".$uid);
		//echo "select focused_user_id from ".DB_PREFIX."user_focus where focused_user_id in( ".implode(",", $ids)." ) and focus_user_id = ".$uid;exit;
		foreach($result_data as $k=>$v){
			$focus_ids[] = $v['focused_user_id'];
		}
		
		foreach($data as $k=>$v){
			if(in_array($v['id'], $focus_ids)){
				$data[$k]['focused'] = 1;
			}
		}
		return $data;
		
	}
	
}
function get_uc_nav($id=0){
	$uc_nav = array();
	if($id != $GLOBALS['user_info']['id'] && $id>0){		
		$uc_nav['index'] = array('url'=>url("index","uc_home#index",array('id'=>$id)),'name'=>'TA的主页');
		$uc_nav['myfav'] = array('url'=>url("index","uc_home#myfav",array('id'=>$id)),'name'=>'喜欢');
		if(defined("FX_LEVEL"))
		$uc_nav['mall'] = array('url'=>url("index","uc_home#mall",array('r'=>base64_encode($id))),'name'=>'TA的小店');
	}else{		
		$uc_nav['index'] = array('url'=>url("index","uc_home#index"),'name'=>'我的主页');
		$uc_nav['myfav'] = array('url'=>url("index","uc_home#myfav"),'name'=>'喜欢');
		if(defined("FX_LEVEL"))
		$uc_nav['mall'] = array('url'=>url("index","uc_home#mall",array('r'=>base64_encode($GLOBALS['user_info']['id']))),'name'=>'我的小店');
	}
	return $uc_nav;
}