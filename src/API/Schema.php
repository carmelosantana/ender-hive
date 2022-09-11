<?php

declare(strict_types=1);

namespace CarmeloSantana\EnderHive\API;

class Schema
{
    public const DEFAULT_CONTEXT = ['view', 'edit', 'embed'];

    public static function instanceId(): array
    {
        return [
            'description' => esc_html__('Unique identifier for this item.', ENDER_HIVE),
            'type' => 'integer',
            'context' => self::DEFAULT_CONTEXT,
            'readonly' => true,
        ];
    }

    public static function instanceTitle(): array
    {
        return [
            'description' => esc_html__('The title of the item.', ENDER_HIVE),
            'type' => 'string',
            'context' => self::DEFAULT_CONTEXT,
            'readonly' => true,
        ];
    }

    public static function lastModified(): array
    {
        return [
            'description' => esc_html__('Server settings last modified date.', ENDER_HIVE),
            'type' => 'string',
            'readonly' => true,
        ];
    }

    public static function lastModifiedGmt(): array
    {
        return [
            'description' => esc_html__('Server settings last modified date in GMT.', ENDER_HIVE),
            'type' => 'string',
            'readonly' => true,
        ];
    }    

    public static function responsePage(): array
    {
        return [
            'description' => esc_html__('Current log page.', ENDER_HIVE),
            'type' => 'integer',
            'readonly' => true,
        ];
    }

    public static function responsePages(): array
    {
        return [
            'description' => esc_html__('Total pages available.', ENDER_HIVE),
            'type' => 'integer',
            'readonly' => true,
        ];
    }

    public static function responseLinks(): array
    {
        return [
            'description' => esc_html__('List of API endpoints.', ENDER_HIVE),
            'type' => 'object',
            'readonly' => true,
        ];
    }

    public static function responseTotal(): array
    {
        return [
            'description' => esc_html__('Total log entries.', ENDER_HIVE),
            'type' => 'integer',
            'readonly' => true,
        ];
    }
    
    public static function serverLogs(): array
    {
        return [
            'description' => esc_html__('Log messages.', ENDER_HIVE),
            'type' => 'array',
            'readonly' => true,
        ];
    }

    public static function serverProperties(): array
    {
        return [
            'description' => esc_html__('The properties of this server instance.', ENDER_HIVE),
            'type' => 'object',
            'context' => self::DEFAULT_CONTEXT,
            'readonly' => true,
        ];
    }

    public static function serverSettings(): array
    {
        return [
            'description' => esc_html__('The instance settings to manage this server.', ENDER_HIVE),
            'type' => 'object',
            'context' => self::DEFAULT_CONTEXT,
            'readonly' => true,
        ];
    }    

    public static function serverQuery(): array
    {
        return [
            'description' => esc_html__('Player and game information.', ENDER_HIVE),
            'type' => 'object',
            'readonly' => true,
        ];
    }

    public static function serverStatusCode(): array
    {
        return [
            'description' => esc_html__('The status of the instance.', ENDER_HIVE),
            'type' => 'integer',
            'readonly' => true,
        ];
    }
}
