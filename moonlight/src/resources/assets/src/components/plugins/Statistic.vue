<template>
  <div class="container">
    <h2>Погода в Москве</h2>
  </div>
</template>

<script>
export default {
  name: 'statistic',
  props: ['classId'],
  data () {
    return { }
  },
  mounted () {

  },
  methods: {

  }
}
</script>

<style scoped>
.container {
  display: table;
  min-width: 30rem;
  border: 1px solid #ccc;
  border-radius: 2px;
  padding: 1rem;
}
</style>
