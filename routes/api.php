<?php

use App\Http\Controllers\API\AdminControlController;
use App\Http\Controllers\API\AgentController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BricksController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\DeviceTokenController;
use App\Http\Controllers\API\GenerateQuotationController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\MaterialTypeController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\OtherUtilitiesController;
use App\Http\Controllers\API\OtherUtilitiesSubController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\PropertyController;
use App\Http\Controllers\API\PurchaseBillController;
use App\Http\Controllers\API\MaterialEstimationRequestController;
use App\Http\Controllers\API\UnitController;
use App\Http\Controllers\API\SalesBillController;
use App\Http\Controllers\API\SiteController;
use App\Http\Controllers\API\SubContractorController;
use App\Http\Controllers\API\SubcontractorTypeController;
use App\Http\Controllers\API\SupervisorController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\API\DrawingController;
use App\Http\Controllers\API\TicketMessageController;
use App\Http\Controllers\API\TicketController;
use App\Models\MaterialOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your aggregator. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

//test
Route::post('/send-notification', [NotificationController::class, 'send']);

Route::middleware('auth:sanctum')->group(function () {

    //Dashboard
      Route::get('/dashboard', [DashboardController::class, 'getDashboard']);

  //Device Token Store
  Route::post('/save-device-token', [DeviceTokenController::class, 'store']);

  //Supervisor Location (mobile app reports current GPS position)
  Route::post('/save-location', [LocationController::class, 'store']);

  //Sites
  Route::get('/site-management', [SiteController::class, 'index']);
 
 
  Route::get('/site-detail/{id}', [SiteController::class, 'siteDetail']);
  Route::post('/site-payment-add', [SiteController::class, 'addPayment']);
  Route::post('/site-payment-update/{id}', [SiteController::class, 'updatePayment']);
  Route::delete('/site-payment-delete/{id}', [SiteController::class, 'deletePayment']);
  Route::get('/site-payment-history/{siteId}', [SiteController::class, 'paymentHistory']);
  Route::get('/site-payment-history/{siteId}/export', [SiteController::class, 'exportPaymentHistory']);
  Route::get('/site-payment-summary/{siteId}', [SiteController::class, 'paymentSummary']);
  Route::get('/site-payment-pdf/{id}', [SiteController::class, 'downloadPaymentPdf']);
  Route::get('/site-payment-whatsapp/{id}', [SiteController::class, 'sendPaymentWhatsapp']);
  Route::get('/site-payment-mail/{id}', [SiteController::class, 'sendPaymentMail']);

  //Sales Bill
  Route::get('/sales-bill-form/{siteId}', [SalesBillController::class, 'getDetails']);
  Route::post('/sales-bill-add', [SalesBillController::class, 'store']);
  Route::get('/sales-bill/{id}', [SalesBillController::class, 'show']);

  //Purchase Bill
  Route::get('/purchase-bill-form/{siteId}', [PurchaseBillController::class, 'getDetails']);
  Route::get('/purchase-bills/{siteId}', [PurchaseBillController::class, 'index']);
  Route::post('/purchase-bill-add', [PurchaseBillController::class, 'store']);
  Route::get('/purchase-bill/{id}', [PurchaseBillController::class, 'show']);
  Route::patch('/purchase-bill-update/{id}', [PurchaseBillController::class, 'update']);
  Route::delete('/purchase-bill-delete/{id}', [PurchaseBillController::class, 'destroy']);

  //Material Estimation
  Route::get('/material-estimation-form/{siteId}', [MaterialEstimationRequestController::class, 'index']);
  Route::post('/material-estimation-add', [MaterialEstimationRequestController::class, 'store']);
  Route::get('/material-estimation/{id}', [MaterialEstimationRequestController::class, 'show']);
  Route::patch('/material-estimation-update/{id}', [MaterialEstimationRequestController::class, 'update']);
  Route::delete('/material-estimation-delete/{id}', [MaterialEstimationRequestController::class, 'destroy']);

  //Unit Master
  Route::get('/unit-master', [UnitController::class, 'index']);
  Route::post('/unit-add', [UnitController::class, 'store']);
  Route::patch('/unit-update/{id}', [UnitController::class, 'update']);
  Route::delete('/unit-delete/{id}', [UnitController::class, 'destroy']);

  //Supervisor Locations
  Route::get('/supervisor-locations', [SupervisorController::class, 'locations']);
  Route::get('/supervisor-location/{id}', [SupervisorController::class, 'locationDetail']);

  //Attendance
  Route::delete('/attendance-delete/{id}', [AttendanceController::class, 'destroy']);

  //Material Details
  Route::get('/material-detail/{siteId}', [MaterialController::class, 'getMaterial']); //material quantity & vlaues
  Route::post('/material/{siteId}/{materialType}', [MaterialController::class, 'materialData']); //bricks, sand list
  Route::post('/request-order', [MaterialController::class, 'materialRequest']); //add request
  Route::get('/my-material-requests/{siteId}', [MaterialController::class, 'myRequests']); //supervisor: view own request statuses
  Route::get('/material-request-list/{siteId}', [MaterialController::class, 'requestList']); //admin: view all supervisor requests for a site
  Route::post('/material-request-approve/{id}', [MaterialController::class, 'approveRequest']); //admin: approve a request
  Route::post('/material-request-reject/{id}', [MaterialController::class, 'rejectRequest']); //admin: reject a request (admin_remark optional)

  //Material Types (dynamic material categories)
  Route::post('/material-type-add', [MaterialTypeController::class, 'store']); //multipart: name, image
  Route::delete('/material-type-delete/{id}', [MaterialTypeController::class, 'delete']); //remove a dynamically-added material card
  Route::delete('/material-type-hide/{slug}', [MaterialTypeController::class, 'hideFixed']); //hide a fixed/built-in material card
  Route::post('/add-order', [MaterialController::class, 'materialOrder']);    // add order
  Route::post('/material-payment', [MaterialController::class, 'materialPayment']); // record vendor payment for a material order
  Route::get('/materials-unit', [MaterialController::class, 'index']);
  Route::get('/material-export', [MaterialController::class, 'exportMaterial']);
  Route::post('/material-update/{id}', [MaterialController::class, 'updateMaterial']);
  Route::delete('/material-delete/{id}', [MaterialController::class, 'destroyMaterial']);
  Route::get('/material-order/{id}/pdf', [MaterialController::class, 'orderPdf']);
  //Material Other Utilities
  Route::get('/site-utilities/{id}', [OtherUtilitiesController::class, 'index']); //siteId
  Route::post('/utilities-add', [OtherUtilitiesController::class, 'store']);
  Route::post('/update-utility/{id}', [OtherUtilitiesController::class, 'update']);
  Route::delete('/utility-delete/{id}', [OtherUtilitiesController::class, 'destroy']);
  Route::get('/site-utilities/{id}/export', [OtherUtilitiesController::class, 'export']);

  //Subcontractor
  Route::get('/subcontractor-detail/{siteId}', [SubcontractorController::class, 'getSubcontractor']);
        
  Route::post('/subcontractor/{siteId}/{subcontractorType}', [SubcontractorController::class, 'subcontractorData']);
  
  Route::post('/add-service', [SubcontractorController::class, 'subcontractorService']);
  Route::post('/service-update/{id}', [SubContractorController::class, 'updateService']);
  Route::delete('/service-delete/{id}', [SubContractorController::class, 'destroyService']);
  Route::get('/subcontractor-export', [SubContractorController::class, 'exportSubcontractor']);

  //Subcontractor Types (dynamic subcontractor categories shown on SubContractor Details grid)
  Route::post('/subcontractor-type-add', [SubcontractorTypeController::class, 'store']); //"+ Add SubContractor" button, multipart: name, image
  Route::delete('/subcontractor-type-delete/{id}', [SubcontractorTypeController::class, 'delete']); //remove a dynamically-added card (× button)
  Route::delete('/subcontractor-type-hide/{slug}', [SubcontractorTypeController::class, 'hideFixed']); //hide a fixed/built-in card (× button)

  //Other Utilities Subcontractor
  Route::get('/site-subutilities/{id}', [OtherUtilitiesSubController::class, 'index']);
  Route::post('/subutilities-add', [OtherUtilitiesSubController::class, 'store']);
  Route::post('/update-subutility/{id}', [OtherUtilitiesSubController::class, 'update']);
  Route::delete('/subutility-delete/{id}', [OtherUtilitiesSubController::class, 'destroy']);
  Route::get('/site-subutilities/{id}/export', [OtherUtilitiesSubController::class, 'export']);

  //Customer Management
  Route::get('/customer-management', [CustomerController::class, 'index']);
  Route::get('/customer-edit/{id}', [CustomerController::class, 'edit']);
  Route::post('/customer-lookup', [CustomerController::class, 'lookupByMobile']);
  Route::post('/customer-update', [CustomerController::class, 'update']);
  Route::delete('/customer-delete/{id}', [CustomerController::class, 'delete']);

  //Supervisor Management
  Route::get('/supervisor-management', [SupervisorController::class, 'index']);
  Route::post('/supervisor-add', [SupervisorController::class, 'store']);
  Route::post('/supervisor-update', [SupervisorController::class, 'update']);
  Route::delete('/supervisor-delete/{id}', [SupervisorController::class, 'delete']);
  Route::get('/supervisor-permissions/{id}', [SupervisorController::class, 'getPermissions']);
  Route::get('/supervisor-sites/{id}', [SupervisorController::class, 'getSites']);

  //Vendor Management
  Route::get('/vendor-management', [VendorController::class, 'index']);
  Route::post('/vendor-add', [VendorController::class, 'store']);
  Route::patch('/vendor-update/{id}', [VendorController::class, 'update']);
  Route::delete('/vendor-delete/{id}', [VendorController::class, 'delete']);
  Route::get('/vendors/search', [VendorController::class, 'search']);

  //Vendor Dashboard
  Route::get('/vendor-dashboard', [VendorController::class, 'dashboard']);
  Route::get('/vendor-material-orders/{vendorId}', [VendorController::class, 'materialOrders']);
  Route::get('/paydetail/{vendorId}', [VendorController::class, 'getPayDetailsForm']);
  Route::post('paydetail-update', [VendorController::class, 'paydetailUpdate']); //only for opening balance
  Route::post('payment-add', [VendorController::class, 'addPayment']);
  Route::patch('payment-update/{id}', [VendorController::class, 'updatePayment']); //payment history: edit action button
  Route::delete('payment-delete/{id}', [VendorController::class, 'deletePayment']);
  Route::get('payment-history/{vendorId}', [VendorController::class, 'paymentHistory']);
  Route::get('payment-history/{vendorId}/export', [VendorController::class, 'exportPaymentHistory']);

  //Subcontractor Management
  Route::get('/subcontractor-management', [SubcontractorController::class, 'index']);
  Route::post('/subcontractor-add', [SubcontractorController::class, 'store']);
  Route::patch('/subcontractor-update/{id}', [SubcontractorController::class, 'update']);
  Route::delete('/subcontractor-delete/{id}', [SubcontractorController::class, 'delete']);
  Route::get('/subcontractors/search', [SubcontractorController::class, 'search']);
    
  //Subcontractor Dashboard Plumber
  Route::get('/subcontractor-dashboard', [SubcontractorController::class, 'dashboard']);
  Route::get('/subpaydetail/{subcontractorId}', [SubcontractorController::class, 'getPayDetailsForm']);
  Route::post('subpaydetail-update', [SubcontractorController::class, 'subcontractorpayUpdate']); //only for opening balance
  Route::post('subpayment-add', [SubcontractorController::class, 'addPayment']);
  Route::patch('subpayment-update/{id}', [SubcontractorController::class, 'updatePayment']); //petty cash / rental management / payment history: edit action button
  Route::delete('subpayment-delete/{id}', [SubcontractorController::class, 'deletePayment']); //petty cash / rental management / payment history: delete action button
  Route::get('subpayment-history/{subcontractorId}', [SubcontractorController::class, 'paymentHistory']);
  Route::get('subpayment-history/{subcontractorId}/export', [SubcontractorController::class, 'exportPaymentHistory']);
  Route::get('subcontractor-orders/{subcontractorId}', [SubcontractorController::class, 'subcontractorOrders']);

  //Petty Cash / Rental Management (site-scoped wrapper subcontractors)
  Route::get('/subcontractor-petty-cash/{siteId}', [SubcontractorController::class, 'pettyCashPaymentDetail']);
  Route::get('/subcontractor-petty-cash/{siteId}/export', [SubcontractorController::class, 'exportPettyCash']);
  Route::get('/subcontractor-rental-management/{siteId}', [SubcontractorController::class, 'rentalManagementPaymentDetail']);
  Route::get('/subcontractor-rental-management/{siteId}/export', [SubcontractorController::class, 'exportRentalManagement']);

  //Agent
  Route::get('/agent-management', [AgentController::class, 'index']);
  Route::post('/agent-add', [AgentController::class, 'store']);
  Route::patch('agent-update/{id}', [AgentController::class, 'update']);
  Route::delete('/agent/inactivate/{id}', [AgentController::class, 'delete']);

  //Property list
  Route::get('/property-management', [PropertyController::class, 'index']);
  Route::post('/property-add', [PropertyController::class, 'store']);

  //Quotation
  Route::post('/quotation-add', [GenerateQuotationController::class, 'store']);
Route::get('/quotations', [GenerateQuotationController::class, 'index']);
  //Profile setting
  Route::get('/profile', [ProfileController::class, 'show']);
  Route::post('/profile-update', [ProfileController::class, 'update']);
//customer login

 Route::post('/admin/task/update', [ChecklistController::class, 'adminUpdateTask']);

  //Checklist Add
  Route::post('/checklist-add', [ChecklistController::class, 'apiStore']);
  Route::post('/checklist-task-add', [ChecklistController::class, 'storeTaskItem']); //add single task to an existing stage

  //Checklist Edit
  Route::post('/checklist-stage-update/{id}', [ChecklistController::class, 'updateStage']);
  Route::post('/checklist-task-update/{id}', [ChecklistController::class, 'updateTaskName']);
  Route::post('/checklist-update/{id}', [ChecklistController::class, 'update']); //replace stage + task_list

  //Checklist Delete
  Route::delete('/checklist-stage/{id}', [ChecklistController::class, 'destroy']);
  Route::delete('/checklist-task/{id}', [ChecklistController::class, 'deleteTask']);

  //Checklist Manage / Reorder (admin template management, mirrored for mobile)
  Route::get('/checklist-manage', [ChecklistController::class, 'manage']);
  Route::post('/checklist-tasks-reorder', [ChecklistController::class, 'reorderTasks']);
  Route::post('/checklist-stages-reorder', [ChecklistController::class, 'reorderChecklists']);

  //Task Media (admin review screens, mirrored for mobile)
  Route::get('/task-media/{siteId}/{taskId}', [ChecklistController::class, 'viewTaskMedia']);
  Route::put('/task-media-update/{id}', [ChecklistController::class, 'updateTaskMedia']);
  Route::delete('/task-media-delete/{id}/{siteId}', [ChecklistController::class, 'deleteByRemarks']);

});
Route::post('/login-customer', [AuthController::class, 'loginWithMobile']);
//checklist
Route::get('/checklists/{siteId}', [ChecklistController::class, 'getChecklistForSite']);
Route::get('/supervisor/checklists/{siteId}', [ChecklistController::class, 'supervisorChecklist']);
Route::post('/task-media/store', [ChecklistController::class, 'taskmediastore']);

   


