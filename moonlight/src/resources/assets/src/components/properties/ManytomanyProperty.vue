<template>
  <div v-if="mode === 'browse'">
    <div v-for="element in view.elements">
      <div><router-link :to="{name: 'browse', params: {classId: element.classId}, query: {mode: 'edit'}}">{{ element.name }}</router-link></div>
    </div>
  </div>
  <div v-else-if="mode === 'edit'">
    <label>{{ view.title }}:</label>
    <span v-if="element">
      <router-link :to="{name: 'browse', params: {classId: element.classId}, query: {mode: 'edit'}}">{{ element.name }}</router-link>
    </span>
    <span v-else>
      Не определено
    </span>
    <span :name="view.name" :label="view.title" class="error"></span><br>
    <autocomplete :name="view.name" :value="initial" :item="view.relatedClass" v-on:update="update(arguments[0])" placeholder="ID или название"></autocomplete>
    <span class="add" @click="add()">Добавить</span>
    <div class="elements">
      <div v-for="el in elements">
        <p><input type="checkbox" :name="view.name" :id="el.classId" v-on:change="change(el, $event.target.checked)" checked value="1"> <label :for="el.classId">{{ el.name }}</label></p>
      </div>
    </div>
  </div>
</template>

<script>
import Autocomplete from '../common/Autocomplete'

export default {
  name: 'manytomany-property',
  components: {
    Autocomplete
  },
  props: ['mode', 'view'],
  watch: {
    view (to, from) {
      this.initial = null
    }
  },
  data () {
    return {
      initial: null,
      element: null,
      elements: this.view.elements,
      ids: [],
      checked: []
    }
  },
  mounted () {
    this.elements.forEach(element => {
      this.checked.push(element.id)
      this.ids.push(element.id)
    })

    this.$emit('update', {value: this.checked})
  },
  methods: {
    update (value) {
      this.element = value ? {
        id: value.id,
        classId: value.classId,
        name: value.value
      } : null

      this.initial = value ? value.value : null
    },
    change (element, value) {
      if (value) {
        this.checked.push(element.id)
      } else {
        var index = this.checked.indexOf(element.id)

        if (index > -1) {
          this.checked.splice(index, 1)
        }
      }

      this.$emit('update', {value: this.checked})
    },
    add () {
      if (this.element) {
        var index = this.ids.indexOf(this.element.id)

        if (index === -1) {
          this.elements.push(this.element)
          this.ids.push(this.element.id)
          this.checked.push(this.element.id)
        }

        this.element = null
        this.initial = null

        this.$emit('update', {value: this.checked})
      }
    }
  }
}
</script>

<style scoped>
  .elements {

  }

  .add {
    margin-left: 1rem;
    cursor: pointer;
  }
</style>
