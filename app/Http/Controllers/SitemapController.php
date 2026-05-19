<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;

class SitemapController extends Controller
{
    /**
     * Generate sitemap.xml for public pages.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $urls = collect([
            route('home'),
        ]);

        $productUrls = Product::where('active', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($product) {
                return route('product.show', $product);
            });

        $categoryUrls = Category::all()
            ->map(function ($category) {
                return route('category.show', $category);
            });

        $urls = $urls->merge($productUrls)->merge($categoryUrls)->unique()->values();

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
