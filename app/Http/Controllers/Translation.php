<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Session;
use Closure;
use App;


class Translation extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
/** public function __construct()
    {

        $this->middleware('auth');
    }
**/
    public function changeLocale(Request $request)
    { 
        
        $this->validate($request, ['locale' => 'required|in:ar,en']);
        \Session::put('locale', $request->locale);
        return redirect()->back();
    }
    
    public function translate(Request $request)
    {
        $messages="";
        $this->auto_render = false; 
        if(isset($_POST['text']) && $_POST['text']!=""){
            $messages = trans('messages.'.$_POST['text']);
        }
       // $messages = I18n::load(App::getCurrentLang()->getLanguageCode());
        /*foreach ($messages as &$val) {
            $val = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
                function($matches) {
                    return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
                }, $val);
        } */
       echo json_encode($messages);exit;
       $this->getResponse()->body();
    }
}
