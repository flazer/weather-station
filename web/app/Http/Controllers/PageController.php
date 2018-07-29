<?php

namespace App\Http\Controllers;

use App\Models\Device;

class PageController extends Controller
{

    public function index() {
        $devices = Device::where('status', Device::STATUS_RUN)
            ->orderBy('sort', 'ASC')
            ->get();

        return view('grid', [
            'devices' => $devices
        ]);
    }
}