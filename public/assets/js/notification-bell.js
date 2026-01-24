// Notification Bell System
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    setInterval(loadNotifications, 60000); // Refresh every 60 seconds
    
    const dropdownButton = document.getElementById('dropdownMenuButton');
    if (dropdownButton) {
        dropdownButton.addEventListener('click', function() {
            loadNotifications();
        });
    }
    
    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllAsRead();
        });
    }
});

function loadNotifications() {
    const notificationRoute = document.querySelector('meta[name="notification-route"]')?.content;
    if (!notificationRoute) return;
    
    fetch(notificationRoute + '?limit=5')
        .then(response => response.json())
        .then(data => updateNotificationUI(data))
        .catch(error => console.error('Error loading notifications:', error));
}

function updateNotificationUI(data) {
    const badge = document.getElementById('notificationBadge');
    const bellIcon = document.getElementById('bellIcon');
    const count = document.getElementById('notificationCount');
    const list = document.getElementById('notificationList');
    const markAllBtn = document.getElementById('markAllReadBtn');
    
    if (!badge || !count || !list) return;
    
    // Update badge with icon animation
    if (data.unread_count > 0) {
        badge.textContent = data.unread_count;
        badge.style.display = 'block';
        // Add visual emphasis to bell icon
        if (bellIcon) {
            bellIcon.classList.add('text-danger');
        }
    } else {
        badge.style.display = 'none';
        if (bellIcon) {
            bellIcon.classList.remove('text-danger');
        }
    }
    
    // Update count text
    count.textContent = data.unread_count + ' Baru';
    
    // Show/hide mark all read button
    if (markAllBtn) {
        markAllBtn.style.display = data.unread_count > 0 ? 'block' : 'none';
    }
    
    // Update notification list
    if (data.notifications.length === 0) {
        list.innerHTML = '<li class="text-center py-3"><p class="text-xs text-secondary mb-0">Tidak ada notifikasi</p></li>';
        return;
    }
    
    let html = '';
    data.notifications.forEach(notification => {
        const bgClass = 'bg-gradient-' + notification.icon_color;
        const unreadClass = notification.is_unread ? 'bg-light' : '';
        const boldClass = notification.is_unread ? 'font-weight-bold' : 'font-weight-normal';
        
        html += `
            <li class="mb-2">
                <a class="dropdown-item border-radius-md ${unreadClass}" href="#" onclick="handleNotificationClick(event, ${notification.id}, '${notification.url || ''}')">
                    <div class="d-flex py-1">
                        <div class="icon icon-shape icon-sm ${bgClass} shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="${notification.icon} text-white text-xs"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center flex-grow-1">
                            <h6 class="text-sm ${boldClass} mb-1">${notification.title}</h6>
                            ${notification.message ? `<p class="text-xs text-secondary mb-1">${notification.message}</p>` : ''}
                            <p class="text-xs text-secondary mb-0"><i class="fa fa-clock me-1"></i>${notification.relative_time}</p>
                        </div>
                    </div>
                </a>
            </li>
        `;
    });
    list.innerHTML = html;
}

function handleNotificationClick(event, notificationId, url) {
    event.preventDefault();
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }
    
    fetch(`/adminui/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Redirect if URL exists
        if (url && url !== 'null' && url !== '' && url !== 'undefined') {
            window.location.href = url;
        } else {
            // Reload notifications to show updated state
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        // Still redirect even if marking fails
        if (url && url !== 'null' && url !== '' && url !== 'undefined') {
            window.location.href = url;
        }
    });
}

function markAllAsRead() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }
    
    fetch('/adminui/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        // Reload notifications to show all as read
        loadNotifications();
        
        // Show success toast
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Semua notifikasi ditandai sudah dibaca',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}
