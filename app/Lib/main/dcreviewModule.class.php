<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dcreviewModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();
		$GLOBALS['tmpl']->assign("no_nav",true); //无分类下拉
		if(empty($GLOBALS['user_info']))
		{
			app_redirect(url("index","user#login"));
		}

		require_once APP_ROOT_PATH."system/model/review.php";
		
		$order_id = intval($_REQUEST['id']);  //订单ID
	
		
		$location_id = intval($_REQUEST['location_id']);
		
		if($location_id>0)
		{
			require_once APP_ROOT_PATH."system/model/supplier.php";
			$location_info = get_location($location_id);
			if($location_info)
			{
				//验证是否可以点评
				
				$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
				
				if($order_id>0)
				{
					if($order_info['is_rs']==0){
						$url=url("index","dc_dcorder");
					}else{
						
						$url=url("index","dc_rsorder");
					}
					if($order_info['confirm_status']!=2)
					{
						showErr("此订单未完结",0,$url);
					}
				
					if($order_info['is_dp']>0)
					{
						showErr("此订单已点评",0,$url);
					}	
					
					$sql = "select * from ".DB_PREFIX."supplier_location_dp where supplier_location_id = ".$location_id." and order_id = ".$order_id;
					$rs = $GLOBALS['db']->getRow($sql);
					if($rs)
					{
						showErr("此订单已点评",0,$url);
						
					}
					
				}
				else
				{
				showErr("没有订单无法点评此商家");
				}
				
				$dp_data = load_dp_info(array("location_id"=>$location_id));
				$dp_cfg = load_dp_cfg(array("cate_id"=>$location_info['deal_cate_id']));
		
				$item_info['id'] = $location_info['id'];
				$item_info['key'] = 'location_id';
				$item_info['name'] = $location_info['name'];
				$item_info['url'] = $location_info['url'];
				$item_info['image'] = $location_info['preview'];
				$item_info['order_id'] = $order_id;
				
				$GLOBALS['tmpl']->assign("dp_data",$dp_data);
				$GLOBALS['tmpl']->assign("dp_cfg",$dp_cfg);
				$GLOBALS['tmpl']->assign("item_info",$item_info);
		
		
				//输出导航
				$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>url("index"));
				$site_nav[] = array('name'=>$location_info['name'],'url'=>url("index","review",array("location_id"=>$location_info['id'])));
				$GLOBALS['tmpl']->assign("site_nav",$site_nav);
		
				//输出seo
				$page_title = "";
				$page_keyword = "";
				$page_description = "";
				if($location_info['supplier_info']['name'])
				{
					$page_title.="[".$location_info['supplier_info']['name']."]";
					$page_keyword.=$location_info['supplier_info']['name'].",";
					$page_description.=$location_info['supplier_info']['name'].",";
				}
				$page_title.= $location_info['name'];
				$page_keyword.=$location_info['name'];
				$page_description.=$location_info['name'];
				$GLOBALS['tmpl']->assign("page_title",$page_title);
				$GLOBALS['tmpl']->assign("page_keyword",$page_keyword);
				$GLOBALS['tmpl']->assign("page_description",$page_description);
			}
			else
			{
				showErr("你要点评的商家不存在");
			}
		}

		else
		{
			app_redirect(url("index"));
		}

		$GLOBALS['tmpl']->display("dc/dcreview.html");
	}
	
	
	
	public function save()
	{		
		global_run();
		if(empty($GLOBALS['user_info']))
		{
			$data['status']=-1;
			$data['info'] = "";
			ajax_return($data);
		}

		
		
		$location_id = intval($_REQUEST['location_id']);
		$order_id = intval($_REQUEST['order_id']);
		$param = array(
			"location_id"	=> $location_id,
			"order_id"	=> $order_id,
		);
		
		$checker = $this->dc_check_dp_status($GLOBALS['user_info']['id'],$param);
		if(!$checker['status'])
		{
			showErr($checker['info'],1);
		}
		
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
		
		
		$content = strim(valid_str($_REQUEST['content']));  //点评内容
	
		$dp_point = intval($_REQUEST['dp_point']); //总评分
		if($dp_point<=0)
		{
			$data['status']=0;
			$data['info'] = "请为总评打分";
			ajax_return($data);
		}
		
		$dp_image = array(); //点评图片
		foreach($_REQUEST['dp_image'] as $k=>$v)
		{
			if(strim($v)!="")
				$dp_image[] = strim($v);
		}
		
		$tag_group = array(); //标签分组
		foreach($_REQUEST['dp_tags'] as $k=>$tags_arr)
		{
			foreach($tags_arr as $v)
			{
				if(strim($v)!="")
				{
					$v_array = preg_split("/[ ,]/", $v);
					foreach($v_array as $kk=>$vv)
					{
						if(strim($vv)!="")
						$tag_group[$k][] = strim(valid_str($vv));
					}				
				}	
			}			
		}

		
		$point_group = array(); //评分分组
		foreach($_REQUEST['dp_point_group'] as $k=>$v)
		{
			if(intval($v)>0)
			{
				$point_group[$k] = intval($v);
 			}
 			else
 			{
 				$name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."point_group where id = ".intval($k));
 				$info = "请打分";
 				if($name)
 					$info = "请为".$name."打分";
 				$data['status']=0;
 				$data['info'] = $info;
 				ajax_return($data);
 			}
		}
		
		$result = $this-> dc_save_review($GLOBALS['user_info']['id'], $param, $content, $dp_point, $dp_image, $tag_group,$point_group);
		
		if($result['status'])
		{	

			//分享
			$attach_list = array();
			
		
		
			if($result['location_id'])
			{
				require_once APP_ROOT_PATH."system/model/supplier.php";
				$location_info = get_location($result['location_id']);
				
				$type = "slocationcomment";
				$url_route = array(
						'rel_app_index'	=>	'index',
						'rel_route'	=>	'store#'.$result['location_id'],
						'rel_param' => ''
				);
				
				//同步图片
				if($location_info['preview'])
				{
					require_once APP_ROOT_PATH."system/utils/es_imagecls.php";
					$imagecls = new es_imagecls();
					$info = $imagecls->getImageInfo(APP_ROOT_PATH.$location_info['preview']);
						
					$image_data['width'] = intval($info[0]);
					$image_data['height'] = intval($info[1]);
					$image_data['name'] = $location_info['name'];
					$image_data['filesize'] = filesize(APP_ROOT_PATH.$location_info['preview']);
					$image_data['create_time'] = NOW_TIME;
					$image_data['user_id'] = intval($GLOBALS['user_info']['id']);
					$image_data['user_name'] = strim($GLOBALS['user_info']['user_name']);
					$image_data['path'] = $location_info['preview'];
					$image_data['o_path'] = $location_info['preview'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."topic_image",$image_data);
						
					$img_id = intval($GLOBALS['db']->insert_id());
					$attach_list[] = array("type"=>"image","id"=>intval($img_id));
				}
			}
			
			foreach($_REQUEST['topic_image_id'] as $att_id)
			{
				if(intval($att_id)>0)
					$attach_list[] = array("type"=>"image","id"=>intval($att_id));
			}
				
			
			require_once APP_ROOT_PATH."system/model/topic.php";			
			
			$tid = insert_topic($content,"",$type,$group="", $relay_id = 0, $fav_id = 0,$group_data = "",$attach_list,$url_route);
			if($tid)
			{
				$GLOBALS['db']->query("update ".DB_PREFIX."topic set source_name = '网站' where id = ".intval($tid));
			}
			$result['jump'] = url('index','dcbuy',array('lid'=>$result['location_id']));
			ajax_return($result);
		}
		else
		{
			ajax_return($result);
		}
		
	
		
}
	
	
	
	
	/**
 * 提交保存点评
 * @param unknown_type $user_id 提交点评的会员
 * @param unknown_type $param 参数 详细规则见 dc_check_dp_status函数说明
 * @param unknown_type $content 点评文字内容
 * @param unknown_type $dp_point 总评分
 * @param unknown_type $dp_image 点评的图片数组 array("./public/...","./public/.....");
 * @param unknown_type $tag_group 点评标签(二维数组)，格式如下
 * array(
 * 		"group_id" = array("tag","tag")
 * ); 其中group_id为分组的ID,第二维为每个分组中的tag
 * @param unknown_type $point_group 点评评分分组数据，格式如下
 * array(
 * 		"group_id" 	=>	"point"
 * ); 其中group_id为分组的ID,point为对应分组的评分
 * 
 * 返回 array("status"=>bool, "info"=>"消息","location_id"=>"门店的ID","deal_id"=>"","youhui_id"=>"","event_id"=>"");
 */
