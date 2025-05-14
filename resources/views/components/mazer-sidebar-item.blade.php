@props(['icon', 'link', 'name', 'active' => false, 'isPremium' => true])

<li class="sidebar-item {{ $active ? 'active' : '' }} {{ !$slot->isEmpty() ? 'has-sub' : '' }}">
  <a href="{{ $slot->isEmpty() ? $link : '#' }}" class="sidebar-link {{ !$isPremium ? 'disabled' : '' }}"
    {{ !$isPremium && !$slot->isEmpty() ? 'style=pointer-events:none;opacity:0.5;' : '' }}>
    <i class="{{ $icon }}"></i>
    <span>{{ $name }}
      @if (!$isPremium)
        <small style="font-size: 12px;">(Premium Only)</small>
      @endif
    </span>
  </a>
  @if (!$slot->isEmpty())
    <ul class="submenu {{ $active ? 'active' : '' }} {{ !$isPremium ? 'disabled' : '' }}"
      {{ !$isPremium ? 'style=pointer-events:none;opacity:0.5;' : '' }}>
      {{ $slot }}
    </ul>
  @endif
</li>
