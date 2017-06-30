<?php
/**
 * 商户中心团购管理
 * @author Administrator
 *
 */
require APP_ROOT_PATH . 'app/Lib/page.php';

class dealModule extends BizBaseModule
{

    function __construct()
    {
        parent::__construct();
        global_run();
        $this->check_auth();
    }

    public function index()
    {
        /* 基本参数初始化 */
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $account_id = $account_info['id'];
        
        /* 获取参数 */
        
        /* 业务逻辑部分 */
        $conditions .= " where d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 0 and d.supplier_id = ".$supplier_id; // 查询条件
        

        // 需要连表操作 只查询支持门店的
        $join = " left join " . DB_PREFIX . "deal_location_link dll on dll.deal_id = d.id ";
        $conditions .= " and dll.location_id in(" . implode(",", $account_info['location_ids']) . ") ";
        
        
        $sql_count = " select count(distinct(d.id)) from " . DB_PREFIX . "deal d";
        $sql = " select distinct(d.id),d.name,d.sub_name,d.begin_time,d.end_time,time_status from " . DB_PREFIX . "deal d";
        
        /* 分页 */
        $page_size = 10;
        $page = intval($_REQUEST['p']);
        if ($page == 0)
            $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;
        
        $total = $GLOBALS['db']->getOne($sql_count . $join . $conditions);
        $page = new Page($total, $page_size); // 初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);

        $list = $GLOBALS['db']->getAll($sql . $join . $conditions . " order by d.id desc limit " . $limit);
        
        foreach ($list as $k => $v) {
            $list[$k]['begin_time'] = $v['begin_time'] != 0 ? to_date($v['begin_time']) : "不限";
            $list[$k]['end_time'] = $v['end_time'] != 0 ? to_date($v['end_time']) : "不限";
            $list[$k]['images'] = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "deal_gallery where deal_id=" . $v['id'] . " order by sort desc");
            $list[$k]['edit_url'] = url("biz", "deal#edit", array(
                "id" => $v['id'],
                "edit_type" =>1
            ));
            
            $list[$k]['preview_url'] = url("index","preview#deal",array("id"=>$v['id'],"type"=>0));
            if(defined("FX_LEVEL"))
            $list[$k]['fx_count'] =$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user_deal where id=".$v['id']);
        }
        
