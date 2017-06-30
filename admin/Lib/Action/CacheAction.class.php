<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class CacheAction extends CommonAction{

	public function index()
	{
		$this->assign("oss_type",$GLOBALS['distribution_cfg']['OSS_TYPE']);
		$this->display();
	}
	
	public function clear_data()
	{
		set_time_limit(0);
		es_session::close();

		$GLOBALS['db']->query("update ".DB_PREFIX."topic set is_cached = 0");
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_location set dp_group_point = '',tuan_youhui_cache = ''");
		$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set data = ''");
		$GLOBALS['cache']->clear();
		
		clear_dir_file(APP_ROOT_PATH."public/runtime/admin/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/app/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/data/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/wap/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/mapi/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/statics/");			
		
		
		header("Content-Type:text/html; charset=utf-8");
       	exit("<div style='line-height:50px; text-align:center; color:#f30;'>".L('CLEAR_SUCCESS')."</div><div style='text-align:center;'><input type='button' onclick='$.weeboxs.close();' class='button' value='关闭' /></div>");
	}

	
	public function syn_data()
	{
		set_time_limit(0);
		es_session::close();
		//同步，supplier_location表, deal表, youhui表, event表 , supplier 表
		//总数
		$page = intval($_REQUEST['p'])==0?1:intval($_REQUEST['p']);
		if($page==1)
		syn_dealing();
		$page_size = 5;		
		$location_total = M("SupplierLocation")->count();
		$deal_total = M("Deal")->count();
		$youhui_total = M("Youhui")->count();
		$event_total = M("Event")->count();
		$supplier_total = M("Supplier")->count();
		$count = max(array($location_total,$deal_total,$youhui_total,$event_total,$supplier_total));
		
		$limit = ($page-1)*$page_size.",".$page_size;
		$location_list = M("SupplierLocation")->limit($limit)->findAll();
		foreach($location_list as $v)
		{
			recount_supplier_data_count($v['id'],"tuan");
			recount_supplier_data_count($v['id'],"youhui");
			recount_supplier_data_count($v['id'],"daijin");
			recount_supplier_data_count($v['id'],"event");
			recount_supplier_data_count($v['id'],"shop");
			syn_supplier_location_match($v['id']);
		}
		$supplier_list = M("Supplier")->limit($limit)->findAll();
		foreach($supplier_list as $v)
		{
			syn_supplier_match($v['id']);
		}
		$deal_list = M("Deal")->limit($limit)->findAll();
		foreach($deal_list as $v)
		{
			syn_deal_match($v['id']);
		}
		$youhui_list = M("Youhui")->limit($limit)->findAll();
		foreach($youhui_list as $v)
		{
			syn_youhui_match($v['id']);
		}	
		$event_list = M("Event")->limit($limit)->findAll();
		foreach($youhui_list as $v)
		{
			syn_event_match($v['id']);
		}		
		
		if($page*$page_size>=$count)
		{
			$this->assign("jumpUrl",U("Cache/index"));
			$ajax = intval($_REQUEST['ajax']);
			 
       		$data['status'] = 1;
       		$data['info'] = "<div style='line-height:50px; text-align:center; color:#f30;'>同步成功</div><div style='text-align:center;'><input type='button' onclick='$.weeboxs.close();' class='button' value='关闭' /></div>";
			header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($data));
		}
		else 
		{
			$total_page = ceil($count/$page_size);       		
       		$data['status'] = 0;
       		$data['info'] = "共".$total_page."页，当前第".$page."页,等待更新下一页记录";
       		$data['url'] = U("Cache/syn_data",array("p"=>$page+1));
       		header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($data));
		}		
	}
	
	public function clear_image()
	{
		set_time_limit(0);
		es_session::close();
		$path  = APP_ROOT_PATH."public/attachment/";
		$this->clear_image_file($path);
		$path  = APP_ROOT_PATH."public/images/";
		$this->clear_image_file($path);		
		$path  = APP_ROOT_PATH."public/comment/";
		$this->clear_image_file($path);
		
		$qrcode_path = APP_ROOT_PATH."public/images/qrcode/";
		$this->clear_qrcode($qrcode_path);
	
		$GLOBALS['db']->query("update ".DB_PREFIX."topic set is_cached = 0");
		$GLOBALS['db']->query("update ".DB_PREFIX."supplier_location set dp_group_point = '',tuan_youhui_cache = ''");
		$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set data = ''");
		$GLOBALS['cache']->clear();
		
		clear_dir_file(APP_ROOT_PATH."public/runtime/admin/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/app/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/data/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/wap/");
		clear_dir_file(APP_ROOT_PATH."public/runtime/statics/");
		
		header("Content-Type:text/html; charset=utf-8");
       	exit("<div style='line-height:50px; text-align:center; color:#f30;'>".L('CLEAR_SUCCESS')."</div><div style='text-align:center;'><input type='button' onclick='$.weeboxs.close();' class='button' value='关闭' /></div>");
	}
	
	private function clear_qrcode($path)
	{
	
	   if ( $dir = opendir( $path ) )
	   {
	            while ( $file = readdir( $dir ) )
	            {
	                $check = is_dir( $path. $file );
	                if ( !$check )
	                {
	                    @unlink ( $path . $file);                       
	                }
	                else 
	                {
	                 	if($file!='.'&&$file!='..')
	                 	{
	                 		$this->clear_qrcode($path.$file."/");              			       		
	                 	} 
	                 }           
	            }
	            closedir( $dir );
	            return true;
	   }
	}
	
	private function clear_image_file($path)
	{
	   if ( $dir = opendir( $path ) )
	   {
	            while ( $file = readdir( $dir ) )
	            {
	                $check = is_dir( $path. $file );
	                if ( !$check )
	                {
	                	if(preg_match("/_(\d+)x(\d+)/i",$file,$matches))
	                    @unlink ( $path . $file);                       
	                }
	                else 
	                {
	                 	if($file!='.'&&$file!='..')
	                 	{
	                 		$this->clear_image_file($path.$file."/");              			       		
	                 	} 
	                 }           
	            }
	            closedir( $dir );
	            return true;
	   }
	}
}
?>