<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\Friendship;

class SearchController extends ApiController {

    

    public function __construct() {
        parent::__construct();
    }

    protected function index(Request $request) {
        $type = $request->input('type');
        $page = $request->input('page');
        $query = $request->input('query');
        if ($page) {
            $current_page = $page;
        } else {
            $current_page = 1;
        }
        $offset = ($current_page - 1) * $this->limit;
        if ($type && $type == 1) {

            $result = Friendship::search($query, $this->limit, $offset);
            $result = Friendship::transformCollection($result, 'search');
        } else if ($type && $type == 2) {

            $result = Post::search($query, $this->limit, $offset);
            $result = Post::transformCollection($result, 'search');
        } else {
            return _api_json(false, '', 400, ['message' => _lang('app.error_is_occured')]);
        }
        try {
            return _api_json(true, $result);
        } catch (Exception $ex) {
            return _api_json(false, '', 400, ['message' => _lang('app.error_is_occured')]);
        }
    }

}
