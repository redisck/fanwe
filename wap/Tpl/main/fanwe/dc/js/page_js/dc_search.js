$(document).ready(function () { 
	
	$("#search_form").bind('submit',function(){
		return false;
	});
    
    $("#search_form .search_bottom").bind("click",function(){

    	var dc_search_url=$("form[name='search_form']").attr('action');
    	var query=new Object();
         query.keyword=$('#keyword').val();

    	$.ajax({
			url:dc_search_url,
			data:query,
			type:'post',
			dataType:'json',
			success:function(data){

				$('#search_content').html(data.html);
			}
    	});
    	
    });
    
    $('#lid_search_result table tr').live('click',function(){
    	
    	var url=$(this).attr('data-i');
    	location.href=url;
    });
	
 });  


