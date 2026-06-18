@extends('auth.body.main')

@section('container')
    <div class="row align-items-center justify-content-center height-self-center">
        <div class="col-lg-8">
            <div class="card auth-card">
                <div class="card-body p-0">
                    <div class="d-flex align-items-center auth-content">
                        <!-- Section: Email Verification Form -->
                        <div class="col-lg-7 align-self-center">
                            <div class="p-3">
                                <h2 class="mb-2">Verifikasi Email Anda</h2>
                                <p>Terima kasih sudah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda? Jika Anda tidak menerima email tersebut, kami akan dengan senang hati mengirimkan yang baru.</p>

                                <!-- Alert: Session Status -->
                                @if (session('status') == 'verification-link-sent')
                                    <div class="alert alert-success" role="alert">
                                        Link verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary">Kirim Ulang Email Verifikasi</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link text-primary p-0">Keluar</button>
                                </form>
                            </div>
                        </div>

                        <!-- Section: Right Side Image -->
                        <div class="col-lg-5 content-right">
                            <img src="{{ asset('assets/images/login/01.png') }}" class="img-fluid image-right" alt="Email Verification Illustration">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
