//文件上传
var AjaxUpload = {

    //上传文件url
    uploadURL: '',
    //合并文件url
    mergeURL: '',

    /**
     * 文件上传，可传入4个参数：文件对象、指定每分片大小、允许同时最大上传分片数
     * 文件对象：必须是文件对象(测试上传5-10G文件正常，理论上传文件大小无限制)
     * 每分片大小：默认值2M(后台上传的限制，PHP默认2M，可通过修改后台配置同时进行修改)
     * 允许同时最大上传分片数：默认值3(测试最佳数量：3-5)
     * 指定开始上传的索引（用于断点续传）
     */
    init: function (file, sliceSize, maxUploadNum, startIndex) {

        /* 初始化参数 */
        file = file ? file : null;                      //文件对象
        sliceSize = sliceSize ? sliceSize : 1;          //每分片大小
        maxUploadNum = maxUploadNum ? maxUploadNum : 3; //允许同时最大上传分片数
        startIndex = startIndex ? startIndex : 0;       //指定开始上传的索引（用于断点续传）

        //判断传入file是否为对象
        if (typeof file !== 'object') {
            alert('请传入文件对象！');
            return false;
        }
        if (this.uploadURL == '') {
            alert('请配置上传URL！');
            return false;
        }
        if (this.mergeURL == '') {
            alert('请配置合并文件URL！');
            return false;
        }

        /* 分片参数 */
        var index = startIndex; //文件切割分片指针，默认从0开始
        var total = Math.ceil(file.size / (1024 * 1024 * sliceSize));   //计算分片总数
        var uploadURL = this.uploadURL;
        var mergeURL = this.mergeURL;

        /* 上传参数 */
        var uniName = new Date().getTime();	                        //唯一文件名（可自行指定，断电续传需要前后一致）
        var uploadingNum = 0;				                        //正在上传的文件个数
        var uploadedNum = (startIndex > 0) ? (startIndex - 1) : 0;  //成功上传总数（兼容断点续传）
        var uploadedIndexs = [];			                        //成功上传分片Index数组
        var uploadProgress = Math.ceil(uploadedNum / total * 100);  //上传进度百分比：默认为0

        /* 合并参数 */
        var mergeIndex = startIndex;		//文件合并指针，默认为0
        var mergeFlag = true;				//允许合并标识

        //开始上传和合并（使用定时器循环执行，也可以用递归）
        up = setInterval(function () {

            //上传
            if (uploadingNum < maxUploadNum && index < total) {

                //计算切割分片的起始与结束位置
                var start = index * (1024 * 1024 * sliceSize);
                var end = Math.min(file.size, start + (1024 * 1024 * sliceSize));

                //构造一个表单，FormData是HTML5新增的
                var form = new FormData();
                form.append("file", file.slice(start, end));  //slice方法用于切出文件的一部分
                form.append("name", file.name);		//old文件名
                form.append("uniName", uniName);	//uni文件名
                form.append("action", 'upload');	//操作标识：上传
                form.append("total", total);   		//总片数
                form.append("index", index);        //当前分片

                //Ajax提交
                $.ajax({
                    url: uploadURL,
                    type: "POST",
                    data: form,
                    dataType: 'json',
                    async: true,       	 //异步
                    processData: false,  //很重要，告诉jquery不要对form进行处理
                    contentType: false,  //很重要，指定为false才能形成正确的Content-Type
                    success: function (json) {
                        //出现异常马上中断上传，并提示信息
                        if (json.status != 1000) {
                            clearInterval(up);
                            $('.status').text(json.message);
                            return true;
                        }

                        //累计上传成功个数
                        uploadedNum++;
                        //计算上传进度
                        uploadProgress = Math.ceil(uploadedNum / total * 100);
                        //显示进度
                        $('.status').text(uploadProgress + '%');

                        if (uploadingNum > 0) {
                            //把正在上传个数-1
                            uploadingNum--;
                        }

                        //把已成功上传的分片index记录到succeedIndexs数组
                        uploadedIndexs.push(json.index - 0);
                    }
                });

                //累计正在上传个数
                uploadingNum++;
                //文件指针往下移动
                index++;
            }

            //合并(三个条件：允许合并标识为真、当前合并index已上传成功、index在总数范围内)
            if (mergeFlag == true && jQuery.inArray(mergeIndex, uploadedIndexs) >= 0 && mergeIndex < total) {

                //关闭合并
                mergeFlag = false;

                $.ajax({
                    url: mergeURL,
                    type: "POST",
                    data: {
                        action: 'merge',
                        name: file.name,
                        uniName: uniName,
                        index: mergeIndex,
                        total: total,
                    },
                    dataType: 'json',
                    success: function (json) {
                        //异常：停止合并，弹出错误信息
                        if (json.status != 2000) {
                            clearInterval(up);
                            $('.status').text(json.message)
                        }

                        //所有分片上传完，显示正在合并
                        if (uploadProgress == 100) {
                            $('.status').text('正在处理...');
                        }

                        //分片合并完成
                        if (mergeIndex == (total - 1)) {

                            //关闭定时器
                            clearInterval(up);

                            //清空文件域，file文件域使用$('xxx').val('')的清空方式有bug，使用节点克隆
                            var fileHtml = $('input[type=file]');
                            $(fileHtml).after($(fileHtml).clone().val(''));
                            $(fileHtml).eq(0).remove();

                            $('.status').text('上传成功！');
                            upload_callback(json);
                            return true;
                        }

                        //合并指针移动到下一个分片
                        mergeIndex++;
                        //开启合并
                        mergeFlag = true;
                    }
                });
            }
        }, 200);
    }
}
