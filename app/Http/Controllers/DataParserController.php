<?php

namespace App\Http\Controllers;

use App\Services\DataProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataParserController extends Controller
{
    protected DataProcessor $dataProcessor;

    public function __construct(DataProcessor $dataProcessor)
    {
        $this->dataProcessor = $dataProcessor;
    }

    public function fetchData(Request $request): JsonResponse
    {
        try {
            $data = $this->dataProcessor->process($request);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
