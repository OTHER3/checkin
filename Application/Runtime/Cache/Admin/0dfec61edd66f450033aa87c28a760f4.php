<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
	<meta charset="UTF-8">
	<title>登录后台</title>
	<link rel="stylesheet" type="text/css" href="http://ques.dd01.work/Public/css/login.css"/>
	<script type="text/javascript" src="http://ques.dd01.work/Public/js/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="http://ques.dd01.work/Public/js/login.js"></script>
</head>
<body>

<div id="login">
	<div id="login-header">
		<div class="space"></div>
		<div class="login-img"></div>
	</div>

	<div id="login-content">
		<form action="" method="post">
			<div class="space"></div>
			<label for="adminName">
				<span class="form-title">帐号：</span>
				<input id="adminName" type="text" name="adminName">
			</label>
			<div class="space name"></div>
			<label for="password">
				<span class="form-title">密码：</span>
				<input id="password" type="password" name="password">
			</label>
			<div class="space pass"></div>

			<label for="code" id="verifyCodeLabel">
				<span class="form-title">验证码：</span>
				<input id="code" type="text" name="verifyCode">
				<img id="verifyCode" src="<?php echo U('Login/verifyCode');?>" alt="" onclick="refreshVerifyCode()">
			</label>
			<div class="space"></div>

			<div class="buttons">
				<!-- <button type="button" id="register" onclick="toRegister()">注册帐号</button> -->
				<button id="button" type="button" onclick="login(this)">登录后台</button>
			</div>

			<div class="space"></div>
		</form>
	</div>
</div>

<script type="text/javascript" src="http://ques.dd01.work/Public/plugin/layer-v3.0.1/layer.js"></script>
<script>

function login(that){
	$(that).attr('disabled', true);

	var data = {};
	data.account = $.trim($('input[name=adminName]').val());
	data.password = $.trim($('input[name=password]').val());
	data.verifyCode = $.trim($('input[name=verifyCode]').val());
	console.log(data);
	
	if(data.account == ''){
		layer.msg("请输入帐号!");
		$(that).attr('disabled', false);
		return false;
	}
	if(data.password == ''){
		layer.msg("请输入密码!");
		$(that).attr('disabled', false);
		return false;
	}
	if(data.verifyCode == ''){
		layer.msg("请输入验证码!");
		$(that).attr('disabled', false);
		return false;
	}
	
	$.ajax({
		url: "<?php echo U('Login/validation');?>",
		type: "post",
		data: data,
		dataType: 'json',
		success: function(json){
			$(that).attr('disabled', false);
			if(json.status == 0){
				layer.msg(json.message, {icon: 2});
				refreshVerifyCode();
				return false;
			}
			layer.msg(json.message, {icon: 1});
			setTimeout(function(){
				location.reload();
			}, 2000);
		}
	});
}

function toRegister() {
	var url = "<?php echo U('Login/register');?>";
	location.href = url;
}

function refreshVerifyCode() {
	var url = "<?php echo U('Login/verifyCode');?>";
	url += "?" + Math.random();
	$('#verifyCode').attr("src", url);
}
</script>
	
</body>
</html>