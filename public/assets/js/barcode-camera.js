(function (window, document) {
    'use strict';

    function createCameraScanner(options) {
        const button = document.getElementById(options.buttonId);
        const video = document.getElementById(options.videoId);
        const status = document.getElementById(options.statusId);
        const input = document.getElementById(options.inputId);
        const closeButton = document.getElementById(options.closeId);
        let stream = null;
        let animationFrame = null;
        let detector = null;

        if (!button || !video || !input) {
            return;
        }

        function setStatus(message, isError) {
            if (status) {
                status.textContent = message;
                status.classList.toggle('text-danger', Boolean(isError));
                status.classList.toggle('text-success', !isError);
            }
        }

        function stop() {
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
                animationFrame = null;
            }
            if (stream) {
                stream.getTracks().forEach((track) => track.stop());
                stream = null;
            }
            video.srcObject = null;
            video.classList.add('d-none');
            if (closeButton) closeButton.classList.add('d-none');
            button.classList.remove('d-none');
        }

        function accept(code) {
            const value = String(code || '').trim();
            if (!value) return;
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            setStatus('Barcode berhasil diisi: ' + value, false);
            stop();
            if (typeof options.onDetected === 'function') options.onDetected(value);
        }

        function scanFrame() {
            if (!detector || !stream) return;
            detector.detect(video).then((barcodes) => {
                if (barcodes.length > 0 && barcodes[0].rawValue) {
                    accept(barcodes[0].rawValue);
                    return;
                }
                animationFrame = requestAnimationFrame(scanFrame);
            }).catch(() => {
                setStatus('Kamera aktif, barcode belum terbaca.', false);
                animationFrame = requestAnimationFrame(scanFrame);
            });
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

            if (!window.BarcodeDetector) {
                setStatus('Browser tidak mendukung baca barcode otomatis. Gunakan Chrome Android terbaru atau input scanner USB.', true);
                return;
            }
            try {
                detector = new BarcodeDetector({ formats: options.formats || ['ean_13', 'ean_8', 'code_128', 'upc_a', 'upc_e', 'itf', 'codabar'] });
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
                video.srcObject = stream;
                video.classList.remove('d-none');
                closeButton?.classList.remove('d-none');
                button.classList.add('d-none');
                await video.play();
                setStatus('Arahkan kamera ke barcode.', false);
                scanFrame();
            } catch (error) {
                stop();
                const messages = {
                    NotAllowedError: 'Izin kamera ditolak. Izinkan kamera pada pengaturan browser.',
                    NotFoundError: 'Kamera tidak ditemukan pada perangkat ini.',
                    NotReadableError: 'Kamera sedang dipakai aplikasi lain.',
                    SecurityError: 'Akses kamera diblokir oleh kebijakan keamanan browser.',
                };
                setStatus(messages[error.name] || 'Kamera tidak dapat digunakan.', true);
            }
        }

        button.addEventListener('click', start);
        closeButton?.addEventListener('click', stop);
        window.addEventListener('pagehide', stop);
    }

    window.createBarcodeCameraScanner = createCameraScanner;
}(window, document));
