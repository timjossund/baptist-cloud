<div class="bg-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm text-gray-800">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-200">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <h3 class="font-bold text-gray-900 text-lg">Markdown Quick Guide</h3>
        </div>
        <a href="/learn-markdown" target="_blank" class="text-xs text-blue-600 hover:underline font-medium flex items-center gap-1">
            <span>Full Guide</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
        </a>
    </div>

    <p class="text-xs text-gray-500 mb-4">Reference these Markdown examples while writing your post. Click any snippet to copy it.</p>

    <div class="space-y-4 text-sm" x-data="{ copied: false, copyText(text) { navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => this.copied = false, 1500); } }">
        <div x-show="copied" x-cloak class="text-xs bg-green-100 text-green-800 px-3 py-1.5 rounded-md font-medium text-center transition-all">
            ✓ Copied to clipboard!
        </div>

        <!-- Headings -->
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Headings</span>
            <div class="space-y-1.5 font-mono text-xs">
                <div @click="copyText('# Heading 1')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800"># Heading 1</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans">H1 Title</span>
                </div>
                <div @click="copyText('## Heading 2')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800">## Heading 2</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans">H2 Section</span>
                </div>
                <div @click="copyText('### Heading 3')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800">### Heading 3</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans">H3 Sub-section</span>
                </div>
            </div>
        </div>

        <!-- Emphasis & Formatting -->
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Text Formatting</span>
            <div class="space-y-1.5 font-mono text-xs">
                <div @click="copyText('**Bold Text**')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800">**Bold Text**</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans font-bold">Bold</span>
                </div>
                <div @click="copyText('*Italic Text*')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800">*Italic Text*</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans italic">Italic</span>
                </div>
                <div @click="copyText('> Blockquote text')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800">> Blockquote</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans">Quote</span>
                </div>
            </div>
        </div>

        <!-- Lists -->
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Lists</span>
            <div class="space-y-1.5 font-mono text-xs">
                <div @click="copyText('- Bullet item\n- Second item')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800">- Bullet point</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans">• Unordered</span>
                </div>
                <div @click="copyText('1. First item\n2. Second item')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800">1. Numbered item</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans">1. 2. 3.</span>
                </div>
            </div>
        </div>

        <!-- Links -->
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Links</span>
            <div class="space-y-1.5 font-mono text-xs">
                <div @click="copyText('[Link Description](https://example.com)')" class="p-1.5 bg-gray-50 hover:bg-blue-50 rounded border border-gray-100 cursor-pointer flex justify-between items-center group transition-colors" title="Click to copy">
                    <code class="text-gray-800 break-all">[Link Text](https://...)</code>
                    <span class="text-[10px] text-gray-400 group-hover:text-blue-600 font-sans shrink-0 ml-2">Link</span>
                </div>
            </div>
        </div>
    </div>
</div>
