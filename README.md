# ques
问卷调查系统
后台需求: file:///home/deng/下载/778223872.jpg
用户原型: https://free.modao.cc/app/dJWhyJkMarsSNaKsluyij2QriB0Qnwo?simulator_type=device&sticky#screen=sA02BCC61271494817734586

http://ques.dd01.work/index.php/Admin 管理后台
http://ques.dd01.work/index.php/Shop  门店模块
http://ques.dd01.work/index.php/Weixin 小程序接口

原型:
    https://free.modao.cc/app/dJWhyJkMarsSNaKsluyij2QriB0Qnwo?simulator_type=device&sticky#screen=sk7w8etbw78rwsx

部署:
    修改index.php 的 WEB_PATH 为网站的域名
    默认账号密码: admin/admin
    
    1. 创建数据库, 详见根目录database.sql
    2. 修改配置文件: Application/Common/Conf/config.php
    3. 日志和图片保存目录增加可写权限: 
        Application/Runtime
        Public/Uploads
    4. 管理后台目录和路径:
        ./Application/Home
        http://ques.dd01.work/index.php/Home/
    5. 小程序接口目录和路径:
        ./Application/Weixin
        http://ques.dd01.work/index.php/Weixin/
    6. 小程序代码：
        ./ques