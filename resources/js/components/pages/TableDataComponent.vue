<template>
  <v-app>
    <v-data-table
      id="dataTableUploadedData"
      :headers="headers"
      :items="tableData"
      :loading="loadingStatus"
      :disable-sort="loadingStatus"
      class="elevation-1"
      fixed-header
      must-sort
      height="70vh"
      :hide-default-footer="loadingStatus"
      :options.sync="options"
      :server-items-length="totalTableData"
      :footer-props="{
        'items-per-page-options': [100, 1000],
        'items-per-page-text': '1ページに表示するデータ数',
      }"
    >
      <template
        v-for="header in headers"
        v-slot:[`item.${header.value}`]="{ item }"
      >
        <v-tooltip bottom :key="header.value">
          <template v-slot:activator="{ on }">
            <span v-on="on">{{ item[header.value] }}</span>
          </template>
          <span>{{ item[header.value] }}</span>
        </v-tooltip>
      </template>
      <template v-slot:top>
        <v-toolbar flat color="white">
          <v-toolbar-title id="dataTableTitle">
            {{ tableInfo.table_name_alias }}
          </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-text-field
            id="searchBox"
            v-model="searchBoxText"
            label="Search"
            single-line
            hide-details
            @keyup.enter="setSearchWord"
            :disabled="loadingStatus"
          ></v-text-field>
          <v-icon large @click="setSearchWord" :disabled="loadingStatus">
            mdi-magnify
          </v-icon>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <vue-excel-xlsx
            :disabled="!tableAllData.length || loadingStatus"
            :data="tableAllData"
            :columns="columns"
            :filename="tableInfo.table_name_alias + '-' + FormatDate()"
            :sheetname="tableInfo.table_name_alias"
          >
            <v-icon
              large
              color="blue darken-4"
              :disabled="!tableAllData.length || loadingStatus"
            >
              mdi-arrow-down-bold-box-outline
            </v-icon>
          </vue-excel-xlsx>
        </v-toolbar>
      </template>
      <template v-for="header in headers" v-slot:[header.value]="{ item }">
        {{ item }}
      </template>
    </v-data-table>
    <v-snackbar
      id="snackBar"
      v-model="snackbarProperty.snackbar"
      :color="snackbarProperty.color"
      :right="snackbarProperty.x === 'right'"
      :timeout="snackbarProperty.timeout"
      :top="snackbarProperty.y === 'top'"
      :multi-line="snackbarProperty.multiLine"
    >
      {{ snackbarProperty.text }}
      <v-btn dark text @click="snackbarProperty.snackbar = false">Close</v-btn>
    </v-snackbar>

    <!-- Dialog to show response -->
    <v-dialog v-model="dialog" width="500" persistent scrollable>
      <v-card style="max-height: 400px">
        <v-card-title
          id="vResCardTitle"
          class="headline grey lighten-2"
          primary-title
        >
          {{ responseDialog.message }}
        </v-card-title>

        <v-card-text id="elseErrorDialogText" class="d-flex">
          詳細：
          <br />
          {{ responseDialog.details[0] }}
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn id="closeBtn" color="primary" @click="closeResponseDialog">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>


