<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_youhuioModule extends MainBaseModule
{

    /**
     * 	 优惠券列表接口
     *
     * 	 输入:
     *  page    [int] 分页
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *  
     *  有权限的情况下返回以下内容
       [item] => Array  :array 优惠券列表数据
        (
            [0] => Array
                (
                    [id] => 27      :int    优惠券编号
                    [name] => 回家的路      :string 优惠券名称
                    [end_time] => 1435623932    :string 优惠券有效期结束时间
                    [f_end_time] => 2015-06-30  :string 格式化优惠券有效期结束时间
                    [user_count] => 0   :int 总下载次数
                    [icon] => http://localhost/o2onew/public/attachment/201505/27/16/55657f781190e_168x140.jpg  :string 优惠券展示图 84X70
                    [use_count] => 0    :int  优惠券已使用张数  
                )
                
        [page] => Array :array 分页
        (
            [page] => 1     
            [page_total] => 1
            [page_size] => 10
            [data_total] => 2
        )

     */
	public function index(){
	    /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "优惠券列表";
        
        /*获取参数*/
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
        
	    /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
  
	    //返回商户权限
	    if(!check_module_auth("youhuio")){
	        $root['is_auth'] = 0;
	        output($root,0,"没有操作权限");
	    }else{
	        $root['is_auth'] = 1;
	    }
	    
	  
	    //分页
	    $page_size = PAGE_SIZE;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    $condition = "y.is_effect = 1 and y.supplier_id=".$account_info['supplier_id']." and yll.location_id in (".implode(",", $account_info['location_ids']).")";
	     
	    $sql = 'select y.* from '.DB_PREFIX.'youhui y left join '.DB_PREFIX.'youhui_location_link yll on yll.youhui_id = y.id where y.is_effect = 1 AND y.supplier_id = '.$account_info['supplier_id'].' and yll.location_id in('.implode(",", $account_info['location_ids']).') GROUP BY y.id order by y.id desc';
	    $count_sql = 'select count(distinct y.id) as count from '.DB_PREFIX.'youhui y left join '.DB_PREFIX.'youhui_location_link yll on yll.youhui_id = y.id where y.is_effect = 1 AND y.supplier_id = 35 and yll.location_id in('.implode(",", $account_info['location_ids']).')';
	  
	    $result = $GLOBALS['db']->getAll($sql." limit ".$limit);
	    $count = $GLOBALS['db']->getOne($count_sql);


	  
	    //分页
	    $page_total = ceil($count/$page_size);
	    
	    foreach ($result as $k=>$v){
	        $temp_data = array();
	        $temp_data['id'] = $v['id'];
	        $temp_data['name'] = $v['name'];
	        $temp_data['end_time'] = $v['end_time'];
	        $temp_data['f_end_time'] = to_date($v['end_time'],"Y-m-d");
	        $temp_data['user_count'] = $v['user_count'];
	        $temp_data['icon'] = get_abs_img_root(get_spec_image($v['icon'],84,70,1));
	        $temp_data['use_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_log where youhui_id=".$v['id']." AND confirm_time>0");
	        $data[] = $temp_data;
	    }

	    $root['item'] = $data?$data:array();
	    $root['page_title'] = "优惠券列表";
	    $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        
        
        output($root);
    }
    
    
    /**
     * 	优惠券门店列表接口
     *
     * 	 输入:
     *  data_id [int] 优惠券编号
     *  page    [int] 分页
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *  
     *  有权限的情况下返回以下内容 
     * [youhui] => Array  :array 优惠券详细信息
        (   
            [id] => 26      ：int 优惠券ID
            [name] => 免费香港游         ：string 优惠券名称
            [end_time] => 1433015421    ：string 优惠券过期时间
            [f_end_time] => 2015-05-31  ：string 格式化优惠券过期时间
            [use_count] => 14   ：int 总使用优惠券总数
        )

        
        [locations] => Array    ：array 优惠券消费的门店列表
        (
            [0] => Array
                (
                    [location_id] => 34     ：int 门店ID
                    [use_count] => 12       ：int 在这门店使用的数量
                    [name] => 美丽人生摄影工作室      ：string 门店名称
                )
        )

     */
    function locations(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "优惠券门店列表";
        
        /*获取参数*/
        $data_id = intval($GLOBALS['request']['data_id']); 
  
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("youhuio")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
         
        //获取支持的门店，并查询统计(门店销量统计查询)
        require_once APP_ROOT_PATH."system/model/youhui.php";
        
        $youhui = get_youhui($data_id);
        if ($youhui){
            $youhui_item = array();
            
            $youhui_item['id'] = $youhui['id'];
            $youhui_item['name'] = $youhui['name'];
            $youhui_item['end_time'] = $youhui['end_time'];
            $youhui_item['f_end_time'] = to_date($youhui['end_time'],"Y-m-d");
            $youhui_item['use_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_log where youhui_id=".$youhui['id']." AND confirm_time>0");
            
            $locations = $GLOBALS['db']->getAll("SELECT yl.location_id,count(yl.youhui_id) as use_count,sl.name from ".DB_PREFIX."youhui_log yl left join ".DB_PREFIX."supplier_location sl on sl.id=yl.location_id  where yl.location_id in (select location_id from ".DB_PREFIX."youhui_location_link where youhui_id=".$data_id.") GROUP BY yl.location_id");
            
        }
        
        $root['youhui'] = $youhui_item?$youhui_item:array();
        $root['locations'] = $locations?$locations:array();
        
        output($root);
    }
    
    
    /**
     * 	 优惠券门店下优惠券列表接口
     *
     * 	 输入:
     *  youhui_id   [int] 优惠券ID
     *  location_id [int] 门店ID
     *  page    [int] 分页
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *  
     *  有权限的情况下返回以下内容 
     * [youhui] => Array       :array 优惠券详细信息
        (
            [id] => 26      ：int 优惠券编号
            [name] => 免费香港游     ：string 优惠券名称
            [end_time] => 1433015421    ：string 优惠券结束时间
            [f_end_time] => 2015-05-31  ：string 格式化优惠券结束时间
            [use_count] => 14   ：int 已经消费总数
        )

    [youhuis] => Array  ：array  优惠券使用明细列表
        (
            [0] => Array
                (
                    [youhui_id] => 26   ：int 优惠券ID
                    [youhui_sn] => 70726804   ： string 优惠券SN
                    [confirm_time] => 1432062135    ：string 使用时间
                    [user_name] => fanwe    ：string 使用会员
                    [f_confirm_time] => 2015-05-20  ：string 格式化使用时间
                )
          )
     */
    function youhuis(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];

        /*获取参数*/
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
        
        $youhui_id = intval($GLOBALS['request']['youhui_id']); 
        $location_id = intval($GLOBALS['request']['location_id']);
    
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
    
        //返回商户权限
        if(!check_module_auth("youhuio")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
        

        if (!in_array($location_id, $account_info['location_ids'])){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }
        
        //分页
        $page_size = PAGE_SIZE;
        $limit = (($page-1)*$page_size).",".$page_size;
         
        
        
        //获取支持的门店，并查询统计(门店销量统计查询)
        require_once APP_ROOT_PATH."system/model/youhui.php";
    
        $youhui = get_youhui($youhui_id);
        if ($youhui){
            $youhui_item = array();
    
            $youhui_item['id'] = $youhui['id'];
            $youhui_item['name'] = $youhui['name'];
            $youhui_item['end_time'] = $youhui['end_time'];
            $youhui_item['f_end_time'] = to_date($youhui['end_time'],"Y-m-d");
            $youhui_item['use_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_log where youhui_id=".$youhui['id']." AND confirm_time>0");
    
            $count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui_log where youhui_id=".$youhui_id." and location_id=".$location_id);
            $youhuis = $GLOBALS['db']->getAll("select yl.youhui_id,yl.youhui_sn,yl.confirm_time,u.user_name from ".DB_PREFIX."youhui_log yl left join ".DB_PREFIX."user u on u.id=yl.user_id where yl.youhui_id=".$youhui_id." and yl.location_id=".$location_id." order by yl.id desc limit ".$limit);
            foreach ($youhuis as $k=>$v){
                $youhuis[$k]['f_confirm_time'] = to_date($v['confirm_time'],"Y-m-d");
            }
            //门店名称
            $location_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier_location where id=".$location_id);
            //分页
            $page_total = ceil($count/$page_size);
            $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        }
        
        $root['youhui'] = $youhui_item?$youhui_item:array();
        $root['youhuis'] = $youhuis?$youhuis:array();
        $root['page_title'] = $location_name?$location_name:"优惠券列表";
    
        output($root);
    }
    
    
}
?>

