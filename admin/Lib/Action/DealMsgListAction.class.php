<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class DealMsgListAction extends CommonAction{
	public function index()
	{
		if(strim($_REQUEST['dest'])!='')
		$condition['dest'] = array('like','%'.strim($_REQUEST['dest']).'%');
		if(strim($_REQUEST['content'])!='')
		$condition['content'] = array('like','%'.strim($_REQUEST['content']).'%');
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function show_content()
	{
		$id = intval($_REQUEST['id']);
		header("Content-Type:text/html; charset=utf-8");
		echo htmlspecialchars(M("DealMsgList")->where("id=".$id)->getField("content"));
	}
	
	public function send()
	{
		$id = intval($_REQUEST['id']);
		$msg_item = M("DealMsgList")->getById($id);
		$res = send_msg_item($msg_item);
		if($res)
		{
			header("Content-Type:text/html; charset=utf-8");
			echo l("SEND_NOW").l("SUCCESS");
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo l("SEND_NOW").l("FAILED");
		}
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
			
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
		
}
?>