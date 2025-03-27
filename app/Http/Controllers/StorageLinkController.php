<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class StorageLinkController extends Controller
{
    public function create()
    {
        $exitCode = Artisan::call('storage:link');
        return 'Storage link created';
    }
}
