<?php

use App\Http\Controllers\Api\AirLineController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CarEnquiryController;
use App\Http\Controllers\Api\CarTypeController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EnquiryController;
use App\Http\Controllers\Api\EnquiryPaymentStatusController;
use App\Http\Controllers\Api\EnquirySourceController;
use App\Http\Controllers\Api\EnquiryStatusController;
use App\Http\Controllers\Api\HotelEnquireController;
use App\Http\Controllers\Api\StaticsConntroller;
use App\Http\Controllers\Api\StaticsController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AssignEnquiryController;
use App\Http\Controllers\Api\AssignHotelEnquiryController;
use App\Http\Controllers\Api\AssignOtherServiceController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CreditCardFormController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerInvoiceController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\FlightBookingEnquiryController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OtherServiceController;
use App\Http\Controllers\Api\PhoneNumberController;
use App\Http\Controllers\Api\StaticsOfOtherServicesController;
use App\Http\Controllers\Api\EmployeeStaticController;
use App\Http\Controllers\Api\InvoiceMainController;
use App\Http\Controllers\Api\FollowupMessageController;
use App\Http\Controllers\Api\WhatsAppController;
use App\Http\Controllers\Api\WorldAirportController;

use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TransactionTypeController;
use App\Http\Controllers\Api\CountryController;
use App\Models\Enquiry;
use App\Http\Controllers\Api\TwilioController;



Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
	return $request->user();
});


Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::post('forgot-password', [UserController::class, 'forgotPassword']);

Route::get('countries', [CountryController::class, 'countryList']);
Route::get('states/{countryId}', [CountryController::class, 'stateList']);
Route::get('cities/{countryId}/{stateId}', [CountryController::class, 'cityList']);

