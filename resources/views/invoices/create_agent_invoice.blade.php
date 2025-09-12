@extends('layouts.app')

@section('content')
<div class="container mx-auto" x-data="agentInvoiceForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-4xl mx-auto">
        <div class="border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Generate Agent Payout Invoice</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Select an agent and the delivery items you wish to include in the payout.</p>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <ul class="list-disc pl-5 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('invoices.storeAgent') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Agent</label>
                    <select id="agent_id" name="agent_id" x-model="selectedAgentId" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border-gray-300 dark:border-gray-600" required>
                        <option value="">Select an agent with pending payouts...</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div x-show="selectedAgentId" class="mt-6 border-t pt-4" x-cloak>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Select Delivery Items to Invoice</h3>
                <div class="space-y-2 max-h-60 overflow-y-auto border dark:border-gray-700 p-2 rounded-md">
                    <template x-if="availableItems.length > 0">
                        <template x-for="item in availableItems" :key="item.id">
                            <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                                <input type="checkbox" name="delivery_item_ids[]" :value="item.id" class="dark:bg-gray-900 rounded">
                                <span class="ml-3 text-sm" x-text="`${item.delivery_note.delivery_note_id}: ${item.quantity_from_agent} x ${item.product_name}`"></span>
                            </label>
                        </template>
                    </template>
                     <template x-if="availableItems.length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 p-2">No uninvoiced items available for this agent.</p>
                    </template>
                </div>
            </div>
            
            <div class="text-right pt-6 mt-6 border-t dark:border-gray-700">
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                    Generate Payout Invoice
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('agentInvoiceForm', () => ({
        agents: @json($agents->keyBy('id')),
        selectedAgentId: '',
        
        get availableItems() {
            if (!this.selectedAgentId) return [];
            const agent = this.agents[this.selectedAgentId];
            return agent ? agent.delivery_items : [];
        }
    }));
});
</script>
@endsection

