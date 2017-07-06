<template>
  <div :node="node">
    <div item v-for="item in items">
      <div class="item">{{ item.name }}</div>
        <div class="margin" v-for="element in item.elements">
          <div v-if="element.children && element.open" @click="close(element)" class="plus"><i class="fa fa-angle-down"></i></div>
          <div v-else-if="element.children" class="plus" @click="open(element)"><i class="fa fa-angle-right"></i></div>
          <div v-else="!element.children" class="empty"></div>
          <span @contextmenu.prevent="onContextMenu($event, item, element)"><router-link :to="{name: 'browse', params: {classId: element.classId}}">{{ element.name }}</router-link></span>
          <div v-if="element.children">
            <transition name="slide">
              <div class="padding" v-show="element.open">
                <tree-folder :node="element.classId" v-on:context="onContextMenu(arguments[0], arguments[1], arguments[2])"></tree-folder>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TreeFolder from './TreeFolder'

export default {
  name: 'tree-folder',
  components: { TreeFolder },
  props: ['node'],
  data () {
    return {
      items: []
    }
  },
  created () {
    let url = this.node ? '/tree/' + this.node : '/tree'

    this.$http.get(url).then((response) => {
      let data = response.body

      this.items = data.items
    })
  },
  methods: {
    open (element) {
      element.open = true
    },
    close (element) {
      element.open = false
    },
    onContextMenu (event, item, element) {
      this.$emit('context', event, item, element)
    }
  }
}
</script>

<style scoped>

</style>