<script>
const API_PARAMETERS = [
  'page',
  'itemsPerPage',
  'sortBy',
  'sortDesc',
  'searchWord',
];
export default {
  data: () => ({
    headers: [],
    columns: [],
    searchBoxText: '',
    loadingStatus: false, // This will show loading bar on initialization
    tableInfo: [],
    tableData: [],
    tableAllData: [],
    TableId: '',

    totalTableData: -1,
    options: {
      // This is default value and will be updated by vuetify.
      page: 1,
      itemsPerPage: 100,
      sortBy: ['created_at'],
      sortDesc: [true],
    },
    searchWordForAPIParam: '',

    snackbarProperty: {
      snackbar: false,
      color: '',
      // mode: 'vertical',
      text: '',
      timeout: 5000,
      x: 'right',
      y: 'top',
      multiLine: false,
    },

    dialog: false,
    responseDialog: {
      message: '',
      details: [],
    },
  }),

  props: { id: Number },

  created() {
    if (this.$props.id) {
      this.TableId = this.$props.id;
      this.getTableInfo(this.TableId);
    }
  },

  watch: {
    id: function () {
      this.searchBoxText = '';
      this.searchWordForAPIParam = '';
      this.totalTableData = -1;
      this.$set(this.options, 'page', 1);
      this.$set(this.options, 'sortBy', ['created_at']);
      this.$set(this.options, 'sortDesc', [true]);

      this.TableId = this.$props.id;
      this.getTableInfo(this.TableId);
    },

    options: {
      handler: function (val, oldVal) {
        if (this.$props.id) {
          this.loadingStatus = true;
          this.getTableData(this.TableId);
        }
      },
      deep: true,
    },

    tableInfo: function () {
      //create download excel header
      this.columns = [];
      this.tableInfo.columns.forEach((column, i) => {
        this.columns.push({ label: column.text, field: column.value });
      });
    },
  },

  methods: {
    showSnackBar(text, color, multiLine = false) {
      this.snackbarProperty.snackbar = true;
      this.snackbarProperty.text = text;
      this.snackbarProperty.color = color;
      this.snackbarProperty.multiLine = multiLine;
    },

    getTableInfo(id) {
      axios
        .get(`/api/v1/table/${id}`)
        .then((response) => {
          this.tableInfo = response.data;
          this.headers = this.tableInfo.columns;
        })
        .catch((error) => {
          // システム管理者に連絡するようメッセージを出す
          this.showSystemErrorDialog('テーブルの取得に失敗しました。');
        })
        .finally(() => {
          this.loadingStatus = false;
        });
    },

    getTableData(id) {
      this.tableAllData = [];
      var apiParams = {};
      for (const key in this.options) {
        // Removing unsupported webAPI parameters set by vuetify
        if (API_PARAMETERS.includes(key)) {
          apiParams[key] = this.options[key];
        }
      }
      if (this.searchWordForAPIParam != '') {
        // Converting a space-separated string to an array
        // Convert full-width spaces to half-width spaces, as they are also treated as delimiters.
        apiParams['searchWords'] = this.searchWordForAPIParam
          .replace(/　/g, ' ')
          .split(' ');
      }
      // Since the search box string may have been changed by the user,
      // overwrite the search words with the search words at the time the search button was pressed.
      this.searchBoxText = this.searchWordForAPIParam;
      axios
        .get(`/api/v1/table-data/${id}`, { params: apiParams })
        .then((response) => {
          this.tableData = response.data.records;
          this.totalTableData = response.data.total_count;

          //get all data for download excel
          delete apiParams.page;
          delete apiParams.itemsPerPage;
          axios
            .get(`/api/v1/table-data/${id}`, { params: apiParams })
            .then((response) => {
              this.tableAllData = response.data.records;
            })
            .catch((error) => {
              // システム管理者に連絡するようメッセージを出す
              this.showSystemErrorDialog('データの取得に失敗しました。');
            });
        })
        .catch((error) => {
          // システム管理者に連絡するようメッセージを出す
          this.showSystemErrorDialog('データの取得に失敗しました。');
        })
        .finally(() => {
          this.loadingStatus = false;
        });
    },

    FormatDate() {
      var toDoubleDigits = function (num) {
        num += '';
        if (num.length === 1) {
          num = '0' + num;
        }
        return num;
      };
      var today = new Date();
      var yyyy = today.getFullYear();
      var mm = toDoubleDigits(today.getMonth() + 1);
      var dd = toDoubleDigits(today.getDate());
      var hh = toDoubleDigits(today.getHours());
      var mi = toDoubleDigits(today.getMinutes());
      var ss = toDoubleDigits(today.getSeconds());
      var dateTime = yyyy + '' + mm + '' + dd + '' + hh + '' + mi + '' + ss;
      return dateTime;
    },

    setSearchWord() {
      if (this.$props.id) {
        // Get the search words when the user presses the search button.
        this.searchWordForAPIParam = this.searchBoxText;
        // Reset pagination settings when searching. Don't reset the sort settings.
        this.$set(this.options, 'page', 1);
        this.loadingStatus = true;
        this.getTableData(this.TableId);
      }
    },

    showSystemErrorDialog(details) {
      this.responseDialog.message =
        'システムエラーが発生しました。IT部門に連絡してください';
      this.responseDialog.details.push(details);
      this.dialog = true;
    },

    closeResponseDialog() {
      // Hide the dialog
      this.dialog = false;
      // Initialize this.responseDialog
      this.$set(this.responseDialog, 'message', '');
      this.$set(this.responseDialog, 'details', []);
    },
  },
};
</script>
