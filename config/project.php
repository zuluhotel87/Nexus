<?php

use Illuminate\Support\Facades\Facade;

return [
    'super_users' => explode(',', env('SUPER_ADMINS')),
];
