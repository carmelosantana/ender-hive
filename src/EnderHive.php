<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

class EnderHive
{
    public function __construct()
    {
        // Setup Carbon fields
        add_action('after_setup_theme', [$this, 'initCarbonFields'], 10);

        // Primary loader
        add_action('after_setup_theme', [$this, 'start'], 11);

        // Styles
        add_action('admin_enqueue_scripts', [$this, 'enqueueStyles']);

        // Load the rest API
        add_action('rest_api_init', [$this, 'initRestApi']);
    }

    public function enqueueStyles(): void
    {
        wp_enqueue_style(ENDER_HIVE . '-admin', plugins_url('/assets/css/admin.css', ENDER_HIVE_FILE_PATH));
    }

    public function initCarbonFields(): void
    {
        // Must define to fix JS loading bugs
        define('Carbon_Fields\URL', ENDER_HIVE_DIR_URL . 'vendor/htmlburger/carbon-fields');

        // Load Carbon Fields
        \Carbon_Fields\Carbon_Fields::boot();
    }

    public function initRestApi(): void
    {
        (new API\Instance())->register_routes();
    }

    public function start(): void
    {
        // Instance support
        new Instance();

        // Network tools
        new Network();

        // Plugin and meta options
        new Options();
    }
}
