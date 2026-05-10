@php
    $detail = $cargo->detail ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Origin City</label>
        <input type="text" name="origin_city" class="form-control" required
            value="{{ old('origin_city', $cargo->origin_city ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Destination City</label>
        <input type="text" name="destination_city" class="form-control" required
            value="{{ old('destination_city', $cargo->destination_city ?? '') }}">
    </div>
    <input type="hidden" name="origin_country" value="TZ">
    <input type="hidden" name="destination_country" value="TZ">
    <div class="col-md-6">
        <label class="form-label">Pickup Location</label>
        <input type="text" name="origin_address" class="form-control"
            value="{{ old('origin_address', $cargo->origin_address ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Drop-off Location</label>
        <input type="text" name="destination_address" class="form-control"
            value="{{ old('destination_address', $cargo->destination_address ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Pickup Date</label>
        <input type="date" name="pickup_date" class="form-control"
            value="{{ old('pickup_date', optional($cargo->pickup_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Delivery Date</label>
        <input type="date" name="delivery_date" class="form-control"
            value="{{ old('delivery_date', optional($cargo->delivery_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Cargo Type</label>
        <input type="text" name="cargo_type" class="form-control"
            value="{{ old('cargo_type', $detail->cargo_type ?? '') }}" placeholder="Electronics, Food, Medical...">
    </div>
    <div class="col-md-6">
        <label class="form-label">Description</label>
        <input type="text" name="description" class="form-control" required
            value="{{ old('description', $detail->description ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Weight (kg)</label>
        <input type="number" step="0.01" min="0.01" name="weight_kg" class="form-control" required
            value="{{ old('weight_kg', $detail->weight_kg ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Volume (CBM)</label>
        <input type="number" step="0.01" min="0" name="volume_cbm" class="form-control"
            value="{{ old('volume_cbm', $detail->volume_cbm ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Quantity</label>
        <input type="number" min="1" name="quantity" class="form-control" required
            value="{{ old('quantity', $detail->quantity ?? 1) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Package Count</label>
        <input type="number" min="1" name="package_count" class="form-control" required
            value="{{ old('package_count', $detail->package_count ?? 1) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Estimated Value</label>
        <input type="number" step="0.01" min="0" name="estimated_value" class="form-control"
            value="{{ old('estimated_value', $detail->estimated_value ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label d-block">Properties</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="is_fragile" value="1"
                @checked(old('is_fragile', $detail->is_fragile ?? false))>
            <label class="form-check-label">Fragile</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="is_hazardous" value="1"
                @checked(old('is_hazardous', $detail->is_hazardous ?? false))>
            <label class="form-check-label">Hazardous</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Special Instructions</label>
        <textarea name="special_instructions" class="form-control" rows="3">{{ old('special_instructions', $detail->special_instructions ?? '') }}</textarea>
    </div>
</div>
