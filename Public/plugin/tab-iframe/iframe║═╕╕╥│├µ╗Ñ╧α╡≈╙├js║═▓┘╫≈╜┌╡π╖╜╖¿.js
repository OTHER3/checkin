//使用注意：必须放在网站环境下才有效，直接在浏览器打开本地文件方式是无效的！！！


//父页面调用iframe里面的方法 (ifram必须有id属性，childFunc = 子页面的函数名)
document.getElementById('iframeId').contentWindow.childFunc();
//iframe页面调用父页面的方法 (funcName = 父页面的函数名)
window.parent.funcName();


//在父页面获取子页面对象(用于操作js)
function getChildObj(iframeId){
    //返回原生js对象，直接使用即可
    return document.getElementById(iframeId).contentWindow;
}
//在子页面获取父页面对象(用于操作js)
function getParentObj(){
    //返回原生js对象，直接使用即可
    return window.parent;
}


//在父页面获取子页面文档对象(用于操作节点)
function getChildDocument(iframeId){
    return document.getElementById(iframeId).contentWindow.document;
}
//在子页面获取父页面文档对象(用于操作节点)
function getParentDocument(){
    return window.parent.document;
}
