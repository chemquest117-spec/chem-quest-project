@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-slate-900/50 border border-slate-700 text-white placeholder-slate-400 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl shadow-sm transition-colors duration-200']) }}>