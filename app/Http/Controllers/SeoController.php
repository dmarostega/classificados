<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(SitemapService $sitemap): Response
    {
        return response()
            ->view('sitemap', ['urls' => $sitemap->urls()])
            ->header('Content-Type', 'application/xml');
    }
}
