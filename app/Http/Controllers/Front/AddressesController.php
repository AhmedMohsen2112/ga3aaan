<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use Validator;
use App\Models\Address;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class AddressesController extends FrontController
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
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['page_title'] = _lang('app.addresses');
        try {
            $addresses = Address::where('user_id',$this->User->id)
                                  ->paginate($this->limit);
            $this->data['addresses'] = $addresses;
            return $this->_view('addresses.index');
        } catch (\Exception $e) {
            session()->flash('msg',_lang('app.error_is_occured_try_again_later'));
            return redirect()->back();
        }
    }

    public function create()
    {
        $this->data['page_title'] = _lang('app.add_address');
        return $this->_view('addresses.create');
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

                if ($request->ajax()) {
                    $errors = $validator->errors()->toArray();
                    return _json('error',$errors);
                } else {
                    return redirect()->back()->withInput()->withErrors($validator->errors()->toArray());
                }
            }
           
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
            $address->user_id = $this->User->id;
            $address->save();
           
            session()->flash('msg',_lang('app.added_successfully'));
            if($request->return){
                $url = url(base64_decode($request->return));
            }else{
                $url=_url('user-addresses');
            }
            //dd($url);
            if ($request->ajax()) {
                
               return _json('success',$url);
           } 
           return redirect($url);

        } catch (\Exception $e) {
            session()->flash('msg',_lang('app.error_is_occured_try_again_later'));
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $this->data['page_title'] = _lang('app.edit_address');
        $address_id = decrypt($id);
        $address = Address::where('id',$address_id)->where('user_id',$this->User->id)->first();
        if (!$address) {
            return $this->err404();
        }
        $this->data['address'] = $address;
        return $this->_view('addresses.edit');
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
            $address_id = decrypt($id);
            $address = Address::where('id',$address_id)->where('user_id',$this->User->id)->first();
            if (!$address) {
                return $this->err404();
            }
            $validator = Validator::make($request->all(), $this->rules);
            if ($validator->fails()) {

                if ($request->ajax()) {
                    $errors = $validator->errors()->toArray();
                    return _json('error',$errors);
                } else {
                    return redirect()->back()->withInput()->withErrors($validator->errors()->toArray());
                }
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
            session()->flash('msg',_lang('app.updated_successfully'));

            if ($request->ajax()) {
               return _json('success',route('user-addresses.index'));
           } 
           return redirect()->route('user-addresses.index');

        } catch (\Exception $e) {
            session()->flash('msg',_lang('app.error_is_occured_try_again_later'));
            return redirect()->back();
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
            $address_id = decrypt($id);
            $address = Address::where('id',$address_id)->where('user_id',$this->User->id)->first();
            if (!$address) {
                return $this->err404();
            }
            $address->delete();
            session()->flash('msg',_lang('app.deleted_successfully'));
           return redirect()->route('user-addresses.index');
        } catch (\Exception $e) {
            session()->flash('msg',_lang('app.error_is_occured_try_again_later'));
            return redirect()->back();
        }
    }
}
