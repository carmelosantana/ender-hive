<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Instance;

use CarmeloSantana\EnderHive\Host\Server;

class Actions
{
    public function __construct()
    {
        add_action('save_post_instance', [$this, 'create'], 10, 2);
        add_action('before_delete_post', [$this, 'delete']);
        add_action('wp_trash_post', [$this, 'trash']);
        add_action('Instance\install', [$this, 'install'], 10, 2);
    }

    /**
     * Hooks on post save. If post is new we start the installation process.
     *
     * @param  mixed $post_id
     * @param  mixed $post
     * @return void
     */
    public function create(int $post_id): void
    {
        // Bail out if this is an autosave.
        if (defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) {
            return;
        }

        // Only run for instance post type.
        if (get_post_type($post_id) != 'instance') {
            return;
        }

        // TODO: API may require another check hidden_post_status is for admin edit.php.
        if (isset($_POST['hidden_post_status']) and $_POST['hidden_post_status'] == 'draft' and !$status) {
            // Schedule the event
            $args = [
                $post_id,
                wp_create_nonce('install' . $post_id)
            ];
            as_schedule_single_action(time(), 'Instance\install', $args);
        }
    }

    /**
     * Attempts to stop server with wait to ensure it is stopped.
     * Deletes all files under the instance directory.
     *
     * @param  mixed $post_id
     * @return void
     */
    public function delete(int $post_id): void
    {
        $this->server = new Server($post_id);
        $this->server->stopWait();

        // TODO: Move to Tools/Utils::rmdir
        if (file_exists($this->server->getPath())) {
            include_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wp-filesystem-base.php';
            include_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wp-filesystem-direct.php';

            if (class_exists('WP_Filesystem_Direct')) {
                (new \WP_Filesystem_Direct([]))->rmdir($this->server->getPath(), true);
            }
        }
    }

    /**
     * Stops server in preparation for file removal.
     *
     * @param  mixed $post_id
     * @return void
     */
    public function trash(int $post_id): void
    {
        $this->server = new Server($post_id);
        $this->server->stop();
    }

    /**
     * Installs server via scheduled action. Requires nonce.
     *
     * @param  mixed $post_id
     * @param  mixed $nonce
     * @return void
     */
    public static function install($post_id, $nonce): void
    {
        if (!wp_verify_nonce($nonce, 'install' . $post_id)) {
            new \WP_Error('forbidden', __('Authentication failed.', ENDER_HIVE));
        }

        $server = new Server($post_id);
        $server->install();
        $server->start();
    }
}
