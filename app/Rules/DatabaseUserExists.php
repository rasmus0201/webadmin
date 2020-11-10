<?php

namespace App\Rules;

use App\Services\DatabaseService;
use Illuminate\Contracts\Validation\Rule;

class DatabaseUserExists implements Rule
{
    /** @var DatabaseService */
    private $databaseService;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->databaseService->userExists($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The database user does not exist.';
    }
}
