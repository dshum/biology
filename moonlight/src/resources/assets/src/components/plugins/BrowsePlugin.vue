<template>
  <transition name="show">
    <div class="plugin" v-if="show">
      <test-loader v-if="classId === 'App.ServiceSection.4'" :classId="classId"></test-loader>
    </div>
  </transition>
</template>

<script>
import TestLoader from '@/components/plugins/TestLoader'

export default {
  name: 'browse-plugin',
  components: { TestLoader },
  props: ['classId'],
  data () {
    return {
      show: true,
      plugin: null
    }
  },
  watch: {
    classId (to, from) {
      /*
      this.show = false
      this.plugin = null

      this.$http.get('/plugins/browse/' + this.classId).then((response) => {
        let data = response.body

        if (data.plugin) {
          this.plugin = data.plugin
        }

        this.show = true
      })
      */
    }
  },
  created () {
    /*
    this.$http.get('/plugins/browse/' + this.classId).then((response) => {
      let data = response.body

      if (data.plugin) {
        this.plugin = data.plugin
      }

      this.show = true
    })
    */
  },
  methods: {

  }
}
</script>

<style scoped>
.plugin {

}
</style>
