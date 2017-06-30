<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dcpointModule extends MainBaseModule
{
	
	/**
	 * 商家点评列表页面
	 * 测试页面：http://localhost/o2onew/mapi/index.php?ctl=dcpoint&r_type=2&lid=41
	 *
	 * 输入：
	 * lid:int 商家ID
	 * page:int 当前的页数
	 * 
	 * 输出：
	 * is_has_location:int是否存在些商家， 0为不存在，1为存在
	 * city_name：string当前城市名称
	 * page_title:string 页面标题
	 * page_keyword:string 页面关键词
	 * page_description:string 页面描述         
	 * page:array 分页信息 array("page"=>当前页数,"page_total"=>总页数,"page_size"=>分页量,"data_total"=>数据总量);
	 * $dclocation:array:array 商家信息,结构如下：
	 * 其中  avg_point 为门店的整体评分，is_collected为是否已经收藏
	 *   Array
        (
            [id] => 41
            [name] => 果果外卖
            [preview] => http://localhost/o2onew/public/attachment/201504/17/10/55306e5b0f72a_1200x900.jpg
            [avg_point] => 3.5556
            [is_collected] => 1
        )
    *	dp_data：array:array  点评评分，结构如下 
		 Array
        (
            [0] => Array
                (
                    [id] => 3
                    [name] => 口味
                    [avg_point] => 3.5
                    [avg_point_percent] => 70
                )

            [1] => Array
                (
                    [id] => 4
                    [name] => 服务
                    [avg_point] => 3.6
                    [avg_point_percent] => 71.25
                )

        )
        
     *dp_list：array:array,点评列表 ，结构如下   
     *其中point为星星级数，一共5星
     *fpoint为好，中，差评，fpoint=1，为好评，fpoint=2，为中评，fpoint=3为差评
      Array(
	  [0] => Array
                (
                    [id] => 23
                    [title] => 
                    [content] => 7777777777777
                    [create_time] => 1436985928
                    [point] => 5
                    [user_id] => 71
                    [status] => 1
                    [images_cache] => a:1:{i:0;s:68:"./public/comment/201507/16/10/8ad90ad20aeac8837ce5bcc1c11276ff41.jpg";}
                    [avg_price] => 0.0000
                    [supplier_location_id] => 41
                    [fpoint] => 1
                    [user_info] => Array
                        (
                            [id] => 71
                            [user_name] => fanwe
                        )

                    [create_time_format] => 2015-07-16
                    [images] => Array
                        (
                            [0] => http://localhost/o2onew/public/comment/201507/16/10/8ad90ad20aeac8837ce5bcc1c11276ff41_200x160.jpg
                        )

                )  
         )         
	 **/
	public function index()
	{	
		global_run();
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		$page = intval($GLOBALS['request']['page']);
		if($page==0){
			$page = 1;
		}
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		$location_id = strim($GLOBALS['request']['lid']);
		$dclocation=$GLOBALS['db']->getRow("select id,name,preview,avg_point,is_dc,is_reserve from ".DB_PREFIX."supplier_location where id =".$location_id);
		$root=array();
		if($dclocation)
		{	
			$dclocation['preview']=get_abs_img_root(get_spec_image($dclocation['preview'],600,450,1));
			
			//关于分类信息与seo
			$page_title = $dclocation['name'];
			$page_keyword = $dclocation['name'];
			$page_description = $dclocation['name'];
			
			$is_colloect=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_location_sc where location_id=".$dclocation['id']." and user_id=".$user_id);
			if($is_colloect>0){
				$dclocation['is_collected']=1;
			}else{
				$dclocation['is_collected']=0;
			}

			//分页
			$page_size = PAGE_SIZE;
			$limit = (($page-1)*$page_size).",".$page_size;

			require_once APP_ROOT_PATH."system/model/review.php";
			$item_array=array("location_id"=>$location_id);
			$dp_data = load_dp_info($item_array);
			$dp_data_new=array();
			foreach($dp_data as $k=>$v){
				if($k=='point_group'){
					$dp_data_new=$v;	
				}
			}
			$dp_list=$this->get_dp_list($location_id,$limit);
			
			$total=$dp_list['total'];
			$page_total = ceil($total/$page_size);
			$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$total);
			$root['is_has_location']=1;
			$root['page_title']=$page_title;
			$root['page_title']=$page_title;
			$root['page_keyword']=$page_keyword;
			$root['page_description']=$page_description;
			$root['avg_point']=$dclocation['avg_point'];
			$root['dclocation']=$dclocation;
			$root['dp_data']=$dp_data_new;
			foreach($dp_list['list'] as $k=>$v){
			
			$dp_list['list'][$k]['counti']=count($v['images']);//图片数量
				foreach($dp_list['list'][$k]['images'] as $kk=>$vv){
					
					$dp_list['list'][$k]['images'][$kk]=get_abs_img_root($vv);
				}
			}
			$root['dp_list']=$dp_list['list'];
	
			output($root);
		}
		else
		{	
			$root['is_has_location']=0;
			output($root);
		}
		
		
	}
	

	

	public function get_dp_list($location_id,$limit){

    $orderby = " order by create_time desc ";
    $limit = " limit ".$limit;
    $sql = "SELECT id,title,content,create_time,point,user_id,status,reply_content,images_cache,avg_price,supplier_location_id FROM ".DB_PREFIX."supplier_location_dp where status=1 and supplier_location_id =".$location_id.$orderby.$limit;
    $data = $GLOBALS['db']->getAll($sql);
    $sql_count = "SELECT count(*) FROM ".DB_PREFIX."supplier_location_dp where status=1 and supplier_location_id =".$location_id;
    
    $total_count=$GLOBALS['db']->getOne($sql_count);
    if($data){
        foreach ($data as $k=>$v){
            //格式化好 中 差 评价
            if($v['point'] == 5 || $v['point'] == 4){
                $v['fpoint'] = 1;
            }
            if($v['point'] == 3){
                $v['fpoint'] = 2;
            }
            if($v['point'] == 1 || $v['point'] == 2){
                $v['fpoint'] = 3;
            }
            
            //获取用户信息
            $user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id = ".$v['user_id']);
            $v['user_name'] = $user_name;

            //获取条件关联信息
            $v['create_time_format'] = to_date($v['create_time']);
            $images = unserialize($v['images_cache']);
            
            $images_new=array();
            foreach($images as $kk=>$vv){
            	$images_new[]=get_abs_img_root(get_spec_image($vv,100,80,1));
            }
          
            $v['images'] = $images_new?$images_new:array();
            $result['list'][] = $v;

        }
    }
    $result['total']=$total_count;
    return $result;
}
	
	
	
}
?>