<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

/**
 * 我的红包接口
 * @author jobin.lin
 *
 */
class uc_ecvModule extends MainBaseModule
{
    
    /**
     * 我的红包
     * 输入：
     * n_valid: int 是否失效， 0未失效，1已失效
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * [data] => Array
        (
            [0] => Array
                (
                    [id] => 7   ：int 红包ID
                    [use_status] => 0   ：int 红包是否已经领取的状态
                    [datetime] =>  不限时      ：string 红包到期时间显示内容
                    [name] => 简单换       ：string 红包名称 
                    [money] => 15   ：红包金额
                )
         )
        [user_avatar] => http://localhost/o2onew/public/avatar/000/00/00/71virtual_avatar_big.jpg  ：string 用户头像
        [ecv_count] => 22   ：int 红包个数
        [ecv_total] => 1482 ：int 红包总金额

        [page] => Array
        (
            [page] => 1
            [page_total] => 3
            [page_size] => 10
            [data_total] => 22
        )

     *
     * */
    public function index(){
        $root = array();
        /*参数列表*/
        $n_valid = intval($GLOBALS['request']['n_valid']);
        
        /*业务逻辑*/
        //检查用户,用户密码
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);

        $user_login_status = check_login();
        
        if($user_login_status!=LOGIN_STATUS_LOGINED){
            $root['user_login_status'] = $user_login_status;
        }else{
            $root['user_login_status'] = $user_login_status;
            //分页
            $page = intval($GLOBALS['request']['page']);
            $page=$page==0?1:$page;
            
            $page_size = PAGE_SIZE;
            $limit = (($page-1)*$page_size).",".$page_size;
            

            $condtion = '';
            if($n_valid){
                $condtion = ' and (e.use_limit>0 and e.use_limit = e.use_count) ';
            }else{
                $condtion = ' and (e.use_limit=0 or e.use_limit > e.use_count) ';
            }
            
            $sql = "select * from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.user_id = ".$user_id.$condtion." order by e.id desc limit ".$limit;
            $sql_count = "select count(*) from ".DB_PREFIX."ecv e where user_id = ".$user_id.$condtion;
            $list = $GLOBALS['db']->getAll($sql);
            $count = $GLOBALS['db']->getOne($sql_count);
            $money = 0;
            foreach ($list as $k=>$v){
                $temp_arr = array();
                $temp_arr['id'] = $v['id'];
                if($v['use_limit'] == $v['use_count'] && $v['use_limit']>0)
                    $temp_arr['use_status'] = 1;    //0为使用或者还可以,1不可使用，或者已经使用光
                else 
                    $temp_arr['use_status'] = 0;
                
                $time_str = '';
                if($v['begin_time']>0 || $v['end_time']>0){
                    $begin_time = $v['begin_time']>0?to_date($v['begin_time'],'Y-m-d H:i'):'';
                    $end_time = $v['end_time']>0?to_date($v['end_time'],'Y-m-d H:i'):'';
                    if($v['begin_time']>NOW_TIME){
                        $time_str = $begin_time.' 可以使用 ';
                    }else{
                        if($v['end_time']>0)
                            $time_str = $begin_time.' 至 '.$end_time;
                        else
                            $time_str = ' 不限时 ';
                    }
                        
                }else{
                    $time_str = ' 不限时 ';
                }
                $temp_arr['datetime']=$time_str;
                if ($temp_arr['use_status'] == 0){
                    if($v['use_limit']>0)
                        $money+=($v['use_limit']-$v['use_count'])*$v['money'];
                    else 
                        $money+=$v['money'];
                }
                $temp_arr['name'] = $v['name'];
                $temp_arr['money'] = round($v['money'],2);;
                $data_list[] =$temp_arr;
            }
            
            $root['data']=$data_list;
            $root['user_avatar'] = get_abs_img_root(get_muser_avatar($user_id,"big"));
            $root['ecv_count'] = $count;
            $root['ecv_total'] = round($money,2);
            
            $page_total = ceil($count/$page_size);
            $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        };
        $root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
        $root['page_title'].="我的红包";
        
