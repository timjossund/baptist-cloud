<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center" x-data="{publish: false}">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <livewire:edit-post :post="$post" />
        </div>
    </section>
</x-app-layout>
