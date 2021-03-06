// pages/scan/scan.js
let router = require("../../utils/router.js")
let store = require("../../utils/store.js")
let Api = require("../../http/api.js")
let App = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    type: store.getItem("type"),
    shop_id: store.getItem("userInfo").shop_id,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    App.doLogin().then((value) => {
      that.setData({
        type: store.getItem("type"),
        shop_id: store.getItem("userInfo").shop_id,
      })
      this.doJump(options);
    })
  },
  doJump:function (options) {
    var that = this;
    if (options != null) {
      //todo 解析需调整

      /**
       * 1 进入页面先检查是否有携带扫码参数
       *   有携带参数,判断app.type,1-访客 2-员工
       *   访客跳转至page/ques/ques,员工page/user/user
       */
      if ('shop_id' in options === true
        && 'ques_id' in options === true
        && 'flag' in options === true) {
        if (that.data.type == 1) {//访客
          //如果是员工二维码,先绑定为该门店的员工
          if (options.flag == 1) {
            var result = App.post(Api.bindShop, { shop_id: options.shop_id })
            result.then((value) => {
              //绑定完后，更新store
              store.setItem("type", 2);
              var userInfo = store.getItem('userInfo');
              userInfo.type = 2;
              store.setItem('userInfo', userInfo);
              //跳转到答卷
              router.push("ques", { query: { shop_id: options.shop_id, ques_id: options.ques_id } })
            })
          }
          //跳转到答卷
          router.push("ques", { query: { shop_id: options.shop_id, ques_id: options.ques_id } })
        } else if (that.data.type == 2) {//员工
          if (options.flag == 1) {//员工二维码
            if (that.data.shop_id != options.shop_id) {
              wx.showToast({
                title: '已是其他门店员工',
                mask: true,
                icon: "none"
              })
              return
            }
          }

          if (that.data.shop_id != options.shop_id) {//其他门店二维码
            //跳转到答卷
            router.push("ques", { query: { shop_id: options.shop_id, ques_id: options.ques_id } })
          }
        }
      }

      /**
       * 2 判断无扫码参数进入时,判断用户是否是员工
       *   是员工，跳转至page/user/user,访客停留在当前页面
       */
      if (that.data.type == 2) {
        router.push("user")
      }
    }
  },
  scan:function() {
    var that = this;
    var show;
    wx.scanCode({
      onlyFromCamera: true,
      scanType: ["barCode", "qrCode", "datamatrix", "pdf417"],
      success: (res) => {
        if (path in res === false) {
          wx.showToast({
            title: '二维码不适用',
          })
          return
        }
        var path = '/' + res.path;
        if (router.routerPath[path]) {
          wx.reLaunch({
            url: res.path,
          })
        } else {
          wx.showToast({
            title: '二维码不适用',
          })
        }
      }
    })
  }
})