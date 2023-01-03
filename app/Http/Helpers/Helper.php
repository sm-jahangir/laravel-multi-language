<?php

use App\Helper\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\User;
use App\Vendor;
use App\Models\Logistic;
use App\Models\Product;
use App\Color;
use App\Models\Campaign;
use App\Models\Demo;
use App\Models\CampaignProduct;
use App\Models\Brand;
use App\Models\Commission;
use Stichoza\GoogleTranslate\GoogleTranslate;

//this function for open Json file to read language json file
function openJSONFile($code)
{
    $jsonString = [];
    if (File::exists(base_path('resources/lang/' . $code . '.json'))) {
        $jsonString = file_get_contents(base_path('resources/lang/' . $code . '.json'));
        $jsonString = json_decode($jsonString, true);
    }
    return $jsonString;
}

//save the new language json file
function saveJSONFile($code, $data)
{
    ksort($data);
    $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(base_path('resources/lang/' . $code . '.json'), stripslashes($jsonData));
}

//translate the key with json
function translate($key)
{
    $key = ucfirst(str_replace('_', ' ', $key));
    if (File::exists(base_path('resources/lang/en.json'))) {
        $jsonString = file_get_contents(base_path('resources/lang/en.json'));
        $jsonString = json_decode($jsonString, true);
        if (!isset($jsonString[$key])) {
            $jsonString[$key] = $key;
            saveJSONFile('en', $jsonString);
        }
    }

    return __($key);
}

//override or add env file or key
function overWriteEnvFile($type, $val)
{
    $path = base_path('.env'); // get file ENV path
    if (file_exists($path)) {
        $val = '"' . trim($val) . '"';
        if (is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0) {
            file_put_contents($path, str_replace($type . '="' . env($type) . '"', $type . '=' . $val, file_get_contents($path)));
        } else {
            file_put_contents($path, file_get_contents($path) . "\r\n" . $type . '=' . $val);
        }
    }
}

//scan directory for load flag
function readFlag()
{
    $dir = base_path('public/images/lang');
    $file = scandir($dir);
    return $file;
}

//auto Rename Flag
function flagRenameAuto($name)
{
    $nameSubStr = substr($name, 8);
    $nameReplace = ucfirst(str_replace('_', ' ', $nameSubStr));
    $nameReplace2 = ucfirst(str_replace('.png', '', $nameReplace));
    return $nameReplace2;
}

//format the Price
function formatPrice($price)
{
    $sc = session('currency');
    if ($sc != null) {
        $id = $sc;
    } else {
        $id = (int)getSystemSetting('default_currencies');
    }

    $currency = App\Models\Currency::find($id);
    $p =$price * $currency->rate;
    return $currency->align == 0 ? number_format($p, 0) . $currency->symbol :  $currency->symbol . number_format($p, 0);
}


/*default*/
function defaultCurrency()
{
    $sc = session('currency');
    if ($sc != null) {
        $id = $sc;
    } else {
        $id = (int)getSystemSetting('default_currencies');
    }
    $currency = \App\Models\Currency::find($id);
    return $currency->code;
}

/*Active Currency*/
function activeCurrency()
{
    $sc = session('currency');
    if ($sc != null) {
        $id = $sc;
    } else {
        $id = (int)getSystemSetting('default_currencies');
    }
    $currency = \App\Models\Currency::find($id);
    return $currency->code;
}

function activeCurrencyFlag()
{
    $sc = session('currency');
    if ($sc != null) {
        $id = $sc;
    } else {
        $id = (int)getSystemSetting('default_currencies');
    }
    $currency = \App\Models\Currency::find($id);
    return $currency->image;
}

/**
 * LANGUAGE
 */
function activeLanguage()
{
    $lang = Illuminate\Support\Facades\Session::get('locale') ?? env('DEFAULT_LANGUAGE');
    return $lang ?? 'en';
}

function activeLanguageCountryName($code)
{
    $language = App\Models\Language::where('code', $code)->first();
    return $language->name;
}

function activeLanguageFlag($code)
{
    $language = App\Models\Language::where('code', $code)->first();
    return $language->image;
}

