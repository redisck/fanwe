<?php
// +----------------------------------------------------------------------
// | Fanwe 方维商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'system/wechat/platform_wechat.class.php';
class wxlbsModule extends BizBaseModule
{
    private $navs = array();
    private $supplier_id = 0;

	function __construct()
	{
		parent::__construct();
		global_run();
		$this->check_auth();

        $account_info = $GLOBALS['account_info'];
        $this->supplier_id = intval($account_info['supplier_id']);

        $this->navs = require APP_ROOT_PATH."system/mobile_cfg/".APP_TYPE."/wxnav_cfg.php";
	}
	
	// LBS回复
	public function index()
	{
		init_app_page();

        $GLOBALS['tmpl']->assign("page_title", "LBS回复");

        $list = array();
        $sql = " select * from " . DB_PREFIX . "weixin_reply";
        $conditions =" where account_id=".$this->supplier_id." and o_msg_type='news' and i_msg_type = 'location' and type=0   ";
        $limit = 10;
        $list = $GLOBALS['db']->getAll($sql . $conditions . " order by id desc limit " . $limit);

        foreach ($list as $k => $v) {
            $list[$k]['edit_url'] = url("biz", "wxlbs#publish", array(
                "id" => $v['id']
            ));
        }

        $GLOBALS['tmpl']->assign("list", $list);
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "wxlbs"));
		$GLOBALS['tmpl']->display("pages/wxlbs/index.html");
		
	}
	
	public function publish()
	{
		init_app_page();

		$reply = array();
		$reply['x_point'] = '';
		$reply['y_point'] = '';
		$GLOBALS['tmpl']->assign("page_title", "LBS回复设置");
		$GLOBALS['tmpl']->assign("BAIDU_MAP_APPKEY", "123");//conf("BAIDU_MAP_APPKEY")

        $id = intval($_REQUEST['id']);

        $reply = array();
        $relate_replys = array();
        $reply = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where type=0 and account_id = ".$this->supplier_id." and o_msg_type = 'news' and i_msg_type='location' and id=".$id);
        if($reply){

            //输出菜单跳转
            $temp = unserialize($reply['data']);
            $reply['data'] = $temp[$this->navs[$reply['ctl']]['field']];
            $reply['data_name'] = $this->navs[$reply['ctl']]['fname'];

            //输出关联的回复
            $sql = " select * from " . DB_PREFIX . "weixin_reply_relate";
            $conditions =" where main_reply_id= '".$reply['id']. "'";
            $limit = 10;
            $relate_replys = $GLOBALS['db']->getAll($sql . $conditions . " order by sort ASC limit " . $limit);
            foreach($relate_replys as $k=>$v){

                $relate_replys[$k] = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where id='".$v['relate_reply_id']."'");

            }
        }

        $GLOBALS['tmpl']->assign("relate_replys", $relate_replys);
		$GLOBALS['tmpl']->assign("reply", $reply);
		$GLOBALS['tmpl']->assign("navs", $this->navs);
		$GLOBALS['tmpl']->assign("navs_json",json_encode($this->navs));
		
		$GLOBALS['tmpl']->display("pages/wxlbs/publish.html");
	}

    /**
     * 保存lbs图文回复
     */
    public function save_lbs(){

        $result = array();
        $result['status'] = 0;

        $id = intval($_POST['id']);
        $x_point = trim($_POST['x_point']);
        $y_point = trim($_POST['y_point']);
        $address = trim($_POST['address']);
        $api_address = trim($_POST['api_address']);
        $scale_meter = intval($_POST['scale_meter']);

        if($x_point=="" || $y_point==""){
            $result['info'] = "请选定位经纬度";
            ajax_return($result);
            exit;
        }
        if($address==""){
            $result['info'] = "地址不能为空";
            ajax_return($result);
            exit;
        }
        if($scale_meter<1000){
            $result['info'] = "范围不能小于1000米";
            ajax_return($result);
            exit;
        }

        $reply_news_description  = trim($_POST['reply_news_description']);
        if($reply_news_description==""){
            $result['info'] = "回复内容不能为空";
            ajax_return($result);
            exit;
        }
        $reply_news_title = trim($_POST['reply_news_title']);
        if($reply_news_title==""){
            $result['info'] = "回复标题不能为空";
            ajax_return($result);
            exit;
        }
        $reply_news_picurl = (trim($_POST['reply_news_picurl']));
        if($reply_news_picurl==""){
            $result['info'] = "回复图片不能为空";
            ajax_return($result);
            exit;
        }
        if(strim($_REQUEST['ctltype'])=="url"&&strim($_REQUEST['data'])=="")
        {
            $result['info'] = "请输入链接地址";
            ajax_return($result);
            exit;
        }

        //读取信息
        $reply_data = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where id=$id");

        if($reply_data){

            $reply_data['reply_news_title'] = $reply_news_title;
            $reply_data['reply_news_description'] = $reply_news_description;
            $reply_data['reply_news_picurl'] = $reply_news_picurl;
            $reply_data['reply_news_url'] = trim($_REQUEST['reply_news_url']);
            $reply_data['ctl'] = strim($_REQUEST['ctltype']);

            switch($reply_data['ctl']){
                //商家主页，则获取商家ID
                case 'home':
                    $data = $this->supplier_id;
                    break;
                default:
                    $data = strim($_REQUEST['data']);
            }
            $field = $this->navs[$reply_data['ctl']]['field'];
            if($field)
            {
                $reply_data['data'] = serialize(array($field=>$data));
            }

            $reply_data['o_msg_type'] = "news";

            $reply_data['x_point'] = $x_point;
            $reply_data['y_point'] = $y_point;
            $reply_data['address'] = $address;
            $reply_data['api_address'] = $api_address;
            $reply_data['scale_meter'] = $scale_meter;
            $reply_data['account_id'] = $this->supplier_id;

            //保存更新
            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply",$reply_data,'update',"id=$id");

            // 删除关联的图文
            $GLOBALS['db']->query("delete from " . DB_PREFIX . "weixin_reply_relate where main_reply_id=" . $id);

            // 插入新的关联图文
            $total = 0;
            if($_POST['relate_reply_id']){
                foreach ($_POST['relate_reply_id'] as $k=>$vv){
                    if(intval($vv) > 0 && $total < 9){
                        $total++;
                        $link_data = array();
                        $link_data['main_reply_id'] = $id;
                        $link_data['relate_reply_id'] = $vv;
                        $link_data['sort'] = $k;

                        $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply_relate",$link_data);
                    }
                }
            }

            $result['info'] = "保存成功";
            $result['jump'] = url("biz", "wxlbs");
            ajax_return($result);
            exit;

        }else{
            //新增
            $reply_data= array();
            $reply_data['i_msg_type'] = "location";
            $reply_data['o_msg_type'] = "news";
            $reply_data['reply_news_title'] = $reply_news_title;
            $reply_data['reply_news_description'] = $reply_news_description;
            $reply_data['reply_news_picurl'] = $reply_news_picurl;
            $reply_data['reply_news_url'] = trim($_REQUEST['reply_news_url']);
            $reply_data['ctl'] = strim($_REQUEST['ctltype']);

            switch($reply_data['ctl']){
                //商家主页，则获取商家ID
                case 'home':
                    $data = $this->supplier_id;
                    break;
                default:
                    $data = strim($_REQUEST['data']);
            }
            $field = $this->navs[$reply_data['ctl']]['field'];
            if($field)
            {
                $reply_data['data'] = serialize(array($field=>$data));
            }

            $reply_data['type'] = 0; //默认回复

            $reply_data['account_id'] = $this->supplier_id;
            $reply_data['x_point'] = $x_point;
            $reply_data['y_point'] = $y_point;
            $reply_data['address'] = $address;
            $reply_data['api_address'] = $api_address;
            $reply_data['scale_meter'] = $scale_meter;

            // 保存新增
            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply",$reply_data);
            $res = $GLOBALS['db']->insert_id();

            if($res>0){
                $total = 0;
                if($_POST['relate_reply_id']){
                    foreach ($_POST['relate_reply_id'] as $k=>$vv){
                        if(intval($vv) > 0 && $total < 9){
                            $total++;
                            $link_data = array();
                            $link_data['main_reply_id'] = $res;
                            $link_data['relate_reply_id'] = $vv;
                            $link_data['sort'] = $k;

                            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply_relate",$link_data);
                        }
                    }
                }

                $result['status'] = 1;
                $result['info'] = "保存成功";
                // status为1，可设置跳转地址
                $result['jump'] = url("biz", "wxlbs");
                ajax_return($result);
                exit;
            }else{
                if($res == -1){
                    $result['info'] = "图文回复限额已满";
                    ajax_return($result);
                    exit;
                }else{
                    $result['info'] = "系统出错，请重试";
                    ajax_return($result);
                    exit;
                }
            }
        }
    }

    /**
     * 删除
     */
    public function do_delete(){
        $id = strim($_REQUEST['id']);
        $result = array();
        $result['status'] = 0;

        if($id > 0){
            // 删除
            $GLOBALS['db']->query("delete from " . DB_PREFIX . "weixin_reply where id=" . $id);

            $result['info'] = "删除成功";
            $result['status'] = 1;
            $result['jump'] = url("biz", "wxlbs");
            ajax_return($result);
            exit;
        }else{
            $result['info'] = "请选择要删除的对象";
            ajax_return($result);
            exit;
        }
    }

    /**
     * 增加多图文回复
     */
    public function load_add_mutil_news_weebox()
    {
        $main_id = intval($_REQUEST['main_id']);
        $keywords = strim($_REQUEST['keywords']);
        //		$where = array(
        //			'account_id'=>0,
        //			'o_msg_type'=>'news',
        //			'type'=>0,
        //			'id'=>array('neq',$main_id)
        //		);
        $conditions =" where account_id=".$this->supplier_id." and o_msg_type='news' and type=0 and id <> ".$main_id." ";
        if($keywords){
// 			$this->assign("keywords",$keywords);

// 			$unicode_tag = str_to_unicode_string($keywords);

// 			$condition .= " and MATCH(keywords_match) AGAINST('".$unicode_tag."' IN BOOLEAN MODE) ";
        }

        $list = array();
        $sql = " select * from " . DB_PREFIX . "weixin_reply";
        $limit = 10;
        $list = $GLOBALS['db']->getAll($sql . $conditions . " order by id desc limit " . $limit);
        $GLOBALS['tmpl']->assign("list", $list);
        $data['html'] = $GLOBALS['tmpl']->fetch("pages/wxreply/add_mutil_news_weebox.html");

        ajax_return($data);
    }
}
?>