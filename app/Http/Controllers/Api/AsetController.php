<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Aset;
use DB;

class AsetController extends Controller
{
/**
 * @OA\Get(
 * path="/api/aset/getdata",
 * description="Show All Data Aset",
 * operationId="aset_getdata",
 * security={{"bearerAuth":{}}},
 * tags={"Aset"},
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function getData() 
    {
        try 
        {
            $user = auth()->user();

            if($user->role == 1) {
                $aset = Aset::all();
            }
            else {
                $aset = Aset::where('user_id', '=', $user->id)->get();
            }

            return response()->json(['status' => 'S', 'message' => 'successfully_show_all_asset', 'aset' => $aset]);
        }
        catch (\Exception $ex) 
        {
            return response()->json([
                'status' => 'E', 
                'message' => $ex->getMessage()], 
                500
            );
        }
    }

/**
 * @OA\Get(
 * path="/api/aset/show/{id}",
 * description="Show Data Aset",
 * operationId="aset_show",
 * security={{"bearerAuth":{}}},
 * tags={"Aset"},
 *      @OA\Parameter(
 *          name="id",
 *          description="ID Aset",
 *          required=true,
 *          in="path",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function show($id) 
    {
        try
        {
            $user = auth()->user();

            if($user->role == 1) {
                $aset = Aset::find($id);
            }
            else {
                $aset = Aset::where([['id', '=', $id], ['user_id', '=', $user->id]])->first();
            }
            
            if($aset != null) {
                return response()->json([
                    'status' => 'S', 
                    'message' => 'successfully_show_asset',
                    'aset' => $aset]
                );
            }
            else {
                throw new \Exception('no_data_found');
            }
        }
        catch (\Exception $ex) 
        {
            return response()->json([
                'status' => 'E', 
                'message' => $ex->getMessage()], 
                500
            );
        }
    }

/**
 * @OA\Post(
 * path="/api/aset/insert",
 * description="Insert Data Aset",
 * operationId="aset_insert",
 * tags={"Aset"},
 * security={{"bearerAuth":{}}},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"name","quantity","brand","desc"},
 *              @OA\Property(property="name", type="string"),
 *              @OA\Property(property="quantity", type="integer"),
 *              @OA\Property(property="brand", type="string"),
 *              @OA\Property(property="desc", type="string")
 *          ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function insert(Request $request) 
    {
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'quantity' => 'required|integer',
                'brand' => 'required'
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'status' => 'E', 
                    'message' => 'error_validation', 
                    'errors' => $validator->errors()],
                     400
                    );
            }

            $user = auth()->user();

            $aset = new Aset();
            $aset->name = $request->name;
            $aset->quantity = $request->quantity;
            $aset->brand = $request->brand;
            $aset->desc = $request->desc;
            $aset->user_id = $user->id;
            $aset->save();
    
             DB::commit();

            return response()->json([
                'status' => 'S', 
                'message' => 'successfully_insert_asset']
            );
        } 
        catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 'E', 
                'message' => $ex->getMessage()], 
                500
            );
        }
    }

/**
 * @OA\Put(
 * path="/api/aset/update/{id}",
 * description="Update Data Aset",
 * operationId="aset_update",
 * tags={"Aset"},
 * security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          description="ID Aset",
 *          required=true,
 *          in="path",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"name","quantity","brand","desc"},
 *              @OA\Property(property="name", type="string"),
 *              @OA\Property(property="quantity", type="integer"),
 *              @OA\Property(property="brand", type="string"),
 *              @OA\Property(property="desc", type="string")
 *          ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function update(Request $request, $id) 
    {
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'quantity' => 'required|integer',
                'brand' => 'required'
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'status' => 'E', 
                    'message' => 'error_validation', 
                    'errors' => $validator->errors()],
                     400
                    );
            }

            $user = auth()->user();

            if($user->role == 1) {
                $aset = Aset::find($id);
            }
            else {
                $aset = Aset::where([['id', '=', $id], ['user_id', '=', $user->id]])->first();
            }

            if($aset != null) {
                $aset->name = $request->name;
                $aset->quantity = $request->quantity;
                $aset->brand = $request->brand;
                $aset->desc = $request->desc;
                $aset->save();

                DB::commit();

                return response()->json([
                    'status' => 'S', 
                    'message' => 'successfully_update_asset']
                );
            }
            else {
                throw new \Exception('no_data_found');
            }
        } 
        catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 'E', 
                'message' => $ex->getMessage()], 
                500
            );
        }
    }

/**
 * @OA\Delete(
 * path="/api/aset/delete/{id}",
 * description="Delete Data Aset",
 * operationId="aset_delete",
 * tags={"Aset"},
 * security={{"bearerAuth":{}}},
 *      @OA\Parameter(
 *          name="id",
 *          description="ID Aset",
 *          required=true,
 *          in="path",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *          )
 *      )
 * )
 */
    public function delete($id) 
    {
        try
        {
            DB::beginTransaction();
            
            $user = auth()->user();

            if($user->role == 1) {
                $aset = Aset::find($id);
            }
            else {
                $aset = Aset::where([['id', '=', $id], ['user_id', '=', $user->id]])->first();
            }

            if($aset != null) {
                $aset->delete();

                DB::commit();

                return response()->json([
                    'status' => 'S', 
                    'message' => 'successfully_delete_asset']
                );
            }
            else {
                throw new \Exception('no_data_found');
            }
        }
        catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                'status' => 'E', 
                'message' => $ex->getMessage()], 
                500
            );
        }
    }
}
