<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class dealModule extends MainBaseModule
{
	
	/**
	 * 商品详细页接口
	 * 输入：
	 * data_id: int 商品ID

	 * 
	 * 
	 * 
	 * 输出：
	 *
    	[id] => 73 [int] 商品ID
        [name] => 仅售388元！价值899元的福州明视眼镜单人配镜套餐，含全场599元以内镜框1次+全场300元以内镜片1次。 [string] 商品名称
        [share_url] => [string] 分享链接
        [sub_name] => 明视眼镜  [string] 简短商品名称
        [brief] => 【37店通用】明视眼镜  [string] 简介
        [current_price] => 388  [ float] 当前价格
        [origin_price] => 899  [ float] 原价
        [return_score_show] => 0 [int] 所需要的积分，buy_type为1时显示的价格
        [icon] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9b8e44904_600x364.jpg   [string] 商品缩略图 300X182
        [begin_time] => 1424829400  [string] 开始时间
        [end_time] => 1519782997    [string] 结束时间
        [time_status] => 1  [int] 时间状态  (0 未开始 / 1 可以兑换或者购买 / 2 已经过期)
        [now_time] => 1429125598 [string] 当前时间
        [buy_count] => 7 [int] 销量（购买的件数）
        [buy_type] => 0 [int] 购买类型， 团购商品的类型0：普通 1:积分商品
        [is_shop] => 0 [int] 是否为商城商品0：否 1:是
        [is_collect] =>0 [int ] 是否收藏商品   0：否 1：是
        [is_my_fx]=>0 [int] 是否是我的分销商品    0：否 1：是
        [deal_attr] => Array [array] 商品属性数据
                    [0] => Array
                    (
                        [id] => 17  [int] 属性分类 ID
                        [name] => 时段    [string] 属性名称
                        [attr_list] => Array [array] 属性下的套餐
                            (
                                [0] => Array
                                    (
                                        [id] => 274  [int]套餐编号
                                        [name] => 早上  [string] 套餐名称
                                        [price] => 0.0000 [float] 递增的价格
                                    )
    
                            )
    
                    )
        [avg_point] => 3 [float] 商品点评平均分
        [dp_count] => 5 [int] 点评人数
        [supplier_location_count] => 4  [int] 门店总数
        [last_time] => 90572607 [int] 剩余的秒数
        [last_time_format] => 1048天以上   [string] 剩余的天数 (结束为0)
        [deal_tags] => Array [array] 商品标签
            (
                [0] => Array
                    (
                        [k] => 2  [int] 标签编号
                        [v] => 多套餐 [string] 标签名称
                    )
    
            )
        [images] => Array [array] 商品图集 230X140
        (
            [0] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9b8e44904_460x280.jpg
            [1] => http://localhost/o2onew/public/attachment/201504/17/11/5530793f0e95d_460x280.jpg
            [2] => http://localhost/o2onew/public/attachment/201504/17/11/553079440bbbf_460x280.jpg
        )

        [oimages] => Array  商品图集原图  
        (
            [0] => http://localhost/o2onew/public/attachment/201502/25/17/54ed9b8e44904.jpg
            [1] => http://localhost/o2onew/public/attachment/201504/17/11/5530793f0e95d.jpg
            [2] => http://localhost/o2onew/public/attachment/201504/17/11/553079440bbbf.jpg
        )
        [description]=> <li id="side;"><b>店内部分菜品价格参考</b>[string] 商品详情 HTML 格式
        [notes] => <span style="font-family:ht;background-color:#e2e8eb;">购买须知</span> [string] 购买须知 HTML 格式
       	[xpoint] => [float] 所在经度
        [ypoint] => [float] 所在纬度
        [supplier_location_list] => Array [array] 门店数据列表
        (
            [0] => Array
                (
                    [id] => 35  [int] 门店编号
                    [name] => 明视眼镜（台江万达店）  [string] 门店名称
                    [address] => 台江区鳌江路8号金融街万达广场一层B区37号 [string] 门店地址
                    [tel] => 0591-89800987 [string] 门店联系方式
                    [xpoint] => 经度 [float]
                    [ypoint] => 纬度 [float]
                )

        )
        [dp_list] => Array [array] 点评数据列表
        (
          [4] => Array
                (
                    [id] => 5 [int] 点评数据ID
                    [create_time] => 2015-04-07 [string] 点评时间
                    [content] => 不错不错   [string] 点评内容
                    [reply_content] => 那是不错的了，可以信任的品牌 [string] 管理员回复内容
                    [point] => 5    [int] 点评分数
                    [user_name] => fanwe  [string] 点评用户名称
                    [images] => Array [array] 点评图集 压缩后的图片
                        (
                            [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36_120x120.jpg   [string] 点评图片 60X60
                            [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986_120x120.jpg   [string] 点评图片 60X60
                            [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061_120x120.jpg   [string] 点评图片 60X60
                        )

                    [oimages] => Array [array] 点评图集 原图
                        (
                            [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36.jpg [string] 点评图片 原图
                            [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986.jpg [string] 点评图片 原图  
                            [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061.jpg [string] 点评图片 原图
                        )

                )

        ),
		//如果该商品有关联商品
		[relate_data]	=>	Array[array]	关联商品数据
			'goodsList'	=>	array(
								//其他字段与主商品一致，增加两个key(属性和库存)
								[stock] => Array(
										[335_337] => Array(
												[id] => 162
												[deal_id] => 64
												[attr_cfg] => Array(
														[19] => 棕色
														[20] => 均码
													)
												[stock_cfg] => 2
												[attr_str] => 棕色均码
												[buy_count] => 0
												[attr_key] => 335_337
											)
									)
								[deal_attr] => Array(
										[0] => Array(
												[id] => 20
												[name] => 尺码
												[attr_list] => Array(
														[0] => Array(
																[id] => 337
																[name] => 均码
																[price] => 3.0000
																[is_checked] => 1
															)
													)
											)
								
										[1] => Array(
												[id] => 19
												[name] => 颜色
												[attr_list] => Array(
														[0] => Array(
																[id] => 335
																[name] => 棕色
																[price] => 1.0000
																[is_checked] => 1
															)
													)
											)
									)
								)
							),
			'dealArray'	=>	array(
								'id'=>array(
									'name'=>'',
									'origin_price'=>'',
									'current_price'=>'',
									'min_bought'=>'',
									'max_bought'=>''
								),
							),
			'attrArray'	=>	array(
								'id'=>array(
									'规格类型'=>array(
										'规格id'=>array(),
									),
								),
							),
			'stockArray'	=>	array(
								'id'=>array(
									'规格类型_规格类型'=>array(),
								),
							),
	  )
      
	 * 
	 */
	public function index()
	{
	    $user = $GLOBALS['user_info'];
	    $user_id  = intval($user['id']);

		$root = array();
		$data_id = intval($GLOBALS['request']['data_id']);//商品ID		
		
		require_once APP_ROOT_PATH."system/model/deal.php";
		require_once APP_ROOT_PATH."system/model/supplier.php";
		$data = get_deal($data_id);
		if($data['id']>0)
		{
			$join = " left join ".DB_PREFIX."deal_location_link as l on sl.id = l.location_id ";
			$where = " l.deal_id = ".$data['id']." ";
			$locations = get_location_list($data['supplier_location_count'],array("supplier_id"=>$data['supplier_id']),$join,$where);
		}else{
			$locations = get_location_list($data['supplier_location_count'],array("supplier_id"=>$data['supplier_id']));
		}
		$data = format_deal_item($data);
		
		if($user_id>0){
		    $is_collect = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_collect where user_id = ".$user_id." and deal_id = ".$data_id);
		    if(defined("FX_LEVEL"))
		    $is_my_fx = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_deal where user_id = ".$user_id." and deal_id = ".$data_id);
		}
		$data['is_my_fx'] =$is_my_fx?$is_my_fx:0;
		$data['is_collect'] = $is_collect>0?1:0;
		
		/*门店数据*/
		$supplier_location_list = array();
		
		if($locations){
		    foreach ($locations['list'] as $k=>$v){
		        $temp_location = array();
		        $temp_location['id'] = $v['id'];
		        $temp_location['name'] = $v['name'];
		        $temp_location['address'] = $v['address'];
		        $temp_location['tel'] = $v['tel'];
		        $temp_location['xpoint'] = $v['xpoint'];
		        $temp_location['ypoint'] = $v['ypoint'];
		        $supplier_location_list[] = $temp_location;
		    }
		}
		$data['supplier_location_list'] = $supplier_location_list;
		
		/*点评数据*/
		require_once APP_ROOT_PATH."system/model/review.php";
	    require_once APP_ROOT_PATH."system/model/user.php";
	    
	    /*获点评数据*/
	    $dp_list = get_dp_list(3,$param=array("deal_id"=>$data_id),"","");
	    $format_dp_list = array();
	    
	    foreach($dp_list['list'] as $k=>$v){
	    
	        $temp_arr = array();
	         
	        $temp_arr['id'] = $v['id'];
	        $temp_arr['create_time'] = $v['create_time'] > 0 ?to_date($v['create_time'],'Y-m-d'):'';
	        $temp_arr['content'] = $v['content'];
	        $temp_arr['reply_content']= $v['reply_content']?$v['reply_content']:'';
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
	    
	    
	        $format_dp_list[] = $temp_arr;
	    }
	    sort($data['deal_attr']);
	    $data['dp_list'] = $format_dp_list;
		$data['page_title'] = $data['sub_name'];
		//关联商品数据
		$relate_data = $this->getRelateData($data_id);
		if( !empty($relate_data) ){
			//app版本不需要 
			$type = intval($GLOBALS['request']['type']);//商品ID		
			if( empty($type) ){
				unset($relate_data['attrArray']);
				unset($relate_data['stockArray']);
			}
			$data['relate_data'] = $relate_data;
		}
		output($data);
	}
	/**
	 * 商品详细页接口
	 * 输入：
	 * data_id: int 商品ID
	 *
	 * 输出：
	 * id:[int]商品ID
	 * name: [string] 商品名称
	 * description： [string] 商品详情 HTML 格式
	 */
	public function detail(){
	    $root = array();
	    $data_id = intval($GLOBALS['request']['data_id']);//商品ID
	    
	    require_once APP_ROOT_PATH."system/model/deal.php";
	    $deal = get_deal($data_id);
	    if($deal){
	        $data['id']=$deal['id'];
	        $data['name']=$deal['name'];
	        $data['description']= get_abs_img_root(format_html_content_image($deal['description'], 150));
	    }
	    if($deal['is_shop']==0)
	    $data['page_title'] = "团购详情";
	    else
	    	$data['page_title'] = "商品详情";
	    output($data);
	}
	
	/**
	 * 收藏接口
	 * 输入：
	 * data_id: int 商品ID
	
	 *
	 *
	 *
	 * 输出：
	 * is_collect [int] 0：未收藏 ，1已收藏
	 *
	 */
	public function add_collect()
	{
	    //检查用户,用户密码
	    $user = $GLOBALS['user_info'];
	    $user_id  = intval($user['id']);
	     
	    $user_login_status = check_login();
	    if($user_login_status==LOGIN_STATUS_NOLOGIN){
	        $root['user_login_status'] = $user_login_status;
	        $status = 0;
	        $info = '请先登录';
	    }
	    else
	    {
	        $root['user_login_status'] = $user_login_status;
	
	        $goods_id = intval($GLOBALS['request']['id']);
	        $goods_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".$goods_id." and is_effect = 1 and is_delete = 0");
	        if($goods_info)
	        {
	            $sql = "INSERT INTO `".DB_PREFIX."deal_collect` (`id`,`deal_id`, `user_id`, `create_time`) select '0','".$goods_info['id']."','".$user_id."','".get_gmtime()."' from dual where not exists (select * from `".DB_PREFIX."deal_collect` where `deal_id`= '".$goods_info['id']."' and `user_id` = ".$user_id.")";
	            $GLOBALS['db']->query($sql);
	            if($GLOBALS['db']->affected_rows()>0){
	                $root['is_collect'] = 1;
	                $info = "收藏成功";
	                $status = 1;
	            }
	            else
	            {
	            	$root['is_collect'] = 1;
	            	$info = "您已经收藏了该商品";
	            	$status = 1;
	            }
	            
	        }
	    }
	    output($root,$status,$info);
	}
	
	/**
	 * 
	 * 根据deal_ids获取列表信息(包括属性，库存)
	 * 
	 * return array(
	 * 	'goodsList'	=>	array(),
	 * 	'dealArray'	=>	array(
	 * 						'id'=>array(
	 * 							'name'=>'','origin_price'=>'','current_price'=>''
	 * 						),
	 * 					),
	 * 	'attrArray'	=>	array(
	 * 						'id'=>array(
	 * 							'规格类型'=>array(
	 * 								'规格id'=>array(),
	 * 							),
	 * 						),
	 * 					),
	 * 	'stockArray'	=>	array(
	 * 						'id'=>array(
	 * 							'规格类型_规格类型'=>array(),
	 * 						),
	 * 					),
	 * 
	 * )
	*/
	private function getRelateData($data_id){
		$deal_ids = $GLOBALS['db']->getOne("select relate_ids from ".DB_PREFIX."relate_goods where good_id=".$data_id);
		$result = array();
		if($deal_ids){
			require_once APP_ROOT_PATH."system/model/deal.php";
			$result = getDetailedList($deal_ids.','.$data_id);
		}
		return $result;
	}
	
	
	
	
	
	
}
?>