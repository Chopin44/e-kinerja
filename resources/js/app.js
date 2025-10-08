import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import Swal from 'sweetalert2';

window.Swal = Swal;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Penghapusan Data',
                html: `
                    <p class="text-gray-700 text-sm leading-relaxed">
                        ${this.getAttribute('data-confirm') || 
                        'Apakah Anda yakin ingin menghapus data ini?'} 
                        <br><br>
                        <b>Catatan:</b> Tindakan ini tidak dapat dibatalkan setelah disetujui.
                    </p>
                `,
                icon: 'warning',
                showCancelButton: true,
                focusCancel: true,
                confirmButtonText: '<i class="fas fa-check-circle mr-1"></i> Ya, Hapus',
                cancelButtonText: '<i class="fas fa-times-circle mr-1"></i> Batal',
                customClass: {
                    popup: 'rounded-xl shadow-lg border border-gray-200',
                    title: 'text-lg font-semibold text-gray-800',
                    htmlContainer: 'text-gray-700 text-sm',
                    confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium rounded-md px-4 py-2',
                    cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md px-4 py-2'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
});

