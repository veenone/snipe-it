@props([
    'target' => null,
])

@if ($target)
    @php
        $iconType = match (class_basename($target)) {
            'User' => 'user',
            'Asset' => 'asset',
            'Location' => 'location',
            default => null,
        };
        $tagColor = $target->tag_color ?? null;
        $link = method_exists($target->present(), 'formattedNameLink')
            ? $target->present()->formattedNameLink()
            : e($target->present()->fullName() ?? $target->name ?? '');
    @endphp

    <x-form.static :label="trans('general.checked_out_to')">
        @if ($iconType)
            <x-icon :type="$iconType" class="fa-fw" style="{{ $tagColor ? 'color: '.e($tagColor).';' : '' }}" />
        @endif
        {!! $link !!}
    </x-form.static>
@endif
