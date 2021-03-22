<template>
  <ul class="alert alert-danger">
    <li v-for="error in errors" :key="error.index">{{ error }}</li>
  </ul>
</template>

<script>
export default {
  props: ['errors'],

  created() {},
};
</script>