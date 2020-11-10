<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Requests\DatabaseUserStoreRequest;
use App\Services\DatabaseService;
use Illuminate\Http\Request;

class DatabaseUserController extends Controller
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
    public function index()
    {
        return JsonResponse::success(
            $this->databaseService->listUsers()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DatabaseUserStoreRequest $request)
    {
        $input = $request->validated();

        try {
            $result = $this->databaseService->createUser(
                $input['name'],
                'localhost',
                'password'
            );
        } catch (\Throwable $th) {
            return JsonResponse::error([], $th->getMessage());
        }

        return JsonResponse::success($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  string $name
     * @return \Illuminate\Http\Response
     */
    public function show($name)
    {
        if (!$this->databaseService->userExists($name)) {
            return JsonResponse::error([], 'Not found', 404);
        }

        return JsonResponse::success(
            $this->databaseService->getUserInfo($name)->toArray()
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $name
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $name)
    {
        // TODO: Update some information?

        return JsonResponse::success([
            'name' => $name,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $name
     * @return \Illuminate\Http\Response
     */
    public function destroy($name)
    {
        if (!$this->databaseService->userExists($name)) {
            return JsonResponse::error([], 'Not found', 404);
        }

        $result = $this->databaseService->deleteUser($name);

        return JsonResponse::success($result);
    }
}
