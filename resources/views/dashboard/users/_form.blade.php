@php
    $editMode = isset($user);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
            value="{{ old('full_name', $user->full_name ?? '') }}" required>
        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email ?? '') }}" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="phone">Phone</label>
        <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $user->phone ?? '') }}">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="role">Role</label>
        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
            @foreach(['admin', 'hgadmin', 'manager', 'staff', 'user', 'customer'] as $role)
                <option value="{{ $role }}" @selected(old('role', $user->role ?? 'user') === $role)>{{ strtoupper($role) }}</option>
            @endforeach
        </select>
        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="company_name">Company</label>
        <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
            value="{{ old('company_name', $user->company_name ?? '') }}">
        @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="country">Country Code</label>
        <input type="text" id="country" name="country" class="form-control @error('country') is-invalid @enderror"
            value="{{ old('country', $user->country ?? '') }}" maxlength="2" placeholder="TZ">
        @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="timezone">Timezone</label>
        <input type="text" id="timezone" name="timezone" class="form-control @error('timezone') is-invalid @enderror"
            value="{{ old('timezone', $user->timezone ?? '') }}" placeholder="Africa/Dar_es_Salaam">
        @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active"
                @checked(old('is_active', $user->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active user</label>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password">Password {{ $editMode ? '(optional)' : '' }}</label>
        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
            {{ $editMode ? '' : 'required' }}>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label" for="address">Address</label>
        <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $user->address ?? '') }}</textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
