<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use App\Models\Pages;
use App\Traits\Basic;
use Auth;
use Image;

class BackendController extends Controller {

    use Basic;

    protected $lang_code = 'en';
    protected $User;
    protected $data = array();
    protected $status_text = [
             0 => 'pending',
             1 => 'order_processing_is_ongoing',
             2 => 'order_is_being_delivered',
             3 => 'order_was_deliverd',
             4 => 'order_was_rejected',
             5 => 'order_under_modification',
             6 => 'cancelled',
             7 => 'finish_modification'
    ];

    public function __construct() {
        $this->middleware('auth:admin');
        $segment2 = \Request::segment(2);
        $this->data['page_link_name'] = $segment2;
        $this->User = Auth::guard('admin')->user();
        $this->data['User'] = $this->User;
        $this->getCookieLangAndSetLocale();
        $pages = Pages::getPages();
        $this->data['pages'] = $this->sideBarHtml($pages);
        $this->slugsCreate();

    }

    protected function getResturantid()
    {
        
    }

    protected function getCookieLangAndSetLocale() {
        if (\Cookie::get('AdminLang') !== null) {
            try {
                $this->lang_code = \Crypt::decrypt(\Cookie::get('AdminLang'));
            } catch (DecryptException $ex) {
                $this->lang_code = 'en';
            }
        } else {
            $this->lang_code = 'en';
        }
       
        $this->data['lang_code'] = $this->lang_code;
        if ($this->lang_code == "ar") {
            $this->data['currency_sign'] = 'جنيه'; 
        }
        else{
            $this->data['currency_sign'] = 'EGP';
        }
       
        app()->setLocale($this->lang_code);
    }

    public function sideBarHtml($pages) {
        $markup = "";
        $page_link_name = $this->data['page_link_name'];
        $page_arr = Pages::where('controller', $page_link_name)->get();
        $page_parents = (count($page_arr)) ? explode(',', $page_arr[0]->parents_ids) : array();
//        dd($page_parents);
//        $ids = array();
        foreach ($pages as $page) {
            $parentClass = '';
            $style = '';
            if (\Permissions::check($page->name, 'open')) {

                if ($page_link_name === null) {
                    if ($page->name == 'dashboard') {
                        $parentClass = 'active';
                    }
                } else {

                    if (in_array($page->id, $page_parents)) {
                        $style = 'style="display:block;"';
                        $parentClass = 'active';
                    }
                    if (count($page_arr)) {
                        if ($page_arr[0]->id == $page->id) {
                            $parentClass = 'active';
                        }
                    }
                }

//                if (in_array($page->id, $ids)) {
//                    $style = 'style="display:block;"';
//                    $parentClass = 'active';
//                }
                $markup .= '<li class="nav-item start ' . $parentClass . '">';
                $url = (!empty($page->children)) ? 'javascript:;' : url("admin/$page->controller");
                $markup .= '<a href="' . $url . '" class="nav-link nav-toggle">';
                $markup .= '<i class="icon-home"></i>';
                $markup .= '<span class="title">' . _lang("app.$page->name") . '</span>';
                if (isset($page->children) && !empty($page->children)) {
                    $markup .= ' <span class="arrow"></span>';
                }
                $markup .= ' </a>';
                if (isset($page->children) && !empty($page->children)) {
                    $markup .= '<ul class="sub-menu" ' . $style . '>';
                    //$this->sideBarHtml($page->children);
                    $markup .= $this->sideBarHtml($page->children);
                    //dd($markup);
                    $markup .= '</ul>';
                }
                //dd($markup);
                $markup .= '</li>';
            }
        }

        return $markup;
    }

    protected function _upload2($file, $path) {
        $image = '';
        $path = public_path() . "/uploads/$path";
        $filename = time() . mt_rand(1, 1000000) . '.' . $file->getClientOriginalExtension();
        if ($file->move($path, $filename)) {
            $image = $filename;
        }
        return $image;
    }

    protected function _upload($file, $path, $resize = false, $model = false) {
        $image = '';
        $path = public_path() . "/uploads/$path";
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = time() . mt_rand(1, 1000000) . '.' . $extension;


        $image = Image::make($file);
        $names = array();
        if ($resize && $model) {
            if (isset($model::$sizes) && !empty($model::$sizes)) {
                foreach ($model::$sizes as $prefix => $size) {
                    $path_with_filename = $path . '/' . $prefix . '_' . $filename;
                    $image->backup();
                    if ($size['width'] == null && $size['height'] != null) {
                        //dd($prefix);
                        $image->resize(null, $size['height'], function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else if ($size['height'] == null && $size['width'] != null) {
                        $image->resize($size['height'], null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else {
                        $image->resize($size['width'], $size['height']);
                    }


                    $image = $image->save($path_with_filename, 100);
                    $image->reset();
                    $names[] = $image->basename;
                    //$image->reset();
                }
                return $names[0];
            }
        }
        $path_with_filename = $path . '/' . $filename;
        $image = $image->save($path_with_filename);
        return $image->basename;
    }

    protected function deleteUploaded($path, $old_image, $model) {
        $image_without_prefix = substr($old_image, strpos($old_image, '_') + 1); //without s_
        $files = array();
        if (isset($model::$sizes) && !empty($model::$sizes)) {
            foreach ($model::$sizes as $prefix => $size) {
                $files[] = public_path("uploads/$path/$prefix" . "_" . "$image_without_prefix");
            }
        }
        if (!empty($files)) {
            foreach ($files as $file) {
                if (!is_dir($file)) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    public function err404() {
        return view('main_content/backend/err404');
    }

}
