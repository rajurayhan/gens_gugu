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
      :items="tableList"
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
          <v-toolbar-title>Table Lists</v-toolbar-title>
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
                      <v-col cols="12">
                        <v-text-field
                          :counter="64"
                          type="text"
                          required
                          :rules="validationRules.table_name"
                          v-model="editedItem.table_name"
                          label="Table Name"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.table_name"
                          :errors="validationErrors.table_name"
                        ></form-error>
                      </v-col>
                      <v-col cols="12">
                        <v-text-field
                          :counter="255"
                          required
                          :rules="validationRules.table_name_alias"
                          type="text"
                          v-model="editedItem.table_name_alias"
                          label="Table Name Alias"
                        ></v-text-field>
                        <form-error
                          v-if="validationErrors.table_name_alias"
                          :errors="validationErrors.table_name_alias"
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
          <!-- Confirm Dialog -->
          <v-dialog v-model="confirmDialog.status" width="500">
            <v-card>
              <v-card-title class="headline grey lighten-2" primary-title>
                Attention
              </v-card-title>

              <v-card-text v-html="confirmDialog.text"></v-card-text>

              <v-divider></v-divider>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  text
                  @click="deleteItem(confirmDialog.item)"
                >
                  OK
                </v-btn>
                <v-btn
                  color="primary"
                  text
                  @click="confirmDialog.status = false"
                >
                  Cancel
                </v-btn>
              </v-card-actions>
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
        <v-icon small @click="confirmItemRelation(item)">mdi-delete</v-icon>
      </template>
    </v-data-table>
    <v-snackbar
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
  </div>
</template>

<script>
export default {
  data: () => ({
    loadingStatus: true, // This will show loading bar on initialization
    showLoader: false,
    dialog: false,
    confirmDialog: {
      status: false,
      text: '',
    },
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
      { text: 'Table', value: 'table_name' },
      { text: 'Table Name Alias', value: 'table_name_alias' },
      { text: 'Updated By', value: 'updated_by' },
      { text: 'Updated At', value: 'updated_at' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    // tableDefinitions: [],
    tableList: [],
    sortSelectedTable: '',
    // selectedTableEdit: '',
    desserts: [],
    editedIndex: -1,
    editedItem: {
      table_name: '',
      table_name_alias: '',
    },
    defaultItem: {
      table_name: '',
      table_name_alias: '',
    },

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
    validationRules: {
      table_name: [
        (v) => !!v || 'This field is required',
        (v) => (v && v.length <= 64) || 'Name must be less than 64 characters',
      ],
      table_name_alias: [
        (v) => !!v || 'This field is required',
        (v) =>
          (v && v.length <= 255) || 'Name must be less than 255 characters',
      ],
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
  },

  methods: {
    showSnackBar(text, color, multiLine = false) {
      this.snackbarProperty.snackbar = true;
      this.snackbarProperty.text = text;
      this.snackbarProperty.color = color;
      this.snackbarProperty.multiLine = multiLine;
    },

    getTableList() {
      axios
        .get('/api/v1/tables')
        .then((response) => {
          this.tableList = response.data.tables;
          this.loadingStatus = false;
        })
        .catch((error) => console.log(error));
    },

    editItem(item) {
      this.editedIndex = this.tableList.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    confirmItemRelation(item) {
      if (!confirm('Are you sure you want to delete this item?')) {
        return;
      }

      axios
        .get(`/api/v1/confirm-relation/tables?id=${item.id}`)
        .then((response) => {
          if (response.data.message == '') {
            this.deleteItem(item);
          } else if (response.data.message) {
            this.confirmDialog.item = item;
            this.confirmDialog.text = response.data.message.replace(
              /\n/g,
              '<br/>'
            );
            this.confirmDialog.status = true;
          } else {
            this.showSnackBar(response.data.error, 'error');
          }
        })
        .catch((error) => {
          this.errorDialog.header = error.response.statusText;
          this.errorDialog.text = error.response.data.error_message;
          this.errorDialog.status = true;
        });
    },

    async deleteItem(item) {
      this.confirmDialog.status = false;
      this.showLoader = true;

      await axios
        .post('/api/v1/delete/tables', item)
        .then((response) => {
          if (response.data.success) {
            const index = this.tableList.indexOf(item);
            this.tableList.splice(index, 1);
            this.showSnackBar(response.data.success, 'success');
          } else {
            this.showSnackBar(response.data.error, 'error');
          }
        })
        .catch((error) => {
          this.errorDialog.header = error.response.statusText;
          this.errorDialog.text = error.response.data.error_message;
          this.errorDialog.status = true;
        });

      this.showLoader = false;
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
          .post('/api/v1/update/tables', this.editedItem)
          .then((response) => {
            Object.assign(this.tableList[updateIndex], response.data.table);
            this.close();
            this.showSnackBar('Updated Successfully!', 'success');
          })
          .catch((error) => {
            if (error.response && error.response.status == 422) {
              this.validationErrors = error.response.data;
            }
          });
      } else {
        axios
          .post('/api/v1/add/tables', this.editedItem)
          .then((response) => {
            this.showSnackBar('Created Successfully!', 'success');
            this.tableList.push(response.data.table);
            this.close();
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
      // this.close()
    },
  },
};
</script>

