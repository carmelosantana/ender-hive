<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use Ramsey\Uuid\Uuid;
use \WP_Filesystem_Direct;

class Instance
{
    const INSTALL = 'EnderHive\Instance\install';

    private object $command;

    public function __construct()
    {
        // WordPress hooks
        add_action('init', [$this, 'postType']);
        add_action('save_post_instance', [$this, 'create'], 10, 2);
        add_action('before_delete_post', [$this, 'delete']);
        add_action('wp_trash_post', [$this, 'trash']);

        // Available actions
        add_action(self::INSTALL, [$this, 'install']);
    }

    public function create(int $post_id, \WP_Post $post): void
    {
        // Bail out if this is an autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Only run for instance post type.
        if ($post->post_type !== 'instance') {
            return;
        }

        // Stops redirect loop
        remove_action('save_post_instance', [$this, __FUNCTION__]);

        // Update with instance ID if none exists.
        if ($post->post_name === '') {
            switch (Options::get('instance_slug_format')) {
                case 'uuid':
                    $post->post_name = Uuid::uuid4()->toString();
                    break;
                case 'post_title':
                    $post->post_name = $post->post_title;
                    break;
                default:
                    $post->post_name = $post->ID;
                    break;
            }
            wp_update_post([
                'ID' => $post->ID,
                'post_name' => $post->post_name,
            ]);
        }

        // re-hook this function
        add_action('save_post_instance', [$this, __FUNCTION__], 200, 2);

        // Only fire installer on first save.
        $status = carbon_get_post_meta($post->ID, 'installer_status');
        if (isset($_POST['hidden_post_status']) and $_POST['hidden_post_status'] == 'draft' and !$status) {
            // Schedule task.
            $timestamp = Options::get('installer_delay') == 0 ? time() : strtotime('+' . Options::get('installer_delay') . ' minute');

            // Schedule the event
            $args = [$post_id];
            $job_id = as_schedule_single_action($timestamp, self::INSTALL, $args);

            // Logging
            carbon_set_post_meta($post->ID, 'installer_status', 3);
        }
    }

    public function postType(): void
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

    public function delete(int $post_id): void
    {
        $server = new Server($post_id);
        $server->stopWait();

        if (file_exists($server->getPath())) {
            include_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
            include_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

            if (class_exists('WP_Filesystem_Direct')) {
                (new \WP_Filesystem_Direct([]))->rmdir($server->getPath(), true);
            }
        }
    }

    public function trash(int $post_id): void
    {
        $server = new Server($post_id);
        $server->stop();
    }

    public static function getPath($id): string
    {
        $url = Options::get('path_pmmp') . DIRECTORY_SEPARATOR . Jargon::INSTANCES . DIRECTORY_SEPARATOR . (string) $id;

        return $url;
    }

    public static function install($post_id): void
    {
        // Get the post
        $post = get_post($post_id);

        // Run PMMP installer
        $installer = new Installer($post);
        $installer->new();

        // Start server
        $server = new Server($post->ID);
        $server->start();
    }
}
