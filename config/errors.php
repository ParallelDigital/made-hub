<?php

return [
    // When true, users will not see error pages; instead, web requests will redirect to the homepage (or a configured route).
    'redirect_on_error' => (bool) env('REDIRECT_ON_ERROR', false),

    // The path to redirect to when redirect_on_error is enabled. Default is '/'.
    'redirect_route' => env('ERROR_REDIRECT_ROUTE', '/'),
];
