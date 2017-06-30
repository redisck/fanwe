$(document).ready(function(){
	if(getCookie("cancel_geo")!=1)
	{
		if(navigator.geolocation)
		{
			 var geolocationOptions={timeout:10000,enableHighAccuracy:true,maximumAge:5000};		 
			 navigator.geolocation.getCurrentPosition(getPositionSuccess, getPositionError, geolocationOptions);
		}
	}	
});




function getPositionSuccess(p){  
	has_location = 1;//定位成功; 
    m_latitude = p.coords.latitude; //纬度
    m_longitude = p.coords.longitude;
	userxypoint(m_latitude, m_longitude);
}

function getPositionError(error){  
	switch(error.code){  
	    case error.TIMEOUT:  
	        alert("定位连接超时，请重试");  
	        break;  
	    case error.PERMISSION_DENIED:  
	        alert("您拒绝了使用位置共享服务，查询已取消");  
	        break;  
	    default:
	    	alert("定位失败");		       
	}  
}	 
//将坐标返回到服务端;
function userxypoint(latitude,longitude){	 	
		var query = new Object();
		query.m_latitude = latitude;
		query.m_longitude = longitude;
		$.ajax({
			url:geo_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status==0)
				{
					$.showConfirm("当前城市是["+data.city.name+"],是否切换到"+data.city.name+"站？",function(){
						location.href = city_url+"&city_id="+data.city.id;
					},function(){						
						setCookie("cancel_geo",1,1);							
					});
				}
			}
			,error:function(){					
			}
		});		 		
} 	


function setCookie(name, value, iDay){   

    /* iDay 表示过期时间   

    cookie中 = 号表示添加，不是赋值 */   

    var oDate=new Date();   

    oDate.setDate(oDate.getDate()+iDay);       

    document.cookie=name+'='+value+';expires='+oDate;

}

function getCookie(name){

    /* 获取浏览器所有cookie将其拆分成数组 */   

    var arr=document.cookie.split('; ');  

    

    for(var i=0;i<arr.length;i++)    {

        /* 将cookie名称和值拆分进行判断 */       

        var arr2=arr[i].split('=');               

        if(arr2[0]==name){           

            return arr2[1];       

        }   

    }       

    return '';

}