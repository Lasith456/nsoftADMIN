@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Welcome back, {{ Auth::user()->name }}!</h2>
        <p class="text-gray-500 dark:text-gray-400">Here's a snapshot of your business activity.</p>
    </div>
    
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            
            {{-- Customer Outstanding --}}
            <a href="{{ route('payments.customerOutstanding') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Customer Outstanding</span>
            </a>

            {{-- Agent Outstanding --}}
            <a href="{{ route('payments.agentOutstanding') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-orange-100 dark:bg-orange-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Agent Outstanding</span>
            </a>
            
            {{-- Supplier Outstanding --}}
            <a href="{{ route('payments.supplierOutstanding') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Supplier Outstanding</span>
            </a>

            {{-- View Purchase Orders --}}
            <a href="{{ route('purchase-orders.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5V3a2 2 0 012-2h2a2 2 0 012 2v2"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View POs</span>
            </a>
            {{-- View Delivery Notes --}}
            <a href="{{ route('delivery-notes.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-pink-100 dark:bg-pink-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View DNs</span>
            </a>
            {{-- View Invoices --}}
            <a href="{{ route('invoices.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View Invoices</span>
            </a>
            {{-- View Receive Notes --}}
            <a href="{{ route('receive-notes.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-teal-100 dark:bg-teal-900/50 rounded-full mb-2">
                   <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View RNs</span>
            </a>
             {{-- View Products --}}
            <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View Products</span>
            </a>
             {{-- View Reports --}}
            <a href="{{ route('reports.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View Reports</span>
            </a>

            {{-- NEW: View GRNs --}}
            {{-- NOTE: Replace 'grns.index' with your actual route name --}}
            <a href="{{ route('grns.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-cyan-100 dark:bg-cyan-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V9"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View GRNs</span>
            </a>
            
            {{-- NEW: Settings --}}
            {{-- NOTE: Replace 'settings.index' with your actual route name --}}
            <a href="{{ route('settings.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full mb-2">
                   <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Settings</span>
            </a>

            {{-- NEW: Users --}}
            {{-- NOTE: Replace 'users.index' with your actual route name --}}
            <a href="{{ route('users.index') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                <div class="p-3 bg-rose-100 dark:bg-rose-900/50 rounded-full mb-2">
                    <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-1a6 6 0 00-1-3.794a4 4 0 00-6.213-3.162M3 7a4 4 0 014-4L12 4.354M3 7a4 4 0 004 4L12 8.646M3 7a4 4 0 014 4L12 8.646"></path></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Manage Users</span>
            </a>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
             <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Stock Distribution</h3>
             <canvas id="stockDonutChart"></canvas>
        </div>
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Last 7 Days Sales</h3>
            <canvas id="salesBarChart"></canvas>
        </div>
    </div>
</div>

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