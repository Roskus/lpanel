<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;

class SiteController extends MainController
{
    /**
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $website = new Website();
        $data['websites'] = $website->all();
        return view('site.index', $data);
    }

    /**
     * @param Request $request
     */
    public function add(Request $request)
    {
        $website = new Website();
        $data['website'] = $website;
        return view('site.site', $data);
    }

    /**
     *
     * @param Request $request
     * @param int $id
     */
    public function edit(Request $request, int $id)
    {
        $website = Website::find($request->id);
        $data['website'] = $website;
        return view('site.site', $data);
    }

    /**
     * Save method
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
        // TODO: Add validator
        if (empty($request->id)) {
            $website = new Website();
            $website->created_at = now();
        } else {
            $website = Website::find($request->id);
        }
        $url = $request->url;
        $url = str_replace('http://', '', $url);
        $url = str_replace('https://', '', $url);
        $website->url = $url;
        $website->type = $request->type;
        $website->updated_at = now();
        $website->save();
        return redirect('/site');
    }
}
