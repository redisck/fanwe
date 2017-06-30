(function($) {   
	
	//瀑布流默认配置
	$.pin_config = {
		_op:{width:225,hSpan:30,wSpan:10,isAnimate:false,speed:300,pin_col_init_height:[]}
	},
	
	$.pin_info = {
		pin_col_array:null, //列的信息	每列数据结构 {height:"",size:"",items:[]}
		pin_layout:null, //瀑布流外盒
		layout_width:null, //布局宽
		col_size:null, //列的数量
		left_array:null, //每列的左边距
		height_span:null, //顶部高margin
		items:[]  //所有子对象，即pin_box
	},

	//选项包含width(单个pin宽度),hSpan(每个pin的高度间隔),wSpan(每个pin的宽度的最小间隔，宽度间隔自动计算，但不能小于最小间隔)
	//pin_col_init_height(整型数组，每一列的初始高度),isAnimate(动画表现),speed(动画表现的速度)
	$.fn.init_pin = function(options) {
		
		var op = $.extend({},$.pin_config._op, options);
		$.pin_config._op.width = op.width;
		$.pin_config._op.hSpan = op.hSpan;
		$.pin_config._op.wSpan = op.wSpan;
		$.pin_config._op.pin_col_init_height = op.pin_col_init_height;
		$.pin_config._op.isAnimate = op.isAnimate;
		$.pin_config._op.speed = op.speed;
		$.pin_info.pin_layout = $(this); //外框盒子
		$.pin_info.layout_width = $.pin_info.pin_layout.width(); //外框总宽
		$.pin_info.col_size = Math.floor($.pin_info.layout_width/($.pin_config._op.width+$.pin_config._op.wSpan));	
		$.pin_info.pin_col_array = new Array($.pin_info.col_size);
		$.pin_info.left_array = new Array($.pin_info.col_size);
		$.pin_info.height_span = $.pin_config._op.hSpan;
		
		var span = Math.floor(($.pin_info.layout_width - $.pin_config._op.width*$.pin_info.col_size)/($.pin_info.col_size-1));
		
		var theightArray = new Array();
		//计算每列的左边距，及初始化列的数据集
		for(i=0;i<$.pin_info.col_size;i++)
		{
			if(i==0)
				$.pin_info.left_array[i] = 0;
			else
				$.pin_info.left_array[i] = ($.pin_config._op.width+span)*i;
			
			var init_height = 0;
			try
			{
				init_height = $.pin_config._op.pin_col_init_height[i];
				if(isNaN(init_height))init_height = 0;
			}
			catch(ex)
			{
				init_height = 0;
			}
			$.pin_info.pin_col_array[i] = {height:init_height,size:0,items:[]};
			theightArray[i] = $.pin_info.pin_col_array[i].height;	
		}
			

		var maxH = Math.max.apply(null,theightArray);
        var maxHIndex = $.inArray(maxH,theightArray); //找到最大高度的列的引
		$.pin_info.pin_layout.height(maxH+$.pin_info.height_span);
		
	},
	
	
	$.fn.pin = function(html){
		var pin_item = $(html);
		$.pin_info.pin_layout.append(pin_item);
		
		
		var heightArray = new Array();
		for(i=0;i<$.pin_info.col_size;i++)
		{
			heightArray[i] = $.pin_info.pin_col_array[i].height;			
		}
		
		var minH = Math.min.apply(null,heightArray);

        var minHIndex = $.inArray(minH,heightArray); //找到最小高度的列的引
        
        
        
       
        var left = $.pin_info.left_array[minHIndex];
        if($.pin_info.pin_col_array[minHIndex].height>0)
        var top = $.pin_info.pin_col_array[minHIndex].height+$.pin_info.height_span;
        else
        var top = 0;

        if($.pin_config._op.isAnimate)
        {
        	var css = {"position":"absolute","top":minH,"left":0}; //重定义样式
            pin_item.css(css);
            
            pin_item.animate({ 
            	top: top,
            	left: left
              }, $.pin_config._op.speed );
        }
        else
        {
        	var css = {"position":"absolute","top":top,"left":left}; //重定义样式
            pin_item.css(css);
        }
        

        
        if($.pin_info.pin_col_array[minHIndex].height>0)
        $.pin_info.pin_col_array[minHIndex].height+=(pin_item.height()+$.pin_info.height_span);
        else
    	$.pin_info.pin_col_array[minHIndex].height+=pin_item.height();
        $.pin_info.pin_col_array[minHIndex].size++;
        $.pin_info.pin_col_array[minHIndex].items.push(pin_item);
        $.pin_info.items.push(pin_item);
		
        
        var theightArray = new Array();
		for(i=0;i<$.pin_info.col_size;i++)
		{
			theightArray[i] = $.pin_info.pin_col_array[i].height;			
		}
		var maxH = Math.max.apply(null,theightArray);
        var maxHIndex = $.inArray(maxH,theightArray); //找到最大高度的列的引
        
        $.pin_info.pin_layout.height(maxH+$.pin_info.height_span);
        
	},
	
	$.fn.reposition = function()
	{
		$.pin_info.pin_layout = $(this); //外框盒子
		if($.pin_info.layout_width == $.pin_info.pin_layout.width())return;
		$.pin_info.layout_width = $.pin_info.pin_layout.width(); //外框总宽
		$.pin_info.col_size = Math.floor($.pin_info.layout_width/($.pin_config._op.width+$.pin_config._op.wSpan));	

		$.pin_info.pin_col_array = new Array($.pin_info.col_size);
		$.pin_info.left_array = new Array($.pin_info.col_size);
		$.pin_info.height_span = $.pin_config._op.hSpan;
		
		var span = Math.floor(($.pin_info.layout_width - $.pin_config._op.width*$.pin_info.col_size)/($.pin_info.col_size-1));
		
		var theightArray = new Array();
		//计算每列的左边距，及初始化列的数据集
		for(i=0;i<$.pin_info.col_size;i++)
		{
			if(i==0)
				$.pin_info.left_array[i] = 0;
			else
				$.pin_info.left_array[i] = ($.pin_config._op.width+span)*i;
			
			var init_height = 0;
			try
			{
				init_height = $.pin_config._op.pin_col_init_height[i];
				if(isNaN(init_height))init_height = 0;
			}
			catch(ex)
			{
				init_height = 0;
			}
			$.pin_info.pin_col_array[i] = {height:init_height,size:0,items:[]};
			theightArray[i] = $.pin_info.pin_col_array[i].height;	
		}
			

		var maxH = Math.max.apply(null,theightArray);
        var maxHIndex = $.inArray(maxH,theightArray); //找到最大高度的列的引
		$.pin_info.pin_layout.height(maxH+$.pin_info.height_span);
		
		
		//重定位
		for(idx=0;idx<$.pin_info.items.length;idx++)
		{
			var pin_item = $.pin_info.items[idx];
			var heightArray = new Array();
			for(i=0;i<$.pin_info.col_size;i++)
			{
				heightArray[i] = $.pin_info.pin_col_array[i].height;			
			}
			
			var minH = Math.min.apply(null,heightArray);

	        var minHIndex = $.inArray(minH,heightArray); //找到最小高度的列的引
	        
	        
	        
	       
	        var left = $.pin_info.left_array[minHIndex];
	        if($.pin_info.pin_col_array[minHIndex].height>0)
	        var top = $.pin_info.pin_col_array[minHIndex].height+$.pin_info.height_span;
	        else
	        var top = 0;

	        
	        if($.pin_config._op.isAnimate)
	        {
	            pin_item.animate({ 
	            	top: top,
	            	left: left
	              }, $.pin_config._op.speed );
	        }
	        else
	        {
	        	var css = {"position":"absolute","top":top,"left":left}; //重定义样式
	            pin_item.css(css);
	        }
	        
	        if($.pin_info.pin_col_array[minHIndex].height>0)
	        $.pin_info.pin_col_array[minHIndex].height+=(pin_item.height()+$.pin_info.height_span);
	        else
	    	$.pin_info.pin_col_array[minHIndex].height+=pin_item.height();
	        $.pin_info.pin_col_array[minHIndex].size++;
	        $.pin_info.pin_col_array[minHIndex].items.push(pin_item);

			
	        
	        var theightArray = new Array();
			for(i=0;i<$.pin_info.col_size;i++)
			{
				theightArray[i] = $.pin_info.pin_col_array[i].height;			
			}
			var maxH = Math.max.apply(null,theightArray);
	        var maxHIndex = $.inArray(maxH,theightArray); //找到最大高度的列的引
	        
	        $.pin_info.pin_layout.height(maxH+$.pin_info.height_span);
		}

		
	}
	
})(jQuery); 




