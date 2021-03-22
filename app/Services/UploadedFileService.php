<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * UploadedFileService
 */
class UploadedFileService
{
    /**
     * find files by datasource id and original name
     *
     * @param  int    $datasourceId
     * @param  string $originalName
     * @param  string $sheetName
     * @return Collection
     */
    public function findFilesByDatasourceIdAndOriginalName($datasourceId, $originalName, $sheetName): Collection
    {
        $files = File::where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->where('sheet_name', $sheetName)
            ->get();
        return $files->pluck('id');
    }
}
