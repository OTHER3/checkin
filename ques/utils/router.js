/**
 * 路由跳转
 */
const routerPath = {
  "user": "/pages/user/user",
  "index": "/pages/index/index",
  "temperature": "/pages/temperature/temperature",
  "scan": "/pages/scan/scan",
  "ques": "/pages/ques/ques",
}

module.exports = {
  push(path, option = {}) {
    if (typeof path == 'string') {
      option.path = path;
    } else {
      option = path;
    }
    let url = routerPath[option.path];
    let { query = {}, openType } = option;
    let params = this.parse(query);
    if (params) {
      url += "?" + params;
    }
    this.to(openType, url);
  },
  /**
   * 路由参数处理
   */
  parse(query) {
    let arr = [];
    for (let key in query) {
      arr.push(key + "=" + query[key]);
    }
    return arr.join("&");
  },
  /**
   * 根据类型进行跳转
   */
  to(openType, url) {
      let obj = { url };
      if (openType === 'redirect') {
        wx.redirectTo(obj)
      } else if (openType === 'reLaunch') {
        wx.reLaunch(obj)
      } else if (openType === 'back') {
        wx.navigateBack({
          delta:1 
        })
      } else {
        wx.navigateTo(obj)
      }
  }
}