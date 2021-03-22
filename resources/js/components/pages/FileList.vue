<template>
  <v-app>
    <div>
      <!-- Progress circule for delete processing -->
      <v-progress-circular
        :size="100"
        color="primary"
        indeterminate
        v-if="isDeleting"
      ></v-progress-circular>

      <v-data-table
        :headers="fileTableHeaders"
        :items="fileTableItems"
        :disable-pagination="true"
        :hide-default-footer="true"
        :options.sync="options"
        :loading="isLoadingFileTable"
        height="70vh"
        fixed-header
        must-sort
        class="elevation-1"
        v-if="!isDeleting"
      >
        <template v-slot:top>
          <v-toolbar flat color="white">
            <v-toolbar-title>ファイル一覧</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <v-spacer></v-spacer>
          </v-toolbar>
        </template>

        <template v-for="header in fileTableHeaders" v-slot:[`item.${header.value}`]="{ item }">
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
        <template v-slot:item.actions="{ item }">
          <v-icon
            small
            @click="deleteFile(item)"
            :disabled="isLoadingFileTable"
          >
            mdi-delete
          </v-icon>
        </template>
      </v-data-table>

      <v-dialog v-model="showResponsedialog" width="500" persistent scrollable>
        <v-card style="max-height: 400px">
          <v-card-title class="headline grey lighten-2" primary-title>
            {{ responseDialog.message }}
          </v-card-title>

          <v-card-text :class="responseDialog.visible">
            詳細：
            <br />
            {{ responseDialog.details[0] }}
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="closeResponseDialog">Close</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-snackbar
        v-model="snackbarProperty.snackbar"
        :color="snackbarProperty.color"
        :right="snackbarProperty.x === 'right'"
        :top="snackbarProperty.y === 'top'"
        :timeout="snackbarProperty.timeout"
      >
        {{ snackbarProperty.text }}
        <v-btn dark text @click="snackbarProperty.snackbar = false">
          Close
        </v-btn>
      </v-snackbar>
    </div>
  </v-app>
</template>

<script>
export default {
  data: () => ({
    isLoadingFileTable: true,
    isDeleting: false,
    fileTableHeaders: [
      { text: 'ファイル名', value: 'original_name', width: '40%' },
      { text: 'シート名', value: 'sheet_name', width: '20%' },
      { text: 'アップロード日時', value: 'updated_at', width: '30%' },
      { text: '削除', value: 'actions', sortable: false, width: '10%' },
    ],
    fileTableItems: [],
    showResponsedialog: false,
    responseDialog: {
      visible: 'd-none',
      message: '',
      details: [],
    },
    snackbarProperty: {
      snackbar: false,
      color: '',
      text: '',
      timeout: 5000,
      x: 'right',
      y: 'top',
    },
    options: {
      sortBy: ['updated_at'],
      sortDesc: [true],
    },
  }),
  created() {
    this.getFileListAndSetTableItems();
  },
  methods: {
    getFileListAndSetTableItems() {
      // テーブルのitemを初期化する
      this.fileTableItems = [];
      this.isLoadingFileTable = true;
      // ファイル一覧を取得
      axios
        .get('/api/v1/excel_files')
        .then((response) => {
          if (response.status == 200) {
            // テーブルのitemを更新する
            this.fileTableItems = response.data.files;
          } else {
            // システム管理者に連絡するようメッセージを出す
            this.showSystemErrorResponseDialog(
              'アップロード済のファイルを取得できませんでした。'
            );
          }
        })
        .catch((error) => {
          // システム管理者に連絡するようメッセージを出す
          this.showSystemErrorResponseDialog(error.response.data.error_message);
        })
        .finally(() => {
          this.isLoadingFileTable = false;
        });
    },
    showSystemErrorResponseDialog(details) {
      this.responseDialog.message =
        'システムエラーが発生しました。IT部門に連絡してください';
      this.responseDialog.details.push(details);
      this.responseDialog.visible = 'd-flex';
      this.showResponsedialog = true;
    },
    closeResponseDialog() {
      // Hide the showResponsedialog
      this.showResponsedialog = false;
      // Initialize this.responseDialog
      this.responseDialog.message = '';
      this.responseDialog.details = [];
      this.responseDialog.visible = 'd-none';
    },
    deleteFile(item) {
      if (confirm('Are you sure you want to delete this file?')) {
        this.isDeleting = true;
        axios
          .delete('/api/v1/excel_files/' + item.id)
          .then((response) => {
            if (response.status == 200) {
              this.showSnackBar('ファイル削除に成功しました。', 'success');
              // 成功したらファイル一覧を再度取得
              this.getFileListAndSetTableItems();
            } else {
              this.showSystemErrorResponseDialog(
                '処理を正常に完了できませんでした。'
              );
            }
          })
          .catch((error) => {
            if (error.response.status == 404) {
              // ファイルがなかったことを通知
              this.responseDialog.message = error.response.data.error_message;
              this.responseDialog.details = error.response.data.error_details;
              this.responseDialog.visible = 'd-flex';
              this.showResponsedialog = true;
              // 既に削除されている可能性があるのでファイル一覧を再度取得
              this.getFileListAndSetTableItems();
            } else {
              this.showSystemErrorResponseDialog(
                error.response.data.error_message
              );
            }
          })
          .finally(() => {
            this.isDeleting = false;
          });
      }
    },
    showSnackBar(text, color) {
      this.snackbarProperty.snackbar = true;
      this.snackbarProperty.text = text;
      this.snackbarProperty.color = color;
    },
  },
};
</script>
