<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 获取文章列表
 */
function get_article_list($limit, $cate_id=0, $where='',$orderby = '',$cached = true)
{		
		$key = md5("ARTICLE".$limit.$cate_id.$where.$orderby);	
		if($cached)
		{				
			$res = $GLOBALS['cache']->get($key);
		}
		else
		{
			$res = false;
		}
		if($res===false)
		{
				
			$count_sql = "select count(*) from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.is_effect = 1 and a.is_delete = 0 and ac.is_delete = 0 and ac.is_effect = 1 ";
			$sql = "select a.*,ac.type_id,ac.title as ctitle from ".DB_PREFIX."article as a left join ".DB_PREFIX."article_cate as ac on a.cate_id = ac.id where a.is_effect = 1 and a.is_delete = 0 and ac.is_delete = 0 and ac.is_effect = 1 ";
			
			if($cate_id>0)
			{

				$ids = load_auto_cache("deal_shop_acate_belone_ids",array("cate_id"=>$cate_id));
				$sql .= " and a.cate_id in (".implode(",",$ids).")";
				$count_sql .= " and a.cate_id in (".implode(",",$ids).")";
			}
				
			
			if($where != '')
			{
				$sql.=" and ".$where;
				$count_sql.=" and ".$where;
			}
			
			if($orderby=='')
			$sql.=" order by a.sort desc limit ".$limit;
			else
			$sql.=" order by ".$orderby." limit ".$limit;

			$articles = $GLOBALS['db']->getAll($sql);	
			foreach($articles as $k=>$v)
			{
				if($v['type_id']==1)
				{
					$module = "help";
				}
				elseif($v['type_id']==2)
				{
					$module = "notice";
				}
				elseif($v['type_id']==3)
				{
					$module = "sys";
				}
				else 
				{
					$module = 'article';
				}
				
				if($v['rel_url']!=''){
					if(!preg_match ("/http:\/\//i", $v['rel_url'])){
						if(substr($v['rel_url'],0,2)=='u:')	{
							$aurl =parse_url_tag($v['rel_url']);
						}else{
							$aurl =APP_ROOT."/".$v['rel_url'];
						}						
					}else{
						$aurl =$v['rel_url'];
					}					
				}else{
					$aurl = url("index",$module."#".$v['id']);
				}
				
					
				$articles[$k]['url'] = $aurl;
			}	
			$articles_count = $GLOBALS['db']->getOne($count_sql);
			
	 		
			$res = array('list'=>$articles,'count'=>$articles_count);	
			$GLOBALS['cache']->set($key,$res);
		}			
		return $res;
}
?>