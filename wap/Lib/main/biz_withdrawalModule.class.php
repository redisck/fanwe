<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_withdrawalModule extends MainBaseModule
{

	public function index(){
	    global_run();
	    init_app_page();
	    $param['page'] = intval($_REQUEST['page']);
	    $data = request_api("biz_withdrawal","index",$param);
	    

	    if ($data['biz_user_status']==0){ //用户未登录
	        app_redirect(wap_url("biz","user#login"));
	    }
	    
	    if(isset($data['page']) && is_array($data['page'])){
	        //感觉这个分页有问题,查询条件处理;分页数10,需要与sjmpai同步,是否要将分页处理移到sjmapi中?或换成下拉加载的方式,这样就不要用到分页了
	        $page = new Page($data['page']['data_total'],$data['page']['page_size']);   //初始化分页对象
	        $p  =  $page->show();
	        $GLOBALS['tmpl']->assign('pages',$p);
	    }

	    $GLOBALS['tmpl']->assign("data",$data);
	    $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","withdrawal"));
	    $GLOBALS['tmpl']->display("biz_withdrawal.html");
    }
    
    public function submit_form(){
        global_run();
        init_app_page();

        $data = request_api("biz_withdrawal","submit_form");
         
        
        if ($data['biz_user_status']==0){ //用户未登录
            app_redirect(wap_url("biz","user#login"));
        }
        
        $GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
        $GLOBALS['tmpl']->assign("data",$data);
        $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","withdrawal"));
        $GLOBALS['tmpl']->display("biz_withdrawal_form.html");
    }
    
    public function do_submit(){
        global_run();
        init_app_page();
        
        $param = array();
        $param['money'] = floatval($_REQUEST['money']);
        $param['sms_verify'] = strim($_REQUEST['sms_verify']);
        $param['pwd_verify'] = strim($_REQUEST['pwd_verify']);

        $data = request_api("biz_withdrawal","do_submit",$param);
         
        
        if ($data['biz_user_status']==0){ //用户未登录
            $data['jump'] = wap_url("biz","user#login");
        }
        
        if($data['status'] == 1){
            $data['jump']  = wap_url("biz","withdrawal#index");
        }
        
        ajax_return($data);
        
    }
    
    /**
     * 银行卡绑定
     */
    public function bindbank(){
        global_run();
        init_app_page();
        
        $data = request_api("biz_withdrawal","bindbank");
        
        if ($data['biz_user_status']==0){ //用户未登录
            app_redirect(wap_url("biz","user#login"));
        }
        
        $GLOBALS['tmpl']->assign("sms_on",$data['sms_on']);
        $GLOBALS['tmpl']->assign("no_gotop",1);
        $GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
        
        $GLOBALS['tmpl']->assign("data",$data);
        $GLOBALS['tmpl']->assign("ajax_url",wap_url("biz","withdrawal"));
        $GLOBALS['tmpl']->display("biz_withdrawal_bindbank.html");
    }
    
    public function do_bindbank(){
        global_run();
        init_app_page();
        
        $param = array();
        $param['bank_name'] = strim($_REQUEST['bank_name']);
        $param['bank_num'] = str_replace(' ','',strim($_REQUEST['bank_num'])) ;
        $param['bank_user'] = strim($_REQUEST['bank_user']);
        
        $param['sms_verify'] = strim($_REQUEST['sms_verify']);
        $param['pwd_verify'] = strim($_REQUEST['pwd_verify']);
        
        $data = request_api("biz_withdrawal","do_bindbank",$param);
         
        
        if ($data['biz_user_status']==0){ //用户未登录
            $data['jump'] = wap_url("biz","user#login");
        }
        
        if($data['status'] == 1){
            $data['jump']  = wap_url("biz","withdrawal#index");
        }
        
        ajax_return($data);
    }
    
    
}
?>

