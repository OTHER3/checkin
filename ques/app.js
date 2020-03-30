let Api = require("./http/api.js")
let config = require("./env/index.js")
let request = require("./http/request.js")
let router = require("./utils/router.js")
let store = require("./utils/store.js")
let env = "Dev";
App.config = config[env];
App({
  config: config[env],
  Api,
  router,
  get: request.fetch,
  post: (url, data, option = {loading: true, toast: true, method: 'post'}) => {
    return request.fetch(url, data, option);
  },
  data: {
    userId : store.getItem("userId"),
    openId : store.getItem("openId"),
    token : store.getItem("token"),
    name : store.getItem("name"),
    type : store.getItem("type"),
    userInfo: store.getItem("userInfo"),
  },
  /**
   * 当小程序初始化完成时，会触发 onLaunch（全局只触发一次）
   */
  onLaunch: function () {
  },
  doLogin:function() {
    var that = this;
    return new Promise((resolve, reject) => {
      wx.login({
        success: res => {
          if (res.code) {
            that.get(Api.getSession, {
              code: res.code
            }).then(res => {
              store.setItem("userId", res.id);
              store.setItem("openId", res.openId);
              store.setItem("token", res.token);
              store.setItem("name", res.name);
              store.setItem("type", res.type);
              store.setItem("userInfo", res);
              resolve(res);
            }).catch(err => {
            })
          }
        }
      })
    })
  }
})
