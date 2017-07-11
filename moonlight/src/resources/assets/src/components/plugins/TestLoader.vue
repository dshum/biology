<template>
  <div>
    <div class="ok" v-if="ok">
      Тест успешно создан!
    </div>
    <form @submit.prevent="submit()">
      <div class="row">
        <label>Название теста:</label><span name="title" class="error"></span><br>
        <input type="text" name="first_name" v-model="model.title" placeholder="Название теста">
      </div>
      <div class="row">
        <label>Тема:</label><span name="topic" class="error"></span><br>
        <select name="topic" v-model="model.topic">
          <option :value="null" selected>- Выберите тему -</option>
          <option v-for="topic in topics" :value="topic.id">{{ topic.name }}</option>
        </select>
      </div>
      <div class="row">
        <label>Скопируйте текст с вопросами и вариантами ответов.<br>
        Вопросы должны разделяться двумя пустыми строками;<br>
        вопрос от ответов отделяется одной пустой строкой.</label><span name="content" class="error"></span><br>
        <textarea name="content" rows="15" v-model="model.content"></textarea>
      </div>
      <div class="row submit">
        <input type="submit" value="Загрузить" class="btn">
      </div>
    </form>
    <transition name="fade">
      <spinner v-show="loading" message="Минутку..."></spinner>
    </transition>
  </div>
</template>

<script>
import Spinner from '@/components/common/Spinner'
import Alert from '@/components/common/Alert'

export default {
  name: 'test-loader',
  components: { Spinner, Alert },
  props: ['classId'],
  data () {
    return {
      loading: false,
      ok: false,
      errors: [],
      topics: [],
      model: {
        title: null,
        topic: null,
        content: null
      }
    }
  },
  created () {
    this.$http.get('/plugins/testloader').then(response => {
      let data = response.body

      if (data.topics) {
        this.topics = data.topics
      }
    })
  },
  methods: {
    submit () {
      this.loading = true
      this.ok = false

      $('.error').hide()

      this.$http.post('/plugins/testloader', this.model).then(response => {
        let data = response.body

        if (data.errors) {
          data.errors.forEach(error => {
            $('.error[name="' + error.name + '"]').html(error.message).fadeIn(200)
          })
        } else if (data.ok) {
          this.model = {
            title: null,
            topic: null,
            content: null
          }

          this.ok = true
        }

        this.loading = false

        $('.main > .container').scrollTop(0)
      })
    }
  }
}
</script>

<style scoped>
textarea {
  width: 50rem;
}

.ok {
  display: table;
  margin: 1rem 0;
  padding: 1rem;
  background-color: darkgreen;
  color: white;
  border-radius: 2px;
  font-weight: bold;
}
</style>
