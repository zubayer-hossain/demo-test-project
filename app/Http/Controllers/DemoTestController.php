<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivateDemoTestRequest;
use App\Http\Requests\DeactivateDemoTestRequest;
use App\Http\Requests\DemoTestStoreRequest;
use App\Services\DemoTestService;
use Illuminate\Http\JsonResponse;

class DemoTestController extends Controller
{
    protected $demoTestService;

    public function __construct(DemoTestService $demoTestService)
    {
        $this->demoTestService = $demoTestService;
    }

    /**
     * Store demo test inquiry.
     * @param DemoTestStoreRequest $request
     * @return JsonResponse
     */
    public function store(DemoTestStoreRequest $request)
    {
        $serviceResponse = $this->demoTestService->handleInquiry($request->validated());
        return response()->json($serviceResponse['body'], $serviceResponse['status']);
    }

    /**
     * Activate a demo test.
     * @param ActivateDemoTestRequest $request
     * @return JsonResponse
     */
    public function activate(ActivateDemoTestRequest $request)
    {
        $serviceResponse = $this->demoTestService->activateTest($request->ref);
        return response()->json($serviceResponse['body'], $serviceResponse['status']);
    }

    /**
     * Deactivate a demo test.
     * @param DeactivateDemoTestRequest $request
     * @return JsonResponse
     */
    public function deactivate(DeactivateDemoTestRequest $request)
    {
        $serviceResponse = $this->demoTestService->deactivateTest($request->ref);
        return response()->json($serviceResponse['body'], $serviceResponse['status']);
    }
}
