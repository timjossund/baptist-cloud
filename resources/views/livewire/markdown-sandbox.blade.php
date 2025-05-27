<div class="bg-white py-12 md:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col ">
    <div class="mx-auto w-full max-w-2xl lg:max-w-6xl flex flex-wrap justify-around">
        <div class="markdown-body w-5/12">
            <div class="mb-8">
                <div class="sm:flex sm:items-center">
                    <div class="flex w-full flex-col ml-2">
                        <h1 class="font-semibold text-gray-900 w-full text-3xl mb-2">Supported Markdown Syntax:</h1>
                    </div>
                </div>
                <div class="mt-8 flow-root border">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3">Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Markdown</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white">
                                <tr class="even:bg-gray-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 border-r">Headings</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"># h1 <br> ## h2 <br> ### h3 <br> #### h4</td>
                                </tr>
                                <tr class="even:bg-gray-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 border-r">Emphasis</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">*italics* <br> **bold** <br> _italics_ <br> __bold__</td>
                                </tr>
                                <tr class="even:bg-gray-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 border-r">Links</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">[link text](https://example.com)</td>
                                </tr>
                                <tr class="even:bg-gray-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 border-r">Blockquote</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">> blockquote</td>
                                </tr>
                                <tr class="even:bg-gray-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-3 border-r">Horizontal Rule</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">---</td>
                                </tr>
                                </tbody>
                            </table>
                            <p class="text-sm mt-4 border-t px-4 pb-2"><br>
                                Paragraphs: Press enter twice to create a new paragraph with some space in between
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="markdown-body w-5/12">
            <textarea wire:model.live="markdown" rows="10" type="text" class="w-full" placeholder="Type your markdown here"></textarea>
            <div class="markdown-body w-full mt-11 border p-4 h-72 overflow-y-scroll">
                {!! $content !!}
            </div>

        </div>
    </div>
</div>
