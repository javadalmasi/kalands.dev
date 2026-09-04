<?php

namespace App\Http\Controllers;

use App\Services\Slider\HomeCategoryBannerStorage;
use App\Services\Slider\SliderStorage;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function home(SliderStorage $sliderStorage, HomeCategoryBannerStorage $homeCategoryBannerStorage)
    {
        if (! Cache::has('home')) {
            $Data = [];
            $Data['data']['offers'] = $this->homeSection(
                'incredible-offers/products/?page=1',
                'شگفت انگیز ها'
            );
            $Data['data']['selling_stock'] = $this->homeSection(
                'search/?has_selling_stock=1&pageno=1&sortby=22',
                'محبوب ترین ها'
            );
            // $Data['data']['best_selling'] = ProductController::DigikalaApi('best-selling/', 'v1');
            // $Data['data']['best_selling']['title'] = "پر فروش ترین ها";
            // $Data['data']['best_selling']['link']['status'] = false;
            Cache::put('home', $Data, 3600);
            $slider = $sliderStorage->loadByModule('home_main_banners');
            $homeCategoryBanners = $homeCategoryBannerStorage->load();

            return response()->view('layouts.home.index', compact('Data', 'slider', 'homeCategoryBanners'));
            // return response()->view('layouts.home.index', compact('Data'))->header('Cache-Control', 'private, max-age=3600');
        } else {
            $Data = Cache::get('home');
            // Laravel 13 Cache::touch() - extend TTL without re-fetching
            Cache::touch('home', 3600);

            $slider = $sliderStorage->loadByModule('home_main_banners');
            $homeCategoryBanners = $homeCategoryBannerStorage->load();

            return response()->view('layouts.home.index', compact('Data', 'slider', 'homeCategoryBanners'));
            // ->header('Cache-Control', 'private, max-age=3600')
            // 	// ->header('X-LiteSpeed-Cache-Control', 'private, max-age=3600')
        }
    }

    private function homeSection(string $endpoint, string $title): array
    {
        try {
            $payload = ProductController::DigikalaApi($endpoint, 'v1', true);
        } catch (\Throwable $exception) {
            report($exception);
            $payload = ['data' => ['products' => []]];
        }

        if (! is_array($payload)) {
            $payload = ['data' => ['products' => []]];
        }

        if (! isset($payload['data']) || ! is_array($payload['data'])) {
            $payload['data'] = ['products' => []];
        }

        if (! isset($payload['data']['products']) || ! is_array($payload['data']['products'])) {
            $payload['data']['products'] = [];
        }

        $payload['data']['products'] = array_values(array_filter($payload['data']['products'], function ($item) {
            $uri = $item['url']['uri'] ?? '';
            // If it's a Digikala URL (contains dkp), enforce the pattern.
            // Other stores (Basalam/etc) should pass through.
            if (str_contains($uri, 'dkp-')) {
                return str_starts_with($uri, '/product/dkp-');
            }

            return true;
        }));

        $payload['title'] = $title;
        $payload['link']['status'] = false;

        return $payload;
    }
}
