<ul class="d-flex nav nav-pills mb-3 text-center profile-tab">
    <li class="nav-item">
        <a href="{{ route('profile') }}" class="nav-link {{ Request::is('profile') ? 'active' : '' }}">Profil</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('profile.edit') }}" class="nav-link {{ Request::is('profile/edit') ? 'active' : '' }}">Edit Profil</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('profile.change-password') }}" class="nav-link {{ Request::is('profile/change-password') ? 'active' : '' }}">Ubah Password</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('profile.delete') }}" class="nav-link {{ Request::is('profile/delete') ? 'active' : '' }}">Hapus Akun</a>
    </li>
</ul>
