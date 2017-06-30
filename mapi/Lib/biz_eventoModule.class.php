<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class biz_eventoModule extends MainBaseModule
{

    /**
     * 	活动列表接口
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
     * [item] => Array  :array 活动列表
        (
            [0] => Array
                (
                    [id] => 7   int 活动ID
                    [name] => 哟哟在哪里     ：string 活动名称
                    [total_count] => 999    ：活动名额总数
                    [submit_count] => 0     ：活动报名总数
                    [icon] => http://localhost/o2onew/public/attachment/201506/01/10/556bca4936697_168x140.jpg  ：string 活动展示图 84X70
                    [event_end_time] => 0   ：string 活动结束时间
                    [f_event_end_time] =>   ：string 格式化活动结束时间
                )
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
	    if(!check_module_auth("evento")){
	        $root['is_auth'] = 0;
	        output($root,0,"没有操作评价权限");
	    }else{
	        $root['is_auth'] = 1;
	    }

	    //分页
	    $page_size = PAGE_SIZE;
	    $limit = (($page-1)*$page_size).",".$page_size;
	    
	    //查询出商户下所有的活动
	    $sql = 'select e.id,e.name,e.total_count,e.submit_count,e.icon,e.event_end_time from '.DB_PREFIX.'event e left join '.DB_PREFIX.'event_location_link ell on ell.event_id = e.id where e.is_effect =1 and ell.location_id in ('.implode(',', $account_info['location_ids']).') GROUP BY e.id order by e.id desc limit '.$limit;
	    $count_sql = 'select count(distinct e.id) as count from '.DB_PREFIX.'event e left join '.DB_PREFIX.'event_location_link ell on ell.event_id = e.id where e.is_effect =1 and ell.location_id in ('.implode(',', $account_info['location_ids']).')';

	    
	    $result = $GLOBALS['db']->getAll($sql);
	    $count = $GLOBALS['db']->getOne($count_sql);
	    
	    //分页
	    $page_total = ceil($count/$page_size);
	    
	    foreach ($result as $k=>$v){
	        $result[$k]['icon'] = get_abs_img_root(get_spec_image($v['icon'],84,70,1));
	        $result[$k]['f_event_end_time'] = $v['event_end_time']?to_date($v['event_end_time'],"Y-m-d"):"不限时";
	    }

	    $root['item'] = $result?$result:array();
	    $root['page_title'] = "活动列表";
	    $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        output($root);
    }
    
    
    /**
     * 	活动报名列表接口
     *
     * 	 输入:
     *  page    [int] 分页
     *  data_id     [int] 活动编号
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
     *  [event_info] => Array
        (
            [id] => 6      ：int 活动ID
            [name] => 香港游玩      ：string 活动名称
            [event_end_time] => 1435621440      ：string 活动结束时间
            [submit_count] => 0     ：int 活动报名人数
            [supplier_id] => 35     
            [f_event_end_time] => 2015-06-30
        )
     * [item] => Array  :array 活动列表
         (
          [0] => Array
                (
                    [id] => 23  ：int 活动ID
                    [create_time] => 1433197867  ：string   活动创建时间
                    [sn] =>                      ：string 验证码
                    [location_id] => 0  ：int 验证门店ID
                    [confirm_time] => 0     ：string     验证时间
                    [is_verify] => 2        ：int    审核状态 0 未审核，1已审核，2已拒绝
                    [user_name] => fanwe    ：string 用户名
                    [f_create_time] => 2015-06-02   ：string 开始时间
                    [location_name] => -            ：string 门店名称
                )
         )
    
     */
    public function events(){
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
         
        /*获取参数*/
        $data_id =  intval($GLOBALS['request']['data_id']);

        
        $page = intval($GLOBALS['request']['page']); //分页
        $page=$page==0?1:$page;
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
         
        //返回商户权限
        if(!check_module_auth("evento")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作评价权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        $event_info = $GLOBALS['db']->getRow("select e.id,e.name,e.event_end_time,e.submit_count from ".DB_PREFIX."event e LEFT JOIN  ".DB_PREFIX."event_location_link ell on ell.event_id = e.id where e.id=".$data_id." and ell.location_id in(".implode(",", $account_info['location_ids']).")");
        
        //返回操作数据的权限
        if(!$event_info){
            output($root,0,"没有操作数据权限");
        }
        $event_info['f_event_end_time'] = $event_info['event_end_time']?to_date($event_info['event_end_time'],"Y-m-d"):"不限时";
        $root['event_info'] = $event_info;
        
        //商户下所有门店
        $locations = $GLOBALS['db']->getAll('select id,name from '.DB_PREFIX."supplier_location where id in (".implode(",", $account_info['location_ids']).")");
        foreach ($locations as $k=>$v){
            $f_location[$v['id']]=$v;
        }
        
        //根据审核状态查询
        $condition = " es.event_id = ".$data_id;
        
        
        //分页
        $page_size = PAGE_SIZE;
        $limit = (($page-1)*$page_size).",".$page_size;
        
        $sql = 'select es.id,es.create_time,es.sn,es.location_id,es.confirm_time,es.is_verify,u.user_name from '.DB_PREFIX."event_submit es left join ".DB_PREFIX."user u on u.id=es.user_id where".$condition." order by es.is_verify asc,es.create_time desc limit ".$limit;
        $count_sql = "select count(*) from ".DB_PREFIX."event_submit es where ".$condition;
       

        $result = $GLOBALS['db']->getAll($sql);
        $count = $GLOBALS['db']->getOne($count_sql);
         
        //分页
        $page_total = ceil($count/$page_size);
         
        foreach ($result as $k=>$v){
            $result[$k]['f_create_time'] = to_date($v['create_time'],"Y-m-d");
            if ($v['location_id']>0)
                $result[$k]['location_name'] = $f_location[$v['location_id']]['name'];
            else 
                $result[$k]['location_name'] = "-";
        }
        
        $root['item'] = $result?$result:array();
        $root['page_title'] = "活动报名列表";
        $root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size,"data_total"=>$count);
        output($root);
        
    }
    
    
    /**
     * 	活动同意审核接口
     *
     * 	 输入:
     *
     *  data_id     [int] 活动编号
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *
     *  
     */
    public function approval()	{
    
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
         
        /*获取参数*/
        $data_id =  intval($GLOBALS['request']['data_id']);
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
         
        //返回商户权限
        if(!check_module_auth("evento")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作权限");
        }else{
            $root['is_auth'] = 1;
        }
        
        require_once APP_ROOT_PATH.'system/model/event.php';

        $event_ids=$GLOBALS['db']->getRow("select group_concat(event_id SEPARATOR ',') as ids  from ".DB_PREFIX."event_location_link where location_id in (".implode(",",$account_info['location_ids']).")");
        $event_ids=explode(',',$event_ids['ids']);
    
        $auth_id=$GLOBALS['db']->getOne("select event_id from ".DB_PREFIX."event_submit where id=".$data_id);
    
        if(!in_array($auth_id,$event_ids)){
             output($root,0,"没有操作权限");
        }
    
        //$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set is_verify=1 where id=".$id." and event_id in (".$event_ids['ids'].")");
        verify_event_submit($data_id);
        if($GLOBALS['db']->affected_rows())
        {
            output($root,1,"已审核");
        }
        else
        {
            output($root,0,"操作失败");
        }
    
    }
    
    
    /**
     * 	活动拒绝审核接口
     *
     * 	 输入:
     *
     *  data_id     [int] 活动编号
     *
     *  输出:
     *  status:int 结果状态 0失败 1成功
     *  info:信息返回
     *  biz_user_status：int 商户登录状态 0未登录/1已登录
     *
     *  以下仅在biz_user_status为1时会返回
     *  is_auth：int 模块操作权限 0没有权限 / 1有权限
     *
     *
     */
    public function refuse()	{
    
        /*初始化*/
        $root = array();
        $account_info = $GLOBALS['account_info'];
         
        /*获取参数*/
        $data_id =  intval($GLOBALS['request']['data_id']);
        
        /*业务逻辑*/
        $root['biz_user_status'] = $account_info?1:0;
        if (empty($account_info)){
            output($root,0,"商户未登录");
        }
         
        //返回商户权限
        if(!check_module_auth("evento")){
            $root['is_auth'] = 0;
            output($root,0,"没有操作评价权限");
        }else{
            $root['is_auth'] = 1;
        }
        require_once APP_ROOT_PATH.'system/model/event.php';
    
        $event_ids=$GLOBALS['db']->getRow("select group_concat(event_id SEPARATOR ',') as ids  from ".DB_PREFIX."event_location_link where location_id in (".implode(",",$account_info['location_ids']).")");
        $event_ids=explode(',',$event_ids['ids']);
    
        $auth_id=$GLOBALS['db']->getOne("select event_id from ".DB_PREFIX."event_submit where id=".$data_id);
    
        if(!in_array($auth_id,$event_ids)){
             output($root,0,"没有操作权限");
        }
    
        //$GLOBALS['db']->query("update ".DB_PREFIX."event_submit set is_verify=1 where id=".$id." and event_id in (".$event_ids['ids'].")");
        refuse_event_submit($data_id);
        if($GLOBALS['db']->affected_rows())
        {
             output($root,1,"已拒绝");
        }
        else
        {
            output($root,0,"操作失败");
        }
    
    }
   
}
?>

