<?php
//加载供应商信息的模块
class supplier_auto_cache extends auto_cache{
	public function load($param)
	{
// 		$key = $this->build_key(__CLASS__,$param);
// 		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
// 		$deal = $GLOBALS['cache']->get($key);				
// 		if($deal === false)
// 		{		
// 			$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($param['id']));
// 			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
// 			$GLOBALS['cache']->set($key,$deal);
// 		}
		$param = array("id"=>$param['id']);
		$supplier_id = intval($param['id']);
		static $suppliers;
		if(!$suppliers[$supplier_id])
		{
			if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
			{
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$supplier = $GLOBALS['cache']->get($key);
			}
			else
			{
				$supplier = false;
			}
			
			if($supplier===false)
			{
				$param['id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."supplier where id = ".$supplier_id));
				
				$key = $this->build_key(__CLASS__,$param);
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
				$supplier = $GLOBALS['cache']->get($key);
				if($supplier!==false)return $supplier;
				
				$supplier_id = $param['id'];
				
				$supplier = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id = '".$supplier_id."'");
				if($supplier)
				{
					$supplier['url'] = url("index","supplier#".$supplier['id']);
					if($GLOBALS['distribution_cfg']['CACHE_TYPE']!="File")
					{
						$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
						$GLOBALS['cache']->set($key,$supplier);
					}
				}
			}
			
			$suppliers[$supplier_id] = $supplier;
		}
		return $suppliers[$supplier_id];	
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