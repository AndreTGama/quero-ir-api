<?php

namespace App\Http\Requests\TypeUser;

use App\Builder\ReturnMessage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTypeUserRequest extends FormRequest
{
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
            'name' => 'required|max:255',
            'description' => 'required',
        ];
    }
    /**
     *
     * @return array
     * You can use this function (message)
     * or the other one (failedValidation)
     * both are correct and working
     */
    // public function messages()
    // {
    //     return [
    //         'name.required' => 'Name field is required',
    //         'name.max' => 'You passed the maximum character value (value is 255)',
    //         'description.required' => 'Description field is required',
    //     ];
    // }
    /**
     *
     * @return array
     */
    public function failedValidation(Validator $validator)
    {
        $errors = [];
        $messages = $validator->errors()->messages();

        foreach ($messages as $m) {
            array_push($errors, $m[0]);
        }

        throw new HttpResponseException(
            ReturnMessage::message(
                true,
                'Something went wrong',
                'Erro in request',
                null,
                $errors,
                400
            )
        );
    }
}
