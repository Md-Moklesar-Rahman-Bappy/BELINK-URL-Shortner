<?php

return [
    'roles' => [
        [
            'default' => true,
            'name' => 'users',
            'extends' => 'users',
            'permissions' => [
                // API
                'api.access',

                // LINKS
                'links.view',
                [
                    'name' => 'links.create',
                    'restrictions' => [
                        [
                            'name' => 'count',
                            'value' => 20,
                        ],
                        [
                            'name' => 'click_count',
                            'value' => 5000,
                        ]
                    ]
                ],

                // LINK GROUPS
                [
                    'name' => 'link_groups.create',
                    'restrictions' => [
                        [
                            'name' => 'count',
                            'value' => 5,
                        ],
                    ]
                ],

                // CUSTOM DOMAINS
                [
                    'name' => 'custom_domains.create',
                    'restrictions' => [
                        [
                            'name' => 'count',
                            'value' => 5,
                        ],
                    ]
                ],

                // LINK OVERLAYS
                [
                    'name' => 'link_overlays.create',
                    'restrictions' => [
                        [
                            'name' => 'count',
                            'value' => 5,
                        ],
                    ]
                ],

                // LINK PAGES
                [
                    'name' => 'link_pages.create',
                    'restrictions' => [
                        [
                            'name' => 'count',
                            'value' => 5,
                        ],
                    ]
                ],

                // TRACKING PIXELS
                [
                    'name' => 'tracking_pixels.create',
                    'restrictions' => [
                        [
                            'name' => 'count',
                            'value' => 5,
                        ],
                    ]
                ],

                // WORKSPACES
                [
                    'name' => 'workspaces.create',
                    'restrictions' => [
                        [
                            'name' => 'count',
                            'value' => 3,
                        ],
                        [
                            'name' => 'member_count',
                            'value' => 5,
                        ],
                    ]
                ],
            ]
        ],

        [
            'name' => 'guests',
            'guests' => true,
            'extends' => 'guests',
            'permissions' => [
                'links.view',
                'links.create',
            ]
        ],
    ],
    'all' => [
        'api' => [
            [
                'name' => 'api.access',
                'description' => 'Required in order for users to be able to use the API.',
            ],
        ],
        'links' => [
            'links.view',
            [
                'name' => 'links.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'urls'])
                    ],
                    [
                        'name' => 'click_count',
                        'type' => 'number',
                        'description' => 'Maximum number of clicks/visits allowed per month for all user urls. Leave empty for unlimited.'
                    ]
                ]
            ],
            'links.update',
            'links.delete',
        ],
        'link_overlays' => [
            'link_overlays.view',
            [
                'name' => 'link_overlays.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'overlays'])
                    ]
                ]
            ],
            'link_overlays.update',
            'link_overlays.delete',
        ],
        'link_groups' => [
            'link_groups.view',
            [
                'name' => 'link_groups.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'groups'])
                    ]
                ]
            ],
            'link_groups.update',
            'link_groups.delete',
        ],
        'tracking_pixels' => [
            'tracking_pixels.view',
            [
                'name' => 'tracking_pixels.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'pixels'])
                    ]
                ]
            ],
            'tracking_pixels.update',
            'tracking_pixels.delete',
        ],
        'link_pages' => [
            'link_pages.view',
            [
                'name' => 'link_pages.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'pixels'])
                    ]
                ]
            ],
            'link_pages.update',
            'link_pages.delete',
        ],
        'workspaces' => [
            'workspaces.view',
            [
                'name' => 'workspaces.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'workspaces'])
                    ],
                    [
                        'name' => 'member_count',
                        'type' => 'number',
                        'description' => 'Maximum number of members workspace is allowed to have.',
                    ]
                ]
            ],
            'workspaces.update',
            'workspaces.delete'
        ],
        'workspace_members' => [
            [
                'name' => 'workspace_members.invite',
                'display_name' => 'Invite Members',
                'type' => 'workspace',
                'description' => 'Allow user to invite new members into a workspace.',
            ],
            [
                'name' => 'workspace_members.update',
                'display_name' => 'Update Members',
                'type' => 'workspace',
                'description' => 'Allow user to change role of other members.',
            ],
            [
                'name' => 'workspace_members.delete',
                'display_name' => 'Delete Members',
                'type' => 'workspace',
                'description' => 'Allow user to remove members from workspace.',
            ]
        ]
    ],
];
