<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Validator;
use App\Models\Address;

class AddressesController extends ApiController
{
    private $rules = array(
        'city' => 'required',
        'region' => 'required',
        'sub_region' => 'required',
        'street' => 'required',
        'building_number' => 'required',
        'floor_number' => 'required',
        'apartment_number' => 'required',
    );

    public function __construct() {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $addresses = array();
        try {
            $user = $this->auth_user();
            $addresses = Address::where('user_id',$user->id)
                                  ->paginate($this->limit);

            return _api_json(Address::transformCollection($addresses));
        } catch (\Exception $e) {
            $message = _lang('app.error_is_occured');
            return _api_json($addresses, ['message' => $message],422);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json('', ['errors' => $errors],422);
            }
            $user = $this->auth_user();
            $address = new Address;
            $address->city = $request->city;
            $address->region = $request->region;
            $address->sub_region = $request->sub_region;
            $address->street = $request->street;
            $address->building_number = $request->building_number;
            $address->floor_number = $request->floor_number;
            $address->apartment_number = $request->apartment_number;
            if ($request->special_sign) {
                $address->special_sign = $request->special_sign;
            }
            else{
                $address->special_sign = "";
            }
            if ($request->extra_info) {
                $address->extra_info = $request->extra_info;
            }
            else{
                $address->extra_info = "";
            }
            $address->user_id = $user->id;
            $address->save();
            return _api_json('',['message' => _lang('app.added_successfully')]);

        } catch (\Exception $e) {
            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message],422);
        }
    }
   
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $this->auth_user();
            $address = Address::where('id',$id)->where('user_id',$user->id)->first();
            if (!$address) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }
            $validator = Validator::make($request->all(), $this->rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json('', ['errors' => $errors],422);
            }
           
            $address->city = $request->city;
            $address->region = $request->region;
            $address->sub_region = $request->sub_region;
            $address->street = $request->street;
            $address->building_number = $request->building_number;
            $address->floor_number = $request->floor_number;
            $address->apartment_number = $request->apartment_number;
            if ($request->special_sign) {
                $address->special_sign = $request->special_sign;
            }
            if ($request->extra_info) {
                $address->extra_info = $request->extra_info;
            }
            $address->save();
            return _api_json('',['message' => _lang('app.updated_successfully')]);

        } catch (\Exception $e) {
            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message],422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = $this->auth_user();
            $address = Address::where('id',$id)->where('user_id',$user->id)->first();
            if (!$address) {
                $message = _lang('app.not_found');
                return _api_json('', ['message' => $message], 404);
            }
            $address->delete();
            return _api_json('',['message' => _lang('app.deleted_successfully')]);
            
        } catch (\Exception $e) {
            $message = _lang('app.error_is_occured');
            return _api_json('', ['message' => $message],422);
        }
    }
}
