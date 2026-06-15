var userId = document.querySelector('meta[name="user-id"]')?.content;

if (userId && window.Echo) {
    window.Echo.private('user.' + userId)
        .listen('.notification.created', function (e) {
            var badge = document.getElementById('notificationBadge');
            if (badge) {
                var count = parseInt(badge.textContent) || 0;
                badge.textContent = count + 1;
                if (badge.style.display === 'none') {
                    badge.style.display = 'inline';
                }
            }
        });

    if (window.conversationId) {
        window.Echo.private('conversation.' + window.conversationId)
            .listen('.message.sent', function (e) {
                var chatBox = document.getElementById('chatMessages');
                if (!chatBox) return;

                var isMine = parseInt(e.sender_id) === parseInt(userId);

                var outer = document.createElement('div');
                outer.className = 'd-flex ' + (isMine ? 'justify-content-start' : 'justify-content-end');

                var bubble = document.createElement('div');
                bubble.className = 'p-3 rounded-3';
                bubble.style.cssText = 'max-width: 75%; background: ' + (isMine ? 'var(--blue)' : 'white')
                    + '; color: ' + (isMine ? 'white' : 'var(--dark)')
                    + '; border-bottom-' + (isMine ? 'right' : 'left') + '-radius: 4px;'
                    + ' box-shadow: 0 1px 3px rgba(0,0,0,0.08);';

                var msgDiv = document.createElement('div');
                msgDiv.style.cssText = 'font-size: 0.9rem; line-height: 1.5;';
                msgDiv.textContent = e.message;

                var metaDiv = document.createElement('div');
                metaDiv.className = 'd-flex justify-content-end gap-1 mt-1';
                metaDiv.style.cssText = 'font-size: 0.65rem; opacity: 0.7;';

                var timeSpan = document.createElement('span');
                timeSpan.textContent = e.time_formatted;

                metaDiv.appendChild(timeSpan);

                if (isMine) {
                    var checkSpan = document.createElement('span');
                    checkSpan.innerHTML = '<i class="fas fa-check"></i>';
                    metaDiv.appendChild(checkSpan);
                }

                bubble.appendChild(msgDiv);
                bubble.appendChild(metaDiv);
                outer.appendChild(bubble);
                chatBox.appendChild(outer);
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }
}
