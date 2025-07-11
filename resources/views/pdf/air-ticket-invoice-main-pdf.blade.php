<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoiceMain->invoice_number }}</title>
</head>

<body style="margin:0;padding:0;background:#efefef;font-family:Arial,sans-serif;color:#333; font-size: 12px">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#efefef;padding:20px 0;">
        <tr>
            <td align="center">
                <!-- container -->
                <table width="100%" cellpadding="0" cellspacing="0" border="1"
                    style="border-color:#ddd;background:#fff;">

                    <!-- HEADER -->
                    <tr>
                        <td
                            style="padding:10px;background:linear-gradient(90deg,#001f7f,#0047ab); background-color: #000000;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="middle" style="width:200px;">
                                        <img src="{{ public_path('/assets/images/completeLogo.png') }}" width="200"
                                            alt="Logo" style="display:block;">
                                    </td>
                                    <td valign="top" style="text-align:right;color:#fff;line-height:1.4;">
                                        <div><strong>Invoice No:</strong> {{ $invoiceMain->invoice_number }}</div>
                                        <div><strong>Invoice Date:</strong>
                                            {{ $invoiceMain->created_at->format('Y-m-d') }}</div>
                                        <div><strong>Client No:</strong>
                                            {{ $invoiceMain->customer_details['phone_number'] ?? '' }}</div>
                                        <div><strong>GDS:</strong> {{ $invoiceMain->gds_type ?? '' }}&nbsp;
                                            <strong>PNR:</strong> {{ $invoiceMain->ticket_number ?? '' }}
                                        </div>

                                        <div><strong>TICO (Retail):</strong> 50016404</div>
                                        <div><strong>TICO (Wholesale):</strong> 50027611</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- BOOKING DETAILS -->
                    <tr>
                        <td style="font-size: 11px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr style="background:#0070c0;color:#fff">
                                    <td colspan="4" style="padding:4px;">Booking Details</td>
                                </tr>
                                <tr style="background:#f2f2f2;">
                                    <th style="padding:2px; text-align: center; border:1px solid #ccc;">Booking Date
                                    </th>
                                    <th style="padding:2px; text-align: center; border:1px solid #ccc;">Booking Agent
                                    </th>
                                    <!-- <th style="padding:2px; text-align: center; border:1px solid #ccc;">Supplier</th> -->
                                    <th colspan="2" style="padding:2px; text-align: center; border:1px solid #ccc;">
                                        Passengers</th>
                                </tr>
                                <tr style="">
                                    <td style="padding:2px; text-align: center; border:1px solid #ccc;">
                                        {{ $invoiceMain->booking_date }}</td>
                                    <td style="padding:2px; text-align: center; border:1px solid #ccc;">
                                        {{ $invoiceMain->sales_agents?->name }}</td>
                                    <!-- <td style="padding:2px; text-align: center; border:1px solid #ccc;">{{ $invoiceMain->supplier?->supplier_code }}</td> -->
                                    <!-- Passengers as list in one cell -->
                                    <td colspan="2"
                                        style="padding:2px; text-align: center; border:1px solid #ccc; vertical-align: top;">
                                        <!-- ✅ NEW: Flex container to wrap names -->
                                        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px;">
                                            @if ($invoiceMain->airticket && $invoiceMain->airticket['passenger_details'])
                                                @foreach ($invoiceMain->airticket['passenger_details'] as $p)
                                                    <!-- ✅ Each name in a flex item -->
                                                    <span style="white-space: nowrap;">
                                                        {{ $p['first_name'] }}
                                                        {{ $p['last_name'] }}{{ !$loop->last ? ',' : '' }}
                                                    </span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <tr style="background:#f2f2f2;">
                                    <th style="padding:2px; text-align: center; border:1px solid #ccc;">Bill To</th>
                                    <th style="padding:2px; text-align: center; border:1px solid #ccc;">Ticket Number
                                    </th>
                                    <th style="padding:2px; text-align: center; border:1px solid #ccc;">Airline</th>
                                    <th style="padding:2px; text-align: center; border:1px solid #ccc;">Travel Date</th>
                                </tr>
                                <tr style="">
                                    <!-- Bill To -->
                                    <td style="padding:2px; text-align: center; border:1px solid #ccc;">
                                        {{ $invoiceMain->customer_details['full_name'] }}</td>
                                    <td style="padding:2px; text-align: center; border:1px solid #ccc;">
                                        {{ $invoiceMain->ticket_number }}</td>

                                    <!-- Airline & Travel Date -->
                                    <td style="padding:2px; text-align: center; border:1px solid #ccc;">
                                        {{ $invoiceMain->airLine?->airline_code }}</td>
                                    <td style="padding:2px; text-align: center; border:1px solid #ccc;">
                                        {{ $invoiceMain->departure_date }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FLIGHT INFO -->
                    <tr>
                        <td style="font-size: 11px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr style="background:#0070c0;color:#fff;">
                                    <td colspan="7" style="padding:4px;">Flight Information</td>
                                </tr>
                                <tr style="background:#f2f2f2;">
                                    <th style="padding:2px;border:1px solid #ccc;">Airline</th>
                                    <th style="padding:2px;border:1px solid #ccc;">Flight No.</th>
                                    <th style="padding:2px;border:1px solid #ccc;">From</th>
                                    <th style="padding:2px;border:1px solid #ccc;">To</th>
                                    <th style="padding:2px;border:1px solid #ccc;">Arrival Date</th>
                                    <th style="padding:2px;border:1px solid #ccc;">Departure Time</th>
                                    <th style="padding:2px;border:1px solid #ccc;">Arrival Time</th>
                                </tr>
                                @if ($invoiceMain->airticket['flight_details'])
                                    @foreach ($invoiceMain->airticket['flight_details'] as $f)
                                        <tr style="">
                                            <td style="padding:2px;border:1px solid #ccc;">{{ $f['airline'] }}</td>
                                            <td style="padding:2px;border:1px solid #ccc;">{{ $f['flight_no'] }}</td>
                                            <td style="padding:2px;border:1px solid #ccc;">{{ $f['from'] }}</td>
                                            <td style="padding:2px;border:1px solid #ccc;">{{ $f['to'] }}</td>
                                            <td style="padding:2px;border:1px solid #ccc;">{{ $f['date'] }}</td>
                                            <td style="padding:2px;border:1px solid #ccc;">{{ $f['dep_time'] }}</td>
                                            <td style="padding:2px;border:1px solid #ccc;">{{ $f['arr_time'] }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </table>
                        </td>
                    </tr>

                    <!-- FARE SUMMARY -->
                    <!-- inside the data you will see airticket object inside that their is airticket_from_pax inside that are is all info adult,child,infant
                    example: baseFare: {adt1: "405",chd1: "0", inf1: "0"}-->
                    <tr>
                        <td style="font-size: 11px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr style="background:#0070c0;color:#fff;">
                                    <td colspan="5" style="padding:4px;">Fare Summary</td>
                                </tr>
                                <tr style="background:#f2f2f2;">
                                    <th style="padding:2px;border:1px solid #ccc;"></th>
                                    <th style="padding:2px;border:1px solid #ccc;text-align:right;">Base Fare</th>
                                    <th style="padding:2px;border:1px solid #ccc;text-align:right;">GST/HST</th>
                                    <th style="padding:2px;border:1px solid #ccc;text-align:right;">Taxes</th>
                                    <th style="padding:2px;border:1px solid #ccc;text-align:right;">Total</th>
                                </tr>
                                <tr style="">
                                    <td style="padding:2px;border:1px solid #ccc;">Adult</td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['baseFare']) && isset($invoiceMain->airticket['airticket_from_pax']['baseFare']['adt1']) ? $invoiceMain->airticket['airticket_from_pax']['baseFare']['adt1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['gstHst']) && isset($invoiceMain->airticket['airticket_from_pax']['gstHst']['adt1']) ? $invoiceMain->airticket['airticket_from_pax']['gstHst']['adt1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['taxes']) && isset($invoiceMain->airticket['airticket_from_pax']['taxes']['adt1']) ? $invoiceMain->airticket['airticket_from_pax']['taxes']['adt1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['subTotal']) && isset($invoiceMain->airticket['airticket_from_pax']['subTotal']['adt1']) ? $invoiceMain->airticket['airticket_from_pax']['subTotal']['adt1'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td style="padding:2px;border:1px solid #ccc;">Child</td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['baseFare']) && isset($invoiceMain->airticket['airticket_from_pax']['baseFare']['chd1']) ? $invoiceMain->airticket['airticket_from_pax']['baseFare']['chd1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['gstHst']) && isset($invoiceMain->airticket['airticket_from_pax']['gstHst']['chd1']) ? $invoiceMain->airticket['airticket_from_pax']['gstHst']['chd1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['taxes']) && isset($invoiceMain->airticket['airticket_from_pax']['taxes']['chd1']) ? $invoiceMain->airticket['airticket_from_pax']['taxes']['chd1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['subTotal']) && isset($invoiceMain->airticket['airticket_from_pax']['subTotal']['chd1']) ? $invoiceMain->airticket['airticket_from_pax']['subTotal']['chd1'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td style="padding:2px;border:1px solid #ccc;">Infant</td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['baseFare']) && isset($invoiceMain->airticket['airticket_from_pax']['baseFare']['inf1']) ? $invoiceMain->airticket['airticket_from_pax']['baseFare']['inf1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['gstHst']) && isset($invoiceMain->airticket['airticket_from_pax']['gstHst']['inf1']) ? $invoiceMain->airticket['airticket_from_pax']['gstHst']['inf1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['taxes']) && isset($invoiceMain->airticket['airticket_from_pax']['taxes']['inf1']) ? $invoiceMain->airticket['airticket_from_pax']['taxes']['inf1'] : '' }}
                                    </td>
                                    <td style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['subTotal']) && isset($invoiceMain->airticket['airticket_from_pax']['subTotal']['inf1']) ? $invoiceMain->airticket['airticket_from_pax']['subTotal']['inf1'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td style="padding:2px;border:1px solid #ccc;">Total Amount</td>
                                    <td colspan="4" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['total']) ? $invoiceMain->airticket['airticket_from_pax']['total'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td style="padding:2px;border:1px solid #ccc;">Payment Method</td>
                                    <td colspan="4" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                    </td>
                                </tr>
                                <tr style="">
                                    <td style="padding:2px;border:1px solid #ccc;">Amount Paid</td>
                                    <td colspan="4" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['amountPaid']) ? $invoiceMain->airticket['airticket_from_pax']['amountPaid'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td style="padding:2px;border:1px solid #ccc;">Balance Due</td>
                                    <td colspan="4" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['balance']) ? $invoiceMain->airticket['airticket_from_pax']['balance'] : '' }}
                                    </td>
                                </tr>
                                <!-- <tr style="">
                                    <td style="padding:2px;border:1px solid #ccc;">Payment Method</td> Now working fix that
                                    <td colspan="4" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                         {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['transaction_type_name']) ? $invoiceMain->airticket['airticket_from_pax']['transaction_type_name'] : '' }}
                                    </td>
                                </tr> -->
                                <!-- inside the data you will see airticket object inside that their is airticket_from_pax inside that are is total Amount-->
                                <tr style="background:#f2f2f2;">
                                    <th style="padding:2px;border:1px solid #ccc;text-align:right;">Total Amount:</th>
                                    <th colspan="4" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['total']) ? $invoiceMain->airticket['airticket_from_pax']['total'] : '' }}
                                    </th>
                                </tr>
                                <tr style="">
                                    <td colspan="2" style="border:none;"></td>
                                    <td style="padding:2px;border:1px solid #ccc;"><strong>Amount Paid:</strong></td>
                                    <td colspan="2" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['amountPaid']) ? $invoiceMain->airticket['airticket_from_pax']['amountPaid'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td colspan="2" style="border:none;"></td>
                                    <td style="padding:2px;border:1px solid #ccc;"><strong>Refund:</strong></td>
                                    <td colspan="2" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['refund']) ? $invoiceMain->airticket['airticket_from_pax']['refund'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td colspan="2" style="border:none;"></td>
                                    <td style="padding:2px;border:1px solid #ccc;"><strong>Refund Paid to
                                            client:</strong></td>
                                    <td colspan="2" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['refund_paid']) ? $invoiceMain->airticket['airticket_from_pax']['refund_paid'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td colspan="2" style="border:none;"></td>
                                    <td style="padding:2px;border:1px solid #ccc;"><strong>Balance Due:</strong></td>
                                    <td colspan="2" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                        {{ $invoiceMain->airticket && $invoiceMain->airticket['airticket_from_pax'] && isset($invoiceMain->airticket['airticket_from_pax']['balance']) ? $invoiceMain->airticket['airticket_from_pax']['balance'] : '' }}
                                    </td>
                                </tr>
                                <tr style="">
                                    <td colspan="2" style="border:none;"></td>
                                    <td style="padding:2px;border:1px solid #ccc;"><strong>Payment Method:</strong>
                                    </td>
                                    <td colspan="2" style="padding:2px;border:1px solid #ccc;text-align:right;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- NOTES & DOCS -->
                    <tr>
                        <td style="padding:4px;font-size:9px;">
                            <p>Please review your itinerary to ensure that all information is correct. Check-in 2-3
                                hours prior to departures. Re-confirm flight times at least 24hrs prior to departures as
                                they are subject to change. Tickets are fully non-refundable unless otherwise stated.
                            </p>
                            <p><strong>Travel Documents Required:</strong></p>
                            <!-- // if true show tik if false show X -->
                            <table style="width:100%;">
                                <tr>
                                    <td>Canadian Passport:
                                        {{ $invoiceMain->valid_canadian_passport === 'true' ? 'Yes' : 'No' }}</td>
                                    <td>Canadian Citizenship or PR Card:
                                        {{ $invoiceMain->canadian_citizenship_or_prCard === 'true' ? 'Yes' : 'No' }}
                                    </td>
                                    <td>Valid Travel Visa:
                                        {{ $invoiceMain->valid_travel_visa === 'true' ? 'Yes' : 'No' }}</td>
                                    <td>Tourist Card: {{ $invoiceMain->tourist_card === 'true' ? 'Yes' : 'No' }}</td>
                                </tr>
                            </table>

                            <p><strong>Special Remarks:</strong><br>**IMPORTANT NOTE:** Passengers must ensure that the
                                name on their ticket matches their passport's name. For those traveling through the USA,
                                a valid visa is required. Passengers flying via Europe must present a valid and stamped
                                Canadian visa. We advise you to confirm all visa requirements with your airline or the
                                appropriate consulate/embassy, as we cannot guarantee that any restrictions due to visa
                                issues will be lifted. Please verify that your travel dates and times match your
                                booking, and note that we are not responsible for any schedule changes made by the
                                airline—although we are happy to help, all available options must first be confirmed
                                with the carrier. In addition, passengers are required to report to the airport 4–5
                                hours before departure. For Indian passport holders, kindly check the validity of your
                                passport, PR Card, and Work Permit (TRV). Canadian citizens must ensure that their
                                passport is valid for more than six months at the time of travel and that they possess
                                either an Indian visa or an OCI card as mandated. To confirm all these requests, please
                                call us at least 72 hours before your travel. Thank you. Email: Katariatravels@gmail.com
                                | Website: www.katariatravel.com
                            </p>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background:#f9f9f9;text-align:center;padding:15px;color:#666;">
                            <div style="color:#0070c0;font-weight:bold;font-size:16px;">TRAVEL LIKE A BOSS</div>
                            <div>2883 Derry Rd E, Mississauga, ON L4T 1A6, Canada</div>
                            <div>Contact: support@katariatravel.ca | Tel: 905 678 1200 | Fax: 905 678 1201</div>
                        </td>
                    </tr>

                </table>
                <!-- /container -->
            </td>
        </tr>
    </table>
</body>

</html>
