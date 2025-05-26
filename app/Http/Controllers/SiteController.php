<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Website;
use App\Service\Webserver\Apache;
use App\Service\Webserver\Nginx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SiteController extends MainController
{
    public function index(Request $request)
    {
        $apacheService = new Apache();
        $nginxService = new Nginx();

        $apacheEnabled = $apacheService->getEnabledSites();
        $apacheAvailable = $apacheService->getAvailableSites();
        $nginxEnabled = $nginxService->getEnabledSites();
        $nginxAvailable = $nginxService->getAvailableSites();

        $data['websites'] = array_merge(
            array_map(fn($site) => ['url' => $site, 'type' => 'Apache', 'status' => 'enabled'], $apacheEnabled),
            array_map(fn($site) => ['url' => $site, 'type' => 'Apache', 'status' => 'available'], array_diff($apacheAvailable, $apacheEnabled)),
            array_map(fn($site) => ['url' => $site, 'type' => 'Nginx', 'status' => 'enabled'], $nginxEnabled),
            array_map(fn($site) => ['url' => $site, 'type' => 'Nginx', 'status' => 'available'], array_diff($nginxAvailable, $nginxEnabled))
        );

        return view('site.index', $data);
    }

    public function create(Request $request)
    {
        $website = new Website;
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

        $url = $request->url;
        $url = str_replace('http://', '', $url);
        $url = str_replace('https://', '', $url);


        if (empty($request->id)) {
            $website = Website::where('url', $url)->find();
            if (!$website) {
                $website = new Website;
                $website->created_at = now();
            }
        } else {
            $website = Website::find($request->id);
        }
        $website->url = $url;
        $alias = $request->alias ? trim($request->alias) :'';
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
        Artisan::call('panel:website:create', $params);

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

