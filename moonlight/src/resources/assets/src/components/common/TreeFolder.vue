<template>
  <div node="root"></div>
</template>

<script>
export default {
  name: 'tree-folder',
  data () {
    return {
      items: [],
      currentClassId: null
    }
  },
  watch: {
    '$route' (to, from) {
      $('span.go').removeClass('router-link-active')

      if (to.params.classId) {
        $('span.go[classId="' + to.params.classId + '"]').addClass('router-link-active')
      }
    }
  },
  created () {
    $('span.go').removeClass('router-link-active')

    this.createNode()
  },
  methods: {
    createNode (node = null) {
      let self = this
      let url = node ? '/tree/' + node : '/tree'

      this.currentClassId = this.$route.params.classId

      if (node) {
        var divMarginClassId = $('div.margin[classId="' + node + '"]')
        var divPadding = $('<div class="padding" classId="' + node + '">').hide()
        var divNode = $('<div node="' + node + '"></div>')

        divMarginClassId.append(divPadding)
        divPadding.append(divNode)
      }

      this.$http.get(url).then((response) => {
        let data = response.body

        data.items.forEach(item => {
          this.elements = item.elements

          let divNode = node ? $('div[node="' + node + '"]') : $('div[node="root"]')
          let divItem = $('<div item></div>')
          let divClassItem = $('<div class="item">' + item.name + '</div>')

          divNode.append(divItem)
          divItem.append(divClassItem)

          item.elements.forEach(element => {
            let divMargin = $('<div class="margin" classId="' + element.classId + '">')
            let divArrowRight = $('<div class="plus arrow-right" classId="' + element.classId + '"><i class="fa fa-angle-right"></i></div>')
            let divArrowDown = $('<div class="plus arrow-down" classId="' + element.classId + '"><i class="fa fa-angle-down"></i></div>')
            let divEmpty = $('<div class="empty"></div>')
            let divSpan = $('<span class="go" classId="' + element.classId + '">' + element.name + '</span>')

            if (this.$route.params.classId === element.classId) {
              divSpan.addClass('router-link-active')
            }

            divItem.append(divMargin)

            if (element.children) {
              divMargin.append(divArrowRight)
              divMargin.append(divArrowDown.hide())
            } else {
              divMargin.append(divEmpty)
            }

            divMargin.append(divSpan)

            if (this.isOpen(element)) {
              this.createNode(element.classId)
              element.loaded = true
            }

            divArrowRight.click(function () {
              self.open(element)

              if (element.loaded) {
                let divPadding = $('div.padding[classId="' + element.classId + '"]')

                divPadding.slideDown(200, function () {
                  divArrowRight.hide()
                  divArrowDown.show()
                })
              } else {
                $('.tree .plus[classId="' + element.classId + '"] .fa').css({color: '#ccc'})

                self.createNode(element.classId)
                element.loaded = true
              }
            })

            divArrowDown.click(function () {
              self.close(element)

              let divPadding = $('div.padding[classId="' + element.classId + '"]')

              divPadding.slideUp(200, function () {
                divArrowDown.hide()
                divArrowRight.show()
              })
            })

            divSpan.click(function () {
              let classId = $(this).attr('classId')

              self.$router.push({name: 'browse', params: {classId: classId}})
            })
          })
        })

        if (divPadding) {
          let divArrowRight = $('div.plus.arrow-right[classId="' + node + '"]')
          let divArrowDown = $('div.plus.arrow-down[classId="' + node + '"]')

          divPadding.slideDown(200, function () {
            divArrowRight.hide()
            $('.tree .plus[classId="' + node + '"] .fa').css({color: '#3a7'})
            divArrowDown.show()
          })
        }
      })
    },
    isOpen (element) {
      let loggedUserId = localStorage.getItem('loggedUserId')

      return localStorage.getItem('open[' + loggedUserId + '][' + element.classId + ']')
    },
    open (element) {
      let loggedUserId = localStorage.getItem('loggedUserId')

      localStorage.setItem('open[' + loggedUserId + '][' + element.classId + ']', true)
    },
    close (element) {
      let loggedUserId = localStorage.getItem('loggedUserId')

      localStorage.removeItem('open[' + loggedUserId + '][' + element.classId + ']')
    },
    onContextMenu (event, item, element) {
      this.$emit('context', event, item, element)
    }
  }
}
</script>

<style scoped>
.dnone {
  display: none;
}
</style>
