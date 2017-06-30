<?php
//每个分类的点评配置
class cache_dp_cfg_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("cate_id"=>$param['cate_id'],"scate_id"=>$param['scate_id'],"ecate_id"=>$param['ecate_id']); //重新定义缓存的有效参数，过滤非法参数
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$dp_data = $GLOBALS['cache']->get($key);
		if($dp_data === false)
		{
			
			//验证参数有效性
			$param['cate_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_cate where id = ".intval($param['cate_id'])));
			$param['scate_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."shop_cate where id = ".intval($param['scate_id'])));
			$param['ecate_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."event_cate where id = ".intval($param['ecate_id'])));
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$dp_data = $GLOBALS['cache']->get($key);
			if($dp_data!==false)return $dp_data;
			
			$cate_id = intval($param['cate_id']);
			$scate_id = intval($param['scate_id']);
			$ecate_id = intval($param['ecate_id']);
	
			if($cate_id>0)
			{
				$dp_data['point_group'] = $GLOBALS['db']->getAll("select g.* from ".DB_PREFIX."point_group as g left join ".DB_PREFIX."point_group_link as l on g.id = l.point_group_id where l.category_id = ".$cate_id);
				$dp_data['tag_group'] = $GLOBALS['db']->getAll("select g.* from ".DB_PREFIX."tag_group as g left join ".DB_PREFIX."tag_group_link as l on g.id = l.tag_group_id where l.category_id = ".$cate_id);
				foreach($dp_data['tag_group'] as $k=>$v)
				{
					$dp_data['tag_group'][$k]['preset_list'] = preg_split("/[, ]/", $v['preset']);
					foreach($dp_data['tag_group'][$k]['preset_list'] as $kk=>$vv)
					{
						if(trim($vv)=="")
							unset($dp_data['tag_group'][$k]['preset_list'][$kk]);
					}
				}
			}
			elseif($scate_id>0)
			{
				$shop_cate = $GLOBALS['db']->getRow("select id,pid from ".DB_PREFIX."shop_cate where id = ".$scate_id);
				if($shop_cate['pid']!=0)
				{
					$scate_id = $shop_cate['pid'];
				}	
					
				$dp_data['point_group'] = $GLOBALS['db']->getAll("select g.* from ".DB_PREFIX."point_group as g left join ".DB_PREFIX."point_group_slink as l on g.id = l.point_group_id where l.category_id = ".$scate_id);
				$dp_data['tag_group'] = $GLOBALS['db']->getAll("select g.* from ".DB_PREFIX."tag_group as g left join ".DB_PREFIX."tag_group_slink as l on g.id = l.tag_group_id where l.category_id = ".$scate_id);
				foreach($dp_data['tag_group'] as $k=>$v)
				{
					$dp_data['tag_group'][$k]['preset_list'] = preg_split("/[, ]/", $v['preset']);
					foreach($dp_data['tag_group'][$k]['preset_list'] as $kk=>$vv)
					{
						if(trim($vv)=="")
							unset($dp_data['tag_group'][$k]['preset_list'][$kk]);
					}
				}
			}
			elseif($ecate_id>0)
			{
				$dp_data['point_group'] = $GLOBALS['db']->getAll("select g.* from ".DB_PREFIX."point_group as g left join ".DB_PREFIX."point_group_elink as l on g.id = l.point_group_id where l.category_id = ".$ecate_id);
				$dp_data['tag_group'] = $GLOBALS['db']->getAll("select g.* from ".DB_PREFIX."tag_group as g left join ".DB_PREFIX."tag_group_elink as l on g.id = l.tag_group_id where l.category_id = ".$ecate_id);
				foreach($dp_data['tag_group'] as $k=>$v)
				{
					$dp_data['tag_group'][$k]['preset_list'] = preg_split("/[, ]/", $v['preset']);
					foreach($dp_data['tag_group'][$k]['preset_list'] as $kk=>$vv)
					{
						if(trim($vv)=="")
							unset($dp_data['tag_group'][$k]['preset_list'][$kk]);
					}
				}
			}
			
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$dp_data,30);
		}
		return $dp_data;
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