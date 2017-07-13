<template>
  <div>
    <div class="path">Добро пожаловать!</div>
    <transition name="show">
      <div class="plugin" v-if="show">
        <div class="widget">
          На сайте зарегистрировано пользователей: {{ userCount }}<br>
          Добавлено тестов: {{ testCount }}<br>
          Всего вопросов: {{ questionCount }}<br>
          <br>
          Недавно зарегистрировались:<br>
          <div v-for="user in lastUsers"><router-link :to="{name: 'browse', params: {classId: user.classId}}">{{ user.email }}</router-link></div>
        </div>
        <div class="widget"><a href="https://clck.yandex.ru/redir/dtype=stred/pid=7/cid=1228/*https://yandex.ru/pogoda/213" target="_blank"><img src="//info.weather.yandex.net/213/2_white.ru.png?domain=ru" border="0" alt="Яндекс.Погода"/><img width="1" height="1" src="https://clck.yandex.ru/click/dtype=stred/pid=7/cid=1227/*https://img.yandex.ru/i/pix.gif" alt="" border="0"/></a></div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'welcome',
  data () {
    return {
      show: false,
      lastUsers: [],
      userCount: 0,
      testCount: 0,
      questionCount: 0
    }
  },
  created () {
    this.$http.get('/plugins/welcome').then(response => {
      let data = response.body

      this.lastUsers = data.lastUsers
      this.userCount = data.userCount
      this.testCount = data.testCount
      this.questionCount = data.questionCount

      this.show = true
    })
  },
  methods: {

  }
}
</script>

<style scoped>
.plugin {
  font-size: 1.2rem;
}

.widget {
  display: block;
  float: left;
  margin: 1rem 2rem 1rem 0;
  padding: 1rem 2rem;
  border: 5px dashed limegreen;
  border-radius: 5px;
}
</style>