Route::middleware('auth:sanctum')->group(function () {
	Route::post('/send-whatsapp-message', [WhatsAppController::class, 'sendMessage']);
	Route::prefix('user')->group(function () {
		Route::get('user-list', [UserController::class, 'userList']);
		Route::post('register', [UserController::class, 'register']);
		Route::get('/send-verify-mail/{email}', [UserController::class, 'sendVerifyMail']);
		Route::post('update-user-details', [UserController::class, 'updateUser']);
		Route::post('change-user-password', [UserController::class, 'changeUserPassword']);
		Route::get('get-user-info', [UserController::class, 'getUserInfo']);
		Route::post('update-password', [UserController::class, 'updatePassword']);
		Route::get('logout', [UserController::class, 'logout']);
		Route::post('employee-register', [UserController::class, 'employeeRegister']);
		Route::get('find-by-id/{id}', [UserController::class, 'findById']);
		Route::get('verify/{id}', [UserController::class, 'verifyEmployee']);
		Route::delete('delete/{id}', [UserController::class, 'delete']);
		Route::get('employeeStatus/{id}', [UserController::class, 'employeeStatus']);
		Route::get('admin-employee-list', [UserController::class, 'adminUserList']);
	});

	Route::prefix('roles')->group(function () {
		Route::get('list', [RoleController::class, 'list']);
		Route::post('save-and-update', [RoleController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [RoleController::class, 'delete']);
	});
	Route::prefix('enquiries')->group(function () {
		Route::get('list', [EnquiryController::class, 'list']);
		Route::post('save-and-update', [EnquiryController::class, 'saveAndUpdateOne']);
		Route::post('save-and-update-multiple', [EnquiryController::class, 'saveAndUpdateMultiple']);
		Route::delete('delete/{id}', [EnquiryController::class, 'delete']);
		Route::get('assigned-enquiry-list', [EnquiryController::class, 'assignedEnquiryList']);
		Route::post('accept-or-reject/{id}', [EnquiryController::class, 'acceptOrReject']);
		Route::get('all-enquiries', [EnquiryController::class, 'allEnquiries']);

		Route::post('accept-or-reject-by-admin/{id}', [EnquiryController::class, 'acceptOrRejectByAdmin']);
	});
	Route::prefix('assign-enquiries')->group(function () {
		Route::get('list', [AssignEnquiryController::class, 'assignedEnquiryList']);
		Route::post('save-and-update', [AssignEnquiryController::class, 'saveAndUpdateOneAssignedEnquiry']);
		Route::post('save-and-update-multiple', [AssignEnquiryController::class, 'saveAndUpdateMultipleAssignEnquiry']);
	});
	Route::prefix('invoices')->group(function () {
		Route::get('list', [InvoiceController::class, 'list']);
		Route::post('save-and-update', [InvoiceController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [InvoiceController::class, 'delete']);
	});
	Route::prefix('enquiries')->group(function () {
		Route::post('search-flight', [EnquiryController::class, 'getFlightEnquiries']);
	});

	Route::prefix('enquiries-status')->group(function () {
		Route::get('list', [EnquiryStatusController::class, 'list']);
		Route::post('save-and-update', [EnquiryStatusController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [EnquiryStatusController::class, 'delete']);
	});
	Route::prefix('transaction-type')->group(function () {
		Route::get('list', [TransactionTypeController::class, 'list']);
		Route::post('save-and-update', [TransactionTypeController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [TransactionTypeController::class, 'delete']);
	});


	Route::prefix('departments')->group(function () {
		Route::get('list', [DepartmentController::class, 'list']);
		Route::post('save-and-update', [DepartmentController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [DepartmentController::class, 'delete']);
	});
	Route::prefix('branches')->group(function () {
		Route::get('list', [BranchController::class, 'list']);
		Route::post('save-and-update', [BranchController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [BranchController::class, 'delete']);
	});
	Route::prefix('air-lines')->group(function () {
		Route::get('list', [AirLineController::class, 'list']);
		Route::post('save-and-update', [AirLineController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [AirLineController::class, 'delete']);
	});
	Route::prefix('chat-message')->group(function () {
		Route::get('list', [RoleController::class, 'list']);
		Route::post('save-and-update', [RoleController::class, 'saveAndUpdate']);
	});
	Route::prefix('enquiries-source')->group(function () {
		Route::get('list', [EnquirySourceController::class, 'list']);
		Route::post('save-and-update', [EnquirySourceController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [EnquirySourceController::class, 'delete']);
	});
	Route::prefix('enquiries-payment-status')->group(function () {
		Route::get('list', [EnquiryPaymentStatusController::class, 'list']);
		Route::post('save-and-update', [EnquiryPaymentStatusController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [EnquiryPaymentStatusController::class, 'delete']);
	});
	Route::prefix('hotel-enquire')->group(function () {
		Route::get('list', [HotelEnquireController::class, 'list']);
		Route::post('save-and-update', [HotelEnquireController::class, 'saveAndUpdate']);
		Route::post('accept-or-reject/{id}', [HotelEnquireController::class, 'acceptOrReject']);
		Route::delete('delete/{id}', [HotelEnquireController::class, 'delete']);
		Route::get('created-by-employee', [HotelEnquireController::class, 'createdByUserList']);
		Route::post('search-hotel-enquiry', [HotelEnquireController::class, 'searchHotelEnquiries']);

		Route::post('accept-or-reject-by-admin/{id}', [HotelEnquireController::class, 'acceptOrRejectByAdmin']);
		Route::post('follow-up/{id}', [HotelEnquireController::class, 'followUp']);
	});
	Route::prefix('assign-hotel-enquiries')->group(function () {
		Route::get('list', [AssignHotelEnquiryController::class, 'assignedHotelEnquiryList']);
		Route::post('save-and-update', [AssignHotelEnquiryController::class, 'saveAndUpdateAssignedHotelEnquiry']);
	});
	Route::prefix('car-type')->group(function () {
		Route::get('list', [CarTypeController::class, 'list']);
		Route::post('save-and-update', [CarTypeController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [CarTypeController::class, 'delete']);
	});
	Route::prefix('car-enquire')->group(function () {
		Route::get('list', [CarEnquiryController::class, 'list']);
		Route::post('save-and-update', [CarEnquiryController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [CarEnquiryController::class, 'delete']);
	});
	Route::prefix('statics')->group(function () {
		Route::post('list', [StaticsController::class, 'list']);
		Route::post('profile-statics', [StaticsController::class, 'profileStatics']);
		Route::post('employee-flight/{id}', [EmployeeStaticController::class, 'emoloyeFlightStaticsList']);
	});
	Route::prefix('statics')->group(function () {
		Route::post('hotel-list', [StaticsController::class, 'hotelEnquiresList']);
		Route::post('profile-hotel-enquires', [StaticsController::class, 'profileStaticsHotel']);
		Route::post('employee-hotel/{id}', [EmployeeStaticController::class, 'emoloyeHotelStaticsList']);
		Route::post('employee-other-service/{id}', [EmployeeStaticController::class, 'emoloyeOtherServiceStaticsList']);
	});
	Route::prefix('other-service-statics')->group(function () {
		Route::post('all-list', [StaticsOfOtherServicesController::class, 'otherServiceEnquiresList']);
		Route::post('profile-static', [StaticsOfOtherServicesController::class, 'profileStatics']);
	});
	Route::prefix('flight-booking-enquiry')->group(function () {
		Route::get('list', [FlightBookingEnquiryController::class, 'list']);
		Route::post('save-and-update', [FlightBookingEnquiryController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [FlightBookingEnquiryController::class, 'delete']);
	});
	Route::prefix('other-services')->group(function () {
		Route::get('list', [OtherServiceController::class, 'list']);
		Route::post('save-and-update', [OtherServiceController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [OtherServiceController::class, 'delete']);
		Route::post('accept-or-reject/{id}', [OtherServiceController::class, 'acceptOrReject']);
		Route::post('accept-or-reject-by-admin/{id}', [OtherServiceController::class, 'acceptOrRejectByAdmin']);
	});
	Route::prefix('assign-other-services-enquiry')->group(function () {
		Route::get('list', [AssignOtherServiceController::class, 'assignedOtherEnquiryList']);
		Route::post('save-and-update', [AssignOtherServiceController::class, 'saveAndUpdateAssignedOtherEnquiry']);
	});

	Route::prefix('attendance')->group(function () {
		Route::get('list', [AttendanceController::class, 'list']);
		Route::get('mark-attendance', [AttendanceController::class, 'markAttendance']);
		Route::get('get-monthly/{month}', [AttendanceController::class, 'getMonthlyAttendance']);
		// Route::post('save-and-update', [AttendanceController::class, 'saveAndUpdate']);
		// Route::delete('delete/{id}',[AttendanceController::class, 'delete']);
	});
	Route::prefix('notifications')->group(function () {
		Route::post('send', [\App\Http\Controllers\Api\NotificationController::class, 'sendNotification']);
		Route::post('send-automatic', [\App\Http\Controllers\Api\NotificationController::class, 'sendAutomaticNotifications']);
		Route::get('list', [\App\Http\Controllers\Api\NotificationController::class, 'list']);
	});
	Route::prefix('suppliers')->group(function () {
		Route::get('list', [SupplierController::class, 'list']);
		Route::post('save-and-update', [SupplierController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [SupplierController::class, 'delete']);
	});
	Route::prefix('phone_numbers')->group(function () {
		Route::get('list', [PhoneNumberController::class, 'list']);
		Route::post('save-and-update', [PhoneNumberController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [PhoneNumberController::class, 'delete']);
		Route::post('/messages/send', [MessageController::class, 'sendMessages']);
	});
	Route::prefix('messages')->group(function () {
		Route::post('/send', [MessageController::class, 'sendMessages']);
	});


	Route::prefix('world-airports')->group(function () {
		Route::post('save-and-update', [WorldAirportController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [WorldAirportController::class, 'delete']);
	});

	Route::prefix('live-chat')->group(function () {
		// Route::get('/test-channel/{receiverId}', [\App\Http\Controllers\Api\ChatController::class, 'testChannel']);

		Route::get('/all-chats', [ChatController::class, 'allChats']);
		Route::get('/chats/{receiverId}', [ChatController::class, 'index']);
		Route::post('/chats', [ChatController::class, 'store']);
	});
	Route::prefix('flight-enquiry-follow-up')->group(function () {
		Route::post('/{id}', [FollowupMessageController::class, 'store']);
	});

	// Chat Routes

	// Route::get('get_chat/{sender}/{receiver}', [ChatController::class, 'get_chat']);
	// Route::get('store_chat/{sender}/{receiver}/{msg}', [ChatController::class, 'store_chat']);

	Route::prefix('feedback-form')->group(function () {
		Route::get('list', [FeedbackController::class, 'list']);
		Route::delete('delete/{id}', [FeedbackController::class, 'delete']);
	});
	Route::prefix('credit-card-auth-form')->group(function () {
		Route::get('list', [CreditCardFormController::class, 'list']);
		Route::delete('delete/{id}', [CreditCardFormController::class, 'delete']);
	});
	Route::prefix('customers')->group(function () {
		Route::get('list', [CustomerController::class, 'list']);
		Route::post('save-and-update', [CustomerController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [CustomerController::class, 'delete']);
	});
	Route::prefix('customers-invoices')->group(function () {
		Route::get('list', [CustomerInvoiceController::class, 'list']);
		Route::post('save-and-update', [CustomerInvoiceController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [CustomerInvoiceController::class, 'delete']);
	});

	Route::prefix('invoice-main')->group(function () {
		Route::get('list', [InvoiceMainController::class, 'list']);
		Route::post('save-and-update', [InvoiceMainController::class, 'saveAndUpdate']);
		Route::delete('delete/{id}', [InvoiceMainController::class, 'delete']);
		Route::post('send-invoice-mail/{id}/{type}', [InvoiceMainController::class, 'sendInvoiceMail'])->whereIn('type', ['airticket', 'insurance', 'hotel']);
		Route::get('custom-list', [InvoiceMainController::class, 'customList']);
		Route::get('pax-payment-list', [InvoiceMainController::class, 'paxPaymentList']);
	});
});

Route::prefix('flight-booking-enquiry')->group(function () {
	Route::post('save-and-update', [FlightBookingEnquiryController::class, 'saveAndUpdate']);
});
Route::prefix('feedback-form')->group(function () {
	Route::post('save-and-update', [FeedbackController::class, 'saveAndUpdate']);
});
Route::prefix('credit-card-auth-form')->group(function () {
	Route::post('save-and-update', [CreditCardFormController::class, 'saveAndUpdate']);
});

Route::prefix('air-lines')->group(function () {
	Route::get('list', [AirLineController::class, 'list']);
});

Route::prefix('world-airports')->group(function () {
	Route::get('/list', [WorldAirportController::class, 'list']);
});

Route::post('/send-whatsapp', [TwilioController::class, 'sendMessage']);
// Route::get('send-invoice-mail/{id}/{type}', [InvoiceMainController::class, 'sendInvoiceMail'])->whereIn('type', ['airticket', 'insurance', 'hotel']);