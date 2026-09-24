<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'    => $request->user()->id,
                    'name'  => $request->user()->name,
                    'email' => $request->user()->email,
                    'role'  => method_exists($request->user(), 'getRoleNames') ? ($request->user()->getRoleNames()->first() ?? 'Usuario') : 'Usuario',
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'sidebarMenu' => function () use ($request) {
                if (! $request->user()) {
                    return [];
                }
                $adminlte = app(\JeroenNoten\LaravelAdminLte\AdminLte::class);
                $rawMenu = collect($adminlte->menu('sidebar'))
                    ->filter(fn ($item) => \JeroenNoten\LaravelAdminLte\Helpers\MenuItemHelper::isAllowed($item))
                    ->values();

                $sections = [];
                $currentSection = null;

                foreach ($rawMenu as $item) {
                    if (isset($item['header'])) {
                        if ($currentSection && ! empty($currentSection['items'])) {
                            $sections[] = $currentSection;
                        }
                        $currentSection = [
                            'header' => $item['header'],
                            'items'  => [],
                        ];
                    } elseif (isset($item['text']) && (! isset($item['type']) || $item['type'] !== 'sidebar-menu-search')) {
                        if (! $currentSection) {
                            $currentSection = ['header' => '', 'items' => []];
                        }
                        $url = $item['href'] ?? $item['url'] ?? '#';
                        $path = parse_url($url, PHP_URL_PATH) ?: $url;

                        $spaRoutes = [
                            '/inicio',
                            '/accions',
                            '/accion-inicio',
                            '/accion-general',
                            '/accion-ugel',
                            '/accion-director',
                            '/difusions',
                            '/difusion-inicio',
                            '/difusion-general',
                            '/difusion-ugel',
                            '/difusion-director',
                            '/sector',
                            '/sectores',
                            '/sector-inicio',
                            '/sector-general',
                            '/sector-ugel',
                            '/sector-director',
                            '/evidencias',
                            '/evidencia-inicio',
                            '/evidencia-general',
                            '/evidencia-ugel',
                            '/evidencia-director',
                        ];
                        $isSpa = in_array(rtrim($path, '/'), $spaRoutes) || in_array(rtrim($url, '/'), $spaRoutes);

                        $currentSection['items'][] = [
                            'text'   => $item['text'],
                            'url'    => $item['url'] ?? $path,
                            'href'   => $url,
                            'icon'   => $item['icon'] ?? null,
                            'color'  => $item['icon_color'] ?? null,
                            'active' => $item['active'] ?? false,
                            'isSpa'  => $isSpa,
                        ];
                    }
                }
                if ($currentSection && ! empty($currentSection['items'])) {
                    $sections[] = $currentSection;
                }

                return $sections;
            },
        ]);
    }
}