        /* 数据 */
        $GLOBALS['tmpl']->assign("list", $list);
        $GLOBALS['tmpl']->assign("publish_btn_url", url("biz", "deal#publish"));
        $GLOBALS['tmpl']->assign("form_url", url("biz", "deal#index"));
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "deal"));
        $GLOBALS['tmpl']->assign("index_url", url("biz", "deal#index"));
        $GLOBALS['tmpl']->assign("no_online_index_url", url("biz", "deal#no_online_index"));

        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "团购项目管理");
        $GLOBALS['tmpl']->display("pages/project/index.html");
    }

    /**
     * 未发布的列表
     */
    public function no_online_index()
    {
        /* 基本参数初始化 */
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $account_id = $account_info['id'];
        
        /* 获取参数 */
	    if(isset($_REQUEST['filter_admin_check']) && $_REQUEST['filter_admin_check']!=''){
	        $filter_admin_check = intval($_REQUEST['filter_admin_check']);
	    }else{
	        $filter_admin_check = -1;
	    }

        /* 业务逻辑部分 */
        $conditions .= " where d.is_effect = 1 and d.is_delete = 0 and d.is_shop = 0 "; // 查询条件
        
        if ($account_info['is_main'] == 1) { // 总管理员
            $conditions .= " and d.supplier_id = " . $supplier_id;
        } else { // 子账户操作
               // 只查询支持门店的
            $conditions .= " and d.account_id =" . $account_id;
        }
        
        if ($filter_admin_check >= 0) {
            $conditions .= " and admin_check_status = " . $filter_admin_check;
        }

        $sql_count = " select count(*) from " . DB_PREFIX . "deal_submit d";
        $sql = " select d.id,d.name,d.sub_name,d.begin_time,d.end_time,d.biz_apply_status,d.admin_check_status,d.cache_focus_imgs,d.deal_id from " . DB_PREFIX . "deal_submit d";
        
        /* 分页 */
        $page_size = 10;
        $page = intval($_REQUEST['p']);
        if ($page == 0)
            $page = 1;
        $limit = (($page - 1) * $page_size) . "," . $page_size;
        
        $total = $GLOBALS['db']->getOne($sql_count . $conditions);
        $page = new Page($total, $page_size); // 初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);
//         echo $sql . $conditions . " order by id desc limit " . $limit;exit;
        $list = $GLOBALS['db']->getAll($sql . $conditions . " order by id desc limit " . $limit);
        
        foreach ($list as $k => $v) {
            $list[$k]['images'] = unserialize($v['cache_focus_imgs']);
            $list[$k]['edit_url'] = url("biz", "deal#edit", array(
                "id" => $v['id'],
                "edit_type" =>2
            ));
            $list[$k]['preview_url'] = url("index","preview#deal",array("id"=>$v['id'],"type"=>1));
        }
        
        /* 数据 */
        $GLOBALS['tmpl']->assign("filter_admin_check", $filter_admin_check);
        $GLOBALS['tmpl']->assign("list", $list);
        $GLOBALS['tmpl']->assign("publish_btn_url", url("biz", "deal#publish"));
        $GLOBALS['tmpl']->assign("form_url", url("biz", "deal#no_online_index"));
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "deal"));
        $GLOBALS['tmpl']->assign("index_url", url("biz", "deal#index"));
        $GLOBALS['tmpl']->assign("no_online_index_url", url("biz", "deal#no_online_index"));
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("page_title", "团购项目管理");
        $GLOBALS['tmpl']->display("pages/project/index.html");
    }

    /**
     * 团购发布
     */
    public function publish()
    {
        /* 基本参数初始化 */
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        /* 业务逻辑 */
        
        // 支持门店
        $location_infos = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
        
        // 标签数据
        for ($i = 1; $i < 10; $i ++) {
            if ($i!=0 && $i!= 7)
                $tags_html .= '<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_tag[]" value="' . $i . '" />' . lang("DEAL_TAG_" . $i) . '</label>';
        }
        // 商品类型
        $goods_type_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "goods_type");
        
        
        /* 数据 */
        $GLOBALS['tmpl']->assign("location_infos", $location_infos); // 支持门店
        $GLOBALS['tmpl']->assign("tags_html", $tags_html); // 标签数据
        $GLOBALS['tmpl']->assign("goods_type_list", $goods_type_list); // 商品分类
        
        //是否开启自动审核
        $GLOBALS['tmpl']->assign('allow_publish_verify',intval($GLOBALS['db']->getOne("select allow_publish_verify from ".DB_PREFIX."supplier where id=".$supplier_id)));
        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "deal"));
        $GLOBALS['tmpl']->assign("page_title", "团购项目发布");
        $GLOBALS['tmpl']->display("pages/project/deal_publish.html");
    }

    public function edit()
    {
        /* 基本参数初始化 */
        init_app_page();
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $account_id = $account_info['id'];
        
        $id = intval($_REQUEST['id']);
        $edit_type = intval($_REQUEST['edit_type']);
        
        if($edit_type == 1 && $id>0){ //判断是否有存在修改
            $deal_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_submit where deal_id = ".$id." and supplier_id = ".$supplier_id);
            if($deal_submit_info && $deal_submit_info['admin_check_status']==0){
                showBizErr("已经存在申请操作，请先删除避免重复申请",0,url("biz","deal#index"));
                exit;
            }
        }
        
        /* 业务逻辑 */
        
        if ($edit_type == 1) {//管理员发布
            /*********************************
             * 取真正的商品、团购数据表数据
             ********************************/
            $deal_info = $GLOBALS['db']->getRow("select d.* from " . DB_PREFIX . "deal d left join " . DB_PREFIX . "deal_location_link dll on dll.deal_id = d.id  where d.is_effect = 1 and d.is_delete = 0 and id=".$id." and supplier_id = ".$supplier_id."
                         and dll.location_id in(" . implode(",", $account_info['location_ids']).")");
//             $deal_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "deal where id=" . $id); //仅限开放测试使用
            if (empty($deal_info)) {
                showBizErr("数据不存在或没有操作权限！",0,url("biz","deal#index"));
                exit();
            }
            
            //支持门店 , 门店选中状态
            $location_infos = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
            $curr_location_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_location_link where  deal_id = ".$id);
            foreach($curr_location_list as $k=>$v){
                $curr_locations[] = $v['location_id'];
            }
            
            foreach ($location_infos as $k => $v) {
                if (in_array($v['id'], $curr_locations) ) {
                    $location_infos[$k]['checked'] = 1;
                }
            }

            //关联商品
         	$related_deal_id=$GLOBALS['db']->getOne("select relate_ids from ".DB_PREFIX."relate_goods where good_id = ".$id);
        	$related_deal=$GLOBALS['db']->getAll("select id,name,icon from ".DB_PREFIX."deal where id in(".$related_deal_id.")");
        	
        	$GLOBALS['tmpl']->assign("related_deal",$related_deal);
        	$GLOBALS['tmpl']->assign("related_deal_id",$related_deal_id);
            
            // 图集
            $img_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id=".$id." order by sort asc");
            
            $imgs = array();
            foreach($img_list as $k=>$v)
            {
                $focus_imgs[$v['sort']] = $v['img'];
            }

            // 输出规格库存的配置
            $attr_stock = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."attr_stock where deal_id=".$id." order by id asc");
            $go_list_url = url("biz","deal#index");
        } elseif($edit_type == 2) {//商户提交
            /**********************************
             * 取商户提交数据表
             *********************************/
            $deal_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "deal_submit where id=" . $id." and supplier_id = ".$supplier_id);
            
            if (empty($deal_info)) {
                showBizErr("数据不存在或没有操作权限！",0,url("biz","deal#no_online_index"));
                exit();
            }
            // 支持门店 , 门店选中状态
            $cache_location_id = unserialize($deal_info['cache_location_id']);
            $location_infos = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
            
            foreach ($location_infos as $k => $v) {
                if (in_array($v['id'], $cache_location_id)) {
                    $location_infos[$k]['checked'] = 1;
                }
            }


            //关联商品
         	$related_deal_id=unserialize($deal_info['cache_relate']);
         	$related_deal_id=$related_deal_id['relate_ids'];
        	$related_deal=$GLOBALS['db']->getAll("select id,name,icon from ".DB_PREFIX."deal where id in(".$related_deal_id.")");
        	$GLOBALS['tmpl']->assign("related_deal",$related_deal);
        	$GLOBALS['tmpl']->assign("related_deal_id",$related_deal_id);                
            
            
            // 图集
            $focus_imgs = unserialize($deal_info['cache_focus_imgs']);
            
            
            // 输出规格库存的配置
            $attr_stock = unserialize($deal_info['cache_attr_stock']);
            $go_list_url = url("biz","deal#no_online_index");
        }
        
        //转换头部SCRIPT 用的 库存 JSON
        $attr_cfg_json = "{";
        $attr_stock_json = "{";
        
        foreach ($attr_stock as $k => $v) {
            $attr_cfg_json .= $k . ":" . "{";
            $attr_stock_json .= $k . ":" . "{";
            foreach ($v as $key => $vvv) {
                if ($key != 'attr_cfg')
                    $attr_stock_json .= "\"" . $key . "\":" . "\"" . $vvv . "\",";
            }
            $attr_stock_json = substr($attr_stock_json, 0, - 1);
            $attr_stock_json .= "},";
            
            $attr_cfg_data = unserialize($v['attr_cfg']);
            foreach ($attr_cfg_data as $attr_id => $vv) {
                $attr_cfg_json .= $attr_id . ":" . "\"" . $vv . "\",";
            }
            $attr_cfg_json = substr($attr_cfg_json, 0, - 1);
            $attr_cfg_json .= "},";
        }
        if ($attr_stock) {
            $attr_cfg_json = substr($attr_cfg_json, 0, - 1);
            $attr_stock_json = substr($attr_stock_json, 0, - 1);
        }
        
        $attr_cfg_json .= "}";
        $attr_stock_json .= "}";
        
        /*******************************************
         * 通用数据部分
         ********************************************/

        // 商品类型
        $goods_type_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "goods_type");
        foreach ($goods_type_list as $k => $v) {
            if ($v['id'] == $deal_info['deal_goods_type']) {
                $goods_type_list[$k]['selected'] = 1;
                break;
            }
        }
        
        // 标签数据
        for ($i = 0; $i < 10; $i ++) {
            if ($i!=0 && $i != 7) {
                if (($deal_info['deal_tag'] & pow(2, $i)) == pow(2, $i))
                    $tags_html .= '<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_tag[]" value="' . $i . '" checked="checked"/>' . lang("DEAL_TAG_" . $i) . '</label>';
                else
                    $tags_html .= '<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_tag[]" value="' . $i . '" />' . lang("DEAL_TAG_" . $i) . '</label>';
            }
        }
        
        // 时间格式化
        $deal_info['begin_time'] = $deal_info['begin_time']>0?to_date($deal_info['begin_time'], "Y-m-d H:i"):'';
        $deal_info['end_time'] = $deal_info['end_time']>0?to_date($deal_info['end_time'], "Y-m-d H:i"):'';
        $deal_info['coupon_begin_time'] = $deal_info['coupon_begin_time']>0?to_date($deal_info['coupon_begin_time'], "Y-m-d H:i"):'';
        $deal_info['coupon_end_time'] = $deal_info['coupon_end_time']>0?to_date($deal_info['coupon_end_time'], "Y-m-d H:i"):'';

        /* 数据 */
        
        $GLOBALS['tmpl']->assign("attr_cfg_json", $attr_cfg_json); // 属性配置
        $GLOBALS['tmpl']->assign("attr_stock_json", $attr_stock_json); // 属性配置
        $GLOBALS['tmpl']->assign("location_infos", $location_infos); // 支持门店
        $GLOBALS['tmpl']->assign("tags_html", $tags_html); // 标签数据
        $GLOBALS['tmpl']->assign("goods_type_list", $goods_type_list); // 商品分类
        $GLOBALS['tmpl']->assign("focus_imgs",$focus_imgs); // 图集数组
        $GLOBALS['tmpl']->assign("deal_info", $deal_info); // 商品所有数据
        $GLOBALS['tmpl']->assign("edit_type", $edit_type); // 请求数据类型
        $GLOBALS['tmpl']->assign("go_list_url", $go_list_url); // 返回列表连接
        
        //是否开启自动审核
        $GLOBALS['tmpl']->assign('allow_publish_verify',intval($GLOBALS['db']->getOne("select allow_publish_verify from ".DB_PREFIX."supplier where id=".$supplier_id)));
        
        /* 系统默认 */
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "deal"));
        $GLOBALS['tmpl']->assign("page_title", "团购项目编辑");
        $GLOBALS['tmpl']->display("pages/project/deal_edit.html");
    }

    public function del()
    {
        /* 基本参数初始化 */
        
        $id = intval($_REQUEST['id']);
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $account_id = $account_info['id'];
        
        /* 业务逻辑 */
        if ($GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "deal_submit where id=" . $id . " and account_id =" . $account_id)) {
            // 存在切用户有权限删除
            $GLOBALS['db']->query("delete from " . DB_PREFIX . "deal_submit where id=" . $id . " and account_id =" . $account_id);
            $data['status'] = 1;
            $data['info'] = "删除成功";
        } else {
            $data['status'] = 0;
            $data['info'] = "数据不存在货没有管理权限";
        }
        ajax_return($data);
    }

    /**
     * 保存团购产品数据
     */
    public function do_save_publish()
    {
        $account_info = $GLOBALS['account_info'];
        $supplier_id = $account_info['supplier_id'];
        $account_id = $account_info['id'];
        
        $edit_type = intval($_REQUEST['edit_type']);
        $id = intval($_REQUEST['id']);
        
        //是否开启自动审核
        $supplier_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id=".$supplier_id);
        if($supplier_data['allow_publish_verify']){
            $allow_publish_verify = $supplier_data['allow_publish_verify'];
            $publish_verify_balance = $supplier_data['publish_verify_balance'];
            require_once APP_ROOT_PATH.'system/model/deal.php';
        }
 
        if($edit_type == 1 && $id>0){ //判断是否有存在修改
            $deal_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_submit where deal_id = ".$id." and supplier_id = ".$supplier_id);
            if($deal_submit_info && $deal_submit_info['admin_check_status']==0){
                $result['status'] = 0;
                $result['info'] = "已经存在申请操作，请先删除避免重复申请";
                ajax_return($result);
                exit;
            }else{
                $deal_info = $GLOBALS['db']->getRow("select d.* from " . DB_PREFIX . "deal d 
                        left join " . DB_PREFIX . "deal_location_link dll on dll.deal_id = d.id  
                            where d.is_effect = 1 and d.is_delete = 0 and id=".$id." and supplier_id = ".$supplier_id."
                            and dll.location_id in(" . implode(",", $account_info['location_ids']).")");

                if(empty($deal_info)){
                    $result['status'] = 0;
                    $result['info'] = "数据不存在或没有权限操作该数据";
                    ajax_return($result);
                    exit;
                }
                $new_data = $deal_info;
                $new_data['deal_id'] = $deal_info['id'];
                unset($new_data['id']);
                $new_data['supplier_id'] = $supplier_id;
                $new_data['account_id'] = $account_id;
                
                //如果数据已经有存在，通过审核的数据，先清除掉在进行插入更新操作
                if($deal_submit_info && $deal_submit_info['admin_check_status']!=0){
                    $GLOBALS['db']->query("delete from ".DB_PREFIX."deal_submit where id=".$deal_submit_info['id']);
                }
              
                //先建立数据
                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",$new_data);
                $deal_submit_id = $GLOBALS['db']->insert_id();
            }
        
        }
        
        
        
        // 白名单过滤
        require_once APP_ROOT_PATH . 'system/model/no_xss.php';
        
        $this->check_deal_publish_data($_REQUEST);
        
        $data['supplier_id'] = $supplier_id; // 所属商户
        $data['account_id'] = $account_id;
        $data['name'] = strim($_REQUEST['name']); // 团购名称
        $data['sub_name'] = strim($_REQUEST['sub_name']); // 简短名称
        $data['brief'] = strim($_REQUEST['brief']); // 描述
        $data['city_id'] = intval($_REQUEST['city_id']); // 城市
        $data['cate_id'] = intval($_REQUEST['cate_id']); // 分类
        
        $data['description'] = btrim(no_xss($_REQUEST['description']));
        $data['notes'] = btrim(no_xss($_REQUEST['notes']));
        $data['begin_time'] = strim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time'], "Y-m-d H:i");
        $data['end_time'] = strim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time'], "Y-m-d H:i");
        $data['coupon_begin_time'] = strim($_REQUEST['coupon_begin_time']) == '' ? 0 : to_timespan($_REQUEST['coupon_begin_time'], "Y-m-d H:i"); // 团购券生效时间:
        $data['coupon_end_time'] = strim($_REQUEST['coupon_end_time']) == '' ? 0 : to_timespan($_REQUEST['coupon_end_time'], "Y-m-d H:i"); // 团购券到期时间
        
        $data['max_bought'] = intval($_REQUEST['max_bought']); // 库存
        $data['user_min_bought'] = intval($_REQUEST['user_min_bought']); // 用户最小购买
        $data['user_max_bought'] = intval($_REQUEST['user_max_bought']); // 用户最大购买
        $data['origin_price'] =floatval($_REQUEST['origin_price']); // 原价
        $data['current_price'] = floatval($_REQUEST['current_price']); // 团购价
        
        //开启自动审核
        if($allow_publish_verify){
            $data['balance_price'] = $data['current_price']*$publish_verify_balance; // 商户结算价
        }else{
            $data['balance_price'] = floatval($_REQUEST['balance_price']); // 商户结算价
        }
        
        $data['deal_type'] = intval($_REQUEST['deal_type']); // 发券类型
        $data['coupon_time_type'] = intval($_REQUEST['coupon_time_type']); // 团购券有效期类型
        if ($data['coupon_time_type'] == 1) {
            $data['coupon_begin_time'] = 0;
            $data['coupon_end_time'] = 0;
            $data['coupon_day'] = intval($_REQUEST['coupon_day']); // 团购券有效天数
        } else {
            $data['coupon_day'] = 0;
        }
        
        $data['deal_goods_type'] = intval($_REQUEST['deal_goods_type']); // 团购券商品类型
        $data['create_time'] = NOW_TIME;
        $data['update_time'] = NOW_TIME;
        $data['is_effect'] = 1;
        $data['is_delete '] = 0;
        if ($_REQUEST['deal_attr'] && count($_REQUEST['deal_attr']) > 0) {
            $data['multi_attr'] = 1;
        } else {
            $data['multi_attr'] = 0;
        }
        
        $deal_tags = $_REQUEST['deal_tag']; // 标签
        $deal_tag = 0;
        foreach ($deal_tags as $t) {
            $t2 = pow(2, $t);
            // 根据tag计算免预约
            if ($t == 1) {
                $data['auto_order'] = 1;
            }
            $deal_tag = $deal_tag | $t2;
        }
        $data['deal_tag'] = $deal_tag;
        foreach ($deal_tags as $t) {
            if ($t == 0) {
                $data['is_lottery'] = 1;
            }
            // 根据tag计算免预约
            if ($t == 1) {
                $data['auto_order'] = 1;
            }
            // 随时退
            if ($t == 6) {
                $data['any_refund'] = 1;
            }
            // 过期退
            if ($t == 5) {
                $data['expire_refund'] = 1;
            }
        }
        if ($data['any_refund'] == 1 || $data['expire_refund'] == 1) {
            $data['is_refund'] = 1;
        }
        
        $deal_cate_type_id = $_REQUEST['deal_cate_type_id']; // 子分类
        $location_id = $_REQUEST['location_id']; // 支持门店
        
        $icon = strim($_REQUEST['img_icon']); // 缩略图
        $focus_imgs = $_REQUEST['focus_imgs']; // 图集
        
        if($id > 0){ //更新操作需要替换图片地址
            $icon = replace_domain_to_public($icon);

            foreach ($focus_imgs as $k => $v) {
                $v = replace_domain_to_public($v);;
                $focus_imgs[$k] = $v;
            }
        }
        $data['icon'] = $icon;
        // 主图
        foreach ($focus_imgs as $k => $v) {
            if ($v != '') {
                $data['img'] = $v;
                break;
            }
        }

        $data['cache_deal_cate_type_id'] = serialize($deal_cate_type_id);
        $data['cache_location_id'] = serialize($location_id);
        $data['cache_focus_imgs'] = serialize($focus_imgs);
        
        // 开始处理属性
        $deal_attr = $_REQUEST['deal_attr'];
        $deal_attr_price = $_REQUEST['deal_attr_price'];
        
        //开启自动审核
        if($allow_publish_verify){
            $deal_add_balance_price = array();
        }else{
            $deal_add_balance_price = $_REQUEST['deal_add_balance_price'];
        }
        
        $deal_attr_stock_hd = $_REQUEST['deal_attr_stock_hd'];
        
        foreach ($deal_attr as $goods_type_attr_id => $arr) {
            foreach ($arr as $k => $v) {
                if ($v != '') {
                    $deal_attr_item['goods_type_attr_id'] = $goods_type_attr_id;
                    $deal_attr_item['name'] = $v;
                    $deal_attr_item['price'] = $deal_attr_price[$goods_type_attr_id][$k];
                    //开启自动审核，结算金额必须根据 比例计算
                    if($allow_publish_verify){
                        $deal_attr_item['add_balance_price'] = $deal_attr_item['price']*$publish_verify_balance;
                    }else{
                        $deal_attr_item['add_balance_price'] = $deal_add_balance_price[$goods_type_attr_id][$k];
                    }
                    
                    $deal_attr_item['is_checked'] = intval($deal_attr_stock_hd[$goods_type_attr_id][$k]);
                    
                    $deal_attr_data[] = $deal_attr_item;
                }
            }
        }
        $data['cache_deal_attr'] = serialize($deal_attr_data);
        
        // 开始创建属性库存
        $stock_cfg = $_REQUEST['stock_cfg_num'];
        $attr_cfg = $_REQUEST['stock_attr'];
        $attr_str = $_REQUEST['stock_cfg'];
        foreach ($stock_cfg as $row => $v) {
            $stock_data = array();
            $stock_data['stock_cfg'] = $v;
            $stock_data['attr_str'] = $attr_str[$row];
            $attr_cfg_data = array();
            foreach ($attr_cfg as $attr_id => $cfg) {
                $attr_cfg_data[$attr_id] = $cfg[$row];
            }
            
            $stock_data['attr_cfg'] = serialize($attr_cfg_data);
            $attr_stock[] = $stock_data;
        }
        
        $data['cache_attr_stock'] = serialize($attr_stock);
        
        
        //关联商品
        $related_deal=strim($_REQUEST['related_deal']);
        $relate_array=array();
        $relate_array['is_shop']=1;        
        $relate_array['good_id']=$id;
        $relate_array['relate_ids']=$related_deal;
        $data['cache_relate']=serialize($relate_array);        
        
        
        
        // 管理员状态
        $data['admin_check_status'] = 0; // 待审核
        
        
        if ($id > 0) {
            if($edit_type == 1){
                $id = $deal_submit_id; //上面生成的记录IDs
                $data['biz_apply_status'] = 2; // 修改申请
            }

            $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_submit", $data, "UPDATE", " id=" . $id." and supplier_id = ".$supplier_id);
            if($allow_publish_verify){
                deal_auto_publish($id);
                $result['info'] = "修改成功";
            }else{
                $result['info'] = "修改成功，等待管理员审核";
            }
                
            $result['status'] = 1;
            
            $result['jump'] = url("biz", "deal#no_online_index");
        } else {
            $data['biz_apply_status'] = 1; // 新增申请
            $list = $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_submit", $data);
            $id = $GLOBALS['db']->insert_id();
            if ($list) {
                if($allow_publish_verify){
                    deal_auto_publish($id);
                    $result['info'] = "提交成功";
                }else{
                    $result['info'] = "提交成功，等待管理员审核";
                }
                $result['status'] = 1;
                $result['jump'] = url("biz", "deal#no_online_index");
            }
        }
        
        
        ajax_return($result);
    }
    
    /**
     * 下架操作
     */
    public function down_line(){
        $account_info = $GLOBALS['account_info'];
        $account_id = $account_info['id'];
        $supplier_id = $account_info['supplier_id'];
        
        $id = intval($_REQUEST['id']);
        //是否开启自动审核
        $supplier_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier where id=".$supplier_id);
        if($supplier_data['allow_publish_verify']){
            $allow_publish_verify = $supplier_data['allow_publish_verify'];
            $publish_verify_balance = $supplier_data['publish_verify_balance'];
            require_once APP_ROOT_PATH.'system/model/deal.php';
        }
        if($id>0){
            //商户提交数据
            $deal_submit_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_submit where deal_id =".$id." and supplier_id=".$supplier_id);
            //真实团购数据
            $deal_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal d left join " . DB_PREFIX . "deal_location_link dll on dll.deal_id = d.id where id=".$id." and dll.location_id in(".implode(",", $GLOBALS['account_info']['location_ids']).")");
            if($deal_info){
                //数据导入 deal_submit表
                $data = array();      
                $data['admin_check_status'] = 0;
                $data['biz_apply_status'] = 3;
                $data['supplier_id'] = $supplier_id;
                $data['account_id'] = $account_id;
                $data['is_shop'] = 0;
                $data['is_effect'] = 1;
                $data['is_delete'] = 0;
                
                if($deal_submit_info){ //存在数据
                    if($allow_publish_verify){ //自动审核
                        if($deal_submit_info['biz_apply_status']!=3){ //更新状态
                            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",$data,"UPDATE","id=".$deal_submit_info['id']);
                        }
                        deal_auto_downline($deal_submit_info['id']);
                        $result['status'] = 1;
                        $result['info'] = "修改成功";
                    }else{
                        if($deal_submit_info['biz_apply_status']!=3){ //更新状态
                            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",$data,"UPDATE","id=".$deal_submit_info['id']);
                            $result['status'] = 1;
                            $result['info'] = "修改成功，等待管理员审核";
                        }elseif($deal_submit_info['biz_apply_status']==3){
                            $result['status'] = 0;
                            $result['info'] = "下架待审核中，请勿重复申请";
                        }
                    }
                    
                }else{ //增加新数据

                    $data['deal_id'] = $deal_info['id'];
                    $data['name'] = $deal_info['name'];
                    $data['cate_id'] = $deal_info['cate_id'];
                    $data['city_id'] = $deal_info['city_id'];
                    $data['create_time'] = NOW_TIME;
                    // 图集
                    $img_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_gallery where deal_id=".$id." order by sort asc");
                    
                    $imgs = array();
                    foreach($img_list as $k=>$v)
                    {
                        $focus_imgs[$v['sort']] = $v['img'];
                    }
                    
                    $data['cache_focus_imgs'] = serialize($focus_imgs);
                    $GLOBALS['db']->autoExecute(DB_PREFIX."deal_submit",$data);
                    $id = $GLOBALS['db']->insert_id();
                    if($allow_publish_verify){ //自动审核
                        deal_auto_downline($id);
                        $result['status'] = 1;
                        $result['info'] = "下架申请成功";
                    }else{
                        $result['status'] = 1;
                        $result['info'] = "下架申请成功等待管理员审核";
                    }
                    
                }
                
            }else{
                $result['status'] = 0;
                $result['info'] = "数据不存在或权限不足";
                
            }
        }else{
            $result['status'] = 0;
            $result['info'] = "请正确提交数据";
        }
        ajax_return($result);
    }
    
    /**
     * 加载子分类
     */
    public function load_sub_cate(){
        $cate_id = intval($_REQUEST['cate_id']);
        $edit_type = intval($_REQUEST['edit_type']);
        $id = intval($_REQUEST['id']);
    
        $sub_cate_list = $GLOBALS['db']->getAll("select c.* from ".DB_PREFIX."deal_cate_type as c left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = c.id where l.cate_id = ".$cate_id);
        if($edit_type == 1){ //管理员添加数据
            $sub_cate_arr_data = $GLOBALS['db']->getAll("select deal_cate_type_id from ".DB_PREFIX."deal_cate_type_deal_link where deal_id = ".$id);
            foreach ($sub_cate_arr_data as $k=>$v){
                $sub_cate_arr[] = $v['deal_cate_type_id'];
            }
    
        }elseif ($edit_type == 2){//商户提交数据
            $sub_cate_arr = unserialize($GLOBALS['db']->getOne("select cache_deal_cate_type_id from ".DB_PREFIX."deal_submit where id=".$id));  //序列化的字段  
        }
        //处理选择状态
        foreach ($sub_cate_list as $k=>$v){
            if(in_array($v['id'], $sub_cate_arr)){
                $sub_cate_list[$k]['checked'] =1 ;
            }
        }
    
    
        $html = '';
        if($sub_cate_list){
            $result['status'] = 1;
            foreach($sub_cate_list as $k=>$v){
                if($v['checked']){
                    $html.='<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_cate_type_id[]" value="'.$v['id'].'" checked="checked"/>'.$v['name'].'</label>';
                }else{
                    $html.='<label class="ui-checkbox" rel="common_cbo"><input type="checkbox" name="deal_cate_type_id[]" value="'.$v['id'].'" />'.$v['name'].'</label>';
                }
            }
    
        }else
            $result['status'] = 0;
    
        $result['html'] = $html;
        ajax_return($result);
    }

    /**
     * 验证提交的 团购商品数据是否符合
     * 
     * @param unknown $data            
     */
    function check_deal_publish_data($data)
    {
        $is_err = 0;
        if (strim($data['name']) == '' && $is_err == 0) {
            $result['status'] = 0;
            $result['info'] = '团购名称不能为空！';
            $is_err = 1;
        }
        if ($is_err == 0 && strim($data['sub_name']) == '') {
            $result['status'] = 0;
            $result['info'] = '简短名称不能为空！';
            $is_err = 1;
        }
        if ($is_err == 0 && intval($data['city_id']) == 0) {
            $result['status'] = 0;
            $result['info'] = '请选择城市！';
            $is_err = 1;
        }
        if ($is_err == 0 && intval($data['cate_id']) == 0) {
            $result['status'] = 0;
            $result['info'] = '请选择分类！';
            $is_err = 1;
        }
        if ($is_err == 0 && count($data['location_id']) <= 0) {
            $result['status'] = 0;
            $result['info'] = '至少支持一家门店！';
            $is_err = 1;
        }
        if ($is_err == 0 && strim($data['img_icon']) == '') {
            $result['status'] = 0;
            $result['info'] = '请上传团购缩略图！';
            $is_err = 1;
        }
        if ($is_err == 0 && count($data['focus_imgs']) <= 0) {
            $result['status'] = 0;
            $result['info'] = '至少上传一张图集！';
            $is_err = 1;
        }

        if ($is_err == 1) {
            $result['jump'] = '';
            ajax_return($result);
        }
    }
    
    

    /**
     * 增加商品分类
     */
    public function load_add_goods_type_weebox()
    {
        $data['html'] = $GLOBALS['tmpl']->fetch("pages/project/deal_add_goods_type_weebox.html");
        ajax_return($data);
    }

    public function do_save_goods_type()
    {
        $account_info = $GLOBALS['account_info'];
        
        $result['status'] = 0;
        $result['info'] = '';
        $result['jump'] = '';
        
        $goods_type_name = strim($_REQUEST['goods_type_name']);
        $goods_attr_arr = $_REQUEST['goods_attr'];
        // 去重复
        $goods_attr_arr = array_unique($goods_attr_arr);
        foreach ($goods_attr_arr as $k => $v) {
            if (strim($v)) {
                $attr_arr[] = $v;
            }
        }
        $goods_attr_arr = $attr_arr;
        $supplier_id = $account_info['supplier_id'];
        // 存在数据
        if ($goods_type_name && $attr_arr) {
            /* 保存分类 */
            $GLOBALS['db']->autoExecute(DB_PREFIX . "goods_type", array(
                "name" => $goods_type_name,
                "supplier_id" => $supplier_id
            ));
            $goods_type_id = $GLOBALS['db']->insert_id();
            if ($goods_type_id) {
                foreach ($goods_attr_arr as $k => $v) {
                    $data = array();
                    $data['name'] = $v;
                    $data['input_type'] = 0;
                    $data['goods_type_id'] = $goods_type_id;
                    $data['supplier_id'] = $supplier_id;
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "goods_type_attr", $data);
                }
                $result['status'] = 1;
            } else {
                $result['status'] = 0;
                $result['info'] = '执行失败请稍后再试';
                $result['jump'] = '';
            }
        } else {
            $result['status'] = 0;
            $result['info'] = '请正确填写数据';
            $result['jump'] = '';
        }
        ajax_return($result);
    }
}



?>