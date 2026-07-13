@extends('layouts.app')

@section('title', 'ChatBot')

@section('content')
    <main class="main">
            <header class="topbar">
                <div style="display:flex; align-items:center;">
                    <button class="icon-button mobile-menu" id="openSidebar" aria-label="Open sidebar">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </button>
                    <button class="model-button">
                        ChatBot
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m7 10 5 5 5-5"/>
                        </svg>
                    </button>
                </div>
                <div class="top-actions">
                    <button class="icon-button" id="themeToggle" aria-label="Toggle dark mode">
                        <svg class="moon-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M20.5 14.2A8.3 8.3 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"/>
                        </svg>
                    </button>
                    <button class="icon-button" aria-label="Share conversation">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 16V3m0 0L7 8m5-5 5 5M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"/>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="chat-scroll" id="chatScroll">
                <section class="chat">
                    <div class="welcome" id="welcome">
                        <div class="welcome-badge">
                            <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
                                <path d="M12 3.5a4.2 4.2 0 0 1 4 2.9 4.2 4.2 0 0 1 2.7 7.2 4.2 4.2 0 0 1-6.4 4.8A4.2 4.2 0 0 1 5.9 15 4.2 4.2 0 0 1 8 7.2 4.2 4.2 0 0 1 12 3.5Z" stroke="currentColor" stroke-width="1.6"/>
                                <path d="m8.6 8.4 6.8 4M8.6 15.6V8.4l6.2-3.5M15.4 15.6l-6.8-4m6.8-3.2v7.2l-6.2 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h1>How can I help you today?</h1>
                        <p class="welcome-copy">Ask a question, explore an idea, or get help with your next project. Start with one of these suggestions.</p>

                        <div class="suggestions">
                            <button class="suggestion" data-prompt="Create a simple roadmap for learning web development">
                                <span class="suggestion-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z"/>
                                    </svg>
                                </span>
                                <span><strong>Make a learning plan</strong><small>Create a practical roadmap for a new skill</small></span>
                            </button>
                            <button class="suggestion" data-prompt="Help me brainstorm unique ideas for a mobile app">
                                <span class="suggestion-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M9 18h6M10 22h4M8.2 14.5a7 7 0 1 1 7.6 0c-.8.5-1.3 1.4-1.3 2.5h-5c0-1.1-.5-2-1.3-2.5Z"/>
                                    </svg>
                                </span>
                                <span><strong>Brainstorm ideas</strong><small>Turn a rough thought into useful concepts</small></span>
                            </button>
                            <button class="suggestion" data-prompt="Explain artificial intelligence in simple terms">
                                <span class="suggestion-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.6 2.6 0 1 1 4.1 2.1c-1 .7-1.6 1.1-1.6 2.4M12 17h.01"/>
                                    </svg>
                                </span>
                                <span><strong>Explain a concept</strong><small>Understand something complex in simple terms</small></span>
                            </button>
                            <button class="suggestion" data-prompt="Write a professional email asking for a project update">
                                <span class="suggestion-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M4 4h16v16H4zM8 8h8M8 12h8M8 16h5"/>
                                    </svg>
                                </span>
                                <span><strong>Draft some writing</strong><small>Emails, posts, stories, and more</small></span>
                            </button>
                        </div>
                    </div>

                    <div class="messages" id="messages" aria-live="polite"></div>
                </section>
            </div>

            <div class="composer-area">
                <form class="composer" id="chatForm">
                    <textarea id="prompt" rows="1" placeholder="Message ChatBot..." aria-label="Message ChatBot"></textarea>
                    <div class="composer-tools">
                        <div class="tool-group">
                            <button class="tool-button" type="button" aria-label="Attach file">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m20.5 11.5-8.8 8.8a6 6 0 0 1-8.5-8.5l9-9a4 4 0 0 1 5.7 5.7l-9 9a2 2 0 1 1-2.8-2.8l8.3-8.3"/>
                                </svg>
                                <span>Attach</span>
                            </button>
                            <button class="tool-button" type="button" aria-label="Search the web">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>
                                </svg>
                                <span>Search</span>
                            </button>
                        </div>
                        <button class="send-button" id="sendButton" type="submit" disabled aria-label="Send message">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M12 19V5m0 0L6.5 10.5M12 5l5.5 5.5"/>
                            </svg>
                        </button>
                    </div>
                </form>
                <p class="disclaimer">ChatBot can make mistakes. Check important information.</p>
            </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/chat.js') }}?v={{ filemtime(public_path('js/chat.js')) }}"></script>
@endpush
