<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\Config;

class Defaults
{
  public static function files(): array
  {
    return [
      'htaccess' => [
        'filename' => '.htaccess',
        'callback' => [__CLASS__, 'htaccess'],
      ],
      'banned_ips_txt' => [
        'filename' => 'banned-ips',
        'extension' => 'txt',
      ],
      'banned_players_txt' => [
        'filename' => 'banned-players',
        'extension' => 'txt',
      ],
      'ops_txt' => [
        'filename' => 'ops',
        'extension' => 'txt',
      ],
      'plugin_list_yml' => [
        'filename' => 'plugin_list',
        'extension' => 'yml',
        'callback' => [__CLASS__, 'pluginList'],
      ],
      'pocketmine_yml' => [
        'filename' => 'pocketmine',
        'extension' => 'yml',
        'callback' => [__CLASS__, 'pocketmine'],
      ],
      'server_properties' => [
        'filename' => 'server',
        'extension' => 'properties',
        'callback' => [__CLASS__, 'serverProperties'],
      ],
      'white_list_xml' => [
        'filename' => 'white-list',
        'extension' => 'txt',
      ],
    ];
  }

  public static function htaccess(): string
  {
    return <<<HTACCESS
order allow,deny
deny from all
HTACCESS;
  }

  public static function pluginList(): array
  {
    return [
      // blacklist: Only plugins which ARE NOT listed will load.
      // whitelist: Only plugins which ARE listed will load.
      'mode' => 'blacklist',
      'plugins' => [],
    ];
  }

  public static function pocketmine(): array
  {
    return [
      'settings' => [
        'force-language' => false,
        'shutdown-message' => 'Server closed',
        'query-plugins' => true,
        'enable-profiling' => false,
        'profile-report-trigger' => 20,
        'async-workers' => 'auto',
        'enable-dev-builds' => false,
      ],
      'memory' => [
        'global-limit' => 0,
        'main-limit' => 0,
        'main-hard-limit' => 1024,
        'async-worker-hard-limit' => 256,
        'check-rate' => 20,
        'continuous-trigger' => true,
        'continuous-trigger-rate' => 30,
        'garbage-collection' => [
          'period' => 36000,
          'collect-async-worker' => true,
          'low-memory-trigger' => true,
        ],
        'memory-dump' => [
          'dump-async-worker' => true,
        ],
        'max-chunks' => [
          'chunk-radius' => 4,
          'trigger-chunk-collect' => true,
        ],
        'world-caches' => [
          'disable-chunk-cache' => true,
          'low-memory-trigger' => true,
        ],
      ],
      'network' => [
        'batch-threshold' => 256,
        'compression-level' => 6,
        'async-compression' => false,
        'upnp-forwarding' => false,
        'max-mtu-size' => 1492,
        'enable-encryption' => true,
      ],
      'debug' => [
        'level' => 1,
      ],
      'player' => [
        'save-player-data' => true,
        'verify-xuid' => true,
      ],
      'level-settings' => [
        'default-format' => 'leveldb',
      ],
      'chunk-sending' => [
        'per-tick' => 4,
        'spawn-radius' => 4,
      ],
      'chunk-ticking' => [
        'per-tick' => 40,
        'tick-radius' => 3,
        'blocks-per-subchunk-per-tick' => 3,
        'disable-block-ticking' => NULL,
      ],
      'chunk-generation' => [
        'population-queue-size' => 32,
      ],
      'ticks-per' => [
        'autosave' => 6000,
      ],
      'auto-report' => [
        'enabled' => true,
        'send-code' => true,
        'send-settings' => true,
        'send-phpinfo' => false,
        'use-https' => true,
        'host' => 'crash.pmmp.io',
      ],
      'anonymous-statistics' => [
        'enabled' => false,
        'host' => 'stats.pocketmine.net',
      ],
      'auto-updater' => [
        'enabled' => true,
        'on-update' => [
          'warn-console' => true,
        ],
        'preferred-channel' => 'stable',
        'suggest-channels' => true,
        'host' => 'update.pmmp.io',
      ],
      'timings' => [
        'host' => 'timings.pmmp.io',
      ],
      'console' => [
        'title-tick' => true,
      ],
      'aliases' => [],
      'worlds' => [],
      'plugins' => [
        'legacy-data-dir' => false,
      ],
    ];
  }

  public static function serverProperties(): array
  {
    return [
      'Properties Config file',
      date('D M j H:i:s T Y'),
      'language' => 'eng',
      'motd' => 'Powered by ' . ENDER_HIVE_TITLE . ' + WordPress',
      'server-name' => 'Just another PocketMine Server',
      'enable-ipv6' => true,
      'server-port' => 19132,
      'server-portv6' => 19133,
      'white-list' => false,
      'max-players' => 20,
      'gamemode' => 'survival',
      'force-gamemode' => false,
      'hardcore' => false,
      'pvp' => true,
      'difficulty' => 2,
      'generator-settings' => '',
      'level-name' => 'world',
      'level-seed' => '',
      'level-type' => 'DEFAULT',
      'enable-query' => true,
      'auto-save' => true,
      'view-distance' => 16,
      'xbox-auth' => true,
    ];
  }
}