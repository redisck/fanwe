<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
class youhuirModule extends BizBaseModule
{
    function __construct(){
        parent::__construct();
        global_run();
        $this->check_auth();
    }
    /**
     * 优惠券点评
     * @see BizBaseModule::index()
     */
	public function index()
	{		
		init_app_page();
		$s_account_info = $GLOBALS['account_info'];
		$supplier_id = intval($s_account_info['supplier_id']);
        $filter_point = intval($_REQUEST['filter_point']);
        $filter_is_img = intval($_REQUEST['filter_is_img']);
        $GLOBALS['tmpl']->assign("filter_point",$filter_point);
        $GLOBALS['tmpl']->assign("filter_is_img",$filter_is_img);
        $dp_type = "youhui";
		require_once APP_ROOT_PATH.'system/model/review.php';
		//组装查询条件
	    $conditions = biz_get_dp_conditions($supplier_id,$dp_type,$filter_point,$filter_is_img);
        
	    require_once APP_ROOT_PATH."app/Lib/page.php";
	    //分页
	    $page_size = 10;
	    $page = intval($_REQUEST['p']);
	    if($page==0)
	        $page = 1;
	    $limit = (($page-1)*$page_size).",".$page_size;
	     
	    $total = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."supplier_location_dp ".$conditions);
	    $page = new Page($total,$page_size);   //初始化分页对象
	    $p  =  $page->show();
	    $GLOBALS['tmpl']->assign('pages',$p);
	    //获取点评数据
	    $dp_list = biz_get_dp_list($conditions,$dp_type,$limit);

	    $GLOBALS['tmpl']->assign("dp_list",$dp_list);
	    $GLOBALS['tmpl']->assign("ajax_url",url("biz","youhuir"));
	    $GLOBALS['tmpl']->assign("form_url",url("biz","youhuir#index"));
		$GLOBALS['tmpl']->assign("head_title","优惠券点评管理");
		$GLOBALS['tmpl']->display("pages/review/index.html");
	}
	
    public function reply_dp(){
        $s_account_info = $GLOBALS['account_info'];
        $account_id = intval($s_account_info['id']);
        $dp_id = intval($_REQUEST['dp_id']);
        require_once APP_ROOT_PATH.'system/model/review.php';
        ajax_return(biz_reply_dp_html($dp_id));
    }
    
    public function do_reply_dp(){
        $s_account_info = $GLOBALS['account_info'];
        $account_id = intval($s_account_info['id']);
        $dp_id = intval($_REQUEST['dp_id']);
        $reply_content = strim($_REQUEST['reply_content']);
        
        require_once APP_ROOT_PATH.'system/model/review.php';
        ajax_return(biz_do_reply_dp($account_id,$dp_id,$reply_content));
    }
	
}
?>