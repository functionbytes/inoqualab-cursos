<?php

namespace App\Services;

use App\Services\Concerns\BaseNavService;

/**
 * Navegación del panel manager en formato "icon rail + panel lateral" (mismo
 * patrón que modules/Theme/app/Services/NavService.php en inoqualab, pero
 * como config estática: training es monolito sin ServiceProviders por
 * módulo que registren items dinámicamente). La lógica de detección de
 * item/sidebar activo vive en BaseNavService, compartida por todos los
 * portales (managers, supports, distributors, enterprises, accountings).
 */
class ManagerNavService extends BaseNavService
{
    /**
     * Estructura completa del menú: mini-items (rail de iconos) + sidebars
     * (paneles laterales con secciones e items). Portado 1:1 desde el menú
     * plano anterior en managers/includes/nav.blade.php.
     */
    protected static function menu(): array
    {
        return [
            // Un icono por dominio (como en modules/Theme de inoqualab: cada
            // módulo tiene su propio icono en el rail, no unos pocos cajones
            // genéricos tipo "Contenido/Plataforma").
            'miniItems' => [
                ['id' => 'dashboard', 'icon' => 'fa-duotone fa-house', 'tooltip' => 'Dashboard', 'sidebar_id' => 'dashboard', 'url' => 'manager.dashboard', 'order' => 1],
                ['id' => 'courses', 'icon' => 'fa-duotone fa-ballot-check', 'tooltip' => 'Cursos', 'sidebar_id' => 'courses', 'order' => 2],
                ['id' => 'orders', 'icon' => 'fa-duotone fa-memo-pad', 'tooltip' => 'Órdenes', 'sidebar_id' => 'orders', 'order' => 3],
                ['id' => 'mails', 'icon' => 'fa-duotone fa-envelope', 'tooltip' => 'Correos', 'sidebar_id' => 'mails', 'order' => 4],
                ['id' => 'newsletter', 'icon' => 'fa-duotone fa-paper-plane', 'tooltip' => 'Newsletter', 'sidebar_id' => 'newsletter', 'order' => 5],
                ['id' => 'reviews', 'icon' => 'fa-duotone fa-star', 'tooltip' => 'Reseñas', 'sidebar_id' => 'reviews', 'order' => 6],
                ['id' => 'platform', 'icon' => 'fa-duotone fa-graduation-cap', 'tooltip' => 'Plataforma', 'sidebar_id' => 'platform', 'order' => 7],
                ['id' => 'users', 'icon' => 'fa-duotone fa-user-vneck-hair', 'tooltip' => 'Usuarios', 'sidebar_id' => 'users', 'order' => 8],
                ['id' => 'help', 'icon' => 'fa-duotone fa-circle-exclamation', 'tooltip' => 'Ayuda', 'sidebar_id' => 'help', 'order' => 9],
                ['id' => 'analytics', 'icon' => 'fa-duotone fa-square-poll-vertical', 'tooltip' => 'Analytics y SEO', 'sidebar_id' => 'analytics', 'order' => 10],
                ['id' => 'settings', 'icon' => 'fa-duotone fa-gear-code', 'tooltip' => 'Configuración', 'sidebar_id' => 'settings', 'order' => 11],
            ],
            'sidebars' => [
                'courses' => [
                    'sections' => [[
                        'title' => 'Cursos',
                        'items' => [
                            ['label' => 'Cursos', 'route' => 'manager.courses', 'permission' => 'courses.view'],
                            ['label' => 'Categorias', 'route' => 'manager.categories.courses', 'permission' => 'courses.view'],
                            ['label' => 'Cupones', 'route' => 'manager.coupons', 'permission' => 'coupons.view', 'setting' => 'module_coupons'],
                            ['label' => 'Paquetes', 'route' => 'manager.bundles', 'permission' => 'bundles.view', 'setting' => 'module_bundles'],
                        ],
                    ]],
                ],
                'orders' => [
                    'sections' => [[
                        'title' => 'Órdenes',
                        'items' => [
                            ['label' => 'Ordenes', 'route' => 'manager.orders', 'permission' => 'orders.view'],
                            ['label' => 'Facturas', 'route' => 'manager.invoices', 'permission' => 'invoices.view', 'setting' => 'module_invoices'],
                            ['label' => 'Carritos incompletos', 'route' => 'manager.cart-abandonments.index'],
                        ],
                    ]],
                ],
                'mails' => [
                    'sections' => [[
                        'title' => 'Correos',
                        'items' => [
                            ['label' => 'Correos entrantes', 'route' => 'manager.mails.index', 'setting' => 'module_incoming_mail'],
                            ['label' => 'Contactenos', 'route' => 'manager.contacts', 'permission' => 'contacts.view', 'setting' => 'module_contacts'],
                            ['label' => 'Departamentos', 'route' => 'manager.departments', 'permission' => 'departments.view', 'setting' => 'module_departments'],
                        ],
                    ]],
                ],
                'newsletter' => [
                    'sections' => [[
                        'title' => 'Newsletter',
                        'items' => [
                            ['label' => 'Suscriptores', 'route' => 'manager.newsletter.index', 'setting' => 'module_newsletter'],
                            ['label' => 'Campañas', 'route' => 'manager.newsletter.campaigns.index', 'setting' => 'module_newsletter'],
                            ['label' => 'Listas', 'route' => 'manager.newsletter.lists.index', 'setting' => 'module_newsletter'],
                            ['label' => 'Remarketing', 'route' => 'manager.newsletter.remarketing', 'setting' => 'module_newsletter'],
                            ['label' => 'Configuración', 'route' => 'manager.settings.newsletter', 'setting' => 'module_newsletter'],
                        ],
                    ]],
                ],
                'reviews' => [
                    'sections' => [[
                        'title' => 'Reseñas',
                        'items' => [
                            ['label' => 'Testimonios', 'route' => 'manager.testimonies', 'permission' => 'testimonies.view'],
                            ['label' => 'Reseñas', 'route' => 'manager.reviews', 'setting' => 'module_reviews'],
                        ],
                    ]],
                ],
                'platform' => [
                    'sections' => [[
                        'title' => 'Plataforma',
                        'items' => [
                            ['label' => 'Certificados', 'route' => 'manager.certifications', 'setting' => 'module_certifications'],
                            ['label' => 'Capacitadores', 'route' => 'manager.certifiers', 'permission' => 'certifiers.view', 'setting' => 'module_certifiers'],
                            ['label' => 'Empresas', 'route' => 'manager.enterprises', 'permission' => 'enterprises.view', 'setting' => 'module_enterprises'],
                            ['label' => 'Distribuidores', 'route' => 'manager.distributors', 'permission' => 'distributors.view', 'setting' => 'module_distributors'],
                        ],
                    ]],
                ],
                'users' => [
                    'sections' => [[
                        'title' => 'Usuarios',
                        'items' => [
                            ['label' => 'Usuarios', 'route' => 'manager.users', 'permission' => 'users.view'],
                            ['label' => 'Roles y permisos', 'route' => 'manager.roles.index', 'permission' => 'roles.view'],
                        ],
                    ]],
                ],
                'help' => [
                    'sections' => [
                        [
                            'title' => 'Preguntas',
                            'items' => [
                                ['label' => 'Preguntas', 'route' => 'manager.faqs', 'permission' => 'faqs.view'],
                                ['label' => 'Categorias', 'route' => 'manager.faqs.categories', 'permission' => 'faqs.view'],
                            ],
                        ],
                        [
                            'title' => 'Instrucciones',
                            'items' => [
                                ['label' => 'Instrucciones', 'route' => 'manager.instructions', 'permission' => 'instructions.view'],
                                ['label' => 'Categorias', 'route' => 'manager.instructions.categories', 'permission' => 'instructions.view'],
                            ],
                        ],
                        [
                            'title' => 'Documentos',
                            'items' => [
                                ['label' => 'Documentos', 'route' => 'manager.documents', 'permission' => 'documents.view', 'setting' => 'module_documents'],
                            ],
                        ],
                    ],
                ],
                'analytics' => [
                    'sections' => [
                        [
                            'title' => 'Analytics',
                            'items' => [
                                ['label' => 'Dashboard', 'route' => 'manager.analytics'],
                                ['label' => 'Reportes programados', 'route' => 'manager.settings.analytics.schedules.index'],
                                ['label' => 'Configuración', 'route' => 'manager.settings.analytics'],
                            ],
                        ],
                        [
                            'title' => 'SEO',
                            'items' => [
                                ['label' => 'Dashboard', 'route' => 'manager.seo.dashboard'],
                                ['label' => 'Meta tags', 'route' => 'manager.seo.metas.index'],
                                ['label' => 'URLs del sitio', 'route' => 'manager.seo.page-urls.index'],
                                ['label' => 'Sin SEO', 'route' => 'manager.seo.orphans.index'],
                                ['label' => 'Redirecciones', 'route' => 'manager.seo.redirects.index'],
                                ['label' => 'Errores 404', 'route' => 'manager.seo.logs.index'],
                                ['label' => 'URLs sitemap', 'route' => 'manager.seo.static-urls.index'],
                                ['label' => 'Sitemap', 'route' => 'manager.seo.sitemap.index'],
                                ['label' => 'robots.txt', 'route' => 'manager.seo.robots.index'],
                                ['label' => 'llms.txt', 'route' => 'manager.seo.llms.index'],
                                ['label' => 'IndexNow', 'route' => 'manager.seo.indexnow.index'],
                                ['label' => 'Configuracion', 'route' => 'manager.settings.seo.index'],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'sections' => [[
                        'title' => 'Configuración',
                        'items' => [
                            ['label' => 'Configuración', 'route' => 'manager.settings'],
                            ['label' => 'Google Analytics', 'route' => 'manager.settings.analytics'],
                            ['label' => 'Pixel Analytics', 'route' => 'manager.settings.pixel'],
                            ['label' => 'Portal del alumno', 'route' => 'manager.settings.portal'],
                            ['label' => 'Smtp', 'route' => 'manager.settings.emails'],
                            ['label' => 'Seo', 'route' => 'manager.settings.metadata'],
                            ['label' => 'Facturación', 'route' => 'manager.settings.invoices'],
                            ['label' => 'Horario', 'route' => 'manager.settings.hours'],
                            ['label' => 'Mantenimiento', 'route' => 'manager.settings.maintenance'],
                            ['label' => 'Pagos / Wompi', 'route' => 'manager.settings.payments'],
                            ['label' => 'Newsletter', 'route' => 'manager.settings.newsletter'],
                            ['label' => 'Correos entrantes', 'route' => 'manager.settings.incoming-mail'],
                            ['label' => 'Módulos', 'route' => 'manager.settings.modules'],
                            ['label' => 'Registro de actividad', 'route' => 'manager.activity.index', 'permission' => 'activity.view'],
                        ],
                    ]],
                ],
            ],
        ];
    }
}
