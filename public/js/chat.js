(() => {
    const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const getConversationsUrl = () => document.getElementById('newChat')?.dataset.conversationsUrl || '/conversations';

    let currentConversationId = null;
    let isCreatingConversation = false;

    async function create(message) {
        const cleanMessage = message.trim();

        if (!cleanMessage) {
            return null;
        }

        if (currentConversationId || isCreatingConversation) {
            return currentConversationId;
        }

        isCreatingConversation = true;

        try {
            const response = await fetch(getConversationsUrl(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    message: cleanMessage,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.status) {
                throw new Error(data.message || `Conversation request failed: ${response.status}`);
            }

            currentConversationId = data.conversation.conversation_id;
            console.log('Conversation created:', data.conversation);

            return currentConversationId;
        } catch (error) {
            console.error(error);
            return null;
        } finally {
            isCreatingConversation = false;
        }
    }

    function reset() {
        currentConversationId = null;
    }

    window.chatConversation = {
        create,
        reset,
        getCurrentId: () => currentConversationId,
    };
})();
