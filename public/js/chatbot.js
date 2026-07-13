const root = document.documentElement;
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const promptInput = document.getElementById('prompt');
const sendButton = document.getElementById('sendButton');
const chatForm = document.getElementById('chatForm');
const welcome = document.getElementById('welcome');
const messages = document.getElementById('messages');
const chatScroll = document.getElementById('chatScroll');
const newChatButton = document.getElementById('newChat');

const icon = `
    <span class="assistant-avatar" aria-hidden="true">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path d="M12 3.5a4.2 4.2 0 0 1 4 2.9 4.2 4.2 0 0 1 2.7 7.2 4.2 4.2 0 0 1-6.4 4.8A4.2 4.2 0 0 1 5.9 15 4.2 4.2 0 0 1 8 7.2 4.2 4.2 0 0 1 12 3.5Z" stroke="currentColor" stroke-width="1.7"/>
        </svg>
    </span>`;

function setSidebar(open) {
    sidebar.classList.toggle('open', open);
    overlay.classList.toggle('open', open);
}

function resizeInput() {
    promptInput.style.height = 'auto';
    promptInput.style.height = Math.min(promptInput.scrollHeight, 170) + 'px';
    sendButton.disabled = !promptInput.value.trim();
}

function scrollToBottom() {
    requestAnimationFrame(() => {
        chatScroll.scrollTop = chatScroll.scrollHeight;
    });
}

function addUserMessage(text) {
    const message = document.createElement('div');
    message.className = 'message user';

    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.textContent = text;

    message.appendChild(bubble);
    messages.appendChild(message);
}

function addAssistantReply() {
    const message = document.createElement('div');
    message.className = 'message assistant';
    message.innerHTML = `${icon}<div class="assistant-content"><strong>ChatBot</strong><span class="typing"><span></span><span></span><span></span></span></div>`;
    messages.appendChild(message);
    scrollToBottom();

    window.setTimeout(() => {
        const content = message.querySelector('.assistant-content');
        content.innerHTML = '<strong>ChatBot</strong>This UI demo is ready. Connect the form to your chatbot API to return live AI responses. The conversation area, typing state, responsive sidebar, and input behavior are already in place.';
        scrollToBottom();
    }, 1100);
}

async function sendMessage(text) {
    const cleanText = text.trim();

    if (!cleanText) {
        return;
    }

    await window.chatConversation?.create(cleanText);

    welcome.hidden = true;
    messages.classList.add('visible');
    addUserMessage(cleanText);
    promptInput.value = '';
    resizeInput();
    addAssistantReply();
    scrollToBottom();
}

document.getElementById('openSidebar').addEventListener('click', () => setSidebar(true));
document.getElementById('closeSidebar').addEventListener('click', () => setSidebar(false));
overlay.addEventListener('click', () => setSidebar(false));

promptInput.addEventListener('input', resizeInput);
promptInput.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        chatForm.requestSubmit();
    }
});

chatForm.addEventListener('submit', (event) => {
    event.preventDefault();
    sendMessage(promptInput.value);
});

document.querySelectorAll('.suggestion').forEach((button) => {
    button.addEventListener('click', () => sendMessage(button.dataset.prompt || ''));
});

newChatButton.addEventListener('click', () => {
    window.chatConversation?.reset();
    messages.replaceChildren();
    messages.classList.remove('visible');
    welcome.hidden = false;
    setSidebar(false);
    promptInput.focus();
});

document.getElementById('themeToggle').addEventListener('click', () => {
    const dark = root.dataset.theme === 'dark';
    root.dataset.theme = dark ? 'light' : 'dark';
    localStorage.setItem('chatbot-theme', root.dataset.theme);
});

const savedTheme = localStorage.getItem('chatbot-theme');

if (savedTheme) {
    root.dataset.theme = savedTheme;
} else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    root.dataset.theme = 'dark';
}

resizeInput();
