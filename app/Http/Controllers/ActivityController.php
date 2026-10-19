<?php
namespace App\Http\Controllers;
use App\Models\Activity;
class ActivityController extends Controller
{
    public function index() {
        return view('public.activities.index',['activities'=>Activity::published()->with('images')->orderByDesc('activity_date')->orderByDesc('id')->paginate(9)]);
    }
    public function show(string $slug) {
        return view('public.activities.show',['activity'=>Activity::published()->with('images')->where('slug',$slug)->firstOrFail()]);
    }
}