<?php
//指定文章父分类下子分类树状格式化后的结果
class cache_shop_acate_tree_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("pid"=>$param['pid'],"type_id"=>$param['type_id'],"act_name"=>$param['act_name']);
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$cate_list = $GLOBALS['cache']->get($key);
		if($cate_list===false)
		{
			$param['pid'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."article_cate where id = ".intval($param['pid'])));
			$param['type_id'] = intval($GLOBALS['db']->getOne("select type_id from ".DB_PREFIX."article_cate where type_id = ".intval($param['type_id'])." limit 1"));
			if($param['act_name']!="news"&&$param['act_name']!="acate")
				$param['act_name'] = "acate";
			
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$cate_list = $GLOBALS['cache']->get($key);
			if($cate_list!==false)return $cate_list;
			
			$pid = intval($param['pid']);
			$type_id = intval($param['type_id']);
			$act_name=strim($param['act_name']);

			if($pid==0){
				$condition=" 1=1 ";
			}else{
				$condition=" id=".$pid;
			}
			$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."article_cate where is_effect = 1 and is_delete = 0 and pid=0 and type_id = ".$type_id." and ".$condition);
			foreach($cate_list as $k=>$v)
			{
				$cate_list[$k]['child_cate']=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."article_cate where is_effect = 1 and is_delete = 0 and pid=".$v['id']." and type_id = ".$type_id);
				foreach($cate_list[$k]['child_cate'] as $kk=>$vv){
					$cate_list[$k]['child_cate'][$kk]['url'] = url("index",$act_name."#".$vv['id']);
				}				
				$cate_list[$k]['url'] = url("index",$act_name."#".$v['id']);
			}	

			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$cate_list);
		}
		return $cate_list;
	}
	public function rm($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['cache']->rm($key);
	}
	public function clear_all()
	{
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['cache']->clear();
	}
}
?>