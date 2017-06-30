$(function(){
	
	//上传控件
	$(".publish_box_options .pub_img").ui_upload({multi:false,FilesAdded:function(files){
		
		return true;
	},FileUploaded:function(responseObject){
		if(responseObject.error==1000)
		{
			ajax_login();
		}
		else if(responseObject.error==0)
		{
		
		}
		else
		{
			$.showErr(responseObject.message);
		}
	},UploadComplete:function(files){
	
	},Error:function(errObject){
		
	}});
});
