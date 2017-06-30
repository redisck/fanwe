<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class dpModule extends MainBaseModule
{
	/**
	 * 点评列表
	 * 输入：
	 * type  [string] 点评的数据类型 (1.团购或者商品  deal/2.优惠券  youhui /3.活动  event/4.门店 store)
	 * data_id [int] 团购/商品/优惠券/活动/门店  的ID
	 * page [int] 分页所在的页数
	 * 
	 * 
	 * 输出：
	 * item [array] 点评数据数组
	 * [item]=>array(
    	   [1] => Array
                    (
                        [id] => 5
                        [create_time] => 2015-04-07
                        [content] => 不错不错
                        [reply_content] => 那是不错的了，可以信任的品牌
                        [point_percent] => 100
                        [point] => 5
                        [user_name] => fanwe
                        [images] => Array
                            (
                                [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36_120x120.jpg   string:点评图片 60x60
                                [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986_120x120.jpg
                                [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061_120x120.jpg
                            )
    
                        [oimages] => Array
                            (
                                [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36.jpg  string:原图
                                [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986.jpg
                                [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061.jpg
                            )
    
                    )
      )      
      
    [message_count] => 1  int:点评数量
    [name] => 普吉岛旅游                 string:点评数据的名称 （商品/团购/优惠券/活动）
    [star_1] => 0       int:一星人数
    [star_2] => 0       int:二星人数
    [star_3] => 1       int:三星人数
    [star_4] => 0       int:四星人数
    [star_5] => 1       int:五星人数
    [star_dp_width_1] => 0      int:一星点评显示进度条 长度
    [star_dp_width_2] => 0      int:二星点评显示进度条 长度
    [star_dp_width_3] => 100    int:三星点评显示进度条 长度
    [star_dp_width_4] => 0      int:四星点评显示进度条 长度
    [star_dp_width_5] => 100    int:五星点评显示进度条 长度
    [buy_dp_sum] => 2           int:购买点评数量
    [buy_dp_avg] => 3           float:点评平均值
    [buy_dp_width] => 60        int:平均值 进度条长度
       
	 */
	public function index()
	{
	    /*参数列表*/
		$type = strim($GLOBALS['request']['type']);
		$id = intval($GLOBALS['request']['data_id']);
		$deal_id = 0;
		$youhui_id = 0;
		$location_id = 0;
		$event_id = 0;	
		
		$root = array();
		$root['user_login_status'] = check_login();
		
		
		/*根据类型获取数据*/
		if($type=="deal")
		{
			$deal_id = $id;
			require_once APP_ROOT_PATH."system/model/deal.php";
			$deal_info = get_deal($deal_id);
			$relate_data_name = $deal_info['name'];
		}
		elseif($type=="store")
		{
			$location_id = $id;
			require_once APP_ROOT_PATH."system/model/supplier.php";
			$location_info = get_location($location_id);
			$relate_data_name = $location_info['name'];
		}
		elseif($type=="youhui")
		{
			$youhui_id = $id;
			require_once APP_ROOT_PATH."system/model/youhui.php";
			$youhui_info = get_youhui($youhui_id);
			$relate_data_name = $youhui_info['name'];
		}
		elseif($type=="event")
		{
			$event_id = $id;
			require_once APP_ROOT_PATH."system/model/event.php";
			$event_info = get_event($event_id);
			$relate_data_name = $event_info['name'];
		}

		/*分页处理*/
		$page = intval($GLOBALS['request']['page']);/*分页*/
		
		//检查用户,用户密码
		$user = $GLOBALS['user_info'];
		$user_id  = intval($user['id']);
		
		
		
		$page=$page==0?1:$page;
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		
		require_once APP_ROOT_PATH."system/model/review.php";
		require_once APP_ROOT_PATH."system/model/user.php";
		
		/*获点评数据*/
		$message_re = get_dp_list($limit,$param=array("deal_id"=>$deal_id,"youhui_id"=>$youhui_id,"event_id"=>$event_id,"location_id"=>$location_id,"tag"=>""),"","");
		$data = array();

		foreach($message_re['list'] as $k=>$v){
		    
		    $temp_arr = array();
		       
		    $temp_arr['id'] = $v['id'];
		    $temp_arr['create_time'] = $v['create_time'] > 0 ?to_date($v['create_time'],'Y-m-d'):'';
		    $temp_arr['content'] = $v['content'];
		    $temp_arr['reply_content']= $v['reply_content']?$v['reply_content']:'';
		    $temp_arr['point_percent'] = $v['point_percent']>0?$v['point_percent']:0;
		    $temp_arr['point'] = $v['point'];
		    
		    $uinfo = load_user($v['user_id']);
		    $temp_arr['user_name'] = $uinfo['user_name'];
		    
		
		    
		    $images = array();
		    $oimages = array();
		    
		    if($v['images']){
		        foreach ($v['images'] as $ik=>$iv){
		            $images[] = get_abs_img_root(get_spec_image($iv,60,60,1));
		            $oimages[] = get_abs_img_root($iv);
		        }
		         
		    }
		    $temp_arr['images'] = $images;
		    $temp_arr['oimages'] = $oimages;
            

			$data[] = $temp_arr;
		}
		$root['item']=$data;
		
		if(count($data)>0)
		{
			$sql = "select count(*) from ".DB_PREFIX."supplier_location_dp where  ".$message_re['condition'];
			$data['count'] = $GLOBALS['db']->getOne($sql);
		}

		$count = $data['count'];
		$root['message_count']=$count;		
		
		//$deal = get_deal($tuan_id);
		
		$dp_info = load_dp_info(array("deal_id"=>$deal_id,"youhui_id"=>$youhui_id,"event_id"=>$event_id,"location_id"=>$location_id));
		
		$root['name'] = $relate_data_name;
		//星级点评数
		$root['star_1'] = $dp_info['dp_count_1'];
		$root['star_2'] = $dp_info['dp_count_2'];
		$root['star_3'] = $dp_info['dp_count_3'];
		$root['star_4'] = $dp_info['dp_count_4'];
		$root['star_5'] = $dp_info['dp_count_5'];
		$root['star_dp_width_1'] = $dp_info['avg_point_1_percent'];
		$root['star_dp_width_2'] = $dp_info['avg_point_2_percent'];
		$root['star_dp_width_3'] = $dp_info['avg_point_3_percent'];
		$root['star_dp_width_4'] = $dp_info['avg_point_4_percent'];
		$root['star_dp_width_5'] = $dp_info['avg_point_5_percent'];
		
		$buy_dp_sum = 0.0;
// 		$buy_dp_group = $GLOBALS['db']->getAll("select point,count(*) as num from ".DB_PREFIX."message where rel_id = ".$tuan_id." and rel_table = 'deal' and pid = 0 and is_buy = 1 group by point");
// 		foreach($buy_dp_group as $dp_k=>$dp_v)
// 		{
// 			$star = intval($dp_v['point']);
// 			if ($star >= 1 && $star <= 5){
// 				$root['star_'.$star] = $dp_v['num'];				
// 				$buy_dp_sum += $star * $dp_v['num'];
// 				$root['star_dp_width_'.$star] = (round($dp_v['num']/ $message_re['count'],1)) * 100;
// 			}
// 		}
		
		//点评平均分
		$root['buy_dp_sum']= $dp_info['dp_count'];
		$root['buy_dp_avg'] = $dp_info['avg_point'];
		$root['buy_dp_width'] = ( $dp_info['avg_point'] / 5) * 100;		
		$page_total = ceil($count/$page_size);
		
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
	
		
		$root['allow_dp'] = 0;//0:不允许点评;1:允许点评
		//判断用户是否购买了这个商品
		if ($user_id > 0){

			$dp_status = check_dp_status($user_id,array("deal_id"=>$deal_id,"youhui_id"=>$youhui_id,"event_id"=>$event_id,"location_id"=>$location_id));
		
			if($dp_status['status'])
				$root['allow_dp'] = 1;
		}
		
		
		$root['type'] = $type;
		$root['data_id']=$id;
		$root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
		$root['page_title'].="点评列表";
		output($root);
	}
	
	
	
	/**
	 * 添加点评
	 * 输入：
	 * type [string] 点评类型
	 * data_id [int] 团购/商品/优惠券/活动/门店  的ID
	 * content [string] 点评内容
	 * point [int] 点评星星数
	 * [file] => Array
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
	 * 输出：
	 * user_login_status:int 用户登录状态(1 已经登录/0 用户未登录/2临时用户) 
	 * Array
        (
            [allow_dp] => 0 [int] 点评权限
            [status] => 0 [int] 状态
            [info] => 您还不能点评 [string] 错误/成功的提示
        )
	 *
	 */
	public function add_dp(){
	    $content = strim($GLOBALS['request']['content']);//点评内容
	    $point = intval($GLOBALS['request']['point']);//点评分数
	    $type = strim($GLOBALS['request']['type']);
	    $id = intval($GLOBALS['request']['data_id']);
	    $deal_id = 0;
	    $youhui_id = 0;
	    $location_id = 0;
	    $event_id = 0;
	    
	    //检查用户,用户密码
	    $user = $GLOBALS['user_info'];
	    $user_id  = intval($user['id']);
	    $root = array();
	    
	    $user_login_status = check_login();
	    if($user_login_status!=LOGIN_STATUS_LOGINED){
	        $root['user_login_status'] = $user_login_status;
	        output($root,0,'请先登录');
	    }
	    else
	    {
	        $root['user_login_status'] = $user_login_status;
	        require_once APP_ROOT_PATH."system/model/review.php";

	        if($type=="deal")
	        {
	            $deal_id = $id;
	            require_once APP_ROOT_PATH."system/model/deal.php";
	            $relate_data = get_deal($deal_id);
	        }
	        elseif($type=="store")
	        {
	            $location_id = $id;
	            require_once APP_ROOT_PATH."system/model/supplier.php";
	            $relate_data = get_location($location_id);
	        }
	        elseif($type=="youhui")
	        {
	            $youhui_id = $id;
	            require_once APP_ROOT_PATH."system/model/youhui.php";
	            $relate_data = get_youhui($youhui_id);
	        }
	        elseif($type=="event")
	        {
	            $event_id = $id;
	            require_once APP_ROOT_PATH."system/model/event.php";
	            $relate_data = get_event($event_id);
	        }
	        
	        $root['allow_dp'] = 0;//0:不允许点评;1:允许点评
	        //判断用户是否购买了这个商品
	        if ($user_id > 0){
	        
	            $dp_status = check_dp_status($user_id,array("deal_id"=>$deal_id,"youhui_id"=>$youhui_id,"event_id"=>$event_id,"location_id"=>$location_id));
	            
	            if($dp_status['status'])
	                $root['allow_dp'] = 1;
	        }
	        if($root['allow_dp']){
	            
	            require_once APP_ROOT_PATH."system/model/review.php";
	            require_once APP_ROOT_PATH."system/model/deal.php";

	            if($type=="deal")
	            {
	                if($relate_data['is_shop']==1)
	                    $cfg = load_dp_cfg(array("scate_id"=>$relate_data['shop_cate_id']));
	                else
	                    $cfg = load_dp_cfg(array("cate_id"=>$relate_data['cate_id']));
	            }
	            elseif($type=="event")
	            {
	                $cfg = load_dp_cfg(array("ecate_id"=>$relate_data['cate_id']));
	            
	            }
	            elseif($type=="store")
	            {
	                $cfg = load_dp_cfg(array("cate_id"=>$relate_data['deal_cate_id']));
	            }
	            elseif($type=="youhui")
	            {
	                $cfg = load_dp_cfg(array("cate_id"=>$relate_data['deal_cate_id']));
	            }
	             
	            $point_group = array();
	            foreach($cfg['point_group'] as $row)
	            {
	                $point_group[$row['id']] = $point;
	            }
	             
	            $dp_img = array();
	            if(count($_FILES['file']['name'])>9)
	            {
	                output($root,0,'上传图片不能超过9张');
	            }
	            else
	            {
	                
	                if(count($_FILES['file']['name'])>0){
	                    
	                    //同步图片
	                    foreach($_FILES['file']['name'] as $k=>$v)
	                    {
	                        $_files['file']['name'] = $v;
	                        $_files['file']['type'] = $_FILES['file']['type'][$k];
	                        $_files['file']['tmp_name'] = $_FILES['file']['tmp_name'][$k];
	                        $_files['file']['error'] = $_FILES['file']['error'][$k];
	                        $_files['file']['size'] = $_FILES['file']['size'][$k];
	                        $res = upload_topic($_files);
	                         
	                        if($res['error']==0)
	                        {
	                            $dp_img[] = $res['url'];
	                        }
	                    }
	                }

	                $result = save_review($user_id,array("deal_id"=>$deal_id,"youhui_id"=>$youhui_id,"event_id"=>$event_id,"location_id"=>$location_id), $content, $point, $dp_img,array(),$point_group);

	                //$result = add_deal_dp($user_id, $content, $point, $deal_id);
	                $status = $result['status']?$result['status']:0;
	                $info = $result['info'];
	                output($root,$status,$info);
	            }
	        }else{
	            output($root,0,'您还不能点评');
	        }
	        
	    }
	    
	    output($root);
	}
	
	
	/**
	 * 获取点评
	 * 输入：
	 * type  [string] 点评的数据类型 (1.团购或者商品  deal/2.优惠券  youhui /3.活动  event/4.门店 store)
	 * data_id [int] 团购/商品/优惠券/活动/门店  的ID
	 * limit [string] 0,10 分页所在的页数,或穿单个数字 10 就取10条
	 * 
	 * 输出：
	 * item [array] 点评数据
	 * [1] => Array
	 (
    	 [id] => 5 [int]用户点评数据编号
    	 [create_time] => 2015-04-07   [string] 点评时间
    	 [content] => 不错不错  [string]   点评内容
    	 [reply_content] => 那是不错的了，可以信任的品牌  [string] 管理员回复 
    	 [point] => 3  [int]   点评分数
    	 [user_name] => fanwe  [string] 点评用户名称
    	 [images] => Array 
    	 (
        	 [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36_120x120.jpg string:点评图片 60x60
        	 [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986_120x120.jpg
        	 [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061_120x120.jpg
    	 )
    	
    	 [oimages] => Array
    	 (
        	 [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36.jpg  点评:原图
        	 [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986.jpg
        	 [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061.jpg
    	 )
	
	 )
	 */
	public function get_dp(){
	    /*参数列表*/
	    $id = intval($GLOBALS['request']['data_id']);
	    $type = strim($GLOBALS['request']['type']);
	    $limit = strim($GLOBALS['request']['limit']);
	    
	    $deal_id = 0;
	    $youhui_id = 0;
	    $location_id = 0;
	    $event_id = 0;
	    
	    $root = array();

	   	    
	    /*根据类型获取数据*/
	    if($type=="deal")
	    {
	        $deal_id = $id;
	        require_once APP_ROOT_PATH."system/model/deal.php";
	        $deal_info = get_deal($deal_id);
	        $relate_data_name = $deal_info['name'];
	    }
	    elseif($type=="store")
	    {
	        $location_id = $id;
	        require_once APP_ROOT_PATH."system/model/supplier.php";
	        $location_info = get_location($location_id);
	        $relate_data_name = $location_info['name'];
	    }
	    elseif($type=="youhui")
	    {
	        $youhui_id = $id;
	        require_once APP_ROOT_PATH."system/model/youhui.php";
	        $youhui_info = get_youhui($youhui_id);
	        $relate_data_name = $youhui_info['name'];
	    }
	    elseif($type=="event")
	    {
	        $event_id = $id;
	        require_once APP_ROOT_PATH."system/model/event.php";
	        $event_info = get_event($event_id);
	        $relate_data_name = $event_info['name'];
	    }

	    require_once APP_ROOT_PATH."system/model/review.php";
	    require_once APP_ROOT_PATH."system/model/user.php";
	    
	    /*获点评数据*/
	    $message_re = get_dp_list($limit,$param=array("deal_id"=>$deal_id,"youhui_id"=>$youhui_id,"event_id"=>$event_id,"location_id"=>$location_id,"tag"=>""),"","");
	    $data = array();
	    
	    foreach($message_re['list'] as $k=>$v){
	    
	        $temp_arr = array();
	         
	        $temp_arr['id'] = $v['id'];
	        $temp_arr['create_time'] = $v['create_time'] > 0 ?to_date($v['create_time'],'Y-m-d'):'';
	        $temp_arr['content'] = $v['content'];
	        $temp_arr['reply_content']= $v['reply_content']?$v['reply_content']:'';
	        $temp_arr['point_percent'] = $v['point_percent']>0?$v['point_percent']:0;
	        $temp_arr['point'] = $v['point'];
	    
	        $uinfo = load_user($v['user_id']);
	        $temp_arr['user_name'] = $uinfo['user_name'];
	    
	    
	    
	        $images = array();
	        $oimages = array();
	    
	        if($v['images']){
	            foreach ($v['images'] as $ik=>$iv){
	                $images[] = get_abs_img_root(get_spec_image($iv,60,60,1));
	                $oimages[] = get_abs_img_root($iv);
	            }
	             
	        }
	        $temp_arr['images'] = $images;
	        $temp_arr['oimages'] = $oimages;
	    
	    
	        $data[] = $temp_arr;
	    }
	    $root['item']=$data;
	    
	    if(count($data)>0)
	    {
	        $sql = "select count(*) from ".DB_PREFIX."supplier_location_dp where  ".$message_re['condition'];
	        $data['count'] = $GLOBALS['db']->getOne($sql);
	    }
	    $count = $data['count'];
	    $root['message_count']=$count;
	    
	    $root['type'] = $type;
	    $root['data_id']=$id;
	    output($root);
	}
	
}
?>