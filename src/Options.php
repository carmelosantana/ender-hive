<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use CarmeloSantana\EnderHive\Config\Defaults;
use CarmeloSantana\EnderHive\Options\FileDatastore;

class Options
{
    private array $languages = [
        'chs' => '中文 (简体)',
        'deu' => 'Deutsch',
        'ell' => 'Ελληνικά',
        'eng' => 'English',
        'fra' => 'Français',
        'hrv' => 'Hrvatski',
        'jpn' => '日本語',
        'kor' => '한국어',
        'lav' => 'Latviešu',
        'nld' => 'Nederlands',
        'rus' => 'Pyccĸий',
    ];

    public function __construct()
    {
        add_action('carbon_fields_register_fields', [$this, 'metas']);
        add_action('carbon_fields_register_fields', [$this, 'options']);
        add_filter('upload_mimes', [$this, 'uploadMimeTypes']);
    }

    public function banned(): array
    {
        return [
            Field::make('textarea', 'banned_ips_txt', __('Banned IPs', ENDER_HIVE))
                ->set_datastore( new FileDatastore() )
                ->set_rows(10)
                ->set_help_text(__('List of IPs that are not allowed to connect to your server.', ENDER_HIVE)),
            Field::make('textarea', 'banned_players_txt', __('Banned Players', ENDER_HIVE))
                ->set_datastore( new FileDatastore() )
                ->set_rows(10)
                ->set_help_text(__('List of player names that are banned from your server.', ENDER_HIVE)),
        ];
    }

    public function metas(): void
    {
        $this->container_metas = Container::make('post_meta', __('Server', ENDER_HIVE))
            ->add_tab(__('Server Properties', ENDER_HIVE), $this->serverProperties())
            ->add_tab(__('Plugins', ENDER_HIVE), $this->plugins())
            ->add_tab(__('Player Lists', ENDER_HIVE), $this->playerLists())
            ->add_tab(__('Banned', ENDER_HIVE), $this->banned())
            ->add_tab(__('PocketMine', ENDER_HIVE), $this->pocketmine());

        $this->container_metas = Container::make('post_meta', __('Server', ENDER_HIVE))
            ->set_context('side')
            ->add_fields([
                Field::make('text', 'server-port', __('Port', ENDER_HIVE))
                    ->set_attribute('readOnly', true),
                Field::make('text', 'server-portv6', __('Port IPv6', ENDER_HIVE))
                    ->set_attribute('readOnly', true),
                Field::make('text', 'server-lock', __('Lock File', ENDER_HIVE))
                    ->set_attribute('readOnly', true),
            ]);
    }

