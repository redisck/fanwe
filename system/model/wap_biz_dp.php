<?php 
function wap_biz_get_dp_list($limit,$type,$id,$is_bad='')
{
    /*参数列表*/
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

    $where_str = '';
    if($is_bad==1){
        $where_str = " point = 1 ";
    }
    
    /*获点评数据*/
    $message_re = get_dp_list($limit,$param=array("deal_id"=>$deal_id,"youhui_id"=>$youhui_id,"event_id"=>$event_id,"location_id"=>$location_id,"tag"=>""),$where_str,"");
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
    $root['count']=$count;


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
    
    $root['data_id']=$id;

    return $root;
}
?>