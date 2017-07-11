<template>
  <div>
    <div v-if="mode === 'browse'">
      <div class="answers">
        <div v-for="(answer, index) in answers" :class="{answer: true, correct: answer.correct}" @click="setCorrect(answer)"><b>{{ index + 1 }})</b> {{ answer.answer }}</div>
      </div>
    </div>
    <div v-else-if="mode === 'edit'">
      <label>{{ view.title }}:</label><br>
      <div class="answers">
        <div v-for="(answer, index) in answers" :class="{answer: true, correct: answer.correct}" @click="setCorrect(answer)"><b>{{ index + 1 }})</b> {{ answer.answer }}</div>
      </div>
    </div>
    <transition name="fade">
        <spinner v-show="loading" message="Минутку..."></spinner>
    </transition>
  </div>
</template>

<script>
import Spinner from '@/components/common/Spinner'

export default {
  name: 'answers',
  components: { Spinner },
  props: ['mode', 'view'],
  data () {
    return {
      loading: false,
      answers: []
    }
  },
  created () {
    this.answers = this.view.value.answers
  },
  methods: {
    setCorrect (answer) {
      if (this.loading) return false

      this.loading = true

      this.$http.post('/plugins/answers/' + answer.id).then(response => {
        let data = response.data

        if (data.answers) {
          this.answers = data.answers
        }

        this.loading = false
      })
    }
  }
}
</script>

<style scoped>
.answers {
  width: 15rem;
}

.answer {
  cursor: pointer;
}

.answer:hover {
  background-color: lightskyblue;
}

.correct {
  background-color: lawngreen;
}
</style>
