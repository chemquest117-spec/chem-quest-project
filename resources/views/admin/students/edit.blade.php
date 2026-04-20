<x-app-layout>
     @section('title', 'Edit Student')

     <div class="py-8">
          <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
               <a href="{{ route('admin.students.show', $user) }}"
                    class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Back to Student</a>
               <h1 class="text-3xl font-bold text-white mt-1 mb-8">Edit Student</h1>

               <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                    <form action="{{ route('admin.students.update', $user) }}" method="POST">
                         @csrf
                         @method('PUT')

                         <div class="space-y-6">
                              <div>
                                   <label for="name" class="block text-sm font-medium text-white mb-2">Name</label>
                                   <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                        class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                   @error('name')
                                       <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                   @enderror
                              </div>

                              <div>
                                   <label for="email" class="block text-sm font-medium text-white mb-2">Email</label>
                                   <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                        class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                   @error('email')
                                       <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                   @enderror
                              </div>
                         </div>

                         <div class="mt-8 flex gap-4">
                              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                                   Update Student
                              </button>
                              <a href="{{ route('admin.students.show', $user) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition">
                                   Cancel
                              </a>
                         </div>
                    </form>
               </div>
          </div>
     </div>
</x-app-layout>
