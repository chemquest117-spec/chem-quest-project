<x-app-layout>
     @section('title', 'License Management')

     <div class="py-8">
          <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
               <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> {{ __('admin.dashboard') }}</a>
               <h1 class="text-3xl font-bold text-white mt-1 mb-8 flex items-center gap-2"><x-icon name="key"
                         class="w-7 h-7 text-cyan-400" /> License Management</h1>

               <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                    @if($license)
                         <div class="mb-6">
                              <h2 class="text-xl font-semibold text-white mb-4">Current License</h2>
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                   <div>
                                        <label class="block text-sm font-medium text-slate-400 mb-1">License Key</label>
                                        <p class="text-white font-mono bg-black/20 px-3 py-2 rounded">{{ $license->key }}</p>
                                   </div>
                                   <div>
                                        <label class="block text-sm font-medium text-slate-400 mb-1">Status</label>
                                        <span class="px-3 py-1 text-sm rounded-full {{ $license->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                             {{ $license->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                   </div>
                                   @if($license->activated_at)
                                        <div>
                                             <label class="block text-sm font-medium text-slate-400 mb-1">Activated At</label>
                                             <p class="text-white">{{ $license->activated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                   @endif
                                   @if($license->activated_by)
                                        <div>
                                             <label class="block text-sm font-medium text-slate-400 mb-1">Activated By</label>
                                             <p class="text-white">{{ $license->activator->name ?? 'Unknown' }}</p>
                                        </div>
                                   @endif
                              </div>
                         </div>

                         <div class="flex gap-4">
                              @if($license->is_active)
                                   <form action="{{ route('admin.license.deactivate') }}" method="POST" onsubmit="return confirm('Are you sure you want to deactivate the license? This will disable access to the platform.')">
                                        @csrf
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition">
                                             Deactivate License
                                        </button>
                                   </form>
                              @else
                                   <form action="{{ route('admin.license.activate') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="key" value="{{ $license->key }}">
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition">
                                             Activate License
                                        </button>
                                   </form>
                              @endif
                         </div>
                    @else
                         <div class="text-center py-12">
                              <x-icon name="key" class="w-16 h-16 text-slate-600 mx-auto mb-4" />
                              <h3 class="text-xl font-semibold text-white mb-2">No License Found</h3>
                              <p class="text-slate-400 mb-6">Enter a license key to activate the platform.</p>

                              <form action="{{ route('admin.license.activate') }}" method="POST" class="max-w-md mx-auto">
                                   @csrf
                                   <div class="mb-4">
                                        <input type="text" name="key" placeholder="Enter license key"
                                             class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                        @error('key')
                                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                        @enderror
                                   </div>
                                   <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition w-full">
                                        Activate License
                                   </button>
                              </form>
                         </div>
                    @endif
               </div>
          </div>
     </div>
</x-app-layout>
