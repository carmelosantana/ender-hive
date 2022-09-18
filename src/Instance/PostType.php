<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Instance;

use CarmeloSantana\EnderHive\Host\Server;
use CarmeloSantana\EnderHive\Tools\Utils;
use stdClass;

class PostType
{
    public object $server;

    public function __construct()
    {
        $this->server = new stdClass();

        add_action('admin_post_instance', [$this, 'actions']);
        add_action('init', [$this, 'register']);
        add_action('manage_instance_posts_custom_column', [$this, 'columnValues'], 10, 2);

        add_filter('post_row_actions', [$this, 'columnRow'], 10, 2);
        add_filter('manage_edit-instance_columns', [$this, 'columnKeys']);
        add_filter('manage_edit-instance_sortable_columns', [$this, 'columnSortable']);
        add_filter('request', [$this, 'columnSort']);
    }

    public function actions()
    {
        // TODO: Add nonce check
        if (isset($_GET['action']) and $_GET['action'] === 'instance') {
            if (isset($_GET['instance_id']) and isset($_GET['instance_action'])) {

                $instance_id = (int) sanitize_text_field($_GET['instance_id']);
                $this->server($instance_id);

                switch ($_GET['instance_action']) {
                    case 'start':
                        $this->server->startWait();
                        break;
                    case 'stop':
                        $this->server->stopWait();
                        break;
                    case 'restart':
                        $this->server->restart();
                        break;
                }
            }

            if (wp_get_referer()) {
                wp_safe_redirect(wp_get_referer());
            } else {
                wp_safe_redirect(admin_url('edit.php?post_type=instance'));
            }
        }
    }

    public function columnRow($actions, $post)
    {
        if (get_post_type($post) != 'instance') {
            return $actions;
        }

        $this->server($post->ID);

        if (!$this->server->isRunning()) {
            $actions['start'] = '<a href="' . admin_url('admin-post.php?action=instance&instance_action=start&instance_id=' . $post->ID) . '">' . __('Start', ENDER_HIVE) . '</a>';
            $actions['stop'] = __('Stop', ENDER_HIVE);
            $actions['restart'] = __('Restart', ENDER_HIVE);
        } else {
            $actions['start'] = __('Start', ENDER_HIVE);
            $actions['stop'] = '<a href="' . admin_url('admin-post.php?action=instance&instance_action=stop&instance_id=' . $post->ID) . '">' . __('Stop', ENDER_HIVE) . '</a>';
            $actions['restart'] = '<a href="' . admin_url('admin-post.php?action=instance&instance_action=restart&instance_id=' . $post->ID) . '">' . __('Restart', ENDER_HIVE) . '</a>';
        }

        return $actions;
    }

    public function columnKeys($columns): array
    {
        $date = $columns['date'];
        unset($columns['date']);

        $columns['status'] = 'Status';
        $columns['ports'] = 'Ports';
        $columns['players'] = 'Players';
        $columns['date'] = $date;

        return $columns;
    }

    public function columnSortable($columns): array
    {
        $columns['status'] = 'Status';

        return $columns;
    }

    public function columnValues($column, $post_id): void
    {
        $this->server($post_id);

        switch ($column) {
            case 'players':
                if ($this->server->isRunning()) {
                    echo $this->server->query()['players'] . '/' . $this->server->query()['maxplayers'];
                } else {
                    echo '<span class="dashicons dashicons-minus"></span>';
                }
                break;

            case 'ports':
                echo '<span class="label">IPv4</span>';
                echo '<input type="text" value="' . $this->server->getPortIp4() . '" disabled>';
                echo '<br>';
                echo '<span class="label">IPv6</span>';
                if ($this->server->isIp6Enabled()) {
                    echo '<input type="text" value="' . $this->server->getPortIp6() . '" disabled>';
                } else {
                    echo '<span class="dashicons dashicons-no"></span>' . __('Disabled', ENDER_HIVE);
                }
                break;

            case 'ipv6':

                break;

            case 'status':
                $status = get_post_meta($post_id, '_last_known_state', true);
                switch ($status) {
                    case 200:
                        echo '<span class="dashicons dashicons-yes-alt status-200"></span>' . __('Online', ENDER_HIVE);
                        break;

                    default:
                        echo '<span class="dashicons dashicons-dismiss status-204"></span>' . __('Offline', ENDER_HIVE);
                        break;
                }
                break;
        }
    }

    public function columnSort($vars)
    {
        if (array_key_exists('orderby', $vars)) {
            switch ($vars['orderby']) {
                case 'status':
                    $vars = array_merge($vars, [
                        'meta_key' => '__last_known_state',
                        'orderby' => 'meta_value_num'
                    ]);
                    break;
            }
        }
        return $vars;
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

    public function server(int $post_id, bool $refresh = false): Server
    {
        // Only create new server instance if not initialized or the post ID has changed.
        // If refresh is true, the server instance should be refreshed.
        if (!$this->server instanceof Server or $this->server->getId() != $post_id or $refresh) {
            $this->server = new Server($post_id);
        }

        return $this->server;
    }
}
