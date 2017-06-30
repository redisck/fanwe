$(document).ready(function(){

	
	
	/*图片上传*/
	var img_index = 0;
	$("#file-btn").live("change",function(){
		//var obj=$(this).find('.file-btn');
		 if(this.files[0].type=='image/png'||this.files[0].type=='image/jpeg'||this.files[0].type=='image/gif'){
			 
			if($("#image-lib").find(".review_unit").length>8)
			{
				$.showErr("最多只能传9张图片");
				return false;
			}
			else
			{

				var html = '<div class="review_unit w100 h100  f_l pr mr020 mb020 bg_e8e9ea" data-index="'+img_index+'"></div>';
				var dom = $(html);		
				$("#image-lib").append(dom);	
				
			}
			
	    	
	    	
	       // 也可以传入图片路径：lrz('../demo.jpg', ...
	    	var is_err = 0;
	    	
	       lrz(this.files[0], {
				width:1200,
				height:900,
	           before: function() {
	           	//压缩开始
	           },
	           fail: function(err) {
	               //console.error(err);
	           	is_err = 1;
	           	alert(err);
	           },
	           always: function() {
	           	//压缩结束
	           },
	           done: function (results) {
	           // 你需要的数据都在这里，可以以字符串的形式传送base64给服务端转存为图片。
	           	
	           	if(is_err !=1){
	           		var data = new Array();
	           		var data = {
	                           base64: results.base64,
	                           size: results.base64.length // 校验用，防止未完整接收
	                       };
	           		
	           		upfile_data[img_index] = JSON.stringify(data);
	    
	           		demo_report(results.base64, results.origin.size);
	           		img_index++;
	           	}
	           	
	           	console.log(upfile_data);
	           	
	           }
	       });
		}else{
			$.showErr("上传的文件格式有误");
		}
	});



	
    $('#start_upload').bind('click',function(){
    	var is_pass=1;
    	var dp_points=$("input[name^='dp_points']");
    		$.each(dp_points,function(i,obj){	
    			if($(obj).val()==0){
    				alert('请为'+$(obj).attr('dp-name')+'！');
    				is_pass=0;
    				return false;
    			}
    		});
    	if(is_pass==1){

	    	if($("textarea[name='content']").val()==''){
	    		alert('请填写你的宝贵意见！');
	    		is_pass=0;
	    		return false;
	    	}
    	}
    		
		if(is_pass==0){
			return false;
		}


    	var url=$('#review_form').attr('action');
    	
		 var query = new Object();
		 query.location_id = $("input[name='location_id']").val();
		 query.order_id = $("input[name='order_id']").val();
		 
		dp_points=$("input[name^='dp_points']");
		
		var p_arr=new Array();
		$.each(dp_points,function(i,obj){
		var id=obj.id;
		p_arr[id]=obj.value;
			
		})

		query.dp_points=p_arr;

    	 query.content = $("textarea[name='content']").val(); 
    	 query.img_data=upfile_data;
    	$.ajax({
			url:url,
			data:query,
			type:'post',
			dataType:'json', 
			success:function(data){
			
				if(data.status==1){
					$.showSuccess(data.info,function(){
						if(data.jump){
							location.href=data.jump;
						}
					});
					
				}else{
					alert(data.info);
				}
			}
    	});
    	   
          
    });

	
});



function del_img_box(o){
	var index = $(o).parents('.review_unit').attr('data-index');
	$(o).parents('.review_unit').remove();
	upfile_data[index] = '';
}




/*图片base64 数组*/
var upfile_data = new Array();
function demo_report(base64,size) {
    var img = new Image();

    if(size === 'NaNKB') size = '';
    if(size>0){
    	var span_html = '<span class="item_span" style="background-image: url('+base64+');background-size: cover;background-position: 50% 20%;background-repeat: no-repeat;"></span><a class="close-btn" href="javascript:void(0);" onclick="del_img_box(this)"><i class="iconfont">&#xe608;</i></a>';
    	$("#image-lib .review_unit").last().html(span_html);
    //	$(".img_load").removeClass('img_load');
    	
    }
}

