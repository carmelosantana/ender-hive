<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\API;

use CarmeloSantana\EnderHive\Host\Server;
use CarmeloSantana\EnderHive\Host\Status;

class Instance extends Base
{
    public const JSON_DRAFT_4 = 'http://json-schema.org/draft-04/schema#';

    public const RESOURCE = 'instance';

    public const RESOURCE_NAME = '/instances';

    public const RESOURCE_ID = '/(?P<id>[\d]+)';

    public function register_routes()
    {
        register_rest_route(self::NAMESPACE, self::RESOURCE_NAME, [
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
            'schema' => [$this, 'get_item_schema'],
        ]);
        register_rest_route(self::NAMESPACE, self::RESOURCE_NAME . self::RESOURCE_ID, [
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
            'schema' => [$this, 'get_item_schema'],
        ]);
        register_rest_route(self::NAMESPACE, self::RESOURCE_NAME . self::RESOURCE_ID . '/logs', [
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'serverLogs'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => [
                    'page' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ],
                    'page_order' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_string($param) and in_array($param, ['asc', 'desc']);
                        }
                    ],
                    'page_size' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ],
                ]
            ],
            'schema' => [$this, 'get_item_schema'],
        ]);
        register_rest_route(self::NAMESPACE, self::RESOURCE_NAME . self::RESOURCE_ID . '/restart', [
            [
                'methods'   => 'POST',
                'callback'  => [$this, 'serverRestart'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
            ],
            'schema' => [$this, 'get_item_schema'],
        ]);
        register_rest_route(self::NAMESPACE, self::RESOURCE_NAME . self::RESOURCE_ID . '/start', [
            [
                'methods'   => 'POST',
                'callback'  => [$this, 'serverStart'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
            ],
            'schema' => [$this, 'get_item_schema'],
        ]);
        register_rest_route(self::NAMESPACE, self::RESOURCE_NAME . self::RESOURCE_ID . '/stop', [
            [
                'methods'   => 'POST',
                'callback'  => [$this, 'serverStop'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
            ],
            'schema' => [$this, 'get_item_schema'],
        ]);
    }

    public function get_item(\WP_REST_Request $request)
    {
        if (!get_post_status((int) $request['id'])) {
            return rest_ensure_response([]);
        }

        return $this->prepare_item_for_response($request);
    }

    public function get_items(\WP_REST_Request $request)
    {
        $args = [
            'author' => get_current_user_id(),
            'fields' => 'ids',
            'post_type' => 'instance',
            'posts_per_page' => -1,
        ];

        $posts = get_posts($args);

        $data = [];

        if (empty($posts)) {
            return rest_ensure_response($data);
        }

        foreach ($posts as $post) {
            $this->serverInit($post);
            $response = $this->prepare_item_for_response($request);
            $data[] = $this->prepare_response_for_collection($response);
        }

        return rest_ensure_response($data);
    }

    public function get_item_schema(\WP_REST_Request $request)
    {
        if (isset($this->schema)) {
            return $this->schema;
        }

        switch ($request->get_attributes()['callback'][1]) {
            case 'get_item':
                $this->schema = array(
                    '$schema' => self::JSON_DRAFT_4,
                    'title' => self::RESOURCE,
                    'type' => 'object',
                    'properties' => [
                        'id' => Schema::instanceId(),
                        'title' => Schema::instanceTitle(),
                        'status_code' => Schema::serverStatusCode(),
                        'query' => Schema::serverQuery(),
                        'properties' => Schema::serverProperties(),
                        'settings' => Schema::serverSettings(),
                        'log_messages' => Schema::serverLogs(),
                        'last_modified' => Schema::lastModified(),
                        'last_modified_gmt' => Schema::lastModifiedGmt(),
                        '_links' => Schema::responseLinks(),
                    ],
                );
                break;

            case 'get_items':
                $this->schema = array(
                    '$schema' => self::JSON_DRAFT_4,
                    'title' => self::RESOURCE,
                    'type' => 'object',
                    'properties' => [
                        'id' => Schema::instanceId(),
                        'title' => Schema::instanceTitle(),
                        'status_code' => Schema::serverStatusCode(),
                        'query' => Schema::serverQuery(),
                        'settings' => Schema::serverSettings(),
                        '_links' => Schema::responseLinks(),
                    ],
                );
                break;

            case 'serverLogs':
                $this->schema = array(
                    '$schema' => self::JSON_DRAFT_4,
                    'title' => 'post',
                    'type' => 'object',
                    'properties' => array(
                        'status_code' => Schema::serverStatusCode(),
                        '_links' => Schema::responseLinks(),
                    ),
                );
                break;

            case 'serverRestart':
            case 'serverStart':
            case 'serverStop':
                $this->schema = array(
                    '$schema' => self::JSON_DRAFT_4,
                    'title' => 'post',
                    'type' => 'object',
                    'properties' => array(
                        'status_code' => Schema::serverStatusCode(),
                        '_links' => Schema::responseLinks(),
                    ),
                );
                break;
        }

        return $this->schema;
    }

    public function get_response_links()
    {
        return [
            'self' => [
                [
                    'href' => rest_url(self::NAMESPACE . self::RESOURCE_NAME . '/' . $this->server->getId() . ''),
                ]
            ],
            'collection' => [
                [
                    'href' => rest_url(self::NAMESPACE . self::RESOURCE_NAME),
                ]
            ],
            'logs' => [
                [
                    'href' => rest_url(self::NAMESPACE . self::RESOURCE_NAME . '/' . $this->server->getId() . '/logs'),
                ]
            ],
            'start' => [
                [
                    'href' => rest_url(self::NAMESPACE . self::RESOURCE_NAME . '/' . $this->server->getId() . '/start'),
                ]
            ],
            'stop' => [
                [
                    'href' => rest_url(self::NAMESPACE . self::RESOURCE_NAME . '/' . $this->server->getId() . '/stop'),
                ]
            ],
        ];
    }

    public function prepare_item_for_response(\WP_REST_Request $request)
    {
        $post_data = [];

        $schema = $this->get_item_schema($request);

        // Setup server if not already done. 
        if (!isset($this->server)) {
            $this->serverInit($request['id']);
        }

        if (isset($schema['properties']['id'])) {
            $post_data['id'] = (int) $this->server->getId();
        }

        if (isset($schema['properties']['logs'])) {
            $logs = array_reverse($this->server->logs());
            $post_data['logs'] = array_slice($logs, -10);
        }

        if (isset($schema['properties']['title'])) {
            $post_data['title'] = $this->server->getPost()->post_title;
        }

        if (isset($schema['properties']['status_code'])) {
            $post_data['status_code'] = $this->server->getStatus();
        }

        if (isset($schema['properties']['properties'])) {
            $post_data['properties'] = $this->server->getServerProperties();
        }

        if (isset($schema['properties']['settings'])) {
            $post_data['settings'] = $this->server->getServerSettings();
        }

        if (isset($schema['properties']['query'])) {
            $post_data['query'] = $this->server->query();
        }

        if (isset($schema['properties']['last_modified'])) {
            $post_data['last_modified'] = $this->server->getPost()->post_modified;
        }

        if (isset($schema['properties']['last_modified_gmt'])) {
            $post_data['last_modified_gmt'] = $this->server->getPost()->post_modified_gmt;
        }

        if (isset($schema['properties']['_links'])) {
            $post_data['_links'] = $this->get_response_links();
        }

        return rest_ensure_response($post_data);
    }

    public function serverInit(int|string $post_id)
    {
        $this->server = new Server((int) $post_id);
    }

    /**
     * Logs are displayed in chunks from newest to oldest.
     *
     * @param array $request
     * @return void
     */
    public function serverLogs(\WP_REST_Request $request)
    {
        $this->serverInit($request['id']);

        // Get the log
        $messages = $this->server->logs();

        // Start with reversed array for newest chunk first
        $messages = array_reverse($messages);

        // Limit output to $request['page_size'] lines
        $page_size = (int) ($request['page_size'] ?? 100);

        // Setup pages
        $pages = array_chunk($messages, $page_size);

        // We don't want to request more pages than we have
        $page = (int) ($request['page'] ?? 0);
        if ($page >= count($pages)) {
            $page = array_key_last($pages);
        } elseif ($page <= 0) {
            $page = 0;
        } else {
            $page = (int) $page - 1;
        }

        // Page order
        $page_order = $request['page_order'] ?? 'desc';
        switch ($page_order) {
            case 'asc':
                $pages[$page] = array_reverse($pages[$page]);
                break;
        }

        // Start output
        $output = [
            'messages' => $pages[$page],
            'page' => $page + 1,
            'pages' => count($pages),
            'total' => count($messages),
        ];

        return rest_ensure_response($output);
    }

    /**
     * Attempts to restart server if running.
     *
     * @param  mixed $request
     * @return void
     */
    public function serverRestart(\WP_REST_Request $request)
    {
        $this->serverInit($request['id']);

        switch ($this->server->getStatus()) {
            case Status::OK:
                $this->server->restart();
                break;
        }

        return $this->prepare_item_for_response($request);
    }

    public function serverStart(\WP_REST_Request $request)
    {
        $this->serverInit($request['id']);

        $this->server->start();

        return $this->prepare_item_for_response($request);
    }

    public function serverStop(\WP_REST_Request $request)
    {
        $this->serverInit($request['id']);

        $this->server->stop();

        return $this->prepare_item_for_response($request);
    }
}
