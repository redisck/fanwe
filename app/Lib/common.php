<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

//app项目用到的函数库






/**
 * 获取前次停留的页面地址
 * @return string url
 */
function get_gopreview()
{
	$gopreview = es_session::get("gopreview");
	if($gopreview==get_current_url())
	{
		$gopreview = url("index");
	}
	if(empty($gopreview))
		$gopreview = url("index");
	return $gopreview;
}


/**
 * 商户中心使用获取前次停留的页面地址
 * @return string url
 */
function get_biz_gopreview()
{
    $gopreview = es_session::get("biz_gopreview");
    if($gopreview==get_current_url())
    {
        $gopreview = url("biz");
    }
    if(empty($gopreview))
        $gopreview = url("biz");
    return $gopreview;
}

/**
 * 获取当前的url地址，包含分页
 * @return string
 */
function get_current_url()
{
	$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?");
	$parse = parse_url($url);
	if(isset($parse['query'])) {
		parse_str($parse['query'],$params);
		$url   =  $parse['path'].'?'.http_build_query($params);
	}
	if(app_conf("URL_MODEL")==1)
	{
		$url = $GLOBALS['current_url'];
		if(isset($_REQUEST['p'])&&intval($_REQUEST['p'])>0)
		{
			$req = $_REQUEST;
			unset($req['ctl']);
			unset($req['act']);
			unset($req['p']);
			if(count($req)>0)
			{
				$url.="-p-".intval($_REQUEST['p']);
			}
			else
			{
				$url.="/p-".intval($_REQUEST['p']);
			}
		}
	}
	if(substr($url, -1)=="?")$url = substr($url,0,-1);
	return $url;
}

/**
 * 将当前页设为回跳的上一页地址
 */
function set_gopreview()
{
	$url =  get_current_url();
	es_session::set("gopreview",$url);
}


/**
 * 将当前页设为回跳的上一页地址
 */
function set_biz_gopreview()
{
//     $url =  get_current_url();
//     es_session::set("biz_gopreview",$url);
	es_session::set("biz_gopreview",url("biz"));
}

/**
 * 跳转回上一页
 */
function app_redirect_preview()
{
	app_redirect(get_gopreview());
}



//获取所有子集的类
class ChildIds
{
	public function __construct($tb_name)
	{
		$this->tb_name = $tb_name;
	}
	private $tb_name;
	private $childIds;
	private function _getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$childItem_arr = $GLOBALS['db']->getAll("select id from ".DB_PREFIX.$this->tb_name." where ".$pid_str."=".intval($pid));
		if($childItem_arr)
		{
			foreach($childItem_arr as $childItem)
			{
				$this->childIds[] = $childItem[$pk_str];
				$this->_getChildIds($childItem[$pk_str],$pk_str,$pid_str);
			}
		}
	}
	public function getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$this->childIds = array();
		$this->_getChildIds($pid,$pk_str,$pid_str);
		return $this->childIds;
	}
}


//显示错误
function showErr($msg,$ajax=0,$jump='',$stay=0)
{
	if($ajax==1)
	{
		$result['status'] = 0;
		$result['info'] = $msg;
		$result['jump'] = $jump;
		ajax_return($result);
	}
	else
	{

		$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['ERROR_TITLE']);
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = get_gopreview();
		}

		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("stay",$stay);
		$GLOBALS['tmpl']->display("msg_page.html");
		exit;
	}
}

//显示成功
function showSuccess($msg,$ajax=0,$jump='',$stay=0)
{
	if($ajax==1)
	{
		$result['status'] = 1;
		$result['info'] = $msg;
		$result['jump'] = $jump;
		ajax_return($result);
	}
	else
	{
		$GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['SUCCESS_TITLE']);
		$GLOBALS['tmpl']->assign('msg',$msg);
		if($jump=='')
		{
			$jump = get_gopreview();
		}
		$GLOBALS['tmpl']->assign('jump',$jump);
		$GLOBALS['tmpl']->assign("stay",$stay);
		$GLOBALS['tmpl']->display("msg_page.html");
		exit;
	}
}

//显示错误
function showBizErr($msg,$ajax=0,$jump='',$stay=0)
{
    if($ajax==1)
    {
        $result['status'] = 0;
        $result['info'] = $msg;
        $result['jump'] = $jump;
        ajax_return($result);
    }
    else
    {

        $GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['ERROR_TITLE']);
        $GLOBALS['tmpl']->assign('msg',$msg);
        if($jump=='')
        {
            $jump = get_biz_gopreview();
        }

        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign("stay",$stay);
        $GLOBALS['tmpl']->display("msg_page.html");
        exit;
    }
}

//显示成功
function showBizSuccess($msg,$ajax=0,$jump='',$stay=0)
{
    if($ajax==1)
    {
        $result['status'] = 1;
        $result['info'] = $msg;
        $result['jump'] = $jump;
        ajax_return($result);
    }
    else
    {
        $GLOBALS['tmpl']->assign('page_title',$GLOBALS['lang']['SUCCESS_TITLE']);
        $GLOBALS['tmpl']->assign('msg',$msg);
        if($jump=='')
        {
            $jump = get_biz_gopreview();
        }
        $GLOBALS['tmpl']->assign('jump',$jump);
        $GLOBALS['tmpl']->assign("stay",$stay);
        $GLOBALS['tmpl']->display("msg_page.html");
        exit;
    }
}


