
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Organization Settings</h5>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    Please correct the errors below.
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('settings.organization.update') }}"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">
                        Organization Logo
                    </label>

                    @if($settings?->logo_path)
                        <div class="mb-2">
                            <img
                                src="{{ asset('storage/' . $settings->logo_path) }}"
                                alt="Organization Logo"
                                style="max-width: 120px; max-height: 120px;"
                            >
                        </div>
                    @endif

                    <input
                        type="file"
                        name="logo"
                        class="form-control"
                        accept=".jpg,.jpeg,.png"
                    >

                    <small class="text-muted">
                        JPG or PNG, maximum 2 MB.
                    </small>

                    @error('logo')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Organization Name
                    </label>

                    <input
                        type="text"
                        name="organization_name"
                        class="form-control"
                        value="{{ old('organization_name', $settings?->organization_name) }}"
                        required
                    >

                    @error('organization_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Organization Subtitle / Office Title
                    </label>

                    <input
                        type="text"
                        name="organization_subtitle"
                        class="form-control"
                        value="{{ old('organization_subtitle', $settings?->organization_subtitle) }}"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>

                    <input
                        type="text"
                        name="address"
                        class="form-control"
                        value="{{ old('address', $settings?->address) }}"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Contact Information
                    </label>

                    <input
                        type="text"
                        name="contact_information"
                        class="form-control"
                        value="{{ old('contact_information', $settings?->contact_information) }}"
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Settings
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
