<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;

class InvoiceController extends Controller
{
    public function show(Shipment $shipment)
    {
        $shipment->load('customer');

        return view('admin.invoice', ['shipment' => $shipment]);
    }
}
