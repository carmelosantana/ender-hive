<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Instance;

use CarmeloSantana\EnderHive\Host\Server;
use CarmeloSantana\EnderHive\Host\Status;

class Actions
{
    public function __construct()
    {
        add_action('save_post_instance', [$this, 'create'], 10, 2);
        add_action('before_delete_post', [$this, 'delete']);
        add_action('init', [$this, 'scheduleActions']);
        add_action('wp_trash_post', [$this, 'trash']);
        add_action('Instance\autorestart', [$this, 'autoRestart']);
        add_action('Instance\install', [$this, 'install']);
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
        $status = get_post_meta($post_id, '_install_status', true);
        if (isset($_POST['hidden_post_status']) and $_POST['hidden_post_status'] == 'draft' and !$status) {
            // Schedule the event
            $args = [
                $post_id,
            ];
            as_enqueue_async_action('Instance\install', $args);
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
     * Handles action scheduling on init.
     *
     * @return void
     */
    public function scheduleActions(): void
    {
        if (!as_has_scheduled_action('Instance\autorestart')) {
            as_schedule_recurring_action(time(), 60, 'Instance\autorestart');
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
     * Restart server if it was last known as running but no longer online.
     *
     * @param int $post_id
     * @return void
     */
    public static function autoRestart(): void
    {
        // Use WP_Query to get all instances
        $query = new \WP_Query([
            'post_type' => 'instance',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'autorestart',
                    'value' => 'yes',
                ],
            ],

        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $server = new Server(get_the_ID());
                if (!$server->isRunning() and $server->getLastKnownState() == Status::OK) {
                    $server->start();
                }
            }
        }
    }

    /**
     * Installs server via scheduled action.
     *
     * @param  mixed $post_id
     * @return void
     */
    public static function install($post_id): void
    {
        $server = new Server($post_id);
        $server->install();
        $server->start();
    }
}