function dc_save_review($user_id,$param=array("location_id"=>0,"order_id"=>0),$content,$dp_point,$dp_image=array(),$tag_group=array(),$point_group=array())
{	
	//获取参数	
	$order_id = intval($param['order_id']);  //订单ID
	
	$location_id = intval($param['location_id']);
	//部份初始化的变量	
	$is_buy = 0; //默认的点评为非购物点评
	$avg_price = 0; //均价为0
	
	if($location_id>0)
	{
		require_once APP_ROOT_PATH."system/model/supplier.php";
		$location_info = get_location($location_id);
		if($location_info)
		{
			//验证是否可以点评
			$checker = $this->dc_check_dp_status($GLOBALS['user_info']['id'],array("location_id"=>$location_id,"order_id"=>$order_id));
			
			if(!$checker['status'])
			{
				return array("status"=>false,"info"=>$checker['info']);
			}
			else
				$supplier_location_id = $checker['supplier_location_id'];
		}
		else
		{
			return array("status"=>false,"info"=>"你要点评的商家不存在");
		}
	}
	

	if($order_id>0)
	{
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
		
		if(!$order_info)
		{
			
			return array("status"=>false,"info"=>"你的订单不存在",'jump'=>url("index","dc_dcorder"));
		}
		else
		{	if($order_info['is_rs']==0){
				$url=url("index","dc_dcorder");
			}else{
				
				$url=url("index","dc_rsorder");
			}
			
			if($order_info['confirm_status']!=2)
			{
				return array("status"=>false,"info"=>"此订单未完结",'jump'=>$url);
			}
			if($order_info['is_dp']>0)
			{
				
				return array("status"=>false,"info"=>"此订单已点评",'jump'=>$url);
			}
			else
			{
				$order_id=$checker['order_id'];
				
			}
			
			
		}		
	
		
	}
		
	//点评入库
	$supplier_info = $GLOBALS['db']->getRow("select name,id,new_dp_count_time,supplier_id from ".DB_PREFIX."supplier_location where id = ".intval($supplier_location_id));
	$supplier_id = $supplier_info['supplier_id'];
	$dp_data = array();
	if($content!="")
	{
		$dp_data['is_content'] = 1;
		$dp_data['content'] = $content;
	}
	$dp_data['create_time'] = NOW_TIME;
	$dp_data['point'] = $dp_point;
	$dp_data['user_id'] = $user_id;
	$dp_data['supplier_location_id'] = $supplier_location_id;
	$dp_data['images_cache'] = serialize($dp_image);
	$dp_data['supplier_id'] = $supplier_id;
	$dp_data['status'] = 1;
	$dp_data['order_id'] = $order_id;
	if(count($dp_image)>0)
	{
		$dp_data['is_img'] = 1;
	}
	$dp_data['avg_price'] = floatval($avg_price);

	$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp", $dp_data ,"INSERT");
	$dp_id = $GLOBALS['db']->insert_id();
	
	
	if($dp_id>0)
	{	
		if($checker['order_id'])
		{
			$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set dp_id = ".$dp_id.",is_dp = 1 where id = '".$checker['order_id']."'");
		}
		
		increase_user_active($user_id,"发表了一则点评");
		$GLOBALS['db']->query("update ".DB_PREFIX."user set dp_count = dp_count + 1 where id = ".$user_id);
		//创建点评图库
		if(count($dp_image) > 0)
		{
			foreach($dp_image as $pkey => $photo)
			{
				//点评图片不入商户图片库
// 				$c_data = array();
// 				$c_data['image'] = $photo;
// 				$c_data['sort'] = 10;
// 				$c_data['create_time'] = NOW_TIME;
// 				$c_data['user_id'] = $user_id;
// 				$c_data['supplier_location_id'] = $supplier_location_id;
// 				$c_data['dp_id'] = $dp_id;
// 				$c_data['status'] = 0;
// 				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_images", $c_data,"INSERT");
				
				$c_data = array();
				$c_data['image'] = $photo;
				$c_data['dp_id'] = $dp_id;
				$c_data['create_time'] = NOW_TIME;
				$c_data['location_id'] = $supplier_location_id;
				$c_data['supplier_id'] = $supplier_id;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_images", $c_data,"INSERT");
			}
		}

		//创建点评评分
		foreach($point_group as $group_id => $point)
		{
			$point_data = array();
			$point_data['group_id'] = $group_id;
			$point_data['dp_id'] = $dp_id;
			$point_data['supplier_location_id'] = $supplier_location_id;
			$point_data['point'] = $point;
			$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_point_result", $point_data,"INSERT");
			
		}
		
		//创建点评分组的标签
		foreach($tag_group as $group_id => $tag_row_arr)
		{	
	
			foreach ($tag_row_arr as $tag_row)
			{
				$tag_row_data = array();
				$tag_row_data['tags'] = $tag_row;
				$tag_row_data['dp_id'] = $dp_id;
				$tag_row_data['supplier_location_id'] = $supplier_location_id;
				$tag_row_data['group_id'] = $group_id;
		
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_location_dp_tag_result", $tag_row_data, "INSERT");
				
				insert_match_item($tag_row,"supplier_location_dp",$dp_id,"tags_match"); //更新点评的索引

				$this->review_supplier_location_match($supplier_location_id,$tag_row,$group_id);
			
				
			}
		}		
	
		//更新统计
		syn_supplier_locationcount($supplier_info);
		cache_store_point($supplier_info['id']);
			
		rm_auto_cache("cache_dp_info",$param);
		$return['location_id'] = $supplier_location_id;
		$return['deal_id'] = $dp_data['deal_id'];		
		$return['youhui_id'] = $dp_data['youhui_id'];		
		$return['event_id'] = $dp_data['event_id'];
		
		$return['status'] = 1;
		$return['info'] = "发表成功";
		$return['jump'] = url("index","dc_dcorder");
		return $return;
	}
	else{
		$return['status'] = 0;
		$return['info'] = "数据库异常，提交失败";
		return $return;
	}
}