Route::post('/ticket-submit', [TicketController::class, 'store']);
Route::post('tickets/messages-store', [TicketMessageController::class, 'storeMessage']);
Route::post('/tickets/{id}/messages-store-supervisor', [TicketMessageController::class, 'storeSupervisorMessage']);
Route::get('/ticket/{id}/messages', [TicketController::class, 'getMessages']);
Route::get('/get-tickets-by-site/{site_id}', [TicketController::class, 'getTicketsBySite']);
//admin supervisor remark
Route::get('/checklist-admin-supervisor-remarks', [ChecklistController::class, 'getTaskMedia']);
// Drawing
Route::get('/drawings/by-site/{site_id}', [DrawingController::class, 'getDrawingsBySite']);
Route::post('/drawings', [DrawingController::class, 'store']);
Route::get('/drawings/download/{id}', [DrawingController::class, 'download'])->name('drawings.download');
Route::get('/test', function() {
    return response()->json(['status' => 'ok']);
});
Route::delete('/drawing/{id}', [DrawingController::class, 'destroy']);
Route::post('/drawings/{id}/whatsapp', [DrawingController::class, 'shareWhatsapp']);



 Route::post('/create_sites', [SiteController::class, 'store']);
 Route::get('/all_sites', [SiteController::class, 'siteview']);
 Route::get('/sites/{id}', [SiteController::class, 'edit']);        // Get site + customer details for edit
 Route::post('/sites/update', [SiteController::class, 'update']);      // Update site + customer
