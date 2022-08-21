<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive;

use Symfony\Component\Yaml\Yaml;

class Config
{
  public function allNew(\WP_Post $post): void
  {
    $this->post = $post;

    foreach ($this->defaultFiles() as $file) {
      $file_path = Instance::getPath($post->post_name) . DIRECTORY_SEPARATOR . $file['filename'] . (isset($file['extension']) ? '.' . $file['extension'] : '');

      if (isset($file['callback']) and is_callable($file['callback'])) {
        $file['content'] = call_user_func($file['callback']);
      }

      switch ($file['extension'] ?? null) {
        case 'properties':
          $file['content'] = self::arrayToIni($file['content']);
          break;

        case 'yml':
          $file['content'] = Yaml::dump($file['content']);
          break;
      }

      file_put_contents($file_path, ($file['content'] ?? ''));
    }
  }

  public function defaultFiles(): array
  {
    return [
      'htaccess' => [
        'filename' => '.htaccess',
        'callback' => [$this, 'defaultHtaccess'],

      ],
      'banned_ips' => [
        'filename' => 'banned-ips',
        'extension' => 'txt',
      ],
      'banned_players' => [
        'filename' => 'banned-players',
        'extension' => 'txt',
      ],
      'ops' => [
        'filename' => 'ops',
        'extension' => 'txt',
      ],
      'plugin_list' => [
        'filename' => 'plugin_list',
        'extension' => 'yml',
        'callback' => [$this, 'defaultPluginList'],
      ],
      'pocketmine' => [
        'filename' => 'pocketmine',
        'extension' => 'yml',
        'callback' => [$this, 'defaultPocketmine'],
      ],
      'server' => [
        'filename' => 'server',
        'extension' => 'properties',
        'callback' => [$this, 'generateServerProperties'],
      ],
      'white_list' => [
        'filename' => 'white-list',
        'extension' => 'txt',
      ],
    ];
  }

  public function defaultHtaccess(): string
  {
    return <<<HTACCESS
order allow,deny
deny from all
HTACCESS;
  }

  public function defaultPluginList(): array
  {
    return [
      // blacklist: Only plugins which ARE NOT listed will load.
      // whitelist: Only plugins which ARE listed will load.
      'mode' => 'blacklist',
      'plugins' => new \stdClass(),
    ];
  }

  public function defaultPocketmine(): array
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

  static public function serverProperties(): array
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

  public function generateServerProperties(): array
  {
    $properties = [];

    foreach (self::serverProperties() as $key => $value) {
      if (is_string($key)) {
        $meta = Options::getMeta($this->post->ID, $key);
      }

      if ($meta) {
        $properties[$key] = $meta;
      } else {
        $properties[$key] = $value;
      }
      $properties[$key] = $meta;
    }

    return $properties;
  }

  public static function arrayToIni(array $array): string
  {
      $ini = '';
      foreach ($array as $key => $value) {
          if (is_bool($value)) {
              $value = $value ? 'on' : 'off';
          }

          // If key is integer treat as comment.
          if (is_int($key)) {
              $ini .= '#' . $value . PHP_EOL;
          } else {
              $ini .= $key . '=' . $value . PHP_EOL;
          }
      }
      return $ini;
  }  
}
