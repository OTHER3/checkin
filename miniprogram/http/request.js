/**
 * 网络请求的公共方法
 */

//自定义固定请求头数据，如用户端/版本号等等
const store = require("../utils/store.js");

module.exports = {
  fetch:(url, data = {}, option ={}) => {
    let { loading = true, toast = true, method = 'get' } = option;
    return new Promise((resolve, reject) => {
      if (loading) {
        wx.showLoading({
          title: '请稍等...',
          mask: true
        })
      }
      let env = App.config.baseApi;
      var clientInfo = {
        token: store.getItem("token"),
        user_id: store.getItem("userId")
      }
      wx.request({
        url: env + url,
        data,
        method,
        header: {
          "Content-Type": "application/x-www-form-urlencoded",
          "clientInfo": JSON.stringify(clientInfo)
        },
        success: function (result) {
          let res = result.data;
          if (res.code == 0) {
            if (loading) {
              if (method == 'post') {
                wx.showToast({
                  title: '操作成功',
                  mask: true,
                  icon: "none"
                })
              } else {
                wx.hideLoading();
              }
            }
            resolve(res.data);
          } else {
            if (toast) {
              wx.showToast({
                title: res.message,
                mask: true,
                icon: "none"
              })
            } else {
              wx.hideLoading();
            }
          }
        },
        fail:function(e = {code:-1, msg:errMsg, errMsg}) {
          let msg = e.errMsg;
          if (msg == "request:fail timeout") {
            msg = "请求超时"
          }
          wx.showToast({
            title: msg,
            icon: "none"
          })
          reject(e);
        }
      })
    })
  }
}