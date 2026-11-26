<?php

namespace App\Http\Controllers;

use App\Support\WhitePaperDocx;
use Illuminate\View\View;

class WhitepaperController extends Controller
{
    public function index(): View
    {
        return view('whitepaper.index', $this->viewData());
    }

    public function download(WhitePaperDocx $docx)
    {
        return $docx->download(
            'whitepaper.index',
            $this->viewData(),
            'WP_LIS_CMS.docx',
            'LIS CMS White Paper',
            'Legislative Content Management System'
        );
    }

    private function viewData(): array
    {
        return [
            'productName' => 'LIS CMS',
            'productLabel' => 'Legislative Content Management System',
            'homeRoute' => route('login'),
            'loginRoute' => route('login'),
            'loginLabel' => 'Staff Login',
            'cmsUrl' => rtrim(config('app.cms_url', config('app.url')), '/'),
            'lisUrl' => rtrim(config('app.lis_url', ''), '/'),
            'dmsUrl' => rtrim(config('app.dms_url', ''), '/'),
        ];
    }
}
