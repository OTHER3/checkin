$(function(){
	//点击提交判断是否为空
	$('#button').click(function(){
		var adminName = $('#adminName').val();
		var password  = $('#password').val();
		if(adminName && password){
			return true;
		}else{
			//如果为空则显示提示信息
			if(!adminName){$('.name').text('帐号不能为空！');}
			if(!password) {$('.pass').text('密码不能为空！');}
			return false;
		}
	});
	//清空提示信息
	$('#adminName').click(function(){
		$('.name').text('');
	});
	//清空提示信息
	$('#password').click(function(){
		$('.pass').text('');
	});

});