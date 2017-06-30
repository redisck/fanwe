<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


/**
 * 获取分享列表
 */
function get_topic_list($limit, $param=array("cid"=>0,"tag"=>""),$join='', $where='',$orderby = '')
{
	if(empty($param))
		$param=array("cid"=>0,"tag"=>"");	

	$tname = "t";
	$condition = ' '.$tname.'.is_effect = 1 and '.$tname.'.is_delete = 0 ';	
	
	$param_condition = build_topic_filter_condition($param,$tname);
	$condition.=" ".$param_condition;
	
	if($where != '')
	{
		$condition.=" and ".$where;
	}
	
	if($join)
		$sql = "select * from ".DB_PREFIX."topic as ".$tname." ".$join." where  ".$condition;
	else
		$sql = "select * from ".DB_PREFIX."topic as ".$tname." where  ".$condition;
	
	if($orderby=='')
		$sql.=" order by create_time desc limit ".$limit;
	else
		$sql.=" order by ".$orderby." limit ".$limit;
// echo $sql;exit;
	$result_list = $GLOBALS['db']->getAll($sql);
	
	foreach($result_list as $k=>$v)
	{
		$topic = format_topic_item($v);
		if(msubstr(preg_replace("/<[^>]+>/i","",$topic['content']),0,50)!=preg_replace("/<[^>]+>/i","",$topic['content']))
			$topic['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$topic['content']),0,50);
		else
			$topic['short_content'] = preg_replace("/<br[^>]+>/i","",$topic['content']);
	
		if($topic['origin'])
		{
			if(msubstr(preg_replace("/<[^>]+>/i","",$topic['origin']['content']),0,50)!=preg_replace("/<[^>]+>/i","",$topic['origin']['content']))
				$topic['origin']['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$topic['origin']['content']),0,50);
			else
				$topic['origin']['short_content'] = preg_replace("/<br[^>]+>/i","",$topic['origin']['content']);
		}
		$result_list[$k] = $topic;
	}
	return array('list'=>$result_list,'condition'=>$condition);
}

/**
 * 获取分享单个
 * @param unknown_type $id
 */
function get_topic_item($id)
{
	$sql = "select * from ".DB_PREFIX."topic where id = ".$id;
	$topic = $GLOBALS['db']->getRow($sql);
	$topic = format_topic_item($topic);
	
	if(msubstr(preg_replace("/<[^>]+>/i","",$topic['content']),0,50)!=preg_replace("/<[^>]+>/i","",$topic['content']))
		$topic['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$topic['content']),0,50);
	else
		$topic['short_content'] = preg_replace("/<br[^>]+>/i","",$topic['content']);
	
	if($topic['origin'])
	{
		if(msubstr(preg_replace("/<[^>]+>/i","",$topic['origin']['content']),0,50)!=preg_replace("/<[^>]+>/i","",$topic['origin']['content']))
			$topic['origin']['short_content'] = msubstr(preg_replace("/<[^>]+>/i","",$topic['origin']['content']),0,50);
		else
			$topic['origin']['short_content'] = preg_replace("/<br[^>]+>/i","",$topic['origin']['content']);
	}
	return $topic;
}

/**
 * 构建分享查询条件
 * @param unknown_type $param
 * @return string
 */
function build_topic_filter_condition($param,$tname="")
{
	$cid = intval($param['cid']);
	$tag = strim($param['tag']);
	$condition = "";
	
	if($cid>0)
	{
		$cate_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."topic_tag_cate where id = ".$cid);
		if($cate_name)
		{
			$unicode_cate_name = str_to_unicode_string($cate_name);
			
			if($tname)
				$condition.=" and match(".$tname.".cate_match) against('".$unicode_cate_name."'  IN BOOLEAN MODE) ";
			else
				$condition.=" and match(cate_match) against('".$unicode_cate_name."'  IN BOOLEAN MODE) ";
		}
	}
	if($tag!="")
	{
		$unicode_tag = str_to_unicode_string($tag);
		if($tname)
			$condition.=" and match(".$tname.".keyword_match) against('".$unicode_tag."'  IN BOOLEAN MODE) ";
		else
			$condition.=" and match(keyword_match) against('".$unicode_tag."'  IN BOOLEAN MODE) ";
	}	
	return $condition;
}


//解析分享的html，不显示图，显示头像与链接
function decode_topic_without_img($str)
{
	$expression_replace_array = load_auto_cache("expression_replace_array");
	$str = str_replace($expression_replace_array['search'],$expression_replace_array['replace'],$str);

	$name_count = preg_match_all("/@([^\f\n\r\t\v: ]+)/i",$str,$name_matches);
	if($name_count > 0)
	{
		$name_matches[0] = array_unique($name_matches[0]);
		$name_matches[1] = array_unique($name_matches[1]);
		foreach($name_matches[1] as $k=>$user_name)
		{
			$uinfo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_name = '".$user_name."' and is_effect = 1 and is_delete = 0");
			if($uinfo)
			{
				$name_matches[1][$k] = "<a href='".url("index","space",array("id"=>$uinfo['id']))."'  class='user_name' onmouseover='userCard.load(this,".$uinfo['id'].");' >@".$user_name."</a>";
			}
			else
				$name_matches[1][$k] = $name_matches[0][$k];
		}
		$str = str_replace($name_matches[0],$name_matches[1],$str);
	}

	$count = preg_match_all("/\[img\](\d+)\[\/img\]/",$str,$matches);
	if($count>0)
	{

		foreach($matches[1] as $k=>$image_id)
		{
			$matches[1][$k] = "";
		}
		$str = str_replace($matches[0],$matches[1],$str);
	}

	//开始解析url
	$url_count = preg_match_all("/\[url\](\d+)\[\/url\]/",$str,$url_matches);
	if($url_count>0)
	{
		$url_result = $GLOBALS['db']->getAll("select id,url from ".DB_PREFIX."urls where id in (".implode(",",$url_matches[1]).")");
		foreach($url_result as $kk=>$vv)
		{
			$url_data[$vv['id']] = $vv;
		}
		foreach($url_matches[1] as $k=>$url_id)
		{
			if($url_data[$url_id])
				$url_matches[1][$k] = "<a href='".SITE_DOMAIN.url("index","url",array("r"=>base64_encode($url_id)))."' target='_blank' title='".$url_data[$url_id]['url']."' >".SITE_DOMAIN.url("index","url",array("r"=>base64_encode($url_id)))."</a>";
			else
				$url_matches[1][$k] = "";
		}
		$str = str_replace($url_matches[0],$url_matches[1],$str);
	}
	return $str;
}

//解析分享的html内容，没有任何媒体标识,没有图，没有头像，没有链接
function decode_topic_without_allmedia($str)
{
	$expression_replace_array = load_auto_cache("expression_replace_none_array");
	$str = str_replace($expression_replace_array['search'],$expression_replace_array['replace'],$str);
		

	$count = preg_match_all("/\[img\](\d+)\[\/img\]/",$str,$matches);
	if($count>0)
	{

		foreach($matches[1] as $k=>$image_id)
		{
			$matches[1][$k] = "";
		}
		$str = str_replace($matches[0],$matches[1],$str);
	}

	//开始解析url
	$url_count = preg_match_all("/\[url\](\d+)\[\/url\]/",$str,$url_matches);
	if($url_count>0)
	{
		foreach($url_matches[1] as $k=>$url_id)
		{
			$url_matches[1][$k] = "";
		}
		$str = str_replace($url_matches[0],$url_matches[1],$str);
	}
	return $str;
}

//解析分享的html，显示图，头像，链接
function decode_topic($str)
{
	$expression_replace_array = load_auto_cache("expression_replace_array");
	$str = str_replace($expression_replace_array['search'],$expression_replace_array['replace'],$str);

	$name_count = preg_match_all("/@([^\f\n\r\t\v: ]+)/i",$str,$name_matches);
	if($name_count > 0)
	{
		$name_matches[0] = array_unique($name_matches[0]);
		$name_matches[1] = array_unique($name_matches[1]);
		foreach($name_matches[1] as $k=>$user_name)
		{
			$user_name = strim($user_name);
			$uinfo = $GLOBALS['db']->getRow("select id,user_name from ".DB_PREFIX."user where user_name = '".$user_name."' and is_effect = 1 and is_delete = 0");
			if($uinfo)
			{
				$name_matches[1][$k] = "<a href='".url("index","space",array("id"=>$uinfo['id']))."' class='user_name'  onmouseover='userCard.load(this,".$uinfo['id'].");' >@".$user_name."</a>";
			}
			else
				$name_matches[1][$k] = $name_matches[0][$k];
		}
		$str = str_replace($name_matches[0],$name_matches[1],$str);
	}


	//开始处理图片的解析
	$count = preg_match_all("/\[img\](\d+)\[\/img\]/",$str,$matches);
	if($count>0)
	{
		$img_result = $GLOBALS['db']->getAll("select id,path,o_path,width,height from ".DB_PREFIX."topic_image where id in (".implode(",",$matches[1]).")");
		foreach($img_result as $kk=>$vv)
		{
			$img_data[$vv['id']] = $vv;
		}
		foreach($matches[1] as $k=>$image_id)
		{
			if($img_data[$image_id])
				$matches[1][$k] = "<span class='toogle_topic_image_box'><img onclick='zoom(this);' src='".$img_data[$image_id]['path']."' b='".$img_data[$image_id]['o_path']."' s = '".$img_data[$image_id]['path']."' w='".$img_data[$image_id]['width']."' h='".$img_data[$image_id]['height']."' tag='s' /></span>";
			else
				$matches[1][$k] = "";
		}
		$str = str_replace($matches[0],$matches[1],$str);
	}

	//开始解析url
	$url_count = preg_match_all("/\[url\](\d+)\[\/url\]/",$str,$url_matches);
	if($url_count>0)
	{
		$url_result = $GLOBALS['db']->getAll("select id,url from ".DB_PREFIX."urls where id in (".implode(",",$url_matches[1]).")");
		foreach($url_result as $kk=>$vv)
		{
			$url_data[$vv['id']] = $vv;
		}
		foreach($url_matches[1] as $k=>$url_id)
		{
			if($url_data[$url_id])
				$url_matches[1][$k] = "<a href='".SITE_DOMAIN.url("index","url",array("r"=>base64_encode($url_id)))."' target='_blank' title='".$url_data[$url_id]['url']."' >".SITE_DOMAIN.url("index","url",array("r"=>base64_encode($url_id)))."</a>";
			else
				$url_matches[1][$k] = "";
		}
		$str = str_replace($url_matches[0],$url_matches[1],$str);
	}
	return $str;
}





/**
 * 格式化每个分享的内容
 * @param unknown_type $topic
 * @param unknown_type $keywords_array
 * @return mixed
 */
function format_topic_item($topic,$keywords_array=array())
{
	//开始解析同步的数据
	$group = $topic['topic_group'];
	if(file_exists(APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php"))
	{
		require_once APP_ROOT_PATH."system/fetch_topic/".$group."_fetch_topic.php";
		$class_name = $group."_fetch_topic";
		if(class_exists($class_name))
		{
			$fetch_obj = new $class_name;
			$topic = $fetch_obj->decode($topic);
		}
	}


	/**
	生成缓存
	 */

	if($topic['is_cached']==0)
	{
		if($topic['id']!=$topic['origin_id']&&$topic['origin_id']!=0)
		{
			$origin_topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic['origin_id']);
			if($origin_topic)
			{
				$origin_topic['images'] = $GLOBALS['db']->getAll("select path,o_path,width,height,id,name from ".DB_PREFIX."topic_image where topic_id = ".$origin_topic['id']." order by id desc");
				$origin_topic['images_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where topic_id = ".$origin_topic['id']);
			}
			$topic['origin'] = $origin_topic;
			$topic['origin_topic_data'] = serialize($origin_topic);
		}
	
		$topic['images'] = $GLOBALS['db']->getAll("select path,o_path,width,height,id,name from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']." order by id desc");
		$topic['image_list'] = serialize($topic['images']);
		$topic['images_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where topic_id = ".$topic['id']);
		
		
	
		$group_id = intval($topic['group_id']);
		$group_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_group where is_effect = 1 and id = ".$group_id);
		$topic['topic_group_data'] = serialize($group_item);
		$topic['topic_group'] = $group_item;
	
		$topic['is_cached'] = 1;
		$sql = "update ".DB_PREFIX."topic set origin_topic_data = '".$topic['origin_topic_data']."',
				image_list ='".$topic['image_list']."',
				images_count = '".$topic['images_count']."',
				topic_group_data = '".$topic['topic_group_data']."',
				is_cached = 1 where id = ".$topic['id'];
		$GLOBALS['db']->query($sql);
	}
	else
	{
		$topic['images'] = unserialize($topic['image_list']);
		if($topic['id']!=$topic['origin_id'])
			$topic['origin'] = unserialize($topic['origin_topic_data']);
		$topic['topic_group'] = unserialize($topic['topic_group_data']);
	}
	if($topic['id']!=$topic['origin_id']&&$topic['origin_id']!=0)
	{
		$origin_topic = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic where id = ".$topic['origin_id']);
		if($origin_topic['rel_app_index']!=""&&$origin_topic['rel_route']!="")
		{
			$origin_topic['url'] = url($origin_topic['rel_app_index'],$origin_topic['rel_route'],$origin_topic['rel_param']);
			if(app_conf("URL_MODEL")==0)
			{
				$origin_topic['url'].="&r=".base64_encode($topic['user_id']);
			}
			else
			{
				$origin_topic['url'].="?r=".base64_encode($topic['user_id']);
			}
		}
		else
			$origin_topic['url'] = url("index","topic",array("id"=>$origin_topic['id']));
		
		$topic['origin']['fav_count'] = $origin_topic['fav_count'];
		$topic['origin']['reply_count'] = $origin_topic['reply_count'];
		$topic['origin']['relay_count'] = $origin_topic['relay_count'];
		$topic['url'] = $origin_topic['url'];
		$topic['origin']['url'] = $origin_topic['url'];
	}
	else
	{
		if($topic['rel_app_index']!=""&&$topic['rel_route']!="")
		{
			$topic['url'] = url($topic['rel_app_index'],$topic['rel_route'],$topic['rel_param']);
			if(app_conf("URL_MODEL")==0)
			{
				$topic['url'].="&r=".base64_encode($topic['user_id']);
			}
			else
			{
				$topic['url'].="?r=".base64_encode($topic['user_id']);
			}
		}
		else
			$topic['url'] = url("index","topic",array("id"=>$topic['id']));
	}
	//end 生成缓存

	$topic['content'] = nl2br(trim($topic['content']));
	$topic['tags_array'] = explode(" ",$topic['tags']);


	$matches = array();
	foreach($keywords_array as $k=>$item)
	{
		$matches[0][] = $item;
		$matches[1][] = "<span class='result_match'>".$item."</span>";
	}
	$topic['title'] = str_replace($matches[0],$matches[1],$topic['title']);
	$topic['content'] = str_replace($matches[0],$matches[1],$topic['content']);
	
	
	return $topic;  //格式化每条的主题
}




//添加一则分享
/**
 *
 * @param $content  	内容
 * @param $title    	可能存在的标题
 * @param $type			转发的类型标识    见下代码中的范围
 * @param $group		插件的类型    插件名称
 * @param $relay_id		转发的主题ID
 * @param $fav_id		喜欢主题的ID
 * @param $group_data   插件同步过来的额外数据, 如价格，标题, url等
 * @param $attach_list	主题的附件列表
 * attach_list = array(
 * 	array(
 * 		'id'=>xx, 附件的ID
 * 		'type'	=>	xx, (如image, 可扩展，如vedio,music等)  //image为到topic_image表中查询
 * 	),
 * )
 * @param $url_route	关联数据的url配置
 * $url_route = array(
 * 	'rel_app_index'	=>	'',
 *  'rel_route'	=>	'',
 *  'rel_param'	=>	''
 * )
 * @param $tags	分享的标签集合，一维数组
 * 如
 * array("美食","旅游")
 * @param xpoint与ypoint移动端可能用到的分享产生的地理定位
 */
function insert_topic($content, $title='', $type='', $group='', $relay_id = 0 , $fav_id = 0, $group_data = "", $attach_list = array() , $url_route=array(), $tags=array(), $xpoint="", $ypoint="",$forum_title='',$group_id=0,$syn_weibo )
{

	//定义类型的范围
	$type_array = array(
			"share", //分享 
			"dealcomment", //商品点评
			'youhuicomment', //优惠券购物点评
			'eventcomment', //活动点评
			'slocationcomment',  //门店点评
			'eventsubmit',  //活动报名
			'sharedeal',  //分享团购
			'shareyouhui', //分享优惠券
			'shareevent',	//分享活劝
	);

	$group_array = load_auto_cache("group_array_cache");
	if(!in_array($group,$group_array))
		$group = "share";

	if(!in_array($type,$type_array))
		$type = "share";

	//转发与喜欢都是转发喜欢原主题

	if($relay_id>0)
	{
		$from_data = $GLOBALS['db']->getRow("select origin_id,title,content from ".DB_PREFIX."topic where id = ".$relay_id);
		if($from_data)
		{
			$data['relay_id'] = $relay_id;
			$data['origin_id'] = $from_data['origin_id'];
			//更新计数
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set relay_count = relay_count + 1 where id in ('".$relay_id."','".$from_data['origin_id']."')");
		}
	}
	if($fav_id>0)
	{
		$from_data = $GLOBALS['db']->getRow("select origin_id,title,content,user_id from ".DB_PREFIX."topic where id = ".$fav_id);
		if($from_data)
		{
			$data['fav_id'] = $fav_id;
			$data['origin_id'] = $from_data['origin_id'];
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set fav_count = fav_count + 1 where id in ('".$fav_id."','".$from_data['origin_id']."')");

			//更新会员的喜欢数与被喜欢数
			$GLOBALS['db']->query("update ".DB_PREFIX."user set fav_count = fav_count + 1 where id = ".intval($GLOBALS['user_info']['id']));
			$GLOBALS['db']->query("update ".DB_PREFIX."user set faved_count = faved_count + 1 where id = ".$from_data['user_id']);

			if($fav_id!=$from_data['origin_id'])
			{
				//对原贴表示喜欢，并对原贴的作者被喜欢数+1
				$origin_user_id = intval($GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."topic where id = ".$from_data['origin_id']));
				$GLOBALS['db']->query("update ".DB_PREFIX."user set faved_count = faved_count + 1 where id = ".$origin_user_id);
			}

		}
	}

	//	preg_match_all("/@[^\:]+:/i",$content,$matches);
	//	$matches[0] = array_unique($matches[0]);
	//	$utitle = "";
	//	foreach($matches[0] as $k=>$v)
	//	{
	//		$matches[1][$k] = "";
	//		$utitle.=$v;
	//	}
	//	$content = str_replace($matches[0],$matches[1],$content);
	//	$content = $utitle.$content;

	//开始解析url
	$content = htmlspecialchars_decode($content);
	$url_reg = "/http:\/\/[a-zA-Z0-9%\&_\-\.\/=\?]+/i";
	preg_match_all($url_reg,$content,$url_matches);

	foreach($url_matches[0] as $k=>$url)
	{

		$url_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."urls where url = '".$url."'");
		if(!$url_data)
		{
			$url_data = array();
			$url_data['url'] = $url;
			$GLOBALS['db']->autoExecute(DB_PREFIX."urls",$url_data);
			$url_id = $GLOBALS['db']->insert_id();
		}
		else
			$url_id = $url_data['id'];
		$url_matches[1][$k] = "[url]".$url_id."[/url]";

	}
	$content = str_replace($url_matches[0],$url_matches[1],$content);
	$content = htmlspecialchars($content);



	//解析标题
	if($title=='')
	{

		if(preg_match("/#([^#]+)#/",$content,$title_matches))
		{
			$title = $title_matches[1];
			$content = str_replace($title_matches[0],"",$content);
		}

	}
	$data['forum_title'] = $forum_title;
	$data['group_id'] = $group_id;
	$data['title'] = $title;
	$data['content'] = $content;
	$data['create_time'] = get_gmtime();
	$data['user_id'] = intval($GLOBALS['user_info']['id']);
	$data['user_name'] = trim($GLOBALS['user_info']['user_name']);
	$data['is_effect']  = 1;
	$data['is_delete'] = 0;
	$data['type'] = $type;
	//$data['message_id'] = $message_id;
	$data['topic_group'] = $group;
	$data['group_data'] = $group_data;
	$data['tags'] = implode(" ",$tags);
	$data['xpoint'] = $xpoint;
	$data['ypoint'] = $ypoint;

	foreach($url_route as $k=>$v)
	{
		$data[$k]=$v;
	}

	$GLOBALS['db']->autoExecute(DB_PREFIX."topic",$data);

	$id = intval($GLOBALS['db']->insert_id());
	if($id>0)
	{
		//同步添加话题
		if($title!='')
			$topic_title = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."topic_title where name = '".$title."'");

		if($topic_title)
		{
			//已有话题，为分享定位分类
			$cate_ids = $GLOBALS['db']->getAll("select cate_id from ".DB_PREFIX."topic_title_cate_link where title_id = ".$topic_title['id']);
			foreach($cate_ids as $row)
			{
				if($row['cate_id']>0)
				{
					$link_data = array();
					$link_data['topic_id'] = $id;
					$link_data['cate_id'] = $row['cate_id'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."topic_cate_link",$link_data,"INSERT","","SILENT");
				}
			}
			$GLOBALS['db']->query("update ".DB_PREFIX."topic_title set count = count + 1 where name = '".$title."'");
		}
		else
		{
			//新话题
			if($title!='')
			{
				$topic_title['name'] = $title;
				$topic_title['count'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."topic_title",$topic_title,"INSERT","","SILENT");
			}
		}

		$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set topic_count = topic_count + 1 where id = ".$group_id);
		//发贴量加1
		$GLOBALS['db']->query("update ".DB_PREFIX."user set topic_count = topic_count + 1 where id = ".intval($GLOBALS['user_info']['id']));
		if($group=='Fanwe')
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."user set insite_count = insite_count + 1 where id = ".intval($GLOBALS['user_info']['id']));
		}
		//处理标签自动分类
// 		if(count($tags)>0)
// 		{

// 			foreach($tags as $tag)
// 			{
// 				$tag_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."topic_tag where name = '".$tag."'");

// 				if($tag_id>0)
// 				{
// 					$cate_ids = $GLOBALS['db']->getAll("select cate_id from ".DB_PREFIX."topic_tag_cate_link where tag_id = ".$tag_id);
// 					foreach($cate_ids as $row)
// 					{
// 						if($row['cate_id']>0)
// 						{
// 							$link_data = array();
// 							$link_data['topic_id'] = $id;
// 							$link_data['cate_id'] = $row['cate_id'];
// 							$GLOBALS['db']->autoExecute(DB_PREFIX."topic_cate_link",$link_data,"INSERT","","SILENT");
// 						}
// 					}
// 				}
// 			}
// 		}

		foreach($attach_list as $attach)
		{
			if($attach['type']=='image')
			{
				//插入图片
				$GLOBALS['db']->query("update ".DB_PREFIX."topic_image set topic_id = ".$id.",topic_table='topic' where id = ".$attach['id']);
			}
		}

		//删除所有创建超过一小时，且未被使用过的图片
		$del_list = $GLOBALS['db']->getAll("select id,path from ".DB_PREFIX."topic_image where topic_id = 0 and ".get_gmtime()." - create_time > 3600");
		$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_image where topic_id = 0 and ".get_gmtime()." - create_time > 3600");
		foreach($del_list as $k=>$v)
		{
			@unlink(APP_ROOT_PATH.$v['path']);
			@unlink(APP_ROOT_PATH.$v['o_path']);
		}
		if($relay_id==0&&$fav_id==0)
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set origin_id = ".$id." where id = ".$id);
		}
		syn_topic_match($id);
		
		if($syn_weibo){
		    //微博同步
		    //获取所有微博接口
		    $weibo_apis = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login where is_weibo = 1");
		    foreach ($weibo_apis as $k=>$v){
		        $api_class= strtolower($v['class_name']);
		        if($GLOBALS['user_info']['is_syn_'.$api_class] == 1){
		            //同步到微博
		            syn_to_weibo($id,$v['class_name']);
		        }
		    }
		}
		
		
		return $id;
	}
	else
		return false;
}


