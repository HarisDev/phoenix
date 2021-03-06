@php
    $block = $renderData->block;
    $settings = $block->settings;
    $block->content = nl2br($block->content);
    $parentDisplay = isset($renderData->display) ? $renderData->display : 'block';
@endphp

@includeIf('content.blocks.text.css')

<div class="text-block text-{{ $block->unique_id }} {{ $settings->get('customClass') }}" @if($settings->onClick == 'open-link')onclick="window.open('{{ $settings->get('link') }}', '{{ $settings->get('target') }}');"@endif>
    {!! $block->content !!}
</div>
