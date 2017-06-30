<?php
//每个点评的统计缓存
class cache_dp_info_auto_cache extends auto_cache{
	public function load($param)
	{
		$param = array("deal_id"=>$param['deal_id'],"youhui_id"=>$param['youhui_id'],"event_id"=>$param['event_id'],"location_id"=>$param['location_id']); //重新定义缓存的有效参数，过滤非法参数
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$dp_data = $GLOBALS['cache']->get($key);
		if($dp_data === false)
		{
			//验证参数有效性
			$param['deal_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal where id = ".intval($param['deal_id'])));
			$param['youhui_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."youhui where id = ".intval($param['youhui_id'])));
			$param['event_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."event where id = ".intval($param['event_id'])));
			$param['location_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."supplier_location where id = ".intval($param['location_id'])));
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$result = $GLOBALS['cache']->get($key);
			if($result!==false)return $result;
			
			$deal_id = intval($param['deal_id']);
			$youhui_id = intval($param['youhui_id']);
			$event_id = intval($param['event_id']);
			$location_id = intval($param['location_id']);
			
			
			if($deal_id>0)
			{
				$item_data = load_auto_cache("deal",array("id"=>$deal_id));
				
				if($item_data['is_shop']==1)
				{					
					$shop_cate = $GLOBALS['db']->getRow("select id,pid from ".DB_PREFIX."shop_cate where id = ".$item_data['shop_cate_id']);
					if($shop_cate['pid']!=0)
					{
						$item_data['shop_cate_id'] = $shop_cate['pid'];
					}					
					$sql = "select g.id,g.name,AVG(r.point) as avg_point from ".DB_PREFIX."point_group as g left join
					".DB_PREFIX."point_group_slink as l on l.point_group_id = g.id left join
					".DB_PREFIX."deal_dp_point_result as r on r.group_id = g.id and r.deal_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['shop_cate_id']." group by g.id";
					$dp_data['point_group'] = $GLOBALS['db']->getAll($sql);
					$sql = "select g.id,g.name,group_concat(r.tags) as tags from ".DB_PREFIX."tag_group as g left join
					".DB_PREFIX."tag_group_slink as l on l.tag_group_id = g.id left join
					".DB_PREFIX."deal_dp_tag_result as r on r.group_id = g.id and r.deal_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['shop_cate_id']." group by g.id";
					$dp_data['tag_group'] = $GLOBALS['db']->getAll($sql);
				}
				else
				{
					$sql = "select g.id,g.name,AVG(r.point) as avg_point from ".DB_PREFIX."point_group as g left join
					".DB_PREFIX."point_group_link as l on l.point_group_id = g.id left join
					".DB_PREFIX."deal_dp_point_result as r on r.group_id = g.id and r.deal_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['cate_id']." group by g.id";
					$dp_data['point_group'] = $GLOBALS['db']->getAll($sql);
					$sql = "select g.id,g.name,group_concat(r.tags) as tags from ".DB_PREFIX."tag_group as g left join
					".DB_PREFIX."tag_group_link as l on l.tag_group_id = g.id left join
					".DB_PREFIX."deal_dp_tag_result as r on r.group_id = g.id and r.deal_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['cate_id']." group by g.id";
					$dp_data['tag_group'] = $GLOBALS['db']->getAll($sql);
				}
				
				
			}
			elseif($youhui_id>0)
			{
				$item_data = load_auto_cache("youhui",array("id"=>$youhui_id));
				$sql = "select g.id,g.name,AVG(r.point) as avg_point from ".DB_PREFIX."point_group as g left join
					".DB_PREFIX."point_group_link as l on l.point_group_id = g.id left join
					".DB_PREFIX."youhui_dp_point_result as r on r.group_id = g.id and r.youhui_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['deal_cate_id']." group by g.id";
				$dp_data['point_group'] = $GLOBALS['db']->getAll($sql);
				$sql = "select g.id,g.name,group_concat(r.tags) as tags from ".DB_PREFIX."tag_group as g left join
					".DB_PREFIX."tag_group_link as l on l.tag_group_id = g.id left join
					".DB_PREFIX."youhui_dp_tag_result as r on r.group_id = g.id and r.youhui_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['deal_cate_id']." group by g.id";
				$dp_data['tag_group'] = $GLOBALS['db']->getAll($sql);
			}
			elseif($event_id>0)
			{
				$item_data = load_auto_cache("event",array("id"=>$event_id));
				
				$sql = "select g.id,g.name,AVG(r.point) as avg_point from ".DB_PREFIX."point_group as g left join
					".DB_PREFIX."point_group_elink as l on l.point_group_id = g.id left join
					".DB_PREFIX."event_dp_point_result as r on r.group_id = g.id and r.event_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['cate_id']." group by g.id";
				$dp_data['point_group'] = $GLOBALS['db']->getAll($sql);
				$sql = "select g.id,g.name,group_concat(r.tags) as tags from ".DB_PREFIX."tag_group as g left join
					".DB_PREFIX."tag_group_elink as l on l.tag_group_id = g.id left join
					".DB_PREFIX."event_dp_tag_result as r on r.group_id = g.id and r.event_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['cate_id']." group by g.id";
				$dp_data['tag_group'] = $GLOBALS['db']->getAll($sql);
			}
			elseif($location_id>0)
			{
				$item_data = load_auto_cache("store",array("id"=>$location_id));
				$sql = "select g.id,g.name,AVG(r.point) as avg_point from ".DB_PREFIX."point_group as g left join
					".DB_PREFIX."point_group_link as l on l.point_group_id = g.id left join
					".DB_PREFIX."supplier_location_dp_point_result as r on r.group_id = g.id and r.supplier_location_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['deal_cate_id']." group by g.id";
				$dp_data['point_group'] = $GLOBALS['db']->getAll($sql);
				$sql = "select g.id,g.name,group_concat(r.tags) as tags from ".DB_PREFIX."tag_group as g left join
					".DB_PREFIX."tag_group_link as l on l.tag_group_id = g.id left join
					".DB_PREFIX."supplier_location_dp_tag_result as r on r.group_id = g.id and r.supplier_location_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['deal_cate_id']." group by g.id";
				$dp_data['tag_group'] = $GLOBALS['db']->getAll($sql);
			}
			
			
			if($item_data)
			{
				$dp_data['dp_count'] = $item_data['dp_count'];
				$dp_data['avg_point'] = round($item_data['avg_point'],1);
					
				$dp_data['dp_count_1'] = $item_data['dp_count_1'];
				$dp_data['avg_point_1_percent'] = $item_data['dp_count_1']/$item_data['dp_count']*100;
					
				$dp_data['dp_count_2'] = $item_data['dp_count_2'];
				$dp_data['avg_point_2_percent'] = $item_data['dp_count_2']/$item_data['dp_count']*100;
					
				$dp_data['dp_count_3'] = $item_data['dp_count_3'];
				$dp_data['avg_point_3_percent'] = $item_data['dp_count_3']/$item_data['dp_count']*100;
					
				$dp_data['dp_count_4'] = $item_data['dp_count_4'];
				$dp_data['avg_point_4_percent'] = $item_data['dp_count_4']/$item_data['dp_count']*100;
					
				$dp_data['dp_count_5'] = $item_data['dp_count_5'];
				$dp_data['avg_point_5_percent'] = $item_data['dp_count_5']/$item_data['dp_count']*100;
				foreach($dp_data['point_group'] as $k=>$v)
				{
					$dp_data['point_group'][$k]['avg_point'] = round(floatval($v['avg_point']),1);
					$dp_data['point_group'][$k]['avg_point_percent'] = floatval($v['avg_point'])/5*100;
				}
					
				foreach($dp_data['tag_group'] as $k=>$v)
				{
					$dp_data['tag_group'][$k]['tags'] = preg_split("/[ |,]/",$v['tags']);
					foreach($dp_data['tag_group'][$k]['tags'] as $kk=>$vv)
					{
						if(trim($vv)=="")
						{
							unset($dp_data['tag_group'][$k]['tags'][$kk]);
						}
						else
						{
							$dp_data['tag_group'][$k]['tags_count'][$vv] = intval($dp_data['tag_group'][$k]['tags_count'][$vv])+1;
						}
					}
					$tmp_tags = $dp_data['tag_group'][$k]['tags'];
					$dp_data['tag_group'][$k]['tags'] = array();
					foreach($tmp_tags as $kk=>$vv)
					{
						$dp_data['tag_group'][$k]['tags'][$vv] = $vv;
					}
					if(count($dp_data['tag_group'][$k]['tags'])==0)
					{
						unset($dp_data['tag_group'][$k]);
					}
				}
					
			}
			else
			{
				unset($dp_data['point_group']);
				unset($dp_data['tag_group']);
			}
			
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$dp_data,30);
		}
		return $dp_data;
	}
	public function rm($param)
	{
		$param = array("deal_id"=>$param['deal_id'],"youhui_id"=>$param['youhui_id'],"event_id"=>$param['event_id'],"location_id"=>$param['location_id']); //重新定义缓存的有效参数，过滤非法参数
		
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