    public function options(): void
    {
        $this->container_options = Container::make('theme_options', ENDER_HIVE_TITLE)
            ->set_icon('dashicons-networking')
            ->add_tab(__('System', ENDER_HIVE), [
                Field::make('text', 'instance_path', __('Instances Directory', ENDER_HIVE))
                    ->set_default_value(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pmmp')
                    ->set_help_text(__('Path for server instances without trailing slash.', ENDER_HIVE))
                    ->set_width(50),
                Field::make('select', 'instance_slug_format', __('Instance Slug', ENDER_HIVE))
                    ->set_default_value('post_id')
                    ->set_options([
                        'post_id' => __('Post ID', ENDER_HIVE),
                        'uuid' => __('UUID v4', ENDER_HIVE),
                        'title' => __('Title', ENDER_HIVE),
                    ])
                    ->set_width(50),
                Field::make('text', 'pmmp_install_sh_url', __('PMMP Install URL', ENDER_HIVE))
                    ->set_default_value('https://get.pmmp.io')
                    ->set_width(50),
                Field::make('text', 'installer_delay', __('Installer Delay', ENDER_HIVE))
                    ->set_attribute('type', 'number')
                    ->set_default_value(0)
                    ->set_help_text(__('Delay the installer for this many minutes.', ENDER_HIVE))
                    ->set_width(50),
            ])
            ->add_tab(__('Network', ENDER_HIVE), [
                Field::make('complex', 'available_ports', __('Available Ports', ENDER_HIVE))
                    ->add_fields('range', [
                        Field::make('text', 'start', __('Start', ENDER_HIVE))
                            ->set_attribute('min', Network::PORT_MIN)
                            ->set_attribute('max', Network::PORT_MAX)
                            ->set_attribute('type', 'number')
                            ->set_width(50),
                        Field::make('text', 'end', __('End', ENDER_HIVE))
                            ->set_attribute('min', Network::PORT_MIN)
                            ->set_attribute('max', Network::PORT_MAX)
                            ->set_attribute('type', 'number')
                            ->set_width(50),
                    ]),
                Field::make('radio', 'port_assignment', __('Port Assignment', ENDER_HIVE))
                    ->add_options([
                        'random' => __('Random', ENDER_HIVE),
                        'sequential' => __('Sequential', ENDER_HIVE),
                    ])
                    ->set_default_value('sequential'),
            ]);
    }

    public function playerLists(): array
    {
        return [
            Field::make('textarea', 'ops_txt', __('Operators', ENDER_HIVE))
                ->set_datastore( new FileDatastore() )
                ->set_rows(10)
                ->set_help_text(__('Gives the specified players operator status.<br><strong>ops.txt</strong>', ENDER_HIVE)),
            Field::make('textarea', 'white_list_txt', __('White List', ENDER_HIVE))
                ->set_datastore( new FileDatastore() )
                ->set_rows(10)
                ->set_help_text(__('List of players allowed to use this server.<br><strong>white-list.txt</strong>', ENDER_HIVE)),
        ];
    }

    public function plugins(): array
    {
        return [
            Field::make('media_gallery', 'plugin_phar', __('Upload .phar', ENDER_HIVE))
                ->set_type('phar')
                ->set_duplicates_allowed(false),
            Field::make('textarea', 'plugin_list_yml', __('Plugins', ENDER_HIVE))
                ->set_datastore( new FileDatastore() )
                ->set_rows(10)
                ->set_help_text(__('Allows you to control which plugins are loaded on your server.', ENDER_HIVE)),
        ];
    }

    public function pocketmine(): array
    {
        return [
            Field::make('textarea', 'pocketmine_yml', __('PocketMine Config', ENDER_HIVE))
                ->set_datastore( new FileDatastore() )
                ->set_rows(10)
                ->set_help_text(__('Main configuration file for PocketMine-MP.', ENDER_HIVE)),
        ];
    }

    public function serverProperties(): array
    {
        return [
            Field::make('select', 'language', __('Language', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['language'])
                ->set_options($this->languages)
                ->set_width(100),
            Field::make('select', 'gamemode', __('Gamemode', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['gamemode'])
                ->set_options([
                    'survival' => __('Survival', ENDER_HIVE),
                    'creative' => __('Creative', ENDER_HIVE),
                    'adventure' => __('Adventure', ENDER_HIVE),
                    'spectator' => __('Spectator', ENDER_HIVE),
                ])
                ->set_width(50),
            Field::make('select', 'difficulty', __('Difficulty', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['difficulty'])
                ->set_options([
                    0 => __('Peaceful', ENDER_HIVE),
                    1 => __('Easy', ENDER_HIVE),
                    2 => __('Normal', ENDER_HIVE),
                    3 => __('Hard', ENDER_HIVE),
                ])
                ->set_width(50),
            Field::make('text', 'server-name', __('Server Name', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['server-name'])
                ->set_width(50),
            Field::make('text', 'motd', __('MOTD', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['motd'])
                ->set_width(50),
            Field::make('checkbox', 'force-gamemode', __('Force Gamemode', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['force-gamemode'])
                ->set_width(33),
            Field::make('checkbox', 'hardcore', __('Hardcore', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['hardcore'])
                ->set_width(33),
            Field::make('checkbox', 'pvp', __('PvP', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['pvp'])
                ->set_width(33),
            Field::make('text', 'generator-settings', __('Generator Settings', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['generator-settings'])
                ->set_width(50),
            Field::make('text', 'level-name', __('Level Name', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['level-name'])
                ->set_width(50),
            Field::make('text', 'level-seed', __('Level Seed', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['level-seed'])
                ->set_width(50),
            Field::make('select', 'level-type', __('Level Type', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['level-type'])
                ->set_options([
                    'DEFAULT' => __('Default', ENDER_HIVE),
                    'FLAT' => __('Flat', ENDER_HIVE)
                ])
                ->set_width(50),
            Field::make('checkbox', 'enable-ipv6', __('Enable IPv6', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['enable-ipv6'])
                ->set_width(50),
            Field::make('checkbox', 'white-list', __('White List', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['white-list'])
                ->set_width(50),
            Field::make('text', 'max-players', __('Max Players', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['max-players'])
                ->set_attribute('type', 'number')
                ->set_width(50),
            Field::make('text', 'view-distance', __('View Distance', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['view-distance'])
                ->set_attribute('type', 'number')
                ->set_width(50),
            Field::make('checkbox', 'enable-query', __('Enable Query', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['enable-query'])
                ->set_width(50),
            Field::make('checkbox', 'auto-save', __('Auto Save', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['auto-save'])
                ->set_width(50),
            Field::make('checkbox', 'xbox-auth', __('Xbox Authentication', ENDER_HIVE))
                ->set_default_value(Defaults::serverProperties()['xbox-auth'])
                ->set_width(50),
        ];
    }

    public function uploadMimeTypes($wp_get_mime_types)
    {
        $wp_get_mime_types['phar'] = 'application/x-phar';
        return $wp_get_mime_types;
    }

    public static function get(string $option)
    {
        return carbon_get_theme_option($option);
    }

    public static function getMeta(int $id, string $option)
    {
        return carbon_get_post_meta($id, $option);
    }

    public static function set(string $option, $value)
    {
        return carbon_set_theme_option($option, $value);
    }

    public static function setMeta(int $id, string $option, $value)
    {
        return carbon_set_post_meta($id, $option, $value);
    }
}
