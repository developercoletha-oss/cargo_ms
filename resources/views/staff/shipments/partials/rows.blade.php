@foreach($shipments as $shipment)
<tr>
    <td>
        <strong>{{ $shipment->tracking_number }}</strong>
    </td>
    <td>
        {{ $shipment->origin_city ?? $shipment->origin_country }}
        <br><small class="text-muted">{{ $shipment->origin_country }}</small>
    </td>
    <td>
        {{ $shipment->destination_city ?? $shipment->destination_country }}
        <br><small class="text-muted">{{ $shipment->destination_country }}</small>
    </td>
    <td>
        <span class="badge bg-secondary-subtle text-secondary border">
            {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
        </span>
    </td>
    <td>
        <span class="badge bg-secondary-subtle text-secondary border">
            {{ ucfirst($shipment->priority) }}
        </span>
    </td>
    <td>
        {{ $shipment->assignedUser?->name ?? 'Unassigned' }}
    </td>
    <td>
        {{ $shipment->created_at->diffForHumans() }}
    </td>
    <td>
        <a href="#" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye"></i> View
        </a>
    </td>
</tr>
@endforeach
