@props([
    'title',
    'value',
    'subtitle',
    'icon',
    'color'
])

<div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden">

    <div class="flex items-center justify-between">

        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">
                {{ $title }}
            </p>

            <h3 class="text-3xl font-black text-slate-800">
                {{ $value }}
            </h3>

            <p class="text-[10px] text-slate-400 font-bold mt-2 uppercase tracking-widest">
                {{ $subtitle }}
            </p>
        </div>

        <div class="w-14 h-14 rounded-2xl bg-{{ $color }}-50 flex items-center justify-center text-{{ $color }}-500">
            <i class="fas {{ $icon }} text-xl"></i>
        </div>

    </div>

</div>