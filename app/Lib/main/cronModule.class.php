<?php
// +----------------------------------------------------------------------
// | Fanwe 方维系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class cronModule extends MainBaseModule
{
	//业务队列的群发
	public function deal_msg_list()
	{		
		set_time_limit(0);
		
		$GLOBALS['db']->query("update ".DB_PREFIX."conf set `value` = 1 where name = 'DEAL_MSG_LOCK' and `value` = 0");
		$rs = $GLOBALS['db']->affected_rows();
		if($rs)
		{			
			//业务队列中处理返利发放
			$rid = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."referrals where ".NOW_TIME."-create_time > ".(intval(app_conf('REFERRALS_DELAY'))*60)." and pay_time = 0");
			if($rid)
				pay_referrals(intval($rid));
			
			$msg_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_msg_list where is_send = 0 order by id asc limit 1");
			
			send_msg_item($msg_item);
			
			$count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."deal_msg_list where is_send = 0"));
			if($count==0)
			{
				$count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."referrals where pay_time = 0");
			}
			$GLOBALS['db']->query("update ".DB_PREFIX."conf set `value` = 0 where name = 'DEAL_MSG_LOCK'");	
		}
		else
		{
			$count = 0;
		}	
		$data['count'] = $count;
		ajax_return($data,true);
	}
	
	
	
	//群发队列
	public function promote_msg_list()
	{
		set_time_limit(0);
		
		//推广队列的群发
		$GLOBALS['db']->query("update ".DB_PREFIX."conf set `value` = 1 where name = 'PROMOTE_MSG_LOCK' and `value` = 0");
		$rs = $GLOBALS['db']->affected_rows();
		if($rs)
		{
			$promote_msg = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."promote_msg where send_status <> 2 and send_time <= ".NOW_TIME." order by id asc limit 1");
			
			if($promote_msg)
			{
				$last_id = intval($GLOBALS['db']->getOne("select value from ".DB_PREFIX."conf where name = 'PROMOTE_MSG_PAGE'"));
				
				//开始更新为发送中
				$GLOBALS['db']->query("update ".DB_PREFIX."promote_msg set send_status = 1 where id = ".intval($promote_msg['id'])." and send_status <> 2");
				switch(intval($promote_msg['send_type']))
				{
					case 0: //会员组					
						$group_id = intval($promote_msg['send_type_id']);
						if($promote_msg['type']==0)
						{
							//短信
							$sql = "select u.id,u.mobile from ".DB_PREFIX."user as u where u.mobile <> '' ";
							if($group_id>0)
							{
								$sql.=" and u.group_id = ".$group_id;
							}
							$sql.=" and u.id > ".$last_id." order by u.id asc";   
							$res = $GLOBALS['db']->getRow($sql);
							$dest = $res['mobile'];
							$uid = $res['id'];
							$last_id = $res['id'];
						}
						
						if($promote_msg['type']==1)
						{
							//邮件
							$sql = "select u.id,u.email from ".DB_PREFIX."user as u where u.email <> '' ";
							if($group_id>0)
							{
								$sql.=" and u.group_id = ".$group_id;
							}
							$sql.=" and u.id > ".$last_id." order by u.id asc";   
							$res = $GLOBALS['db']->getRow($sql);
							$dest = $res['email'];
							$uid = $res['id'];
							$last_id = $res['id'];
						}					
						break;
					case 1: //会员等级
						$level_id = intval($promote_msg['send_type_id']);
						if($promote_msg['type']==0)
						{
							//短信
							$sql = "select u.id,u.mobile from ".DB_PREFIX."user as u where u.mobile <> '' ";
							if($level_id>0)
							{
								$sql.=" and u.level_id = ".$level_id;
							}
							$sql.=" and u.id > ".$last_id." order by u.id asc";
							$res = $GLOBALS['db']->getRow($sql);
							$dest = $res['mobile'];
							$uid = $res['id'];
							$last_id = $res['id'];
						}
					
						if($promote_msg['type']==1)
						{
							//邮件
							$sql = "select u.id,u.email from ".DB_PREFIX."user as u where u.email <> '' ";
							if($level_id>0)
							{
								$sql.=" and u.level_id = ".$level_id;
							}
							$sql.=" and u.id > ".$last_id." order by u.id asc";
							$res = $GLOBALS['db']->getRow($sql);
							$dest = $res['email'];
							$uid = $res['id'];
							$last_id = $res['id'];
						}
						break;
					case 2: //自定义
						$send_define_data = trim($promote_msg['send_define_data']); //自定义的内容
						$dest_array = preg_split("/[ ,]/i",$send_define_data);
						
						foreach($dest_array as $k=>$v)
						{
								$rs = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."promote_msg_list where msg_id = ".intval($promote_msg['id'])." and dest = '".$v."'");
								if($rs==0)
								{
									$dest = $v;
									break;
								}
						}				
						$last_id = 0;
						break;
				}
				
				if($dest)
				{
					//开始创建一个新的发送队列
					$msg_data['dest'] = $dest;
					$msg_data['send_type'] = $promote_msg['type'];
					$msg_data['content'] = addslashes($promote_msg['content']);
					$msg_data['title'] = $promote_msg['title'];
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = NOW_TIME;
					$msg_data['user_id'] = intval($uid);
					$msg_data['is_html'] = $promote_msg['is_html'];
					$msg_data['msg_id'] = $promote_msg['id'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."promote_msg_list",$msg_data); //插入	
					if($id = $GLOBALS['db']->insert_id())
					{
							$msg_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."promote_msg_list where id = ".$id);
							
							if($msg_item)
							{
								//优先改变发送状态,不论有没有发送成功
								$GLOBALS['db']->query("update ".DB_PREFIX."promote_msg_list set is_send = 1,send_time='".NOW_TIME."' where id =".intval($msg_item['id']));
				
								if($msg_item['send_type']==0)
								{
									//短信
									require_once APP_ROOT_PATH."system/utils/es_sms.php";
									$sms = new sms_sender();
									$result = $sms->sendSms($msg_item['dest'],$msg_item['content'],1);
									//发送结束，更新当前消息状态
									$GLOBALS['db']->query("update ".DB_PREFIX."promote_msg_list set is_success = ".intval($result['status']).",result='".$result['msg']."' where id =".intval($msg_item['id']));
								}	
						
								if($msg_item['send_type']==1)
								{
									//邮件
									require_once APP_ROOT_PATH."system/utils/es_mail.php";
									$mail = new mail_sender();
							
									$mail->AddAddress($msg_item['dest']);
									$mail->IsHTML($msg_item['is_html']); 				  // 设置邮件格式为 HTML
									$mail->Subject = $msg_item['title'];   // 标题
						
									$mail->Body = $msg_item['content'];  // 内容	
							
									$is_success = $mail->Send();
									$result = $mail->ErrorInfo;
						
									//发送结束，更新当前消息状态
									$GLOBALS['db']->query("update ".DB_PREFIX."promote_msg_list set is_success = ".intval($is_success).",result='".$result."' where id =".intval($msg_item['id']));
								}						
							}	
					}
					$GLOBALS['db']->query("update ".DB_PREFIX."conf set value = ".intval($last_id)." where name='PROMOTE_MSG_PAGE'");
				}
				else //当没有目标可以发送时。完成发送
		        {
					$GLOBALS['db']->query("update ".DB_PREFIX."promote_msg set send_status = 2 where id = ".intval($promote_msg['id']));
					$GLOBALS['db']->query("update ".DB_PREFIX."conf set value = 0 where name='PROMOTE_MSG_PAGE'");
		        }
	        }
			
			$count = intval($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."promote_msg where send_status <> 2 and send_time <=".get_gmtime()));
	        $GLOBALS['db']->query("update ".DB_PREFIX."conf set `value` = 0 where name = 'PROMOTE_MSG_LOCK'");	
		}
	    else
	    {
			$count = 0;	
	    }
	    
	    $data['count'] = $count;
	    ajax_return($data,true);
	}
	
	
}
?>