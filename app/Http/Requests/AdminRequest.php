<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
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
        $rules = [];
        if (request()->isMethod('post')) {
            $rules['username'] = 'required|regex:/^\S*$/|max:255|unique:App\Model\Admin,username';
            $rules['password'] = 'required|min:8|max:255';
        }
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:App\Model\Admin,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Model\Admin,id';
            $rules['username'] = 'required|regex:/^\S*$/|max:255|unique:App\Model\Admin,username,' . request()->id;
            $rules['password'] = 'nullable|min:8|max:255';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['role'] = 'required|max:50|in:Super_Admin,Admin,User';
            $rules['fullname'] = 'required|max:255';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            'username.regex' => 'Nama Pengguna tidak boleh ada spasi',
            'username.required' => 'Nama Pengguna tidak boleh kosong',
            'username.unique' => 'Nama Pengguna sudah digunakan',
            'username.max' => 'Nama Pengguna tidak boleh lebih dari 255 karakter',

            'password.required' => 'Kata Sandi tidak boleh kosong',
            'password.min' => 'Kata Sandi tidak boleh kurang dari 8 karakter',
            'password.max' => 'Kata Sandi tidak boleh lebih dari 255 karakter',

            'role.required' => 'Jabatan tidak boleh kosong',
            'role.in' => 'Jabatan harus dipilih',
            'role.max' => 'Jabatan tidak boleh lebih dari 50 karakter',

            'fullname.required' => 'Nama Identitas tidak boleh kosong',
            'fullname.max' => 'Nama Identitas tidak boleh lebih dari 255 karakter',

        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'username' => strtolower($this->username),
        ]);
    }
}
