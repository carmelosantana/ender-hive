<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Options;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use CarmeloSantana\EnderHive\Host\PocketMineMP\Server as PocketMineMP;
use CarmeloSantana\EnderHive\Tools\Network;
use CarmeloSantana\EnderHive\Tools\Utils;

class Fields
{
    public array $languages = [
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
        add_action('carbon_fields_post_meta_container_saved', [$this, 'writeServerProperties']);
    }

    public function banned(): array
    {
        return [
            Field::make('textarea', 'banned_ips_txt', __('Banned IPs', ENDER_HIVE))
                ->set_datastore(new FileDatastore())
                ->set_rows(10)
                ->set_help_text(__('List of IPs that are not allowed to connect to your server.', ENDER_HIVE)),
            Field::make('textarea', 'banned_players_txt', __('Banned Players', ENDER_HIVE))
                ->set_datastore(new FileDatastore())
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
            ->add_tab(__('PocketMine', ENDER_HIVE), $this->pocketmine())
            ->add_fields([
                Field::make('hidden', 'server_type', __('Server Type', ENDER_HIVE))
                    ->set_default_value('pocketmine-mp'),
            ]);

        $this->container_metas = Container::make('post_meta', __('System', ENDER_HIVE))
            ->set_context('side')
            ->add_fields([
                Field::make('text', 'last_known_state', __('Last Known State', ENDER_HIVE))
                    ->set_attribute('readOnly', true),
                Field::make('checkbox', 'autorestart', __('Auto Restart', ENDER_HIVE))
                    ->set_default_value(true),
                Field::make('text', 'server-port', __('IP4', ENDER_HIVE))
                    ->set_attribute('readOnly', true)
                    ->set_width(50),
                Field::make('text', 'server-portv6', __('IP6', ENDER_HIVE))
                    ->set_attribute('readOnly', true)
                    ->set_width(50),
            ]);
    }

    public function options(): void
    {
        $this->container_options = Container::make('theme_options', ENDER_HIVE_TITLE)
            ->set_icon('dashicons-networking')
            ->add_tab(__('System', ENDER_HIVE), [
                Field::make('separator', 'servers_separator', __('Servers', ENDER_HIVE)),
                Field::make('checkbox', 'server_type_pmmp', __('PocketMine-MP', ENDER_HIVE))
                    ->set_help_text(__('Enable Minecraft server type.', ENDER_HIVE))
                    ->set_default_value(true)
                    ->set_width(100),
                Field::make('text', 'pmmp_install_sh_url', __('Pocketmine-MP Install URL', ENDER_HIVE))
                    ->set_default_value('https://get.pmmp.io')
                    ->set_conditional_logic([
                        [
                            'field' => 'server_type_pmmp',
                            'value' => true,
                        ],
                    ])
                    ->set_width(100),
                Field::make('separator', 'instances_separator', __('Instances', ENDER_HIVE)),
                Field::make('text', 'instance_path', __('Instances Directory', ENDER_HIVE))
                    ->set_default_value(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pmmp')
                    ->set_help_text(__('Path for server instances without trailing slash.', ENDER_HIVE))
                    ->set_width(100),
                Field::make('separator', 'action_scheduler_separator', __('Action Scheduler', ENDER_HIVE)),
                Field::make('select', 'action_scheduler_retention_period', __('Retention Period', ENDER_HIVE))
                    ->set_default_value('weekly')
                    ->set_options([
                        'hourly' => __('Hourly', ENDER_HIVE),
                        'daily' => __('Daily', ENDER_HIVE),
                        'weekly' => __('Weekly', ENDER_HIVE),
                        'monthly' => __('Monthly', ENDER_HIVE),
                    ])
                    ->set_width(50),
                Field::make('select', 'action_scheduler_restart_interval', __('Restart Interval', ENDER_HIVE))
                    ->set_default_value('weekly')
                    ->set_options([
                        'minute' => __('Minute', ENDER_HIVE),
                        'survival' => __('Hourly', ENDER_HIVE),
                        'creative' => __('Daily', ENDER_HIVE),
                        'weekly' => __('Weekly', ENDER_HIVE),
                        'monthly' => __('Monthly', ENDER_HIVE),
                    ])
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
                ->set_datastore(new FileDatastore())
                ->set_rows(10)
                ->set_help_text(__('Gives the specified players operator status.<br><strong>ops.txt</strong>', ENDER_HIVE)),
            Field::make('textarea', 'white_list_txt', __('White List', ENDER_HIVE))
                ->set_datastore(new FileDatastore())
                ->set_rows(10)
                ->set_help_text(__('List of players allowed to use this server.<br><strong>white-list.txt</strong>', ENDER_HIVE)),
        ];
    }

    public function plugins(): array
    {
        return [
            Field::make('textarea', 'plugin_list_yml', __('Plugins', ENDER_HIVE))
                ->set_datastore(new FileDatastore())
                ->set_default_value(PocketMineMP::getAsset('plugin_list.yml'))
                ->set_rows(10)
                ->set_help_text(__('Allows you to control which plugins are loaded on your server.', ENDER_HIVE)),
        ];
    }

    public function pocketmine(): array
    {
        return [
            Field::make('textarea', 'pocketmine_yml', __('PocketMine Config', ENDER_HIVE))
                ->set_datastore(new FileDatastore())
                ->set_default_value(PocketMineMP::getAsset('pocketmine.yml'))
                ->set_rows(10)
                ->set_help_text(__('Main configuration file for PocketMine-MP.', ENDER_HIVE)),
        ];
    }

    public function serverProperties(): array
    {
        return [
            Field::make('select', 'language', __('Language', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['language'])
                ->set_options($this->languages)
                ->set_width(100),
            Field::make('select', 'gamemode', __('Gamemode', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['gamemode'])
                ->set_options([
                    'survival' => __('Survival', ENDER_HIVE),
                    'creative' => __('Creative', ENDER_HIVE),
                    'adventure' => __('Adventure', ENDER_HIVE),
                    'spectator' => __('Spectator', ENDER_HIVE),
                ])
                ->set_width(50),
            Field::make('select', 'difficulty', __('Difficulty', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['difficulty'])
                ->set_options([
                    0 => __('Peaceful', ENDER_HIVE),
                    1 => __('Easy', ENDER_HIVE),
                    2 => __('Normal', ENDER_HIVE),
                    3 => __('Hard', ENDER_HIVE),
                ])
                ->set_width(50),
            Field::make('text', 'server-name', __('Server Name', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['server-name'])
                ->set_width(50),
            Field::make('text', 'motd', __('MOTD', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['motd'])
                ->set_width(50),
            Field::make('checkbox', 'force-gamemode', __('Force Gamemode', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['force-gamemode'])
                ->set_width(33),
            Field::make('checkbox', 'hardcore', __('Hardcore', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['hardcore'])
                ->set_width(33),
            Field::make('checkbox', 'pvp', __('PvP', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['pvp'])
                ->set_width(33),
            Field::make('text', 'generator-settings', __('Generator Settings', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['generator-settings'])
                ->set_width(50),
            Field::make('text', 'level-name', __('Level Name', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['level-name'])
                ->set_width(50),
            Field::make('text', 'level-seed', __('Level Seed', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['level-seed'])
                ->set_width(50),
            Field::make('select', 'level-type', __('Level Type', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['level-type'])
                ->set_options([
                    'DEFAULT' => __('Default', ENDER_HIVE),
                    'FLAT' => __('Flat', ENDER_HIVE)
                ])
                ->set_width(50),
            Field::make('checkbox', 'enable-ipv6', __('Enable IPv6', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['enable-ipv6'])
                ->set_width(50),
            Field::make('checkbox', 'white-list', __('White List', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['white-list'])
                ->set_width(50),
            Field::make('text', 'max-players', __('Max Players', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['max-players'])
                ->set_attribute('type', 'number')
                ->set_width(50),
            Field::make('text', 'view-distance', __('View Distance', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['view-distance'])
                ->set_attribute('type', 'number')
                ->set_width(50),
            Field::make('checkbox', 'enable-query', __('Enable Query', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['enable-query'])
                ->set_width(50),
            Field::make('checkbox', 'auto-save', __('Auto Save', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['auto-save'])
                ->set_width(50),
            Field::make('checkbox', 'xbox-auth', __('Xbox Authentication', ENDER_HIVE))
                ->set_default_value(PocketMineMP::defaultServerProperties()['xbox-auth'])
                ->set_width(50),
        ];
    }

    /**
     * Hooks on post meta save via Carbon Fields.
     * 
     *
     * @param mixed $post_id
     * @return void
     */
    public static function writeServerProperties(int $post_id): void
    {
        // Setup server.
        $server = new PocketMineMP($post_id);
        $update = $server::prepServerProperties($post_id);

        // Setup file.
        $filename = 'server.properties';
        $path = $server->getPath($filename);

        // Get current file
        $current_file = file_exists($path) ? file_get_contents($path) : null;

        // Compare files and update if needed.
        if ($current_file and strcmp(Utils::removeFirstTwoLines($current_file), Utils::removeFirstTwoLines($update))) {
            $server->stopWait();
            file_put_contents($path, $update);
            $server->start();
        }
    }
}
