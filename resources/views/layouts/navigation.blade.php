<nav class="flex flex-1 flex-col w-full">
    <ul role="list" class="flex flex-1 flex-col gap-y-7 no-scrollbar overflow-y-auto">
        <li>
            <ul role="list" class="-mx-2 space-y-1">
                <li>
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold relative">
                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h7.5" />
                        </svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Dashboard</span>
                    </a>
                </li>
                
                <li x-data="{ open: {{ request()->routeIs('customers.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('customers.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                        <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.962a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm7.5 2.962l-3.68-3.679m2.121-2.121l3.68 3.68m-3.68-3.68l-2.12 2.12" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Customer</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('customers.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add Customer</a></li>
                        <li><a href="{{ route('customers.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Customers</a></li>
                    </ul>
                </li>

                <li x-data="{ open: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('products.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Products</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                     <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('products.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add Product</a></li>
                        <li><a href="{{ route('products.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Products</a></li>
                    </ul>
                </li>

                <li x-data="{ open: {{ request()->routeIs('purchase-orders.*') || request()->routeIs('delivery-notes.*') || request()->routeIs('receive-notes.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('purchase-orders.*') || request()->routeIs('delivery-notes.*') || request()->routeIs('receive-notes.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                        <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-1.125 0-2.25.9-2.25 2.25v11.25c0 1.24 1.009 2.25 2.25 2.25h9.75c1.24 0 2.25-1.01 2.25-2.25V13.5m-9 0h3.75" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Orders</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('purchase-orders.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add Purchase Order</a></li>
                        <li><a href="{{ route('purchase-orders.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Purchase Orders</a></li>
                        <li><a href="{{ route('delivery-notes.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Delivery Notes Add</a></li>
                        <li><a href="{{ route('delivery-notes.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Delivery Notes</a></li>
                        <li><a href="{{ route('receive-notes.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Receive Notes</a></li>
                        <li><a href="{{ route('receive-notes.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Create Receive Note</a></li>
                    </ul>
                </li>

                <li x-data="{ open: {{ request()->routeIs('suppliers.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('suppliers.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                        <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v.958m12 0c-2.278 0-4.271.996-5.621 2.62m12 0v.958c0 .568-.422 1.048-.987 1.106a48.554 48.554 0 01-10.026 0 1.106 1.106 0 01-.987-1.106v-.958m12 0c.058.118.106.236.148.355" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Supplier</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('suppliers.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add Supplier</a></li>
                        <li><a href="{{ route('suppliers.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Suppliers</a></li>
                    </ul>
                </li>
                
                <li x-data="{ open: {{ request()->routeIs('vehicles.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('vehicles.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                       <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v.958m12 0c-2.278 0-4.271.996-5.621 2.62m12 0v.958c0 .568-.422 1.048-.987 1.106a48.554 48.554 0 01-10.026 0 1.106 1.106 0 01-.987-1.106v-.958m12 0c.058.118.106.236.148.355" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Vehicle</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('vehicles.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add Vehicle</a></li>
                        <li><a href="{{ route('vehicles.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Vehicles</a></li>
                    </ul>
                </li>

                <li x-data="{ open: {{ request()->routeIs('agents.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('agents.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                        <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Agent</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('agents.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add Agent</a></li>
                        <li><a href="{{ route('agents.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Agents</a></li>
                    </ul>
                </li>
                
                <li x-data="{ open: {{ request()->routeIs('payments.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('payments.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                       <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 21z" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Payment</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('payments.history.agent') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Agent Payment</a></li>
                        <li><a href="{{ route('payments.history.supplier') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Suppliers Payment</a></li>
                        <li><a href="{{ route('payments.history.customer') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Customer Payments</a></li>
                        <li><a href="{{ route('payments.customerOutstanding') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Customer Outstanding</a></li>

                    </ul>
                </li>

                <li x-data="{ open: {{ request()->routeIs('departments.*') || request()->routeIs('subdepartments.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('departments.*') || request()->routeIs('subdepartments.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                        <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Departments</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('departments.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add Department</a></li>
                        <li><a href="{{ route('departments.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Departments</a></li>
                        <li><a href="{{ route('subdepartments.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add Sub-Department</a></li>
                        <li><a href="{{ route('subdepartments.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Sub-Departments</a></li>
                    </ul>
                </li>

                <li x-data="{ open: {{ request()->routeIs('grns.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('grns.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                       <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Stock</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('grns.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Add GRN</a></li>
                        <li><a href="{{ route('grns.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All GRN</a></li>
                        <li><a href="{{ route('stock-management.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Wastage</a></li>

                    </ul>
                </li>
                
                <li x-data="{ open: {{ request()->routeIs('invoices.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('invoices.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                       <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-1.125 0-2.25.9-2.25 2.25v11.25c0 1.24 1.009 2.25 2.25 2.25h9.75c1.24 0 2.25-1.01 2.25-2.25V13.5m-9 0h3.75" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Invoices</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('invoices.index') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">All Invoices</a></li>
                        <li><a href="{{ route('invoices.create') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Create Invoice</a></li>

                    </ul>
                </li>
                <li x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('reports.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                       <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-1.125 0-2.25.9-2.25 2.25v11.25c0 1.24 1.009 2.25 2.25 2.25h9.75c1.24 0 2.25-1.01 2.25-2.25V13.5m-9 0h3.75" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Reports</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('reports.customers') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Customer Report</a></li>
                        <li><a href="{{ route('reports.sales') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Sales Report</a></li>
                        <li><a href="{{ route('reports.stock_levels') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Stock Report</a></li>
                        <li><a href="{{ route('reports.suppliers') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Supplier Report</a></li>
                        <li><a href="{{ route('reports.delivery_notes') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Delivery Note Report</a></li>
                        <li><a href="{{ route('reports.receive_notes') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Receive Note Report</a></li>
                        <li><a href="{{ route('reports.purchase_orders') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Purchase Order Report</a></li>
                        <li><a href="{{ route('reports.agents') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Agent Report</a></li>
                        <li><a href="{{ route('reports.order_flow') }}" class="block rounded-md py-2 pr-2 pl-9 text-sm leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">Order Flow Report</a></li>

                    </ul>
                </li>
                <li x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="{{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold w-full text-left relative">
                        <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Users</span>
                        <svg x-show="!sidebarCollapsed || sidebarHover" class="ml-auto h-5 w-5 shrink-0 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" :class="{'rotate-90': open}"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open" class="mt-1 px-2 space-y-1">
                        <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'text-white' : 'text-gray-400' }} block rounded-md py-2 pr-2 pl-9 text-sm leading-6 hover:bg-gray-800 hover:text-white">Manage Users</a></li>
                        <li><a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'text-white' : 'text-gray-400' }} block rounded-md py-2 pr-2 pl-9 text-sm leading-6 hover:bg-gray-800 hover:text-white">Manage Roles</a></li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.112.077l1.07-.372a1.125 1.125 0 011.218.884l1.281 4.547a1.125 1.125 0 01-.44 1.265l-.845.602a1.125 1.125 0 00-.472 1.359c.04.089.074.182.103.277.294.948.093 2.01-.464 2.752l-.845.602a1.125 1.125 0 01-1.265.44l-1.07-.372a1.125 1.125 0 00-1.112.077c-.073.044-.146.087-.22.127a1.125 1.125 0 00-.645.87l-.213 1.281c-.09.542-.56.94-1.11.94h-2.593c-.55 0-1.02-.398-1.11-.94l-.213-1.281a1.125 1.125 0 00-.645-.87c-.074-.04-.147-.083-.22-.127a1.125 1.125 0 00-1.112-.077l-1.07.372a1.125 1.125 0 01-1.218-.884l-1.281-4.547a1.125 1.125 0 01.44-1.265l.845-.602a1.125 1.125 0 00.472-1.359c-.04-.089-.074-.182-.103-.277-.294-.948-.093-2.01.464-2.752l.845-.602a1.125 1.125 0 011.265-.44l1.07.372a1.125 1.125 0 001.112-.077c.073-.044.146-.087.22.127.324.196.72.257 1.112.077l.213-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed || sidebarHover" class="whitespace-nowrap">Settings</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>