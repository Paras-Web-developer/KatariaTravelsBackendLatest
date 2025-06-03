<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/emails', function () {
    $enquiry = App\Models\FlightBookingEnquiry::first();
    dd($enquiry);

    return view('emails.social-media-enquiry', ['enquiry' => $enquiry]);
});


require __DIR__ . '/auth.php';
