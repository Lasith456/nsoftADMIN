<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\ReceiveNoteController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyDepartmentNameController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    // Resourceful Routes for CRUD operations
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('products', ProductController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('vehicles', VehicleController::class);
    Route::resource('agents', AgentController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::resource('grns', GrnController::class);
    Route::resource('delivery-notes', DeliveryNoteController::class);
    Route::resource('receive-notes', ReceiveNoteController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('company_department_names', CompanyDepartmentNameController::class);

    // Dynamic Form Routes
    Route::get('/purchase-orders/get-agents-for-product', [PurchaseOrderController::class, 'getAgentsForProduct'])->name('purchase-orders.getAgentsForProduct');
    Route::post('/delivery-notes/check-stock', [DeliveryNoteController::class, 'checkStock'])->name('delivery-notes.checkStock');
    Route::post('/receive-notes/get-items', [ReceiveNoteController::class, 'getItemsForDeliveryNote'])->name('receive-notes.getItems');

    // GRN Status Management
    Route::get('grn-manage', [GrnController::class, 'manage'])->name('grns.manage');
    Route::post('grns/{grn}/confirm', [GrnController::class, 'complete'])->name('grns.complete');
    Route::post('grns/{grn}/cancel', [GrnController::class, 'cancel'])->name('grns.cancel');
    Route::post('grns/{grn}/generate-invoice', [GrnController::class, 'generateInvoice'])->name('grns.generateInvoice');

    // Delivery Note Status Management
    Route::get('delivery-notes-manage', [DeliveryNoteController::class, 'manage'])->name('delivery-notes.manage');
    Route::post('delivery-notes/{deliveryNote}/update-status', [DeliveryNoteController::class, 'updateStatus'])->name('delivery-notes.updateStatus');
    Route::post('/departments/api-store', [DepartmentController::class, 'apiStore'])->name('departments.api.store');

    // Stock Management Routes
    Route::get('/stock-management', [StockManagementController::class, 'index'])->name('stock-management.index');
    Route::post('/stock-management/convert', [StockManagementController::class, 'apiConvert'])->name('stock-management.api.convert');
    Route::post('/stock-management/wastage', [StockManagementController::class, 'apiWastage'])->name('stock-management.api.wastage');

    // Invoice Creation Routes
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::get('/invoices/create/agent', [InvoiceController::class, 'createAgentInvoice'])->name('invoices.createAgent');
    Route::post('/invoices/store/agent', [InvoiceController::class, 'storeAgentInvoice'])->name('invoices.storeAgent');
    Route::get('/invoices/create/customer', [InvoiceController::class, 'createCustomerInvoice'])->name('invoices.createCustomer'); // shows the form
    Route::post('/invoices/store/customer', [InvoiceController::class, 'storeCustomerInvoice'])->name('invoices.storeCustomer'); // handles form submit
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print'); 
    Route::get('/invoices/create/supplier', [InvoiceController::class, 'createSupplierInvoice'])->name('invoices.createSupplier');
    Route::post('/invoices/store/supplier', [InvoiceController::class, 'storeSupplierInvoice'])->name('invoices.storeSupplier');
    Route::get('/invoices/create/from-rn/{receiveNote}', [InvoiceController::class, 'createFromReceiveNote'])->name('invoices.createFromRN');
    Route::post('/invoices/store/from-rn/{receiveNote}', [InvoiceController::class, 'storeFromReceiveNote'])->name('invoices.storeFromRN');
     // Payment Routes
    Route::get('/payments/create/{invoice}', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/store/{invoice}', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/history/customer', [PaymentController::class, 'customerHistory'])->name('payments.history.customer');
    Route::get('/payments/history/agent', [PaymentController::class, 'agentHistory'])->name('payments.history.agent');
    Route::get('/payments/history/supplier', [PaymentController::class, 'supplierHistory'])->name('payments.history.supplier');
    Route::get('/customers/{customer}/unpaid-invoices', [CustomerController::class, 'getUnpaidInvoices'])->name('customers.unpaid-invoices');
    Route::get('/payments/create-bulk/{customer}', [PaymentController::class, 'createBulk'])->name('payments.createBulk.customer');    
    Route::post('/payments/store-bulk', [PaymentController::class, 'storeBulk'])->name('payments.storeBulk');
    Route::get('/payments/customerOutstanding', [PaymentController::class, 'customerOutstanding'])->name('payments.customerOutstanding');
    Route::get('/payments/create-bulk', [PaymentController::class, 'createBulk'])->name('payments.createBulk');

    // Report Routes 
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/customers', [ReportController::class, 'customerReport'])->name('reports.customers');
    Route::get('/reports/stock-levels', [ReportController::class, 'stockLevelReport'])->name('reports.stock_levels');
    Route::get('/reports/suppliers', [ReportController::class, 'supplierReport'])->name('reports.suppliers');
    Route::get('/reports/delivery-notes', [ReportController::class, 'deliveryNoteReport'])->name('reports.delivery_notes');
    Route::get('/reports/receive-notes', [ReportController::class, 'receiveNoteReport'])->name('reports.receive_notes');
    Route::get('/reports/purchase-orders', [ReportController::class, 'purchaseOrderReport'])->name('reports.purchase_orders');
    Route::get('/reports/agents', [ReportController::class, 'agentReport'])->name('reports.agents');
    Route::get('/reports/order-flow', [ReportController::class, 'orderFlowReport'])->name('reports.order_flow');
    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/update-vat', [SettingsController::class, 'updateVat'])->name('updateVat');
        Route::post('/store-bank', [SettingsController::class, 'storeBank'])->name('storeBank');
        Route::put('/banks/{bank}/toggle-status', [SettingsController::class, 'toggleBankStatus'])->name('banks.toggleStatus');
    });
    Route::get('/invoices/{invoice}/showopt2', [App\Http\Controllers\InvoiceController::class, 'showOpt2'])
    ->name('invoices.showopt2');
Route::get('/invoices/{id}/printopt3', [InvoiceController::class, 'printInvoice'])->name('invoices.opt3');

    Route::post('/purchase-orders/auto-create', [PurchaseOrderController::class, 'autoCreateFromDiscrepancy'])->name('purchase-orders.autoCreate');
    Route::post('/companies/store', [App\Http\Controllers\CompanyController::class, 'store'])->name('companies.api.store');

    Route::get('/payments/receipt/{batchId}', [PaymentController::class, 'showReceipt'])->name('payments.receipt');
Route::get('/payments/history/{customer}', [PaymentController::class, 'history'])
    ->name('payments.history.customerPayments');
Route::prefix('payments')->name('payments.')->group(function () {
    // Agent outstanding
    Route::get('/agent-outstanding', [PaymentController::class, 'agentOutstanding'])
        ->name('agentOutstanding');
        Route::get('/supplier-outstanding', [PaymentController::class, 'supplierOutstanding'])
        ->name('supplierOutstanding');
    // Bulk agent payment
    Route::get('/create-bulk-agent/{agent?}', [PaymentController::class, 'createBulkAgent'])
        ->name('createBulkAgent');

    Route::post('/store-bulk-agent', [PaymentController::class, 'storeBulkAgent'])
        ->name('storeBulkAgent');
    Route::get('/create-bulk-supplier/{supplier?}', [PaymentController::class, 'createBulkSupplier'])
    ->name('createBulkSupplier');
    Route::post('/store-bulk-supplier', [PaymentController::class, 'storeBulkSupplier'])
        ->name('storeBulkSupplier');


});
        Route::get('/agents/{agent}/unpaid-invoices', [AgentController::class, 'getUnpaidInvoices'])
    ->name('agents.unpaid-invoices');
    Route::get('/suppliers/{supplier}/unpaid-invoices', [SupplierController::class, 'getUnpaidInvoices'])
    ->name('suppliers.unpaid-invoices');
    Route::get('/payments/history/agent/{agent}', [PaymentController::class, 'agentPaymentHistory'])
    ->name('payments.history.agentPayments');
    Route::get('/payments/history/supplier/{supplier}', [PaymentController::class, 'supplierPaymentHistory'])
    ->name('payments.history.supplierPayments');
Route::get('/stock/wastage-report', [StockManagementController::class, 'wastageReport'])
    ->name('stock.wastage.report');

Route::get('/stock/wastage-report/export-excel', [StockManagementController::class, 'exportWastageExcel'])
    ->name('stock.wastage.export.excel');

Route::get('/stock/wastage-report/export-pdf', [StockManagementController::class, 'exportWastagePdf'])
    ->name('stock.wastage.export.pdf');
Route::get('/reports/outstanding-payments', [ReportController::class, 'outstandingPayments'])->name('reports.outstanding');


Route::get('/agents/{agent}/outstanding', [AgentController::class, 'outstanding'])
    ->name('agents.outstanding');

Route::post('/agents/{agent}/outstanding/pay', [AgentController::class, 'payOutstanding'])
    ->name('agents.outstanding.pay');
Route::get('/receive-notes/{id}/popup', [ReceiveNoteController::class, 'popup'])
    ->name('receive-notes.popup');
Route::get('payments/customer/{customerId}', [PaymentController::class, 'customerPayments'])
     ->name('payments.history.customerInvocies');



     // routes/web.php
Route::get('/reports/customers/export-excel', [ReportController::class, 'exportCustomerExcel'])
    ->name('reports.customers.export.excel');

Route::get('/reports/customers/export-pdf', [ReportController::class, 'exportCustomerPdf'])
    ->name('reports.customers.export.pdf');

Route::get('/reports/sales/export-excel', [ReportController::class, 'exportSalesExcel'])
    ->name('reports.sales.export.excel');

Route::get('/reports/sales/export-pdf', [ReportController::class, 'exportSalesPdf'])
    ->name('reports.sales.export.pdf');

Route::get('/reports/stock-level/export-excel', [ReportController::class, 'exportStockLevelExcel'])
    ->name('reports.stock_level.export.excel');

Route::get('/reports/stock-level/export-pdf', [ReportController::class, 'exportStockLevelPdf'])
    ->name('reports.stock_level.export.pdf');

Route::get('/reports/suppliers/export-excel', [ReportController::class, 'exportSupplierExcel'])
    ->name('reports.suppliers.export.excel');

Route::get('/reports/suppliers/export-pdf', [ReportController::class, 'exportSupplierPdf'])
    ->name('reports.suppliers.export.pdf');

Route::get('/reports/agents/export-excel', [ReportController::class, 'exportAgentExcel'])
    ->name('reports.agents.export.excel');

Route::get('/reports/agents/export-pdf', [ReportController::class, 'exportAgentPdf'])
    ->name('reports.agents.export.pdf');

Route::get('/reports/delivery-notes/export-excel', [ReportController::class, 'exportDeliveryNotesExcel'])
    ->name('reports.delivery_notes.export.excel');

Route::get('/reports/delivery-notes/export-pdf', [ReportController::class, 'exportDeliveryNotesPdf'])
    ->name('reports.delivery_notes.export.pdf');

Route::get('/reports/receive-notes/export-excel', [ReportController::class, 'exportReceiveNotesExcel'])
    ->name('reports.receive_notes.export.excel');

Route::get('/reports/receive-notes/export-pdf', [ReportController::class, 'exportReceiveNotesPdf'])
    ->name('reports.receive_notes.export.pdf');

Route::get('/reports/purchase-orders/export-excel', [ReportController::class, 'exportPurchaseOrdersExcel'])
    ->name('reports.purchase_orders.export.excel');

Route::get('/reports/purchase-orders/export-pdf', [ReportController::class, 'exportPurchaseOrdersPdf'])
    ->name('reports.purchase_orders.export.pdf');

Route::get('/reports/order-flow/export-excel', [ReportController::class, 'exportOrderFlowExcel'])
    ->name('reports.order_flow.export.excel');

Route::get('/reports/order-flow/export-pdf', [ReportController::class, 'exportOrderFlowPdf'])
    ->name('reports.order_flow.export.pdf');

Route::get('/reports/outstanding/export-excel', [ReportController::class, 'exportOutstandingExcel'])
    ->name('reports.outstanding.export.excel');

Route::get('/reports/outstanding/export-pdf', [ReportController::class, 'exportOutstandingPdf'])
    ->name('reports.outstanding.export.pdf');

Route::get('/reports/company', [ReportController::class, 'companyReport'])
        ->name('reports.company');

Route::get('/reports/company/export/excel', [ReportController::class, 'exportCompanyExcel'])
        ->name('reports.company.export.excel');

Route::get('/reports/company/export/pdf', [ReportController::class, 'exportCompanyPdf'])
        ->name('reports.company.export.pdf');

Route::get('/reports/company/select', [ReportController::class, 'selectCompany'])
    ->name('reports.company.select');
    Route::get('reports/company-outstanding', [ReportController::class, 'companyOutstandingReport'])->name('reports.companyOutstanding');
Route::get('reports/company-outstanding/excel', [ReportController::class, 'exportCompanyOutstandingExcel'])->name('reports.companyOutstanding.exportExcel');
Route::get('reports/company-outstanding/pdf', [ReportController::class, 'exportCompanyOutstandingPdf'])->name('reports.companyOutstanding.exportPdf');

});

