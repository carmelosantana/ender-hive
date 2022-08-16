<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\API;

use \WP_Error;

class Instance extends Base
{
    public function __construct()
    {
        $this->resource_name = 'instances'; 
    }

    // Register our routes.
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->resource_name, [
            // Here we register the readable endpoint for collections.
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
            ],
            // Register our schema callback.
            'schema' => [$this, 'get_item_schema'
        ],
        ]);
        register_rest_route($this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            [
                'methods'   => 'GET',
                'callback'  => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
            // Register our schema callback.
            'schema' => [$this, 'get_item_schema'],
        ));
        register_rest_route($this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)/start', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            [
                'methods'   => 'PUT',
                'callback'  => [$this, 'start'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
            // Register our schema callback.
            'schema' => [$this, 'get_item_schema'],
        ));        
    }

    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items($request)
    {
        $args = [
            'post_type' => 'instance',
            'posts_per_page' => -1,
            'author' => get_current_user_id(),
        ];
        $posts = get_posts($args);

        $data = [];

        if (empty($posts)) {
            return rest_ensure_response($data);
        }

        foreach ($posts as $post) {
            $response = $this->prepare_item_for_response($post, $request);
            $data[] = $this->prepare_response_for_collection($response);
        }

        // Return all of our comment response data.
        return rest_ensure_response($data);
    }

    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item($request)
    {
        $id = (int) $request['id'];
        $post = get_post($id);

        if (empty($post)) {
            return rest_ensure_response([]);
        }

        $response = $this->prepare_item_for_response($post, $request);

        // Return all of our post response data.
        return $response;
    }

    /**
     * Get our sample schema for a post.
     *
     * @return array The sample schema for a post
     */
    public function get_item_schema()
    {
        if (isset($this->schema)) {
            // Since WordPress 5.3, the schema can be cached in the $schema property.
            return $this->schema;
        }

        $this->schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'post',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__('Unique identifier for the object.', ENDER_HIVE),
                    'type'         => 'integer',
                    'context'      => array('view', 'edit', 'embed'),
                    'readonly'     => true,
                ),
                'title' => array(
                    'description'  => esc_html__('The title of the object.', ENDER_HIVE),
                    'type'         => 'string',
                    'context'      => array('view', 'edit', 'embed'),
                    'readonly'     => true,
                ),
                'server_status_code' => array(
                    'description'  => esc_html__('The status of the instance.', ENDER_HIVE),
                    'type'         => 'integer',
                    'context'      => array('view', 'edit', 'embed'),
                    'readonly'     => true,
                ),
                'server_properties' => array(
                    'description'  => esc_html__('Server properties.', ENDER_HIVE),
                    'type'         => 'object',
                    'context'      => array('view', 'edit', 'embed'),
                    'readonly'     => true,
                ),
                'content' => array(
                    'description'  => esc_html__('The content for the object.', ENDER_HIVE),
                    'type'         => 'string',
                ),
            ),
        );

        return $this->schema;
    }

    /**
     * Matches the post data to the schema we want.
     *
     * @param WP_Post $post The comment object whose response is being prepared.
     */
    public function prepare_item_for_response($post, $request)
    {
        $post_data = [];

        $schema = $this->get_item_schema($request);

        // We are also renaming the fields to more understandable names.
        if (isset($schema['properties']['id'])) {
            $post_data['id'] = (int) $post->ID;
        }

        if (isset($schema['properties']['content'])) {
            $post_data['content'] = apply_filters('the_content', $post->post_content, $post);
        }

        return rest_ensure_response($post_data);
    }
}
