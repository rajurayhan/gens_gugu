<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="DataColumnMapping"
      :disable-pagination="true"
      :hide-default-footer="true"
      :loading="loadingStatus"
      class="elevation-1"
      fixed-header
      must-sort
      height="70vh"
    >
        <template v-for="header in headers" v-slot:[`item.${header.value}`]="{ item }">
            <v-tooltip
            bottom
            :key="header.value"
            >
            <template v-slot:activator="{ on }">
                <span v-on="on">{{ item[header.value] }}</span>
            </template>
            <span>{{ item[header.value] }}</span>
            </v-tooltip>
      </template>
      <template v-slot:top>
        <v-toolbar flat color="white">
          <v-toolbar-title>Datasource Columns</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-select
            :items="dataSource"
            item-text="datasource_name"
            item-value="id"
            label="Datasource Name"
            :clearable="true"
            v-model="sortSelectedTable"
            v-on:change="sortByDataSource()"
            :disabled="loadingStatus"
          ></v-select>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-dialog persistent v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on }">
              <v-btn
                color="primary"
                dark
                class="mb-2"
                v-on="on"
                :disabled="loadingStatus"
              >
                New Item
              </v-btn>
            </template>
            <v-card>
              <v-form ref="form">
                <v-card-title>
                  <span class="headline">{{ formTitle }}</span>
                </v-card-title>

                <v-card-text>
                  <v-container>
                    <v-row>
                      <v-col cols="12" sm="6" md="6">
                        <v-select
                          :items="dataSource"
                          item-text="datasource_name"
                          item-value="id"
                          label="Datasource Name"
                          v-model="editedItem.datasource_id"
                          v-on:change="getTableId()"
                          :rules="validationRules.datasource_id"
                          :disabled="disabledField"
                        ></v-select>
                        <form-error
                          v-if="validationErrors.datasource_id"
                          :errors="validationErrors.datasource_id"
                        ></form-error>
                      </v-col>

                      <v-col cols="12" sm="6" md="6">
                        <v-text-field
                          type="text"
                          v-model="editedItem.datasource_column_number_string"
                          label="Column Index"
                          :counter="3"
                          @keyup.native="setConvertedDatasourceColumnNumber"
                          :rules="validationRules.datasource_column_number"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.datasource_column_number"
                          :errors="validationErrors.datasource_column_number"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-text-field
                          type="text"
                          v-model="editedItem.datasource_column_name"
                          label="Column Name"
                          :counter="255"
                          :rules="validationRules.column_name"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.datasource_column_name"
                          :errors="validationErrors.datasource_column_name"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-select
                          :items="tableDefinition"
                          item-text="column_name"
                          item-value="id"
                          label="Table Column"
                          v-model="editedItem.table_column_id"
                          :rules="validationRules.table_definition_id"
                        ></v-select>
                        <form-error
                          v-if="validationErrors.table_column_id"
                          :errors="validationErrors.table_column_id"
                        ></form-error>
                      </v-col>
                    </v-row>
                  </v-container>
                </v-card-text>

                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="blue darken-1" text @click="close">
                    Cancel
                  </v-btn>
                  <v-btn color="blue darken-1" text @click="save">Save</v-btn>
                </v-card-actions>
              </v-form>
            </v-card>
          </v-dialog>
          <!-- Error Dialog -->
          <v-dialog v-model="errorDialog.status" width="500">
            <v-card>
              <v-card-title class="headline grey lighten-2" primary-title>
                {{ errorDialog.header }}
              </v-card-title>
              ​
              <v-card-text>{{ errorDialog.text }}</v-card-text>
              ​
              <v-divider></v-divider>
              ​
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" text @click="errorDialog.status = false">
                  Close
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="editItem(item)">mdi-pencil</v-icon>
        <v-icon small @click="deleteItem(item)">mdi-delete</v-icon>
      </template>

      <template v-slot:item.datasource_column_number="{ item }">
        {{ number2alphabet(item.datasource_column_number) }}
      </template>
    </v-data-table>
    <v-snackbar
      v-model="snackbarProperty.snackbar"
      :color="snackbarProperty.color"
      :right="snackbarProperty.x === 'right'"
      :timeout="snackbarProperty.timeout"
      :top="snackbarProperty.y === 'top'"
    >
      {{ snackbarProperty.text }}
      <v-btn dark text @click="snackbarProperty.snackbar = false">Close</v-btn>
    </v-snackbar>
  </div>
