@props(['link', 'name', 'active' => false, 'isPremium' => true])
<li class="submenu-item {{ $active ? 'active' : '' }} {{ $slot->isEmpty() ? '' : 'has-sub' }}">
  <a href="{{ $link }}" class="submenu-link"><span>{{ $name }}
      @if (!$isPremium)
        <small style="font-size: 12px;">(Premium Only)</small>
      @endif
    </span></a>
  @if (!$slot->isEmpty())
    <ul class="submenu {{ $active ? 'active' : '' }}">
      {{ $slot ?? '' }}
    </ul>
  @endif
</li>
