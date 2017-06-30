<?php
// +----------------------------------------------------------------------
// | Fanwe 方维商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'system/wechat/platform_wechat.class.php';
class wxtextModule extends BizBaseModule
{
    private $faces = array();
    private $supplier_id = 0;

	function __construct()
	{
		parent::__construct();
		global_run();
		$this->check_auth();

        $account_info = $GLOBALS['account_info'];
        $this->supplier_id = intval($account_info['supplier_id']);

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
	
	// 文本回复
	public function index()
	{
		init_app_page();
		
		$GLOBALS['tmpl']->assign("page_title", "文本回复");

        $list = array();
        $sql = " select * from " . DB_PREFIX . "weixin_reply";
        $conditions =" where account_id=".$this->supplier_id." and o_msg_type='text' and type=0   ";
        $limit = 10;
        $list = $GLOBALS['db']->getAll($sql . $conditions . " order by id desc limit " . $limit);

        foreach ($list as $k => $v) {
            $list[$k]['edit_url'] = url("biz", "wxtext#publish", array(
                "id" => $v['id']
            ));
            $list[$k]['match_name'] = $v['match_type']?'全字配匹':'模糊配匹';
        }
        $GLOBALS['tmpl']->assign("list", $list);
        $GLOBALS['tmpl']->assign("ajax_url", url("biz", "wxtext"));
		$GLOBALS['tmpl']->display("pages/wxtext/index.html");
	}
	
	public function publish()
	{
		init_app_page();

        $id = intval($_REQUEST['id']);

        $reply = array();
        $reply = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "weixin_reply where type=0 and account_id = ".$this->supplier_id." and o_msg_type = 'text' and id=".$id);
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
		$GLOBALS['tmpl']->assign("page_title", "自定义文本回复");
		$GLOBALS['tmpl']->display("pages/wxtext/publish.html");
	}

    /**
     * 新增/修改文本回复
     */
    public function save_text(){

        $result = array();
        $result['status'] = 0;

        $id = intval($_POST['id']);
        $reply_content  = trim($_REQUEST['reply_content']);
        $keywords = trim($_POST['keywords']);

        $match_type = (int)$_POST['match_type'];

        //验证关键词的重复性。模糊匹配，关键通过空格分开；全文匹配，整体当作一个关键字
        $exists_keywords = word_check($keywords,$id,$match_type,$this->supplier_id);

        if(count($exists_keywords)>0){
            $err_content = "关键词：%s 已经存在相关回复";
            $keywords_str = implode(",", $exists_keywords);
            $keywords_str = sprintf($err_content,$keywords_str);

            $result['info'] = $keywords_str;
            ajax_return($result);
            exit;
        }

        if($reply_content==""){
            $result['info'] = "回复内容不能为空";
            ajax_return($result);
            exit;
        }
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
        $reply_content = strim($reply_content);
        if($id > 0){
            //更新
            $reply_data  = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."weixin_reply where id=".$id." and o_msg_type = 'text' and account_id = ".$this->supplier_id);
            if($reply_data){
                $reply_data['match_type'] = $match_type;
                $reply_data['reply_content'] = $reply_content;
                $reply_data['keywords'] = $keywords;
                $reply_data['keywords_match'] = '';
                $reply_data['keywords_match_row'] = '';
                $reply_data['account_id'] = $this->supplier_id;

                $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply",$reply_data,'UPDATE'," id=".$id);

                // 模糊匹配时才进行索引处理
                if($match_type == 0){
                    syncMatch($id);
                }
                $result['status'] = 1;
                $result['info'] = "保存成功";
                $result['jump'] = url("biz", "wxtext");
                ajax_return($result);
                exit;
            }else{
                $result['info'] = "非法操作";
                ajax_return($result);
                exit;
            }
        }else{
            //新增
            $reply_data= array();
            $reply_data['i_msg_type'] = "text";
            $reply_data['o_msg_type'] = "text";
            $reply_data['reply_content'] = $reply_content;
            $reply_data['keywords'] = $keywords;
            $reply_data['match_type'] = $match_type;
            $reply_data['type'] = 0;
            $reply_data['account_id'] = $this->supplier_id;

            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_reply",$reply_data);
            $res = $GLOBALS['db']->insert_id();

            if($res>0){

                // 模糊匹配时才进行索引处理
                if($match_type == 0){
                    syncMatch($res);
                }
                $result['status'] = 1;
                $result['info'] = "保存成功";
                $result['jump'] = url("biz", "wxtext");
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
            $result['jump'] = url("biz", "wxtext");
            ajax_return($result);
            exit;
        }else{
            $result['info'] = "请选择要删除的对象";
            ajax_return($result);
            exit;
        }
    }

}
?>