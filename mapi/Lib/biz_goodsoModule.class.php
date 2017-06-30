<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_goodsoModule extends MainBaseModule
{

    /**
     * 	 商品列表接口
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
     *  [item] => Array
        (
            [0] => Array
                (
                    [id] => 124         ：int 商品ID
                    [name] => ipad mini2        ：string 商品名称
                    [sub_name] => mini2         ：string 商品缩略名
                    [icon] => http://localhost/o2onew/public/attachment/201506/03/09/556e540248da4_168x140.jpg  ：string 84*70
                    [current_price] => 0.0000   ：float 当前价格
                    [create_time] => 1433265066     ：string 创建时间
                    [buy_count] => 4    ：int 销量
                    [refund_count] => 1     ：int 退款量
                    [f_create_time] => 2015-06-03   ：string 格式化创建时间
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
	    if(!check_module_auth("goodso")){
	        $root['is_auth'] = 0;
	        output($root,0,"没有操作权限");
	    }else{
	        $root['is_auth'] = 1;
	    }
	    

	    
	    //分页
	    $page_size = PAGE_SIZE;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    
	    $supplier_id = $account_info['supplier_id'];
	    require_once APP_ROOT_PATH."system/model/deal_order.php";
	    $order_item_table_name = get_supplier_order_item_table_name($supplier_id);
	    $order_table_name = get_supplier_order_table_name($supplier_id);
 
	    $condition = " d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 1 and dll.location_id in (".implode(",", $account_info['location_ids']).") ";
	     
	    $sql = 'select  d.id,d.name,d.sub_name,d.icon,d.current_price,d.create_time,d.balance_price  from '.DB_PREFIX.'deal d LEFT JOIN '.DB_PREFIX.'deal_location_link dll on dll.deal_id = d.id where '.$condition.' group by d.id order by id desc';
	    $count_sql = 'SELECT COUNT(DISTINCT d.id) from '.DB_PREFIX.'deal  d LEFT JOIN '.DB_PREFIX.'deal_location_link dll on dll.deal_id = d.id where '.$condition;
	  
	    $result = $GLOBALS['db']->getAll($sql." limit ".$limit);
	    $count = $GLOBALS['db']->getOne($count_sql);

        //获取统计信息
        foreach ($result as $v){
            $deal_ids[] = $v['id'];
        }

       
        //1.商品的销量 -数组  （条件：is_balance =1 已经结算）
        $buy_count_sql = "select count(doi.id) as count ,doi.deal_id from fanwe_deal_order_item as doi left join fanwe_deal_order as do on doi.order_id = do.id ".
                            " where doi.deal_id in(".implode(",",$deal_ids).") and do.is_delete = 0 and do.type = 0 and doi.is_shop = 1 and do.pay_status = 2 GROUP BY doi.deal_id";       
        $goodso_buy_counts = $GLOBALS['db']->getAll($buy_count_sql) ;
//         echo $buy_count_sql;exit;
        foreach ($goodso_buy_counts as $v){
            $f_goodso_buy_counts[$v['deal_id']] = $v['count'];
        }
        
        //2.商品退款量 -数组
        $refund_counts_sql = "select count(doi.id) as count,doi.deal_id from fanwe_deal_order_item as doi left join fanwe_deal_order as do on doi.order_id = do.id ".
                            " where doi.deal_id in(".implode(",",$deal_ids).") and do.is_delete = 0 and do.type = 0 and doi.is_shop = 1 and do.pay_status = 2  and doi.refund_status = 2 GROUP BY doi.deal_id"; 
        $goodso_refund_counts = $GLOBALS['db']->getAll($refund_counts_sql) ;
        foreach ($goodso_refund_counts as $v){
            $f_goodso_refund_counts[$v['deal_id']] = $v['count'];
        }
        
        foreach ($result as $k=>$v){
            $result[$k]['buy_count'] = intval($f_goodso_buy_counts[$v['id']]);
            $result[$k]['refund_count'] = intval($f_goodso_refund_counts[$v['id']]);
            $result[$k]['f_create_time'] = to_date($v['create_time'],"Y-m-d");
            $result[$k]['icon'] = get_abs_img_root(get_spec_image($v['icon'],84,70,1));
        }
        
	    //分页
	    $page_total = ceil($count/$page_size);
	    


	    $root['item'] = $result?$result:array();
	    $root['page_title'] = "商品列表";
	    $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        
        
        output($root);
    }
    
    
    
    /**
     * 	 商品订单列表接口
     *
     * 	 输入:
     *  page    [int] 分页
     *  data_id [int] 商品ID
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
     * [item] => Array
        (
            [0] => Array
                (
                    [id] => 166         ：int 订单对象ID
                    [deal_id] => 112    ：int 商品ID
                    [number] => 1        ：int 购买商品数量
                    [unit_price] => 141     ：float 商品单价
                    [total_price] => 141    ：float 商品总价
                    [delivery_status] => 5  ：int 快递状态  （发货状态 0:未发货 1:已发货 5.无需发货）
                    [name] => 后台发布的商品 [黑白,165]  ：string 商品+规格名称
                    [order_sn] => 2015060305191715  ：string 订单SN
                    [sub_name] => 后他简短名称 [黑白,165]   ：string 短商品名称+规格
                    [is_arrival] => 0   ：int 是否已收货0:未收货1:已收货2:没收到货
                    [icon] => http://localhost/o2onew/public/attachment/201505/29/21/55686643b18c9_168x140.jpg ：string 商品图片 84*70
                    [order_status] => 1 ：int 订单状态
                    [refund_status] => 0    ：int 退款状态   1退款中 2已退款
                    [user_name] => fanwe    ：string 购买用户名称
                    [create_time] => 06/03 17:19    ：string 购买时间
                    [s_total_price] => 11   ：float 商品的结算价
                    [region_lv1] =>     ：string 国家
                    [region_lv2] =>     ：string 城市
                    [region_lv3] =>     ：string 地区
                    [region_lv4] =>     ：string 乡镇
                    [mobile] => 13555566666 :string 收货人手机号
                    [consignee] => 李四   ：string 收货人
                    [address_str] => 福建,福州,台江区,群升国际,350001  ：string 收货地址和邮编
                    [delivery] =>       ：string 快递
                    [delivery_notice] =>    ：string 快递备注
                )
     ORDER_DELIVERY_EXPIRE :int 订单结束时间
     NOW_TIME    ：int 服务器当前时间
      
                页面上显示状态的流程
     1.状态的显示  【完成的情况】order_status=1(订单已经完结) 或 delivery_status=5(无需配送) 或者 delivery_status=1 && is_arrival=1(已发货且会员已经收到货)
                                           【未完成】order_status!=1 (订单未完结) 且不存上上面的状态
     2.状态文字的显示   判断order_status =1 已经完结 ， 否则 判断delivery_status = 0 （未发货） 在判断  delivery_status=1 已发货{ 
                        is_arrival=1 (已收货) is_arrival=2(维权中) 
                        判断时间    $data.nowtime - $row.delivery_notice.delivery_time > 3600*24*$data.ORDER_DELIVERY_EXPIRE}
												(超期收货)
                        }
     3.退款状态显示 refund_status 退款状态   1退款中 2已退款

     */
    public function goodss(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];

        $root['page_title'] = "商品订单列表";
        /*获取参数*/
        $data_id = intval($GLOBALS['request']['data_id']);
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
    
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
    
        //返回商户权限
        if(!check_module_auth("goodso")){
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
        $supplier_id = $account_info['supplier_id'];
        require_once APP_ROOT_PATH."system/model/deal_order.php";
	    $order_item_table_name = get_supplier_order_item_table_name($supplier_id);
	    $order_table_name = get_supplier_order_table_name($supplier_id);
	    
	    if($data_id)
	       $condition = " and doi.deal_id = ". $data_id." and doi.supplier_id = ".$supplier_id;
	    else{
	        $condition = " and doi.supplier_id = ".$supplier_id;
	    }
	    
	    $sql = "select distinct(doi.id),doi.*,do.order_status,do.delivery_id,do.memo,do.create_time,do.order_sn,do.total_price,do.pay_amount,doi.refund_status,do.region_lv1,do.region_lv2,do.region_lv3,do.region_lv4,do.consignee,do.address,do.zip,do.mobile from ".$order_item_table_name." as doi left join ".
	 	    	$order_table_name." as do on doi.order_id = do.id  ".
	    		" where  do.is_delete = 0 and do.type = 0 and doi.is_shop = 1 and do.pay_status = 2 $condition order by doi.id desc limit ".$limit;

	    $sql_count = "select count(distinct(doi.id)) from ".$order_item_table_name." as doi left join ".
	    		$order_table_name." as do on doi.order_id = do.id  ".
	    		" where do.is_delete = 0 and do.type = 0 and doi.is_shop = 1  and do.pay_status = 2 $condition ";
        

	    $list = $GLOBALS['db']->getAll($sql);

	    $region_conf = load_auto_cache("cache_delivery_region_conf");
	    $delivery_conf = load_auto_cache("cache_delivery");

	    foreach($list as $k=>$v){
	    	
	    	$temp_data = array();
	    	$temp_data['id'] = $v['id'];
	    	$temp_data['deal_id'] = $v['deal_id'];
	    	$temp_data['number'] = $v['number'];
	    	$temp_data['unit_price'] = round($v['unit_price'],2);
	    	$temp_data['total_price'] = round($v['total_price'],2);
	    	$temp_data['delivery_status'] = $v['delivery_status'];
	    	$temp_data['name'] = $v['name'];
	    	$temp_data['order_sn'] = $v['order_sn'];
	    	$temp_data['sub_name'] = $v['sub_name'];
	    	$temp_data['is_arrival'] = $v['is_arrival'];
	    	$temp_data['icon'] = get_abs_img_root(get_spec_image($v['deal_icon'],84,70,1));
            $temp_data['order_status'] = $v['order_status'];
	    	$temp_data['refund_status'] = $v['refund_status'];
	    	$temp_data['balance_total_price'] = round($v['balance_total_price'],2);
	    	
            
	    	$uinfo = load_user($v['user_id']);
	    	$temp_data['user_name'] = $uinfo['user_name'];
	    	$temp_data['create_time'] = to_date($v['create_time'],"m/d H:i");
	    	$deal_info = load_auto_cache("deal",array("id"=>$v['deal_id']));
	    	
	    	$temp_data['s_total_price'] = $v['balance_total_price'] + $v['add_balance_price_total'];
	    	$temp_data['region_lv1'] = $region_conf[$v['region_lv1']]['name'];
	    	$temp_data['region_lv2'] = $region_conf[$v['region_lv2']]['name'];
	    	$temp_data['region_lv3'] = $region_conf[$v['region_lv3']]['name'];
	    	$temp_data['region_lv4'] = $region_conf[$v['region_lv4']]['name'];

            $temp_data['mobile'] = $v['mobile'];
            $temp_data['consignee'] = $v['consignee'];
	    	$temp_data['address_str'] = ($temp_data['region_lv2']?$temp_data['region_lv2']:'').",".($temp_data['region_lv3']?$temp_data['region_lv3']:'').",".($temp_data['region_lv4']?$temp_data['region_lv4']:'').",".($v['address']).",".$v['zip'];
	    	$temp_data['delivery'] = $delivery_conf[$v['delivery_id']]['name'];
	    	
	    	$temp_data['delivery_notice'] = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."delivery_notice where order_item_id = ".$v['id']." order by delivery_time desc limit 1");
	    	
	    	$result[] = $temp_data;
	    }
	    $root['order_delivery_expire'] = ORDER_DELIVERY_EXPIRE;
	    $root['now_time'] = NOW_TIME;
	    $count = $GLOBALS['db']->getOne($sql_count);
        
        //分页
        $page_total = ceil($count/$page_size);
         
    
        $root['item'] = $result?$result:array();
        
        $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
    
    
        output($root);
    }

    /**
     * 	 商品发货接口
     *
     * 	 输入:
     *  data_id [int] 发货订单商品ID
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
     *[location_list] => Array  门店列表
        (
            [0] => Array
                (
                    [id] => 32  :int 门店ID
                    [name] => 爱丁堡尊贵养生会所（福祥店)    :string 门店名称
                )

        )
        [rel_deal_id] => 88 :int 商品ID
        [express_name] => 圆通快递      :string 用户选择的快递
        [express_list] => Array     快递列表
        (
            [0] => Array       
                (
                    [id] => 3   :int 快递ID
                    [name] => EMS   :string 快递名称
                )
        )
        [address_data] => Array         配送地址信息
        (
            [consignee] => 张三                           :string 收件人
            [mobile] => 15544433333     :收件人手机
            [address] => 中国 福建 福州 台江区,群升国际E区111,350000  :string 收货地址
        )
        [doi_list] => Array     要发货的订单商品
        (
            [0] => Array
                (
                    [id] => 182         :int 商品订单ID
                    [deal_id] => 86     :int 商品ID
                    [deal_icon] => http://localhost/o2onew/public/attachment/201502/26/11/54ee903778026_168x140.jpg     :string 商品缩略图  84*70
                    [name] => 仅售8.9元！价值39元的下曹吸盘收纳置物架1个，…    :string 商品名称
                    [number] => 1   :int 购买商品数量
                    [total_price] => 8.9    :float 购买商品的总价格
                )
         )
        
        
     */
    public function delivery_form(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
        
        $root['page_title'] = "商品发货";
        /*获取参数*/
        $data_id = intval($GLOBALS['request']['data_id']);

        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
        
        //返回商户权限
        if(!check_module_auth("goodso")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
        //获取支持的门店
        $location_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $account_info['location_ids']) . ")");
        $root['location_list'] = $location_list?$location_list:array();

        
        $supplier_id = $account_info['supplier_id'];
        require_once APP_ROOT_PATH."system/model/deal_order.php";
        $order_item_table_name = get_supplier_order_item_table_name($supplier_id);
        $order_table_name = get_supplier_order_table_name($supplier_id);
        
        $sql = "select distinct(doi.id),doi.deal_id,doi.order_id,do.delivery_id,do.region_lv1,do.region_lv2,do.region_lv3,do.region_lv4,do.consignee,do.address,do.zip,do.mobile from ".$order_item_table_name." as doi left join ".
            $order_table_name." as do on doi.order_id = do.id  ".
            " where  do.is_delete = 0 and do.type = 0 and doi.is_shop = 1 and do.pay_status = 2 and doi.id=".$data_id;
        
        $doi_data = $GLOBALS['db']->getRow($sql);
        $root['rel_deal_id'] = $doi_data['deal_id'];
        
        $order_id = $doi_data['order_id'];
        
        
        $region_conf = load_auto_cache("cache_delivery_region_conf");
        $delivery_conf = load_auto_cache("cache_delivery");
        $root['express_name'] = $delivery_conf[$doi_data['delivery_id']]['name'];
        
        //获取支持快递
        $root['express_list'] =$GLOBALS['db']->getAll('select id,name from '.DB_PREFIX.'express where is_effect=1');

        if($order_id){
            $address_data=array();
            
            $address_data['consignee'] = $doi_data['consignee'];
            $address_data['mobile'] = $doi_data['mobile'];
            $address_data['address'] = $region_conf[$doi_data['region_lv1']]['name']." ".$region_conf[$doi_data['region_lv2']]['name']
                        ." ".$region_conf[$doi_data['region_lv3']]['name']." ".$region_conf[$doi_data['region_lv4']]['name'].",".$doi_data['address'].",".$doi_data['zip'];
            $root['address_data'] = $address_data;
            
            //查询订单相关要发货的商品
            $condition = " and doi.order_id = ". $order_id." and dl.location_id in (".implode(",",$account_info['location_ids']).")";
            
            $sql = "select distinct(doi.id),doi.deal_id,doi.deal_icon,doi.name,doi.number,doi.total_price  from ".$order_item_table_name." as doi left join ".
                $order_table_name." as do on doi.order_id = do.id  ".
                " left join ".DB_PREFIX."deal_location_link as dl on doi.deal_id = dl.deal_id".
                " where  do.is_delete = 0 and do.type = 0 and doi.is_shop = 1 and do.pay_status = 2 and doi.delivery_status=0 $condition ";
            $doi_list = $GLOBALS['db']->getAll($sql);
            
            foreach ($doi_list as $k=>$v){
                $doi_list[$k]['deal_icon'] =  get_abs_img_root(get_spec_image($v['deal_icon'],84,70,1));
                $doi_list[$k]['total_price'] = round($v['total_price'],2);
                $doi_list[$k]['name'] = msubstr($v['name'],0,25);
            }
            
            $root['doi_list'] = $doi_list;
        }else{
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        } 
        output($root);
    }
    
    /**
     * 	 商品发货接口
     *
     * 	 输入:
     *  rel_deal_id [int] 发货订单商品ID
     *  doi_ids  [array] 发货商品订单ID
     *  delivery_sn [string] 快递单号
     *  memo    [string]   备注
     *  express_id  [int] 快递ID
     *  location_id [int] 门店ID
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
     *  rel_deal_id [int] 来源商品数据ID 
     *  status  [int] 状态
     *  info [string] 消息
     *  
     */
    public function do_delivery(){
            /*初始化*/
            $root = array();
            $account_info = $GLOBALS['account_info'];
            

            /*获取参数*/
            $rel_deal_id = intval($GLOBALS['request']['rel_deal_id']);
            $doi_ids = $GLOBALS['request']['doi_ids'];
            $delivery_sn = strim($GLOBALS['request']['delivery_sn']);
            $memo = strim($GLOBALS['request']['memo']);
            $express_id = intval($GLOBALS['request']['express_id']);
            $location_id = intval($GLOBALS['request']['location_id']);

            $root['rel_deal_id'] = $rel_deal_id;

            /*业务逻辑*/
            $root['biz_user_status'] = $account_info?1:0;
            if (empty($account_info)){
                output($root,0,"商户未登录");
            }
            
            //返回商户权限
            if(!check_module_auth("goodso")){
                $root['is_auth'] = 0;
                output($root,0,"没有操作权限");
            }else{
                $root['is_auth'] = 1;
            }
            
            $supplier_id = intval($account_info['supplier_id']);
            require_once APP_ROOT_PATH."system/model/deal_order.php";
            $order_item_table_name = get_supplier_order_item_table_name($supplier_id);
            $order_table_name = get_supplier_order_table_name($supplier_id);
            	
           
            $order_ids = $GLOBALS['db']->getAll("select order_id from ".$order_item_table_name." where id in (".implode(",", $doi_ids).") ");

            $order_id = $order_ids[0]['order_id'];
            $is_notorder_id =0;
            foreach ($order_ids as $k=>$v){
                if($k>0 && $v['order_id'] != $order_id){
                    $is_notorder_id = 1;
                }
            }

            
            if ($is_notorder_id){
                output($root,0,"提交数据有误");
            }
            
            $order_info = $GLOBALS['db']->getRow("select * from ".$order_table_name." where id = '".$order_id."'");
            
            $items = $GLOBALS['db']->getAll("select distinct(doi.id),doi.* from ".$order_item_table_name." as doi left join ".DB_PREFIX."deal_location_link as l on doi.deal_id = l.deal_id where doi.id in (".implode(",", $doi_ids).") and l.location_id in (".implode(",",$account_info['location_ids']).")");
            
//             $root['sql'] = "select distinct(doi.id),doi.* from ".$order_item_table_name." as doi left join ".DB_PREFIX."deal_location_link as l on doi.deal_id = l.deal_id where doi.id in (".implode(",", $doi_ids).") and l.location_id in (".implode(",",$account_info['location_ids']).")";
   

            if(count($items) == count($doi_ids))
            {
                foreach ($items as $k=>$v){
                    $id = $v['id'];
                    $rs = make_delivery_notice($order_id,$id,$delivery_sn,$memo,$express_id,$location_id);
                    if($rs)
                    {
                        $GLOBALS['db']->query("update ".DB_PREFIX."deal_order_item set delivery_status = 1 where id = ".$id);
                    }
                    send_delivery_mail($delivery_sn,$v['name'],$order_id);
                    send_delivery_sms($delivery_sn,$v['name'],$order_id);
                }
                
                //开始同步订单的发货状态
                $order_deal_items = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_order_item where order_id = ".$order_id);
                foreach($order_deal_items as $k=>$v)
                {
                    if($v['delivery_status']==5) //无需发货的商品
                    {
                        unset($order_deal_items[$k]);
                    }
                }
                $delivery_deal_items = $order_deal_items;
                foreach($delivery_deal_items as $k=>$v)
                {
                    if($v['delivery_status']==0) //未发货去除
                    {
                        unset($delivery_deal_items[$k]);
                    }
                }
            
                	
                if(count($delivery_deal_items)==0&&count($order_deal_items)!=0)
                {
                    $GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 0,update_time = '".NOW_TIME."' where id = ".$order_id); //未发货
                }
                elseif(count($delivery_deal_items)>0&&count($order_deal_items)!=0&&count($delivery_deal_items)<count($order_deal_items))
                {
                    $GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 1,update_time = '".NOW_TIME."' where id = ".$order_id); //部分发
                }
                else
                {
                    $GLOBALS['db']->query("update ".DB_PREFIX."deal_order set delivery_status = 2,update_time = '".NOW_TIME."' where id = ".$order_id); //全部发
                }
            
                
                update_order_cache($order_id);
                distribute_order($order_id);
                	
                foreach ($items as $k=>$v){
                    order_log($v['name']."发货了，发货单号：".$delivery_sn, $order_id);
                    send_msg($order_info['user_id'], $v['name']."发货了，发货单号：".$delivery_sn, "orderitem", $v['id']);
                    //发微信通知
                    $weixin_conf = load_auto_cache("weixin_conf");
                    if($weixin_conf['platform_status']==1)
                    {
                    	$wx_account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_account where user_id = ".$supplier_id);
                    	$express_name = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."express where id = ".$express_id);
                    	send_wx_msg("OPENTM200565259", $order_info['user_id'], $wx_account,array("order_id"=>$order_id,"order_sn"=>$order_info['order_sn'],"company_name"=>$express_name,"delivery_sn"=>$delivery_sn,"order_item_id"=>$v['id']));
                    }
                }
                
                output($root,1,"发货成功");
            }
            else
            {
                output($root,0,"非法的数据");
            }
            
    }
    
    
}
?>

