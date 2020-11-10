<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Services\DatabaseService;
use Illuminate\Http\Request;

class DatabasePrivilegesController extends Controller
{
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
        $userParts = $this->databaseService->getUserParts($databaseUser);

        $privileges = $this->databaseService->getPrivilegesOnDatabase(
            $userParts['username'],
            $userParts['host'],
            'webadmin'
        );

        return JsonResponse::success($privileges);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
