<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technical\AnalyzeDamagePhotoRequest;
use App\Http\Requests\Technical\EstimateRepairCostRequest;
use App\Http\Requests\Technical\TranscribeInspectionAudioRequest;
use App\Services\Maintenance\AudioTranscriptionService;
use App\Services\Maintenance\DamagePhotoAnalysisService;
use App\Services\Maintenance\RepairCostEstimatorService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Throwable;

class MaintenanceAiController extends Controller
{
    public function analyzeDamage(AnalyzeDamagePhotoRequest $request, DamagePhotoAnalysisService $service): JsonResponse
    {
        try {
            return response()->json($service->analyze($request->file('photo')));
        } catch (RequestException $exception) {
            report($exception);

            $message = match ($exception->response?->status()) {
                401, 403 => __('The configured AI key was rejected by the provider.'),
                429 => __('The AI provider quota has been reached. Please try again later.'),
                503 => __('The AI provider is temporarily unavailable. Please try again shortly.'),
                default => __('The image analysis service is temporarily unavailable.'),
            };

            return response()->json(['message' => $message], 503);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['message' => __('The image analysis service is temporarily unavailable.')], 503);
        }
    }

    public function transcribe(TranscribeInspectionAudioRequest $request, AudioTranscriptionService $service): JsonResponse
    {
        try {
            return response()->json(['text' => $service->transcribe($request->file('audio'))]);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['message' => __('The transcription service is temporarily unavailable.')], 503);
        }
    }

    public function estimate(EstimateRepairCostRequest $request, RepairCostEstimatorService $service): JsonResponse
    {
        $data = $request->validated();
        return response()->json($service->estimate($data['category_id'] ?? null, $data['severity']));
    }
}