//get system setting data
function getSystemSetting($key)
{
    return \App\Models\Settings::where('name', $key)->first()->value;
}

//get Promotions data
function getPromotions($key)
{
    return App\Models\Promotion::where('type', $key)->Published()->get();
}

//get Promotions data
function getPopup($key)
{
    return App\Models\Promotion::where('type', $key)->Published()->first();
}
//Get file path

//path is storage/app/
function filePath($file)
{
    return asset($file);
}

//delete file
function fileDelete($file)
{
    if ($file != null) {
        if (file_exists(public_path($file))) {
            unlink(public_path($file));
        }
    }
}

//uploads file
// uploads/folder
function fileUpload($file, $folder)
{
    return $file->store('uploads/' . $folder);
}

/*paginate default value*/
function paginate()
{
    return 12;
}

/*paginate default value*/
function sku()
{
    return rand(100000, 1000000);
}
/*calculate as percentage or amount*/
function commissionStatus()
{
    /*todo:here are the setting form admin how to calculate data ase percentage or amount*/
    return true;
}
/*vendor active or disable*/
function sellerStatus()
{
    /*todo::here are the setting vendor active or disable*/
    if (getSystemSetting('seller') == 'enable') {
        return true;
    } else {
        return false;
    }
}
/*seller publish mode*/
function sellerMode()
{
    /*todo::here seller Mode request is true freedom is false if any user issue activate request mode true */
    if (getSystemSetting('seller_mode') == 'request') {
        return true;
    } elseif (getSystemSetting('seller_mode') == 'freedom') {
        return false;
    } else {
        return true;
    }
}
/*customer login status*/
function loginStatus()
{
    /*todo::customer login in modal or not*/
    if (getSystemSetting('login_modal') == "on") {
        return '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Add to cart</button>';
    } else {
        return '<a class="btn btn-warning m-2 p-3 fs-12" href="#">Add to cart a</a>';
    }
}
/*brand Product Price*/
function brandProductPrice($sellers)
{
    $price = collect();
    foreach ($sellers as $min_price) {
        $price->push($min_price->product_price);
    }
    return $price;
}
/*brand Product sale Price*/
function brandProductSalePrice($sellers)
{
    $price = collect();
    foreach ($sellers as $min_price) {
        $price->push($min_price->discount_price);
    }
    return $price;
}

/*show category*/
function categories($items, $type)
{
    if ($items == 0) {
        return App\Models\Category::where('parent_category_id', 0)->Published()->with('childrenCategories')->get();
    }
    return App\Models\Category::where('parent_category_id', 0)->Published()->with('childrenCategories')->take($items)->get();
}
/*show products*/
function brandProducts($items, $slug)
{
    if ($items == 0) {
        return App\Models\Brand::where('slug', $slug)->Published()->with('products')->get();
    }
    return App\Models\Brand::where('slug', $slug)->Published()->with('products')->take($items)->get();
}

/*show products*/
function brandProductsCount($slug)
{
        return App\Models\Brand::where('slug', $slug)->Published()->with('products')->count();
    
}
/*brands*/
function brands($show)
{
    return App\Models\Brand::Published()->paginate($show);
}
/*brands shuffle*/
function brandsShuffle($showAll)
{
    return App\Models\Brand::Published()->paginate($showAll)->shuffle();
}
/*all products*/
function all_products()
{
    return App\Models\Product::latest()->Published()->paginate(36);
}
/*return sale products*/
function sale_products($paginate)
{
    if (vendorActive()) {
        return App\VendorProduct::with('sale_products')->Published()->Discounted()->paginate($paginate);
    }
    return \App\Models\Product::Published()->Discounted()->paginate($paginate);
}

function seller_product_count($id)
{
    return App\VendorProduct::where('user_id', $id)->with('products')->count();
}

/*total product count*/
function total_products()
{
    return App\Models\Product::Published()->count();
}
/*total product count*/
function authCart()
{
    try {
        return App\Models\Cart::where('user_id', Auth::user()->id)->count();
    }catch (Exception $exception){
        return 0;
    }
}
/*total product count*/
function authWishlist()
{
    try {
        return App\Models\Wishlist::where('user_id', Auth::user()->id)->count();
    }catch (Exception $exception){
        return 0;
    }
}
/*promotionBanners*/
function promotionBanners($type)
{
    return App\Models\Promotion::where('type', $type)->Published()->paginate(4);
}

