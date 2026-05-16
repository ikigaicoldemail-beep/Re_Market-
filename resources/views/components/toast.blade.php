{{-- Usage: include in any page. Trigger with window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Done!' } })) --}}
<div x-data="{
        toasts: [],
        push(type, message) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, message });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 4000);
        }
     }"
     @toast.window="push($event.detail.type || 'info', $event.detail.message)"
     class="fixed top-20 right-4 z-50 space-y-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition
             :class="{
                'bg-green-50 border-green-200 text-green-800': toast.type === 'success',
                'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
                'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info',
             }"
             class="border rounded-lg px-4 py-3 shadow-lg max-w-sm">
            <p class="text-sm font-medium" x-text="toast.message"></p>
        </div>
    </template>
</div>
