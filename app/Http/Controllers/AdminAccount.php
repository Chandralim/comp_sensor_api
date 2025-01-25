<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Str;
use Hash;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Model\Admin;
use App\Helpers\MyLib;

class AdminAccount extends Controller
{
  public function login(Request $request)
  {
    $request['username'] = strtolower($request->username);
    $rules = [
      'username' => 'required',
      'password' => "required|min:8",
    ];

    $messages = [
      'username.required' => 'Nama Pengguna tidak boleh kosong',

      'password.required' => 'Kata Sandi tidak boleh kosong',
      'password.min' => 'Kata Sandi minimal 8 Karakter',

    ];

    $validator = \Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    $username = $request->username;
    $password = $request->password;

    $admin = Admin::where("username", $username)->first();
    if (!$admin) {
      return response()->json([
        "message" => "Nama Pengguna dan Kata Sandi tidak cocok"
      ], 400);
    }

    if (Hash::check($password, $admin->password)) {
      $api_token = $admin->generateToken();

      return response()->json([
        "status" => 200,
        "message" => "Berhasil login",
        "token" => $api_token
      ], 200);
    } else {
      return response()->json([
        "message" => "Nama Pengguna dan Kata Sandi tidak cocok"
      ], 400);
    }
  }

  public function refresh(Request $request)
  {
    try {
      $token = json_decode(array_keys((array)$request->all())[0], true)["token"];
      if ($token == "") {
        return response()->json([
          "status" => 400,
          "message" => "Token Tidak Ditemukan",
        ], 400);
      }

      $admin = Admin::where("api_token", $token)->first();

      if (!$admin) {
        return response()->json([
          "message" => "Maaf token tidak valid"
        ], 400);
      }

      $api_token = $admin->generateToken();

      return response()->json([
        "status" => 200,
        "message" => "Berhasil refresh token",
        "token" => $api_token
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "message" => "Maaf Server Sedang Di tindak lanjuti harap kembali lagi nanti"
      ], 500);
    }
  }


  public function logout(Request $request)
  {
    $this->admin = MyLib::admin();

    try {
      $token = $request->bearerToken();
      if ($token == "") {
        return response()->json([
          "status" => 400,
          "message" => "Token Tidak Ditemukan",
        ], 400);
      }

      $admin = Admin::where("api_token", $token)->first();
      if (!$admin) {
        return response()->json([
          "message" => "Maaf token tidak valid"
        ], 400);
      }

      $admin->api_token = "";
      $admin->save();

      return response()->json([
        "status" => 200,
        "message" => "Logout Berhasil",
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "message" => "Maaf Server Sedang Di tindak lanjuti harap kembali lagi nanti"
      ], 500);
    }
  }

  public function getInfo(Request $request)
  {
    $this->admin = MyLib::admin();

    $token = $request->bearerToken();
    if ($token == "") {
      return response()->json([
        "status" => 400,
        "message" => "Token Tidak Ditemukan",
      ], 400);
    }

    $admin = Admin::where("api_token", $token)->first();
    if (!$admin) {
      return response()->json([
        "message" => "Maaf token tidak valid"
      ], 400);
    }

    return response()->json([
      "status" => 200,
      "message" => "Tampilkan data user",
      "user" => [
        "username" => $admin->username,
        "fullname" => $admin->fullname,
        "scope" => [$admin->role],
      ]
    ], 200);
  }


  public function change_password(Request $request)
  {
    $this->admin = MyLib::admin();

    $rules = [
      'old_password' => 'required|min:8|max:255',
      'password' => 'required|confirmed|min:8|max:255',
      'password_confirmation' => 'required|same:password|min:8|max:255',
    ];

    $rule = [
      "old_password.required" => "Kata Sandi lama harus diisi",
      "old_password.min" => "Kata Sandi lama minimal 8 karakter",
      "old_password.max" => "Kata Sandi lama maksimal 255 karakter",

      "password.required" => "Kata Sandi Baru harus diisi",
      "password.confirmed" => "Kata Sandi Baru tidak cocok",
      "password.min" => "Kata Sandi Baru minimal 8 karakter",
      "password.max" => "Kata Sandi Baru maksimal 255 karakter",

      "password_confirmation.required" => "Ulangi Kata Sandi Baru harus diisi",
      "password_confirmation.same" => "Ulangi Kata Sandi Baru tidak cocok",
      "password_confirmation.min" => "Ulangi Kata Sandi Baru minimal 8 karakter",
      "password_confirmation.max" => "Ulangi Kata Sandi Baru maksimal 255 karakter",
    ];

    $validator = \Validator::make($request->all(), $rules, $rule);
    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    $profile = \App\Model\Admin::where("id", $this->admin->id)->first();

    $old_password = $request->old_password;
    if (!Hash::check($old_password, $profile->password)) {
      return response()->json([
        "message" => "Kata sandi lama tidak sesuai"
      ], 400);
    }

    $profile->password = bcrypt($request->password);
    $profile->save();

    return response()->json([
      "message" => "Kata sandi berhasil diubah"
    ], 200);
  }

  public function change_fullname(Request $request)
  {
    $this->admin = MyLib::admin();

    $rules = [
      'fullname' => 'required|max:255',
    ];

    $rule = [
      'fullname.required' => 'Nama Identitas tidak boleh kosong',
      'fullname.max' => 'Nama Identitas tidak boleh lebih dari 255 karakter',
    ];

    $validator = \Validator::make($request->all(), $rules, $rule);
    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    $profile = \App\Model\Admin::where("id", $this->admin->id)->first();
    $profile->fullname = $request->fullname;
    $profile->save();

    return response()->json([
      "message" => "Nama Identitas berhasil diubah"
    ], 200);
  }
}