/**
 * 插入主题评论数据
 * @param array $reply_data
 * array(
 * 	'topic_id'=>'',
 * 	'user_id'=>'',
 * 	'user_name'=>'',
 * 	'reply_id'=>'', //可选
 * 	'create_time'=>'', (int)
 * 	'is_effect'=>'',
 * 	'is_delete'=>'',
 * 	'content'=>''
 * )
 * @return int insert_id
 */
function insert_topic_reply($reply_data){
	if($reply_data['reply_id']>0)
	{
		$reply_reply_data = $GLOBALS['db']->getRow("select id,user_id,user_name from ".DB_PREFIX."topic_reply where id = ".$reply_data['reply_id']);
		$reply_data['reply_user_id'] = $reply_reply_data['user_id'];
		$reply_data['reply_user_name'] = $reply_reply_data['user_name'];
	}
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."topic_reply",$reply_data);
	$id = $GLOBALS['db']->insert_id();
	if($id>0)
	{
		increase_user_active(intval($GLOBALS['user_info']['id']),"回应了一则分享");
	}
	$GLOBALS['db']->query("update ".DB_PREFIX."topic set reply_count = reply_count + 1,last_time = ".get_gmtime().",last_user_id = ".intval($GLOBALS['user_info']['id'])." where id = ".$reply_data['topic_id']);
	return $id;
}
/**
 * 获取主题评论
 * @param int $topic_id
 * @param string $limit
 * @return array
 */
