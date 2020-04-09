// pages/ques/ques.js
let Api = require("../../http/api.js");
const store = require("../../utils/store.js");
const app = getApp();
const router = require("../../utils/router.js");
Page({
  /**
   * 页面的初始数据
   */
  data: {
    type: store.getItem("type"),
    shop_id : 1,
    ques_id : 0,
    shop_name: '',
    unEdit: false,//是否不可编辑
    visit : {//展示的问卷相关信息
      name: '',
      age: '',
      sex: 1,
      mobile: '',
      id_card: '',
      topic: [],
      created_at:'',
    },
    prom: false,
    agree: false,
    switch_confirm: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    that.setData({
      type: store.getItem("type"),
      shop_id: options.shop_id,
      ques_id: options.ques_id,
    })
    //获取用户登记过的信息
    var param = {
      shop_id: that.data.shop_id,
      ques_id: that.data.ques_id,
    }

    var result = app.get(Api.getShopName, {shop_id : that.data.shop_id})
    result.then((value) => {
      that.setData({
        shop_name: value
      })
    })

    // 获取用户是否已填写信息
    var result = app.get(Api.userTopic, param);
    result.then((value) => {
      var unEdit = that.data.unEdit;
      var visit = that.data.visit;

      //有填写信息，将用户原填写的信息填充进去
      if (value != null) {
        visit.name = value.name;
        visit.age = value.age;
        visit.sex = value.sex;
        visit.mobile = value.mobile;
        visit.id_card = value.id_card;
        visit.topic = value.topic;
        visit.created_at = value.created_at;
        unEdit = true;
        //将展示的内容填入
        that.setData({
          visit: visit,
          unEdit: unEdit,//控制是否可以编辑
        })
        if (that.data.type == 1) {//访客身份需弹框
          that.setData({
            switch_confirm: true
          })
        }
      } else {
        //没有，则获取问卷列表信息
        var param = {
          ques_id: that.data.ques_id,
        }
        var result = app.get(Api.getTopic, param)
        result.then((value) => {
          visit.topic = value;
          //将展示的内容填入
          that.setData({
            visit: visit,
            unEdit: unEdit,//控制是否可以编辑
          })
        })
      }
    })
  },
  sub:function(event) {
    var that = this;
    if (this.data.prom == false) {
      wx.showToast({
        title: '请勾选承诺',
        mask: true,
        icon: "none"
      })
      return
    }
    if (this.data.agree == false) {
      wx.showToast({
        title: '请同意《用户服务协议》及《隐私政策》',
        mask: true,
        icon: "none"
      })
      return
    }
    var param = {
      shop_id: that.data.shop_id,
      ques_id: that.data.ques_id,
      form_data: event.detail.value
    }
    var result = app.post(Api.addTopic,  JSON.stringify(param));
    result.then((value) => {
      that.onLoad({shop_id: that.data.shop_id, ques_id: that.data.ques_id})
    })
  },
  prom:function(event) {
    var prom = event.detail.value.length
    this.setData({
      prom: prom == 1 ? true : false,
    })
  },
  agreement:function(event) {
    var agree = event.detail.value.length
    this.setData({
      agree: agree == 1 ? true : false,
    })
  },
  gotoAgreement:function() {
    router.push("agreement")
  },
  confirm:function() {
    this.setData({
      switch_confirm: false
    })
  }
})