</template>


<script>
export default {
  data: () => ({
    loadingStatus: true, // This will show loading bar on initialization
    dialog: false,
    errorDialog: {
      status: false,
      text: '',
      header: '',
    },
    headers: [
      { text: 'Datasource ID', value: 'datasource_id' },
      { text: 'Datasource Name', value: 'DataSourceName' },
      { text: 'Datasource Column Index', value: 'datasource_column_number' },
      { text: 'Datasource Column Name', value: 'datasource_column_name' },
      { text: 'Table Name', value: 'DataSourceTableName' },
      { text: 'Table Column ID', value: 'table_column_id' },
      { text: 'Table Column Name', value: 'ColumnName' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    DataColumnMapping: [],
    dataSource: [],
    sortSelectedTable: '',
    tableDefinition: [],
    disabledField: false,
    desserts: [],
    editedIndex: -1,
    editedItem: {
      id: '',
      datasource_id: '',
      datasource_name: '',
      datasource_column_number: '',
      datasource_column_number_string: '',
      datasource_column_name: '',
      table_column_id: '',
      table_name: '',
      table_id: '',
    },
    defaultItem: {
      id: '',
      datasource_id: '',
      datasource_name: '',
      datasource_column_number: '',
      datasource_column_number_string: '',
      datasource_column_name: '',
      table_column_id: '',
      table_name: '',
      table_id: '',
    },

    snackbarProperty: {
      snackbar: false,
      color: '',
      text: '',
      timeout: 5000,
      x: 'right',
      y: 'top',
    },
    validationErrors: [],
    validationRules: {
      column_name: [
        (v) => !!v || 'This field is required',
        (v) =>
          (v && v.length <= 255) || 'Name must be less than 255 characters',
      ],
      table_definition_id: [(v) => !!v || 'This field is required'],
      datasource_id: [(v) => !!v || 'This field is required'],
      datasource_column_number: [
        (v) => !!v || 'This field is required',
        (v) => ((v && v) || '').indexOf(' ') < 0 || 'No spaces are allowed',
        (v) => (v && !!/^[a-zA-Z]+$/.test(v)) || 'Only letters are allowed',
        (v) =>
          (v &&
            v.split('').reduce((r, a) => r * 26 + parseInt(a, 36) - 9, 0) <=
              16384) ||
          `Maximum possible value is 'XFD'`,
      ],
    },
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'New Item' : 'Edit Item';
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
  },

  created() {
    this.getDataSource();
    this.getDatasourceColumns();
  },

  methods: {
    /**
     * Convert Excel column index number(e.g 1,2,3) to Excel column index(e.g A,B,C)
     * @param columnNumber
     * @returns {string|string}
     */
    number2alphabet(columnNumber) {
      for (
        var ret = '', a = 1, b = 26;
        (columnNumber -= a) >= 0;
        a = b, b *= 26
      ) {
        ret = String.fromCharCode(parseInt((columnNumber % b) / a) + 65) + ret;
      }
      return ret;
    },
    /**
     * Convert Excel column index(e.g A,B,C) to Excel column index number(e.g 1,2,3)
     * @param value
     * @returns {number|number}
     */
    alphabet2number(value) {
      // Total number of alphabet = 26, Hexatridecimal(Base 36) number base = 36,
      //9 is just a common number, It added because in Hexatridecimal
      // Maximum column number of Excel is 16384
      var numericValue = value
        .split('')
        .reduce((r, a) => r * 26 + parseInt(a, 36) - 9, 0);
      if (numericValue > 16384) {
        numericValue = 0;
      }
      return numericValue;
    },
    /**
     * Assign converted value to "editedItem.datasource_column_number"
     * @param event
     */
    setConvertedDatasourceColumnNumber(event) {
      var value = event.target.value;
      var numericValue = 0;

      if (/^[a-zA-Z]+$/.test(value)) {
        numericValue = this.alphabet2number(value);
      }
      this.editedItem.datasource_column_number = numericValue;
    },

    showSnackBar(text, color) {
      this.snackbarProperty.snackbar = true;
      this.snackbarProperty.text = text;
      this.snackbarProperty.color = color;
    },

    getDatasourceColumns() {
      axios
        .get('/api/get/datasource-columns')
        .then((response) => {
          this.DataColumnMapping = response.data;
          this.loadingStatus = false; // This will remove loading bar
          this.showSnackBar('Data Fetched Successfully!', 'success');
        })
        .catch((error) => console.log(error));
    },

    getDataSource() {
      axios
        .get('/api/get/data-source/all')
        .then((response) => {
          this.dataSource = response.data;
        })
        .catch((error) => console.log(error));
    },

    getTableColumns(item) {
      var table_id = item;
      axios
        .get('/api/get/table-columns/' + table_id)
        .then((response) => {
          this.tableDefinition = response.data;
        })
        .catch((error) => {
          console.log(error.response);
        });
    },

    getTableId() {
      axios
        .get('/api/get/table-id-datasource/' + this.editedItem.datasource_id)
        .then((response) => {
          this.getTableColumns(response.data);
        })
        .catch((error) => console.log(error));
    },

    sortByDataSource() {
      this.loadingStatus = true;
      this.editedItem.datasource_id = this.sortSelectedTable;

      if (this.sortSelectedTable) {
        this.getTableId();
        axios
          .get(`/api/get/datasource-columns?id=${this.sortSelectedTable}`)
          .then((response) => {
            this.DataColumnMapping = response.data;
            this.loadingStatus = false; // This will remove loading bar
            this.showSnackBar('Data Sorted Successfully!', 'success');
          })
          .catch((error) => console.log(error));
      } else {
        this.getDatasourceColumns();
      }
    },

    editItem(item) {
      this.editedIndex = this.DataColumnMapping.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.editedItem.datasource_column_number_string = this.number2alphabet(
        item.datasource_column_number
      );
      this.disabledField = true;

      axios
        .get('/api/get/table-id-datasource/' + this.editedItem.datasource_id) //Get table_id from datasource_id
        .then((response) => {
          this.getTableColumns(response.data);
        })
        .catch((error) => {
          console.log(error.response);
        });
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.DataColumnMapping.indexOf(item);
      if (confirm('Are you sure you want to delete this item?')) {
        axios
          .post('/api/delete/datasource-columns', item)
          .then((response) => {
            this.DataColumnMapping.splice(index, 1);
            this.showSnackBar('Deleted Successfully!', 'success');
          })
          .catch((error) => {
            this.errorDialog.header = error.response.statusText;
            this.errorDialog.text = error.response.data.error_message;
            this.errorDialog.status = true;
          });
      }
    },

    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
        this.editedItem.datasource_id = this.sortSelectedTable;
        this.disabledField = false;
        this.validationErrors = [];
        this.$refs.form.resetValidation();
      }, 300);
    },

    save() {
      if (!this.$refs.form.validate()) {
        return;
      }
      if (this.editedIndex > -1) {
        var updateIndex = this.editedIndex;
        axios
          .post('/api/update/datasource-columns', this.editedItem)
          .then((response) => {
            if (response.data.error) {
              this.showSnackBar(response.data.error, 'error');
              this.validationErrors = [];
            } else {
              this.close();
              Object.assign(this.DataColumnMapping[updateIndex], response.data);
              this.showSnackBar('Updated Successfully!', 'success');
            }
          })
          .catch((error) => {
            console.log(error.response);
            if (error.response && error.response.status == 422) {
              this.validationErrors = error.response.data;
            }
          });
      } else {
        axios
          .post('/api/add/datasource-columns', this.editedItem)
          .then((response) => {
            if (response.data.error) {
              this.showSnackBar(response.data.error, 'error');
              this.validationErrors = [];
            } else {
              this.close();
              this.showSnackBar('Created Successfully!', 'success');
              this.DataColumnMapping.push(response.data);
            }
          })
          .catch((error) => {
            if (error.response && error.response.status == 422) {
              this.validationErrors = error.response.data;
            } else if (error.response && error.response.status == 500) {
              this.errorDialog.header = error.response.statusText;
              this.errorDialog.text = error.response.data.error_message;
              this.errorDialog.status = true;
            }
          });
      }
    },
  },
};
</script>
