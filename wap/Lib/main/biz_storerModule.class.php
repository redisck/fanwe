<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class biz_storerModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		init_app_page();		
		
		$data = request_api("biz_storer","index");

		if ($data['biz_user_status']==0){ //用户未登录
		    app_redirect(wap_url("biz","user#login"));
		}

		if(isset($data['page']) && is_array($data['page'])){
		    //感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
		    $page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
		    $p  =  $page->show();
		    $GLOBALS['tmpl']->assign('pages',$p);
		}
		//设定页面类型为验证部分
		$data['page_type'] = "r";
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","storer"));
		$GLOBALS['tmpl']->display("biz_storer_list.html");
	}
	
	
	
	public function storer_dp_list(){
	    global_run();
	    init_app_page();
	    $param['data_id'] = intval($_REQUEST['data_id']); //分类ID
	    $param['page'] = intval($_REQUEST['page']);
	    $param['is_bad'] = intval($_REQUEST['is_bad']);
	    $data = request_api("biz_storer","storer_dp_list",$param);

	    if ($data['biz_user_status']==0){ //用户未登录
	        app_redirect(wap_url("biz","user#login"));
	    }
	    if(isset($data['page']) && is_array($data['page'])){
	        //感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
	        $page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
	        $p  =  $page->show();
	        $GLOBALS['tmpl']->assign('pages',$p);
	    }
    
	    //设定页面类型为验证部分
	    $data['page_type'] = "r";
	    $GLOBALS['tmpl']->assign("data",$data);
	    
	    $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","storer"));
	    $GLOBALS['tmpl']->display("biz_storer_dp_list.html");
	}
	
	public function storer_reply_dp(){
	    global_run();
	    init_app_page();
	    $param['data_id'] = intval($_REQUEST['data_id']); //分类ID
	    $data = request_api("biz_storer","storer_reply_dp",$param);
	    
	    if ($data['biz_user_status']==0){ //用户未登录
	        app_redirect(wap_url("biz","user#login"));
	    }
	    $GLOBALS['tmpl']->assign("data",$data);
	    $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","storer"));
	    $GLOBALS['tmpl']->display("biz_storer_reply_dp.html");
	}
	
	public function do_reply_dp(){
	    global_run();

	    $param=array();
	    $param['data_id'] = intval($_REQUEST['data_id']);
	    $param['reply_content'] = strim($_REQUEST['reply_content']);
	    
	    $data = request_api("biz_storer","do_reply_dp",$param);

	    if ($data['biz_user_status']==0){ //用户未登录
	        app_redirect(wap_url("biz","user#login"));
	    }else
	        $data['jump'] = wap_url("biz","storer#storer_dp_list",array('data_id'=>$data['obj_id']));
	    ajax_return($data);
	}
	
}
?>