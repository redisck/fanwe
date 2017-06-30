<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_dealoModule extends MainBaseModule
{

	public function index(){
	    global_run();
	    init_app_page();
	    $param['page'] = intval($_REQUEST['page']);
	    $data = request_api("biz_dealo","index",$param);
	    

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
	    $data['page_type'] = "o";
	    $GLOBALS['tmpl']->assign("data",$data);
	    $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","dealo"));
	    $GLOBALS['tmpl']->display("biz_dealo.html");
    }
    
    
    public function locations(){
        global_run();
        init_app_page();
        
        $param['data_id'] = intval($_REQUEST['data_id']);
        
        $data = request_api("biz_dealo","locations",$param);
         
        
        if ($data['biz_user_status']==0){ //用户未登录
            app_redirect(wap_url("biz","user#login"));
        }
         
      
        //设定页面类型为验证部分
        $data['page_type'] = "o";
        $GLOBALS['tmpl']->assign("data",$data);
        $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","dealo"));
        $GLOBALS['tmpl']->display("biz_dealo_locations.html");
    }
    
    
    public function dealos(){
        global_run();
        init_app_page();

        $param['deal_id'] = intval($_REQUEST['deal_id']);
        $param['location_id'] = intval($_REQUEST['location_id']);
        $param['page'] = intval($_REQUEST['page']);
        $data = request_api("biz_dealo","deals",$param);

     
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
        $data['page_type'] = "o";
        $GLOBALS['tmpl']->assign("data",$data);
        $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","dealo"));
        $GLOBALS['tmpl']->display("biz_dealo_deals.html");
    }
   
    
    
}
?>

