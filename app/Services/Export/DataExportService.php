<?php

namespace App\Services\Export;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

class DataExportService
{
    public function exportUserData(User $user, $format = 'csv')
    {
        $filename = "user_data_export_{$user->id}_" . now()->format('Y-m-d_H-i-s');

        $data = [
            'user_profile' => $this->getUserProfile($user),
            'ai_requests' => $this->getAIRequests($user),
            'assistants' => $this->getAssistants($user),
            'projects' => $this->getProjects($user)
        ];

        if ($format === 'csv') {
            return $this->exportToCsv($filename, $data);
        }

        return $this->exportToJson($filename, $data);
    }

    private function getUserProfile(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'registered_at' => $user->created_at,
            'current_plan' => $user->currentPlan()->name ?? 'No Plan'
        ];
    }

    private function getAIRequests(User $user)
    {
        return $user->aiRequests()->with('provider')->get()->map(function ($request) {
            return [
                'id' => $request->id,
                'provider' => $request->provider->name,
                'model' => $request->model_name,
                'tokens_used' => $request->tokens_used,
                'created_at' => $request->created_at
            ];
        });
    }

    private function getAssistants(User $user)
    {
        return $user->assistants()->get()->map(function ($assistant) {
            return [
                'id' => $assistant->id,
                'name' => $assistant->name,
                'model' => $assistant->model,
                'created_at' => $assistant->created_at
            ];
        });
    }

    private function getProjects(User $user)
    {
        return $user->projects()->get()->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'visibility' => $project->visibility,
                'created_at' => $project->created_at
            ];
        });
    }

    private function exportToCsv($filename, $data)
    {
        $path = "exports/{$filename}.csv";
        $writer = SimpleExcelWriter::create(storage_path("app/{$path}"));

        foreach ($data as $key => $items) {
            $writer->addSheet($key, $items->toArray());
        }

        return $path;
    }

    private function exportToJson($filename, $data)
    {
        $path = "exports/{$filename}.json";
        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));

        return $path;
    }
}