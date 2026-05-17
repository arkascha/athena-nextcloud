<?php
declare(strict_types=1);

return [
    'routes' => [
        // Dashboard page (NC session auth)
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // ── Kobo-facing API (client Bearer token auth) ──────────────────────
        ['name' => 'sequence#sequence',    'url' => '/api/v1/sequence',                    'verb' => 'GET'],
        ['name' => 'step#acknowledge',     'url' => '/api/v1/steps/{id}/acknowledge',       'verb' => 'POST'],
        ['name' => 'step#missed',          'url' => '/api/v1/steps/{id}/missed',            'verb' => 'POST'],
        ['name' => 'heartbeat#heartbeat',  'url' => '/api/v1/heartbeat',                    'verb' => 'POST'],
        ['name' => 'client#library',       'url' => '/api/v1/client/library',               'verb' => 'GET'],

        // ── Management: sequences (NC session auth) ──────────────────────────
        ['name' => 'manage_sequence#index',    'url' => '/api/v1/manage/sequences',             'verb' => 'GET'],
        ['name' => 'manage_sequence#create',   'url' => '/api/v1/manage/sequences',             'verb' => 'POST'],
        ['name' => 'manage_sequence#update',   'url' => '/api/v1/manage/sequences/{id}',        'verb' => 'PUT'],
        ['name' => 'manage_sequence#destroy',  'url' => '/api/v1/manage/sequences/{id}',        'verb' => 'DELETE'],
        ['name' => 'manage_sequence#steps',    'url' => '/api/v1/manage/sequences/{id}/steps',  'verb' => 'GET'],
        ['name' => 'manage_sequence#add_step', 'url' => '/api/v1/manage/sequences/{id}/steps',  'verb' => 'POST'],

        // ── Management: steps (NC session auth) ─────────────────────────────
        ['name' => 'manage_step#update',   'url' => '/api/v1/manage/steps/{id}', 'verb' => 'PUT'],
        ['name' => 'manage_step#destroy',  'url' => '/api/v1/manage/steps/{id}', 'verb' => 'DELETE'],

        // ── Management: clients (NC session auth) ────────────────────────────
        ['name' => 'manage_client#index',       'url' => '/api/v1/manage/clients',                       'verb' => 'GET'],
        ['name' => 'manage_client#create',      'url' => '/api/v1/manage/clients',                       'verb' => 'POST'],
        ['name' => 'manage_client#update',      'url' => '/api/v1/manage/clients/{id}',                  'verb' => 'PUT'],
        ['name' => 'manage_client#destroy',     'url' => '/api/v1/manage/clients/{id}',                  'verb' => 'DELETE'],
        ['name' => 'manage_client#rotate_token','url' => '/api/v1/manage/clients/{id}/token',            'verb' => 'POST'],
        ['name' => 'manage_client#sequences',   'url' => '/api/v1/manage/clients/{id}/sequences',        'verb' => 'GET'],

        // ── Management: shares (NC session auth) ─────────────────────────────
        ['name' => 'manage_share#index',        'url' => '/api/v1/manage/clients/{id}/shares',           'verb' => 'GET'],
        ['name' => 'manage_share#create',       'url' => '/api/v1/manage/clients/{id}/shares',           'verb' => 'POST'],
        ['name' => 'manage_share#update',       'url' => '/api/v1/manage/clients/{id}/shares/{shareId}', 'verb' => 'PUT'],
        ['name' => 'manage_share#destroy',      'url' => '/api/v1/manage/clients/{id}/shares/{shareId}', 'verb' => 'DELETE'],
        ['name' => 'manage_share#search_users', 'url' => '/api/v1/manage/users/search',                  'verb' => 'GET'],

        // ── Monitoring (NC session auth) ─────────────────────────────────────
        ['name' => 'monitor#client', 'url' => '/api/v1/manage/clients/{id}/monitor', 'verb' => 'GET'],
        ['name' => 'monitor#events', 'url' => '/api/v1/manage/clients/{id}/events',  'verb' => 'GET'],

        // ── Client-reported events (client Bearer token auth) ─────────────
        ['name' => 'event#record', 'url' => '/api/v1/events', 'verb' => 'POST'],
    ],
];
