<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Requests\DatabaseStoreRequest;
use App\Services\DatabaseService;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseService
     */
    private $databaseService;

    /**
     * Constructor
     *
     * @param DatabaseService $databaseService
     */
    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return JsonResponse::success(
            $this->databaseService->listDatabases()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DatabaseStoreRequest $request)
    {
        $input = $request->validated();

        try {
            $result = $this->databaseService->createDatabase($input['name']);
        } catch (\Throwable $th) {
            return JsonResponse::error([], $th->getMessage());
        }

        return JsonResponse::success($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($name)
    {
        if (!$this->databaseService->databaseExists($name)) {
            return JsonResponse::error([], 'Not found', 404);
        }

        return JsonResponse::success(
            $this->databaseService->getDatabaseInfo($name)->toArray()
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $name)
    {
        // TODO: Update some information (collation, engine?)

        return JsonResponse::success([
            'name' => $name,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($name)
    {
        if (!$this->databaseService->databaseExists($name)) {
            return JsonResponse::error([], 'Not found', 404);
        }

        try {
            $result = $this->databaseService->deleteDatabase($name);
        } catch (\Throwable $th) {
            return JsonResponse::error([], $th->getMessage());
        }

        return JsonResponse::success($result);
    }
}
