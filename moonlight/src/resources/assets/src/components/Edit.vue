<template>
  <div>
    <transition name="show">
      <div class="leaf" v-if="show">
        <div class="buttons">
          <div class="button up enabled" @click="up()"><i class="fa fa-level-up"></i><br>Наверх</div>
          <div class="button browse enabled" @click="browse()"><i class="fa fa-folder-open-o"></i><br>Открыть</div>
          <div class="button save enabled" @click="save()"><i class="fa fa-floppy-o"></i><br>Сохранить</div>
          <transition name="fade">
            <div class="copy-element" v-if="currentElement" v-show="showCopy">
              <div class="container">
                Куда копируем?
              </div>
            </div>
          </transition>
          <div class="button copy enabled" @click="toggleCopy()"><i class="fa fa-clone"></i><div>Копировать<i class="fa fa-caret-down"></i></div></div>
          <transition name="fade">
            <div class="move-element" v-if="currentElement" v-show="showMove">
              <div class="container">
                Куда переносим?
              </div>
            </div>
          </transition>
          <div class="button move enabled" @click="toggleMove()"><i class="fa fa-arrow-right"></i><div>Перенести<i class="fa fa-caret-down"></i></div></div>
          <transition name="fade">
            <div class="tags" v-if="currentElement" v-show="showTags">
              <div class="container">
                <ul>
                  <li class="title">Добавить метку</li>
                  <li><span>Популярное</span></li>
                  <li><span>Тесты</span></li>
                  <li><span>Прочее</span></li>
                  <li class="divider"></li>
                  <li class="title">Снять метку</li>
                  <li><span>Популярное</span></li>
                  <li class="divider"></li>
                  <li class="title">Новая метка</li>
                  <li class="new"><input type="text" value="" placeholder="Название"></li>
                </ul>
              </div>
            </div>
          </transition>
          <div class="button tag enabled" @click="toggleTags()"><i class="fa fa-tag"></i><div>Метка<i class="fa fa-caret-down"></i></div></div>
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
      showCopy: false,
      showMove: false,
      showTags: false,
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
    toggleCopy () {
      var height = $('.buttons').height()

      $('.copy-element > .container').css({top: height + 'px', left: '2px'})

      this.showCopy = !this.showCopy
      this.showMove = false
      this.showTags = false
    },
    toggleMove () {
      var height = $('.buttons').height()

      $('.move-element > .container').css({top: height + 'px', left: '2px'})

      this.showMove = !this.showMove
      this.showCopy = false
      this.showTags = false
    },
    toggleTags () {
      var height = $('.buttons').height()

      $('.tags > .container').css({top: height + 'px', left: '2px'})

      this.showTags = !this.showTags
      this.showMove = false
      this.showCopy = false
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

.tags > .container::-webkit-scrollbar {
    width: 10px;
    background-color: rgba(127, 127, 127, 0.2);
}

.tags > .container::-webkit-scrollbar-thumb {
    background-color: rgba(127, 127, 127, 0.5);
}

.copy-element {
  display: inline-block;
  position: absolute;
  margin: 0;
  padding: 0;
}

.copy-element > .container {
  display: block;
  position: absolute;
  z-index: 100;
  min-width: 15rem;
  min-height: 10rem;
  margin: 0;
  padding: 0;
  box-shadow: 0 3px 7px 0 rgba(0, 0, 0, 0.18), 0 2px 11px 0 rgba(0, 0, 0, 0.15);
  border: none;
  border-collapse: separate;
  border-spacing: 1px;
  background-color: white;
  font-size: 1rem;
}

.move-element {
  display: inline-block;
  position: absolute;
  margin: 0;
  padding: 0;
}

.move-element > .container {
  display: block;
  position: absolute;
  z-index: 100;
  min-width: 15rem;
  min-height: 10rem;
  margin: 0;
  padding: 0;
  box-shadow: 0 3px 7px 0 rgba(0, 0, 0, 0.18), 0 2px 11px 0 rgba(0, 0, 0, 0.15);
  border: none;
  border-collapse: separate;
  border-spacing: 1px;
  background-color: white;
  font-size: 1rem;
}

.tags {
  display: inline-block;
  position: absolute;
  margin: 0;
  padding: 0;
}

.tags > .container {
  display: block;
  position: absolute;
  z-index: 100;
  max-height: 20rem;
  overflow: hidden;
  overflow-y: scroll;
  margin: 0;
  padding: 0;
  box-shadow: 0 3px 7px 0 rgba(0, 0, 0, 0.18), 0 2px 11px 0 rgba(0, 0, 0, 0.15);
  border: none;
  border-collapse: separate;
  border-spacing: 1px;
  background-color: white;
  font-size: 1rem;
}

.tags ul {
    margin: 0;
    padding: 0;
}

.tags ul > li {
    float: none;
    height: 3rem;
    line-height: 3rem;
    margin: 0;
    padding: 0;
    white-space: nowrap;
    color: black;
}

.tags ul > li:hover:not(.new):not(.title)  {
    background-color: #eee;
    transition: 0.2s;
    cursor: pointer;
}

.tags ul > li.title {
    height: auto;
    line-height: 1.5rem;
    padding: 1rem 3rem 0.5rem 2rem;
    background-color: white;
    color: #789;
    font-size: 1rem;
}

.tags ul > li.divider {
    height: 1px;
    background-color: #ccc;
    padding: 0;
}

.tags ul > li span {
    padding: 0 3rem 0 2rem;
    color: black;
}

.tags ul > li.new {
    padding: 0 3rem 1.5rem 2rem;
}
</style>
