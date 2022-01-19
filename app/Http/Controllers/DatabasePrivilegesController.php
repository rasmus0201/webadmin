<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Services\DatabaseService;
use Illuminate\Http\Request;

class DatabasePrivilegesController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $databaseUser)
    {
        if (!$this->databaseService->userExists($databaseUser)) {
            return JsonResponse::error([], 'Not found', 404);
        }

        $userParts = $this->databaseService->getUserParts($databaseUser);
        $databaseNames = $this->databaseService->listDatabasesByUser($userParts['username'], $userParts['host']);

        $databases = [];
        foreach ($databaseNames as $databaseName) {
            $databases[$databaseName] = $this->databaseService->getPrivilegesOnDatabase(
                $userParts['username'],
                $userParts['host'],
                $databaseName
            );
        }

        return JsonResponse::success($databases);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $databaseUser)
    {
        if (!$this->databaseService->userExists($databaseUser)) {
            return JsonResponse::error([], 'Not found', 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $databaseUser, $name)
    {
        if (!$this->databaseService->userExists($databaseUser)) {
            return JsonResponse::error([], 'Not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $databaseUser, $name)
    {
        if (!$this->databaseService->userExists($databaseUser)) {
            return JsonResponse::error([], 'Not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $databaseUser, $name)
    {
        if (!$this->databaseService->userExists($databaseUser)) {
            return JsonResponse::error([], 'Not found', 404);
        }
    }
}
