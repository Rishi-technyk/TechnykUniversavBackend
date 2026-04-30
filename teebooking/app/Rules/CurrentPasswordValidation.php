<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;

class CurrentPasswordValidation implements Rule
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
        $member = Member::where(['id' => auth()->id()])->first();
        if ($member && Hash::check($value, $member->Password)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Current password is not matched';
    }
}
