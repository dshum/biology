<template>
  <property-plugin :mode="mode" :view="view"></property-plugin>
</template>

<script>
import PropertyPlugin from '@/components/plugins/PropertyPlugin'

export default {
  name: 'plugin-property',
  components: { PropertyPlugin },
  props: ['mode', 'view']
}
</script>
