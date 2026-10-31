<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index()
    {
        return view('tracking');
    }

    public function track(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $shipment = Shipment::with('trackingEvents')
            ->where('tracking_number', strtoupper(trim($request->code)))
            ->first();

        if (!$shipment) {
            return response()->json(['found' => false, 'message' => 'Tracking number not found.'], 404);
        }

        return response()->json([
            'found'    => true,
            'shipment' => [
                'tracking_number'    => $shipment->tracking_number,
                'status'             => $shipment->status,
                'status_label'       => $shipment->status_label,
                'status_color'       => $shipment->status_color,
                'sender_name'        => $shipment->sender_name,
                'sender_country'     => strtoupper($shipment->sender_country),
                'recipient_name'     => $shipment->recipient_name,
                'recipient_country'  => strtoupper($shipment->recipient_country),
                'weight_kg'          => $shipment->weight_kg,
                'estimated_delivery' => $shipment->estimated_delivery?->format('d M Y'),
                'events'             => $shipment->trackingEvents->map(fn ($e) => [
                    'occurred_at' => $e->occurred_at->format('d M Y, H:i'),
                    'status'      => $e->status,
                    'location'    => $e->location,
                    'description' => $e->description,
                ]),
            ],
        ]);
    }
}