/*promotion banners for backend*/
function promotionBannersForBackend($type)
{
    return App\Models\Promotion::where('type', $type)->paginate(4);
}
/*order count*/
function orderCount($type)
{
    if (vendorActive()) {
        $vendor = \App\Vendor::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
        if ($vendor == null) {
            if ($type == 'count') {
                return App\Models\OrderProduct::count();
            }
            if ($vendor == null) {
                return App\Models\OrderProduct::where('status', $type)->count();
            }
        } else {
            if ($type == 'count') {
                return App\Models\OrderProduct::where('shop_id', $vendor->id)->count();
            }
            return App\Models\OrderProduct::where('status', $type)->where('shop_id', $vendor->id)->count();
        }
    } else {
        if ($type == 'count') {
            return App\Models\EcomOrderProduct::count();
        } else {
            return App\Models\EcomOrderProduct::where('status', $type)->count();
        }
    }
}
/*complainCount*/
function complainCount($type)
{
    return App\Models\Complain::where('status', $type)->count();
}
//show info page in frontend
function infopage($section, $take)
{
    return \App\Models\infopage::where('section', $section)->with('page')->take($take)->get();
}
/*sub category*/
function subCategory()
{
    $cat = \App\Models\Category::where('parent_category_id', 0)->Published()->with('childrenCategories')->get();
    $subCat = collect();
    foreach ($cat as $x) {
        if ($x->childrenCategories->count() > 0) {
            foreach ($x->childrenCategories as $y) {
                if ($y->childrenCategories != null && $y->is_published == 1) {
                    foreach ($y->childrenCategories as $z) {
                        if ($z->childrenCategories != null && $z->is_published == 1 && $z->is_popular == 1) {
                            $subCat->push($z);
                        }
                    }
                }
            }
        }
    }
    return $subCat;
}

//there all ecommerce setting
function vendorActive()
{
    if (env('APP_ACTIVE') == 'ecommerce') {
        return false;
    }
    return true;
}


function shopRating($shop_id){
    $stars_count = App\Models\OrderProduct::where('shop_id', $shop_id)
        ->whereNotNull('review_star')
        ->select('review_star')
        ->get()
        ->toArray();



    $rateArray =[];
    foreach ($stars_count as $star_count)
    {
        $rateArray[]= $star_count['review_star'];
    }
    $sum = array_sum($rateArray);


    $customer_count = App\Models\OrderProduct::where('shop_id', $shop_id)
        ->whereNotNull('review_star')
        ->count();
    if ($customer_count > 0) {
        $result= round($sum/$customer_count);
    }else {
        $result= round($sum/1);
    }
    return $result == 0 ? 1 : $result;
}


//check if guest checkout is allowed
function guestCheckout()
{
    if (env('GUEST_CHECKOUT') == 'YES') {
        return true;
    }
    return false;
}

function newVariationRequest()
{
    return App\Models\Variant::where('is_published', 0)->count();
}

// check Paytm route for Mapping

function paytmRoute()
{
    if (file_exists(base_path('routes/paytm.php'))) {
        return true;
    }else{
        return false;
    }
}

// check Paytm route for blade
function paytmRouteForBlade()
{
    if (file_exists(base_path('routes/paytm.php'))) {
        return true;
    }else{
        return false;
    }
}

// check Paytm route for blade
function paytmActive()
{
    if (env('PAYTM_ACTIVE') == 'YES') {
        return true;
    }
    return false;
}


// check ProductExportImport route for Mapping

function ProductExportImportRoute()
{
    if (file_exists(base_path('routes/productexportimport.php'))) {
        return true;
    }else{
        return false;
    }
}

// check Paytm route for blade
function ProductExportImportActive()
{
    if (env('PRODUCTEXPORTIMPORT_ACTIVE') == 'YES') {
        return true;
    }
    return false;
}


// check ProductExportImport route for Mapping

