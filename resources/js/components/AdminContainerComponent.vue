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

          <router-link :to="{name: 'tableList'}">
            <v-list-item link>
              <v-list-item-action>
                <v-icon>mdi-database</v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>Table List</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </router-link>

          <router-link :to="{name: 'tableColumnsList'}">
            <v-list-item link>
              <v-list-item-action>
                <v-icon>mdi-database</v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>Table Columns</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </router-link>

          <router-link :to="{name: 'dataSource'}">
            <v-list-item link>
              <v-list-item-action>
                <v-icon>mdi-database</v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>Datasource</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </router-link>

          <router-link :to="{name: 'datasourceColumns'}">
            <v-list-item link>
              <v-list-item-action>
                <v-icon>mdi-database</v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>Datasource Columns</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </router-link>

          <router-link :to="{name: 'definitionFileUpload'}">
            <v-list-item link>
              <v-list-item-action>
                <v-icon>mdi-cloud-upload</v-icon>
              </v-list-item-action>
              <v-list-item-content>
                <v-list-item-title>Definition Upload</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </router-link>

          <!--
          <v-list-item link @click="logout">
            <v-list-item-action>
              <v-icon>mdi-power</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title>Logout</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          -->
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar app color="indigo" dark>
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
    <v-footer color="indigo" app>
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
    headers: [
      {
        text: 'Dessert (100g serving)',
        align: 'start',
        sortable: false,
        value: 'name',
      },
      { text: 'Calories', value: 'calories' },
      { text: 'Fat (g)', value: 'fat' },
      { text: 'Carbs (g)', value: 'carbs' },
      { text: 'Protein (g)', value: 'protein' },
      { text: 'Iron (%)', value: 'iron' },
    ],
    desserts: [
      {
        name: 'Frozen Yogurt',
        calories: 159,
        fat: 6.0,
        carbs: 24,
        protein: 4.0,
        iron: '1%',
      },
      {
        name: 'Ice cream sandwich',
        calories: 237,
        fat: 9.0,
        carbs: 37,
        protein: 4.3,
        iron: '1%',
      },
      {
        name: 'Eclair',
        calories: 262,
        fat: 16.0,
        carbs: 23,
        protein: 6.0,
        iron: '7%',
      },
      {
        name: 'Cupcake',
        calories: 305,
        fat: 3.7,
        carbs: 67,
        protein: 4.3,
        iron: '8%',
      },
      {
        name: 'Gingerbread',
        calories: 356,
        fat: 16.0,
        carbs: 49,
        protein: 3.9,
        iron: '16%',
      },
      {
        name: 'Jelly bean',
        calories: 375,
        fat: 0.0,
        carbs: 94,
        protein: 0.0,
        iron: '0%',
      },
      {
        name: 'Lollipop',
        calories: 392,
        fat: 0.2,
        carbs: 98,
        protein: 0,
        iron: '2%',
      },
      {
        name: 'Honeycomb',
        calories: 408,
        fat: 3.2,
        carbs: 87,
        protein: 6.5,
        iron: '45%',
      },
      {
        name: 'Donut',
        calories: 452,
        fat: 25.0,
        carbs: 51,
        protein: 4.9,
        iron: '22%',
      },
      {
        name: 'KitKat',
        calories: 518,
        fat: 26.0,
        carbs: 65,
        protein: 7,
        iron: '6%',
      },
    ],
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