<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Export\DataExportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DataExportController extends Controller
{
    private $exportService;

    public function __construct(DataExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function exportUserData($format = 'csv')
    {
        $user = Auth::user();
        
        $exportPath = $this->exportService->exportUserData($user, $format);

        return response()->download(
            storage_path("app/{$exportPath}"), 
            "user_data_export.{$format}"
        );
    }
}