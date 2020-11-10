<?php

namespace App\Http\Requests;

use App\Rules\DatabaseNotExists;
use App\Services\DatabaseService;
use Illuminate\Foundation\Http\FormRequest;

class DatabaseStoreRequest extends FormRequest
{
    /** @var DatabaseService */
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
                new DatabaseNotExists($this->databaseService)
            ],
        ];
    }
}
