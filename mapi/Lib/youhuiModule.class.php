<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class youhuiModule extends MainBaseModule
{
    /**
     * 优惠券详细页接口
     * 输入：
     * data_id: int 优惠券ID
     * 
     * 输出：
     * [array] 优惠券数据数组
     * [youhui_info] => Array  
        (
            [id] => 24  [int] 优惠券数据ID
            [share_url] => [string] 分享链接
            [name] => 烤羊腿       [string] 优惠券名称
            [icon] => http://localhost/o2onew/public/attachment/201505/04/11/5546e29f58225_600x364.jpg [string] 展示图 300x182
            [now_time] => 1430875272   [string] 当前时间
            [begin_time] => 1430766480   [string]   开始时间
            [end_time] => 1431716880     [string]   结束时间    
            [last_time] => 841608    [string]       最后剩下时间（结束时间-当前时间） 结束时间必须大于0才有，否则为0
            [last_time_format] => 9天以上   [string]   格式化最后剩下时间
            [expire_day] => 50   [int]  领取后有效天数
            [total_num] => 1000  [int]  优惠券总数
            [is_effect] => 1 [int]      是否有效
            [user_count] => 9   [int]   已经领取数量
            [user_limit] => 10  [int]   用户每天最多领取数量（用于限制）
            [score_limit] => 10 [int]   消耗积分
            [point_limit] => 20 [int]   经验限制
            [supplier_info_name] => 福州肯德基     [string]商户主门店名称
            [avg_point] => 3    [float] 点评平均分
            [description]=>fsafsa  [string] 优惠详情 / 展示图 300x？
            [use_notice]=>afas  [string] 使用须知/ 展示图 300x？
            [xpoint] => [float] 所在经度
            [ypoint] => [float] 所在纬度
        )
    [array] 其它支持门店
    [other_supplier_location] => Array
        (
            [0] => Array
                (
                    [id] => 23  [int]   门店编号
                    [name] => 肯德基（省府店）  [string]    门店名称
                    [address] => 鼓楼区八一七北路68号福建供销大厦二楼    [string]    门店地址
                    [tel] => 059188855566   [string] 门店电话
                    [xpoint] => [float] 所在经度
            		[ypoint] => [float] 所在纬度
                )

        )

    [dp_list] => Array [array] 点评数据列表
        (
          [4] => Array
                (
                    [id] => 5 [int] 点评数据ID
                    [create_time] => 2015-04-07 [string] 点评时间
                    [content] => 不错不错   [string] 点评内容
                    [reply_content] => 那是不错的了，可以信任的品牌 [string] 管理员回复内容
                    [point] => 5    [int] 点评分数
                    [user_name] => fanwe  [string] 点评用户名称
                    [images] => Array [array] 点评图集 压缩后的图片
                        (
                            [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36_120x120.jpg   [string] 点评图片 60X60
                            [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986_120x120.jpg   [string] 点评图片 60X60
                            [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061_120x120.jpg   [string] 点评图片 60X60
                        )

                    [oimages] => Array [array] 点评图集 原图
                        (
                            [0] => http://localhost/o2onew/public/comment/201504/07/14/bca3b3e37d26962d0d76dbbd91611a6a36.jpg [string] 点评图片 原图
                            [1] => http://localhost/o2onew/public/comment/201504/07/14/5ea94540a479810a7559b5db909b09e986.jpg [string] 点评图片 原图  
                            [2] => http://localhost/o2onew/public/comment/201504/07/14/093e5a064c4081a83f31864881bd802061.jpg [string] 点评图片 原图
                        )

                )

        )
     * */
    public function index(){
        $root = array();
        /*参数列表*/
        $data_id = intval($GLOBALS['request']['data_id']);
        
        /*业务逻辑*/
        //检查用户,用户密码
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);
        
        $user_login_status = check_login();
        if($user_login_status!=LOGIN_STATUS_LOGINED){
            $root['user_login_status'] = $user_login_status;
        }
        //获取优惠数据
        require_once APP_ROOT_PATH."system/model/youhui.php";
        $youhui_info = get_youhui($data_id);
        
        if($youhui_info){
            $root['id'] = $youhui_info['id'];
        }else{
            output($root,0,"优惠券数据未找到");
        }
        //获取支持门店数据
        $supplier_locations = $GLOBALS['db']->getAll("select sl.id,sl.name,sl.address,sl.tel,sl.xpoint,sl.ypoint from ".DB_PREFIX."youhui_location_link as yll left join ".DB_PREFIX."supplier_location as sl on sl.id = yll.location_id where yll.youhui_id = ".$data_id);
        /*点评数据*/
        require_once APP_ROOT_PATH."system/model/review.php";
         
        /*获点评数据*/
        $dp_list = get_dp_list(5,$param=array("youhui_id"=>$data_id),"","");
        
        $root['youhui_info'] = format_youhui_item($youhui_info);
        $root['other_supplier_location'] = $supplier_locations?$supplier_locations:array();
        $root['dp_list'] = $dp_list?format_dp_list($dp_list):array();
        $root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
        $root['page_title'].="优惠券领取";

        output($root);
    }
    
    
    /**
     * 优惠券下载接口
     * 输入：
     * data_id: int 优惠券ID
     *
     * 输出：
     * user_login_status:int 用户登录状态(1 已经登录/0用户未登录/2临时用户)
     * [info] string 错误消息/成功消息
     * [status] int 0 失败， 1成功
     * 
     * */
    public function download_youhui(){
        $root = array();
        /*参数列表*/
        $data_id = intval($GLOBALS['request']['data_id']);
        
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);
       
        $root['user_login_status'] = check_login();
         
        
        require_once APP_ROOT_PATH."system/model/youhui.php";
        $youhui_info = get_youhui($data_id);
   
        $result = download_youhui($data_id,$user_id);
        //status:1领取成功 0.领取失败 2.库存已满 3.时间超期
        if($result['status']>=0)
        {
            if($result['status']==YOUHUI_OUT_OF_STOCK||$result['status']==YOUHUI_USER_OUT_OF_STOCK)
            {

                output($root,0,$result['info']);
            }
            else if($result['status']==YOUHUI_DOWNLOAD_SUCCESS)
            {
                output($root,1,$result['info']);
            }
            else
            {
                output($root,0,$result['info']);
            }
        }
        else
        {
            output($root,0,$result['info']);
        }

        output($root);
    }
    
    /**
     * 优惠券详情接口
     * 输入：
     * data_id: int 优惠券ID
     *
     * 输出：
     * 优惠券部分数据
     * [youhui_info] => Array
        (
            [id] => 24
            [name] => 烤羊腿   [string] 优惠券名称
            [description] => <img src='http://localhost/o2onew/public/attachment/201505/06/17/5549dbd2183b6_300x0.jpg' lazy='true' /> [string]优惠券详情 图片大小宽度均为 150x?
            [use_notice] => <div>团将以短信形式通知中奖用户，请届时注意查收短信本单奖品不可折现</dd></div> [string]优惠须知， 图片大小宽度均为 150x?
        )
     *
     * */
    public function detail(){
        $root = array();
        /*参数列表*/
        $data_id = intval($GLOBALS['request']['data_id']);
        
        /*业务逻辑*/
        //检查用户,用户密码
        $user = $GLOBALS['user_info'];
        $user_id  = intval($user['id']);
        

        //获取优惠数据
        require_once APP_ROOT_PATH."system/model/youhui.php";
        $youhui_info = get_youhui($data_id);
        if($youhui_info){
            $root['id'] = $youhui_info['id'];
        }else{
            output($root,0,"优惠券不存在");
        }
        $data['id'] = $youhui_info['id'];
        $data['name'] = $youhui_info['name'];
        $data['description'] = get_abs_img_root(format_html_content_image($youhui_info['description'],150));
        $data['use_notice'] = get_abs_img_root(format_html_content_image($youhui_info['use_notice'],150));
        
        $root['page_title'] = $GLOBALS['m_config']['program_title']?$GLOBALS['m_config']['program_title']." - ":"";
        $root['page_title'].="优惠券详情";
        $root['youhui_info'] = $data;
        output($root);
    }
}