import Vue from 'vue'
import VueRouter from 'vue-router'

import FileUpload from './components/pages/FileUpload.vue'
import FileList from './components/pages/FileList.vue'
import GensAdmin from './components/pages/GensAdmin.vue'
import Dashboard from './components/pages/Dashboard.vue'
import DataSource from './components/pages/DataSourceComponent'
import DatasourceColumns from './components/pages/DatasourceColumnsComponent'
import TableDataContainer from './components/pages/TableDataComponent'

import TableColumnsList from './components/pages/TableColumnsListComponent'
import TableListComponent from './components/pages/TableListComponent'
import DefinitionUpload from './components/pages/DefinitionUpload'

Vue.use(VueRouter)

export default new VueRouter({
  mode: 'history',
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: Dashboard
    },
    {
      path: '/fileupload',
      name: 'fileupload',
      component: FileUpload
    },
    {
      path: '/filelist',
      name: 'filelist',
      component: FileList
    },
    {
      path: '/tabledata/',
      name: 'tableData',
      component: TableDataContainer,
    },
    {
      path: '/tabledata/:id',
      name: 'tableDataDetail',
      component: TableDataContainer,
      props: route => ({ id: Number(route.params.id) })
    },
    {
      path: '/admin',
      name: 'admin',
      component: GensAdmin
    },
    {
      path: '/admin/tables',
      name: 'tableList',
      component: TableListComponent
    },
    {
      path: '/admin/list',
      name: 'tableColumnsList',
      component: TableColumnsList
    },
    {
      path: '/admin/datasource',
      name: 'dataSource',
      component: DataSource
    },
    {
      path: '/admin/datasource-columns',
      name: 'datasourceColumns',
      component: DatasourceColumns
    },
    {
      path: '/admin/definition-upload',
      name: 'definitionFileUpload',
      component: DefinitionUpload
    },

  ]
})