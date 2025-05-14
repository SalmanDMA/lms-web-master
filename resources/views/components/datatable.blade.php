@props(['title', 'customTheme'])

<div class="card" style="background-color: {{ $customTheme->primary_color ?? 'bg-white' }};">
  <div class="card-header" style="background-color: {{ $customTheme->primary_color ?? 'bg-white' }};">
    <h5 class="card-title" style="color: {{ $customTheme->accent_color ?? 'text-muted' }};">
      {{ $title }}
    </h5>
  </div>
  <div class="card-body" style="background-color: {{ $customTheme->primary_color ?? 'bg-white' }};">
    {{ $slot }}
  </div>
</div>
