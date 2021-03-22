<template>
  <v-app>
    <v-card class="mx-auto ma-5" width="75%">
      <v-col>
        <v-progress-linear
          id="progressBar"
          :active="isProcessing"
          :indeterminate="isProcessing"
          absolute
          top
          color="light-blue accent-4"
        ></v-progress-linear>
      </v-col>
      <v-card-title>
        <h1 class="display-1">Definition Upload</h1>
      </v-card-title>
      <v-container>
        <!-- D＆Dエリア開始 -->
        <div id="filepreview" class="mt-n5">
          <input
            type="file"
            id="file"
            name="file"
            @change="selectedFile"
            class="dropify form-control"
            required
          />
        </div>
        <div id="resultTable">
          <table class="table table-responsive" id="exceltable"></table>
        </div>
        <v-row class="m-auto">
          <!-- シートボタン -->
          <div
            class="btn-group sheetBtns"
            @click="sheetBtnClick"
            role="group"
            aria-label="Sheet List"
          ></div>
          <!-- ファイルクリアボタン -->
          <div style="margin-left: auto;">
            <v-btn id="clearSheet" class="btn clearSheet" @click="clearSheet" color="error">
              <v-icon>ti-trash</v-icon>
              <!-- <v-icon>mdi-trash-can</v-icon> -->
            </v-btn>
          </div>
        </v-row>
        <!-- 送信ボタン -->
        <v-card-actions class="mt-3">
          <v-btn
            block
            id="submitBtn"
            @click="submit"
            :disabled="isProcessing"
            color="indigo"
            class="white--text"
          >送信</v-btn>
        </v-card-actions>
      </v-container>

      <!-- Dialog to show response -->
      <v-dialog v-model="dialog" width="500" persistent scrollable>
        <v-card style="max-height: 400px">
          <v-card-title
            id="vCardTitle"
            class="headline grey lighten-2"
            primary-title
          >{{ responseDialog.message }}</v-card-title>
          <!-- <v-divider></v-divider> -->

          <v-card-text id="successDialog" :class="responseDialog.visible200">
            データソース名: {{ responseDialog.datasourceName }}
            <br />
            テーブル名: {{ responseDialog.tableName }}
          </v-card-text>

          <v-card-text id="parameterErrorDialog" :class="responseDialog.visible400">
            <p>エラー件数: {{ responseDialog.count }}</p>
            <br />
            <v-list>
              <v-list-item v-for="detail in responseDialog.details" :key="detail.index">{{ detail }}</v-list-item>
            </v-list>
          </v-card-text>

          <v-card-text id="errorDialog" :class="responseDialog.visibleElse">
            詳細：
            <br />
            <v-list>
              <v-list-item v-for="detail in responseDialog.details" :key="detail.index">{{ detail }}</v-list-item>
            </v-list>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn id="dialogCloseBtn" color="primary" @click="closeResponseDialog">Close</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <!-- Confirm Dialog -->
      <v-dialog v-model="confirmDialog.status" width="500">
        <v-card>
          <v-card-title
            class="headline grey lighten-2"
            primary-title
            id="confirmDialogTitle"
          >Attention</v-card-title>
          <v-card-text v-html="confirmDialog.text" id="confirmDialogText"></v-card-text>
          <v-divider></v-divider>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" text id="confirmDialogOK" @click="reSubmit()">OK</v-btn>
            <v-btn
              color="primary"
              text
              id="confirmDialogCancel"
              @click="confirmDialog.status = false;"
            >Cancel</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-col class="mt">
        <v-progress-linear
          :active="isProcessing"
          :indeterminate="isProcessing"
          absolute
          bottom
          color="light-blue accent-4"
        ></v-progress-linear>
      </v-col>
    </v-card>
  </v-app>
</template>

