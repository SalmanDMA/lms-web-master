<style>
  .page-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .breadcrumb-header {
    margin-right: 2rem;
    display: inline-flex;
    align-items: center;
  }

  .breadcrumb-item a {
    text-decoration: none;
    color: #007bff;
  }

  .breadcrumb-item a:hover {
    text-decoration: underline;
  }

  .notification-wrapper {
    display: flex;
    align-items: center;
  }

  .notification-icon {
    position: relative;
    display: inline-block;
    font-size: 1.5rem;
    color: #6c757d;
    text-decoration: none;
  }

  .notification-icon:hover {
    color: #495057;
  }

  .notification-icon .indicator {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 18px;
    height: 18px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 18px;
    font-size: 12px;
    font-weight: bold;
    border: 2px solid white;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
  }
</style>

<div class="page-heading">
  <div class="page-title">
    <div class="col">
      <h3 style="color: {{ $customTheme->accent_color ?? '#435ebe' }};">{{ $title }}</h3>
      <p style="color: {{ $customTheme->accent_color ?? '#607080' }};">{{ $subtitle }}</p>
    </div>
    <div class="notification-wrapper">
      <nav aria-label="breadcrumb" class="breadcrumb-header">
        <ol class="breadcrumb">
          @foreach ($breadcrumbs as $breadcrumb)
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}"
              @if ($loop->last) aria-current="page" @endif>
              @if (!$loop->last && isset($breadcrumb['url']))
                <a href="{{ $breadcrumb['url'] }}" style="color: {{ $customTheme->secondary_color ?? '#435ebe' }};">
                  {{ $breadcrumb['label'] }}
                </a>
              @else
                <span style="color: {{ $customTheme->accent_color ?? '#607080' }};">
                  {{ $breadcrumb['label'] }}
                </span>
              @endif
            </li>
          @endforeach
        </ol>
      </nav>
      @if ($showNotifications)
        <a class="notification-icon" href="{{ route('notifications.index') }}" title="Lihat Semua Notifikasi">
          <i class="bi bi-bell"></i>
          @if ($unreadNotifications > 0)
            <span class="indicator">{{ $unreadNotifications }}</span>
          @endif
        </a>
      @endif
    </div>
  </div>
</div>
