@extends('layouts.app')
@section('content')
<div class="notifications-page">
    <div class="notifications-header">
        <div>
            <h1>Notifications</h1>
            <p class="notifications-subtitle">Stay updated with your activity.</p>
        </div>

        @if($notifications->contains(fn ($notification) => !$notification->is_read))
            <form action="{{ route('notifications.readAll') }}"
                  method="POST"
                  class="mark-all-read-form">
                @csrf
                @method('PATCH')

                <button type="submit" class="mark-all-read-btn">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    <div class="notifications-list">
        @forelse($notifications as $notification)
            @php
                $destination = $notification->post_id
                    ? route('dashboard') . '#post-' . $notification->post_id
                    : ($notification->actor
                        ? route('users.show', $notification->actor)
                        : route('dashboard'));
            @endphp

            <div class="notification-item {{ $notification->is_read ? 'read' : 'unread' }}"
                 data-notification-id="{{ $notification->id }}">

                <a href="{{ $destination }}"
                   class="notification-content notification-link"
                   data-read-url="{{ route('notifications.read', $notification->id) }}"
                   data-is-read="{{ $notification->is_read ? '1' : '0' }}">

                    <div class="notification-avatar">
                        @if($notification->actor?->profile_photo)
                            <img
                                src="{{ asset('storage/' . $notification->actor->profile_photo) }}"
                                alt="Profile picture">
                        @else
                            {{ strtoupper(substr($notification->actor->name ?? 'S', 0, 1)) }}
                        @endif
                    </div>

                    <div class="notification-text">
                        <p>
                            <strong>{{ $notification->actor->name ?? 'Someone' }}</strong>
                            {{ $notification->message }}
                        </p>

                        <small>{{ $notification->created_at->diffForHumans() }}</small>
                    </div>

                    <span class="unread-indicator" aria-label="Unread"></span>
                </a>

                @if(!$notification->is_read)
                    <form action="{{ route('notifications.read', $notification->id) }}"
                          method="POST"
                          class="mark-read-form">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="mark-read-btn">
                            Mark as read
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="notifications-empty">
                <h3>You're all caught up!</h3>
                <p>You don't have any notifications yet.</p>
            </div>
        @endforelse
    </div>

    <div class="notifications-pagination">
        {{ $notifications->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    function updateNavbarBadge(count) {
        const badge = document.querySelector('.notification-badge');

        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
            } else {
                badge.remove();
            }
        } else if (count > 0) {
            const notificationLink = document.querySelector(
                'a[href="{{ route('notifications.index') }}"]'
            );

            if (notificationLink) {
                const newBadge = document.createElement('span');
                newBadge.className = 'notification-badge';
                newBadge.textContent = count > 99 ? '99+' : count;
                notificationLink.appendChild(newBadge);
            }
        }
    }

    function updateUnreadCount() {
        const unreadCount = document.querySelectorAll(
            '.notification-item.unread'
        ).length;

        updateNavbarBadge(unreadCount);
    }

    async function markNotificationRead(form) {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        });

        if (!response.ok) {
            throw new Error('Could not update notification.');
        }

        const item = form.closest('.notification-item');

        if (item) {
            item.classList.remove('unread');
            item.classList.add('read');

            const link = item.querySelector('.notification-link');
            if (link) {
                link.dataset.isRead = '1';
            }

            form.remove();
        }

        updateUnreadCount();
    }

    document.querySelectorAll('.mark-read-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const button = form.querySelector('button');
            button.disabled = true;

            try {
                await markNotificationRead(form);
            } catch (error) {
                alert('Unable to mark this notification as read. Please try again.');
                button.disabled = false;
            }
        });
    });

    document.querySelectorAll('.mark-all-read-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const button = form.querySelector('button');
            button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                });

                if (!response.ok) {
                    throw new Error('Could not update notifications.');
                }

                document.querySelectorAll('.notification-item.unread')
                    .forEach(function (item) {
                        item.classList.remove('unread');
                        item.classList.add('read');

                        const link = item.querySelector('.notification-link');
                        if (link) {
                            link.dataset.isRead = '1';
                        }

                        item.querySelector('.mark-read-form')?.remove();
                    });

                form.remove();
                updateUnreadCount();
            } catch (error) {
                alert('Unable to mark all notifications as read. Please try again.');
                button.disabled = false;
            }
        });
    });

    document.querySelectorAll('.notification-link').forEach(function (link) {
        link.addEventListener('click', async function (event) {
            if (link.dataset.isRead === '1') {
                return;
            }

            event.preventDefault();

            try {
                const response = await fetch(link.dataset.readUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new URLSearchParams({
                        _token: csrfToken,
                        _method: 'PATCH'
                    })
                });

                if (!response.ok) {
                    throw new Error('Could not mark notification as read.');
                }

                const item = link.closest('.notification-item');

                if (item) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    link.dataset.isRead = '1';
                    item.querySelector('.mark-read-form')?.remove();
                }

                updateUnreadCount();
                window.location.href = link.href;
            } catch (error) {
                window.location.href = link.href;
            }
        });
    });
});
</script>
@endsection