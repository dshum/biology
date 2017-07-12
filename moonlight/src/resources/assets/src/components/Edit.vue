<template>
  <div>
    <transition name="show">
      <div class="leaf" v-if="show">
        <div class="buttons">
          <div class="button up enabled" @click="up()"><i class="fa fa-level-up"></i><br>Наверх</div>
          <div class="button browse enabled" @click="browse()"><i class="fa fa-folder-open-o"></i><br>Открыть</div>
          <div class="button save enabled" @click="save()"><i class="fa fa-floppy-o"></i><br>Сохранить</div>
          <div class="button copy enabled"><i class="fa fa-clone"></i><div>Копировать<i class="fa fa-caret-down"></i></div></div>
          <div class="button move enabled"><i class="fa fa-arrow-right"></i><div>Перенести<i class="fa fa-caret-down"></i></div></div>
          <div class="button tag enabled"><i class="fa fa-tag"></i><div>Метка<i class="fa fa-caret-down"></i></div></div>
          <div class="button delete enabled" @click="confirmDelete = true"><i class="fa fa-trash-o"></i><br>Удалить</div>
        </div>
        <h2 v-if="currentItem">Редактирование элемента типа <b>{{currentItem.name}}</b></h2>
        <div class="site" v-if="currentElement.href"><i class="fa fa-external-link"></i><a href target="_blank">Смотреть на сайте</a></div>
        <form @submit.prevent="save()">
          <div class="row" v-for="property in properties">
            <property v-on:update="update(arguments[0], arguments[1])" v-on:save="save()" :property="property" mode="edit" :view="property.view"></property>
          </div>
          <div class="row submit">
            <input type="submit" value="Сохранить" class="btn">
          </div>
        </form>
      </div>
    </transition>
    <transition name="fade">
      <spinner v-show="loading" message="Минутку..."></spinner>
    </transition>
    <transition name="fade">  
      <confirm v-if="currentElement" v-show="confirmDelete" confirmButton="Удалить" confirm-button-class="remove" v-on:confirm="deleteElement()" v-on:cancel="confirmDelete = false">
        Удалить элемент &laquo;{{ currentElement.name }}&raquo;?
      </confirm>
    </transition>
    <transition name="fade">
      <alert v-show="errorMessageAlert" v-on:cancel="errorMessageAlert = false">
        {{ errorMessage }}
      </alert>
    </transition>
    <transition name="fade">
      <alert v-show="errorsAlert" v-on:cancel="errorsAlert = false">
        <div v-for="error in errors">
          {{error.title}}. {{error.message}}.
        </div>
      </alert>
    </transition>
  </div>
</template>

<script>
import Spinner from '@/components/common/Spinner'
import Confirm from '@/components/common/Confirm'
import Alert from '@/components/common/Alert'
import Property from '@/components/Property'

export default {
  name: 'edit',
  props: ['classId', 'item', 'element', 'favorite'],
  components: { Spinner, Confirm, Alert, Property },
  data () {
    return {
      show: false,
      loading: false,
      model: {},
      currentItem: this.item,
      currentElement: this.element,
      properties: [],
      copyProperty: null,
      moveProperty: null,
      confirmDelete: false,
      errorMessage: null,
      errorMessageAlert: false,
      errors: null,
      errorsAlert: false
    }
  },
  watch: {
    item (to, from) {
      this.currentItem = to

      this.getEdit()
    },
    element (to, from) {
      this.currentElement = to

      this.getEdit()
    }
  },
  created () {
    var self = this

    this.getEdit()

    $(document)
      .off('keypress')
      .off('keydown')
      .on('keypress', function (event) {
        return self.$hotkeys.onCtrlS(event, function () {
          self.save()
        })
      })
      .on('keydown', function (event) {
        return self.$hotkeys.onCtrlS(event, function () {
          self.save()
        })
      })
  },
  methods: {
    update (name, value) {
      this.$set(this.model, name, value)
    },
    getEdit () {
      this.$http.get('/edit/' + this.classId).then((response) => {
        let data = response.body

        if (data.state && data.state === 'error_element_not_found') {
          this.$router.push('/browse')
        } else if (data.state && data.state === 'error_element_access_denied') {
          this.$router.push('/browse')
        } else {
          this.properties = data.properties
          this.copyProperty = data.copyProperty
          this.moveProperty = data.moveProperty
          this.show = true
        }
      })
    },
    up () {
      if (this.currentElement && this.currentElement.parent) {
        this.$router.push({name: 'browse', params: {classId: this.currentElement.parent.classId}})
      } else if (this.currentElement) {
        this.$router.push('/browse')
      }
    },
    browse () {
      if (this.currentElement) {
        this.$router.push({name: 'browse', params: {classId: this.currentElement.classId}})
      }
    },
    save () {
      let formData = new FormData()

      this.properties.forEach(property => {
        let name = property.view.name
        let field = this.model[name]

        if (field) {
          Object.keys(field).forEach(key => {
            let value = field[key]

            if (key === 'value') {
              formData.append(name, value)
            } else {
              formData.append(name + '_' + key, value)
            }
          })
        }
      })

      $('.error').hide()

      this.errorMessageAlert = false
      this.errorsAlert = false
      this.loading = true

      this.$http.post('/edit/' + this.classId, formData).then((response) => {
        let data = response.body

        if (data.error) {
          this.errorMessage = data.error
          this.errorMessageAlert = true
        } else if (data.errors) {
          this.errors = data.errors
          this.errorsAlert = true

          this.errors.forEach(error => {
            $('.error[name="' + error.name + '"]').html(error.message).fadeIn(200)
          })
        }

        if (data.element) {
          this.currentElement = data.element

          this.$emit('updateElement', {element: data.element, parents: data.parents})

          if (this.currentElement.parent) {
            this.$eventBus.emit('refreshTree', this.currentElement.parent.classId)
          } else {
            this.$eventBus.emit('refreshTree', null)
          }
        }

        if (data.views) {
          this.properties.forEach(property => {
            let name = property.name
            let view = data.views[name]

            if (view) {
              property.view = view
            }
          })
        }

        this.loading = false
      })
    },
    deleteElement () {
      this.confirmDelete = false
      this.loading = true

      this.$http.delete('/edit/' + this.classId).then((response) => {
        let data = response.body

        if (data.error) {
          this.errorMessage = data.error

          this.errorMessageAlert = true
        } else if (data.deleted) {
          if (this.currentElement.parent) {
            this.$eventBus.emit('refreshTree', this.currentElement.parent.classId)
          } else {
            this.$eventBus.emit('refreshTree', null)
          }

          this.up()
        }

        this.loading = false
      })
    }
  }
}
</script>

<style scoped>
div.favorite {
  float: right;
  margin-right: 0.5rem;
  cursor: pointer;
  color: silver;
}

div.favorite.active {
  color: orange;
}

div.favorite .fa {
  position: relative;
  top: 5px;
  font-size: 1.5rem;
  color: inherit;
}

h2 {
  color: #999;
}

h2 b {
  color: #333;
}

div.property {
  margin-top: 1rem;
  font-size: 1rem;
}

div.site {
  margin: 0.5rem 0;
}

div.site .fa {
  margin-right: 0.5rem;
}
</style>
