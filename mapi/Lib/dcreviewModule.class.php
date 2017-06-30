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
	
	/**
	 * 商家点评页面
	 * 点评规则：只有完成订单的客人才可以点评，点评入口要有订单ID，做为判断，一个订单，只能点评一次，输出中is_allow_dp会给出是否允许点评状态，no_dp_info为 不允许点评时的提示信息
	 *
	 * 测试链接：http://localhost/o2onew/mapi/index.php?ctl=dcreview&r_type=2&id=34
	 * 输入：
	 * id:int 订单ID
	 *
	 * 输出：
	 * is_has_location:int是否存在些商家， 0为不存在，1为存在
	 * is_allow_dp 是否允许点评
	 * no_dp_info：不允许点评时的提示信息
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page_keyword:string 页面关键词
	 * page_description:string 页面描述        
	 * dp_data:array:array,结构如下：
	 *   Array
        (
            [0] => Array
                (
                    [id] => 0
                    [name] => 整体评分
                )

            [1] => Array
                (
                    [id] => 4
                    [name] => 服务评分
                )

            [2] => Array
                (
                    [id] => 5
                    [name] => 质量评分
                )

        )  
      *dclocation：array商家信息，结构如下：
             Array
        (
            [id] => 41
            [name] => 果果外卖
            [preview] => http://localhost/o2onew/public/attachment/201504/17/10/55306e5b0f72a_1200x900.jpg
            [avg_point] => 3.4375
        )
	 **/
	public function index()
	{	
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		
	//	$location_id = intval($GLOBALS['request']['lid']);
		
		
		$order_id = intval($GLOBALS['request']['id']);
		$order_info=$GLOBALS['db']->getRow("select id,is_dp,confirm_status,is_rs,is_cancel,refund_status,location_id from ".DB_PREFIX."dc_order where id =".$order_id);
		
		$location_id=$order_info['location_id'];
		$dclocation=$GLOBALS['db']->getRow("select id,name,preview,avg_point from ".DB_PREFIX."supplier_location where id =".$location_id);

		$root=array();
		$root['order_info']=$order_info;
		if($order_info['is_cancel'] > 0 || $order_info['refund_status'] > 0 ){
			$root['is_allow_dp']=0;
			$root['no_dp_info']='用户取消订单，不允许点评！';
		}elseif($order_info['is_dp']==1){
			$root['is_allow_dp']=0;
			$root['no_dp_info']='已经点评过，不能再点评！';
		}elseif($order_info['confirm_status']==2){
			$root['is_allow_dp']=1;
		}

		
		
		if($dclocation)
		{	
			$dclocation['preview']=get_abs_img_root(get_spec_image($dclocation['preview'],600,450,1));

			//关于分类信息与seo
			$page_title = $dclocation['name'];
			$page_keyword = $dclocation['name'];
			$page_description = $dclocation['name'];
			require_once APP_ROOT_PATH."system/model/review.php";

			
			$item_data = load_auto_cache("store",array("id"=>$location_id));
			$sql = "select g.id,g.name from ".DB_PREFIX."point_group as g left join
					".DB_PREFIX."point_group_link as l on l.point_group_id = g.id left join
					".DB_PREFIX."supplier_location_dp_point_result as r on r.group_id = g.id and r.supplier_location_id = ".$item_data['id']."
					where  l.category_id = ".$item_data['deal_cate_id']." group by g.id";
			$dp_data = $GLOBALS['db']->getAll($sql);
			$total_dp=array('id'=>0,'name'=>"整体");
			array_unshift($dp_data,$total_dp);
			foreach($dp_data as $k=>$v){
				$dp_data[$k]['name']=$v['name']."评分";
			}
			
			$root['is_has_location']=1;
			$root['page_title']=$page_title;
			$root['page_keyword']=$page_keyword;
			$root['page_description']=$page_description;
			$root['dp_data']=$dp_data;
			$root['dclocation']=$dclocation;
			
			output($root);
		}
		else
		{	
			$root['is_has_location']=0;
			output($root);
		}
		
		
	}
	
	
	/**
	 *  添加点评接口
	 *  测试链接：http://localhost/o2onew/mapi/index.php?ctl=dcreview&act=save&location_id=41&r_type=2&order_id=50
	 *  
	 *  输入：
	 *  
	 *  location_id:商家ID
	 *  order_id:订单ID
	 *  content:点评内容
	 *  dp_points:array 星级评分,结构如下： 
	 *  array("评分项目ID"=>"评分","评分项目ID"=>"评分","评分项目ID"=>"评分");如： array("0"=>4,"4"=>2,"5"=>3);整体评分放第一个
	 *  评分项目ID:整体评分传0，其他传各自的ID
	 * 
	 *  [file] => Array
    	(
    	    [name] => array(
    	                   [0]=>0b46f21fbe096b63376be90e0f338744ebf8ac7a.jpg
    	               )  [array] 图片名称数组
    	    [type] => array(
    	                   [0]=>image/jpeg
    	               )   [array] 图片类型数组
    	    [tmp_name] => array(
    	                   [0]=>C:\Windows\Temp\phpBAA4.tmp
    	               )   [array] 图片临时文件
    	    [error] => array(
    	                   [0]=>0
    	               )   [array] 图片报错
    	    [size] => array(
    	                   [0]=>37393
    	               )   [array] 图片大小
    	)
	 *  
	 *  
	 *  输出：
	 *  
	 *  user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户)
	 * status：为添加点评的状态，status=0,添加点评失败;status=1,添加点评成功
	 * info:返回的提示信息
	 */
	
	public function save()
	{
		global_run();
	    $user_login_status = check_login();
	    if($user_login_status!=LOGIN_STATUS_LOGINED){
	        $root['user_login_status'] = $user_login_status;
	        output($root,0,'请先登录');
	    }
	    else
	    {
	        $root['user_login_status'] = $user_login_status;
	
	
	
		$location_id = intval($GLOBALS['request']['location_id']);
		$order_id = intval($GLOBALS['request']['order_id']);
		$param = array(
				"location_id"	=> $location_id,
				"order_id"	=> $order_id,
		);
	
		$checker = $this->dc_check_dp_status($GLOBALS['user_info']['id'],$param);
		if(!$checker['status'])
		{
			
			output($root,0,$checker['info']);
	
		}

		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
	
	
		$content = strim(valid_str($GLOBALS['request']['content']));  //点评内容
	
		$dp_points_req = $GLOBALS['request']['dp_points']; //总评分
		

		$dp_points=array();
		foreach ($dp_points_req as $k=>$v)
		{
			$sv = intval($v);
			if($sv)
				$dp_points[$k] = intval($sv);
		}
		
		$dp_point=$dp_points[0];
		if($dp_point<=0)
		{	
			output($root,0,"请为总评打分");
		}
	
		/*
		$dp_image = array(); //点评图片
		$dp_image_req=$GLOBALS['request']['dp_image'];
		foreach($dp_image_req as $k=>$v)
		{
			if(strim($v)!="")
				$dp_image[] = strim($v);
		}
	*/
		$tag_group = array(); //标签分组
	
		unset($dp_points[0]);
		$point_group = array(); //评分分组
		
		foreach($dp_points as $k=>$v)
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
				
				output($root,0,$info);
			}
		}
		
		
		
		$dp_image = array();
		
		if ($GLOBALS['request']['from'] == 'wap'){
			$file_arr=$GLOBALS['request']['file'];
			
		}else{
			
			$file_arr=$_FILES;
		}
		
		
		if(count($file_arr['file']['name'])>9)
		{
			output($root,0,'上传图片不能超过9张');
		}
		else
		{
			 
			if(count($file_arr['file']['name'])>0){
				 
				//同步图片
				foreach($file_arr['file']['name'] as $k=>$v)
				{
					$_files['file']['name'] = $v;
					$_files['file']['type'] = $file_arr['file']['type'][$k];
					$_files['file']['tmp_name'] = $file_arr['file']['tmp_name'][$k];
					$_files['file']['error'] = $file_arr['file']['error'][$k];
					$_files['file']['size'] = $file_arr['file']['size'][$k];
					$res = upload_topic($_files);
		
					if($res['error']==0)
					{
						$dp_image[] = $res['url'];
					}
				}
			}
		
			
			$result = $this-> dc_save_review($GLOBALS['user_info']['id'], $param, $content, $dp_point, $dp_image, $tag_group,$point_group);
			
			$status=$result['status']?$result['status']:0;
			$info=$result['info'];
			
			output($root,$status,$info);
		}
	
	
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
					return array("status"=>0,"info"=>$checker['info']);
				}
				else
					$supplier_location_id = $checker['supplier_location_id'];
			}
			else
			{
				return array("status"=>0,"info"=>"你要点评的商家不存在");
			}
		}
	
	
		if($order_id>0)
		{
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
	
			if(!$order_info)
			{
					
				return array("status"=>0,"info"=>"你的订单不存在",'jump'=>url("index","dc_dcorder"));
			}
			else
			{	if($order_info['is_rs']==0){
				$url=url("index","dc_dcorder");
			}else{
	
				$url=url("index","dc_rsorder");
			}
				
			if($order_info['confirm_status']!=2)
			{
				return array("status"=>0,"info"=>"此订单未完结",'jump'=>$url);
			}
			if($order_info['is_dp']>0)
			{
	
				return array("status"=>0,"info"=>"此订单已点评",'jump'=>$url);
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