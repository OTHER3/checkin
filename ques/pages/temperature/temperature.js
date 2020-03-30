// pages/temperature/temperature.js
let Api = require("../../http/api.js");
const app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    hideModal: true, //模态框的状态  true-隐藏  false-显示
    animationData: {},//
    temperature: 36, //体温默认值
    list: [],
    page: 1,
    limit: 20,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.lists();
  },

  lists() {
    var that = this;
    var param = {
      page : that.data.page,
      limit : that.data.limit,
    }

    that.setData({
      page: param.page + 1
    })
  
    var result = app.get(Api.temperatureList, param)
    result.then((value) => {
      value = that.data.list.concat(value);
      that.setData({
        list: value
      })
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.lists();
  },
  // 显示遮罩层
  showModal: function () {
    var that = this;
    that.setData({
      hideModal: false
    })
    var animation = wx.createAnimation({
      duration: 400,//动画的持续时间 默认400ms   数值越大，动画越慢   数值越小，动画越快
      timingFunction: 'ease',//动画的效果 默认值是linear
    })
    this.animation = animation
    setTimeout(function () {
      that.fadeIn();//调用显示动画
    }, 100)
  },

  // 隐藏遮罩层
  hideModal: function () {
    var that = this;
    var animation = wx.createAnimation({
      duration: 800,//动画的持续时间 默认400ms   数值越大，动画越慢   数值越小，动画越快
      timingFunction: 'ease',//动画的效果 默认值是linear
    })
    this.animation = animation
    that.fadeDown();//调用隐藏动画   
    setTimeout(function () {
      that.setData({
        hideModal: true
      })
    }, 720)//先执行下滑动画，再隐藏模块

  },

  //动画集
  fadeIn: function () {
    this.animation.translateY(0).step()
    this.setData({
      animationData: this.animation.export()//动画实例的export方法导出动画数据传递给组件的animation属性
    })
  },
  fadeDown: function () {
    this.animation.translateY(600).step()
    this.setData({
      animationData: this.animation.export(),
    })
  },
  slider: function (e) {
    this.setData({
      temperature: e.detail.value
    })
  },
  submit: function(obj) {
    var that = this;
    var temperature = obj.target.dataset.value

    var result = app.post(Api.temperature, {temp : temperature});
    result.then((value, value2) => {
      that.setData({
        page: 1,
        list: []
      })
      this.lists();
    })
  }
})