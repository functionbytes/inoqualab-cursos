<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Str;

class BundlesController extends Controller
{
    public function index()
    {

        SEOMeta::setTitle(getSetting()->meta_title);
        SEOMeta::setDescription(getSetting()->meta_description);
        SEOMeta::setCanonical(getUrl());

        SEOTools::setTitle(getSetting()->meta_title);
        SEOTools::setDescription(getSetting()->meta_description);
        SEOTools::opengraph()->setUrl(getUrl());
        SEOTools::setCanonical(getUrl());
        SEOTools::opengraph()->addProperty('type', 'articles');
        SEOTools::twitter()->setSite('@bpmsandiego');
        SEOTools::jsonLd()->addImage(getMeta());

        OpenGraph::setTitle(getSetting()->meta_title);
        OpenGraph::setDescription(getSetting()->meta_description);
        OpenGraph::setUrl(getUrl());
        OpenGraph::addProperty('type', 'article');
        OpenGraph::addProperty('locale', 'en-En');
        OpenGraph::addImage(getMeta());

        JsonLd::setTitle(getSetting()->meta_title);
        JsonLd::setDescription(getSetting()->meta_description);
        JsonLd::addImage(getMeta());

        // Catálogo de paquetes: pocos registros, se cargan todos con el nº de cursos.
        $bundles = Bundle::available()->latest()->withCount('courses')->with(['courses' => fn ($q) => $q->with('media')->limit(1)])->get();

        return view('pages.views.bundles.index')->with([
            'bundles' => $bundles,
        ]);

    }

    public function view($slack)
    {

        // La ruta usa {slug}; se busca por slug con fallback a slack por compatibilidad.
        $bundle = Bundle::slug($slack);

        // El scope slug()/slack() devuelve el Builder (no null) cuando no hay
        // match, por eso se comprueba el tipo concreto en lugar de "falsy".
        if (! $bundle instanceof Bundle) {
            $bundle = Bundle::slack($slack);
        }

        abort_unless($bundle instanceof Bundle, 404);

        $bundleTitle = $bundle->title.' | '.getSetting()->meta_title;
        $bundleDescription = strip_tags(Str::limit($bundle->short_detail ?: $bundle->description ?? '', 160));
        $bundleImage = optional($bundle->courses()->first())->getFirstMediaUrl('thumbnail') ?: getMeta();
        $bundleUrl = url()->current();

        SEOMeta::setTitle($bundleTitle);
        SEOMeta::setDescription($bundleDescription);
        SEOMeta::setCanonical($bundleUrl);

        SEOTools::setTitle($bundleTitle);
        SEOTools::setDescription($bundleDescription);
        SEOTools::opengraph()->setUrl($bundleUrl);
        SEOTools::setCanonical($bundleUrl);
        SEOTools::opengraph()->addProperty('type', 'article');
        SEOTools::twitter()->setSite('@bpmsandiego');
        SEOTools::jsonLd()->addImage($bundleImage);

        OpenGraph::setTitle($bundleTitle);
        OpenGraph::setDescription($bundleDescription);
        OpenGraph::setUrl($bundleUrl);
        OpenGraph::addProperty('type', 'article');
        OpenGraph::addProperty('locale', 'es-CO');
        OpenGraph::addImage($bundleImage);

        JsonLd::setTitle($bundleTitle);
        JsonLd::setDescription($bundleDescription);
        JsonLd::addImage($bundleImage);

        $courses = $bundle->courses()->orderBy('title', 'asc')->get();

        $alreadyOwned = false;
        if (auth()->check()) {
            $paidOrderIds = Order::where('user_id', auth()->id())
                ->whereNotNull('payment_at')
                ->pluck('id');

            $alreadyOwned = OrderItem::whereIn('order_id', $paidOrderIds)
                ->where('item_type', Bundle::class)
                ->where('item_id', $bundle->id)
                ->exists();
        }

        return view('pages.views.bundles.view')->with([
            'bundle' => $bundle,
            'courses' => $courses,
            'alreadyOwned' => $alreadyOwned,
        ]);

    }
}
