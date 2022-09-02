<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Instance;

class PostType
{
    public function __construct()
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_post_type('instance', [
            'labels' => [
                'name' => __('Instances', ENDER_HIVE),
                'singular_name' => __('Instance', ENDER_HIVE),
                'add_new_item' => __('Add New Instance', ENDER_HIVE),
                'edit_item' => __('Edit Instance', ENDER_HIVE),
                'new_item' => __('New Instance', ENDER_HIVE),
                'view_item' => __('View Instance', ENDER_HIVE),
                'search_items' => __('Search Instances', ENDER_HIVE),
                'not_found' => __('No Instances found', ENDER_HIVE),
                'not_found_in_trash' => __('No Instances found in trash', ENDER_HIVE),
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => [
                'slug' => 'instance',
            ],
            'supports' => [
                'title',
                'thumbnail',
                'revisions',
                'custom-fields',
            ],
            'menu_icon' => 'dashicons-games',
        ]);
    }
}
