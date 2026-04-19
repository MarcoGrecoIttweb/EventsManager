<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomePendingBannerController extends Controller
{
    public const SESSION_DISMISSED_IDS = 'admin_home_dismissed_pending_user_ids';

    public function dismiss(Request $request): RedirectResponse
    {
        $raw = (string) $request->input('user_ids', '');
        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw)))));

        if ($ids !== []) {
            $existing = $request->session()->get(self::SESSION_DISMISSED_IDS, []);
            if (!is_array($existing)) {
                $existing = [];
            }
            $merged = array_values(array_unique(array_merge(
                array_map('intval', $existing),
                $ids
            )));
            $request->session()->put(self::SESSION_DISMISSED_IDS, $merged);
        }

        return redirect()->route('home');
    }
}
