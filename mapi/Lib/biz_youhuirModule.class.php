<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_youhuirModule extends MainBaseModule
{

     /**
     * 优惠券评价列表
     * 输入：
     * 
     * page [int] 分页所在的页数
     *
     *
     * 输出：
     * 
     *  [biz_user_status] => 1 [int]用户登录状态 0未登录 1已登录
     *  [is_auth] => 1 操作权限 0无/1有
     *  [item] => Array 商户下所有优惠券列表
        (
            [0] => Array
                (
                    [id] => 97   [int] 优惠券编号   
                    [name] => 香奈儿COCO小姐      [string]商品名称
                    [total_num] => 10    [int]优惠券总数
                    [user_count] => 10    [int]优惠券用户总下载数
                    [avg_point] => 3.3    [float] 平均分
                    [dp_count] => 0 [int]评价人数
                )
         )
     *  
     */
	public function index(){
	   
	    /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "优惠券评价";
	    
        /*获取参数*/
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
	    /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
       
	    //返回商户权限
	    if(!check_module_auth("youhuir")){
	        $root['is_auth'] = 0;
	        output($root,0,"没有操作评价权限");
	    }else{
	        $root['is_auth'] = 1;
	    }

	    //分页
	    $page_size = PAGE_SIZE;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    $condition = "is_effect = 1 and supplier_id=".$account_info['supplier_id'];
	    
	    $result = $GLOBALS['db']->getAll("select id,name,total_num,user_count,avg_point,dp_count from ".DB_PREFIX."youhui where ".$condition." order by id desc limit ".$limit);
	    $count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."youhui where ".$condition);
	    //分页
	    $page_total = ceil($count/$page_size);
	    
	    if($result){
	        foreach ($result as $k=>$v){
	            $result[$k]['avg_point'] = round($v['avg_point'],1);
	        }
	    }
	    $root['item'] = $result?$result:array();
	    $root['page_title'] = "优惠券评价列表";
	    $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        output($root);
    }
    
    
    /**
     * 评价数据列表
     * 输入：
     * 
     * data_id [int] 优惠券  的ID
     * is_bad [int] 是否差评数据 0 全部 1 差评数据
     * page [int] 分页所在的页数
     *
     *
     * 输出：
     *  [is_bad] => 1  [int] 是否差评数据 0否/1是
     *  [biz_user_status] => 1 [int]用户登录状态 0未登录 1已登录
     *  [is_auth] => 1 操作权限 0无/1有
     *  [data_id] => 96 [int] 查询的团购或商品的数据ID
     * [item]=>array(  评价数据数组
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
                                     [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36_120x120.jpg   string:评价图片 60x60
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
            
         [count] => 1  int:评价数量
         [name] => 普吉岛旅游                 string:评价数据的名称 （商品/团购/优惠券/活动）
         [star_1] => 0       int:一星人数
         [star_2] => 0       int:二星人数
         [star_3] => 1       int:三星人数
         [star_4] => 0       int:四星人数
         [star_5] => 1       int:五星人数
         [star_dp_width_1] => 0      int:一星评价显示进度条 长度
         [star_dp_width_2] => 0      int:二星评价显示进度条 长度
         [star_dp_width_3] => 100    int:三星评价显示进度条 长度
         [star_dp_width_4] => 0      int:四星评价显示进度条 长度
         [star_dp_width_5] => 100    int:五星评价显示进度条 长度
         [buy_dp_sum] => 2           int:购买评价数量
         [buy_dp_avg] => 3           float:评价平均值
         [buy_dp_width] => 60        int:平均值 进度条长度
              
     */
    public function youhuir_dp_list(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "优惠券评价详情";
        
        /*获取参数*/
        $data_id = intval($GLOBALS['request']['data_id']);
        $is_bad = intval($GLOBALS['request']['is_bad']);
        $root['is_bad'] = $is_bad;
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }

        //返回商户权限
        if(!check_module_auth("youhuir")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作评价权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        /*分页处理*/
        $page = intval($GLOBALS['request']['page']);/*分页*/
        
        $page=$page==0?1:$page;
        $page_size = PAGE_SIZE;
        $limit = (($page-1)*$page_size).",".$page_size;
        

        require_once APP_ROOT_PATH."system/model/wap_biz_dp.php";
        $result = wap_biz_get_dp_list($limit,"youhui", $data_id,$is_bad);
        $root = array_merge($root,$result);
       
        $page_total = ceil($result['count']/$page_size);
        $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$result['count']);
        output($root);
    }
    
    
    
    /**
     * 评价回复页面
     * 输入：
     *
     * data_id [int] 优惠券  的ID
     *
     *
     * 输出：
     * 
     * 
     *  [biz_user_status] => 1 [int]用户登录状态 0未登录 1已登录
     *  [is_auth] => 1 [int]操作权限 0无/1有
     *  [data_id] => 9 [int]数据ID
     *  [reply_content] =>写教案集   [string] 管理员回复内容
     */
    public function youhuir_reply_dp(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "优惠券评价回复";
        
        /*获取参数*/
        $data_id = intval($GLOBALS['request']['data_id']);

        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("youhuir")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作评价权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        //获取评价数据
        $dp_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location_dp where id = ".$data_id." and supplier_id=".$account_info['supplier_id']);
        
        if (empty($dp_info)){
            output($root,0,"评价数据不存在");
        }else{
            $root['data_id'] = $dp_info['id'];
            $root['reply_content'] = $dp_info['reply_content'];
        }
        
        output($root);
    }
    
    
    /**
     * 评价回复发布
     * 输入：
     *
     * data_id [int] 团购/商品  的ID
     * reply_content [string] 回复内容
     *
     * 输出：
     *
     *
     *  [biz_user_status] => 1 [int]用户登录状态 0未登录 1已登录
     *  [is_auth] => 1 [int]操作权限 0无/1有
     *  [obj_id] => 9 [int]数据来源ID (优惠券ID)
     *  ['status'] = 1 [int] 回复状态是否成功 0失败 1成功
     *  ['msg'] = '[回复]' [string] 错误消息/回复成功后的内容
     */
    public function do_reply_dp(){

        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "优惠券评价回复";
        
        /*获取参数*/
        $data_id = intval($GLOBALS['request']['data_id']);
        $reply_content = strim($GLOBALS['request']['reply_content']);
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("youhuir")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作评价权限");
        }else{
            $root['is_auth'] = 1;
        }

        
        require_once APP_ROOT_PATH.'system/model/review.php';
        $result = biz_do_reply_dp($account_info['id'],$data_id,$reply_content);

        $root = array_merge($root,$result);
        output($root);  
    }
    
    
   
}
?>