        output($root);
    }
    
    
    /**
     * 我的红包分页载入数据
     * 输入：
     * page: int 分页
     * n_valid: int 是否失效， 0未失效，1已失效
     *
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * [data] => Array
     (
         [0] => Array
         (
             [id] => 7   ：int 红包ID
             [use_status] => 0   ：int 红包是否已经领取的状态
             [datetime] =>  不限时      ：string 红包到期时间显示内容
             [name] => 简单换       ：string 红包名称
             [money] => 15   ：float 红包金额
        )
     )
    
     [page] => Array
     (
         [page] => 1
         [page_total] => 3
         [page_size] => 10
         [data_total] => 22
     )
    
     *
     * */
    public function load_ecv_list(){
        $root = array();
        /*参数列表*/
        $n_valid = intval($GLOBALS['request']['n_valid']);
        
        /*业务逻辑*/
        //检查用户,用户密码
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);
        
        $user_login_status = check_login();
        
        if($user_login_status!=LOGIN_STATUS_LOGINED){
            $root['user_login_status'] = $user_login_status;
        }else{
            $root['user_login_status'] = $user_login_status;
            //分页
            $page = intval($GLOBALS['request']['page']);
            $page=$page==0?1:$page;
            
            $page_size = PAGE_SIZE;
            $limit = (($page-1)*$page_size).",".$page_size;
            
            $condtion = '';
            if($n_valid){
                $condtion = ' and (e.use_limit>0 and e.use_limit = e.use_count) ';
            }else{
                $condtion = ' and (e.use_limit=0 or e.use_limit > e.use_count) ';
            }
            
            $sql = "select * from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.user_id = ".$user_id.$condtion." order by e.id desc limit ".$limit;
            $sql_count = "select count(*) from ".DB_PREFIX."ecv e where user_id = ".$user_id.$condtion;
            
            $list = $GLOBALS['db']->getAll($sql);
            $count = $GLOBALS['db']->getOne($sql_count);
            $money = 0;
            foreach ($list as $k=>$v){
                $temp_arr = array();
                $temp_arr['id'] = $v['id'];
                if($v['use_limit'] == $v['use_count'] && $v['use_limit']>0)
                    $temp_arr['use_status'] = 1;    //0为使用或者还可以,1不可使用，或者已经使用光
                else 
                    $temp_arr['use_status'] = 0;
                
                $time_str = '';
                if($v['begin_time']>0 || $v['end_time']>0){
                    $begin_time = $v['begin_time']>0?to_date($v['begin_time'],'Y-m-d H:i'):'';
                    $end_time = $v['end_time']>0?to_date($v['end_time'],'Y-m-d H:i'):'';
                    if($v['begin_time']>NOW_TIME){
                        $time_str = $begin_time.' 可以使用 ';
                    }else{
                        if($v['end_time']>0)
                            $time_str = $begin_time.' 至 '.$end_time;
                        else
                            $time_str = ' 不限时 ';
                    }
                        
                }
                $temp_arr['datetime']=$time_str;
                if ($temp_arr['use_status'] == 0){
                    if($v['use_limit']>0)
                        $money+=($v['use_limit']-$v['use_count'])*$v['money'];
                    else 
                        $money+=$v['money'];
                }
                $temp_arr['name'] = $v['name'];
                $temp_arr['money'] = round($v['money'],2);;
                $data_list[] =$temp_arr;
            }
            
            $root['data']=$data_list;
            
            $page_total = ceil($count/$page_size);
            $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        };
        output($root);
    }
    
    /**
     * 我的红包兑换页面
     * 输入：
     * 
     *
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * [data] => Array
     (
         [0] => Array
         (
             [id] => 7   ：int 红包ID
             [name] => 简单换       ：string 红包名称
             [money] => 15   ：float 红包金额
             [exchange_score] => 10     :int 兑换所需的积分
         )
     )
    
     [page] => Array
     (
         [page] => 1
         [page_total] => 3
         [page_size] => 10
         [data_total] => 22
     )
    
     *
     * */
    public function exchange(){
        $root = array();
        /*参数列表*/
        
        /*业务逻辑*/
        //检查用户,用户密码
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);
        
        $user_login_status = check_login();
        
        if($user_login_status!=LOGIN_STATUS_LOGINED){
            $root['user_login_status'] = $user_login_status;
        }else{
            $root['user_login_status'] = $user_login_status;
            $root['score'] = $user['score'];
            //分页
            $page = intval($GLOBALS['request']['page']);
            $page=$page==0?1:$page;
        
            $page_size = PAGE_SIZE;
            $limit = (($page-1)*$page_size).",".$page_size;
            $sql = "select * from ".DB_PREFIX."ecv_type where send_type = 1 and (end_time>".NOW_TIME." or end_time = '') order by id desc limit ".$limit;
            $sql_count = "select count(*) from ".DB_PREFIX."ecv_type where send_type = 1 and (end_time>".NOW_TIME." or end_time = '')";
            
            $list = $GLOBALS['db']->getAll($sql);
            $count = $GLOBALS['db']->getOne($sql_count);
            $data_list = array();
            foreach($list as $k=>$v){
                $temp_arr = array();
                $temp_arr['id'] =  $v['id'];
                $temp_arr['name'] =  $v['name'];
                $temp_arr['money'] =  round($v['money'],2);
                $temp_arr['exchange_score'] =  $v['exchange_score'];
                $temp_arr['id'] =  $v['id'];
                $data_list[] = $temp_arr;
            }
            
            $root['data'] = $data_list;
            $page_total = ceil($count/$page_size);
            $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
            
        }
        $root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
        $root['page_title'].="红包兑换";
        output($root);
    }
    
    /**
     * SN红包兑换
     * 输入：
     * sn:string 红包SN
     *
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * status   :int 状态 0失败 1成功
     * info     ：string  消息
     *
     * */
    public function do_snexchange(){
        $root = array();
        /*参数列表*/
        
        /*业务逻辑*/
        //检查用户,用户密码
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);
        
        $user_login_status = check_login();
       
        if($user_login_status!=LOGIN_STATUS_LOGINED){
            $root['user_login_status'] = $user_login_status;
            output($root);
        }else{
            $root['user_login_status'] = $user_login_status;
            
            $sn = strim($GLOBALS['request']['sn']);
            if (empty($sn)){
                output($root,0,'口令不能为空');
            }
            $ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where exchange_sn = '".$sn."'");
            $root['ecv_type'] = $ecv_type;
            if(!$ecv_type)
            {
            	$GLOBALS['db']->query("update ".DB_PREFIX."ecv set user_id = '".$user_id."' where sn = '".$sn."' and user_id = 0");
            	if($GLOBALS['db']->affected_rows())
            	{
            		output($root,1,$GLOBALS['lang']['EXCHANGE_SUCCESS']);
            	}
            	else
                output($root,0,$GLOBALS['lang']['INVALID_VOUCHER']);
            }
            else
            {
                $exchange_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ecv where ecv_type_id = ".$ecv_type['id']." and user_id = ".intval($GLOBALS['user_info']['id']));
                if($ecv_type['exchange_limit']>0&&$exchange_count>=$ecv_type['exchange_limit'])
                {
                    $msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_LIMIT'],$ecv_type['exchange_limit']);
                    output($root,0,$msg);
                }
                else
                {
                    require_once APP_ROOT_PATH."system/libs/voucher.php";
                    $rs = send_voucher($ecv_type['id'],$user_id,1);
               		if($rs>0)
                    {
                        output($root,1,$GLOBALS['lang']['EXCHANGE_SUCCESS']);
                    }
                    else if($rs==-1)
                    {
                    	output($root,0,"您来晚了，红包已领光了!");
                    }
                    else
                    {
                        output($root,0,$GLOBALS['lang']['EXCHANGE_FAILED']);
                    }
                }
            }
        }
        
        output($root);
    }
    
    /**
     * 积分红包兑换
     * 输入：
     * id ：int 红包id
     *
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * status   :int 状态 0失败 1成功
     * info     ：string  消息
     *
     * */
    public function do_exchange(){
        $root = array();
        /*参数列表*/
    
        /*业务逻辑*/
        //检查用户,用户密码
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);
    
        $user_login_status = check_login();
         
        if($user_login_status!=LOGIN_STATUS_LOGINED){
            $root['user_login_status'] = $user_login_status;
            output($root);
        }else{
            $root['user_login_status'] = $user_login_status;
            
            $id = intval($GLOBALS['request']['id']);
		    $ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where id = ".$id);

            if(!$ecv_type)
            {
                output($root,0,$GLOBALS['lang']['INVALID_VOUCHER']);
            }
            else
            {
                $exchange_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ecv where ecv_type_id = ".$id." and user_id = ".$user_id);
    			if($ecv_type['exchange_limit']>0&&$exchange_count>=$ecv_type['exchange_limit'])
    			{
    				$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_LIMIT'],$ecv_type['exchange_limit']);
    				output($root,0,$msg);
    			}
    			elseif($ecv_type['exchange_score']>intval($GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".$user_id)))
    			{
    				output($root,0,$GLOBALS['lang']['INSUFFCIENT_SCORE']);
    			}
    			else
    			{
    				require_once APP_ROOT_PATH."system/libs/voucher.php";
    				$rs = send_voucher($ecv_type['id'],$user_id,1);
    				if($rs>0)
    				{
    					require_once APP_ROOT_PATH."system/model/user.php";
    					$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_USE_SCORE'],$ecv_type['name'],$ecv_type['exchange_score']);
    					modify_account(array('money'=>0,'score'=>"-".$ecv_type['exchange_score']),$user_id,$msg);
    					output($root,1,$GLOBALS['lang']['EXCHANGE_SUCCESS']);
    				}
    				else if($rs == -1)
    				{
    					output($root,0,"您来晚了，红包已领光了!");
    				}
    				else
    				{
    					output($root,0,$GLOBALS['lang']['EXCHANGE_FAILED']);
    				}
    			}
            }
        }
    
        output($root);
    }
    
    /**
     * 我的红包兑换页面分页数
     * 输入：
     *
     *
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * [data] => Array
     (
     [0] => Array
             (
                 [id] => 7   ：int 红包ID
                 [name] => 简单换       ：string 红包名称
                 [money] => 15   ：float 红包金额
                 [exchange_score] => 10     :int 兑换所需的积分
             )
     )
    
     [page] => Array
     (
     [page] => 1
     [page_total] => 3
     [page_size] => 10
     [data_total] => 22
     )
    
     *
     * */
    public function load_ecv_exchange_list(){
        $root = array();
        /*参数列表*/
    
        /*业务逻辑*/
        //检查用户,用户密码
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);
    
        $user_login_status = check_login();
    
        if($user_login_status!=LOGIN_STATUS_LOGINED){
            $root['user_login_status'] = $user_login_status;
        }else{
            $root['user_login_status'] = $user_login_status;
            $root['score'] = $user['score'];
            //分页
            $page = intval($GLOBALS['request']['page']);
            $page=$page==0?1:$page;
        
            $page_size = PAGE_SIZE;
            $limit = (($page-1)*$page_size).",".$page_size;
            $sql = "select * from ".DB_PREFIX."ecv_type where send_type = 1 and (end_time>".NOW_TIME." or end_time = '') order by id desc limit ".$limit;
            $sql_count = "select count(*) from ".DB_PREFIX."ecv_type where send_type = 1 and (end_time>".NOW_TIME." or end_time = '')";
            
            $list = $GLOBALS['db']->getAll($sql);
            $count = $GLOBALS['db']->getOne($sql_count);
            
            foreach($list as $k=>$v){
                $list[$k]['money'] = round($v['money'],2);
            }
            
            $root['data'] = $list;
            $page_total = ceil($count/$page_size);
            $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        }
        output($root);
    }

}

