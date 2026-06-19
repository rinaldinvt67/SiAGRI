// Toggle New Discussion Topic Form
window.toggleForumForm = function(show) {
    const form = document.getElementById('form-new');
    const btnNew = document.getElementById('btn-new');
    const emptyState = document.getElementById('empty-state');
    const discussionsList = document.getElementById('discussions-list');

    if (form) {
        if (show) {
            form.classList.remove('hidden');
            if (btnNew) btnNew.classList.add('hidden');
            if (emptyState) emptyState.classList.add('hidden');
            if (discussionsList) discussionsList.classList.add('hidden');
        } else {
            form.classList.add('hidden');
            if (btnNew) btnNew.classList.remove('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
            if (discussionsList) discussionsList.classList.remove('hidden');
        }
    }
};

// Toggle Comments and full content of a topic
window.toggleTopicComments = function(forumId) {
    const commentsSec = document.getElementById('comments-sec-' + forumId);
    const contentText = document.getElementById('content-' + forumId);
    const btnToggle = document.getElementById('btn-toggle-' + forumId);
    
    if (commentsSec) {
        const isHidden = commentsSec.classList.contains('hidden');
        if (isHidden) {
            commentsSec.classList.remove('hidden');
            if (contentText) {
                contentText.classList.remove('line-clamp-3');
            }
            if (btnToggle) {
                btnToggle.classList.add('text-siagri-gold');
            }
        } else {
            commentsSec.classList.add('hidden');
            if (contentText) {
                contentText.classList.add('line-clamp-3');
            }
            if (btnToggle) {
                btnToggle.classList.remove('text-siagri-gold');
            }
        }
    }
};

// Toggle Reply Form inline
window.toggleReplyForm = function(commentId) {
    const replyForm = document.getElementById('reply-form-' + commentId);
    if (replyForm) {
        replyForm.classList.toggle('hidden');
    }
};
