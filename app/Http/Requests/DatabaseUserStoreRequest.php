<?php

namespace App\Http\Requests;

use App\Rules\DatabaseUserNotExists;
use App\Services\DatabaseService;
use Illuminate\Foundation\Http\FormRequest;

class DatabaseUserStoreRequest extends FormRequest
{
    /**
     * @var DatabaseService
     */
    private $databaseService;

    /**
     * Create a new form request instance.
     *
     * @return void
     */
    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'min:4',
                'max:32',
                new DatabaseUserNotExists($this->databaseService)
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:32',
            ]
        ];
    }
}
