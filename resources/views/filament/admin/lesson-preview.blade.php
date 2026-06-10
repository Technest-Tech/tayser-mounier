{{-- Plays a lesson video inside a Filament action modal. --}}
<div class="overflow-hidden rounded-xl bg-black">
    <div style="position: relative; padding-top: 56.25%;">
        <iframe
            src="{{ $url }}"
            style="position: absolute; inset: 0; height: 100%; width: 100%; border: 0;"
            loading="lazy"
            allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen"
            allowfullscreen
        ></iframe>
    </div>
</div>
