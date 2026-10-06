<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WhitepaperController extends Controller
{
    public function index(): View
    {
        return view('whitepaper.index', [
            'productName' => 'LIS CMS',
            'productLabel' => 'Legislative Content Management System',
            'homeRoute' => route('login'),
            'loginRoute' => route('login'),
            'loginLabel' => 'Staff Login',
            'cmsUrl' => rtrim(config('app.cms_url', config('app.url')), '/'),
            'lisUrl' => rtrim(config('app.lis_url', ''), '/'),
            'dmsUrl' => rtrim(config('app.dms_url', ''), '/'),
        ]);
    }
}
