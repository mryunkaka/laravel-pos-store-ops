(function (window, document) {
    'use strict';

    function createProductImageCamera(options) {
        const button = document.getElementById(options.buttonId);
        const video = document.getElementById(options.videoId);
        const captureButton = document.getElementById(options.captureId);
        const closeButton = document.getElementById(options.closeId);
        const input = document.getElementById(options.inputId);
        const preview = document.getElementById(options.previewId);
        const status = document.getElementById(options.statusId);
        let stream = null;

        if (!button || !video || !captureButton || !closeButton || !input || !preview) {
            return;
        }

        function setStatus(message, isError) {
            if (!status) return;
            status.textContent = message;
            status.classList.toggle('text-danger', Boolean(isError));
            status.classList.toggle('text-success', !isError);
        }

        function stop() {
            if (stream) {
                stream.getTracks().forEach((track) => track.stop());
                stream = null;
            }
            video.srcObject = null;
            video.classList.add('d-none');
            captureButton.classList.add('d-none');
            closeButton.classList.add('d-none');
            button.classList.remove('d-none');
        }

        async function start() {
            if (!window.isSecureContext) {
                setStatus('Kamera membutuhkan HTTPS atau localhost.', true);
                return;
            }

            if (!navigator.mediaDevices?.getUserMedia) {
                setStatus('Browser tidak mendukung akses kamera.', true);
                return;
            }

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false
                });
                video.srcObject = stream;
                video.classList.remove('d-none');
                captureButton.classList.remove('d-none');
                closeButton.classList.remove('d-none');
                button.classList.add('d-none');
                await video.play();
                setStatus('Arahkan kamera ke gambar produk, lalu tekan Ambil Foto.', false);
            } catch (error) {
                stop();
                const messages = {
                    NotAllowedError: 'Izin kamera ditolak. Izinkan kamera pada pengaturan browser.',
                    NotFoundError: 'Kamera tidak ditemukan pada perangkat ini.',
                    NotReadableError: 'Kamera sedang dipakai aplikasi lain.',
                    SecurityError: 'Akses kamera diblokir oleh kebijakan keamanan browser.'
                };
                setStatus(messages[error.name] || 'Kamera tidak dapat digunakan.', true);
            }
        }

        function capture() {
            if (!stream || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
                setStatus('Kamera belum siap.', true);
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => {
                if (!blob) {
                    setStatus('Gambar kamera gagal dibuat.', true);
                    return;
                }

                const file = new File([blob], 'product-camera.jpg', { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
                setStatus('Foto produk siap diunggah.', false);
                stop();
            }, 'image/jpeg', 0.9);
        }

        button.addEventListener('click', start);
        captureButton.addEventListener('click', capture);
        closeButton.addEventListener('click', () => {
            stop();
            setStatus('Kamera ditutup.', false);
        });
        window.addEventListener('pagehide', stop);
    }

    window.createProductImageCamera = createProductImageCamera;
}(window, document));