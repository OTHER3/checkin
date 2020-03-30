let router = require("../../utils/router.js")
let store = require("../../utils/store.js")
let Api = require("../../http/api.js")
let App = getApp();
// pages/user/user.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    name : App.data.name,
    type : App.data.type,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  },
  onShow:function(options) {
    var that = this;
    var result = App.get(Api.userInfo);
    result.then((res) => {
      store.setItem("userId", res.id);
      store.setItem("openId", res.openId);
      store.setItem("token", res.token);
      store.setItem("name", res.name);
      store.setItem("type", res.type);
      store.setItem("userInfo", res);
      that.setData({
        name: res.name,
        type: res.type,
      })
    })
  },
  gotoTemp: function() {
    router.push("temperature")
  },
  gotoQues: function() {
    var userInfo = store.getItem("userInfo")
    //如果用户已经有本门店,则直接跳记录页面
    if (userInfo.shop_id > 0) {
      router.push("ques", {query: {shop_id : userInfo.shop_id, ques_id : 0}})
    } else {
      //否则跳扫码页面
      router.push("scan")
    }
  }

})