function SslRoute()
{
    if (file_exists(base_path('routes/sslcommerz.php'))) {
        return true;
    }else{
        return false;
    }
}

// check Paytm route for blade
function SslActive()
{
    if (env('SSL_COMMERZ_ACTIVE') == 'YES') {
        return true;
    }
    return false;
}




//Affiliate Routes
function affiliateRoute()
{
    if (file_exists(base_path('routes/affiliate.php'))) {
        return true;
    }else{
        return false;
    }
}

//Affiliate add-on installed?
function affiliateActive()
{
    if (env('AFFILIATE_MARKETING') == 'YES') {
        return true;
    }
    return false;
}

//get affiliate user
function affiliateUser()
{
    $user = App\Models\AffiliateAccount::where('user_id', Auth::user()->id)->first();
    if(!is_null($user)){
        return true;
    }else{
        return false;
    }
}

//get approved affiliate user
function affiliateApprovedUser()
{
    $user = App\Models\AffiliateAccount::where('user_id', Auth::user()->id)->first();
    if($user->is_approved == 1){
        return true;
    }else{
        return false;
    }
}


//get approved & not blocked affiliate user
function affiliateNotBlockedUser()
{
    $user = App\Models\AffiliateAccount::where('user_id', Auth::user()->id)->first();
    if($user->is_blocked == 0){
        return true;
    }else{
        return false;
    }
}

//get cookie time day
function cookiesLimit(){
    $days = (int)getSystemSetting('affiliate_cookie_limit');
    /*return day*/
    return ($days*1440);
}

// get affiliate commission
function affiliateCommission(){
    $commission = (int)getSystemSetting('affiliate_commission');
    return $commission;
}


function logisticActive()
{
    if (env('LOGISTIC_ACTIVE') == "YES"){
        return true;
    }else{
        return  false;
    }
}


function deliverActive()
{
    if (config('manyvendor.delivery_active') == "YES"){
        return true;
    }else{
        return false;
    }
}

function userType()
{
    return Auth::user();
}

function userTypeIsDeliver(){

    return userType();
//    return Auth::user();
}

function deliverProcessPick(){
//    if (env('DELIVER_PROCESS') == "YES"){
////        Deliveryman Can Pick the order
//        return true;
//    }
//   Admin Assign the Orders in Deliveryman
    return  false;
}

/**
 * UUID
 */

function generateUuid()
 {
     return Str::uuid()->toString();
 }

 /**
  * invoice_path
  */
function invoice_path($file)
{
    return public_path('invoices/' . $file .'.pdf');
}

/**
 * All Customer
 */

 function allCustomer()
 {
     return User::select('id', 'name')->orderBy('name')->get();
 }

 /**
  * allShopName
  */
 function allShopName()
 {
     return Vendor::select('id', 'shop_name')->orderBy('shop_name')->get();
 }

 /**
  * allLogisticpName
  */
 function allLogisticpName()
 {
     return Logistic::select('id', 'name')->orderBy('name')->get();
 }

 /**
  * allLogisticpName
  */
 function allProductName()
 {
     return Product::select('id', 'name')->orderBy('name')->get();
 }

// check Paytm route for blade
function bankActive()
{
    if (env('BANK_TRANSFER') == 'YES') {
        return true;
    }
    return false;
}

/**
 * VERSION 2.6
 */

 function theme_color($name)
 {
     return Color::first()->$name ?? null;
 }

/**
 * VERSION 2.6::END
 */

//  version 3.0

/*show category product*/
function categoryProducts()
{
    return App\Models\Category::where('parent_category_id', 0)->with('CategoryProducts')->Published()->paginate(2);
}

function liveCampaign()
{
    return Campaign::where('active_for_customer',1)
                    ->Live()
                    ->orderBy('start_from','asc')
                    ->get();
}

function UpcomingCampaign()
{
    return Campaign::where('active_for_customer',1)
                    ->Upcoming()
                    ->orderBy('start_from','asc')
                    ->get();
}

//  version 3.0::END

/**
 * LANGUAGE TRANSLATE
 */

// Google translate
function gTrans($value, $targetLang, $sourceLang = null)
{

    $tr = new GoogleTranslate();
    return $transValue = $tr->setSource($sourceLang)->setTarget($targetLang)->translate($value);
}

