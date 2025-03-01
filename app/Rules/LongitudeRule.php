<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class LongitudeRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        return preg_match( "/^\s?[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d{1,8}))?)|180(\.0+)?)$/", $value );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid longitude coordinate, with a limit of 8 digits after a decimal point';
    }
}
