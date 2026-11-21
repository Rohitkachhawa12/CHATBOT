(() => {
    const root = document.documentElement;
    const appContainer = document.querySelector('.app');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const promptInput = document.getElementById('prompt');
    const sendButton = document.getElementById('sendButton');
    const chatForm = document.getElementById('chatForm');
    const welcome = document.getElementById('welcome');
    const messages = document.getElementById('messages');
    const chatScroll = document.getElementById('chatScroll');
    const mainChat = document.getElementById('mainChat');
    const historyGroups = document.getElementById('historyGroups');

    // Sidebar buttons
    const collapseSidebarBtn = document.getElementById('collapseSidebarBtn');
    const expandSidebarBtn = document.getElementById('expandSidebarBtn');
    const openSidebarBtn = document.getElementById('openSidebar');

    // Profile menu elements
    const profileMenu = document.getElementById('profileMenu');
    const profileMenuTrigger = document.getElementById('profileMenuTrigger');
    const profileModalOverlay = document.getElementById('profileModalOverlay');
    const settingsModalOverlay = document.getElementById('settingsModalOverlay');

    let currentConversationToken = mainChat?.dataset.activeToken || null;
    const sendUrl = mainChat?.dataset.sendUrl || '/send-message';
    const rawBaseUrl = mainChat?.dataset.baseUrl || '/';
    const baseUrl = rawBaseUrl.endsWith('/') ? rawBaseUrl : (rawBaseUrl + '/');
    let isSubmitting = false;

    const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    // SweetAlert2 helper with theme support
    function isDarkTheme() {
        return root.dataset.theme === 'dark';
    }

    function swalConfig(custom = {}) {
        const dark = isDarkTheme();
        return {
            background: dark ? '#212121' : '#ffffff',
            color: dark ? '#f3f3f3' : '#1f2023',
            confirmButtonColor: '#7257df',
            cancelButtonColor: '#4b5563',
            ...custom,
        };
    }

    const assistantIcon = `
        <span class="assistant-avatar" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 3.5a4.2 4.2 0 0 1 4 2.9 4.2 4.2 0 0 1 2.7 7.2 4.2 4.2 0 0 1-6.4 4.8A4.2 4.2 0 0 1 5.9 15 4.2 4.2 0 0 1 12 3.5Z" stroke="currentColor" stroke-width="1.7"/>
            </svg>
        </span>`;

    function renderMarkdown(rawText) {
        if (window.marked && typeof window.marked.parse === 'function') {
            try {
                return window.marked.parse(rawText);
            } catch (e) {
                console.warn('Marked parse error, using fallback:', e);
            }
        }
        const escaped = rawText
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        return escaped.replace(/\n/g, '<br>');
    }

    // Format any server-rendered messages on initial load
    document.querySelectorAll('.formatted-content').forEach((el) => {
        const raw = el.dataset.raw;
        if (raw) {
            el.innerHTML = renderMarkdown(raw);
        }
    });

    // Sidebar collapse / expand logic
    function setSidebarCollapsed(collapsed) {
        if (window.innerWidth <= 768) {
            // On mobile, hide overlay and open class
            sidebar?.classList.remove('open');
            overlay?.classList.remove('open');
        } else {
            // On desktop, collapse sidebar
            appContainer?.classList.toggle('sidebar-collapsed', collapsed);
            localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');
        }
    }

    // Restore desktop sidebar state
    if (window.innerWidth > 768 && localStorage.getItem('sidebar-collapsed') === '1') {
        appContainer?.classList.add('sidebar-collapsed');
    }

    collapseSidebarBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (window.innerWidth <= 768) {
            sidebar?.classList.remove('open');
            overlay?.classList.remove('open');
        } else {
            setSidebarCollapsed(true);
        }
    });

    expandSidebarBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        setSidebarCollapsed(false);
    });

    openSidebarBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        if (window.innerWidth <= 768) {
            sidebar?.classList.add('open');
            overlay?.classList.add('open');
        } else {
            setSidebarCollapsed(false);
        }
    });

    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('open');
    });

    // Profile Dropdown Menu (ChatGPT style)
    profileMenuTrigger?.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = profileMenu?.classList.toggle('open');
        profileMenuTrigger?.classList.toggle('active', isOpen);
    });

    // Close profile menu on outside click
    document.addEventListener('click', (e) => {
        if (!profileMenu?.contains(e.target) && !profileMenuTrigger?.contains(e.target)) {
            profileMenu?.classList.remove('open');
            profileMenuTrigger?.classList.remove('active');
        }
    });

    // Modals handling
    function openModal(modalEl) {
        if (modalEl) {
            modalEl.style.display = 'flex';
            profileMenu?.classList.remove('open');
            profileMenuTrigger?.classList.remove('active');
        }
    }

    function closeModal(modalEl) {
        if (modalEl) {
            modalEl.style.display = 'none';
        }
    }

    // Profile Modal triggers
    document.getElementById('btnProfileModal')?.addEventListener('click', () => openModal(profileModalOverlay));
    document.getElementById('menuOpenProfileBtn')?.addEventListener('click', () => openModal(profileModalOverlay));
    document.getElementById('closeProfileModalBtn')?.addEventListener('click', () => closeModal(profileModalOverlay));
    document.getElementById('cancelProfileBtn')?.addEventListener('click', () => closeModal(profileModalOverlay));

    // Settings Modal triggers
    document.getElementById('btnSettingsModal')?.addEventListener('click', () => openModal(settingsModalOverlay));
    document.getElementById('closeSettingsModalBtn')?.addEventListener('click', () => closeModal(settingsModalOverlay));
    document.getElementById('closeSettingsBtn')?.addEventListener('click', () => closeModal(settingsModalOverlay));

    // Close modals on clicking backdrop
    profileModalOverlay?.addEventListener('click', (e) => {
        if (e.target === profileModalOverlay) closeModal(profileModalOverlay);
    });
    settingsModalOverlay?.addEventListener('click', (e) => {
        if (e.target === settingsModalOverlay) closeModal(settingsModalOverlay);
    });

    // Upgrade plan SweetAlert
    document.getElementById('btnUpgradePlan')?.addEventListener('click', () => {
        profileMenu?.classList.remove('open');
        profileMenuTrigger?.classList.remove('active');
        if (window.Swal) {
            Swal.fire(swalConfig({
                icon: 'info',
                title: 'EduGPT Local Pro',
                html: '<p style="line-height:1.6;">You are running on your private local AI stack with <strong>Ollama</strong>.<br><br>⚡ Unlimited queries<br>🔒 100% Private & on-device<br>✨ Zero cloud subscription fees</p>',
                confirmButtonText: 'Great!',
            }));
        }
    });

    // Personalization SweetAlert
    document.getElementById('btnPersonalization')?.addEventListener('click', () => {
        profileMenu?.classList.remove('open');
        profileMenuTrigger?.classList.remove('active');
        if (window.Swal) {
            Swal.fire(swalConfig({
                icon: 'info',
                title: 'Personalization',
                html: '<p style="line-height:1.6;">Custom context & multi-turn memory are enabled automatically for all your conversations.</p>',
                confirmButtonText: 'Got it',
            }));
        }
    });

    // Help SweetAlert
    document.getElementById('btnHelpModal')?.addEventListener('click', () => {
        profileMenu?.classList.remove('open');
        profileMenuTrigger?.classList.remove('active');
        if (window.Swal) {
            Swal.fire(swalConfig({
                icon: 'question',
                title: 'EduGPT Help & FAQ',
                html: '<div style="text-align:left; font-size:0.9rem; line-height:1.7;">' +
                      '<p><strong>• How does it work?</strong> Your queries are processed on your local machine by Ollama.</p>' +
                      '<p><strong>• Where is history saved?</strong> All conversation logs are tied securely to your user ID.</p>' +
                      '<p><strong>• Shortcuts:</strong> Press <code>Enter</code> to send, <code>Shift + Enter</code> for new line.</p>' +
                      '</div>',
                confirmButtonText: 'Close',
            }));
        }
    });

    // Handle Profile Update Form
    const profileForm = document.getElementById('profileUpdateForm');
    profileForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const saveBtn = document.getElementById('saveProfileBtn');
        if (saveBtn) saveBtn.disabled = true;

        const formData = new FormData(profileForm);
        const dataPayload = Object.fromEntries(formData.entries());

        try {
            const res = await fetch(`${baseUrl}profile`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(dataPayload),
            });

            const resData = await res.json();

            if (!res.ok || !resData.status) {
                let errText = resData.message || 'Failed to update profile.';
                if (resData.errors) {
                    errText = Object.values(resData.errors).flat().join('<br>');
                }
                throw new Error(errText);
            }

            // Update UI elements with new name & email
            if (resData.user) {
                document.querySelectorAll('.main-user-name, .menu-user-name').forEach(el => el.textContent = resData.user.name);
                document.querySelectorAll('.main-user-email').forEach(el => el.textContent = resData.user.email);
                document.querySelectorAll('.main-user-avatar, .menu-user-avatar').forEach(el => el.textContent = resData.user.initials);
            }

            closeModal(profileModalOverlay);

            if (window.Swal) {
                Swal.fire(swalConfig({
                    icon: 'success',
                    title: 'Profile Updated',
                    text: 'Your details have been saved successfully.',
                    timer: 1800,
                    showConfirmButton: false,
                }));
            }
        } catch (err) {
            if (window.Swal) {
                Swal.fire(swalConfig({
                    icon: 'error',
                    title: 'Update Error',
                    html: err.message,
                }));
            } else {
                alert(err.message);
            }
        } finally {
            if (saveBtn) saveBtn.disabled = false;
        }
    });

    // Clear all conversations button in Settings
    document.getElementById('clearAllChatsBtn')?.addEventListener('click', async () => {
        let confirmed = false;
        if (window.Swal) {
            const result = await Swal.fire(swalConfig({
                title: 'Clear all conversations?',
                text: 'All your chat history will be permanently deleted. This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, clear all',
                cancelButtonText: 'Cancel',
            }));
            confirmed = result.isConfirmed;
        } else {
            confirmed = confirm('Are you sure you want to clear all conversations?');
        }

        if (!confirmed) return;

        try {
            const res = await fetch(`${baseUrl}clear-all-conversations`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            const data = await res.json();
            if (res.ok && data.status) {
                closeModal(settingsModalOverlay);
                if (window.Swal) {
                    await Swal.fire(swalConfig({
                        icon: 'success',
                        title: 'All Cleared',
                        text: 'Your chat history has been emptied.',
                        timer: 1400,
                        showConfirmButton: false,
                    }));
                }
                window.location.href = baseUrl;
            } else {
                throw new Error(data.message || 'Failed to clear conversations.');
            }
        } catch (e) {
            if (window.Swal) {
                Swal.fire(swalConfig({
                    icon: 'error',
                    title: 'Error',
                    text: e.message,
                }));
            } else {
                alert(e.message);
            }
        }
    });

    // Settings theme toggle button
    document.getElementById('settingsToggleThemeBtn')?.addEventListener('click', () => {
        toggleTheme();
    });

    function toggleTheme() {
        const isDark = root.dataset.theme === 'dark';
        root.dataset.theme = isDark ? 'light' : 'dark';
        localStorage.setItem('chatbot-theme', root.dataset.theme);
    }

    const themeToggleBtn = document.getElementById('themeToggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleTheme);
    }

    const savedTheme = localStorage.getItem('chatbot-theme');
    if (savedTheme) {
        root.dataset.theme = savedTheme;
    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        root.dataset.theme = 'dark';
    }

    function resizeInput() {
        if (!promptInput) return;
        promptInput.style.height = 'auto';
        promptInput.style.height = Math.min(promptInput.scrollHeight, 170) + 'px';
        if (sendButton) {
            sendButton.disabled = !promptInput.value.trim() || isSubmitting;
        }
    }

    function scrollToBottom() {
        if (!chatScroll) return;
        requestAnimationFrame(() => {
            chatScroll.scrollTop = chatScroll.scrollHeight;
        });
    }

    function addUserMessage(text) {
        const messageEl = document.createElement('div');
        messageEl.className = 'message user';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.textContent = text;

        messageEl.appendChild(bubble);
        messages.appendChild(messageEl);
        scrollToBottom();
    }

    function addAssistantLoading() {
        const messageEl = document.createElement('div');
        messageEl.className = 'message assistant';
        messageEl.innerHTML = `
            ${assistantIcon}
            <div class="assistant-content">
                <strong>EduGPT</strong>
                <span class="typing"><span></span><span></span><span></span></span>
            </div>`;
        messages.appendChild(messageEl);
        scrollToBottom();
        return messageEl;
    }

    function updateSidebarHistory(conv) {
        const historyEmpty = document.getElementById('historyEmpty');
        if (historyEmpty) {
            historyEmpty.remove();
        }

        document.querySelectorAll('.history-item-wrapper').forEach(item => {
            item.classList.remove('active');
        });

        let todayGroup = document.querySelector('.history-group[data-group="Today"]');
        if (!todayGroup) {
            todayGroup = document.createElement('div');
            todayGroup.className = 'history-group';
            todayGroup.dataset.group = 'Today';
            todayGroup.innerHTML = `
                <p class="history-label">Today</p>
                <div class="history-list"></div>
            `;
            if (historyGroups.firstChild) {
                historyGroups.insertBefore(todayGroup, historyGroups.firstChild);
            } else {
                historyGroups.appendChild(todayGroup);
            }
        }

        const list = todayGroup.querySelector('.history-list');

        const existingItem = document.getElementById('chat-item-' + conv.conversation_token);
        if (existingItem) {
            existingItem.classList.add('active');
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'history-item-wrapper active';
        wrapper.id = 'chat-item-' + conv.conversation_token;
        wrapper.innerHTML = `
            <a href="${baseUrl}c/${conv.conversation_token}" class="history-item-link" title="${conv.title}">
                ${conv.title || 'New conversation'}
            </a>
            <button type="button" class="history-delete-btn" data-token="${conv.conversation_token}" title="Delete chat" aria-label="Delete chat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/>
                </svg>
            </button>
        `;

        if (list.firstChild) {
            list.insertBefore(wrapper, list.firstChild);
        } else {
            list.appendChild(wrapper);
        }
    }

    async function sendMessage(text) {
        const cleanText = text.trim();
        if (!cleanText || isSubmitting) {
            return;
        }

        isSubmitting = true;
        promptInput.value = '';
        resizeInput();

        if (welcome) {
            welcome.style.display = 'none';
        }
        if (messages) {
            messages.classList.add('visible');
        }

        addUserMessage(cleanText);
        const assistantPlaceholder = addAssistantLoading();

        try {
            const response = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    message: cleanText,
                    conversation_token: currentConversationToken,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.status) {
                throw new Error(data.message || `Server returned error (${response.status})`);
            }

            const contentContainer = assistantPlaceholder.querySelector('.assistant-content');
            if (contentContainer) {
                const replyHtml = renderMarkdown(data.reply || 'No response.');
                contentContainer.innerHTML = `
                    <strong>EduGPT</strong>
                    <div class="formatted-content">${replyHtml}</div>
                `;
            }

            if (data.is_new_conversation && data.conversation?.conversation_token) {
                currentConversationToken = data.conversation.conversation_token;
                const newUrl = `${baseUrl}c/${currentConversationToken}`;
                try {
                    window.history.pushState({ token: currentConversationToken }, '', newUrl);
                } catch (pushErr) {
                    console.warn('Could not update history state:', pushErr);
                }
                document.title = `${data.conversation.title || 'Conversation'} - EduGPT`;
                updateSidebarHistory(data.conversation);
            }
        } catch (error) {
            console.error('Error sending message:', error);
            const contentContainer = assistantPlaceholder.querySelector('.assistant-content');
            if (contentContainer) {
                contentContainer.innerHTML = `
                    <strong>EduGPT</strong>
                    <div class="formatted-content" style="color: #ef4444;">
                        ⚠️ Could not get response: ${error.message}
                    </div>
                `;
            }
        } finally {
            isSubmitting = false;
            resizeInput();
            promptInput?.focus();
            scrollToBottom();
        }
    }

    // Delete single conversation with SweetAlert2 ("swap alert")
    document.getElementById('historyContainer')?.addEventListener('click', async (event) => {
        const deleteBtn = event.target.closest('.history-delete-btn');
        if (!deleteBtn) return;

        event.preventDefault();
        event.stopPropagation();

        const token = deleteBtn.dataset.token;
        if (!token) return;

        let confirmed = false;
        if (window.Swal) {
            const result = await Swal.fire(swalConfig({
                title: 'Delete chat?',
                text: 'Are you sure you want to delete this chat?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
            }));
            confirmed = result.isConfirmed;
        } else {
            confirmed = confirm('Are you sure you want to delete this chat?');
        }

        if (!confirmed) return;

        try {
            const res = await fetch(`${baseUrl}conversations/${token}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            const data = await res.json();
            if (res.ok && data.status) {
                const itemWrapper = document.getElementById('chat-item-' + token);
                if (itemWrapper) {
                    const group = itemWrapper.closest('.history-group');
                    itemWrapper.remove();
                    if (group && group.querySelectorAll('.history-item-wrapper').length === 0) {
                        group.remove();
                    }
                }

                if (currentConversationToken === token) {
                    window.location.href = baseUrl;
                }
            } else {
                if (window.Swal) {
                    Swal.fire(swalConfig({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete chat.',
                    }));
                } else {
                    alert(data.message || 'Failed to delete chat.');
                }
            }
        } catch (e) {
            console.error('Delete error:', e);
            if (window.Swal) {
                Swal.fire(swalConfig({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete chat.',
                }));
            } else {
                alert('Failed to delete chat.');
            }
        }
    });

    // Input handlers
    promptInput?.addEventListener('input', resizeInput);
    promptInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            chatForm?.requestSubmit();
        }
    });

    chatForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (promptInput) {
            sendMessage(promptInput.value);
        }
    });

    // Suggestion buttons
    document.querySelectorAll('.suggestion').forEach((btn) => {
        btn.addEventListener('click', () => {
            const text = btn.dataset.prompt || '';
            if (text) {
                sendMessage(text);
            }
        });
    });

    scrollToBottom();
    resizeInput();
})();
