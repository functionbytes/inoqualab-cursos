<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;

class BundlesController extends Controller
{
    public function index()
    {
        seo()->setCanonical(url()->current());

        $bundles = Bundle::available()->latest()->withCount('courses')->with(['courses' => fn ($q) => $q->with('media')->limit(1)])->get();

        return view('pages.views.bundles.index')->with([
            'bundles' => $bundles,
        ]);
    }

    public function view($slack)
    {
        $bundle = Bundle::slug($slack);

        if (! $bundle instanceof Bundle) {
            $bundle = Bundle::slack($slack);
        }

        abort_unless($bundle instanceof Bundle, 404);

        $bundleImage = optional($bundle->courses()->first())->getFirstMediaUrl('thumbnail') ?: getMeta();

        seo()->loadFromModel($bundle->load('seoMeta'))
            ->setOgType('article')
            ->setOgImage($bundleImage)
            ->setCanonical(url()->current());

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
