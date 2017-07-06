<template>
  <div>
    <transition name="show">
      <div class="leaf" v-if="show">
        <div class="buttons">
          <div class="button up enabled" @click="up()"><i class="fa fa-level-up"></i><br>Наверх</div>
          <div class="button browse"><i class="fa fa-folder-open-o"></i><br>Открыть</div>
          <div class="button save enabled" @click="save()"><i class="fa fa-floppy-o"></i><br>Сохранить</div>
          <div class="button copy"><i class="fa fa-clone"></i><div>Копировать<i class="fa fa-caret-down"></i></div></div>
          <div class="button move"><i class="fa fa-arrow-right"></i><div>Перенести<i class="fa fa-caret-down"></i></div></div>
          <div class="button tag"><i class="fa fa-tag"></i><div>Метка<i class="fa fa-caret-down"></i></div></div>
          <div class="button delete"><i class="fa fa-trash-o"></i><br>Удалить</div>
        </div>
        <h2 v-if="currentItem">Новый элемент типа <b>{{currentItem.name}}</b></h2>
        <form @submit.prevent="save()">
          <div class="row" v-for="property in properties">
            <property v-on:update="update(arguments[0], arguments[1])" v-on:save="save()":className="property.className" mode="edit" :view="property.view"></property>
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
import Property from '@/components/Property'
import Alert from '@/components/common/Alert'

export default {
  name: 'create',
  props: ['classId', 'parent', 'itemId'],
  components: { Spinner, Property, Alert },
  data () {
    return {
      show: false,
      loading: false,
      model: {},
      currentItem: null,
      currentElement: this.parent,
      properties: [],
      errorMessage: null,
      errorMessageAlert: false,
      errors: null,
      errorsAlert: false
    }
  },
  watch: {
    parent (to, from) {
      this.currentElement = to

      this.getCreate()
    }
  },
  created () {
    var self = this

    this.getCreate()

    $(document)
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
    getCreate () {
      this.loading = true

      let url =
        this.classId
        ? '/create/' + this.classId + '/' + this.itemId
        : '/create/root/' + this.itemId

      this.$http.get(url).then((response) => {
        let data = response.body

        if (data.item) {
          this.currentItem = data.item
        }

        if (data.properties) {
          this.properties = data.properties
        }

        this.show = true
        this.loading = false
      })
    },
    up () {
      if (this.currentElement) {
        this.$router.push({name: 'browse', params: {classId: this.currentElement.classId}})
      } else {
        this.$router.push('/browse')
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

      this.$http.post('/add/' + this.itemId, formData).then((response) => {
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

        if (data.added) {
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
