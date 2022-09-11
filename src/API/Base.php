<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\API;

use CarmeloSantana\EnderHive\Host\Status;
use CarmeloSantana\EnderHive\User\Permissions;
use \WP_Error;
use \WP_HTTP_Response;
use \WP_REST_Response;

abstract class Base
{
    public string $namespace = '/' . ENDER_HIVE . '/v1';

    public $rest_forbidden = Status::FORBIDDEN;

    public const PERMISSION_READ = 'read';

    public const PERMISSION_WRITE = 'edit_posts';

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items_permissions_check($request)
    {
        if (!current_user_can(self::PERMISSION_WRITE)) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot access the instance resource.'), array('status' => $this->authorization_status_code()));
        }
        return true;
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_permissions_check($request)
    {
        if (!current_user_can(self::PERMISSION_WRITE)) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot access the instance resource.'), array('status' => $this->authorization_status_code()));
        }
        return true;
    }

    /**
     * Matches the post data to the schema we want.
     *
     * @param WP_Post $post The comment object whose response is being prepared.
     */
    public function prepare_item_for_response(\WP_REST_Request $request)
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

    /**
     * Prepare a response for inserting into a collection of responses.
     *
     * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
     *
     * @param WP_REST_Response $response Response object.
     * @return array Response data, ready for insertion into collection data.
     */
    public function prepare_response_for_collection($response)
    {
        if (!($response instanceof WP_REST_Response)) {
            return $response;
        }

        $data = (array) $response->get_data();
        $server = rest_get_server();

        if (method_exists($server, 'get_compact_response_links')) {
            $links = call_user_func(array($server, 'get_compact_response_links'), $response);
        } else {
            $links = call_user_func(array($server, 'get_response_links'), $response);
        }

        if (!empty($links)) {
            $data['_links'] = $links;
        }

        return $data;
    }

    /**
     * Get our sample schema for a post.
     *
     * @return array The sample schema for a post
     */
    public function get_item_schema(\WP_REST_Request $request)
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
                    'description'  => esc_html__('Unique identifier for the object.', 'my-textdomain'),
                    'type'         => 'integer',
                    'context'      => array('view', 'edit', 'embed'),
                    'readonly'     => true,
                ),
                'content' => array(
                    'description'  => esc_html__('The content for the object.', 'my-textdomain'),
                    'type'         => 'string',
                ),
            ),
        );

        return $this->schema;
    }

    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code()
    {
        return Permissions::authorizationStatusCode();
    }

    public function rest_ensure_response($response, int $status = Status::OK)
    {
        if (is_wp_error($response)) {
            return $response;
        }

        if ($response instanceof WP_REST_Response) {
            return $response;
        }

        // While WP_HTTP_Response is the base class of WP_REST_Response, it doesn't provide
        // all the required methods used in WP_REST_Server::dispatch().
        if ($response instanceof WP_HTTP_Response) {
            return new WP_REST_Response(
                $response->get_data(),
                $response->get_status(),
                $response->get_headers()
            );
        }

        return new WP_REST_Response($response, $status);
    }
}
