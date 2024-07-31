<th scope="col" wire:click="setSortBy('{{ $name }}')">
    <button class="btn btn-link d-flex align-items-center p-0 text-decoration-none text-dark fw-semibold">
        <span class="text-truncate">{{ $displayName }}</span>
        @if ($sortBy !== $name)
        @elseif($sortDir === 'ASC')
            <i class="bi bi-sort-alpha-up"></i>
        @else
            <i class="bi bi-sort-alpha-down"></i>
        @endif
    </button>
</th>