Route::delete('/sites/{id}', [SiteController::class, 'destroy']);
Route::get('/sites/{id}/full-report', [SiteController::class, 'exportFullReport']);



 Route::post('/quotation/store', [GenerateQuotationController::class, 'store']);
Route::get('/quotation/{id}', [GenerateQuotationController::class, 'show']);
Route::delete('/quotation/detail/{id}', [GenerateQuotationController::class, 'removeDetail']);
Route::get('/quotation/pdf/{id}', [GenerateQuotationController::class, 'generatePdf']);
Route::get('/default-items', [GenerateQuotationController::class, 'defaultItemList']);
Route::get('/quotation-history', [GenerateQuotationController::class, 'history']);


// attendance
Route::get('/attendance/{siteId}', [AttendanceController::class, 'index']);
Route::post('/add-wages', [AttendanceController::class, 'addWages']);
Route::post('/add-attendance', [AttendanceController::class, 'addAttendance']);
Route::get('/attendance', [AttendanceController::class, 'apiAttendanceExport']);
Route::prefix('attendance')->group(function () {
Route::post('/edit-page', [AttendanceController::class, 'editPage']);
Route::post('/update', [AttendanceController::class, 'updateAttendance']);
Route::get('/sites/{siteId}/attendance-by-date', [AttendanceController::class, 'attendanceByDate']);
Route::get('/{siteId}/check-date', [AttendanceController::class, 'checkDate']);

});

Route::post('/wages/update', [AttendanceController::class, 'updateWages']);
Route::post('/wages/delete', [AttendanceController::class, 'deleteWages']);
Route::post('/update-attendance-wages', [AttendanceController::class, 'updateAttendanceAndWages']);
Route::delete('/attendance-delete-date/{siteId}/{date}', [AttendanceController::class, 'deleteByDate']);
