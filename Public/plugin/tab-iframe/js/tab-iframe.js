$(function(){

    //多标签开关
    tab.multi_page = false;

    //tab初始化
    tab.init();

    //点击添加
    $(document).on('click', '.tab-btn', function(){
        var url = $(this).attr('data-url');
        var title = $(this).text();
        tab.add(title, url);
    });

    //点击关闭
    $(document).on('click', '.tab-remove', function(){
        //阻止冒泡
        event.stopPropagation();
        tab.close($(this).parent());
        return false;
    });

    //点击激活
    $(document).on('click', '#tab-title ul li', function(){
        tab.active($(this));
    });
});

/**
 * tab-iframe 多选项卡打开多页面
 * 1、添加：add方法，传入标题名称、要打开的链接，可从tab页面去打开新的tab标签
 * 2、关闭：close方法，传入当前选项卡对象
 * 3、激活：active方法，传入当前选项卡对象
 * 4、初始化：将当前已打开的选项卡记录到cookie或localstorage中，刷新页面时自动打开历史的标签(记录时间，打开不超过2小时的记录)
 * 
 * 节点说明：
 * 1、触发打开tab：<!-- 打开tab的节点，带上class="tab-btn"和：data-url="链接地址"即可 -->
 * 
 * 2、tab标题容器(默认页面，不允许关闭，也可自定义)：
 * 	<div id="tab-title">
		<ul class="list-unstyled">
			<li class="tab-default active">默认页面</li>
		</ul>
	</div>

 * 3、tab内容容器(默认页面)：
 * 	<div id="tab-content"><!-- 内容，注意：此容器样式要按照自己的页面去设置宽高，需要自适应可用绝对定位实现 -->
		<div class="tab-iframe tab-default active">	<!-- 默认显示，带上tab-default和active类 -->
			<iframe id='' class="iframe" name='' src='http://www.htmleaf.com/'></iframe>
		</div>
	</div>
 */
var tab = {

    multi_page: false,

    //记录当前添加的tab的id
    id: 0,

    //记录打开的tab数据
    data: {},

    //初始化（刷新页面默认打开原来tab标签）
    init: function (){

        var that = this;
        var now = new Date().getTime();
        //将字符串转换为json对象
        var data = JSON.parse(get_session('tab'));
        
        //判断tab记录时间距离现在的数据如果超过了2小时，则不再恢复
        if(data == null || now - data.timestamp > 2*60*60*1000){
            return true;
        }

        //如果多标签开关为false，只恢复默认标签
        if (this.multi_page === false) {
            //进行恢复
            $.each(data, function(index, value){
                if(index !== 'tab-default'){
                    return false;
                }
                $('#tab-title .tab-default').text(value.title);
                $('#tab-content .tab-default .iframe').attr('src', value.url);
            });
            return true;
        }

        //进行恢复
        $.each(data, function(index, value){
            if(index !== 'active' && index !== 'timestamp'){
                that.add(value.title, value.url);
            }
        });

        //恢复激活，不存在则激活默认选项卡
        if(data.active == undefined){
            var activeObj = $('#tab-title ul li').eq(0);
            this.active(activeObj);
            return true;
        }
        var activeObj = $('#tab-title ul li[data-id=' + data.active + ']');
        this.active(activeObj);
    },

    //将当前的tab数据保存到localstorage
    save: function (){
        //由于localstorage只支持保存字符类型，所以要将对象转换为json字符串
        this.data['timestamp'] = new Date().getTime();
        set_session('tab', JSON.stringify(this.data));
    },

    //添加（已存在则执行激活）
    add: function (title, url) {
        
        //单标签开关打开，所有页面都在默认页面打开
        if (this.multi_page === false) {
            $('#tab-title .tab-default').text(title);
            $('#tab-content .tab-default .iframe').attr('src', url);

            //将打开的tab数据记录到localstorage，用于刷新还原
            this.data['tab-default'] = {
                url : url,
                title : title,
            };
            this.save();
            return true;
        }

        //判断tab数量是否达到容器可以显示数量的最大值
        //如果当前已打开过次url，则激活对应tab
        var obj = $('#tab-title ul li[data-url="'+ url +'"]');
        if($(obj).length > 0){
            this.active(obj);
            return true;
        }

        //将打开的tab数据记录到localstorage，用于刷新还原
        this.data['tab-id-'+this.id] = {
            url : url,
            title : title,
        };
        this.data['active'] = 'tab-id-' + this.id;
        this.save();
        
        //添加标签
        var tab_title_node = "<li class=\"active\" data-url=\"" + url + "\" data-id=\"tab-id-" + this.id + "\">";
        tab_title_node += title +"<span class=\"tab-remove\">×</span></li>";
        $('#tab-title ul li').removeClass('active');
        $('#tab-title ul').append(tab_title_node);

        //添加iframe
        var tab_content_node = "<div class=\"tab-iframe active\" data-id=\"tab-id-" + this.id + "\">";
		tab_content_node += "<iframe class=\"iframe\" src='" + url +"'></iframe></div>";
        
        //去除已激活的，把新添加的激活
        $('#tab-content .tab-iframe').removeClass('active');
        $('#tab-content').append(tab_content_node);

        //记录当前添加的tab的id
        this.id++;
    },

    //关闭
    close: function (that){

        //把当前关闭的tab标题和对应tab内容remove
        var url = $(that).attr('data-url');
        var id = $(that).attr('data-id');
        var prev = $(that).prev();
        $(that).remove();
        $('#tab-content .tab-iframe[data-id='+ id +']').remove();

        //将关闭的tab从localstorage记录中移除
        delete this.data[id];
        this.save();

        //如果当前关闭的节点是激活状态，则把上一个tab显示
        if($(that).hasClass('active')){
            this.active(prev);
        }
    },

    //激活
    active: function (that){

        //判断是否已经是激活状态
        if($(that).hasClass('active')){
            return true;
        }

        //把已激活的节点隐藏
        $('#tab-title ul li').removeClass('active');
        $('#tab-content .tab-iframe').removeClass('active');

        //把当前节点激活
        $(that).addClass('active');
        var id = $(that).attr('data-id');
        $('#tab-content .tab-iframe[data-id='+ id +']').addClass('active');

        //记录当前激活的tab的id，以及激活时间，时间用于判断是否恢复历史在打开的tab选项卡(2个小时内)
        this.data['active'] = id;
        this.save();

        //判断是否为默认
        if($(that).hasClass('tab-default')){
            $('#tab-content .tab-default').addClass('active');
        }
    },

}