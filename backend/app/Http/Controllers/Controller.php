<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/** Base controller for shared HTTP behavior as application modules are added. */
abstract class Controller
{
    use AuthorizesRequests;
}
