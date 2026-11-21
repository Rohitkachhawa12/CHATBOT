@extends('layouts.app')

@section('title', isset($activeConversation) ? $activeConversation->title . ' - EduGPT' : 'EduGPT')

@section('content')
    <main class="main" 
          id="mainChat"
          data-active-token="{{ $activeConversation->conversation_token ?? '' }}"
          data-send-url="{{ url('/send-message') }}"
          data-base-url="{{ rtrim(url('/'), '/') }}/">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:8px;">
                <button class="icon-button mobile-menu" id="openSidebar" aria-label="Open sidebar" title="Open menu">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
                <button class="icon-button expand-sidebar-btn" id="expandSidebarBtn" aria-label="Open sidebar" title="Expand sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect width="18" height="18" x="3" y="3" rx="2" stroke-width="2"/>
                        <path d="M9 3v18" stroke-width="2"/>
                        <path d="m14 9 3 3-3 3" stroke-width="1.8"/>
                    </svg>
                </button>
                <div class="model-badge-container">
                    <button class="model-button" type="button">
                        <span class="brand-sparkle">✨</span>
                        <span>EduGPT</span>
                        <span class="model-tag">{{ $currentModel ?? 'Ollama' }}</span>
                    </button>
                </div>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="themeToggle" aria-label="Toggle dark mode" title="Toggle theme">
                    <svg class="moon-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M20.5 14.2A8.3 8.3 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"/>
                    </svg>
                </button>
                <a href="{{ url('/') }}" class="icon-button" aria-label="New chat" title="Start new chat">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </a>
            </div>
        </header>

        <div class="chat-scroll" id="chatScroll">
            <section class="chat">
                <div class="welcome" id="welcome" @if(isset($activeConversation) || (isset($messages) && $messages->count() > 0)) style="display:none;" @endif>
                    <div class="welcome-badge">
                        <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3.5a4.2 4.2 0 0 1 4 2.9 4.2 4.2 0 0 1 2.7 7.2 4.2 4.2 0 0 1-6.4 4.8A4.2 4.2 0 0 1 5.9 15 4.2 4.2 0 0 1 8 7.2 4.2 4.2 0 0 1 12 3.5Z" stroke="currentColor" stroke-width="1.6"/>
                            <path d="m8.6 8.4 6.8 4M8.6 15.6V8.4l6.2-3.5M15.4 15.6l-6.8-4m6.8-3.2v7.2l-6.2 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h1>How can I help you today?</h1>
                    <p class="welcome-copy">Ask a question, explore an idea, or get help with your work. Powered by Ollama AI with intelligent chat memory.</p>

                    <div class="suggestions">
                        <button class="suggestion" type="button" data-prompt="Create a simple roadmap for learning web development">
                            <span class="suggestion-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z"/>
                                </svg>
                            </span>
                            <span><strong>Make a learning plan</strong><small>Create a practical roadmap for a new skill</small></span>
                        </button>
                        <button class="suggestion" type="button" data-prompt="Help me brainstorm unique ideas for a mobile app">
                            <span class="suggestion-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M9 18h6M10 22h4M8.2 14.5a7 7 0 1 1 7.6 0c-.8.5-1.3 1.4-1.3 2.5h-5c0-1.1-.5-2-1.3-2.5Z"/>
                                </svg>
                            </span>
                            <span><strong>Brainstorm ideas</strong><small>Turn a rough thought into useful concepts</small></span>
                        </button>
                        <button class="suggestion" type="button" data-prompt="Explain artificial intelligence in simple terms">
                            <span class="suggestion-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.6 2.6 0 1 1 4.1 2.1c-1 .7-1.6 1.1-1.6 2.4M12 17h.01"/>
                                </svg>
                            </span>
                            <span><strong>Explain a concept</strong><small>Understand something complex in simple terms</small></span>
                        </button>
                        <button class="suggestion" type="button" data-prompt="Write a professional email asking for a project update">
                            <span class="suggestion-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h5"/>
                                </svg>
                            </span>
                            <span><strong>Draft some writing</strong><small>Emails, posts, stories, and more</small></span>
                        </button>
                    </div>
                </div>

                <div class="messages @if(isset($messages) && $messages->count() > 0) visible @endif" id="messages" aria-live="polite">
                    @if(isset($messages))
                        @foreach($messages as $msg)
                            @if($msg->role === 'user')
                                <div class="message user">
                                    <div class="bubble">{{ $msg->content }}</div>
                                </div>
                            @elseif($msg->role === 'assistant')
                                <div class="message assistant">
                                    <span class="assistant-avatar" aria-hidden="true">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 3.5a4.2 4.2 0 0 1 4 2.9 4.2 4.2 0 0 1 2.7 7.2 4.2 4.2 0 0 1-6.4 4.8A4.2 4.2 0 0 1 5.9 15 4.2 4.2 0 0 1 8 7.2 4.2 4.2 0 0 1 12 3.5Z" stroke="currentColor" stroke-width="1.7"/>
                                        </svg>
                                    </span>
                                    <div class="assistant-content">
                                        <strong>EduGPT</strong>
                                        <div class="formatted-content" data-raw="{{ $msg->content }}">{{ $msg->content }}</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </section>
        </div>

        <div class="composer-area">
            <form class="composer" id="chatForm">
                <textarea id="prompt" rows="1" placeholder="Message EduGPT..." aria-label="Message EduGPT"></textarea>
                <div class="composer-tools">
                    <div class="tool-group">
                        <span class="status-indicator-badge">
                            <span class="status-dot"></span>
                            Ollama Connected
                        </span>
                    </div>
                    <button class="send-button" id="sendButton" type="submit" disabled aria-label="Send message">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M12 19V5m0 0L6.5 10.5M12 5l5.5 5.5"/>
                        </svg>
                    </button>
                </div>
            </form>
            <p class="disclaimer">EduGPT connects directly to local Ollama. Verify important information.</p>
        </div>
    </main>

    <!-- Profile Edit Modal -->
    <div class="custom-modal-overlay" id="profileModalOverlay" style="display:none;">
        <div class="custom-modal" role="dialog" aria-labelledby="profileModalTitle" aria-modal="true">
            <div class="modal-header">
                <div class="modal-title-row">
                    <div class="modal-icon-badge">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h3 id="profileModalTitle">Profile Details</h3>
                </div>
                <button type="button" class="modal-close-btn" id="closeProfileModalBtn" aria-label="Close modal">&times;</button>
            </div>
            <form id="profileUpdateForm">
                @csrf
                <div class="modal-body">
                    <div class="modal-field">
                        <label for="profileNameInput">Full Name</label>
                        <input type="text" id="profileNameInput" name="name" value="{{ Auth::user()->name ?? '' }}" required placeholder="Your name">
                    </div>
                    <div class="modal-field">
                        <label for="profileEmailInput">Email Address</label>
                        <input type="email" id="profileEmailInput" name="email" value="{{ Auth::user()->email ?? '' }}" required placeholder="name@example.com">
                    </div>
                    <div class="modal-divider"></div>
                    <p class="modal-section-title">Change Password <small style="color:var(--muted); font-weight:normal;">(Leave blank to keep unchanged)</small></p>
                    <div class="modal-field">
                        <label for="profilePasswordInput">New Password</label>
                        <input type="password" id="profilePasswordInput" name="password" placeholder="Minimum 6 characters">
                    </div>
                    <div class="modal-field">
                        <label for="profilePasswordConfirmInput">Confirm New Password</label>
                        <input type="password" id="profilePasswordConfirmInput" name="password_confirmation" placeholder="Re-type new password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn-secondary" id="cancelProfileBtn">Cancel</button>
                    <button type="submit" class="modal-btn-primary" id="saveProfileBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="custom-modal-overlay" id="settingsModalOverlay" style="display:none;">
        <div class="custom-modal" role="dialog" aria-labelledby="settingsModalTitle" aria-modal="true">
            <div class="modal-header">
                <div class="modal-title-row">
                    <div class="modal-icon-badge">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                    <h3 id="settingsModalTitle">Settings</h3>
                </div>
                <button type="button" class="modal-close-btn" id="closeSettingsModalBtn" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="settings-row">
                    <div>
                        <strong>Appearance</strong>
                        <p class="settings-desc">Choose between dark and light theme</p>
                    </div>
                    <button type="button" class="settings-theme-btn" id="settingsToggleThemeBtn">
                        Toggle Theme
                    </button>
                </div>

                <div class="settings-row">
                    <div>
                        <strong>AI Engine</strong>
                        <p class="settings-desc">Local Ollama inference</p>
                    </div>
                    <span class="settings-badge">{{ $currentModel ?? 'qwen2.5:0.5b' }}</span>
                </div>

                <div class="modal-divider"></div>

                <div class="settings-row">
                    <div>
                        <strong style="color:#ef4444;">Clear all conversations</strong>
                        <p class="settings-desc">Permanently remove all your chat history</p>
                    </div>
                    <button type="button" class="btn-danger-outline" id="clearAllChatsBtn">
                        Clear all
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn-secondary" id="closeSettingsBtn">Close</button>
            </div>
        </div>
    </div>
@endsection
