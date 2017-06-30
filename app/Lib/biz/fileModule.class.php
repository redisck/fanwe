<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class fileModule extends BizBaseModule
{
	
	/**
	 * 通用上传，上传到attachments目录，按日期划分
	 * 错误返回 error!=0,message错误消息, error=1000表示未登录
	 * 正确时返回 error=0, url: ./public格式的文件相对路径  path:物理路径 name:文件名
	 */
	public function upload()
	{	
		global_run();
		if(empty($GLOBALS['account_info']))
		{
			$data['error'] = 1000;  //未登录
			$data['msg'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($data);
		}
		
		//上传处理
		//创建comment目录
		if (!is_dir(APP_ROOT_PATH."public/attachment")) { 
	             @mkdir(APP_ROOT_PATH."public/attachment");
	             @chmod(APP_ROOT_PATH."public/attachment", 0777);
	        }
		
	    $dir = to_date(NOW_TIME,"Ym");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
	             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	        }
	        
	    $dir = $dir."/".to_date(NOW_TIME,"d");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
	             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	        }
	     
	    $dir = $dir."/".to_date(NOW_TIME,"H");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
	             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	        }
	        
	    if(app_conf("IS_WATER_MARK")==1)
	    $img_result = save_image_upload($_FILES,"file","attachment/".$dir,$whs=array(),1,1);
	    else
		$img_result = save_image_upload($_FILES,"file","attachment/".$dir,$whs=array(),0,1);	
		if(intval($img_result['error'])!=0)	
		{
			ajax_return($img_result);
		}
		else 
		{
			if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!='NONE')
        	{
        		syn_to_remote_image_server($img_result['file']['url']);
        	}
			
		}	
		
		$data_result['error'] = 0;
		$data_result['url'] = $img_result['file']['url'];
		$data_result['web_40'] = get_spec_image($data_result['url'],40,40,1);
		$data_result['path'] = $img_result['file']['path'];
		$data_result['name'] = $img_result['file']['name'];
		ajax_return($data_result);
		
	}
	
	
	/**
	 * 免登录调用上传
	 */
	public function nologin_upload()
	{
	    //上传处理
	    //创建comment目录
	    if (!is_dir(APP_ROOT_PATH."public/attachment")) {
	        @mkdir(APP_ROOT_PATH."public/attachment");
	        @chmod(APP_ROOT_PATH."public/attachment", 0777);
	    }
	
	    $dir = to_date(NOW_TIME,"Ym");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	        @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	    }
	
	    $dir = $dir."/".to_date(NOW_TIME,"d");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	        @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	    }
	
	    $dir = $dir."/".to_date(NOW_TIME,"H");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	        @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	    }
	
	    if(app_conf("IS_WATER_MARK")==1)
	        $img_result = save_image_upload($_FILES,"file","attachment/".$dir,$whs=array(),1,1);
	    else
	        $img_result = save_image_upload($_FILES,"file","attachment/".$dir,$whs=array(),0,1);
	    if(intval($img_result['error'])!=0)
	    {
	        ajax_return($img_result);
	    }
	    else
	    {
	    	
	        if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	        {
	            syn_to_remote_image_server($img_result['file']['url']);
	        }
	
	    }
	
	    $data_result['error'] = 0;
	    $data_result['url'] = $img_result['file']['url'];
	    $data_result['path'] = $img_result['file']['path'];
	    $data_result['name'] = $img_result['file']['name'];
	    ajax_return($data_result);
	
	}
	
	/**
	 * 用户注册免登录调用上传
	 */
	public function user_register_upload()
	{
	    //上传处理
	    //创建comment目录
	    if (!is_dir(APP_ROOT_PATH."public/attachment")) {
	        @mkdir(APP_ROOT_PATH."public/attachment");
	        @chmod(APP_ROOT_PATH."public/attachment", 0777);
	    }
	
	    $dir = to_date(NOW_TIME,"Ym");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	        @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	    }
	     
	    $dir = $dir."/".to_date(NOW_TIME,"d");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	        @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	    }
	
	    $dir = $dir."/".to_date(NOW_TIME,"H");
	    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) {
	        @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
	        @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
	    }
	     
	    if(app_conf("IS_WATER_MARK")==1)
	        $img_result = save_image_upload($_FILES,"file","attachment/".$dir,$whs=array(),1,1);
	    else
	        $img_result = save_image_upload($_FILES,"file","attachment/".$dir,$whs=array(),0,1);
	    if(intval($img_result['error'])!=0)
	    {
	        ajax_return($img_result);
	    }
	    else
	    {
	        if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
	        {
	            syn_to_remote_image_server($img_result['file']['url']);
	        }
	        	
	    }
	
	    $data_result['error'] = 0;
	    $data_result['url'] = $img_result['file']['url'];
	    $data_result['small_url'] =  get_spec_image($data_result['url'],88,75,1);
	    $data_result['big_url'] = get_spec_image($data_result['url'],600,400);
	    $data_result['path'] = $img_result['file']['path'];
	    $data_result['name'] = $img_result['file']['name'];
	    ajax_return($data_result);
	
	}
	
}
?>