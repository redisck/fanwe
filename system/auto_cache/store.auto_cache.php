<?php
//加载门店信息的模块
class store_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("id"=>$param['id']);
		$store_key = intval($param['id']);
		static $store;
		if(!$store[$store_key])
		{
			if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
			{
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$store_info = $GLOBALS['cache']->get($key);
			}
			else
			{
				$store_info = false;
			}
			
			if($store_info===false)
			{
				
				$param['id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."supplier_location where id = ".$store_key));
				
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$store_info = $GLOBALS['cache']->get($key);
				if($store_info!==false)return $store_info;
				
				$store_key = $param['id'];
				
				$store_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = '".$store_key."'");
				if($store_info)
				{
					$id = $store_info['id'];
					$store_info = recount_supplier_data_count($id,"tuan",$store_info);
					$store_info = recount_supplier_data_count($id,"youhui",$store_info);
					$store_info = recount_supplier_data_count($id,"event",$store_info);
					$store_info = recount_supplier_data_count($id,"shop",$store_info);
					$durl = url("index","store#".$store_info['id']);
					$store_info['url'] = $durl;
					
					if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
					{
						$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
						$GLOBALS['cache']->set($key,$store_info,300);
					}
				}
			}
			
			$store[$store_key] = $store_info;
		}
		return $store[$store_key];	
	}
	public function rm($param)
	{
		//$key = $this->build_key(__CLASS__,$param);
		//$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		//$GLOBALS['cache']->rm($key);
	}
	public function clear_all()
	{
		//$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		//$GLOBALS['cache']->clear();
	}
}
?>