<aside class="sidebar" id="sidebar">
    <div class="brand-row">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3.5a4.2 4.2 0 0 1 4 2.9 4.2 4.2 0 0 1 2.7 7.2 4.2 4.2 0 0 1-6.4 4.8A4.2 4.2 0 0 1 5.9 15 4.2 4.2 0 0 1 8 7.2 4.2 4.2 0 0 1 12 3.5Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m8.6 8.4 6.8 4M8.6 15.6V8.4l6.2-3.5M15.4 15.6l-6.8-4m6.8-3.2v7.2l-6.2 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span>ChatBot</span>
        </div>

        <button class="icon-button" id="closeSidebar" aria-label="Close sidebar">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m15 6-6 6 6 6"/>
            </svg>
        </button>
    </div>

    <button class="new-chat" id="newChat" type="button" data-conversations-url="{{ url('/conversations') }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        New chat
    </button>

    <nav class="history" aria-label="Chat history">
        <p class="history-label">Today</p>
        <button class="history-item active">Welcome to ChatBot</button>
        <button class="history-item">Build a portfolio website</button>
        <button class="history-item">Explain REST APIs simply</button>

        <p class="history-label" style="margin-top: 22px;">Previous 7 days</p>
        <button class="history-item">Healthy weekly meal plan</button>
        <button class="history-item">JavaScript interview prep</button>
        <button class="history-item">Travel itinerary ideas</button>
    </nav>

    <button class="profile">
        <span class="profile-avatar">JD</span>
        <span class="profile-text">
            <span class="profile-name">John Doe</span>
            <span class="profile-plan">Free plan</span>
        </span>
        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="5" cy="12" r="1.5"/>
            <circle cx="12" cy="12" r="1.5"/>
            <circle cx="19" cy="12" r="1.5"/>
        </svg>
    </button>
</aside>
