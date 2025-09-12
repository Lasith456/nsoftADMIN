@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <!-- Welcome message -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Welcome back, {{ Auth::user()->name }}!</h2>
        <p class="text-gray-500 dark:text-gray-400">Here's a snapshot of your business activity.</p>
    </div>
    
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        {{-- Row 1 --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-yellow-100 dark:bg-yellow-900/50 p-3 rounded-full mr-4">
                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Customers</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $customerCount }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-blue-100 dark:bg-blue-900/50 p-3 rounded-full mr-4">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Suppliers</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $supplierCount }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center">
             <div class="bg-indigo-100 dark:bg-indigo-900/50 p-3 rounded-full mr-4">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Agents</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $agentCount }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-green-100 dark:bg-green-900/50 p-3 rounded-full mr-4">
                 <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Invoices</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $invoiceCount }}</p>
            </div>
        </div>

        {{-- Row 2 --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-purple-100 dark:bg-purple-900/50 p-3 rounded-full mr-4">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5V3a2 2 0 012-2h2a2 2 0 012 2v2"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Purchase Orders</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $purchaseOrderCount }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center">
             <div class="bg-pink-100 dark:bg-pink-900/50 p-3 rounded-full mr-4">
                <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Delivery Notes</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $deliveryNoteCount }}</p>
            </div>
        </div>
         <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center">
             <div class="bg-teal-100 dark:bg-teal-900/50 p-3 rounded-full mr-4">
                <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Receive Notes</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $receiveNoteCount }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center">
             <div class="bg-red-100 dark:bg-red-900/50 p-3 rounded-full mr-4">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Wastage Logs</h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ $wastageLogCount }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Donut Chart -->
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
             <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Stock Distribution</h3>
             <canvas id="stockDonutChart"></canvas>
        </div>
        <!-- Bar Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Last 7 Days Sales</h3>
            <canvas id="salesBarChart"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Donut Chart for Stock
        const donutCtx = document.getElementById('stockDonutChart').getContext('2d');
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Clear Stock', 'Non-Clear Stock', 'Wastage'],
                datasets: [{
                    label: 'Stock Quantity',
                    data: @json($stockData),
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });

        // Bar Chart for Sales
        const barCtx = document.getElementById('salesBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: @json($weeklyLabels),
                datasets: [{
                    label: 'Sales (LKR)',
                    data: @json($weeklySales),
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                },
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection

