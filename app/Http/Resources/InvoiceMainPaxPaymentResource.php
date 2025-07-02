<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceMainPaxPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer_id'    => $this->customer_id,
            'customer'       => new CustomerResource($this->whenLoaded('customer')),
            'airLine_id'     => $this->airLine_id,
            'airLine'        => new AirLineResource($this->whenLoaded('airLine')),
            'travel_from'    => $this->travel_from,
            'travel_to'      => $this->travel_to,
            'fromAirport'    => new WorldAirportResource($this->whenLoaded('fromAirport')),
            'toAirport'      => new WorldAirportResource($this->whenLoaded('toAirport')),

            //'airticket_from_pax'          => $this->getAirticketFromPax(),
            'balance'                     => $this->getAirticketFromPaxBalance(),
            'hotelFromPaxPendingPayment'       => $this->calculatePending('hotel', 'hotel_from_pax'),
            'airticketFromPaxPendingPayment'   => $this->calculatePending('airticket', 'airticket_from_pax'),
            'cruiseFromPaxPendingPayment'      => $this->calculatePending('cruise', 'cruise_from_pax'),
            'insuranceFromPaxPendingPayment'   => $this->calculatePending('insurance', 'insurance_from_pax'),
            'landPackageFromPaxPendingPayment' => $this->calculatePending('land_package', 'landpackage_from_pax'),
            'miscFromPaxPendingPayment'        => $this->calculatePending('misc', 'misc_from_pax'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function calculatePending(string $fieldName, string $key): float
    {
        try {
            $raw = $this->{$fieldName};

            if (is_string($raw)) {
                $raw = json_decode($raw, true);
            }

            if (!is_array($raw) || !isset($raw[$key])) {
                return 0;
            }

            $item = $raw[$key];
            $total = floatval($item['total'] ?? 0);
            $paid = floatval($item['amountPaid'] ?? 0);
            $refund = floatval($item['refund'] ?? 0);

            return round($total - $paid - $refund, 2);
        } catch (\Throwable $e) {
            \Log::error("Pending payment error on $fieldName: " . $e->getMessage(), ['id' => $this->id]);
            return 0;
        }
    }

    private function getAirticketFromPaxBalance(): float
    {
        try {
            $data = $this->airticket;

            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            if (!is_array($data) || !isset($data['airticket_from_pax']['balance'])) {
                return 0.0;
            }

            return round(floatval($data['airticket_from_pax']['balance']), 2);
        } catch (\Throwable $e) {
            \Log::error("Error extracting balance from airticket_from_pax: " . $e->getMessage(), ['id' => $this->id]);
            return 0.0;
        }
    }

}
