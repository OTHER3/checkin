/*折叠菜单代码*/
$(function(){
	$('.slideMenu > li > a').click(function(){
		//得到li下的子元素ul
		var ulNodes = $(this).parent().find('ul');
		if( $(ulNodes).length > 0 ){
			//对ul节点进行显示隐藏切换(有动画效果)
			if( $(ulNodes).is(':hidden') ){
				$(ulNodes).slideDown(300);
			}else{
				$(ulNodes).slideUp(300);
			}
		}
	});
});