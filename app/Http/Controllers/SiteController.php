<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SiteController extends MainController
{
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
        $website = Website::find($id);
        $data['website'] = $website;

        return view('site.site', $data);
    }

    /**
     * Save method
     */
    public function save(Request $request)
    {
        $request->validate([
            //url' => 'required|unique:website|max:255',
            'type' => 'required',
        ]);

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
        $alias = trim($request->alias);
        $alias = \str_replace("\r", '', $alias);
        $alias = \explode("\n", $alias);
        $website->alias = json_encode($alias);
        $website->type = $request->type;
        $website->http = (empty($request->protocols['http'])) ? false : true;
        $website->https = (empty($request->protocols['https'])) ? false : true;
        $website->http2https = (empty($request->protocols['http2https'])) ? false : true;
        $website->lets_encrypt = (empty($request->protocols['lets_encrypt'])) ? false : true;
        $website->updated_at = now();
        $website->save();

        $params = [
            'domain' => $url,
            'server' => $request->type,
        ];
        Artisan::call('website:create', $params);

        return redirect('/site');
    }

    public function delete(Request $request, int $id)
    {
        $website = Website::find($request->id);
        $website->delete();
        $website->save();

        return redirect('/site');
    }
}
