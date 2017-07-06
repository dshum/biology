<template>
  <transition name="show">
    <div class="plugin" v-if="show">
      <statistic :classId="classId" v-if="classId === 'App.ServiceSection.9'"></statistic>
    </div>
  </transition>
</template>

<script>
import Statistic from '@/components/plugins/Statistic'

export default {
  name: 'browse-plugin',
  props: ['classId'],
  components: { Statistic },
  data () {
    return {
      show: false
    }
  },
  watch: {
    classId (to, from) {
      this.show = false

      this.$http.get('/browse/root').then((response) => {
        this.show = true
      })
    }
  },
  created () {
    this.$http.get('/browse/root').then((response) => {
      this.show = true
    })
  },
  methods: {

  }
}
</script>

<style scoped>

</style>
