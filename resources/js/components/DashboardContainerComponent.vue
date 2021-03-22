<template>
  <v-app id="inspire">
    <v-app-bar app color="indigo" dark>
      <!-- <v-app-bar-nav-icon @click.stop="drawer = !drawer" /> -->
      <v-img
        :src="require('../../assets/gens_logo_light.png')"
        max-width="130"
        contain
        id="gens-logo"
      />
      <v-toolbar-title class="mt-2 ms-2">- Excel Navigation System</v-toolbar-title>
    </v-app-bar>

    <v-content>
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col class="text-center">
            <router-view />
          </v-col>
        </v-row>
      </v-container>
    </v-content>
    <v-footer color="indigo" app>
      <v-spacer></v-spacer>
      <span class="white--text">Copyright &copy; Nobels. All Rights Reserved.</span>
    </v-footer>
  </v-app>
</template>

<script>
export default {
  data: () => ({
    drawer: false,
  }),
  methods: {},
};
</script>