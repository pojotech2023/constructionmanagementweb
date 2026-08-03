<?php

use App\Http\Controllers\Admin\AdminControlController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BricksController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceTokenController;
use App\Http\Controllers\Admin\Material\SandController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\MaterialTypeController;
use App\Http\Controllers\Admin\SubcontractorTypeController;
use App\Http\Controllers\Admin\OtherUtilitiesController;
use App\Http\Controllers\Admin\OtherUtilitiesSubController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\SalesBillController;
use App\Http\Controllers\Admin\PurchaseBillController;
use App\Http\Controllers\Admin\MaterialEstimationRequestController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\SubcontractorController;
use App\Http\Controllers\Admin\SupervisorCreationController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DrawingController;
use App\Models\Attendance;
use App\Models\OtherUtilities;
use App\Models\OtherUtilitiesSub;
use App\Models\Subcontractor;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
// ----------------------------------------Web----------------------------------------------
Route::get('/', function () {
    return Auth::guard('admin')->check()
        ? redirect()->route('admin.dashboard')
        : redirect('admin/login');
})->name('home');


Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/product', function () {
    return view('product');
})->name('product');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/pdf', function () {
    return view('admin.helper.pdf_quotation');
})->name('pdf');
//------------------------------------------ Admin-----------------------------------------------------
Route::prefix('admin')->group(function () {

    // Login and Logout
    Route::get('/', function () {
        return Auth::guard('admin')->check()
            ? redirect()->route('admin.dashboard')
            : redirect('admin/login');
    });
    Route::get('/login', [AuthController::class, 'showLoginForm']);
    Route::post('/login', [AuthController::class, 'adminLogin'])->name('admin.login');
    Route::post('/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');
    

    Route::post('/save-device-token', [DeviceTokenController::class, 'store'])->name('save.device.token');

    Route::middleware(['auth:admin', 'checkUserRole:Admin,Supervisor'])->group(function () {
     
     
        Route::get('/dashboard', [DashboardController::class, 'getDashboard'])->name('admin.dashboard');
        Route::get('/pricing-plan', function () {
            return view('admin.pricing');
        })->name('admin.pricing');

        // back-compat route for /admin/public/admin/*
        Route::get('/public/admin/{path?}', function ($path = null) {
            if ($path) {
                return redirect('/admin/' . $path);
            }
            return redirect()->route('admin.dashboard');
        })->where('path', '.*');

       



        //Customer Management
        Route::get('/customer-management', [CustomerController::class, 'index'])->name('customer.list');
        Route::get('/customer-edit/{id}', [CustomerController::class, 'edit'])->name('customer.edit');
        Route::patch('/customer-update/{id}', [CustomerController::class, 'update'])->name('customer.update');
        Route::delete('/customer-delete/{id}', [CustomerController::class, 'delete'])->name('customer.delete');
        Route::get('/customer-lookup', [CustomerController::class, 'lookupByMobile'])->name('customer.lookup');

        //Vendor Management
        Route::get('/vendor-management', [VendorController::class, 'index'])->name('vendor.list');
        Route::post('/vendor-add', [VendorController::class, 'store'])->name('vendor.add');
        Route::post('/vendor-update', [VendorController::class, 'update'])->name('vendor.update');
        Route::delete('/vendor-delete/{id}', [VendorController::class, 'delete'])->name('vendor.delete');
        Route::get('/vendors/search', [VendorController::class, 'search'])->name('vendors.search');

        //Vendor Dashboard
        Route::get('/vendor-dashboard', [VendorController::class, 'dashboard'])->name('vendor.dashboard');
        Route::get('/paydetail/{vendorId}', [VendorController::class, 'getPayDetailsForm'])->name('vendor.payDetailForm');
        //opening Balance
        Route::post('pay-update', [VendorController::class, 'vendorpayUpdate'])->name('paydetail.update');
        Route::post('payment-add', [VendorController::class, 'addPayment'])->name('payment.add');
        Route::patch('payment-update/{id}', [VendorController::class, 'updatePayment'])->name('payment.update');
        Route::delete('payment-delete/{id}', [VendorController::class, 'deletePayment'])->name('payment.delete');
        Route::get('payment-history/{vendorId}', [VendorController::class, 'paymentHistory'])->name('payment.history');
        Route::get('payment-history/{vendorId}/export', [VendorController::class, 'exportPaymentHistory'])->name('payment.history.export');

        //Subcontractor Management
        Route::get('/subcontractor-management', [SubcontractorController::class, 'index'])->name('subcontractor.list');
        Route::post('/subcontractor-add', [SubcontractorController::class, 'store'])->name('subcontractor.add');
        Route::post('/subcontractor-update', [SubcontractorController::class, 'update'])->name('subcontractor.update');
        Route::delete('/subcontractor-delete/{id}', [SubcontractorController::class, 'delete'])->name('subcontractor.delete');
        Route::get('/subcontractors/search', [SubcontractorController::class, 'search'])->name('subcontractors.search');

        //Subcontractor Dashboard
        Route::get('/subcontractor-dashboard', [SubcontractorController::class, 'dashboard'])->name('subcontractor.dashboard');
        Route::get('/subpaydetail/{subcontractorId}', [SubcontractorController::class, 'getPayDetailsForm'])->name('subcontractor.payDetailForm');
        //opening Balance
        Route::post('subpay-update', [SubcontractorController::class, 'subcontractorpayUpdate'])->name('subpaydetail.update');
        Route::post('subpayment-add', [SubcontractorController::class, 'addPayment'])->name('subpayment.add');
        Route::patch('subpayment-update/{id}', [SubcontractorController::class, 'updatePayment'])->name('subpayment.update');
        Route::delete('subpayment-delete/{id}', [SubcontractorController::class, 'deletePayment'])->name('subpayment.delete');
        Route::get('subpayment-history/{subcontractorId}', [SubcontractorController::class, 'paymentHistory'])->name('subpayment.history');
        Route::get('subpayment-history/{subcontractorId}/export', [SubcontractorController::class, 'exportPaymentHistory'])->name('subpayment.history.export');

        //Supervisor Creation
        Route::get('/supervisor-management', [SupervisorCreationController::class, 'index'])->name('supervisor.list');
        Route::post('/supervisor-add', [SupervisorCreationController::class, 'store'])->name('supervisor.add');
        Route::post('/supervisor-update', [SupervisorCreationController::class, 'update'])->name('supervisor.update');
        Route::delete('/supervisor-delete/{id}', [SupervisorCreationController::class, 'delete'])->name('supervisor.delete');
        Route::get('/supervisor-permissions/{id}', [SupervisorCreationController::class, 'getPermissions'])->name('supervisor.permissions.get');
        Route::post('/supervisor-permissions/{id}', [SupervisorCreationController::class, 'savePermissions'])->name('supervisor.permissions.save');
        Route::get('/supervisor-locations', [SupervisorCreationController::class, 'locations'])->name('supervisor.locations');
        Route::get('/supervisor-location/{id}', [SupervisorCreationController::class, 'location'])->name('supervisor.location');

        //Admin Control (sidebar / feature visibility toggles)
        Route::middleware(['checkUserRole:Admin'])->group(function () {
            Route::get('/admin-control', [AdminControlController::class, 'index'])->name('admin.control.index');
            Route::post('/admin-control', [AdminControlController::class, 'update'])->name('admin.control.update');

            //Unit Master
            Route::get('/unit-master', [UnitController::class, 'index'])->name('unit.list');
            Route::post('/unit-add', [UnitController::class, 'store'])->name('unit.add');
            Route::patch('/unit-update/{id}', [UnitController::class, 'update'])->name('unit.update');
            Route::delete('/unit-delete/{id}', [UnitController::class, 'delete'])->name('unit.delete');
        });

        //Site Management
        Route::get('/site-form', [SiteController::class, 'getForm'])->name('site.form');
        Route::get('/site-management', [SiteController::class, 'index'])->name('sitemanagement.list');
        Route::post('/site-add', [SiteController::class, 'store'])->name('sitemanagement.add');
        Route::get('/site-edit/{id}', [SiteController::class, 'edit'])->name('sitemanagement.edit');
        Route::patch('/site-update/{id}', [SiteController::class, 'update'])->name('sitemanagement.update');
        Route::patch('/site-inactivate/{id}', [SiteController::class, 'delete'])->name('sitemanagement.delete');
        Route::get('/site-detail/{id}', [SiteController::class, 'siteDetail'])->name('site.detail');
        Route::get('/site-detail/{id}/full-report', [SiteController::class, 'exportFullReport'])->name('site.full-report.export');
        Route::get('/sales-bill-form/{siteId}', [SalesBillController::class, 'getForm'])->name('salesBill.form');
        Route::post('/sales-bill-add', [SalesBillController::class, 'store'])->name('salesBill.add');
        Route::get('/purchase-bill-form/{siteId}', [PurchaseBillController::class, 'getForm'])->name('purchaseBill.form');
        Route::post('/purchase-bill-add', [PurchaseBillController::class, 'store'])->name('purchaseBill.add');
        Route::get('/material-estimation-form/{siteId}', [MaterialEstimationRequestController::class, 'getForm'])->name('materialEstimation.form');
        Route::post('/material-estimation-add', [MaterialEstimationRequestController::class, 'store'])->name('materialEstimation.add');
        Route::patch('/material-estimation-update/{id}', [MaterialEstimationRequestController::class, 'update'])->name('materialEstimation.update');
        Route::delete('/material-estimation-delete/{id}', [MaterialEstimationRequestController::class, 'delete'])->name('materialEstimation.delete');
        Route::get('/site-payment-detail/{id}', [SiteController::class, 'paymentDetail'])->name('site.paymentDetail');
        Route::post('/site-payment-add', [SiteController::class, 'addPayment'])->name('site.payment.add');
        Route::get('/site-payment-history/{id}', [SiteController::class, 'paymentHistory'])->name('site.payment.history');
        Route::patch('/site-payment-update/{id}', [SiteController::class, 'updatePayment'])->name('site.payment.update');
        Route::delete('/site-payment-delete/{id}', [SiteController::class, 'deletePayment'])->name('site.payment.delete');
        Route::get('/site-payment-pdf/{id}', [SiteController::class, 'downloadPaymentPdf'])->name('site.payment.pdf');
        Route::get('/site-payment-whatsapp/{id}', [SiteController::class, 'sendPaymentWhatsapp'])->name('site.payment.whatsapp');
        Route::get('/site-payment-mail/{id}', [SiteController::class, 'sendPaymentMail'])->name('site.payment.mail');
        Route::get('/site-payment-export/{id}', [SiteController::class, 'exportPaymentHistory'])->name('site.payment.export');

        //Attendance
        Route::get('/attendance/{siteId}', [AttendanceController::class, 'index'])->name('attendance');
        Route::post('/add-wages', [AttendanceController::class, 'addWages'])->name('add.wages');
        Route::post('/add-attendance', [AttendanceController::class, 'addAttendance'])->name('add.attendance');
        Route::get('/edit-attendance-wages/{site_id}/{date}', [AttendanceController::class, 'editPage'])
     ->name('edit.attendance.wages');

        Route::post('/update-attendance', [AttendanceController::class, 'updateAttendance'])
            ->name('update.attendance');
        Route::post('/update-wages', [AttendanceController::class, 'updateWages'])
        ->name('update.wages');
        Route::post('/update-attendance-wages', [AttendanceController::class, 'updateAttendanceAndWages'])
            ->name('update.attendance.wages');

        // Delete single attendance record
        Route::delete('/attendance-delete/{id}', [AttendanceController::class, 'delete'])->name('attendance.delete');

        // Delete all attendance records for a date (month view)
        Route::delete('/attendance-delete-date/{siteId}/{date}', [AttendanceController::class, 'deleteByDate'])->name('attendance.delete.date');

        Route::get('attendance/{siteId}/wages-form', [AttendanceController::class, 'getWagesForm'])->name('wages.form');
        Route::get('/attendance/{siteId}/form', [AttendanceController::class, 'getAttendanceForm'])->name('attendance.form');
        Route::get('/attendance/{siteId}/check-date', [AttendanceController::class, 'checkDate'])->name('attendance.checkDate');

        //Materials
        Route::get('/material-detail/{siteId}', [MaterialController::class, 'getMaterial'])->name('material.detail');

        Route::get('/material/{siteId}/{materialType}', [MaterialController::class, 'index'])->name('material');
        Route::post('/material/get-data/{siteId}', [MaterialController::class, 'getMaterialData'])->name('material.getData');

        Route::get('/material-requestForm/{siteId}/{materialType}', [MaterialController::class, 'getRequestForm'])->name('material.requestForm');
        Route::post('/request-order', [MaterialController::class, 'materialRequest'])->name('add.request');
        Route::get('/material-request-list/{siteId}', [MaterialController::class, 'requestList'])->name('material.requestList');
        Route::patch('/material-request-status/{id}', [MaterialController::class, 'updateRequestStatus'])->name('material.request.updateStatus');

        Route::get('/material-orderForm/{siteId}/{materialType}', [MaterialController::class, 'getOrderForm'])->name('material.orderForm');
        Route::post('/add-order', [MaterialController::class, 'materialOrder'])->name('add.order');
        Route::patch('/material-order-update/{id}', [MaterialController::class, 'updateOrder'])->name('material.updateOrder');
        Route::delete('/material-order-delete/{id}', [MaterialController::class, 'deleteOrder'])->name('material.deleteOrder');
        Route::get('/material-order/{id}/pdf', [MaterialController::class, 'orderPdf'])->name('material.order.pdf');

        //Material Types (dynamic material categories)
        Route::post('/material-type-add', [MaterialTypeController::class, 'store'])->name('materialType.add');
        Route::delete('/material-type-delete/{id}', [MaterialTypeController::class, 'delete'])->name('materialType.delete');
        Route::delete('/material-type-hide/{slug}', [MaterialTypeController::class, 'hideFixed'])->name('materialType.hideFixed');

        //Subcontractor Types (dynamic subcontractor categories)
        Route::post('/subcontractor-type-add', [SubcontractorTypeController::class, 'store'])->name('subcontractorType.add');
        Route::delete('/subcontractor-type-delete/{id}', [SubcontractorTypeController::class, 'delete'])->name('subcontractorType.delete');
        Route::delete('/subcontractor-type-hide/{slug}', [SubcontractorTypeController::class, 'hideFixed'])->name('subcontractorType.hideFixed');

        //Other Utilities
        Route::get('/site-utilities/{id}', [OtherUtilitiesController::class, 'index'])->name('site.utilities');
        Route::post('/utilities-add', [OtherUtilitiesController::class, 'store'])->name('utilities.add');
        Route::patch('/utilities-update/{id}', [OtherUtilitiesController::class, 'update'])->name('utilities.update');
        Route::delete('/utilities-delete/{id}', [OtherUtilitiesController::class, 'delete'])->name('utilities.delete');
        Route::get('/site-utilities/{id}/export', [OtherUtilitiesController::class, 'export'])->name('utilities.export');

        // Material export (CSV)
        Route::get('/material/{siteId}/{materialType}/export', [\App\Http\Controllers\Admin\MaterialController::class, 'export'])->name('material.export');

        // Bricks export (CSV)
        Route::get('/bricks/{siteId}/export', [\App\Http\Controllers\Admin\BricksController::class, 'export'])->name('bricks.export');

        //Subcontractor
        Route::get('/subcontractor-detail/{siteId}', [SubcontractorController::class, 'getSubcontractor'])->name('subcontractor.detail');
        Route::get('/subcontractor-petty-cash/{siteId}', [SubcontractorController::class, 'pettyCashPaymentDetail'])->name('subcontractor.pettyCash');
        Route::get('/subcontractor-petty-cash/{siteId}/export', [SubcontractorController::class, 'exportPettyCash'])->name('subcontractor.pettyCash.export');
        Route::get('/subcontractor-rental-management/{siteId}', [SubcontractorController::class, 'rentalManagementPaymentDetail'])->name('subcontractor.rentalManagement');
        Route::get('/subcontractor-rental-management/{siteId}/export', [SubcontractorController::class, 'exportRentalManagement'])->name('subcontractor.rentalManagement.export');

        Route::get('/subcontractor/{siteId}/{subcontractorType}', [SubcontractorController::class, 'getSubcontractorDetails'])->name('subcontractor.detailList');
        Route::post('/subcontractor/get-data/{siteId}', [SubcontractorController::class, 'getSubcontractorData'])->name('subcontractor.getData');

        Route::get('/subcontractor-serviceForm/{siteId}/{subcontractorType}', [SubcontractorController::class, 'getServiceForm'])->name('subcontractor.serviceForm');
        Route::post('/add-service', [SubcontractorController::class, 'subcontractorService'])->name('add.service');
        Route::patch('/subcontractor-service-update/{id}', [SubcontractorController::class, 'updateService'])->name('subcontractor.service.update');
        Route::delete('/subcontractor-service-delete/{id}', [SubcontractorController::class, 'deleteService'])->name('subcontractor.service.delete');

        //Other Utilities Subcontractor
        Route::get('/site-subutilities/{id}', [OtherUtilitiesSubController::class, 'index'])->name('site.subutilities');
        Route::post('/subutilities-add', [OtherUtilitiesSubController::class, 'store'])->name('subutilities.add');
        Route::patch('/subutilities-update/{id}', [OtherUtilitiesSubController::class, 'update'])->name('subutilities.update');
        Route::delete('/subutilities-delete/{id}', [OtherUtilitiesSubController::class, 'delete'])->name('subutilities.delete');
        Route::get('/site-subutilities/{id}/export', [OtherUtilitiesSubController::class, 'export'])->name('subutilities.export');
        // Subcontractor exports
        Route::get('/subcontractor/{siteId}/export', [\App\Http\Controllers\Admin\SubcontractorController::class, 'exportSite'])->name('subcontractor.export');
        Route::get('/subcontractor/{siteId}/{subcontractorType}/export', [\App\Http\Controllers\Admin\SubcontractorController::class, 'exportType'])->name('subcontractor.export.type');

        //Agent
        Route::get('/agent-management', [AgentController::class, 'index'])->name('agent.list');
        Route::post('/agent-add', [AgentController::class, 'store'])->name('agent.add');
        Route::post('agent-update', [AgentController::class, 'update'])->name('agent.update');
        Route::patch('/agent/inactivate/{id}', [AgentController::class, 'delete'])->name('agent.delete');

        //Property list
        Route::get('/property-management', [PropertyController::class, 'index'])->name('property-list');
        Route::get('/property-form', [PropertyController::class, 'getPropertyForm'])->name('property.form');
        Route::post('/property-add', [PropertyController::class, 'store'])->name('property.add');

        //Quotation
        Route::get('/quotation-form', [QuotationController::class, 'getForm'])->name('quotation.form');
        Route::post('/quotation-add', [QuotationController::class, 'store'])->name('quotation.add');
        Route::get('/quotation-history', [QuotationController::class, 'history'])->name('quotation.history');

        //Profile setting
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::post('/profile-update', [ProfileController::class, 'update'])->name('profile.update');
    //check list
        Route::get('/checklist/{siteId}', [ChecklistController::class, 'index'])->name('checklist');
        Route::post('/checklist/add', [ChecklistController::class, 'store'])->name('checklist.add');
        Route::post('/checklist/update/{id}', [ChecklistController::class, 'update'])->name('checklist.update');
        //manage checklist templates: delete items/stages, drag-and-drop reorder
        Route::get('/checklist-manage', [ChecklistController::class, 'manage'])->name('checklist.manage');
        Route::delete('/checklist-task/{id}', [ChecklistController::class, 'deleteTask'])->name('checklist.task.delete');
        Route::delete('/checklist-stage/{id}', [ChecklistController::class, 'destroy'])->name('checklist.stage.delete');
        Route::patch('/checklist-task/{id}', [ChecklistController::class, 'updateTaskName'])->name('checklist.task.rename');
        Route::patch('/checklist-stage/{id}', [ChecklistController::class, 'updateStage'])->name('checklist.stage.rename');
        Route::post('/checklist-task/add', [ChecklistController::class, 'storeTaskItem'])->name('checklist.task.add');
        Route::post('/checklist-tasks/reorder', [ChecklistController::class, 'reorderTasks'])->name('checklist.tasks.reorder');
        Route::post('/checklist-stages/reorder', [ChecklistController::class, 'reorderChecklists'])->name('checklist.stages.reorder');
        //task update for supervisor
        Route::get('/taskupdate/{siteId}/{taskId}', [ChecklistController::class, 'taskcreate'])->name('task.create');
        Route::get('/task/{task}/upload', [ChecklistController::class, 'create'])->name('task.upload.create');
        Route::post('/task/upload', [ChecklistController::class, 'taskstore'])->name('task.upload.store');
        
    
       //admin task view page
        Route::get('/taskmedia/{siteId}/{taskId}', [ChecklistController::class, 'viewTaskMedia'])->name('admin.taskmedia.view');
        Route::put('/taskmedia/update/{id}', [ChecklistController::class, 'updateTaskMedia'])->name('admin.taskmedia.update');
       Route::delete('/taskmedia/delete/{id}/{siteId}', [ChecklistController::class, 'deleteByRemarks'])
    ->name('taskmedia.delete');//ticket
        Route::get('/ticket/{siteId}', [TicketController::class, 'viewTicketsBySite'])->name('ticket');
        Route::get('/tickets/{id}/chat', [TicketController::class, 'showChat'])->name('tickets.chat');
        Route::post('/tickets/{id}/reply', [TicketController::class, 'adminReply'])->name('tickets.reply');
      
        Route::post('/tickets/{id}/admin-message', [TicketController::class, 'storeAdminMessage'])
        ->name('tickets.admin.storeMessage');
      //Drawing
       Route::get('/drawing/{siteId}', [DrawingController::class, 'drawingview'])->name('drawing');
       Route::post('/drawings/store', [DrawingController::class, 'store'])->name('drawings.store');
       Route::delete('/drawings/{id}', [DrawingController::class, 'destroy'])->name('drawings.destroy');
 


});
});

//customer login

Route::get('/login-customer', [AuthController::class, 'showcustomerForm']);
Route::get('/customer-dashboard', [ChecklistController::class, 'showDashboard']);


Route::get('/customer-logout', function () {
    session()->forget('customer_id');
    return redirect('/login-customer');
});
//----- End Customer Details---//

Route::get('/taskupdate', function () {
    return view('admin.checklist.task_update');
});
Route::get('/checklist-add', function () {
    return view('admin.checklist.checklist_create');
})->name('checklist-create');

// attanance excel sheetexport

Route::get('admin/attendance/{siteId}/export', [AttendanceController::class, 'exportAttendance'])
    ->name('attendance.export');
