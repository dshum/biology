<template>
  <div>
    <div v-if="!classId" class="refresh" @click="refresh()"><i class="fa fa-refresh"></i></div>
    <div item v-for="item in items">
      <div class="item">{{ item.name }}</div>
      <div class="margin" v-for="element in item.elements">
        <div v-if="element.children && element.open" @click="close(element)" class="plus"><i class="fa fa-angle-down"></i></div>
        <div v-else-if="element.children" class="plus" @click="open(element)"><i class="fa fa-angle-right"></i></div>
        <div v-else class="empty"></div>
        <span @contextmenu.prevent="onContextMenu($event, item, element)"><router-link :to="{name: 'browse', params: {classId: element.classId}}">{{ element.name }}</router-link></span>
        <div v-if="element.children" v-show="element.open" :node="element.classId">
          <div class="padding">
            <tree :classId="element.classId" :itemList="element.items" v-on:context="onContextMenu(arguments[0], arguments[1], arguments[2])"></tree>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'tree',
  props: ['classId', 'itemList'],
  data () {
    return {
      items: []
    }
  },
  watch: {
    itemList (to, from) {
      this.items = this.itemList
    }
  },
  created () {
    if (this.itemList) {
      this.items = this.itemList
    } else {
      this.createNode()
    }
  },
  methods: {
    createNode () {
      this.$http.get('/tree').then((response) => {
        let data = response.body

        this.items = data.items

        $('div.refresh > .fa').removeClass('fa-spin')
      })
    },
    refresh () {
      $('div.refresh > .fa').addClass('fa-spin')

      this.createNode()
    },
    open (element) {
      this.$http.post('/tree/open/' + element.classId)

      $('div[node="' + element.classId + '"]').slideDown(200, function () {
        element.open = true
      })
    },
    close (element) {
      this.$http.post('/tree/close/' + element.classId)

      $('div[node="' + element.classId + '"]').slideUp(200, function () {
        element.open = false
      })
    },
    onContextMenu (event, item, element) {
      this.$emit('context', event, item, element)
    }
  }
}
</script>

<style>
.dnone {
  display: none;
}

.refresh {
  position: absolute;
  top: 0.5rem;
  right: 2rem;
  cursor: pointer;
}

.refresh .fa {
  font-size: 1.2rem;
  color: silver;
}
</style>
