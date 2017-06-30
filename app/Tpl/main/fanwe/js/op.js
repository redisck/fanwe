/*推荐*/
function op_group_setbest(id)
{
	var query = new Object();
	query.m_name="group";
	query.a_name = "setbest";
	query.id = id;
	$.ajax({
		url:MANAGE_AJAX_URL,
		data:query,
		dataType:"json",
		type:"post",
		success:function(data){
			$.weeboxs.open(data.html, {boxid : 'setbest',title : "推荐主题", contentType : 'text',draggable : false,showButton : false,width:570,type:'wee'});	
		}
	});
	
}
/*置顶*/
function op_group_settop(id)
{
	var query = new Object();
	query.m_name="group";
	query.a_name = "settop";
	query.id = id;
	$.ajax({
		url:MANAGE_AJAX_URL,
		data:query,
		dataType:"json",
		type:"post",
		success:function(data){
			$.weeboxs.open(data.html, {boxid : 'settop',title : "置顶主题", contentType : 'text',draggable : false,showButton : false,width:570,type:'wee'});	
		}
	});
}
/*删除*/
function op_topic_del(id)
{
	var query = new Object();
	query.m_name="topic";
	query.a_name = "del";
	query.id = id;
	$.ajax({
		url:MANAGE_AJAX_URL,
		data:query,
		dataType:"json",
		type:"post",
		success:function(data){
			$.weeboxs.open(data.html, {boxid : 'topic_del',title : "删除主题", contentType : 'text',draggable : false,showButton : false,width:570,type:'wee'});	
		}
	});
}
/*删除主题*/
function op_group_del(id)
{
	var query = new Object();
	query.m_name="group";
	query.a_name = "del";
	query.id = id;
	$.ajax({
		url:MANAGE_AJAX_URL,
		data:query,
		dataType:"json",
		type:"post",
		success:function(data){
			$.weeboxs.open(data.html, {boxid : 'group_del',title : "删除主题", contentType : 'text',draggable : false,showButton : false,width:570,type:'wee'});	
		}
	});
}

function op_topic_replydel(id)
{
	var query = new Object();
	query.m_name="topic";
	query.a_name = "replydel";
	query.id = id;
	$.ajax({
		url:MANAGE_AJAX_URL,
		data:query,
		dataType:"json",
		type:"post",
		success:function(data){
			$.weeboxs.open(data.html, {boxid : 'topic_replydel',title : "删除主题回应", contentType : 'text',draggable : false,showButton : false,width:570,type:'wee'});	
		}
	});
}
function do_submit_opform()
{
	var query = $("form[name='opform']").serialize();
	
	var ajaxurl = $("form[name='opform']").attr("action");
	$.ajax({ 
		url: ajaxurl,
		data:query,
		type: "POST",
		dataType: "json",
		success: function(o){
			if(o.status==1)
				location.reload();
			else
				$.showErr(o.info);
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
	
}
function close_pop()
{
	$(".dialog-close").click();
}

