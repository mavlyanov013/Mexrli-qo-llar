<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

namespace App\Http\Controllers;

use App\Models\Page;

class HomeController extends Controller
{
    public function index()
    {
        $page = Page::with(['sections' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])
            ->where('slug', 'home')
            ->where('is_active', true)
            ->first();

        return view('pages.home', compact('page'));
    }
}
