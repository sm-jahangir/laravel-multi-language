<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class LanguageController extends Controller
{
    
    //list of language
    public function langIndex()
    {
        $languages = Language::all();
        return view('backend.common.setting.lang.language',compact('languages'));
    }


    //store a  new language
    public function langStore(Request $request)
    {
        $request->validate([
            'code' => ['required', 'unique:languages'],
            'name' => ['required', 'unique:languages'],
            'image' => ['required', 'unique:languages']
        ],[
            'code.required'=>'Code is required',
            'name.required'=>'Name is Required',
            'image.required'=>'Image is required'
        ]);
        $lan = new Language();
        $lan->code =Str::lower(str_replace(' ','_',$request->code));
        $lan->name = $request->name;
        $lan->image = $request->image;
        $lan->save();
        return back()->with('success',translate('Language Created Successfully'));
    }

    //delete the language
    public function langDestroy($id)
    {
        Language::where('id', $id)->forceDelete();
        return back()->with('success',translate('Language Deleted Successfully'));
    }


    //languages for create translate
    public function translate_create($id)
    {
        $lang = Language::findOrFail($id);
        return view('backend.common.setting.lang.translate',compact('lang'));
    }

    //translate language save ase a json format file
    public function translate_store(Request $request)
    {
        $language = Language::findOrFail($request->id);

        //check the key have translate data
        $data = openJSONFile($language->code);
        foreach ($request->translations as $key => $value) {
            $data[$key] = $value;
        }

        //save the new keys translate data
        saveJSONFile($language->code, $data);
        return back()->with('success',translate('Translation has been saved.'));
    }

    /*languages change in session*/
    public function languagesChange(Request $request)
    {
        session(['locale' => $request->code]);
        Artisan::call('optimize:clear');
        return back();
    }

    //defaultLanguage
    public function defaultLanguage($id)
    {
        $language = Language::findOrFail($id);
        overWriteEnvFile('DEFAULT_LANGUAGE', $language->code);
        return redirect()->back()->with('success', translate('Successful.'));
    }

    /** version 3.2 */

    //languages for create category translate
    public function translate_categories_create($id)
    {
        $lang = Language::findOrFail($id);

        /** VERSION 3.2 */
        if(function_exists('check_lang_file_exists')){
            check_lang_file_exists($lang->code, 'categories');
        }

        return view('backend.common.setting.lang.translate_category',compact('lang'));
    }


}
