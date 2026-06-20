@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Edit Kategori</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('categories.update', $category->slug) }}" method="POST">
                            @csrf
                            @method('put')
                            <div class="row align-items-center">
                                <!-- Input: Name -->
                                <div class="form-group col-md-12">
                                    <label for="name">Nama Kategori <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                        value="{{ old('name', $category->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Input: Slug (Auto-generated) -->
                                <div class="form-group col-md-12">
                                    <label for="slug">Slug Kategori <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug"
                                        value="{{ old('slug', $category->slug) }}" required readonly>
                                    @error('slug')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Input: Description -->
                                <div class="form-group col-md-12">
                                    <label for="description">Deskripsi</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3">{{ old('description', $category->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tax_rate">Pajak Kategori (%)</label>
                                    <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $category->tax_rate) }}" min="0" max="100" step="0.01">
                                    @error('tax_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                </div>

                            <!-- Action Buttons -->
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary mr-2">Perbarui</button>
                                <a class="btn btn-outline-danger" href="{{ route('categories.index') }}">Batal</a>
                            </div>
                            </form>
                            </div>
                            </div>
                            </div>
                            </div>
    </div>

    <!-- Script: Auto-Generate Slug -->
    <script>
            const title = document.querySelector("#name");
            const slug = document.querySelector("#slug");
            title.addEventListener("keyup", function () {
                let preslug = title.value
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-z0-9 ]/g, "")
                .replace(/\s+/g, "-")
                .replace(/-+/g, "-")
                .replace(/^-|-$/g, "");
                slug.value = preslug;
            });
        </script>
@endsection
