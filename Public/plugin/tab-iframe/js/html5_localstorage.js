//JS操作localStorage本地存储(html5支持)

//localStorage - 没有时间限制的数据存储
//localStorage 方法存储的数据没有时间限制。第二天、第二周或下一年之后，数据依然可用。

//sessionStorage - 针对一个 session 的数据存储
//sessionStorage 方法针对一个 session 进行数据存储。当用户关闭浏览器窗口后，数据会被删除。

//设置本地sessionStorage
function set_session(name, value){
    return localStorage[name] = value;
}

//获取本地sessionStorage
function get_session(name){
    if(localStorage[name] !== undefined){
        return localStorage[name];
    }
    return null;
}

//删除本地sessionStorage
function del_session(name){
    return localStorage.removeItem(name);
}

//清空本地sessionStorage
function clear_session(){
    return localStorage.clear();
}