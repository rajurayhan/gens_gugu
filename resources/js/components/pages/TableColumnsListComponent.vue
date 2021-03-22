<template>
  <div>
    <v-progress-circular
      :size="100"
      color="primary"
      indeterminate
      v-if="showLoader"
    ></v-progress-circular>

    <v-data-table
      :headers="headers"
      :items="tableDefinitions"
      :disable-pagination="true"
      :hide-default-footer="true"
      :loading="loadingStatus"
      class="elevation-1"
      v-if="!showLoader"
      fixed-header
      must-sort
      height="70vh"
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
          <v-toolbar-title>Table Columns</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-select
            :items="tableList"
            item-text="table_name"
            item-value="id"
            label="Table List"
            :clearable="true"
            v-model="sortSelectedTable"
            v-on:change="sortByTable()"
            :disabled="loadingStatus"
          ></v-select>
          <v-spacer></v-spacer>
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
                      <v-col cols="12" sm="6" md="4">
                        <v-select
                          :items="tableList"
                          item-text="table_name"
                          item-value="id"
                          label="Table Name"
                          v-model="editedItem.table_id"
                          :disabled="editedIndex > -1"
                          required
                          :rules="validationRules.table_id"
                        ></v-select>
                        <form-error
                          v-if="validationErrors.table_id"
                          :errors="validationErrors.table_id"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          :counter="64"
                          type="text"
                          required
                          :rules="validationRules.column_name"
                          v-model="editedItem.column_name"
                          label="Column Name"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.column_name"
                          :errors="validationErrors.column_name"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          :counter="255"
                          type="text"
                          required
                          :rules="validationRules.column_name_alias"
                          v-model="editedItem.column_name_alias"
                          label="Column Name Alias"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.column_name_alias"
                          :errors="validationErrors.column_name_alias"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-select
                          :items="dataTypes"
                          item-text="text"
                          item-value="value"
                          label="Data Type"
                          v-model="editedItem.data_type"
                          v-on:change="setAttributesByDataType()"
                          required
                          :rules="validationRules.data_type"
                        ></v-select>
                        <form-error
                          v-if="validationErrors.data_type"
                          :errors="validationErrors.data_type"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          type="number"
                          :rules="
                            disabledFields.length ? [] : validationRules.length
                          "
                          :required="disabledFields.length == false"
                          v-model="editedItem.length"
                          label="Length"
                          :disabled="disabledFields.length"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.length"
                          :errors="validationErrors.length"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          type="number"
                          :rules="
                            disabledFields.maximum_number
                              ? []
                              : validationRules.maximum_number
                          "
                          :required="!disabledFields.maximum_number"
                          v-model="editedItem.maximum_number"
                          label="Max Number"
                          :disabled="disabledFields.maximum_number"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.maximum_number"
                          :errors="validationErrors.maximum_number"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          type="number"
                          :rules="
                            disabledFields.decimal_part
                              ? []
                              : validationRules.decimal_part
                          "
                          :required="!disabledFields.decimal_part"
                          v-model="editedItem.decimal_part"
                          label="Decimal Part"
                          :disabled="disabledFields.decimal_part"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.decimal_part"
                          :errors="validationErrors.decimal_part"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="4">
                        <v-text-field
                          type="text"
                          v-model="editedItem.validation"
                          label="Validation"
                          :disabled="disabledFields.validation"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.validation"
                          :errors="validationErrors.validation"
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

              <v-card-text>{{ errorDialog.text }}</v-card-text>

              <v-divider></v-divider>

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
    showLoader: false,
    headers: [
      {
        text: 'Table',
        align: 'start',
        value: 'tableName',
      },
      { text: 'Column', value: 'column_name' },
      { text: 'Column Name Alias', value: 'column_name_alias' },
      { text: 'Data Type', value: 'data_type' },
      { text: 'Length', value: 'length' },
      { text: 'Maximum Number', value: 'maximum_number' },
      { text: 'Decimal Part', value: 'decimal_part' },
      { text: 'Validation', value: 'validation' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    dataTypes: [
      { text: 'BIGINT', value: 'bigint' },
      { text: 'DATE', value: 'date' },
      { text: 'DATETIME', value: 'datetime' },
      { text: 'DECIMAL', value: 'decimal' },
      { text: 'VARCHAR', value: 'varchar' },
    ],
    disabledFields: {
      length: false,
      maximum_number: false,
      validation: false,
      decimal_part: false,
    },
    tableDefinitions: [],
    tableList: [],
    sortSelectedTable: '',
    desserts: [],
    editedIndex: -1,
    editedItem: {
      table_id: '',
      tableName: '',
      column_name: '',
      column_name_alias: '',
      data_type: '',
      length: 0,
      maximum_number: 0,
      decimal_part: 0,
      validation: '',
    },
    defaultItem: {
      table_id: '',
      tableName: '',
      column_name: '',
      column_name_alias: '',
      data_type: '',
      length: 0,
      maximum_number: 0,
      decimal_part: 0,
      validation: '',
    },

    validationRules: {
      length: [(v) => !!v || 'This field is required'],
      maximum_number: [(v) => !!v || 'This field is required'],
      decimal_part: [(v) => !!v || 'This field is required'],
      table_id: [(v) => !!v || 'This field is required'],
      column_name: [
        (v) => !!v || 'This field is required',
        (v) => ((v && v) || '').indexOf(' ') < 0 || 'No spaces are allowed',
        (v) => (v && v.length <= 64) || 'Name must be less than 64 characters',
      ],

      column_name_alias: [
        (v) => !!v || 'This field is required',
        (v) =>
          (v && v.length <= 255) || 'Name must be less than 255 characters',
      ],

      data_type: [(v) => !!v || 'This field is required'],
    },

    errorDialog: {
      status: false,
      text: '',
      header: '',
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
    this.getTableList();
    this.getTableColumns();
  },

  methods: {
    showSnackBar(text, color) {
      this.snackbarProperty.snackbar = true;
      this.snackbarProperty.text = text;
      this.snackbarProperty.color = color;
    },

    getTableColumns() {
      axios
        .get('/api/v1/table-columns')
        .then((response) => {
          this.tableDefinitions = response.data.columns;
          this.loadingStatus = false; // This will remove loading bar
          this.showSnackBar('Data Fetched Successfully!', 'success');
        })
        .catch((error) => console.log(error));
    },

    getTableList() {
      axios
        .get('/api/v1/tables')
        .then((response) => {
          this.tableList = response.data.tables;
        })
        .catch((error) => console.log(error));
    },

    sortByTable() {
      this.loadingStatus = true;
      this.editedItem.table_id = this.sortSelectedTable;
      if (this.sortSelectedTable) {
        axios
          .get(`/api/v1/table-columns?id=${this.sortSelectedTable}`)
          .then((response) => {
            this.tableDefinitions = response.data.columns;
            this.loadingStatus = false; // This will remove loading bar
            this.showSnackBar('Data Sorted Successfully!', 'success');
          })
          .catch((error) => console.log(error));
      } else {
        this.getTableColumns();
      }
    },

    changeValidationBasedOnDataType(dataType) {
      switch (dataType) {
        case 'varchar':
          this.validationRules.length = [
            (v) => !!v || 'This field is required',
            (v) => (v && v >= 1) || 'Value should be greater than or equal 1',
            (v) =>
              (v && v <= 16383) || 'Value should be less than or equal 16383',
          ];
          break;

        case 'bigint':
          this.validationRules.length = [
            (v) => !!v || 'This field is required',
            (v) => (v && v >= 1) || 'Value should be greater than or equal 1',
            (v) => (v && v <= 255) || 'Value should be less than or equal 255',
          ];
          break;
        case 'decimal':
          this.validationRules.maximum_number = [
            (v) => !!v || 'This field is required',
            (v) => (v && v >= 1) || 'Value should be greater than or equal 1',
            (v) => (v && v <= 65) || 'Value should be less than or equal 65',
          ];

          this.validationRules.decimal_part = [
            (v) => !!v || 'This field is required',
            (v) => (v && v >= 1) || 'Value should be greater than or equal 1',
            (v) => (v && v <= 30) || 'Value should be less than or equal 30',
          ];
          break;
        default:
      }
    },

    setAttributesByDataType(item = '') {
      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }

      this.editedItem.length = item ? item.length : '';
      this.editedItem.maximum_number = item ? item.maximum_number : '';
      this.editedItem.validation = item ? item.validation : '';
      this.editedItem.decimal_part = item ? item.decimal_part : '';

      this.changeValidationBasedOnDataType(this.editedItem.data_type);
      if (
        this.editedItem.data_type == 'date' ||
        this.editedItem.data_type == 'datetime'
      ) {
        this.disabledFields.length = true;
        this.disabledFields.maximum_number = true;
        this.disabledFields.validation = true;
        this.disabledFields.decimal_part = true;
      } else if (this.editedItem.data_type == 'varchar') {
        this.editedItem.maximum_number = '';
        this.editedItem.decimal_part = '';
        this.disabledFields.maximum_number = true;
        this.disabledFields.decimal_part = true;

        this.disabledFields.validation = false;
        this.disabledFields.length = false;
        this.editedItem.length = item ? item.length : 255;
      } else if (this.editedItem.data_type == 'decimal') {
        this.disabledFields.length = true;
        this.editedItem.length = '';

        this.disabledFields.maximum_number = false;
        this.disabledFields.decimal_part = false;

        this.editedItem.maximum_number = item ? item.maximum_number : 10;
        this.editedItem.decimal_part = item ? item.decimal_part : 2;

        this.disabledFields.validation = false;
      } else if (this.editedItem.data_type == 'bigint') {
        this.disabledFields.maximum_number = true;
        this.disabledFields.decimal_part = true;

        this.editedItem.maximum_number = '';
        this.editedItem.decimal_part = '';

        this.disabledFields.length = false;
        this.editedItem.length = item ? item.length : 10;
        this.disabledFields.validation = false;
      } else {
        this.disabledFields.length = false;
        this.disabledFields.maximum_number = false;
        this.disabledFields.validation = false;
        this.disabledFields.decimal_part = false;
      }
    },

    editItem(item) {
      this.editedIndex = this.tableDefinitions.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;

      this.setAttributesByDataType(item);
    },

    deleteItem(item) {
      const index = this.tableDefinitions.indexOf(item);

      if (confirm('Are you sure you want to delete this item?')) {
        this.showLoader = true;
        axios
          .post('/api/v1/delete/table-columns', item)
          .then((response) => {
            if (response.data.error) {
              this.showLoader = false;
              this.showSnackBar(response.data.error, 'error');
              this.validationErrors = [];
            } else {
              this.tableDefinitions.splice(index, 1);
              this.showLoader = false;
              this.showSnackBar('Deleted Successfully!', 'success');
            }
          })
          .catch((error) => {
            this.showLoader = false;
          });
      }
    },

    close() {
      this.dialog = false;
      this.disabledFields.length = false;
      this.disabledFields.maximum_number = false;
      this.disabledFields.validation = false;
      this.disabledFields.decimal_part = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedItem.table_id = this.sortSelectedTable;
        this.editedIndex = -1;
        this.validationErrors = [];
        this.$refs.form.resetValidation();
      }, 300);
    },

    validate() {
      this.$refs.form.validate();
    },

    save() {
      if (!this.$refs.form.validate()) {
        return;
      }
      if (this.editedIndex > -1) {
        var updateIndex = this.editedIndex;
        axios
          .post('/api/v1/update/table-columns', this.editedItem)
          .then((response) => {
            if (response.data.error) {
              this.showSnackBar(response.data.error, 'error');
              this.validationErrors = [];
            } else {
              Object.assign(
                this.tableDefinitions[updateIndex],
                response.data.column
              );
              this.close();
              this.showSnackBar('Updated Successfully!', 'success');
            }
          })
          .catch((error) => {
            if (error.response && error.response.status == 422) {
              this.validationErrors = error.response.data;
            } else if (error.response && error.response.status == 500) {
              this.close();
              this.errorDialog.header = error.response.statusText;
              this.errorDialog.text = error.response.data.error_message;
              this.errorDialog.status = true;
            }
          });
      } else {
        axios
          .post('/api/v1/add/table-columns', this.editedItem)
          .then((response) => {
            console.log(response);
            this.close();
            this.showSnackBar('Created Successfully!', 'success');
            this.tableDefinitions.push(response.data.column);
          })
          .catch((error) => {
            if (error.response && error.response.status == 422) {
              this.validationErrors = error.response.data;
            } else if (error.response && error.response.status == 500) {
              this.close();
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
