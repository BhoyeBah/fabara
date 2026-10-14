<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function privacy() { return view('site.legal.privacy'); }
    public function terms() { return view('site.legal.terms'); }
}
