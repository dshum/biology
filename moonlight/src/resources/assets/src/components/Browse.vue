<template>
  <div>
    <transition name="show">
      <div class="leaf" v-if="show">
        <div class="buttons">
          <div class="button up" :class="{enabled: currentElement}" @click="up()"><i class="fa fa-level-up"></i><br>Наверх</div>
          <div class="button edit" :class="{enabled: currentElement}" @click="edit()"><i class="fa fa-pencil"></i><br>Редактировать</div>
          <div class="button save"><i class="fa fa-floppy-o"></i><br>Сохранить</div>
          <div class="button copy"><i class="fa fa-clone"></i><div>Копировать<span class="halflings halflings-triangle-bottom"></span></div></div>
          <div class="button move"><i class="fa fa-arrow-right"></i><div>Перенести<span class="halflings halflings-triangle-bottom"></span></div></div>
          <div class="button delete"><i class="fa fa-trash-o"></i><br>Удалить</div>
        </div>
        <transition name="fade">
          <div class="creates" v-if="creates.length">
            Добавить:<template v-for="(create, index) in creates"><span v-if="index">,</span><router-link :to="{name: 'browse', params: {classId: classId}, query: {mode: 'create', item: create.id}}">{{ create.name }}</router-link></template>
          </div>
        </transition>
        <browse-plugin :classId="classId" v-if="showBrowsePlugin"></browse-plugin>
        <div v-for="i in items">
          <item :classId="classId" :item="i" v-on:load="load(1)" v-on:empty="load(0)"></item>
        </div>
        <div class="empty" v-if="isEmpty">
          Элементы не найдены.
        </div>
      </div>
    </transition>
    <transition name="fade">
      <spinner v-show="loading" message="Минутку..."></spinner>
    </transition>
  </div>
</template>

<script>
import Spinner from '@/components/common/Spinner'
import Item from '@/components/common/Item'
import BrowsePlugin from '@/components/plugins/BrowsePlugin'

export default {
  name: 'browse',
  props: ['classId', 'item', 'element', 'favorite'],
  components: { Spinner, Item, BrowsePlugin },
  data () {
    return {
      show: false,
      showBrowsePlugin: false,
      loading: false,
      currentItem: this.item,
      currentElement: this.element,
      creates: [],
      items: [],
      isEmpty: false,
      isAnyone: false,
      loaded: 0
    }
  },
  watch: {
    classId (to, from) {
      this.creates = []
      this.items = []

      this.getBrowse()
    },
    item (to, from) {
      this.currentItem = to
    },
    element (to, from) {
      this.currentElement = to
    }
  },
  created () {
    this.getBrowse()
  },
  methods: {
    load (n) {
      this.loaded++

      if (n) {
        this.isAnyone = true
      }

      if (this.loaded === this.items.length) {
        this.isEmpty = !this.isAnyone
      }
    },
    getBrowse () {
      this.loading = true
      this.showBrowsePlugin = false
      this.isEmpty = false
      this.isAnyone = false
      this.loaded = 0

      let url = this.classId ? '/browse/' + this.classId : '/browse/root'

      this.$http.get(url).then((response) => {
        let data = response.body

        if (data.creates) {
          this.creates = data.creates
        }

        if (data.items) {
          this.items = data.items
        }

        if (!data.items.length) {
          this.isEmpty = true
        }

        this.loading = false
        this.showBrowsePlugin = true
        this.show = true
      })
    },
    up () {
      if (this.currentElement && this.currentElement.parent) {
        this.$router.push({name: 'browse', params: {classId: this.currentElement.parent.classId}})
      } else if (this.currentElement) {
        this.$router.push('/browse')
      }
    },
    edit () {
      if (this.currentElement) {
        this.$router.push({name: 'browse', params: {classId: this.currentElement.classId}, query: {mode: 'edit'}})
      }
    }
  }
}
</script>

<style scoped>
.creates {
  margin: 2rem 0 0 0;
}

.creates a {
  margin-left: 0.5rem;
}

.empty {
  margin: 2rem 0;
}
</style>
