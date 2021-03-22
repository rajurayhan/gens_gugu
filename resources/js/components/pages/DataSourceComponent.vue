<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="DataSource"
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
          <v-toolbar-title>Datasource</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
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
                      <v-col cols="12" sm="6" md="6">
                        <v-text-field
                          type="text"
                          v-model="editedItem.datasource_name"
                          label="Source Name"
                          :counter="255"
                          :rules="validationRules.datasource_name"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.datasource_name"
                          :errors="validationErrors.datasource_name"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-select
                          :items="tableList"
                          item-text="table_name"
                          item-value="id"
                          label="Table Id"
                          v-model="editedItem.table_id"
                          :rules="validationRules.table_id"
                        ></v-select>
                        <form-error
                          v-if="validationErrors.table_id"
                          :errors="validationErrors.table_id"
                        ></form-error>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-text-field
                          type="number"
                          v-model="editedItem.starting_row_number"
                          label="Starting Row Number"
                          :counter="7"
                          :rules="validationRules.starting_row_number"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.starting_row_number"
                          :errors="validationErrors.starting_row_number"
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
      {
        text: 'ID',
        align: 'start',
        value: 'id',
      },
      { text: 'Datasource', value: 'datasource_name' },
      { text: 'Table id', value: 'table_id' },
      { text: 'Table Name', value: 'tableName' },
      { text: 'Starting row number', value: 'starting_row_number' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    tableDefinitions: [],
    DataSource: [],
    sortSelectedTable: '',
    tableList: '',
    desserts: [],
    editedIndex: -1,
    editedItem: {
      datasource_name: '',
      table_id: '',
      starting_row_number: '',
    },
    defaultItem: {
      datasource_name: '',
      table_id: '',
      starting_row_number: '',
    },

    snackbarProperty: {
      snackbar: false,
      color: '',
      // mode: 'vertical',
      text: '',
      timeout: 5000,
      x: 'right',
      y: 'top',
    },
    validationErrors: [],
    validationRules: {
      datasource_name: [
        (v) => !!v || 'This field is required',
        (v) =>
          (v && v.length <= 255) || 'Name must be less than 255 characters',
      ],
      table_id: [(v) => !!v || 'This field is required'],
      starting_row_number: [
        (v) => !!v || 'This field is required',
        (v) => (v && v.length <= 7) || 'Number must be less than 7 characters',
        (v) => v > 0 || 'The value should not be negative or 0.',
        (v) => v <= 1048576 || 'The maximum possible value is 1048576',
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
    this.getTableList();
    this.getDataSource();
  },

  methods: {
    showSnackBar(text, color) {
      this.snackbarProperty.snackbar = true;
      this.snackbarProperty.text = text;
      this.snackbarProperty.color = color;
    },

    getDataSource() {
      axios
        .get('/api/get/data-source/all')
        .then((response) => {
          this.DataSource = response.data;
          this.loadingStatus = false;
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

    editItem(item) {
      this.editedIndex = this.DataSource.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    async deleteItem(item) {
      let confirmMessage = 'Are you sure you want to delete this item?';
      await axios
        .get(`/api/get/datasource-columns?id=${item.id}`)
        .then((response) => {
          if (response.data.length > 0) {
            confirmMessage += '\nThis datasource has datasource columns.';
          }
        })
        .catch((error) => {
          this.errorDialog.header = error.response.statusText;
          this.errorDialog.text = error.response.data.error_message;
          this.errorDialog.status = true;
          exit;
        });

      if (confirm(confirmMessage)) {
        axios
          .post('/api/delete/data-source', item)
          .then((response) => {
            if (response.data.error) {
              this.showSnackBar(response.data.error, 'error');
              this.validationErrors = [];
            } else {
              const index = this.DataSource.indexOf(item);
              this.DataSource.splice(index, 1);
              this.showSnackBar('Deleted Successfully!', 'success');
            }
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
          .post('/api/update/data-source', this.editedItem)
          .then((response) => {
            this.close();
            Object.assign(this.DataSource[updateIndex], response.data);
            this.showSnackBar('Updated Successfully!', 'success');
          })
          .catch((error) => {
            if (error.response && error.response.status == 422) {
              this.validationErrors = error.response.data;
            }
          });
      } else {
        axios
          .post('/api/add/data-source', this.editedItem)
          .then((response) => {
            this.close();
            this.showSnackBar('Created Successfully!', 'success');
            this.DataSource.push(response.data);
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
