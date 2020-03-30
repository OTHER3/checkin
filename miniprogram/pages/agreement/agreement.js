// pages/agreement/agreement.js
let Api = require("../../http/api.js")
const App = getApp();
var WxParse = require('../../wxParse/wxParse/wxParse.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    text: "",
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var result = App.get(Api.agreement);
    result.then((value) => {
      var article = value;
      WxParse.wxParse('text', 'html', article, that, 5);
    })
  },
})