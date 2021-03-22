<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\DB;

/**
 * FileService
 */
class FileService
{
    /**
     * delete records from raw table and files table
     *
     * @param  int $fileId
     * @return bool
     */
    public function deleteRecordsFromRawTableAndFilesTable($fileId): bool
    {
        // Return response
        return DB::transaction(
            function () use ($fileId) {
                $file = File::find($fileId);
                if (is_null($file)) {
                    return false;
                }
                // Execute the query(delete excel data)
                DB::table($file->table_name)->where('file_id', $fileId)->delete();
                // Execute the query(delete file data)
                $file->delete();

                // Success response
                return true;
            }
        );
    }
}
