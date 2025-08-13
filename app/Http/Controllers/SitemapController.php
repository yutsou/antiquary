<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        // 獲取所有已發布的拍賣品 (狀態 20, 21, 61 表示正常狀態)
        $lots = Lot::whereIn('status', [20, 21, 61])
                  ->latest()
                  ->get();

        $content = view('sitemap', compact('lots'));

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