// Manyvendor Translate
function mTranslate($values, $lang, $type)
{

    $langPath = base_path().'/resources/lang/' .$lang;
    
    if (!File::exists($langPath)) {
        File::makeDirectory($langPath,0775,true);
        fopen($langPath . $type .'.php', "x");
    }
    
    $path = base_path().'/resources/lang/' .$lang. '/'. $type .'.php';
    $content = "<?php\n\nreturn\n[\n";

    foreach ($values as $key => $value) 
    {
        $content .= '"'.$value->key.'" => "'.$value->value.'",' ."\n";
    }

    $content .= '];';

    file_put_contents($path, $content);

}

/** WRITE Existing Config or Lang PHP FILE */
function write_arr_to_file($arr, $path){
    $data = '';
    foreach ($arr as $key => $value) {
        $data = $data . '"' . $key. '"=>"' . $value . '",' ."\n";
    }

    // return file_put_contents($path, $data);
    return file_put_contents($path, "<?php  \nreturn [".$data."];");
}

/**
 * WRITE LANUGUAGE CATEGORY.PHP FILE
 */

function write_to_lang_category_file($name)
{
    $langs = App\Models\Language::all();

    foreach ($langs as $lang) {
      
        $path = base_path('resources/lang/'. $lang->code .'/categories.php');
        
        if (File::exists($path)) {
            // Read array from file
            $my_arr = trans('categories');

            $my_arr[$name] = $name;

            write_arr_to_file($my_arr, $path);
        }
    }
}

/**
 * WRITE LANUGUAGE PRODUCT.PHP FILE
 */

function write_to_lang_product_file($name)
{
    $langs = App\Models\Language::all();

    foreach ($langs as $lang) {
      
        $path = base_path('resources/lang/'. $lang->code .'/products.php');
        
        if (File::exists($path)) {
            // Read array from file
            $my_arr = trans('products');

            $my_arr[$name] = $name;

            write_arr_to_file($my_arr, $path);
        }
    }
}

/**
 * CHECK LANG FILE EXIST
 */

 function check_lang_file_exists($code, $file)
 {
     $path = base_path('resources/lang/'. $code .'/'. $file .'.php');

     if (!File::exists($path)) {
        $categories = \DB::table($file)->get('name');

        $datas = collect();

        foreach ($categories as $category) {

            $obj = new Demo;
            $obj->key = $category->name;
            $obj->value = $category->name;
            $datas->push($obj);
        }

        mTranslate($datas, $code, $file);
     }
 }

/**
 * LANGUAGE TRANSLATE::ENDS
 */

 /**
  * VERSION 3.3
  */

