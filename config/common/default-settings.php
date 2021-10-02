<?php

return [
    // LINKS
    ['name' => 'links.default_type', 'value' => 'direct'],
    ['name' => 'links.enable_type', 'value' => true],
    ['name' => 'links.redirect_time', 'value' => 10],
    ['name' => 'links.geo_targeting', 'value' => true],
    ['name' => 'links.device_targeting', 'value' => true],
    ['name' => 'links.pixels', 'value' => true],
    ['name' => 'links.homepage_creation', 'value' => true],
    ['name' => 'links.homepage_stats', 'value' => true],
    ['name' => 'links.alias_min', 'value' => 5],
    ['name' => 'links.alias_max', 'value' => 10],

    // HOMEPAGE APPEARANCE
    ['name' => 'homepage.appearance', 'value' => json_encode([
        'headerTitle' => 'Create Click-Worthy Links',
        'headerSubtitle' => 'BeLink helps you maximize the impact of every digital initiative with industry-leading features and tools.',
        'headerImage' => 'client/assets/images/landing/landing-bg.svg',
        'headerImageOpacity' => 1,
        'headerOverlayColor1' => null,
        'headerOverlayColor2' => null,
        'footerTitle' => 'The easiest way to get more clicks with custom links.',
        'footerSubtitle' => 'Attract More Clicks Now',
        'footerImage' => 'client/assets/images/landing/landing-bg.svg',
        'actions' => [
            'inputText' => 'Paste a long url',
            'inputButton' => 'Shorten',
            'cta1' => 'Get Started',
            'cta2' => 'Learn More',
        ],
        'primaryFeatures' => [
            [
                'title' => 'Password Protect',
                'subtitle' => 'Set a password to protect your links from unauthorized access.',
                'image' => 'client/assets/images/landing/lock.svg',
            ],
            [
                'title' => 'Retargeting',
                'subtitle' => 'Add retargeting pixels to your links and turn every URL into perfectly targeted ads.',
                'image' => 'client/assets/images/landing/globe.svg',
            ],
            [
                'title' => 'Groups',
                'subtitle' => 'Group links together for easier management and analytics for a group as well as individual links.',
                'image' => 'client/assets/images/landing/campaign.svg',
            ]
        ],
        'secondaryFeatures' => [
            [
                'title' => 'Monitor your link performance.',
                'subtitle' => 'ADVANCED ANALYTICS',
                'description' => 'Full analytics for individual links and link groups, including geo and device information, referrers, browser, ip and more.',
                'image' => 'client/assets/images/landing/stats.png',
            ],
            [
                'title' => 'Manage your links.',
                'subtitle' => 'FULLY-FEATURED DASHBOARD',
                'description' => 'Control everything from the dashboard. Manage your URLs, groups, custom pages, pixels, custom domains and more.',
                'image' => 'client/assets/images/landing/dashboard.png',
            ]
        ]
    ])],

    // menus
    ['name' => 'menus', 'value' => json_encode([
        [
            'name' => 'User Dashboard',
            'position' => 'dashboard-sidebar',
            'items' => [
                ['type' => 'route', 'order' => 1, 'position' => 0, 'activeExact' => true, 'label' => 'Dashboard', 'action' => 'dashboard', 'icon' => 'home'],
                ['type' => 'route', 'order' => 1, 'position' => 1, 'label' => 'Links', 'action' => 'dashboard/links', 'icon' => 'link'],
                ['type' => 'route', 'order' => 1, 'position' => 2, 'label' => 'Link Groups', 'action' => 'dashboard/link-groups', 'icon' => 'dashboard'],
                ['type' => 'route', 'order' => 1, 'position' => 3, 'label' => 'Custom Domains', 'action' => 'dashboard/custom-domains', 'icon' => 'www'],
                ['type' => 'route', 'order' => 1, 'position' => 4, 'label' => 'Link Overlays', 'action' => 'dashboard/link-overlays', 'icon' => 'tooltip'],
                ['type' => 'route', 'order' => 1, 'position' => 5, 'label' => 'Link Pages', 'action' => 'dashboard/link-pages', 'icon' => 'page'],
                ['type' => 'route', 'order' => 1, 'position' => 6, 'label' => 'Tracking Pixels', 'action' => 'dashboard/pixels', 'icon' => 'tracking'],
                ['type' => 'route', 'order' => 1, 'position' => 7, 'label' => 'Workspaces', 'action' => 'dashboard/workspaces', 'icon' => 'people']
            ]
        ],
        [
            'name' => 'footer',
            'position' => 'footer',
            'items' => [
                ['type' => 'link', 'order' => 1, 'position' => 1, 'label' => 'Privacy Policy', 'action' => '/pages/1/privacy-policy'],
                ['type' => 'link', 'order' => 1, 'position' => 2, 'label' => 'Terms of Service', 'action' => '/pages/2/terms-of-service'],
                ['type' => 'link', 'order' => 1, 'position' => 3, 'label' => 'Contact Us', 'action' => '/contact']
            ],
        ]
    ])],

    // custom domains
    ['name' => 'custom_domains.allow_select', 'value' => true],
];