function get_topic_reply_list($topic_id,$limit){
	$sql = "SELECT id,topic_id,user_id,user_name,content,create_time,reply_id,reply_user_id,reply_user_name from ".DB_PREFIX."topic_reply where is_effect=1 and is_delete=0 and topic_id=".$topic_id." order by id desc limit ".$limit;
	$data = $GLOBALS['db']->getAll($sql);
	foreach($data as $k=>$v){
		$data[$k]['format_create_time'] = to_date($v['create_time'],"Y-m-d H:i");
	}
	return $data;
}

function del_topic_reply($id){
	global_run();
	$reply = $GLOBALS['db']->getRow("select user_id,id,topic_id from ".DB_PREFIX."topic_reply where id = ".$id);
	if(!$reply)
	{
		$result['status'] = 0;
		$result['info'] = $GLOBALS['lang']['REPLY_NOT_EXISTS'];
	}
	elseif($reply['user_id']!=$GLOBALS['user_info']['id'])
	{
		$result['status'] = 0;
		$result['info'] = $GLOBALS['lang']['REPLY_NOT_YOURS'];
	}
	else
	{
		$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_reply where id = ".$id);
		$GLOBALS['db']->query("update ".DB_PREFIX."topic set reply_count = reply_count - 1 where id = ".$reply['topic_id']);
		$result['status'] = 1;
		$result['info'] = $GLOBALS['lang']['DELETE_SUCCESS'];
	}
	return $result;
}
function delete_topic($id){
		global_run();
		$topic = $GLOBALS['db']->getRow("select group_id,title,user_id,id,relay_id,fav_id from ".DB_PREFIX."topic where id = ".$id);
		if(!$topic)
		{
			$result['status'] = 0;
			$result['info'] = $GLOBALS['lang']['TOPIC_NOT_EXISTS'];
		}
		else
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."topic_group set topic_count = topic_count - 1 where id = ".$topic['group_id']);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."topic where id = ".$id);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_cate_link where topic_id = ".$id);
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set relay_count = relay_count - 1 where id = ".$topic['relay_id']);
			$GLOBALS['db']->query("update ".DB_PREFIX."topic set fav_count = fav_count - 1 where id = ".$topic['fav_id']);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_image where topic_id = ".$id);			
			
			$GLOBALS['db']->query("update ".DB_PREFIX."topic_title set count = count - 1 where name = '".$topic['title']."'");
			$GLOBALS['db']->query("update ".DB_PREFIX."user set topic_count = topic_count - 1 where id = ".intval($topic['user_id']));
			if(intval(app_conf('USER_DELETE_MONEY'))<0 || intval(app_conf('USER_DELETE_POINT'))<0 || intval(app_conf('USER_DELETE_SCORE'))<0)
			{
				require_once  APP_ROOT_PATH."system/model/user.php";
				modify_account(array("money"=>intval(app_conf('USER_DELETE_MONEY')),"score"=>intval(app_conf('USER_DELETE_SCORE')),"point"=>intval(app_conf('USER_DELETE_POINT'))),$topic['user_id'],"删除了一则商户点评");	
			}
			if($topic['fav_id']>0)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set fav_count = fav_count - 1 where id = ".intval($topic['user_id']));
				$fav_topic = $GLOBALS['db']->getRow("select user_id,id,origin_id from ".DB_PREFIX."topic where id = ".$topic['fav_id']);
				
				$GLOBALS['db']->query("update ".DB_PREFIX."user set faved_count = faved_count - 1 where id = ".intval($fav_topic['user_id']));
				if($fav_topic['id']!=$fav_topic['origin_id'])
				{
					$fav_origin_topic = $GLOBALS['db']->getRow("select user_id,id,origin_id from ".DB_PREFIX."topic where id = ".$fav_topic['origin_id']);
					$GLOBALS['db']->query("update ".DB_PREFIX."user set faved_count = faved_count - 1 where id = ".intval($fav_origin_topic['user_id']));
				}
			}
			if($topic['group']=='Fanwe')
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."user set insite_count = insite_count - 1 where id = ".intval($topic['user_id']));
			}
			$GLOBALS['db']->query("delete from ".DB_PREFIX."topic_reply where topic_id = ".intval($topic['id']));
			$result['status'] = 1;
			$result['info'] = $GLOBALS['lang']['DELETE_SUCCESS'];
		}
		return $result;
}
function get_topic_group($id){
	return $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."topic_group where id=".$id);
}

