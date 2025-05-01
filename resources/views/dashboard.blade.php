<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="">
                    <nav class="flex space-x-4 p-4" aria-label="Tabs">
                        <a href="/dashboard" class="{{ Request::segment(3) == "" ? "rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 active" : "rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" }} rounded-lg flex justify-center items-center" aria-current="page">
                            All
                        </a>
                        @foreach ($categories as $category)
                        <a href="/dashboard/{{ $category['title'] }}" class="inline-block px-4 py-3 {{ Request::segment(3) == $category['title'] ? "rounded-md bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-700 active" : "rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" }} rounded-lg" aria-current="page">
                            {{ $category['title'] }}
                        </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
            <div class="mx-auto max-w-2xl lg:max-w-4xl">
                <h2 class="text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">Latest Articles</h2>
                <p class="mt-2 text-lg/8 text-gray-600">Learn how to grow your business with our expert advice.</p>
                <div class="mt-16 space-y-10 lg:mt-20 lg:space-y-10">
                    @foreach($posts as $post)

                    <article class="relative isolate flex flex-col gap-8 lg:flex-row border-b pb-12">
                        <div class="relative aspect-video sm:aspect-[2/1] lg:aspect-square lg:w-64 lg:shrink-0">
                            <img src="https://images.unsplash.com/photo-1496128858413-b36217c2ce36?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=3603&q=80" alt="" class="absolute inset-0 size-full rounded-2xl bg-gray-50 object-cover">
                            <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-gray-900/10"></div>
                        </div>
                        <div>
                            <div class="flex items-center gap-x-4 text-xs">
                                <time datetime="2020-03-16" class="text-gray-500">Mar 16, 2020</time>
                                <a href="#" class="relative z-10 rounded-full bg-gray-50 px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-100">{{ $category['title'] }}</a>
                            </div>
                            <div class="group relative max-w-xl">
                                <h3 class="mt-3 text-lg/6 font-semibold text-gray-900 group-hover:text-gray-600">
                                    <a href="#">
                                        <span class="absolute inset-0"></span>
                                        {{ $post['title'] }}
                                    </a>
                                </h3>
                                <p class="mt-5 text-sm/6 text-gray-600">Illo sint voluptas. Error voluptates culpa eligendi. Hic vel totam vitae illo. Non aliquid explicabo necessitatibus unde. Sed exercitationem placeat consectetur nulla deserunt vel iusto corrupti dicta laboris incididunt.</p>
                            </div>
                            <div class="mt-6 flex border-t border-gray-900/5 pt-6">
                                <div class="relative flex items-center gap-x-4">
                                    <img src="https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" class="size-10 rounded-full bg-gray-50">
                                    <div class="text-sm/6">
                                        <p class="font-semibold text-gray-900">
                                            <a href="#">
                                                <span class="absolute inset-0"></span>
                                                Michael Foster
                                            </a>
                                        </p>
                                        <p class="text-gray-600">Co-Founder / CTO</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                <div class="mt-6 pagination-wrapper">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
