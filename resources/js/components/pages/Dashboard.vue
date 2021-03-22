<template>
  <v-container>
    <v-card width="250" class="mx-auto text-left">
      <v-card-subtitle class="mb-n5">ファイルアップロード</v-card-subtitle>
      <v-card-text>
        <v-container>
        <!-- TODO リンク先を:to="{name: 'fileupload'}"のようにrouter.jsで定義したnameで指定したいが、
        リンク先に移動したあとの画面で左ペインが表示されない課題があるのでhrefのままとしている -->
          <v-row><v-chip class="ma-1" href="/fileupload">ファイルアップロード画面</v-chip></v-row>
          <v-row><v-chip class="ma-1" href="/filelist">ファイル一覧画面</v-chip></v-row>
          <v-row><v-chip class="ma-1" href="/tabledata">アップロードデータ表示画面</v-chip></v-row>
        </v-container>
      </v-card-text>
      <v-card-subtitle class="my-n5">マスタメンテ</v-card-subtitle>
      <v-card-text>
        <v-container>
          <v-row><v-chip class="ma-1" href="/admin/tables">Table Lists画面</v-chip></v-row>
          <v-row><v-chip class="ma-1" href="/admin/list">Table Columns画面</v-chip></v-row>
          <v-row><v-chip class="ma-1" href="/admin/datasource">Datasource画面</v-chip></v-row>
          <v-row><v-chip class="ma-1" href="/admin/datasource-columns">Datasource Columns画面</v-chip></v-row>
          <v-row><v-chip class="ma-1" href="/admin/definition-upload">Definition Upload画面</v-chip></v-row>
        </v-container>
      </v-card-text>
    </v-card>
  </v-container>
</template>
<script>
export default {
  mounted() {
    console.log('Dashboard Component mounted.');
  },
};
</script>
