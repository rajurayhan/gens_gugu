<template>
  <v-app id="inspire">
    <v-navigation-drawer v-model="drawer" app>
      <v-list dense>
        <v-list-item-group>
          <a href="/">
            <!-- サイドバー/ヘッダが切り替わらないため router-link ではなく aタグを利用する -->
            <v-list-item link>
              <v-img
                :src="require('../../assets/gens_logo.png')"
                max-width="220"
                contain
                id="gens-side-logo"
              />
            </v-list-item>
          </a>

          <v-divider></v-divider>

          <router-link :to="{name : 'fileupload'}">
            <v-list-item link>
              <v-list-item-action>
                <v-icon>mdi-cloud-upload</v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>Excelアップロード</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </router-link>

          <router-link :to="{name: 'filelist'}">
            <v-list-item link>
              <v-list-item-action>
                <v-icon>mdi-format-list-bulleted</v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>ファイル一覧</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </router-link>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar app color="info" dark>
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <a href="/">
        <!-- サイドバー/ヘッダが切り替わらないため router-link ではなく aタグを利用する -->
        <v-img
          :src="require('../../assets/gens_logo_light.png')"
          max-width="130"
          contain
          id="gens-logo"
        />
      </a>
      <v-toolbar-title class="mt-2 ms-2">for Nobels</v-toolbar-title>
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
    <v-footer color="info" app>
      <v-spacer></v-spacer>
      <span class="white--text">Copyright &copy; Nobels. All Rights Reserved.</span>
    </v-footer>
  </v-app>
</template>

<script>
export default {
  props: {
    source: String,
  },
  data: () => ({
    drawer: false,
  }),
  methods: {
    logout() {
      // alert('Logout');
      axios.post('/logout').then((response) => {
        window.location.href = '/login';
      });
    },
  },
};
</script>