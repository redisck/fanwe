<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_dealoModule extends MainBaseModule
{

    /**
     * 	 团购列表
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
     *  
     *   [item] => Array    ：array 团购商品列表
        (
            [0] => Array
                (
                    [id] => 123     ：int 团购商品编号
                    [name] => 新发布团购         ：string 团购商品名称
                    [sub_name] => 1212  ：string 团购商品简短名称
                    [f_create_time] => 2015-05-30   ：string 创建团购商品的时间
                    [buy_count] => 1    ：团购商品的销量
                    [refund_count] => 0     ：团购商品的退款量
                    [confirm_count] => 1    ：团购商品的验证量
                    [icon] => http://localhost/o2onew/public/attachment/201505/29/19/cfa7b6474c4475_168x140.jpg :string 团购图片 84*70
                )
         )
         
        [page] => Array ：分页数据数组
        (
            [page] => 1
            [page_total] => 1
            [page_size] => 10
            [data_total] => 5
        )
      

     */
	public function index(){
	    /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        
        
        /*获取参数*/
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
        
	    /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
  
	    //返回商户权限
	    if(!check_module_auth("dealo")){
	        $root['is_auth'] = 0;
	        output($root,0,"没有操作权限");
	    }else{
	        $root['is_auth'] = 1;
	    }
	    

	    
	    //分页
	    $page_size = PAGE_SIZE;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    //商户支持的团购列表
// 	    select * from fanwe_deal d LEFT JOIN fanwe_deal_location_link dll on dll.deal_id = d.id where dll.location_id in (34,39) GROUP BY d.id
// 	    SELECT COUNT(DISTINCT d.id) from fanwe_deal  d LEFT JOIN fanwe_deal_location_link dll on dll.deal_id = d.id where dll.location_id in (34,39)
	    
	    $condition = " d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 0 and dll.location_id in (".implode(",", $account_info['location_ids']).") ";
	     
	    $sql = 'select d.id,d.name,d.sub_name,d.icon,d.current_price,d.create_time from '.DB_PREFIX.'deal d LEFT JOIN '.DB_PREFIX.'deal_location_link dll on dll.deal_id = d.id where '.$condition.' group by d.id order by id desc';
	    $count_sql = 'SELECT COUNT(DISTINCT d.id) from '.DB_PREFIX.'deal  d LEFT JOIN '.DB_PREFIX.'deal_location_link dll on dll.deal_id = d.id where '.$condition;
	  
	    $result = $GLOBALS['db']->getAll($sql." limit ".$limit);
	    $count = $GLOBALS['db']->getOne($count_sql);

        //获取统计信息
        foreach ($result as $v){
            $deal_ids[] = $v['id'];
        }
//         echo "select deal_id,count(*) as count from ".DB_PREFIX."deal_coupon where deal_id in(".implode(",",$deal_ids).")";exit;
        //1.团购券的销量 -数组
        $dealo_buy_counts = $GLOBALS['db']->getAll("select deal_id,count(*) as count from ".DB_PREFIX."deal_coupon where is_valid>0 and deal_id in(".implode(",",$deal_ids).") group by deal_id") ;
	    $f_dealo_buy_counts = array();
	    foreach ($dealo_buy_counts as $v){
	        $f_dealo_buy_counts[$v['deal_id']] = $v['count'];
	    }
	    
        
        //2.团购券的退款量 -数组
        $dealo_refund_counts = $GLOBALS['db']->getAll("select deal_id,count(*) as count from ".DB_PREFIX."deal_coupon where is_valid>0 and refund_status=2 and deal_id in(".implode(",",$deal_ids).")  group by deal_id") ;
        $f_dealo_refund_counts = array();
        foreach ($dealo_refund_counts as $v){
            $f_dealo_refund_counts[$v['deal_id']] = $v['count'];
        }
        
        //3.团购券验证量 -数组
        $dealo_confirm_counts = $GLOBALS['db']->getAll("select deal_id,count(*) as count from ".DB_PREFIX."deal_coupon where is_valid>0 and confirm_time>0 and deal_id in(".implode(",",$deal_ids).")  group by deal_id") ;
        $f_dealo_confirm_counts = array();
        foreach ($dealo_confirm_counts as $v){
            $f_dealo_confirm_counts[$v['deal_id']] = $v['count'];
        }
        
     
        
        
	    //分页
	    $page_total = ceil($count/$page_size);
	    
	    foreach ($result as $k=>$v){
	        $temp_data = array();
	        $temp_data['id'] = $v['id'];
	        $temp_data['name'] = $v['name'];
	        $temp_data['sub_name'] = $v['sub_name'];
	        $temp_data['f_create_time'] = to_date($v['create_time'],"Y-m-d");
	        $temp_data['buy_count'] = intval($f_dealo_buy_counts[$v['id']]);
	        $temp_data['refund_count'] = intval($f_dealo_refund_counts[$v['id']]);
	        $temp_data['confirm_count'] = intval($f_dealo_confirm_counts[$v['id']]);
	        $temp_data['icon'] = get_abs_img_root(get_spec_image($v['icon'],84,70,1));
	        $data[] = $temp_data;
	    }

	    $root['item'] = $data?$data:array();
	    $root['page_title'] = "团购列表";
	    $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        
        
        output($root);
    }
    
    /**
     * 	 团购门店列表
     *
     * 	 输入:
     *  data_id [int] 团购商品ID
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
     *
     *   [deal_info] => Array   ：array 团购详细数据
        (
            [id] => 96     ：int 团购商品编号
            [name] => 美梦成真：香奈儿COCO小姐，美团网免费送         ：string 团购商品名称
            [end_time] => 1428025020     ：string  团购到期时间
            [f_end_time] => 2015-04-03      ：string 格式化团购结束时间
            [buy_count] => 109  ：int 销售总数
        )

        [locations] => Array    ：array 门店销售数据列表
        (
            [0] => Array    
                (
                    [id] => 34     ：array 门店ID   
                    [name] => 美丽人生摄影工作室         ：string 门店名称
                    [use_count] => 109      ：int 门店验证数量
                )

        )
    
     */
    public function locations(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "门店销量列表";
        
        /*获取参数*/
        $data_id = intval($GLOBALS['request']['data_id']);

        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("dealo")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        //获取团购信息
        require_once APP_ROOT_PATH.'system/model/deal.php';
        $deal = get_deal($data_id);
        $deal_info = array();
        
        
        if($deal){
            $deal_info['id'] = $deal['id'];
            $deal_info['name'] = $deal['name'];
            $deal_info['end_time'] = $deal['end_time'];
            $deal_info['f_end_time'] = $deal['end_time']?to_date($deal['end_time'],"Y-m-d"):"不限时间";
            $deal_info['buy_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon where is_valid>0 and confirm_time>0 and deal_id=".$data_id);
            
            //获取门店和门店销量数据
            
        }else{
            output($root,0,"没有操作权限");
        }
        
        
        
        $locations = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."supplier_location where id in (".implode(",", $account_info['location_ids']).")");
        $location_count = $GLOBALS['db']->getAll("select count(*) as count,location_id from ".DB_PREFIX."deal_coupon where  confirm_time>0 and is_valid > 0 and location_id IN (".implode(",", $account_info['location_ids']).") and deal_id=".$data_id." group by location_id");
        
        foreach ($location_count as $v){
            $f_location_count[$v['location_id']] = $v['count'];
        }
        
        
        foreach ($locations as $k=>$v){
            $locations[$k]['use_count'] = intval($f_location_count[$v['id']]);
        }
        
        $root['deal_info'] = $deal_info;
        $root['locations'] = $locations;
        
        output($root);
    }
    
    /**
     * 	 团购列表
     *
     * 	 输入:
     *  page    [int] 分页
     *  deal_id     [int] 团购商品ID
     *  location_id [int] 团购门店ID
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
     *
     *   
     *    [deal_info] => Array   ：array 团购详细数据
        (
            [id] => 96     ：int 团购商品编号
            [name] => 美梦成真：香奈儿COCO小姐，美团网免费送         ：string 团购商品名称
            [end_time] => 1428025020     ：string  团购到期时间
            [f_end_time] => 2015-04-03      ：string 格式化团购结束时间
            [buy_count] => 109  ：int 销售总数
        )

        [deals] => Array    ：array 团购券 用户消费列表
        (
            [0] => Array
                (
                    [id] => 170     ：int 用户的团购券ID
                    [sn] => 96832623    ：团购券验证码
                    [confirm_time] => 1432257223    ：string 团购券消费时间
                    [user_name] => fanwe        ：string 团购券消费会员 
                    [f_confirm_time] => 2015-05-22  ：string 格式化团购券使用时间
                )
        )
    
     */
    public function deals(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        $root['page_title'] = "团购券列表";
        
        /*获取参数*/
        $deal_id = intval($GLOBALS['request']['deal_id']);
        $location_id = intval($GLOBALS['request']['location_id']);
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("dealo")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        //分页
        $page_size = PAGE_SIZE;
        $limit = (($page-1)*$page_size).",".$page_size;
        
        //获取团购信息
        require_once APP_ROOT_PATH.'system/model/deal.php';
        $deal = get_deal($deal_id);
        $deal_info = array();
        
        $count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_coupon  where confirm_time>0 and is_valid > 0 and deal_id =".$deal_id." and location_id =".$location_id);
        if($deal){
            $deal_info['id'] = $deal['id'];
            $deal_info['name'] = $deal['name'];
            $deal_info['end_time'] = $deal['end_time'];
            $deal_info['f_end_time'] = $deal['end_time']?to_date($deal['end_time'],"Y-m-d"):"不限时";
            $deal_info['buy_count'] = $count;
        
            //获取门店和门店销量数据
        
        }else{
            output($root,0,"没有操作权限");
        }
        $root['deal_info'] = $deal_info;
        
        //获取团购信息
        $deals = $GLOBALS['db']->getAll("select d.id,d.sn,d.confirm_time,u.user_name from ".DB_PREFIX."deal_coupon d left join ".DB_PREFIX."user u on u.id = d.user_id where confirm_time>0 and is_valid > 0 and deal_id =".$deal_id." and location_id =".$location_id." order by id desc limit ".$limit);
//         echo "select d.id,d.sn,d.confirm_time,u.user_name from ".DB_PREFIX."deal_coupon d left join ".DB_PREFIX."user u on u.id = d.user_id where confirm_time>0 and is_valid > 0 and deal_id =".$deal_id." and location_id =".$location_id." order by id desc limit".$limit;exit;
        
        
        
        
        //分页
        $page_total = ceil($count/$page_size);
        
        foreach ($deals as $k=>$v){
            $deals[$k]['f_confirm_time'] = to_date($v['confirm_time'],"Y-m-d");
        }
        $root['deals'] = $deals;
        $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        
        
        output($root);
    }
    
    
}
?>

