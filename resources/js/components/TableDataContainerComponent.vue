<template>
  <v-app id="inspire">
    <v-navigation-drawer id="navidationDrawer" v-model="drawer" app>
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

          <v-subheader>アップロードデータ表示</v-subheader>
          <v-progress-linear
            id="progressBar"
            :active="isProcessing"
            :indeterminate="isProcessing"
            color="light-blue accent-4"
          ></v-progress-linear>

          <router-link v-for="table in tableList" :key="table.id" :to="'/tabledata/'+table.id">
            <v-list-item link>
              <v-list-item-action>
                <v-icon>mdi-database</v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>{{table.table_name_alias}}</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </router-link>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar app color="green darken-4" dark>
      <v-app-bar-nav-icon id="drawerButton" @click.stop="drawer = !drawer" />
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
    <v-footer color="green darken-4" app>
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
    isProcessing: false,
    drawer: true,
    tableList: [],
  }),

  created() {
    this.getTableList();
  },

  methods: {
    getTableList() {
      this.isProcessing = true;
      axios
        .get('/api/v1/tables')
        .then((response) => {
          this.tableList = response.data.tables;
          this.isProcessing = false;
        })
        .catch((error) => console.log(error));
    },
  },
};
</script>