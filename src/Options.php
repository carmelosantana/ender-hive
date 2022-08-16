<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use DateTime;
use Westsworld\TimeAgo;

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
    }

    public function metas(): void
    {
        Container::make('post_meta', __('Server', ENDER_HIVE))
            ->add_tab(__('Server Properties', ENDER_HIVE), $this->optionsServerProperties())
            ->add_tab(__('Network', ENDER_HIVE), [
                Field::make('text', 'host_cname', __('Host CNAME', ENDER_HIVE))
                    ->set_default_value(carbon_get_theme_option('host_cname'))
                    ->set_width(50),
                Field::make('text', 'host_ip', __('Host IP', ENDER_HIVE))
                    ->set_default_value(carbon_get_theme_option('host_ip'))
                    ->set_width(50),
            ])
            ->add_tab(__('Setup', ENDER_HIVE), [
                Field::make('separator', 'separator_log', __('Log', ENDER_HIVE)),
                Field::make('text', 'job_history', __('Job History', ENDER_HIVE))
                    ->set_attribute('data-readonly', 'true')
                    ->set_width(50),
                Field::make('separator', 'separator_install', __('Install', ENDER_HIVE)),
                Field::make('select', 'installer_status', __('Installer Status', ENDER_HIVE))
                    ->set_attribute('data-readonly', 'true')
                    ->set_default_value(0)
                    ->set_options([
                        0 => __('', ENDER_HIVE),
                        1 => __('Success', ENDER_HIVE),
                        2 => __('Error', ENDER_HIVE),
                        3 => __('Pending', ENDER_HIVE),
                    ])
                    ->set_width(50),
                Field::make('textarea', 'installer_log', __('Installer Log', ENDER_HIVE))
                    ->set_attribute('data-readonly', 'true'),
            ]);
    }

    public function options(): void
    {
        $ip = gethostbyname(gethostname());
        $hostname = gethostname();

        Container::make('theme_options', __(ENDER_HIVE_TITLE, ENDER_HIVE))
            ->set_icon('dashicons-networking')
            ->add_tab(__('Server Properties Defaults', ENDER_HIVE), $this->optionsServerProperties())
            ->add_tab(__('System Defaults ', ENDER_HIVE), [
                Field::make('select', 'instance_slug_format', __('Instance Slug', ENDER_HIVE))
                    ->set_default_value('post_id')
                    ->set_options([
                        'post_id' => __('Post ID', ENDER_HIVE),
                        'uuid' => __('UUID v4', ENDER_HIVE),
                        'title' => __('Title', ENDER_HIVE),
                    ])
                    ->set_width(50),
                Field::make('text', 'installer_delay', __('Installer Delay', ENDER_HIVE))
                    ->set_attribute('type', 'number')
                    ->set_default_value(0)
                    ->set_help_text(__('Delay the installer for this many minutes.', ENDER_HIVE))
                    ->set_width(50),
                Field::make('text', 'pmmp_install_sh_url', __('PMMP Install URL', ENDER_HIVE))
                    ->set_default_value('https://get.pmmp.io')
                    ->set_width(50),
                Field::make('text', 'path_pmmp', __('PMMP root DIR', ENDER_HIVE))
                    ->set_default_value(WP_CONTENT_DIR . '/uploads/pmmp')
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
                Field::make('radio', 'mc_port_assignment', __('Port Assignment', ENDER_HIVE))
                    ->add_options([
                        'random' => __('Random', ENDER_HIVE),
                        'sequential' => __('Sequential', ENDER_HIVE),
                    ])
                    ->set_default_value('sequential'),
            ]);
    }

    public function optionsServerProperties(): array
    {
        return [
            Field::make('select', 'language', __('Language', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['language'])
                ->set_options($this->languages)
                ->set_width(100),
            Field::make('select', 'gamemode', __('Gamemode', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['gamemode'])
                ->set_options([
                    'survival' => __('Survival', ENDER_HIVE),
                    'creative' => __('Creative', ENDER_HIVE),
                    'adventure' => __('Adventure', ENDER_HIVE),
                    'spectator' => __('Spectator', ENDER_HIVE),
                ])
                ->set_width(50),
            Field::make('select', 'difficulty', __('Difficulty', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['difficulty'])
                ->set_options([
                    0 => __('Peaceful', ENDER_HIVE),
                    1 => __('Easy', ENDER_HIVE),
                    2 => __('Normal', ENDER_HIVE),
                    3 => __('Hard', ENDER_HIVE),
                ])
                ->set_width(50),
            Field::make('text', 'server-name', __('Server Name', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['server-name'])
                ->set_width(50),
            Field::make('text', 'motd', __('MOTD', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['motd'])
                ->set_width(50),
            Field::make('checkbox', 'force-gamemode', __('Force Gamemode', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['force-gamemode'])
                ->set_width(33),
            Field::make('checkbox', 'hardcore', __('Hardcore', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['hardcore'])
                ->set_width(33),
            Field::make('checkbox', 'pvp', __('PvP', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['pvp'])
                ->set_width(33),
            Field::make('text', 'generator-settings', __('Generator Settings', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['generator-settings'])
                ->set_width(50),
            Field::make('text', 'level-name', __('Level Name', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['level-name'])
                ->set_width(50),
            Field::make('text', 'level-seed', __('Level Seed', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['level-seed'])
                ->set_width(50),
            Field::make('select', 'level-type', __('Level Type', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['level-type'])
                ->set_options([
                    'DEFAULT' => __('Default', ENDER_HIVE),
                    'FLAT' => __('Flat', ENDER_HIVE)
                ])
                ->set_width(50),
            Field::make('hidden', 'server-port', __('Server Port', ENDER_HIVE)),
            Field::make('hidden', 'server-portv6', __('Server Port IPv6', ENDER_HIVE)),
            Field::make('checkbox', 'enable-ipv6', __('Enable IPv6', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['enable-ipv6'])
                ->set_width(50),
            Field::make('checkbox', 'white-list', __('White List', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['white-list'])
                ->set_width(50),
            Field::make('text', 'max-players', __('Max Players', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['max-players'])
                ->set_attribute('type', 'number')
                ->set_width(50),
            Field::make('text', 'view-distance', __('View Distance', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['view-distance'])
                ->set_attribute('type', 'number')
                ->set_width(50),
            Field::make('checkbox', 'enable-query', __('Enable Query', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['enable-query'])
                ->set_width(50),
            Field::make('checkbox', 'auto-save', __('Auto Save', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['auto-save'])
                ->set_width(50),
            Field::make('checkbox', 'xbox-auth', __('Xbox Authentication', ENDER_HIVE))
                ->set_default_value(Config::serverProperties()['xbox-auth'])
                ->set_width(50),
        ];
    }

    public static function get(string $option, $default = false, $prefix = '_')
    {
        return carbon_get_theme_option($option);
    }

    public static function set(string $option, $value, $prefix = '_', $autoload = false)
    {
        $option = $prefix . $option;

        return update_option($option, $value, $autoload);
    }

    public static function setMeta($id, string $option, $value, $prefix = '_', $autoload = false)
    {
        // $option = $prefix . $option;

        return carbon_set_post_meta($id, $option, $value);
    }
}
