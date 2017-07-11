<template>
  <answers v-if="view.item.id === 'App.Question' && view.name === 'answers_info'" :mode="mode" :view="view"></answers>
</template>

<script>
import Answers from '@/components/plugins/Answers'

export default {
  name: 'property-plugin',
  components: { Answers },
  props: ['mode', 'view'],
  data () {
    return {

    }
  },
  created () {

  },
  methods: {

  }
}
</script>

<style scoped>

</style>
