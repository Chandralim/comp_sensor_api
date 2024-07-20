<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Exceptions\MyException;

use App\Model\Admin;
use App\Http\Resources\AdminResource;
use App\Http\Requests\AdminRequest;

class AdminController extends Controller
{
  public function __construct(Request $request)
  {
    $this->admin = MyLib::admin();
    if ($this->admin->role=="User" ) {
      throw new MyException(["message"=>"Maaf Anda Tidak Punya Otoritas"],400);
    }
  }

  public function index(Request $request)
  {
    $user_login_role=$this->admin->role;

    // if($this->admin->role=="User") throw new MyException(["message"=>"Data Tidak Diizinkan"],400);

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 250; // Limit +> Much Data
    if (isset($request->limit)) {
      if ($request->limit <= 250) {
        $limit = $request->limit;
      }else {
        throw new MyException("Max Limit 250");
      }
    }

    $offset = isset($request->offset) ? (int) $request->offset : 0; // example offset 400 start from 401

    //======================================================================================================
    // Jika Halaman Ditentutkan maka $offset akan disesuaikan
    //======================================================================================================
    if (isset($request->page)) {
      $page =  (int) $request->page;
      $offset = ($page*$limit)-$limit;
    }


    //======================================================================================================
    // Init Model
    //======================================================================================================
    $model_query = Admin::offset($offset)->limit($limit);
    
    if($user_login_role=="Admin"){
      $model_query=$model_query->where("role","!=","Super_Admin");
    }

    //======================================================================================================
    // Model Sorting | Example $request->sort = "username:desc,role:desc";
    //======================================================================================================

    if ($request->sort) {
      $sort_lists=[];

      $sorts=explode(",",$request->sort);
      foreach ($sorts as $key => $sort) {
        $side = explode(":",$sort);
        $side[1]=isset($side[1])?$side[1]:'ASC';
        $sort_lists[$side[0]]=$side[1];
      }

      if (isset($sort_lists["username"])) {
        $model_query = $model_query->orderBy("username",$sort_lists["username"]);
      }

      if (isset($sort_lists["fullname"])) {
        $model_query = $model_query->orderBy("fullname",$sort_lists["fullname"]);
      }

      if (isset($sort_lists["role"])) {
        $model_query = $model_query->orderBy("role",$sort_lists["role"]);
      }

      //
      // if (isset($sort_lists["role"])) {
      //   $model_query = $model_query->orderBy(function($q){
      //     $q->from("roles")
      //     ->select("title")
      //     ->whereColumn("id","users.role_id");
      //   },$sort_lists["role"]);
      // }

      // if (isset($sort_lists["admin"])) {
      //   $model_query = $model_query->orderBy(function($q){
      //     $q->from("users as u")
      //     ->select("u.username")
      //     ->whereColumn("u.id","users.id");
      //   },$sort_lists["admin"]);
      // }
    }else {
      $model_query = $model_query->orderBy('id','ASC');
    }
    //======================================================================================================
    // Model Filter | Example $request->like = "username:%username,role:%role%,name:role%,";
    //======================================================================================================

    if ($request->like) {
      $like_lists=[];

      $likes=explode(",",$request->like);
      foreach ($likes as $key => $like) {
        $side = explode(":",$like);
        $side[1]=isset($side[1])?$side[1]:'';
        $like_lists[$side[0]]=$side[1];
      }

      $model_query = $model_query->where(function ($q)use($like_lists) {
        if (isset($like_lists["username"])) {
          $q->orWhere("username","ilike",$like_lists["username"]);
        }
        if (isset($like_lists["fullname"])) {
          $q->orWhere("fullname","ilike",$like_lists["fullname"]);
        }    
        // if (isset($like_lists["role"])) {
        //   $q->orWhere("role","ilike",$like_lists["role"]);
        // }
        // $q->Where("username","ilike",$like_lists["username"]);
        // if($user_login_role=="Admin"){
        //   $q->whereIn("role",["Admin","User"]);
        // }
      });
    }

    // ==============
    // Model Filter
    // ==============
    
  //   if (isset($request->no_acc)) {
  //     $model_query = $model_query->where("no_acc",'like','%'.$request->no_acc.'%');
  //   }

    $model_query=$model_query->get();

    return response()->json([
      "data"=>AdminResource::collection($model_query),
    ],200);
  }

  public function show(AdminRequest $request)
  {
    $model_query = Admin::where("id",$request->id);
    if($this->admin->role=="Admin"){
      $model_query=$model_query->where("role","!=","Super_Admin");
    }
    $model_query = $model_query->first();
    if($model_query)
      return response()->json([
        "data"=>new AdminResource($model_query),
      ],200);    
    else
      return response()->json([
        "data"=>[],
      ],200);
  }

  public function checkRoleStoreAndUpdate($request){
    if ($this->admin->role=="User" ) {
      throw new MyException(["message"=>"Maaf Anda Tidak Punya Otoritas"],400);
    }

    if ($this->admin->role=="Admin" ) {
      if ($request->role!="User") {
        throw new MyException(["message"=>"Maaf Anda Tidak Punya Otoritas Lebih"],400);
      }
    }
  }

  public function store(AdminRequest $request)
  {
    $this->checkRoleStoreAndUpdate($request);
    $model_query=new Admin();
    $model_query->username=$request->username;
    $model_query->fullname=$request->fullname;
    $model_query->role=$request->role;
    $model_query->password=bcrypt($request->password);
    $model_query->created_at=MyLib::getMillis();
    $model_query->updated_at=MyLib::getMillis();
    if ($model_query->save()) {
      return response()->json([
          "message"=>"Proses tambah data berhasil",
      ],200);
    }
    return response()->json([
        "message"=>"Proses tambah data gagal"
    ],400);
  }

  public function update(AdminRequest $request)
  {

    $this->checkRoleStoreAndUpdate($request);
    $model_query = Admin::find($request->id);
    $model_query->username=$request->username;
    $model_query->fullname=$request->fullname;

    if ($this->admin->role=="Admin" ) {
      if ($model_query->role!="User") {
        throw new MyException(["message"=>"Maaf Anda Tidak Punya Otoritas Mengganti Data ini"],400);
      }
    }

    $model_query->role=$request->role;
    if($request->password){
      $model_query->password=bcrypt($request->password);
    }
    $model_query->updated_at=MyLib::getMillis();
    if ($model_query->save()) {
        return response()->json([
            "message"=>"Proses ubah data berhasil",
        ],200);
    }
    return response()->json([
        "message"=>"Proses ubah data gagal"
    ],400);
  }
}