//解析URL标签
// $str = u:shop|acate#index|id=10&name=abc
function parse_url_tag($str)
{
	$key = md5("URL_TAG_".$str);
	if(isset($GLOBALS[$key]))
	{
		return $GLOBALS[$key];
	}

	$url = load_dynamic_cache($key);
	$url=false;
	if($url!==false)
	{
		$GLOBALS[$key] = $url;
		return $url;
	}
	$str = substr($str,2);
	$str_array = explode("|",$str);
	$app_index = $str_array[0];
	$route = $str_array[1];
	$param_tmp = explode("&",$str_array[2]);
	$param = array();

	foreach($param_tmp as $item)
	{
		if($item!='')
			$item_arr = explode("=",$item);
		if($item_arr[0]&&$item_arr[1])
			$param[$item_arr[0]] = $item_arr[1];
	}
	$GLOBALS[$key]= url($app_index,$route,$param);
	set_dynamic_cache($key,$GLOBALS[$key]);
	return $GLOBALS[$key];
}

/**
 * 获得查询次数以及查询时间
 *
 * @access  public
 * @return  string
 */
function run_info()
{

	if(!SHOW_DEBUG)return "";

	$query_time = number_format($GLOBALS['db']->queryTime,6);

	if($GLOBALS['begin_run_time']==''||$GLOBALS['begin_run_time']==0)
	{
		$run_time = 0;
	}
	else
	{
		if (PHP_VERSION >= '5.0.0')
		{
			$run_time = number_format(microtime(true) - $GLOBALS['begin_run_time'], 6);
		}
		else
		{
			list($now_usec, $now_sec)     = explode(' ', microtime());
			list($start_usec, $start_sec) = explode(' ', $GLOBALS['begin_run_time']);
			$run_time = number_format(($now_sec - $start_sec) + ($now_usec - $start_usec), 6);
		}
	}

	/* 内存占用情况 */
	if (function_exists('memory_get_usage'))
	{
		$unit=array('B','KB','MB','GB');
		$size = memory_get_usage();
		$used = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		$memory_usage = lang("MEMORY_USED",$used);
	}
	else
	{
		$memory_usage = '';
	}

	/* 是否启用了 gzip */
	$enabled_gzip = (app_conf("GZIP_ON") && function_exists('ob_gzhandler'));
	$gzip_enabled = $enabled_gzip ? lang("GZIP_ON") : lang("GZIP_OFF");

	$str = lang("QUERY_INFO_STR",$GLOBALS['db']->queryCount, $query_time,$gzip_enabled,$memory_usage,$run_time);

	foreach($GLOBALS['db']->queryLog as $K=>$sql)
	{
		if($K==0)$str.="<br />SQL语句列表：";
		$str.="<br />行".($K+1).":".$sql;
	}

	return "<div style='width:940px; padding:10px; line-height:22px; border:1px solid #ccc; text-align:left; margin:30px auto; font-size:14px; color:#999; height:150px; overflow-y:auto;'>".$str."</div>";
}




/**
 * 前台初始化图片控件
 * @param string $type 对应类中上传的目录类型代码须补充
 * @return string
 */
function load_web_uploadimg($type){
	$tmpl_path = SITE_DOMAIN.APP_ROOT."/app/Tpl/fanwe";
	$plugins_path =$tmpl_path."/js/utils/kindeditor/plugins/";
	$upload_json = url("index","upload#".$type);
	return '<script>
			if(K == undefined)
				var K = KindEditor;
					var editor = K.editor({
					allowFileManager : true,
					pluginsPath:"'.$plugins_path.'",
					uploadJson:"'.$upload_json.'"
				});</script>';
}


//获取上传的主题附件数据
/* attach_list = array(
 * 	array(
 		* 		'id'=>xx,
 		* 		'type'	=>	xx, (如image, 可扩展，如vedio,music等)  //image从 topic_image表中取数据
 		* 	),
		*/
function get_topic_attach_list()
{
	$result = array();
	foreach($_REQUEST['topic_image_id'] as $id)
	{
		$topic_image =array();
		$topic_image['type'] = "image";
		$topic_image['id'] =  intval($id);
		$result[] = $topic_image;
	}
	return $result;
}

function load_compatible()
{
	return "";
	//return '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';
}


function toFormatTree($list,$title = 'title')
{
    $list = toTree($list);
    $formatTree = array();
    _toFormatTree($list,0,$title,$formatTree);
    return $formatTree;
}

function toTree($list=null, $pk='id',$pid = 'pid',$child = '_child')
{
    if(null === $list) {
        // 默认直接取查询返回的结果集合
        $list   =   &$this->dataList;
    }
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();

        foreach ($list as $key => $data) {
            $_key = is_object($data)?$data->$pk:$data[$pk];
            $refer[$_key] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = is_object($data)?$data->$pid:$data[$pid];
            $is_exist_pid = false;
            foreach($refer as $k=>$v)
            {
                if($parentId==$k)
                {
                    $is_exist_pid = true;
                    break;
                }
            }
            if ($is_exist_pid) {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            } else {
                $tree[] =& $list[$key];
            }
        }
    }
    return $tree;
}

function _toFormatTree($list,$level=0,$title = 'title',&$formatTree)
{
    foreach($list as $key=>$val)
    {
        $tmp_str=str_repeat("&nbsp;&nbsp;",$level*2);
        $tmp_str.="|--";

        $val['level'] = $level;
        $val['title_show'] = $tmp_str.$val[$title];
        if(!array_key_exists('_child',$val))
        {
            array_push($formatTree,$val);
        }
        else
        {
            $tmp_ary = $val['_child'];
            unset($val['_child']);
            array_push($formatTree,$val);
            _toFormatTree($tmp_ary,$level+1,$title,$formatTree); //进行下一层递归
        }
    }
    return;
}


?>