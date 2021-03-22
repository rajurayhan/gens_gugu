/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import VueExcelXlsx from "vue-excel-xlsx";

// Vue
import Vue from 'vue'

Vue.use(VueExcelXlsx);

// routing file
import router from './router'

// Vuetify
import Vuetify from 'vuetify';
import 'vuetify/dist/vuetify.min.css';
Vue.use(Vuetify);
// Vue.use(Vuetify, {
// theme: {
//     primary: colors.indigo.base,
//     secondary: colors.blue.base,
//     accent: colors.amber.base,
// }
// });


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('dashboard-container-component', require('./components/DashboardContainerComponent.vue').default);
Vue.component('container-component', require('./components/AdminContainerComponent.vue').default);
Vue.component('table-data-container-component', require('./components/TableDataContainerComponent.vue').default);
Vue.component('main-container-component', require('./components/MainContainerComponent.vue').default);
Vue.component('form-error', require('./components/FormError.vue').default);
// Vue.component('list2-component', require('./components/ListComponentTwo.vue').default);
// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */


const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(),
    // store,
    router,
    // data: {
    //     message: 'Hello Vue!'
    // },
    // computed: {
    //     msg() {
    //         return store.state.msg
    //     }
    // },
});