function dc_check_dp_status($user_id,$param=array("location_id"=>0,"order_id"=>0))
{
	$order_id = intval($param['order_id']);  //订单ID

	$location_id = intval($param['location_id']);
	
	if($location_id>0)
	{
		$sql = "select * from ".DB_PREFIX."dc_order where id = ".$order_id;
		$order_info = $GLOBALS['db']->getRow($sql);
		if($order_info)
		{


			
			if($order_info['is_dp']==1)
			{
				if($order_info['is_rs']==0){
					$url=url("index","dc_dcorder");
				}else{
					$url=url("index","dc_rsorder");
				}
				return array("status"=>false,"info"=>"您已经点评过了,谢谢！",'jump'=>$url);
			}
			else
			{	
					$supplier_location_id = $location_id;
					return array("status"=>true,"info"=>"","supplier_location_id"=>$supplier_location_id,"order_id"=>$order_id);
			}
			
		}
		else
		{
			return array("status"=>false,"info"=>"非法的数据");
		}

	
	}
	else
	{
		return array("status"=>false,"info"=>"非法的数据");
	}
	
	
}


	
	function review_supplier_location_match($location_id,$tags,$group_id){
	$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id);
	if($location)
	{
		$location['tags_match'] = "";
		$location['tags_match_row'] = "";

		//标签
		$tags_arr = explode(" ",$tags);
		foreach($tags_arr as $tgs){
			//同步 supplier_tag 表
			$tag_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_tag where tag_name = '".trim($tgs)."' and supplier_location_id = ".$location_id." and group_id = ".$group_id);
			if($tag_data)
			{
				$tag_data['total_count'] = intval($tag_data['total_count'])+1 ;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_tag", $tag_data,"UPDATE", "tag_name = '".trim($tgs)."' and supplier_location_id = ".$location_id." and group_id = ".$group_id);

			}
			else
			{
				$tag_data['tag_name'] = trim($tgs);
				$tag_data['supplier_location_id'] = $location_id;
				$tag_data['group_id'] = $group_id;
				$tag_data['total_count'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_tag", $tag_data, "INSERT");
			}
			insert_match_item(trim($tgs),"supplier_location",$location_id,"tags_match");
		}
	}
}
	
}
?>