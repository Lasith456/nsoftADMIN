@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">
        <div class="border-b pb-4 mb-6 flex items-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="h-8 w-8 text-blue-600 dark:text-blue-400" 
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7a2 2 
                         0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z" />
            </svg>
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Reports Dashboard</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Select and generate detailed reports for better insights.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <!-- Sales Report -->
            <a href="{{ route('reports.sales') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-green-600" fill="none" 
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8c1.657 0 3-1.343 3-3S13.657 2 
                                 12 2 9 3.343 9 5s1.343 3 3 3zM19 
                                 21v-2a4 4 0 00-3-3.87M6 21v-2a4 
                                 4 0 013-3.87M16 7a6 6 0 00-8 0" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Sales Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    View invoices by date range including totals and VAT breakdowns.
                </p>
            </a>

            <!-- Customer Outstanding -->
            <a href="{{ route('reports.customers') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" 
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8c-3.866 0-7 3.134-7 
                                 7h14c0-3.866-3.134-7-7-7z" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Customer Outstanding</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Summary of customers with unpaid invoices and balances.
                </p>
            </a>

            <!-- Stock Levels -->
            <a href="{{ route('reports.stock_levels') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" 
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M20 13V7a2 2 0 00-2-2h-6a2 2 
                                 0 00-2 2v6a2 2 0 002 2h6a2 2 
                                 0 002-2zM4 15h4m-2-2v4" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Stock Level Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Check available stock for all products, split by department.
                </p>
            </a>

            <!-- Suppliers -->
            <a href="{{ route('reports.suppliers') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M7 8h10M7 16h10M5 12h14" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Supplier Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Overview of suppliers, transactions, and balances.
                </p>
            </a>

            <!-- Agents -->
            <a href="{{ route('reports.agents') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-pink-600" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M5 13l4 4L19 7" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Agent Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Track agent invoices and outstanding commissions.
                </p>
            </a>

            <!-- Delivery Notes -->
            <a href="{{ route('reports.delivery_notes') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6M9 8h6" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Delivery Note Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    List of delivery notes with status and linked invoices.
                </p>
            </a>

            <!-- Receive Notes -->
            <a href="{{ route('reports.receive_notes') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8v8m4-4H8" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Receive Note Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Track received items and validate against purchase orders.
                </p>
            </a>

            <!-- Purchase Orders -->
            <a href="{{ route('reports.purchase_orders') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M3 10h11M9 21V3m4 18h8" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Purchase Order Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Review purchase orders, supplier info, and approval status.
                </p>
            </a>

            <!-- Order Flow -->
            <a href="{{ route('reports.order_flow') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 16l4-4m0 0l-4-4m4 4H3" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Order Flow Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Full process flow from purchase order to invoice completion.
                </p>
            </a>

            <!-- Company Report -->
            <a href="{{ route('reports.company.select') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 17v-2a4 4 0 118 0v2M12 7a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Company Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Department-wise report for all customers under a company.
                </p>
            </a>

            <!-- Outstanding Payments -->
            <a href="{{ route('reports.outstanding') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M11 11V3h2v8h8v2h-8v8h-2v-8H3v-2h8z" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Outstanding Payments</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Track overdue invoices and outstanding customer, agent, and supplier payments.
                </p>
            </a>

            <!-- Wastage Report -->
            <a href="{{ route('stock.wastage.report') }}" 
               class="p-5 rounded-lg border bg-gray-50 dark:bg-gray-700 hover:shadow-md transition">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M19 7l-7 7-7-7" />
                    </svg>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-200">Wastage Report</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    View stock wastage records and track loss per department.
                </p>
            </a>

        </div>
    </div>
</div>
@endsection
