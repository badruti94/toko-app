<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Daftar Produk</h2>
    </x-slot>
    <div class="py-8 max-w-3xl mx-auto">
        @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            Periksa kembali isian form
        </div>
        @endif

        <form action="{{ route('products.store') }}" method="post" class="space-y-4">
            @csrf

            <div>
                <label class="block mb-1">Nama</label>
                <input name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2">
                @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Harga (angka, tanpa titik)</label>
                <input name="price" type="number" value="{{ old('price') }}" class="w-full border rounded px-3 py-2">
                @error('price') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Deskripsi (opsional)</label>
                <textarea name="description" class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
            </div>

            <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
            <a href="{{ route('products.index') }}" class="ml-2">Batal</a>
        </form>
    </div>
</x-app-layout>