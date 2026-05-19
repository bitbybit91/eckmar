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

        $productUrls = collect();
        Product::where('active', true)
            ->orderByDesc('created_at')
            ->chunk(500, function ($products) use (&$productUrls) {
                foreach ($products as $product) {
                    $productUrls->push(route('product.show', $product));
                }
            });

        $categoryUrls = collect();
        Category::query()->chunk(500, function ($categories) use (&$categoryUrls) {
            foreach ($categories as $category) {
                $categoryUrls->push(route('category.show', $category));
            }
        });

        $urls = $urls->merge($productUrls)->merge($categoryUrls)->unique()->values();

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }
}
