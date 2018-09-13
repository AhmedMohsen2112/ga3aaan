<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\FrontController;
use App\Models\ContactMessage;
use App\Models\Offer;
use Validator;

class StaticController extends FrontController {

    private $contact_rules = array(
        'email' => 'required|email',
        'subject' => 'required',
        'message' => 'required',
        'type' => 'required'
    );

    public function __construct() {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function terms_conditions() {
        $this->data['page_title'] = _lang('app.terms_and_conditions');
        return $this->_view('static_pages/terms_conditions');
    }

    public function about_us() {
        $this->data['page_title'] = _lang('app.about_us');
        return $this->_view('static_pages/about_us');
    }

    public function usage_coditions() {
        $this->data['page_title'] = _lang('app.usage_conditions');
        return $this->_view('static_pages/usage_conditions');
    }

    public function contact_us() {
        $this->data['page_title'] = _lang('app.contact_us');
        return $this->_view('static_pages/contact_us');
    }

    public function offers() {
        $this->data['page_title'] = _lang('app.offers');
        $this->data['offers'] = $this->getOffers();
        //dd($this->data['offers']);
        return $this->_view('static_pages/offers');
    }

    private function getOffers() {

        $offers = Offer::join('resturantes', 'resturantes.id', '=', 'offers.resturant_id')
                ->join('resturant_branches', 'resturantes.id', '=', 'resturant_branches.resturant_id')
                ->where('offers.available_until', '>', date('Y-m-d'))
                ->orderBy('offers.this_order', 'ASC')
                ->select(["offers.image as image", "resturantes.title_$this->lang_code as resturant_title",
                    "resturantes.slug as resturant_slug", "offers.type", "offers.available_until", "offers.discount"])
                ->groupBy('resturantes.id')
                ->paginate($this->limit);
        $offers->getCollection()->transform(function($offer, $key) {
            return Offer::transformOffersPage($offer);
        });
        return $offers;
    }

    public function sendContactMessage(Request $request) {
        $validator = Validator::make($request->all(), $this->contact_rules);
        if ($validator->fails()) {
            if ($request->ajax()) {

                $errors = $validator->errors()->toArray();
                return response()->json([
                            'type' => 'error',
                            'errors' => $errors
                ]);
            } else {
                return redirect()->back()->withInput()->withErrors($validator->errors()->toArray());
            }
        } else {
            try {
                $ContactMessage = new ContactMessage;
                $ContactMessage->email = $request->input('email');
                $ContactMessage->subject = $request->input('subject');
                $ContactMessage->message = $request->input('message');
                $ContactMessage->type = $request->input('type');
                $ContactMessage->save();

                if ($request->ajax()) {
                    return _json('success', _lang('app.sent_successfully'));
                } else {
                    session()->flash('message', _lang('app.sent_successfully'));
                    return redirect()->back();
                }
            } catch (\Exception $ex) {
                session()->flash('message', _lang('app.error_occured'));
                return redirect()->back()->withInput();
            }
        }
    }

}