function makeSpaceBeforeCapitalLetter($string)
{
    return preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
}

 /**
  * VERSION 3.3::END
  */

  /**
   * VERSION 3.4::STARTS
   */

   function categoryCount()
   {
        return App\Models\Category::count();
   }

   function brandCount()
   {
        return Brand::count();
   }

   function commissionCount()
   {
        return Commission::count();
   }

  /**
   * VERSION 3.4::ENDS
   */

   /**
    * VERSION 4.0::STARTS
    */

    // create_a_visitor_count_for_product
    function create_a_visitor_count_for_product($product_id)
    {
        // check if visitor count is already created
        $visitor_count = App\ProductViews::where('product_id', $product_id)
                                        ->where('ipAddress', $_SERVER['REMOTE_ADDR'])
                                        ->first();

        $get_categories = Product::where('id', $product_id)->first();

        if ($visitor_count) {
            $visitor_count->total_count = $visitor_count->total_count + 1; // total_count
            $visitor_count->brand_id = $get_categories->brand_id; // brand_id
            $visitor_count->parent_id = $get_categories->parent_id; // parent_id
            $visitor_count->category_id = $get_categories->category_id; // category_id
            $visitor_count->category_group_id = $get_categories->category_group_id; // category_group_id
            $visitor_count->save();
        } else {
            $visitor_count = new App\ProductViews;
            $visitor_count->product_id = $product_id;
            $visitor_count->total_count = 1; // total_count
            $visitor_count->ipAddress = $_SERVER['REMOTE_ADDR']; // ipAddress
            $visitor_count->brand_id = $get_categories->brand_id; // brand_id
            $visitor_count->parent_id = $get_categories->parent_id; // parent_id
            $visitor_count->category_id = $get_categories->category_id; // category_id
            $visitor_count->category_group_id = $get_categories->category_group_id; // category_group_id
            $visitor_count->save();
        }
    }

    // IP BASED::STARTS

    // get most viewed product
    function ip_get_most_viewed_product()
    {
        $products = App\ProductViews::select('product_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('product_id')
                                    ->where('ipAddress', $_SERVER['REMOTE_ADDR'])
                                    ->orderBy('total_count', 'desc')
                                    ->get();

        return $products;
    }

    // get most viewed brand
    function ip_get_most_viewed_brand()
    {
        $brands = App\ProductViews::select('brand_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('brand_id')
                                    ->where('ipAddress', $_SERVER['REMOTE_ADDR'])
                                    ->orderBy('total_count', 'desc')
                                    ->limit($limit)
                                    ->get();

        return $brands;
    }

    // get most viewed category
    function ip_get_most_viewed_category()
    {
        $categories = App\ProductViews::select('category_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('category_id')
                                    ->where('ipAddress', $_SERVER['REMOTE_ADDR'])
                                    ->orderBy('total_count', 'desc')
                                    ->get();

        return $categories;
    }

    // get most viewed category
    function ip_get_most_viewed_parent_category()
    {
        $categories = App\ProductViews::select('parent_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('parent_id')
                                    ->where('ipAddress', $_SERVER['REMOTE_ADDR'])
                                    ->orderBy('total_count', 'desc')
                                    ->with('products')
                                    ->get();

        return $categories;
    }

    // get most viewed category
    function ip_get_most_viewed_category_group_id()
    {
        $categories = App\ProductViews::select('category_group_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('category_group_id')
                                    ->where('ipAddress', $_SERVER['REMOTE_ADDR'])
                                    ->orderBy('total_count', 'desc')
                                    ->get();

        return $categories;
    }

    // IP BASED::ENDS

    // get most viewed product
    function get_most_viewed_product()
    {
        $products = App\ProductViews::select('product_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('product_id')
                                    ->orderBy('total_count', 'desc')
                                    ->get();

        return $products;
    }

    // get most viewed brand
    function get_most_viewed_brand()
    {
        $brands = App\ProductViews::select('brand_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('brand_id')
                                    ->orderBy('total_count', 'desc')
                                    ->limit($limit)
                                    ->get();

        return $brands;
    }

    // get most viewed category
    function get_most_viewed_category()
    {
        $categories = App\ProductViews::select('category_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('category_id')
                                    ->orderBy('total_count', 'desc')
                                    ->get();

        return $categories;
    }

    // get most viewed category
    function get_most_viewed_parent_category()
    {
        $categories = App\ProductViews::select('parent_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('parent_id')
                                    ->orderBy('total_count', 'desc')
                                    ->with('products')
                                    ->get();

        return $categories;
    }

    // get most viewed category
    function get_most_viewed_category_group_id()
    {
        $categories = App\ProductViews::select('category_group_id', \DB::raw('SUM(total_count) as total_count'))
                                    ->groupBy('category_group_id')
                                    ->orderBy('total_count', 'desc')
                                    ->get();

        return $categories;
    }

    // INFINITY LOOP PRODUCTS::STARTS

    // get most viewed parent category products
    function get_most_viewed_parent_category_products()
    {
        $all_products = collect();

        foreach (ip_get_most_viewed_parent_category() as $products) {
            $all_products->push($products->products);
        }

        return $all_products;
    }

    // default products catelog

    function default_frontend_product_catelog()
    {
        if (get_most_viewed_parent_category_products()->count() < 0) {
            $products = get_most_viewed_parent_category_products();

            $mproductCollections = collect();

            foreach ($products[0] as $product) {
                $mproductCollections->push($product);
            }
        }else {
            $products = App\Models\Product::where('is_published', 1)->get();

            $mproductCollections = collect();

            foreach ($products as $product) {
                $mproductCollections->push($product);
            }
        }

        return $mproductCollections->shuffle();
    }

    // total sold products
    function total_sold_product($product_id)
    {
        $total_sold = App\Models\OrderProduct::where('product_id', $product_id)
                                    ->where('status', 'delivered')
                                    ->count();
        return $total_sold ?? 0;
    }

    // total procuct stock
    function total_product_stock($product_id)
    {
        if (vendorActive()) {
            $total_stock = App\VendorProduct::where('product_id', $product_id)
                                    ->sum('stock');
            return $total_stock ?? 0;
        }else {
            $total_stock = App\EcomProductVariantStock::where('product_id', $product_id)
                                    ->sum('quantity');
            return $total_stock ?? 0;
        }
        
    }

    // total left product stock
    function total_left_product_stock($product_id)
    {
        if (vendorActive()) {
            $total_stock = App\VendorProduct::where('product_id', $product_id)
                                    ->sum('stock');
            $total_sold = App\Models\OrderProduct::where('product_id', $product_id)
                                        ->where('status', 'delivered')
                                        ->count();
            $left_stock = $total_stock - $total_sold;
            return $left_stock ?? 0;
        }else {
            $total_stock = App\EcomProductVariantStock::where('product_id', $product_id)
                                    ->sum('quantity');
            $total_sold = App\Models\EcomOrderProduct::where('product_id', $product_id)
                                        ->where('status', 'delivered')
                                        ->count();
            $left_stock = $total_stock - $total_sold;
            return $left_stock ?? 0;
        }
        
    }

    // calculate product stock in percentage
    function product_stock_percentage($product_id)
    {
        if (vendorActive()) {
            $total_stock = App\VendorProduct::where('product_id', $product_id)
                                    ->sum('stock');
            $total_sold = App\Models\OrderProduct::where('product_id', $product_id)
                                        ->where('status', 'delivered')
                                        ->count();
            $left_stock = $total_stock - $total_sold;

            if ($total_stock == 0 || $total_stock == null) {
                return 0;
            } else {
                $percentage = ($left_stock / $total_stock) * 100;
                return $percentage;
            }
        }else {
            $total_stock = App\EcomProductVariantStock::where('product_id', $product_id)
                                    ->sum('quantity');
            $total_sold = App\Models\EcomOrderProduct::where('product_id', $product_id)
                                        ->where('status', 'delivered')
                                        ->count();
            $left_stock = $total_stock - $total_sold;

            if ($total_stock == 0 || $total_stock == null) {
                return 0;
            } else {
                $percentage = ($left_stock / $total_stock) * 100;
                return $percentage;
            }
        }
        
    }

    // removeThirdBrackets
    function removeThirdBrackets($string)
    {
        $string = str_replace('[', '', $string);
        $string = str_replace(']', '', $string);
        $string = str_replace('"', '', $string);
        return $string;
    }


    /**
     * AFFILIATE SITES
     */

    function affiliateSites()
    {
        return [
            'Amazon',
            'AliExpress',
            'Flipkart',
            'Snapdeal',
            'Myntra',
            'Ebay',
            'Daraz',
            'JD.com',
            'pinduoduo',
            'suning.com',
        ];
    }

    /**
     * GET AFFILIATE SITE PRODUCT
     */
    function getAffiliateSiteProduct($product_id)
    {
        $affiliate = App\AffiliateProductLink::where('product_id', $product_id)->first();
        return $affiliate;
    }

    /**
     * ACTIVE CAMPAIGN
     */
    function activeCampaign()
    {
        return Campaign::where('active_for_customer', 1)->On()->orderBy('start_from', 'asc')->get();
    }

   /**
    * VERSION 4.0::ENDS
    */
    function bestSellingProduct()
    {
        $products = Product::query()
        ->join('order_products', 'order_products.product_id', '=', 'products.id')
        ->selectRaw('products.*, SUM(order_products.quantity) AS quantity_sold')
        ->groupBy(['products.id']) // should group by primary key
        ->orderBy('quantity_sold', 'DESC') // should order by
        ->where('status', 'delivered')
        ->get();
        return $products;
    }