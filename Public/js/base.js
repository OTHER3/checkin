$(function(){

	//选择主菜单选项，显示对应左侧菜单
	$('#top-list ul li').click(function(){

		//判断是否已经是选中状态
		if($(this).hasClass('top-list-selected')){
			return false;
		}

		//移除已选中的，将自己选中
		$('.top-list-selected').removeClass('top-list-selected');
		$(this).addClass('top-list-selected');

		//移除已显示的左侧菜单，把对应的显示
		var data_list = $(this).attr('data-list');
		//如果该选项没有data-list属性，则不进行操作
		if(!data_list){
			return false;
		}
		$('.left-item-selected').removeClass('left-item-selected');
		$('.left-item[data-list='+ data_list +']').addClass('left-item-selected');

	});

});