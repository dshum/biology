<template>
  <div v-if="property.className === 'TextfieldProperty'">
    <textfield-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></textfield-property>
  </div>
  <div v-else-if="property.className === 'TextareaProperty'">
    <textarea-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></textarea-property>
  </div>
  <div v-else-if="property.className === 'RichtextProperty'">
    <richtext-property v-on:update="update(arguments[0])" v-on:save="save()" :mode="mode" :view="view"></richtext-property>
  </div>
  <div v-else-if="property.className === 'DatetimeProperty'">
    <datetime-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></datetime-property>
  </div>
  <div v-else-if="property.className === 'DateProperty'">
    <date-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></date-property>
  </div>
  <div v-else-if="property.className === 'IntegerProperty'">
    <integer-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></integer-property>
  </div>
  <div v-else-if="property.className === 'FloatProperty'">
    <float-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></float-property>
  </div>
  <div v-else-if="property.className === 'ImageProperty'">
    <image-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></image-property>
  </div>
  <div v-else-if="property.className === 'OneToOneProperty'">
    <onetoone-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></onetoone-property>
  </div>
  <div v-else-if="property.className === 'ManyToManyProperty'">
    <manytomany-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></manytomany-property>
  </div>
  <div v-else-if="property.className === 'CheckboxProperty'">
    <checkbox-property v-on:update="update(arguments[0])" :mode="mode" :view="view"></checkbox-property>
  </div>
  <div v-else-if="property.className === 'PluginProperty'">
    <plugin-property :mode="mode" :view="view"></plugin-property>
  </div>
  <div v-else-if="property.className === 'VirtualProperty'">
    <virtual-property :mode="mode" :view="view"></virtual-property>
  </div>
  <div v-else>
    Неизвестный тип поля.
  </div>
</template>

<script>
import TextfieldProperty from '@/components/properties/TextfieldProperty'
import TextareaProperty from '@/components/properties/TextareaProperty'
import RichtextProperty from '@/components/properties/RichtextProperty'
import DatetimeProperty from '@/components/properties/DatetimeProperty'
import DateProperty from '@/components/properties/DateProperty'
import IntegerProperty from '@/components/properties/IntegerProperty'
import FloatProperty from '@/components/properties/FloatProperty'
import ImageProperty from '@/components/properties/ImageProperty'
import OnetooneProperty from '@/components/properties/OnetooneProperty'
import ManytomanyProperty from '@/components/properties/ManytomanyProperty'
import CheckboxProperty from '@/components/properties/CheckboxProperty'
import PluginProperty from '@/components/properties/PluginProperty'
import VirtualProperty from '@/components/properties/VirtualProperty'

export default {
  name: 'property',
  components: {
    TextfieldProperty,
    TextareaProperty,
    RichtextProperty,
    DatetimeProperty,
    DateProperty,
    IntegerProperty,
    FloatProperty,
    ImageProperty,
    OnetooneProperty,
    ManytomanyProperty,
    CheckboxProperty,
    PluginProperty,
    VirtualProperty
  },
  props: ['property', 'mode', 'view'],
  methods: {
    update (value) {
      this.$emit('update', this.view.name, value)
    },
    save () {
      this.$emit('save')
    }
  }
}
</script>
