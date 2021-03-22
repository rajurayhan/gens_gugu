<?php

namespace App\Services;

use App\Models\Table;
use App\Models\TableColumns;
use Illuminate\Support\Facades\Log;
use DB;

/**
 * TableDataSearchService
 */
class TableDataSearchService
{
    /**
     * Get Table Header from m_table_columns by tableId
     *
     * @param  int $tableId table_id
     * @return array table header list
     */
    public function getTableHeader($tableId): array
    {
        $tableHeader = TableColumns::where('table_id', $tableId)->select('column_name_alias as text', 'column_name as value')->orderBy('id', 'asc')->get();
        return array_merge([['text' => 'アップロード日時', 'value' => 'created_at']], $tableHeader->toArray());
    }

    /**
     * Get table data from the table identified by tableId
     *
     * @param  int   $tableId table_id
     * @param  array $options options for sorting and pagination
     * @return array table data list
     */
    public function getTableData($tableId, $options = []): array
    {
        // returned data
        $data = [
            'records' => [],
            'total_count' => 0,
        ];

        // Initialize option values
        $itemsPerPage = $options['itemsPerPage'] ?? null;
        $page = $options['page'] ?? null;
        $sortBy = $options['sortBy'] ?? null;
        $sortDesc = !empty($options['sortDesc']) && $options['sortDesc'] == 'true' ? true : false;
        $searchWords = $options['searchWords'] ?? null;
        // Get data from m_tables by tableId
        $table = Table::findOrFail($tableId);
        // Build SQL query
        $targetTable = DB::table($table->table_name);

        // Setting search word before total count
        $targetTable = $targetTable->when(
            $searchWords,
            function ($query, $searchWords) use ($tableId) {
                // The columns displayed on the uploaded data view screen are the search targets
                // get columns name(Including uploaded_at)
                $columns = collect($this->getTableHeader($tableId))->pluck('value');
                // set search word
                $query->where(
                    function ($query) use ($columns, $searchWords) {
                        foreach ($searchWords as $searchWord) {
                            foreach ($columns as $column_name) {
                                /**
                                 * Note
                                 * This process also do "like search" for timestamp and date.
                                 * mysql can perform "like search" for timestamp and date, but mysql8.0.16 can't.
                                 * Therefore, in mysql8.0.16, an SQL error occurs here.
                                 */
                                $query->orWhere($column_name, 'like', '%' . $searchWord . '%');
                            }
                        }
                    }
                );
                return $query;
            }
        );

        // set total count before setting limit
        $data['total_count'] = $targetTable->count();

        $targetTable = $targetTable->when(
            $itemsPerPage,
            function ($query, $itemsPerPage) use ($page) {
                // itemsPerPage
                $query->when(
                    $page,
                    function ($query, $page) use ($itemsPerPage) {
                        // page
                        $query->offset($itemsPerPage * ($page - 1));
                    }
                );
                return $query->limit($itemsPerPage);
            }
        )->when(
            $sortBy,
            function ($query, $sortBy) use ($sortDesc) {
                // sortBy and sortDesc
                $sortDir = !empty($sortDesc) ? 'desc' : 'asc';
                return $query->orderBy($sortBy, $sortDir);
            }
        );

        // get data
        $data['records'] = $targetTable->get()->all();

        return $data;
    }
}
