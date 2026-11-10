<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Daftar Produk</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto">
        @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
        @endif

        <a href="{{ route('products.create') }}" class="btn btn-primary mb-4 inline-block">
            + Tambah Produk
        </a>

        @if ($products->isEmpty())
        <p>Belum ada produk.</p>
        @else
        <table class="w-full text-left">
            <thead>
                <tr>
                    <th class="py-2">Nama</th>
                    <th class="py-2">Harga</th>
                    <th class="py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                <tr>
                    <td class="py-2">{{ $product->name }}</td>
                    <td class="py-2">Rp {{ number_format($product->price) }}</td>
                    <td class="py-2">
                        <a href="{{ route('products.edit', $product) }}">Edit</a>
                        <form action="{{ route('products.destroy', $product) }}" method="post" class="inline"
                            onsubmit="return confirm('Hapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</x-app-layout>