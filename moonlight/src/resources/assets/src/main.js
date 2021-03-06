import Vue from 'vue'
import App from './App'
import router from './router'
import Hotkeys from './hotkeys'
import EventBus from './eventBus'
import 'jquery/dist/jquery.min.js'
import './assets/css/app.scss'

require('./filters')

Vue.use(Hotkeys)
Vue.use(EventBus)

EventBus.init()

Vue.config.productionTip = false

Vue.http.interceptors.push((request, next) => {
  let loggedUser = JSON.parse(localStorage.getItem('loggedUser'))
  let token = loggedUser && loggedUser.token
  let hasAuthHeader = request.headers.has('Authorization')

  if (token && !hasAuthHeader) {
    request.headers.set('Authorization', 'Bearer ' + token)
  }

  request.url = process.API_URL + request.url

  next((response) => {

  })
})

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  render: h => h(App)
})
