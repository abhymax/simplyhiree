<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Single source of truth for the client-side navigation menu.
 *
 * Both the client dashboard (which renders its own sidebar) and layouts/client
 * (used by every inner page) call ClientNav::menu(), so a menu item only ever
 * has to be added or changed in ONE place.
 */
class ClientNav
{
    /** @return array<int,array{icon:string,label:string,route:string,active:bool}> */
    public static function menu(): array
    {
        $cu = Auth::user();
        if (!$cu) {
            return [];
        }

        $isOwner = $cu->hasRole('client') && empty($cu->parent_partner_id);
        $mod = fn ($m) => $isOwner || (is_array($cu->team_modules) && in_array($m, $cu->team_modules, true));
        $isReferral = $cu->hasRole('referral_partner');

        $menu = [
            ['icon' => 'fa-solid fa-chart-line',            'label' => 'Dashboard',          'route' => route('client.dashboard'),          'active' => request()->routeIs('client.dashboard')],
            ['icon' => 'fa-solid fa-briefcase',             'label' => 'My Jobs',             'route' => route('client.jobs.index'),         'active' => request()->is('client/jobs*')],
            ['icon' => 'fa-solid fa-file-lines',            'label' => 'Applications',        'route' => route('client.applications.index'), 'active' => request()->is('client/applications*') && !request()->has('joined_status')],
            ['icon' => 'fa-solid fa-video',                 'label' => 'Interviews',          'route' => route('client.interviews.calendar'),'active' => request()->is('client/interviews*')],
            ['icon' => 'fa-solid fa-arrows-rotate',         'label' => 'Replacements',        'route' => route('client.replacements.index'), 'active' => request()->routeIs('client.replacements.*'), 'visible' => $mod('selection')],
            ['icon' => 'fa-solid fa-handshake',             'label' => 'Sourcing Partners',   'route' => route('client.vendors.browse'),     'active' => request()->is('client/vendors*') || request()->is('client/vendor-performance*'), 'visible' => $mod('vendors')],
            ['icon' => 'fa-solid fa-clipboard-question',    'label' => 'Questionnaires',      'route' => route('client.assessments.index'),  'active' => request()->is('client/assessments*'), 'visible' => $mod('assessments')],
            ['icon' => 'fa-solid fa-square-poll-vertical',  'label' => 'Assessment Results',  'route' => route('client.assessment-results.index'), 'active' => request()->is('client/assessment-results*'), 'visible' => $mod('assessments')],
            ['icon' => 'fa-solid fa-users',                 'label' => 'Team',                'route' => route('client.team.index'),         'active' => request()->is('client/team*'), 'visible' => $isOwner],
            ['icon' => 'fa-solid fa-file-invoice-dollar',   'label' => 'Invoices & Billing',  'route' => route('client.billing'),            'active' => request()->is('client/billing*'), 'visible' => $mod('billing')],
            ['icon' => 'fa-solid fa-share-nodes',           'label' => $isReferral ? 'Referral Dashboard' : 'Refer & Earn', 'route' => $isReferral ? route('referral.dashboard') : route('referral.enroll'), 'active' => request()->routeIs('referral.*')],
            ['icon' => 'fa-solid fa-gear',                  'label' => 'Settings',            'route' => route('client.profile.company'),    'active' => request()->is('client/profile*'), 'visible' => $mod('company')],
            ['icon' => 'fa-solid fa-circle-question',       'label' => 'Help & Support',      'route' => route('support'),                   'active' => request()->is('support*')],
        ];

        return array_values(array_filter($menu, fn ($i) => ($i['visible'] ?? true)));
    }
}
