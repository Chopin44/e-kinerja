<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-tasks text-blue-600 mr-3"></i>
                        Kelola Pengguna
                    </h1>
                    <p class="text-gray-600 mt-1">Kelola Pengguna dan staf admin</p>
                </div>
                <a href="{{ route('users.create') }}" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Staf
                </a>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Nama</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Bidang</th>
                        <th class="px-4 py-2 text-left">Role</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2">{{ $user->bidang->nama ?? '-' }}</td>
                        <td class="px-4 py-2 capitalize">{{ $user->role }}</td>
                        <td class="px-4 py-2 text-center">
                            <form method="POST" action="{{ route('users.toggle', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="px-3 py-1 rounded-full text-xs font-medium 
                                    {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <a href="{{ route('users.edit', $user) }}"
                                class="btn-primary text-xs px-3 py-1 inline-flex items-center"><i
                                    class="fas fa-edit mr-1"></i>Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                data-confirm="Menghapus staf atau pengguna <b>{{ $user->name }}</b> berarti seluruh kegiatan yang berkaitan juga akan dihapus. Apakah Anda yakin ingin melanjutkan?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger text-xs px-3 py-1 inline-flex items-center">
                                    <i class="fas fa-trash-alt mr-1"></i>Hapus
                                </button>
                            </form>


                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-layout>