	$(document).ready(function(){
		load_exchange_row();
		$("select[name='send_type']").bind("change",function(){load_exchange_row();});
		load_ecv_preview();
		$("select[name='tpl']").bind("change",function(){
			load_ecv_preview();
		});
	});
	function load_exchange_row()
	{
		var send_type = $("select[name='send_type']").val();
		if(send_type==1)
		{
			$("input[name='exchange_sn']").val("");
			$("input[name='exchange_limit_bonus']").val("");
			$("#bonus_row").hide();
			$("#exchange_row").show();
			$("#share_url_row").hide();
			$("#tpl_row").hide();
			$("#memo_row").hide();
			$("#total_limit").show();
		}
		else if(send_type==2)
		{
			$("input[name='exchange_score']").val("");
			$("input[name='exchange_limit_score']").val("");	
			$("#exchange_row").hide();
			$("#bonus_row").show();
			$("#share_url_row").show();
			$("#tpl_row").show();
			$("#memo_row").show();
			$("#total_limit").show();
		}
		else
		{			
			$("#total_limit").hide();
			$("input[name='exchange_score']").val("");
			$("input[name='exchange_limit_score']").val("");	
			$("input[name='exchange_sn']").val("");
			$("input[name='exchange_limit_bonus']").val("");	
			$("#exchange_row").hide();
			$("#bonus_row").hide();
			$("#share_url_row").hide();
			$("#tpl_row").hide();
			$("#memo_row").hide();
		}
	}
	

	function load_ecv_preview()
	{
		var tpl_file = $("select[name='tpl']").val();
		var t = tpl_file.split(".");
		var ecv_key = t[0];
		$("#preview").html("<img src='"+APP_ROOT+"/system/ecv_tpl/preview/"+ecv_key+".jpg' />");
	}