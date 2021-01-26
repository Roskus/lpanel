<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;

class SiteController extends MainController
{
    //
    public function index(Request $request)
    {
        $website = new Website();
        $data['websites'] = $website->all();
        return view('site.index', $data);
    }

    public function add(Request $request)
    {
        $website = new Website();
        $data['website'] = $website;
        return view('site.site', $data);
    }

    public function edit(Request $request, int $id)
    {
        $website = Website::find($request->id);
        $data['website'] = $website;
        return view('site.site', $data);
    }

    public function save(Request $request)
    {
        if (empty($request->id)) {
            $website = new Website();
            $website->created_at = now();
        } else {
            $website = Website::find($request->id);
        }
        $website->url = $request->url;
        $website->type = $request->type;
        $website->updated_at = now();
        $website->save();
        return redirect('/site');
    }
}
