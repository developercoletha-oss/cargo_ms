@props([
    'title',
    'subtitle' => null,
])

<div class="coletha-shared-page-header__text">
    <h1 class="coletha-shared-page-header__title">{{ $title }}</h1>
    @if ($subtitle)
        <p class="coletha-shared-page-header__subtitle">{{ $subtitle }}</p>
    @endif
</div>