<script>
import 'material-design-icons-iconfont/dist/material-design-icons.css'; // Ensure you are using css-loader
import 'dropify/dist/css/dropify.min.css';
import 'dropify/dist/js/dropify.min.js';
export default {
  self: this, // For setting value of sheet name
  data: () => ({
    isProcessing: false,
    fileInfosObject: {
      size: '',
      sheets: '',
      time: '',
    },
    uploadFile: '',
    sheet_name: '',
    dialog: false,
    responseDialog: {
      visible: false,
      visible200: 'd-none',
      visible400: 'd-none',
      visibleElse: 'd-none',
      color: '',
      count: '',
      message: '',
      tableName: '',
      datasourceName: '',
      details: [],
    },
    confirmDialog: {
      status: false,
      text: '',
    },
  }),
  mounted() {
    // Dropifyの初期化
    $('#file').dropify({
      messages: {
        default:
          'ここにファイルをドラッグアンドドロップするか、クリックしてください',
        replace: 'ドラッグアンドドロップまたはクリックして置換',
        remove: '削除する',
        error: 'おっと、何か間違ったことが起こった。',
      },
    });
    $('.file-icon > p').css('font-size', '20px');
    $('.dropify-clear').click(function () {
      $('#exceltable').empty();
      $('#selectSheet').empty();
      $('#selectSheet').append(
        $('<option>', {
          value: '',
          text: '選択する シート',
        })
      );
    });
    $('.clearSheet').hide();
  },
  methods: {
    selectedFile(e) {
      this.uploadFile = e.target.files[0];
      e.stopPropagation();
      $('#exceltable').empty();
      $('.sheetBtns').empty();
      self.sheet_name = '';
      e.stopPropagation();
      if (e.type == 'drop') {
        var files = e.dataTransfer.files,
          f = files[0];
      } else {
        var files = e.target.files,
          f = files[0];
      }
      if (!f) {
        return false;
      }
      var lastDot = f.name.lastIndexOf('.');
      var ext = f.name.substring(lastDot + 1);
      var supportedFileTypes = ['xlsx', 'xls', 'xlsm'];
      if ($.inArray(ext, supportedFileTypes) < 0) {
        this.showError(
          'おっと、それはExcelファイルではないようです。Excelファイルをアップロードしてください。'
        );
        $('.dropify-clear').trigger('click');
        return false;
      }
      var reader = new FileReader();
      // Define "this2" to use "this" in another instance
      var this2 = this;
      reader.onload = function (e) {
        var data = new Uint8Array(e.target.result);
        var showPreview;
        this2.rawData = data;
        this2.rawDatafileSize = this2.rawData.length;
        var _size = (this2.rawDatafileSize / (1024 * 1000)).toFixed(2);
        if (_size > 50) {
          // toastr.error('Maximum File size 50MB allowed.');
          this2.showError('50MBより大きいファイルはアップロードできません。');
          $('.dropify-clear').trigger('click');
          return false;
        }
        if (_size >= 10 && _size <= 50) {
          swal({
            title: '本当に実行しますか？',
            text: `このファイルのサイズは ${_size} MBです。処理に時間がかかるかもしれませんが、実行しますか？`,
            type: 'warning',
            confirmButtonText: 'Yes',
            showCancelButton: true,
          }).then((result) => {
            if (result.value) {
              swal({
                title: 'プレビューを表示しますか？',
                text: `表示まで時間がかかるかもしれません。プレビューを表示しますか？`,
                type: 'warning',
                confirmButtonText: 'Yes',
                showCancelButton: true,
              }).then((confirm) => {
                var workbook = XLSX.read(data, {
                  type: 'array',
                  bookSheets: true,
                  sheetRows: 1,
                });
                var sheets = workbook.SheetNames;
                this2.fileInfosObject.sheets = sheets.length;

                var sheetButtons = ``;
                $.each(sheets, function (key, sheet) {
                  if (key == 0) {
                    sheetButtons += `<v-btn-toggle><v-btn type="button" value="${sheet}" data-sheet-name="${sheet}" class="btn btn-secondary singleSheetBtn active">${sheet}</v-btn>`;
                  } else {
                    sheetButtons += `<v-btn type="button" value="${sheet}" data-sheet-name="${sheet}" class="btn btn-secondary singleSheetBtn">${sheet}</v-btn>`;
                  }
                });
                sheetButtons += `</v-btn-toggle>`;
                this.btnHtml = sheetButtons;
                $('.sheetBtns').html(this.btnHtml);
                $('.sheetBtns').show();
                $('.clearSheet').show();
                if (confirm.value) {
                  showPreview = true;
                } else if (confirm.dismiss === 'cancel') {
                  showPreview = false;
                  self.sheet_name = sheets[0];
                }
                if (showPreview) {
                  self.sheet_name = sheets[0];
                  this2.getSheetDetails(workbook.SheetNames[0]);
                }
              });
            } else if (result.dismiss === 'cancel') {
              $('.dropify-clear').trigger('click');
              return false;
            }
          });
        } else {
          var workbook = XLSX.read(data, {
            type: 'array',
            bookSheets: true,
            sheetRows: 1,
          });
          var sheets = workbook.SheetNames;
          this2.fileInfosObject.sheets = sheets.length;
          var sheetButtons = ``;
          $.each(sheets, function (key, sheet) {
            if (key == 0) {
              sheetButtons += `<v-btn-toggle><v-btn value="${sheet}" data-sheet-name="${sheet}" class="btn btn-secondary singleSheetBtn active">${sheet}</v-btn>`;
            } else {
              sheetButtons += `<v-btn type="button" value="${sheet}" data-sheet-name="${sheet}" class="btn btn-secondary singleSheetBtn">${sheet}</v-btn>`;
            }
          });
          sheetButtons += `</v-btn-toggle>`;
          this.btnHtml = sheetButtons;
          $('.sheetBtns').html(this.btnHtml);
          $('.sheetBtns').show();
          $('.clearSheet').show();
          self.sheet_name = sheets[0];
          this2.getSheetDetails(workbook.SheetNames[0]);
        }
        this2.fileInfosObject.size = _size + ' MB';
      };
      reader.readAsArrayBuffer(f);
    },
    getSheetDetails(sheetName) {
      // Show processing bar
      this.isProcessing = true;
      this.startedAt = new Date();

      // Define "this3" to use "this" in another instance
      var this3 = this;
      setTimeout(function () {
        if (this3.processMainFile(this3.rawData, sheetName)) {
          // Hide processing bar
          this3.isProcessing = false;
        }
      }, 300);
    },
    processMainFile(rawData, sheetName) {
      var workbook = XLSX.read(rawData, {
        type: 'array',
        sheetRows: 10,
        cellDates: true,
        dateNF: 'YYYY/MM/DD',
        sheets: [sheetName],
      });
      this.selectedSheetData = workbook;
      var worksheet = workbook.Sheets[sheetName];
      var range = XLSX.utils.decode_range(worksheet['!ref']);
      var totalRow = range.e.r;
      this.displayPreview(sheetName);
      return 1;
    },
    displayPreview(sheetName) {
      var workbook = this.selectedSheetData;
      var worksheet = workbook.Sheets[sheetName];
      var exceljson = XLSX.utils.sheet_to_json(worksheet, {
        raw: false,
        defval: '　　　　',
        blankrows: true,
      }); // Use formatted strings (not raw values), Use 4 full-width spaces in place of null or undefined
      if (exceljson.length > 0) {
        $('#exceltable').empty();
        this.BindTable(exceljson, '#exceltable');
      }
      this.isProcessing = false;
    },
    BindTable(jsondata, tableid) {
      /*Function used to convert the JSON array to Html Table*/
      var columns = this.BindTableHeader(
        jsondata,
        tableid
      ); /*Gets all the column headings of Excel*/
      for (var i = 0; i < jsondata.length; i++) {
        var row$ = $('<tr/>');
        for (var colIndex = 0; colIndex < columns.length; colIndex++) {
          var cellValue = jsondata[i][columns[colIndex]];
          if (cellValue == null) cellValue = '';
          row$.append($('<td/>').html(cellValue));
        }
        $(tableid).append(row$);
      }
      $('#filepreview').hide();
      this.endedAt = new Date();
      var difference = this.endedAt.getTime() - this.startedAt.getTime();
      this.fileInfosObject.time = Math.abs(difference / 1000) + 's';
    },
    BindTableHeader(jsondata, tableid) {
      /*Function used to get all column names from JSON and bind the html table header*/
      var columnSet = [];
      var headerTr$ = $('<tr/>');
      for (var i = 0; i < jsondata.length; i++) {
        var rowHash = jsondata[i];
        for (var key in rowHash) {
          if (rowHash.hasOwnProperty(key)) {
            if ($.inArray(key, columnSet) == -1) {
              /*Adding each unique column names to a variable array*/
              columnSet.push(key);
              var html = key;
              if (html.substring(2, 7) == 'EMPTY') {
                html = '';
              }
              headerTr$.append($('<th/>').html(html));
            }
          }
        }
      }
      $(tableid).append(headerTr$);
      return columnSet;
    },
    clearSheet(e) {
      $('.dropify-clear').trigger('click');
      $('.sheetBtns').empty();
      $('.sheetBtns').hide();
      $('#filepreview').show();
      $('.clearSheet').hide();
      // Initialize the file info
      this.uploadFile = '';
      self.sheet_name = '';
    },
    sheetBtnClick(event) {
      var sheetClicked = $(event.target);
      var activeSheet = $('.sheetBtns').find('.active');
      var sheetName = sheetClicked.data('sheetName');
      if (activeSheet.data('sheetName') == sheetName) {
        return false;
      } else {
        activeSheet.toggleClass('active');
        sheetClicked.toggleClass('active');
        self.sheet_name = sheetName;
        this.previewConfirmation(sheetName);
      }
    },
    previewConfirmation(sheetName) {
      var _size = (this.rawDatafileSize / (1024 * 1000)).toFixed(2);
      if (_size >= 10 && _size <= 50) {
        swal({
          title: 'プレビューを表示しますか？',
          text: `表示まで時間がかかるかもしれません。プレビューを表示しますか？`,
          type: 'warning',
          confirmButtonText: 'Yes',
          showCancelButton: true,
        }).then((result) => {
          if (result.value) {
            $('.clearSheet').show();
            this.getSheetDetails(sheetName);
          }
          if (result.dismiss === 'cancel') {
            return false;
          }
        });
      } else {
        this.getSheetDetails(sheetName);
      }
    },
    submit() {
      if (this.uploadFile != '') {
        this.isProcessing = true;
        // 送信処理
        const formData = new FormData(); // multipart/form-data形式のため、new FormData()を使う
        formData.append('file', this.uploadFile);
        formData.append('sheet_name', self.sheet_name); // スコープの問題で、ここだけselfを使う

        this.postExcel(formData);
      }
    },
    reSubmit() {
      if (this.uploadFile != '') {
        this.isProcessing = true;
        // 送信処理
        const formData = new FormData(); // multipart/form-data形式のため、new FormData()を使う
        formData.append('file', this.uploadFile);
        formData.append('sheet_name', self.sheet_name); // スコープの問題で、ここだけselfを使う
        formData.append('add_only_datasource', true);

        this.confirmDialog.status = false;
        this.postExcel(formData);
      }
    },
    postExcel(formData) {
      axios
        .post('/api/v1/definition-bulk', formData)
        .then((response) => {
          if (response.data.code == 30) {
            this.isProcessing = false;
            this.confirmDialog.text = response.data.message.replace(
              /\n/g,
              '<br/>'
            );
            this.confirmDialog.status = true;
          } else {
            this.showResponseDialog(response);
            this.isProcessing = false;
          }
        })
        .catch((error) => {
          this.showResponseDialog(error.response);
          this.isProcessing = false;
        });
    },
    showResponseDialog(response) {
      // Success
      if (response.status == 200) {
        this.responseDialog.message = '定義情報の設定成功';
        this.responseDialog.tableName = response.data.table_name;
        this.responseDialog.datasourceName = response.data.datasource_name;
        this.responseDialog.visible200 = 'd-flex';
      }
      // Bad Request
      else if (response.status == 400) {
        this.responseDialog.message = response.data.error_message;
        this.responseDialog.count = response.data.error_details_count;

        var selfInEach = this;

        $.each(response.data.error_details, function (key, error_detail) {
          var detailStr = '';
          detailStr += error_detail + '\n';
          selfInEach.responseDialog.details.push(detailStr);
        });
        this.responseDialog.visible400 = 'd-flex';
      }
      // CSRF Token error (session is exipred)
      else if (response.status == 419) {
        this.responseDialog.message =
          'セッションが切れました。リロードしてください。';
      }
      // Other errors
      else {
        // システム管理者に連絡するようメッセージを出す
        this.showSystemErrorDialog(response);
      }
      this.dialog = true;
    },
    closeResponseDialog() {
      // Hide the dialog
      this.dialog = false;
      // Initialize this.responseDialog
      this.$set(this.responseDialog, 'visible200', 'd-none');
      this.$set(this.responseDialog, 'visible400', 'd-none');
      this.$set(this.responseDialog, 'visibleElse', 'd-none');
      this.$set(this.responseDialog, 'color', '');
      this.$set(this.responseDialog, 'count', '');
      this.$set(this.responseDialog, 'message', '');
      this.$set(this.responseDialog, 'fileName', '');
      this.$set(this.responseDialog, 'sheetName', '');
      this.$set(this.responseDialog, 'details', []);
    },
    showError(text) {
      Swal.fire('error!', text, 'error');
    },
    showSystemErrorDialog(response) {
      this.responseDialog.message = response.data.error_message;
      for (let detail of response.data.error_details) {
        this.responseDialog.details.push(detail);
      }
      // this.responseDialog.details.push(response.data.error_details);
      this.responseDialog.visibleElse = 'd-flex';
      this.dialog = true;
    },
  },
};
</script>