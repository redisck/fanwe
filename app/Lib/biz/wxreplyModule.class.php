<?php
// +----------------------------------------------------------------------
// | Fanwe 方维商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'system/wechat/platform_wechat.class.php';
class wxreplyModule extends BizBaseModule
{
    private $faces = array();
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
        $this->faces = array(
            "/::)"=>"0.gif","/::~"=>"1.gif","/::B"=>"2.gif","/::|"=>"3.gif","/:8-)"=>"4.gif",
            "/::<"=>"5.gif",'/::$'=>"6.gif",
            "/::X"=>"7.gif","/::Z"=>"8.gif","/::'("=>"9.gif",
            "/::-|"=>"10.gif","/::@"=>"11.gif","/::P"=>"12.gif","/::D"=>"13.gif","/::O"=>"14.gif",
            "/::("=>"15.gif","/::+"=>"16.gif","/:–b"=>"17.gif","/::Q"=>"18.gif","/::T"=>"19.gif","/:,@P"=>"20.gif","/:,@-D"=>"21.gif","/::d"=>"22.gif","/:,@o"=>"23.gif","/::g"=>"24.gif","/:|-)"=>"25.gif","/::!"=>"26.gif","/::L"=>"27.gif","/::>"=>"28.gif","/::,@"=>"29.gif","/:,@f"=>"30.gif","/::-S"=>"31.gif","/:?"=>"32.gif","/:,@x"=>"33.gif","/:,@@"=>"34.gif","/::8"=>"35.gif","/:,@!"=>"36.gif","/:!!!"=>"37.gif","/:xx"=>"38.gif","/:bye"=>"39.gif","/:wipe"=>"40.gif","/:dig"=>"41.gif","/:handclap"=>"42.gif","/:&-("=>"43.gif","/:B-)"=>"44.gif","/:<@"=>"45.gif","/:@>"=>"46.gif","/::-O"=>"47.gif","/:>-|"=>"48.gif","/:P-("=>"49.gif","/::'|"=>"50.gif","/:X-)"=>"51.gif","/::*"=>"52.gif","/:@x"=>"53.gif","/:8*"=>"54.gif","/:pd"=>"55.gif","/:<W>"=>"56.gif","/:beer"=>"57.gif",
            "/:basketb"=>"58.gif","/:oo"=>"59.gif","/:coffee"=>"60.gif","/:eat"=>"61.gif","/:pig"=>"62.gif","/:rose"=>"63.gif","/:fade"=>"64.gif","/:showlove"=>"65.gif","/:heart"=>"66.gif","/:break"=>"67.gif","/:cake"=>"68.gif","/:li"=>"69.gif","/:bome"=>"70.gif","/:kn"=>"71.gif","/:footb"=>"72.gif","/:ladybug"=>"73.gif","/:shit"=>"74.gif","/:moon"=>"75.gif","/:sun"=>"76.gif","/:gift"=>"77.gif","/:hug"=>"78.gif","/:strong"=>"79.gif","/:weak"=>"80.gif","/:share"=>"81.gif","/:v"=>"82.gif","/:@)"=>"83.gif","/:jj"=>"84.gif","/:@@"=>"85.gif","/:bad"=>"86.gif","/:lvu"=>"87.gif","/:no"=>"88.gif","/:ok"=>"89.gif","/:love"=>"90.gif","/:<L>"=>"91.gif","/:jump"=>"92.gif","/:shake"=>"93.gif","/:<O>"=>"94.gif","/:circle"=>"95.gif","/:kotow"=>"96.gif","/:turn"=>"97.gif","/:skip"=>"98.gif","[挥手]"=>"99.gif","/:#-0"=>"100.gif","[街舞]"=>"101.gif",
            "/:kiss"=>"102.gif","/:<&"=>"103.gif","/:&>"=>"104.gif"
        );
	}

    public function index()
    {
        // 页面初始化，比如引入左侧菜单配置信息等
        init_app_page();

        // type=1表示默认回复，只会存在一条记录(文本和图文内容都在一条记录里)
        $reply = array();
        $reply = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where type=1 and account_id = ".$this->supplier_id);
        if($reply){
            // 以o_msg_type类型来判断当前的默认回复内容
            if($reply['o_msg_type'] == "news"){
                app_redirect(url("biz","wxreply#dnews"));
            }else{
                app_redirect(url("biz","wxreply#dtext"));
            }
        }else{
            app_redirect(url("biz","wxreply#dtext"));
        }
    }
	
	/**
	 * 默认回复（文本）
	 */
	public function dtext()
	{
		// 页面初始化，比如引入左侧菜单配置信息等
		init_app_page();
		
		$GLOBALS['tmpl']->assign("page_title", "默认回复(文本)");

        // type=1表示默认回复，只会存在一条记录(文本和图文内容都在一条记录里)
        $reply = array();
        $reply = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where type=1 and account_id = ".$this->supplier_id);
        if($reply){
            $faces = $this->faces;
            $face_keys = array();
            $face_values = array();
            foreach($faces as $fkey => $fval){
                $face_keys[] = $fkey;
                $face_values[] = '<img src="'.get_domain().APP_ROOT.'/system/weixin/static/images/face/'.$fval.'" border="0" alt="'.$fkey.'">';
            }
            $reply['reply_content'] = nl2br(str_replace($face_keys,$face_values,htmlspecialchars_decode($reply['reply_content'])));
        }

        $GLOBALS['tmpl']->assign("reply", $reply);
		$GLOBALS['tmpl']->display("pages/wxreply/index.html");
	}

    /**
     * 保存默认文本回复
     */
    public function save_dtext(){
        $id = intval($_POST['id']);
        $default_close = intval($_POST['default_close']);
        $reply_content  = trim($_REQUEST['reply_content']);

        $result = array();
        $result['status'] = 0;

        if($reply_content==""){
            $result['info'] = "回复内容不能为空";
            ajax_return($result);
            exit;
        }
        //var_dump($reply_content);exit;
        preg_match_all('/(<a.*?>.*?<\/a>)/',$reply_content,$links);
        $search_array = array();
        $replace_array = array();
        foreach($links[1] as $link){
            $replace_key = md5($link);
            $search_array[] = $replace_key;
            $replace_array[] = $link;
            $reply_content = str_replace($link,$replace_key,$reply_content);
        }

        $reply_content = preg_replace('/&amp;/',"&",$reply_content);

        $reply_content = preg_replace('/<img src=".*?"( border="0")? alt="(.*?)"( border="0")?( \/)?>/',' $2',$reply_content);

        $reply_content = preg_replace('/<div>(.*?)<\/div>/',"\n$1 ",$reply_content);
        $reply_content = trim(strip_tags($reply_content));
        $reply_content = str_replace($search_array,$replace_array,$reply_content);

        //$reply_content = strim($reply_content);

        if($id > 0){
            //更新
            $reply_data = array();
            $reply_data = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where id=$id");
            $data=array(
                'reply_content'=>$reply_content,
                'o_msg_type'=>'text',
                'default_close'=>$default_close,
                'account_id'=> $this->supplier_id,
            );
            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply",$data,'update',"id=$id");

            $result['info'] = "修改成功";
            //$result['status'] = 1;
            //$result['jump'] = url("biz", "wxreply#index");
            ajax_return($result);
            exit;

        }else{
            //新增
            $reply_data= array();
            $reply_data['i_msg_type'] = "text";
            $reply_data['o_msg_type'] = "text";
            $reply_data['reply_content'] = $reply_content;
            $reply_data['type'] = 1; //默认回复
            $reply_data['default_close'] = $default_close;
            $reply_data['account_id'] = $this->supplier_id;

            //保存数据
            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply",$reply_data);
            $res = $GLOBALS['db']->insert_id();
            if($res > 0){
                //$result['status'] = 1;
                $result['info'] = "保存成功";
                //$result['jump'] = url("biz", "wxreply#index");
                ajax_return($result);
                exit;
            }else{
                if($res == -1){
                    $result['info'] = "文本回复限额已满";
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
	
	// 默认回复（图文）
	public function dnews(){
	
		init_app_page();

		$GLOBALS['tmpl']->assign("navs", $this->navs);
		$GLOBALS['tmpl']->assign("navs_json",json_encode($this->navs));

        // type=1表示默认回复，只会存在一条记录(文本和图文内容都在一条记录里)
        $reply = array();
        $reply = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where type=1 and account_id = ".$this->supplier_id);
        if($reply){
            //输出菜单跳转
            $temp = unserialize($reply['data']);

            $reply['data'] = $temp[$this->navs[$reply['ctl']]['field']];
            $reply['data_name'] = $this->navs[$reply['ctl']]['fname'];

            //输出关联的回复
            $relate_replys = array();
            $sql = " select * from " . DB_PREFIX . "weixin_reply_relate";
            $conditions =" where main_reply_id= '".$reply['id']. "'";
            $limit = 10;
            $relate_replys = $GLOBALS['db']->getAll($sql . $conditions . " order by sort ASC limit " . $limit);
            foreach($relate_replys as $k=>$v){

                $relate_replys[$k] = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where id='".$v['relate_reply_id']."'");

            }

            $faces = $this->faces;
            $face_keys = array();
            $face_values = array();
            foreach($faces as $fkey => $fval){
                $face_keys[] = $fkey;
                $face_values[] = '<img src="'.get_domain().APP_ROOT.'/system/weixin/static/images/face/'.$fval.'" border="0" alt="'.$fkey.'">';
            }
            $reply['reply_content'] = nl2br(str_replace($face_keys,$face_values,htmlspecialchars_decode($reply['reply_content'])));
        }

        $GLOBALS['tmpl']->assign("relate_replys", $relate_replys);
		$GLOBALS['tmpl']->assign("reply", $reply);
		$GLOBALS['tmpl']->assign("ajax_url", url("biz", "wxreply#dnews"));
		$GLOBALS['tmpl']->assign("page_title", "默认回复(图文)");
		$GLOBALS['tmpl']->display("pages/wxreply/dnews.html");
	}

    public function save_dnews(){

        $id = intval($_POST['id']);
        $default_close = intval($_POST['default_close']);
        $reply_news_description  = trim($_POST['reply_news_description']);

        $result = array();
        $result['status'] = 0;

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
        $reply_news_picurl = strim($_POST['reply_news_picurl']);
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

        //更新
        if($reply_data){

            $reply_data['reply_news_title'] = $reply_news_title;
            $reply_data['reply_news_description'] = $reply_news_description;
            $reply_data['reply_news_picurl'] = $reply_news_picurl;

            $reply_data['reply_news_url'] = trim($_REQUEST['reply_news_url']);
            $reply_data['account_id'] = $this->supplier_id;
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
            $reply_data['default_close'] = $default_close;

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
            ajax_return($result);
            exit;
        }else{
            //新增
            $reply_data= array();
            $reply_data['i_msg_type'] = "text";
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

            $reply_data['type'] = 1; //默认回复
            $reply_data['default_close'] = $default_close;
            //			$reply_data['relate_data'] = $relate_data;
            //			$reply_data['relate_id'] = $relate_id;
            $reply_data['relate_type'] = $relate_type;

            // 保存新增
            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply",$reply_data);
            $res = $GLOBALS['db']->insert_id();

            if($res > 0){
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

                $result['info'] = "保存成功";
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


		$sql = " select * from " . DB_PREFIX . "weixin_reply";
		$limit = 10;
		$list = $GLOBALS['db']->getAll($sql . $conditions . " order by id desc limit " . $limit);
		$GLOBALS['tmpl']->assign("list", $list);
		$data['html'] = $GLOBALS['tmpl']->fetch("pages/wxreply/add_mutil_news_weebox.html");
		
		ajax_return($data);
	}

}
?>