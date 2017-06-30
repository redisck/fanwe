<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/*以下为动态载入的函数库*/

//动态加载用户提示
function insert_load_user_tip()
{
	//输出未读的消息数
	if($GLOBALS['user_info'])
	{
		$GLOBALS['tmpl']->assign("user_info",$GLOBALS['user_info']);
		//输出签到结果
		$signin_result = es_session::get("signin_result");
		if($signin_result['status'])
		{
			$GLOBALS['tmpl']->assign("signin_result",json_encode($signin_result));
			es_session::delete("signin_result");
		}
	}
	else 
	{

		$m_config = getMConfig();//初始化手机端配置
		if($m_config['wx_appid']&&$m_config['wx_secrit'])
		{
			$GLOBALS['tmpl']->assign("wx_login",true);
		}
	}
	return $GLOBALS['tmpl']->fetch("inc/insert/load_user_tip.html");
}

//动态加载购物车数量
function insert_load_cart_count()
{
	return load_cart_tip();
}

function insert_load_city_name()
{
	return $GLOBALS['city']['name'];
}

//动态获取可同步登录的图片
function insert_get_app_login()
{
	$apis = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."api_login");	
	foreach($apis as $k=>$api)
	{
		$class_name = $api['class_name'];
		if(file_exists(APP_ROOT_PATH."system/api_login/".$class_name."_api.php"))
		{
			require_once APP_ROOT_PATH."system/api_login/".$class_name."_api.php";
			$api_class = $class_name."_api";
			$api_obj = new $api_class($api);
			$url = $api_obj->get_api_url();
			if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']!="NONE")
			{
				$domain = $GLOBALS['distribution_cfg']['OSS_DOMAIN'];
			}
			else
			{
				$domain = SITE_DOMAIN.$GLOBALS['IMG_APP_ROOT'];
			}
			$url = str_replace("./public/",$domain."/public/",$url);
			
			$str .= $url;
		}		
	}
	return $str;

}


/*********************************************************
 *   			商户模块部分实用 insert						 *
 *********************************************************/
/**
 * 商户中心用户面板
 */
function insert_load_biz_user_tip(){
	$GLOBALS['tmpl']->assign("supplier_name",$GLOBALS['db']->getOne("select name from ".DB_PREFIX."supplier where id = '".$GLOBALS['account_info']['supplier_id']."'"));
 	$GLOBALS['tmpl']->assign("account_info",$GLOBALS['account_info']);
 	$GLOBALS['tmpl']->assign("biz_gen_qrcode",gen_qrcode(SITE_DOMAIN.url("biz","downapp"),app_conf("QRCODE_SIZE")));
 	return $GLOBALS['tmpl']->fetch("inc/insert/load_biz_user_tip.html");
}

/**
 * 商户中心分类字段
 */
function insert_cate_id_select($param = array()){
    $cate_id = 0;
    if($param['cate_id']>0){
        $cate_id = $param['cate_id'];
    }
    
    $cate_tree = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_delete = 0");
    $cate_tree = toFormatTree($cate_tree,'name');
    $html = '<select name="cate_id" class="require ui-select filter_select medium">';
    if($cate_id==0){
        $html.= '<option value="0" selected="selected">==请选择分类==</option>';
    }else{
        $html.= '<option value="0" >==请选择分类==</option>';
    }
    
    foreach ($cate_tree as $k=>$cate_item){
        if($cate_id == $cate_item['id']){
            $html.='<option value="'.$cate_item['id'].'" selected="selected" >'.$cate_item['title_show'].'</option>';
        }else{
            $html.='<option value="'.$cate_item['id'].'">'.$cate_item['title_show'].'</option>';
        }
        
    }
    $html.= '</select>';
    return $html;
}

function insert_city_id_select($param = array()){
    if(intval($param['city_id'])>0){
        $city_id = intval($param['city_id']);
    }
    $city_tree = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_city where is_delete = 0");
    if($city_id>0){
        foreach ($city_tree as $k=>$v){
            if($city_id == $v['id']){
                $city_tree[$k]['selected'] = 1;
            }
        }
    }

    $city_tree = toTree($city_tree);
    
    $GLOBALS['tmpl']->assign("city_id",$city_id);
    $GLOBALS['tmpl']->assign("city_tree",$city_tree);
    $html = $GLOBALS['tmpl']->fetch("inc/city_id_select.html");
    return $html;
}

function insert_load_head_history(){

    
    require_once APP_ROOT_PATH.'system/model/deal.php'; 
    $history_list = array();
    
    //浏览历史
    $history_ids = get_view_history("deal");
    if($history_ids)
    {
        $ids_conditioin = " d.id in (".implode(",", $history_ids).") ";    
        $history_deal_list = get_deal_list(2,array(DEAL_ONLINE),array("city_id"=>$GLOBALS['city']['id']),"",$ids_conditioin);
        //重新组装排序		
		foreach($history_ids as $k=>$v)
		{
			foreach($history_deal_list['list'] as $history_item)
			{
				if($history_item['id']==$v)
				{
					$history_list[] = $history_item;
				}
			}
		}
    }
    
    $history_ids = get_view_history("shop");
    if($history_ids)
    {
    	$ids_conditioin = " d.id in (".implode(",", $history_ids).") ";
    	$history_deal_list = get_goods_list(2,array(DEAL_ONLINE),array("city_id"=>0),"",$ids_conditioin);
    	//重新组装排序
    	foreach($history_ids as $k=>$v)
    	{
    		foreach($history_deal_list['list'] as $history_item)
    		{
    			if($history_item['id']==$v)
    			{
    				$history_list[] = $history_item;
    			}
    		}
    	}
    }
    
    
    $GLOBALS['tmpl']->assign("history_list",$history_list);

    $html = $GLOBALS['tmpl']->fetch("inc/insert/load_head_history.html");
    return $html;
    
}

?>