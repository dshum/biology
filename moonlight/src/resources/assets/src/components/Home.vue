<template>
  <div>
    <home-plugin></home-plugin>
  </div>
</template>

<script>
import HomePlugin from '@/components/plugins/HomePlugin'

export default {
  name: 'home',
  components: { HomePlugin },
  data () {
    return {

    }
  },
  created () {
    $(document).attr('title', 'Moonlight')
  }
}
</script>

<style scoped>

</style>
