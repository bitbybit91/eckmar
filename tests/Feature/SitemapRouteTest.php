<?php

namespace Tests\Feature;

use Tests\TestCase;

class SitemapRouteTest extends TestCase
{
    public function testSitemapRouteIsRegistered()
    {
        $this->assertTrue(\Route::has('sitemap'));
    }

    public function testRobotsTxtFileExists()
    {
        $this->assertFileExists(public_path('robots.txt'));
    }
}