/**
 * 获取发布的分享格式数据
 * @param unknown $ctl 模块
 * @param unknown $data_id
 * @return string
 */
function get_format_share_data($ctl,$data_id){
   
        switch($ctl)
        {
            case "deal":  //团购分享
                require_once APP_ROOT_PATH."system/model/deal.php";
                $deal = get_deal($data_id);
                if($deal)
                {
                    $result['status'] = 1;
                    $result['content'] = "我购买了：".$deal['sub_name'];
    
    
                    if($deal['brief'])
                        $result['content'].="[".$deal['brief']."]";
                    $result['type'] = "sharedeal";
                    require_once APP_ROOT_PATH."system/libs/words.php";
                    $tags = words::segment($deal['name']);
                    $result['tags'] = implode(" ",$tags);
                    $group_data['url']['app_index'] =  "index";
                    $group_data['url']['route'] =  "deal#".$data_id;
                    $group_data['url']['param'] =  '';
                    $group_data['data'] = $deal;
                    $result['group_data'] =  base64_encode(serialize($group_data));
                    $result['images'] = array();
                    $deal_gallery = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id = ".$deal['id']." order by sort asc");
                    foreach($deal_gallery as $row)
                    {
                        $result['images'][] = syn_image_to_topic($row['img']);
                    }
                }
                else
                {
                    $result['status'] = 0;
                    $result['info'] = "商品不存在";
                }
                break;
            case "youhui":
                require_once APP_ROOT_PATH."system/model/youhui.php";
                $youhui = get_youhui($data_id);
                if($youhui)
                {
                    $result['status'] = 1;
                    $result['content'] = "我领取了:".$youhui['name'];
                    $result['type'] = "shareyouhui";
                    require_once APP_ROOT_PATH."system/libs/words.php";
                    $tags = words::segment($youhui['name']);
                    $result['tags'] = implode(" ",$tags);
                    $group_data['url']['app_index'] =  "index";
                    $group_data['url']['route'] =  "youhui#".$data_id;
                    $group_data['url']['param'] =  "";
                    $group_data['data'] = $youhui;
                    $result['group_data'] =  base64_encode(serialize($group_data));
                    $result['images'][] = syn_image_to_topic($youhui['icon']);
                }
                else
                {
                    $result['status'] = 0;
                    $result['info'] = "优惠券不存在";
                }
                break;
    
            case "event":
                require_once APP_ROOT_PATH."system/model/event.php";
                $event = get_event($data_id);
                if($event)
                {
                    $result['status'] = 1;
                    $result['content'] = "我报名了:".$event['name'];
                    $result['type'] = "shareevent";
                    require_once APP_ROOT_PATH."system/libs/words.php";
                    $tags = words::segment($event['name']);
                    $result['tags'] = implode(" ",$tags);
                    $group_data['url']['app_index'] =  "index";
                    $group_data['url']['route'] =  "event#".$data_id;
                    $group_data['url']['param'] =  "";
                    $group_data['data'] = $event;
                    $result['group_data'] =  base64_encode(serialize($group_data));
                    $result['images'][] = syn_image_to_topic($event['icon']);
                }
                else
                {
                    $result['status'] = 0;
                    $result['info'] = "活动不存在";
                }
                break;
            default:
                $result['status'] = 0;
                $result['info'] = "URL地址错误，该地址的数据无法分享";
                break;
        }
    return $result;
}
function order_share($order_id){
    global_run();
    require_once APP_ROOT_PATH."system/model/deal.php";
    require_once APP_ROOT_PATH."system/libs/words.php";

    $order_item = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id=".$order_id);

    if($order_item){
        foreach($order_item as $k=>$v){
            $data_id = $v['deal_id'];
            $deal = array();
            $deal = get_deal($data_id);
            if($deal)
            {
                $content = "我购买了：".$deal['sub_name'];
                if($deal['brief'])
                    $content.="[".$deal['brief']."]";
                
                $type = "sharedeal";
                $group = "Fanwe";
                $tags = words::segment($deal['name']);
                $group_data = array();
                $group_data['url']['app_index'] =  "index";
                $group_data['url']['route'] =  "deal#".$data_id;
                $group_data['url']['param'] =  '';
                $group_data['data'] = $deal;
                
                $url_route = array();
                $url_route = array(
                    'rel_app_index'=>$group_data['url']['app_index'],
                    'rel_route'=>$group_data['url']['route'],
                    'rel_param'=>''
                );
                
                $group_data =  base64_encode(serialize($group_data));
                $images = array();
                $deal_gallery = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id = ".$deal['id']." order by sort asc");
                foreach($deal_gallery as $row)
                {
                    $images[] = syn_image_to_topic($row['img']);
                }

                $attach_list = array();
                foreach($images as $row)
                {
                    $topic_image =array();
                    $topic_image['type'] = "image";
                    $topic_image['id'] =  intval($row['id']);
                    $attach_list[] = $topic_image;
                }
            }

            $id = insert_topic($content,$title,$type,$group, $relay_id = 0, $fav_id = 0,$group_data,$attach_list,$url_route,$tags,'','','',0,0);
            if($id)
            {
                $GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($id));
                increase_user_active(intval($GLOBALS['user_info']['id']),"发表了一则分享");
            }
        }
    }
    
}
?>