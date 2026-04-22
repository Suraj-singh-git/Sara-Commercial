@props(['nodes'])

<ul class="space-y-0.5 border-l border-white/10 pl-3">
    @foreach ($nodes as $cat)
        <li>
            @if ($cat->children->isNotEmpty())
                <details class="group/nav">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 rounded-md py-2 pl-1 pr-2 text-sm text-white/90 hover:bg-white/10 [&::-webkit-details-marker]:hidden">
                        <span class="font-medium">{{ $cat->name }}</span>
                        <svg class="h-4 w-4 shrink-0 text-white/50 transition group-open/nav:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="pb-2 pt-1">
                        <a href="{{ route('shop.catalog', ['category_id' => $cat->id]) }}"
                           class="mb-2 block rounded-md px-2 py-1.5 text-xs font-semibold text-amber-300 hover:bg-white/10 hover:text-amber-200">
                            View all in {{ $cat->name }}
                        </a>
                        <x-shop.nav-category-branch :nodes="$cat->children" />
                    </div>
                </details>
            @else
                <a href="{{ route('shop.catalog', ['category_id' => $cat->id]) }}"
                   class="flex items-center gap-2 rounded-md py-2 pl-1 pr-2 text-sm text-white/85 hover:bg-white/10 hover:text-white">
                    @if ($cat->menu_image_path)
                        <img src="{{ $cat->menuImageUrl() }}" alt="" class="h-8 w-8 rounded object-cover ring-1 ring-white/20" width="32" height="32">
                    @endif
                    <span>{{ $cat->name }}</span>
                </a>
            @endif
        </li>
    @endforeach
</ul>
