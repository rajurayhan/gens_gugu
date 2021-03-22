<template>
  <v-app>
    <v-card class="mx-auto ma-5" width="75%">
      <v-col class="mt">
        <v-progress-linear
          id="progressBarTop"
          :active="isProcessing"
          :indeterminate="isProcessing"
          absolute
          top
          color="light-blue accent-4"
        ></v-progress-linear>
      </v-col>
      <v-card-title>
        <h1 class="display-1">ファイルアップロード</h1>
      </v-card-title>
      <v-form ref="fileUploadForm" @submit.prevent="submit">
        <v-container>
          <v-row class="mt-n5">
            <v-col cols="5">
              <v-select
                id="datasourceSelect"
                :items="itemsDataSource"
                v-model="datasource_id"
                v-on:change="setStartRowDefaultValue()"
                name="datasource_id"
                label="アップロードするファイル"
                :rules="validationRules.datasourceIdRules"
                menu-props="auto"
                solo
              ></v-select>
            </v-col>
            <v-col cols="2">
              <v-text-field
                id="startrow"
                name="start_row"
                v-model="start_row"
                label="データ開始行"
                :rules="validationRules.startRowRules"
                solo
              ></v-text-field>
            </v-col>
            <v-radio-group v-model="upload_mode" row>
              <span>アップロード形式:</span>&nbsp;
              <v-radio label="追加" value="append" id="modeAppend"></v-radio>
              <v-radio label="洗い替え" value="replace" id="modeReplace"></v-radio>
            </v-radio-group>
          </v-row>
          <!-- <v-col class="d-flex" cols="3">
                        <v-checkbox id="endrowControll" name="all_row" v-model="all_row" label="最終行まで"></v-checkbox>
                    </v-col>
                    <v-col class="d-flex" cols="3">
                        <v-text-field id="endrow" name="end_row" v-model="end_row" label="読込終了行" value="0" readonly></v-text-field>
          </v-col>-->
          <!-- <v-col class="d-flex" cols="3">
                        <v-text-field name="sheet_name" v-model="sheet_name" id="selectSheet" class="d-none"
                        value="" required></v-text-field>
          </v-col>-->
          <!-- D＆Dエリア開始 -->
          <div id="filepreview" class="mt-n5">
            <label for="file">ファイル</label>
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
          <!-- D＆Dエリア終了 -->
          <!-- シートボタン・データクリアボタン開始 -->
          <v-row class="m-auto">
            <div
              class="btn-group sheetBtns"
              @click="sheetBtnClick"
              role="group"
              aria-label="Sheet List"
            ></div>
            <!-- <div class="btn-group sheetBtns" role="group" aria-label="Sheet List">
                        	{{ btnHtml }}
            </div>-->
            <div style="margin-left: auto;">
              <v-btn id="clearSheetBtn" class="btn clearSheet" @click="clearSheet" color="error">
                <v-icon id="clearSheetIcon" class="clearSheet">ti-trash</v-icon>
                <!-- <v-icon>mdi-trash-can</v-icon> -->
              </v-btn>
            </div>
          </v-row>
          <!-- シートボタン・データクリアボタン終了 -->
          <!-- 送信ボタン -->
          <v-card-actions class="mt-3">
            <v-btn block id="submitBtn" @click="submit" :disabled="isProcessing" class="info">送信</v-btn>
          </v-card-actions>
        </v-container>
      </v-form>
      <!-- Dialog to show response -->
      <v-dialog v-model="dialog" width="500" persistent scrollable>
        <v-card style="max-height: 400px">
          <v-card-title
            id="vResCardTitle"
            class="headline grey lighten-2"
            primary-title
          >{{ responseDialog.message }}</v-card-title>
          <!-- <v-divider></v-divider> -->

          <v-card-text id="successDialogText" :class="responseDialog.visible200">
            ファイル名: {{ responseDialog.fileName }}
            <br />
            シート名: {{ responseDialog.sheetName }}
            <br />
            アップロード形式: {{ responseDialog.uploadMode }}
          </v-card-text>

          <v-card-text id="400ErrorDialogText" :class="responseDialog.visible400">
            <p>エラー件数: {{ responseDialog.count }}</p>
            <br />
            <v-list>
              <v-list-item v-for="detail in responseDialog.details" :key="detail.index">{{ detail }}</v-list-item>
            </v-list>
          </v-card-text>

          <v-card-text id="elseErrorDialogText" :class="responseDialog.visibleElse">
            詳細：
            <br />
            {{ responseDialog.details[0] }}
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn id="closeBtn" color="primary" @click="closeResponseDialog">Close</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-col class="mt">
        <v-progress-linear
          id="progressBarBottom"
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
    previewRowNumber: 10,
    itemsDataSource: [],
    apiResultDataSources: [],
    // validation
    valid: true,
    validationRules: {
      datasourceIdRules: [
        (v) => !!v || 'アップロードするファイルを指定してください',
      ],
      startRowRules: [
        (v) => !!v || 'データ開始行を指定してください',
        (v) => (v && v >= 1) || 'データ開始行は1以上を指定してください',
        (v) =>
          (v && v <= 1048576) || 'データ開始行は1048576以下を指定してください',
      ],
    },

    uploadFile: '',
    sheet_name: '',
    datasource_id: '',
    start_row: '',
    // all_row: true,
    end_row: 0,
    btnHtml: ``,
    isProcessing: false,
    dialog: false,
    responseDialog: {
      visible: false,
      visible200: 'd-none',
      visible400: 'd-none',
      visibleElse: 'd-none',
      color: '',
      count: '',
      message: '',
      fileName: '',
      sheetName: '',
      uploadMode: '',
      details: [],
    },
    fileInfosObject: {
      size: '',
      sheets: '',
      time: '',
      extension: '',
    },
    rawData: '',
    rawDatafileSize: '',
    selectedSheetData: '',
    startedAt: '',
    endedAt: '',
    upload_mode: 'append',
  }),
  icons: {
    iconfont: 'mdi', // default - only for display purposes
  },
  created() {
    this.getDatasourcesAndSetSelectBox();
  },
  mounted() {
    //エラーをリセット
    // this.$refs.form.resetValidation()

    // Dropifyの初期化
    $('#endrowControll').click(function () {
      if ($(this).is(':checked')) {
        $('#endrow').prop('readonly', true);
        $('#endrow').prop('required', false);
        $('#endrow').val(0);
      } else {
        $('#endrow').prop('readonly', false);
        $('#endrow').prop('required', true);
        $('#endrow').val('');
      }
    });
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
    getDatasourcesAndSetSelectBox() {
      // API結果を格納する配列、セレクトボックスのitemを初期化する
      this.apiResultDataSources = [];
      this.itemsDataSource = [];
      // データソース一覧を取得
      axios
        .get('/api/v1/datasources')
        .then((response) => {
          if (response.status == 200) {
            // APIから受け取ったデータソースの結果を保存する
            this.apiResultDataSources = response.data.datasources;
            // セレクトボックスのitemを更新する
            for (var datasource of response.data.datasources) {
              this.itemsDataSource.push({
                text: datasource.datasource_name,
                value: datasource.id,
              });
            }
          } else {
            // システム管理者に連絡するようメッセージを出す
            this.showSystemErrorDialog(
              'アップロード画面の準備に失敗しました。'
            );
          }
        })
        .catch((error) => {
          // システム管理者に連絡するようメッセージを出す
          this.showSystemErrorDialog('アップロード画面の準備に失敗しました。');
        });
    },
    showSystemErrorDialog(details) {
      this.responseDialog.message =
        'システムエラーが発生しました。IT部門に連絡してください';
      this.responseDialog.details.push(details);
      this.responseDialog.visibleElse = 'd-flex';
      this.dialog = true;
    },
    setStartRowDefaultValue() {
      // 選択されたデータソースのstarting_row_numberを取得する
      const selected_datasource = this.apiResultDataSources.find(
        (datasource) => {
          return datasource.id == this.datasource_id;
        }
      );
      if (selected_datasource != null) {
        if (selected_datasource.starting_row_number != null) {
          this.start_row = selected_datasource.starting_row_number;
        } else {
          if (this.start_row != '') {
            // DB上のデータ開始行にnullが入っていた場合は画面上のデータ開始行を空欄にする
            this.start_row = '';
          } else {
            // 最初の画面表示時にバリデーションエラーのメッセージが表示されないように、すでに空欄の場合は何もしない
            // Do nothing
          }
        }
      }
    },
    clearSheet(e) {
      $(e.target).hide();
      $('.dropify-clear').trigger('click');
      $('.sheetBtns').empty();
      $('.sheetBtns').hide();
      $('#filepreview').show();
      $('.clearSheet').hide();
      // Initialize the file info
      this.uploadFile = '';
      self.sheet_name = '';
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
      this.$set(this.responseDialog, 'uploadMode', '');
      this.$set(this.responseDialog, 'details', []);
    },
    showResponseDialog(response) {
      // Success
      if (response.status == 200) {
        if (response.data.meta.code == 10) {
          this.responseDialog.message = 'アップロード成功';
          this.responseDialog.fileName = response.data.meta.file_name;
          if (this.fileInfosObject.extension == 'csv') {
            this.responseDialog.sheetName = '-';
          } else {
            this.responseDialog.sheetName = response.data.meta.sheet_name;
          }
          this.responseDialog.uploadMode = response.data.meta.mode;
          this.responseDialog.visible200 = 'd-flex';
        } else if (response.data.meta.code == 20) {
          this.responseDialog.message = 'おっと！';
          this.responseDialog.details.push(response.data.meta.message);
          this.responseDialog.visibleElse = 'd-flex';
        }
      }
      // Bad Request
      else if (response.status == 400) {
        this.responseDialog.message = response.data.error_summary.error_message;
        this.responseDialog.count = response.data.error_summary.error_count;

        var selfInEach = this;

        $.each(response.data.error_details, function (key, error_detail) {
          var detailStr = '';
          // Parameter error
          if (response.data.meta.error_code == 10) {
            detailStr += error_detail;
          }
          // Validation error
          else {
            // 100th error (Show that it interrupted processing)
            if (key == 99) {
              detailStr += error_detail.message += '\n';
            }
            // 1st - 99th error
            else {
              detailStr += error_detail.row += '行目 ';
              detailStr += error_detail.column_name += ': ';
              $.each(error_detail.message, function (key2, errorDetailMessage) {
                detailStr += errorDetailMessage += '\n';
              });
            }
          }
          detailStr += '\n';
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
        this.showSystemErrorDialog(response.data.error_message);
      }
      this.dialog = true;
    },
    submit() {
      if (this.$refs.fileUploadForm.validate()) {
        this.isProcessing = true;
        //送信処理
        const formData = new FormData(); // multipart/form-data形式のため、new FormData()を使う
        formData.append('file', this.uploadFile);
        formData.append('sheet_name', self.sheet_name); // スコープの問題で、ここだけselfを使う
        formData.append('datasource_id', this.datasource_id);
        formData.append('start_row', this.start_row);
        // formData.append('all_row', this.all_row);
        formData.append('end_row', this.end_row);
        formData.append('mode', this.upload_mode);

        axios
          .post('/upload-excel', formData)
          .then((response) => {
            console.log(response);
            // this.showSnackBar('アップロードに成功しました！', 'success');
            this.showResponseDialog(response);
            this.isProcessing = false;
          })
          .catch((error) => {
            console.log(error.response);
            // this.showSnackBar('エラーが発生しました', 'error');
            this.showResponseDialog(error.response);
            this.isProcessing = false;
          });
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
    processMainFile(rawData, sheetName) {
      var xlsxReadOption = {};
      if (this.fileInfosObject.extension == 'csv') {
        // No need to convert the date format for csv files.
        xlsxReadOption = {
          type: 'array',
          raw: true,
          sheetRows: this.previewRowNumber,
          cellDates: false,
          sheets: [sheetName],
        };
      } else {
        // In the case of 'xlsx', 'xls' or 'xlsm', specifying the date display format
        xlsxReadOption = {
          type: 'array',
          sheetRows: this.previewRowNumber,
          cellDates: true,
          dateNF: 'YYYY/MM/DD',
          sheets: [sheetName],
        };
      }
      var workbook = XLSX.read(rawData, xlsxReadOption);
      this.selectedSheetData = workbook;
      var worksheet = workbook.Sheets[sheetName];
      var range = XLSX.utils.decode_range(worksheet['!ref']);
      var totalRow = range.e.r;
      this.displayPreview(sheetName);
      return 1;
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
    displayPreview(sheetName) {
      var workbook = this.selectedSheetData;
      var worksheet = workbook.Sheets[sheetName];
      var exceljson = XLSX.utils.sheet_to_json(worksheet, {
        raw: false,
        defval: '　　　　',
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
    currentTime() {
      var d = new Date(),
        dformat =
          [d.getMonth() + 1, d.getDate(), d.getFullYear()].join('/') +
          ' ' +
          [d.getHours(), d.getMinutes(), d.getSeconds()].join(':');
      return dformat;
    },
    showError(text) {
      Swal.fire('error!', text, 'error');
    },
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
      this.fileInfosObject.extension = ext;
      var supportedFileTypes = ['xlsx', 'xls', 'xlsm', 'csv'];
      if ($.inArray(ext, supportedFileTypes) < 0) {
        // toastr.error('Only .xlsx and .xls files are supported', 'Error!');
        // showError('拡張子が .xlsx、.xlsm または .xls のファイルのみ許可されています。');
        this.showError(
          'おっと、そのファイルはアップロードできません。Excelファイルかcsvファイルを選択してください。'
        );
        $('.dropify-clear').trigger('click');
        return false;
      }
      var reader = new FileReader();
      // Define "this2" to use "this" in another instance
      var this2 = this;
      reader.onload = function (e) {
        var data = new Uint8Array(e.target.result);
        if (ext == 'csv') {
          // Convert character codes to prevent garbled previews
          var detectedEncoding = Encoding.detect(data);
          console.log('encode: ' + detectedEncoding);
          var unicodeArrayBuffer = Encoding.convert(data, {
            to: 'unicode',
            from: detectedEncoding,
            type: 'arraybuffer',
          });

          this2.rawData = unicodeArrayBuffer;
        } else {
          // In the case of 'xlsx', 'xls' or 'xlsm', no conversion of character codes is required.
          this2.rawData = data;
        }
        var showPreview;
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
                var workbook = XLSX.read(this2.rawData, {
                  type: 'array',
                  bookSheets: true,
                  sheetRows: 1,
                });
                var sheets = workbook.SheetNames;
                this2.fileInfosObject.sheets = sheets.length;

                if (this2.fileInfosObject.extension != 'csv') {
                  // The csv file doesn't have a sheet, so it doesn't show the sheet button.
                  this2.makeSheetsBtn(sheets);
                }
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
          var workbook = XLSX.read(this2.rawData, {
            type: 'array',
            bookSheets: true,
            sheetRows: 1,
          });
          var sheets = workbook.SheetNames;
          this2.fileInfosObject.sheets = sheets.length;
          if (this2.fileInfosObject.extension != 'csv') {
            // The csv file doesn't have a sheet, so it doesn't show the sheet button.
            this2.makeSheetsBtn(sheets);
          }
          $('.clearSheet').show();
          self.sheet_name = sheets[0];
          this2.getSheetDetails(workbook.SheetNames[0]);
        }
        this2.fileInfosObject.size = _size + ' MB';
      };
      reader.readAsArrayBuffer(f);
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
    makeSheetsBtn(sheets) {
      var sheetButtons = ``;
      $.each(sheets, function (key, sheet) {
        console.log(sheet);

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
    },
  },
};
</script>
