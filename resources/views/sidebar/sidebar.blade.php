<aside class="sidebar" id="sidebar">
    <div class="brand-row">
        <a href="{{ url('/') }}" class="brand" style="text-decoration:none; color:inherit;">
            <span class="brand-mark" aria-hidden="true">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3.5a4.2 4.2 0 0 1 4 2.9 4.2 4.2 0 0 1 2.7 7.2 4.2 4.2 0 0 1-6.4 4.8A4.2 4.2 0 0 1 5.9 15 4.2 4.2 0 0 1 8 7.2 4.2 4.2 0 0 1 12 3.5Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m8.6 8.4 6.8 4M8.6 15.6V8.4l6.2-3.5M15.4 15.6l-6.8-4m6.8-3.2v7.2l-6.2 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span>EduGPT</span>
        </a>

        <button class="icon-button" id="collapseSidebarBtn" aria-label="Collapse sidebar" title="Close sidebar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m15 6-6 6 6 6"/>
            </svg>
        </button>
    </div>

    <a href="{{ url('/') }}" class="new-chat" id="newChatBtn" style="text-decoration:none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        New chat
    </a>

    <nav class="history" id="historyContainer" aria-label="Chat history">
        @php
            $hasAnyChats = !empty($groupedConversations) && count($groupedConversations) > 0;
            $activeToken = $activeConversation->conversation_token ?? null;
        @endphp

        <div id="historyGroups">
            @if($hasAnyChats)
                @foreach($groupedConversations as $groupName => $convList)
                    <div class="history-group" data-group="{{ $groupName }}">
                        <p class="history-label">{{ $groupName }}</p>
                        <div class="history-list">
                            @foreach($convList as $item)
                                <div class="history-item-wrapper {{ $activeToken === $item->conversation_token ? 'active' : '' }}" id="chat-item-{{ $item->conversation_token }}">
                                    <a href="{{ url('/c/' . $item->conversation_token) }}" class="history-item-link" title="{{ $item->title }}">
                                        {{ $item->title ?: 'New conversation' }}
                                    </a>
                                    <button type="button" class="history-delete-btn" data-token="{{ $item->conversation_token }}" title="Delete chat" aria-label="Delete chat">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="history-empty" id="historyEmpty">
                    <p style="color:#777; font-size:0.82rem; padding: 10px 12px;">No chat history yet.</p>
                </div>
            @endif
        </div>
    </nav>

    <div class="profile-container" id="profileContainer">
        <!-- ChatGPT-style Popup Menu (opens upward) -->
        <div class="chatgpt-menu" id="profileMenu" role="menu" aria-hidden="true">
            <div class="menu-user-row" id="menuOpenProfileBtn" role="menuitem" tabindex="0" title="View profile">
                <span class="profile-avatar sm menu-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                </span>
                <div class="profile-text">
                    <span class="profile-name menu-user-name">{{ Auth::user()->name ?? 'User' }}</span>
                    <span class="profile-plan">Free</span>
                </div>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="menu-chevron">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </div>

            <div class="chatgpt-menu-divider"></div>

            <button type="button" class="chatgpt-menu-item" id="btnUpgradePlan" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m12 3 1.9 5.8a2 2 0 0 0 1.3 1.3L21 12l-5.8 1.9a2 2 0 0 0-1.3 1.3L12 21l-1.9-5.8a2 2 0 0 0-1.3-1.3L3 12l5.8-1.9a2 2 0 0 0 1.3-1.3L12 3Z"/>
                </svg>
                <span>Upgrade plan</span>
            </button>

            <button type="button" class="chatgpt-menu-item" id="btnPersonalization" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Personalization</span>
            </button>

            <button type="button" class="chatgpt-menu-item" id="btnProfileModal" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Profile</span>
            </button>

            <button type="button" class="chatgpt-menu-item" id="btnSettingsModal" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span>Settings</span>
            </button>

            <div class="chatgpt-menu-divider"></div>

            <button type="button" class="chatgpt-menu-item" id="btnHelpModal" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span>Help</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="menu-chevron" style="margin-left:auto;">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </button>

            <form action="{{ route('logout') }}" method="POST" id="menuLogoutForm">
                @csrf
                <button type="submit" class="chatgpt-menu-item text-danger" role="menuitem">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                    <span>Log out</span>
                </button>
            </form>
        </div>

        <!-- Trigger row (Profile button) -->
        <button type="button" class="profile" id="profileMenuTrigger" aria-haspopup="true" aria-expanded="false" title="Account settings">
            <span class="profile-avatar main-user-avatar">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
            </span>
            <div class="profile-text">
                <span class="profile-name main-user-name">{{ Auth::user()->name ?? 'User' }}</span>
                <span class="profile-plan main-user-email">{{ Auth::user()->email ?? 'Active account' }}</span>
            </div>
            <span class="profile-expand-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="5" cy="12" r="1.5" fill="currentColor"/>
                    <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                    <circle cx="19" cy="12" r="1.5" fill="currentColor"/>
                </svg>
            </span>
        </button>
    </div>
</aside>
