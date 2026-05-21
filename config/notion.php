<?php

return [
    'token'      => env('NOTION_TOKEN'),
    'version'    => env('NOTION_VERSION', '2022-06-28'),
    'users_db'   => env('NOTION_USERS_DB'),
    'content_db' => env('NOTION_CONTENT_DB'),
];
