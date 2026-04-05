<div class="min-h-[80vh] flex items-center justify-center">
    <div class="bg-white p-10 rounded-[2rem] shadow-2xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black text-blue-600 tracking-tight">🔐 Masuk</h1>
            <p class="text-gray-500 mt-2">Sistem Kasir Toko</p>
        </div>

        <form wire:submit="login" class="space-y-6">
            <div>
                <label class="text-xs font-bold text-gray-400 uppercase">Alamat Email</label>
                <input type="email" wire:model="email" autofocus class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1 font-bold">
                @error('email') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="text-xs font-bold text-gray-400 uppercase">Password</label>
                <input type="password" wire:model="password" class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1 font-bold">
            </div>

            <button type="submit" wire:loading.attr="disabled" class="w-full bg-blue-600 text-white font-black py-4 rounded-xl hover:bg-blue-700 transition shadow-lg mt-4">
                <span wire:loading.remove wire:target="login">MASUK SEKARANG</span>
                <span wire:loading wire:target="login">MEMERIKSA...</span>
            </button>
        </form>
    </div>
</div>