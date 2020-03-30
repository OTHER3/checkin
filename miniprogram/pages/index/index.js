//index.js
//获取应用实例
let router = require('../../utils/router.js');
const app = getApp()

Page({
  data: {
    motto: 'Hello World',
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo')
  },
  onLoad: function(option) {
    router.push('scan', { query: {shop_id : 2, ques_id : 0, flag : 0}})
  }
})
