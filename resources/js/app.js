import './bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    // Send AJAX requests and safely handle JSON or unexpected responses.
    async function sendRequest(url, body, method = 'POST') {
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch(url, {
            method,
            headers,
            body
        });

        const text = await response.text();
        let data = {};

        try {
            data = text ? JSON.parse(text) : {};
        } catch {
            throw new Error(
                'The server returned an unexpected response. Please refresh and try again.'
            );
        }

        if (!response.ok) {
            const validationErrors = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : data.message;

            throw new Error(
                validationErrors || 'The request could not be completed.'
            );
        }

        return data;
    }

    function createAvatar(className, photo, name, altText) {
        const avatar = document.createElement('div');
        avatar.className = className;

        if (photo) {
            const img = document.createElement('img');
            img.src = '/storage/' + photo;
            img.alt = altText || `${name}'s profile photo`;
            avatar.appendChild(img);
        } else {
            avatar.textContent = (name || 'U').charAt(0).toUpperCase();
        }

        return avatar;
    }

    function updateCommentCounts(section, count) {
        const headingCount = section.querySelector('h3 span');

        if (headingCount) {
            headingCount.textContent = count;
        }

        const postCard = section.closest('.post-card');
        const countLink = postCard?.querySelector('.comment-count');

        if (countLink) {
            countLink.textContent =
                `💬 ${count} ${count === 1 ? 'comment' : 'comments'}`;
        }
    }

    function showEmptyComments(list) {
        if (!list.querySelector('.comment-item')) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'empty-comments';
            emptyMessage.textContent =
                'No comments yet. Be the first to start the conversation.';

            list.appendChild(emptyMessage);
        }
    }

    function updateNavbarBadge(count) {
        const badge = document.querySelector('.notification-badge');

        if (count <= 0) {
            badge?.remove();
            return;
        }

        if (badge) {
            badge.textContent = count > 99 ? '99+' : String(count);
            return;
        }

        const notificationLink = document.querySelector(
            'a[href*="/notifications"]'
        );

        if (notificationLink) {
            const newBadge = document.createElement('span');
            newBadge.className = 'notification-badge';
            newBadge.textContent = count > 99 ? '99+' : String(count);
            notificationLink.appendChild(newBadge);
        }
    }

    function decrementNavbarBadge() {
        const badge = document.querySelector('.notification-badge');

        if (!badge) return;

        const current = badge.textContent.trim();

        // The exact count is unknown when the badge displays 99+.
        if (current === '99+') return;

        const count = Number.parseInt(current, 10) || 0;
        updateNavbarBadge(count - 1);
    }

    function markNotificationReadInUI(item) {
        if (!item || !item.classList.contains('unread')) {
            return;
        }

        item.classList.remove('unread');
        item.classList.add('read');

        const link = item.querySelector('.notification-link');

        if (link) {
            link.dataset.isRead = '1';
        }

        item.querySelector('.mark-read-form')?.remove();
        decrementNavbarBadge();
    }

    // AJAX: Add comments without reloading the page.
    async function addComment(form) {
        const input = form.querySelector('input[name="content"]');
        const button = form.querySelector('button[type="submit"]');

        if (!input || !button) return;

        const content = input.value.trim();

        if (!content) {
            input.focus();
            return;
        }

        const section = form.closest('.comments-section');
        const list = section?.querySelector('.comments-list');

        if (!section || !list) {
            throw new Error('Comment section not found.');
        }

        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Sending...';

        try {
            const data = await sendRequest(
                form.action,
                new FormData(form)
            );

            if (!data.comment) {
                throw new Error('The server did not return the new comment.');
            }

            const comment = data.comment;

            list.querySelector('.empty-comments')?.remove();

            const item = document.createElement('div');
            item.className = 'comment-item';
            item.id = `comment-${comment.id}`;

            const avatar = createAvatar(
                'comment-avatar',
                comment.profile_photo,
                comment.user_name
            );

            const wrapper = document.createElement('div');
            wrapper.className = 'comment-content';

            const body = document.createElement('div');
            body.className = 'comment-body';

            const heading = document.createElement('div');
            heading.className = 'comment-heading';

            const name = document.createElement('strong');
            name.textContent = comment.user_name || 'User';

            const time = document.createElement('span');
            time.textContent = 'Just now';

            const text = document.createElement('p');
            text.textContent = comment.content;

            heading.append(name, time);
            body.append(heading, text);
            wrapper.appendChild(body);
            item.append(avatar, wrapper);
            list.appendChild(item);

            input.value = '';

            if (typeof data.comments_count === 'number') {
                updateCommentCounts(section, data.comments_count);
            }

        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }

    // AJAX: Delete comments without reloading the page.
    async function deleteComment(form) {
        if (!confirm('Delete this comment?')) return;

        const button = form.querySelector('button[type="submit"]');

        if (button) {
            button.disabled = true;
        }

        try {
            const data = await sendRequest(
                form.action,
                new FormData(form)
            );

            const item = form.closest('.comment-item');
            const section = form.closest('.comments-section');
            const list = section?.querySelector('.comments-list');

            item?.remove();

            if (section && typeof data.comments_count === 'number') {
                updateCommentCounts(section, data.comments_count);
            } else if (section && list) {
                const remaining = list.querySelectorAll('.comment-item').length;
                updateCommentCounts(section, remaining);
            }

            if (list) {
                showEmptyComments(list);
            }

        } catch (error) {
            if (button) button.disabled = false;
            throw error;
        }
    }

    // AJAX: Like and unlike posts without reloading the page.
    async function toggleLike(form) {
        const button = form.querySelector('.like-btn');

        if (!button || button.disabled) return;

        button.disabled = true;

        try {
            const data = await sendRequest(
                form.action,
                new FormData(form)
            );

            const label = button.querySelector('.like-label');
            const count = button.querySelector('.like-count');

            if (label) {
                label.textContent = data.liked ? '♥ Liked' : '♡ Like';
            }

            if (count && typeof data.likes_count !== 'undefined') {
                count.textContent = data.likes_count;
            }

            button.classList.toggle('liked', Boolean(data.liked));
            button.setAttribute('aria-pressed', String(Boolean(data.liked)));

        } finally {
            button.disabled = false;
        }
    }

    // AJAX: Create a post without reloading the page.
    async function createPost(form) {
        const textarea = form.querySelector('textarea[name="content"]');
        const fileInput = form.querySelector('input[name="image"]');
        const button = form.querySelector('button[type="submit"]');
        const feed = document.querySelector('#posts-feed');

        if (!textarea || !button || !feed) {
            throw new Error('Unable to find the post form or feed.');
        }

        const content = textarea.value.trim();
        const image = fileInput?.files[0];

        if (!content && !image) {
            throw new Error('Please write something or select an image.');
        }

        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Publishing...';

        try {
            const data = await sendRequest(
                form.action,
                new FormData(form)
            );

            const post = data.post;

            if (!post || !post.id) {
                throw new Error('The server did not return the new post.');
            }

            const card = document.createElement('article');
            card.className = 'panel post-card';
            card.id = `post-${post.id}`;

            // Post header and author.
            const header = document.createElement('div');
            header.className = 'post-header';

            const avatar = createAvatar(
                'post-avatar',
                post.profile_photo,
                post.user_name
            );

            const author = document.createElement('div');
            author.className = 'post-author';

            const authorName = document.createElement('strong');
            const profileLink = document.createElement('a');

            profileLink.href = post.profile_url || '#';
            profileLink.className = 'profile-name-link';
            profileLink.textContent = post.user_name || 'User';

            authorName.appendChild(profileLink);

            const time = document.createElement('span');
            time.textContent = post.created_at || 'Just now';

            author.append(authorName, time);
            header.append(avatar, author);

            // Delete form.
            if (post.delete_url) {
                const deleteForm = document.createElement('form');
                deleteForm.action = post.delete_url;
                deleteForm.method = 'POST';
                deleteForm.className = 'delete-post-form';

                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = csrfToken || '';

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                const deleteButton = document.createElement('button');
                deleteButton.type = 'submit';
                deleteButton.className = 'delete-btn';
                deleteButton.textContent = 'Delete';

                deleteForm.append(token, method, deleteButton);
                header.appendChild(deleteForm);
            }

            card.appendChild(header);

            // Post text.
            if (post.content) {
                const paragraph = document.createElement('p');
                paragraph.className = 'post-content';
                paragraph.textContent = post.content;
                card.appendChild(paragraph);
            }

            // Post image.
            if (post.image) {
                const imageWrap = document.createElement('div');
                imageWrap.className = 'post-image-wrap';

                const imageElement = document.createElement('img');
                imageElement.className = 'post-image';
                imageElement.src = post.image;
                imageElement.alt = 'Image attached to post';
                imageElement.loading = 'lazy';

                imageWrap.appendChild(imageElement);
                card.appendChild(imageWrap);
            }

            // Post statistics and like form.
            const stats = document.createElement('div');
            stats.className = 'post-stats';

            const likeForm = document.createElement('form');
            likeForm.action = post.like_url;
            likeForm.method = 'POST';
            likeForm.className = 'like-form';

            const likeToken = document.createElement('input');
            likeToken.type = 'hidden';
            likeToken.name = '_token';
            likeToken.value = csrfToken || '';

            const likeButton = document.createElement('button');
            likeButton.type = 'submit';
            likeButton.className = 'like-btn';
            likeButton.setAttribute('aria-pressed', 'false');

            const likeLabel = document.createElement('span');
            likeLabel.className = 'like-label';
            likeLabel.textContent = '♡ Like';

            const likeCount = document.createElement('span');
            likeCount.className = 'like-count';
            likeCount.textContent = post.likes_count ?? 0;

            likeButton.append(
                likeLabel,
                document.createTextNode(' · '),
                likeCount
            );

            likeForm.append(likeToken, likeButton);
            stats.appendChild(likeForm);

            const commentCount = document.createElement('a');
            commentCount.className = 'comment-count';
            commentCount.href = `#comments-${post.id}`;

            const initialCommentCount = post.comments_count ?? 0;
            commentCount.textContent =
                `💬 ${initialCommentCount} ${initialCommentCount === 1 ? 'comment' : 'comments'
                }`;

            stats.appendChild(commentCount);
            card.appendChild(stats);

            // Comments section.
            const commentsSection = document.createElement('div');
            commentsSection.className = 'comments-section';
            commentsSection.id = `comments-${post.id}`;

            const commentsHeading = document.createElement('h3');
            commentsHeading.appendChild(
                document.createTextNode('Conversation ')
            );

            const headingCount = document.createElement('span');
            headingCount.textContent = initialCommentCount;
            commentsHeading.appendChild(headingCount);

            const commentsList = document.createElement('div');
            commentsList.className = 'comments-list';

            if (initialCommentCount === 0) {
                const emptyComments = document.createElement('p');
                emptyComments.className = 'empty-comments';
                emptyComments.textContent =
                    'No comments yet. Be the first to start the conversation.';
                commentsList.appendChild(emptyComments);
            }

            const commentForm = document.createElement('form');
            commentForm.action = post.comment_url;
            commentForm.method = 'POST';
            commentForm.className = 'comment-form';

            const commentToken = document.createElement('input');
            commentToken.type = 'hidden';
            commentToken.name = '_token';
            commentToken.value = csrfToken || '';

            const commentAvatar = createAvatar(
                'comment-avatar current-avatar',
                post.profile_photo,
                post.user_name,
                'Your profile photo'
            );

            const commentInput = document.createElement('input');
            commentInput.type = 'text';
            commentInput.name = 'content';
            commentInput.placeholder = 'Write a comment...';
            commentInput.maxLength = 1000;
            commentInput.required = true;

            const commentButton = document.createElement('button');
            commentButton.type = 'submit';
            commentButton.className = 'btn-primary';
            commentButton.textContent = 'Send';

            commentForm.append(
                commentToken,
                commentAvatar,
                commentInput,
                commentButton
            );

            commentsSection.append(
                commentsHeading,
                commentsList,
                commentForm
            );

            card.appendChild(commentsSection);

            // Remove the empty-feed message, if present.
            feed.querySelector('.empty-feed')?.remove();

            // Add the new post at the top of the feed.
            const feedHeading = feed.querySelector('.feed-heading');

            if (feedHeading) {
                feedHeading.insertAdjacentElement('afterend', card);
            } else {
                feed.insertBefore(card, feed.firstChild);
            }

            // Update the feed total if that element exists.
            const total = feed.querySelector('.feed-total');

            if (total) {
                const match = total.textContent.match(/\d+/);
                const currentTotal = match ? Number(match[0]) : 0;
                total.textContent = `${currentTotal + 1} posts`;
            }

            form.reset();

        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }

    // AJAX: Mark one notification as read.
    async function markOneNotification(form) {
        const button = form.querySelector('button[type="submit"]');

        if (button) button.disabled = true;

        try {
            await sendRequest(form.action, new FormData(form));
            markNotificationReadInUI(form.closest('.notification-item'));
        } catch (error) {
            if (button) button.disabled = false;
            throw error;
        }
    }

    // AJAX: Mark all notifications as read.
    async function markAllNotifications(form) {
        const button = form.querySelector('button[type="submit"]');

        if (button) button.disabled = true;

        try {
            await sendRequest(form.action, new FormData(form));

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
            updateNavbarBadge(0);

        } catch (error) {
            if (button) button.disabled = false;
            throw error;
        }
    }

    // One delegated submit listener supports forms added dynamically.
    document.addEventListener('submit', async function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) return;

        try {
            if (form.matches('.comment-form')) {
                event.preventDefault();
                await addComment(form);
                return;
            }

            if (form.matches('.delete-comment-form')) {
                event.preventDefault();
                await deleteComment(form);
                return;
            }

            if (form.querySelector('.like-btn')) {
                event.preventDefault();
                await toggleLike(form);
                return;
            }

            if (form.matches('.post-create-form')) {
                event.preventDefault();
                await createPost(form);
                return;
            }

            if (form.matches('.mark-read-form')) {
                event.preventDefault();
                await markOneNotification(form);
                return;
            }

            if (form.matches('.mark-all-read-form')) {
                event.preventDefault();
                await markAllNotifications(form);
                return;
            }

            if (form.matches('.delete-post-form') ||
                form.querySelector('.delete-btn')) {
                if (!confirm('Delete this post?')) {
                    event.preventDefault();
                }
            }

        } catch (error) {
            alert(error.message || 'Something went wrong. Please try again.');
        }
    });

    // Clicking an unread notification marks it as read before navigating.
    document.addEventListener('click', async function (event) {
        const link = event.target.closest('.notification-link');

        if (!link || link.dataset.isRead === '1') return;

        // Allow new-tab and modified clicks to work normally.
        if (
            event.button !== 0 ||
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

        event.preventDefault();

        try {
            const body = new URLSearchParams();
            body.set('_token', csrfToken || '');
            body.set('_method', 'PATCH');

            await sendRequest(link.dataset.readUrl, body);

            markNotificationReadInUI(
                link.closest('.notification-item')
            );

        } catch (error) {
            // Still let the user open the notification destination.
            console.error(error);
        }

        window.location.href = link.href;
    });
});
