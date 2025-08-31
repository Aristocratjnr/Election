/**
 * AI-Powered Live Chat for Voting System
 * Only handles voting-related FAQs and questions
 */
class AIVotingChat {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.isTyping = false;
        this.conversationContext = [];
        this.lastInteraction = null;
        
        // FAQ knowledge base - only voting related
        this.faqData = {
            'how to vote': {
                keywords: ['how', 'vote', 'voting', 'cast', 'ballot', 'process', 'steps'],
                response: "To vote: 1) Review all candidates and their manifestos, 2) Select one candidate per position, 3) Click 'Submit Your Vote' button, 4) Confirm your selections in the popup. Remember, you can only vote once!"
            },
            'voting requirements': {
                keywords: ['requirements', 'eligible', 'who can vote', 'qualifications', 'criteria'],
                response: "All registered students can vote in the election. You need to be logged in with your student credentials and the election must be active. Each student gets one vote per position."
            },
            'election schedule': {
                keywords: ['when', 'time', 'schedule', 'date', 'deadline', 'ends', 'starts'],
                response: "You can see the election countdown timer on this page. The election dates and remaining time are displayed in the voting portal. Make sure to vote before the deadline!"
            },
            'candidate information': {
                keywords: ['candidates', 'manifesto', 'platform', 'policies', 'who is running'],
                response: "You can view all candidates on this page. Click 'View Manifesto' on any candidate card to read their policies and plans. Take time to review all candidates before voting."
            },
            'vote security': {
                keywords: ['secure', 'anonymous', 'privacy', 'safe', 'confidential', 'secret'],
                response: "Your vote is completely secure and anonymous. We use blockchain technology to ensure vote integrity. No one can see how you voted - your privacy is protected."
            },
            'change vote': {
                keywords: ['change', 'modify', 'edit', 'undo', 'cancel', 'revote'],
                response: "Once you submit your vote, it cannot be changed or undone. Please review all your selections carefully before clicking the final submit button."
            },
            'voting problems': {
                keywords: ['problem', 'error', 'issue', 'not working', 'bug', 'glitch', 'help'],
                response: "If you're experiencing voting issues: 1) Refresh the page, 2) Clear your browser cache, 3) Try a different browser, 4) Contact the election administrator if problems persist."
            },
            'results': {
                keywords: ['results', 'winner', 'outcome', 'count', 'tally', 'who won'],
                response: "Election results will be available after the voting period ends. You can view live preliminary results during voting, but final results will be announced officially after the election closes."
            },
            'positions': {
                keywords: ['positions', 'roles', 'offices', 'what am i voting for'],
                response: "You're voting for student leadership positions. Each position is listed separately with its candidates. You must select one candidate for each available position."
            },
            'technical support': {
                keywords: ['technical', 'support', 'browser', 'device', 'mobile', 'computer'],
                response: "For the best voting experience: Use a modern browser (Chrome, Firefox, Safari, Edge), ensure stable internet connection, enable JavaScript. The system works on both desktop and mobile devices."
            }
        };
        
        // Enhanced quick suggestions based on common user intents
        this.quickSuggestions = [
            "How do I vote?",
            "When does voting end?",
            "Is my vote secure?",
            "Can I change my vote?",
            "Who are the candidates?",
            "What if I have problems?"
        ];
        
        this.init();
    }
    
    init() {
        this.createChatUI();
        this.bindEvents();
        this.addWelcomeMessage();
        this.checkForImportantNotifications();
    }
    
    checkForImportantNotifications() {
        // Check if election is ending soon and show notification
        const countdownTimer = document.getElementById('election-countdown');
        if (countdownTimer) {
            const hoursEl = document.getElementById('hours');
            const daysEl = document.getElementById('days');
            
            if (hoursEl && daysEl) {
                const hours = parseInt(hoursEl.textContent);
                const days = parseInt(daysEl.textContent);
                
                // Show notification if less than 2 hours or less than 1 day remaining
                if ((days === 0 && hours < 2) || (days === 0 && hours < 24)) {
                    setTimeout(() => {
                        this.showUrgentNotification();
                    }, 3000); // Show after 3 seconds
                }
            }
        }
        
        // Check if user hasn't voted yet and election is active
        const votingForm = document.getElementById('votingForm');
        const votedBadge = document.querySelector('.voted-badge');
        
        if (votingForm && !votedBadge) {
            // Set a subtle reminder after 30 seconds
            setTimeout(() => {
                this.showVotingReminder();
            }, 30000);
        }
    }
    
    showUrgentNotification() {
        const chatButton = document.getElementById('aiChatButton');
        if (chatButton && !this.isOpen) {
            chatButton.classList.add('pulse', 'has-notification');
            
            // Auto-remove after 10 seconds if not clicked
            setTimeout(() => {
                chatButton.classList.remove('pulse', 'has-notification');
            }, 10000);
        }
    }
    
    showVotingReminder() {
        const chatButton = document.getElementById('aiChatButton');
        const votedBadge = document.querySelector('.voted-badge');
        
        // Only show if user still hasn't voted
        if (chatButton && !votedBadge && !this.isOpen) {
            chatButton.classList.add('has-notification');
            
            // Remove after 15 seconds
            setTimeout(() => {
                chatButton.classList.remove('has-notification');
            }, 15000);
        }
    }
    
    createChatUI() {
        const chatContainer = document.createElement('div');
        chatContainer.className = 'ai-chat-container';
        chatContainer.innerHTML = `
            <div class="ai-chat-window" id="aiChatWindow">
                <div class="ai-chat-header">
                    <div>
                        <h4 class="chat-title">Voting Assistant</h4>
                        <p class="chat-subtitle">
                            <span class="ai-status">
                                <span class="status-dot"></span>
                                Online - Voting Help Only
                            </span>
                        </p>
                    </div>
                    <button class="close-btn" id="closeChatBtn">
                        <img src="assets/img/close-icon.svg" alt="Close" class="close-icon">
                    </button>
                </div>
                <div class="ai-chat-body">
                    <div class="ai-chat-messages" id="chatMessages">
                        <!-- Messages will be added here -->
                    </div>
                    <div class="quick-suggestions" id="quickSuggestions">
                        <div class="quick-suggestions-title">Quick Questions:</div>
                        <div class="suggestion-chips">
                            ${this.quickSuggestions.map(suggestion => 
                                `<div class="suggestion-chip" data-suggestion="${suggestion}">${suggestion}</div>`
                            ).join('')}
                        </div>
                    </div>
                </div>
                <div class="ai-chat-input">
                    <div class="chat-input-group">
                        <textarea 
                            class="chat-input" 
                            id="chatInput" 
                            placeholder="Ask about voting process..." 
                            rows="1"
                            maxlength="500"
                        ></textarea>
                        <button class="chat-send-btn" id="sendMessageBtn">
                            <img src="assets/img/send-icon.svg" alt="Send" class="send-icon">
                        </button>
                    </div>
                </div>
            </div>
            <button class="ai-chat-button" id="aiChatButton">
                <img src="assets/img/chat-icon.svg" alt="Chat" class="chat-button-icon">
            </button>
        `;
        
        document.body.appendChild(chatContainer);
    }
    
    bindEvents() {
        const chatButton = document.getElementById('aiChatButton');
        const chatWindow = document.getElementById('aiChatWindow');
        const closeBtn = document.getElementById('closeChatBtn');
        const sendBtn = document.getElementById('sendMessageBtn');
        const chatInput = document.getElementById('chatInput');
        const suggestionChips = document.querySelectorAll('.suggestion-chip');
        
        // Toggle chat window
        chatButton.addEventListener('click', () => this.toggleChat());
        closeBtn.addEventListener('click', () => this.closeChat());
        
        // Send message
        sendBtn.addEventListener('click', () => this.sendMessage());
        
        // Enter key to send (Shift+Enter for new line)
        chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // Auto-resize textarea
        chatInput.addEventListener('input', () => {
            chatInput.style.height = 'auto';
            chatInput.style.height = Math.min(chatInput.scrollHeight, 100) + 'px';
        });
        
        // Quick suggestions
        suggestionChips.forEach(chip => {
            chip.addEventListener('click', (e) => {
                const suggestion = e.target.getAttribute('data-suggestion');
                chatInput.value = suggestion;
                this.sendMessage();
            });
        });
        
        // Close chat when clicking outside
        document.addEventListener('click', (e) => {
            if (this.isOpen && !chatWindow.contains(e.target) && !chatButton.contains(e.target)) {
                this.closeChat();
            }
        });
    }
    
    toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }
    
    openChat() {
        const chatWindow = document.getElementById('aiChatWindow');
        const chatButton = document.getElementById('aiChatButton');
        
        chatWindow.classList.add('show');
        chatButton.classList.add('chat-open');
        chatButton.classList.remove('pulse', 'has-notification');
        this.isOpen = true;
        
        // Check if this was opened due to urgent timing and show helpful message
        this.checkAndShowTimingHelp();
        
        // Focus input
        setTimeout(() => {
            document.getElementById('chatInput').focus();
        }, 300);
    }
    
    checkAndShowTimingHelp() {
        const countdownTimer = document.getElementById('election-countdown');
        const votedBadge = document.querySelector('.voted-badge');
        
        if (countdownTimer && !votedBadge) {
            const hoursEl = document.getElementById('hours');
            const daysEl = document.getElementById('days');
            
            if (hoursEl && daysEl) {
                const hours = parseInt(hoursEl.textContent);
                const days = parseInt(daysEl.textContent);
                
                if (days === 0 && hours < 2) {
                    setTimeout(() => {
                        this.addMessage('ai', "⚠️ URGENT: The election ends in less than 2 hours! If you haven't voted yet, please cast your vote soon. Do you need help with the voting process?");
                    }, 1000);
                } else if (days === 0 && hours < 6) {
                    setTimeout(() => {
                        this.addMessage('ai', "🕐 The election ends in just a few hours. Have you cast your vote yet? I can help guide you through the process if needed!");
                    }, 1000);
                } else if (!votedBadge) {
                    setTimeout(() => {
                        this.addMessage('ai', "👋 Hi! I noticed you haven't voted yet. Would you like me to walk you through the voting process?");
                    }, 1000);
                }
            }
        }
    }
    
    closeChat() {
        const chatWindow = document.getElementById('aiChatWindow');
        const chatButton = document.getElementById('aiChatButton');
        
        chatWindow.classList.remove('show');
        chatButton.classList.remove('chat-open');
        this.isOpen = false;
    }
    
    addWelcomeMessage() {
        const welcomeHtml = `
            <div class="welcome-message">
                <div class="welcome-icon">🗳️</div>
                <h4>Welcome to Voting Assistant!</h4>
                <p>I'm here to help you with voting questions and the election process. Ask me anything about how to vote, candidate information, or election details!</p>
            </div>
        `;
        
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.innerHTML = welcomeHtml;
    }
    
    async sendMessage() {
        const chatInput = document.getElementById('chatInput');
        const message = chatInput.value.trim();
        
        if (!message) return;
        
        // Clear input
        chatInput.value = '';
        chatInput.style.height = 'auto';
        
        // Add user message
        this.addMessage('user', message);
        
        // Show typing indicator
        this.showTyping();
        
        try {
            // Try to get response from API first (if available)
            const response = await this.getAIResponse(message);
            this.hideTyping();
            this.addMessage('ai', response);
        } catch (error) {
            // Fallback to local knowledge base
            console.log('API unavailable, using local knowledge base');
            setTimeout(() => {
                this.hideTyping();
                const response = this.generateResponse(message);
                this.addMessage('ai', response);
            }, 1000 + Math.random() * 1000);
        }
    }
    
    async getAIResponse(message) {
        try {
            const response = await fetch('api/ai_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message })
            });
            
            if (!response.ok) {
                throw new Error('API request failed');
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Add suggestions if provided
            if (data.suggestions && data.suggestions.length > 0) {
                setTimeout(() => {
                    this.updateQuickSuggestions(data.suggestions);
                }, 1000);
            }
            
            return data.response;
        } catch (error) {
            console.error('AI API Error:', error);
            throw error; // Re-throw to trigger fallback
        }
    }
    
    updateQuickSuggestions(suggestions) {
        const suggestionsContainer = document.querySelector('.suggestion-chips');
        if (suggestionsContainer && suggestions.length > 0) {
            suggestionsContainer.innerHTML = suggestions.map(suggestion => 
                `<div class="suggestion-chip" data-suggestion="${suggestion}">${suggestion}</div>`
            ).join('');
            
            // Re-bind events for new suggestions
            suggestionsContainer.querySelectorAll('.suggestion-chip').forEach(chip => {
                chip.addEventListener('click', (e) => {
                    const suggestion = e.target.getAttribute('data-suggestion');
                    const chatInput = document.getElementById('chatInput');
                    chatInput.value = suggestion;
                    this.sendMessage();
                });
            });
        }
    }
    
    addMessage(type, content) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = 'message-bubble';
        bubbleDiv.textContent = content;
        
        messageDiv.appendChild(bubbleDiv);
        messagesContainer.appendChild(messageDiv);
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Store message
        this.messages.push({ type, content, timestamp: Date.now() });
    }
    
    showTyping() {
        if (this.isTyping) return;
        
        this.isTyping = true;
        const messagesContainer = document.getElementById('chatMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message ai typing-message';
        typingDiv.innerHTML = `
            <div class="typing-indicator">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
                <span class="typing-text">Assistant is thinking...</span>
            </div>
        `;
        
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    hideTyping() {
        const typingMessage = document.querySelector('.typing-message');
        if (typingMessage) {
            typingMessage.remove();
        }
        this.isTyping = false;
    }
    
    generateResponse(userMessage) {
        const message = userMessage.toLowerCase();
        
        // Check if the message is voting-related
        if (!this.isVotingRelated(message)) {
            return "Sorry, I can only help with voting-related questions. Please ask me about the voting process, candidates, election schedule, or any other election-related topics.";
        }
        
        // Add message to conversation context
        this.conversationContext.push({
            user: userMessage,
            timestamp: Date.now()
        });
        
        // Keep only last 5 interactions for context
        if (this.conversationContext.length > 5) {
            this.conversationContext = this.conversationContext.slice(-5);
        }
        
        // Handle follow-up questions based on context
        const contextualResponse = this.handleContextualQueries(message);
        if (contextualResponse) {
            return contextualResponse;
        }
        
        // Find the best matching FAQ
        let bestMatch = null;
        let bestScore = 0;
        
        for (const [key, faq] of Object.entries(this.faqData)) {
            const score = this.calculateMatchScore(message, faq.keywords);
            if (score > bestScore && score > 0) {
                bestScore = score;
                bestMatch = faq;
            }
        }
        
        if (bestMatch) {
            const response = bestMatch.response;
            
            // Add contextual information based on current page state
            return this.enhanceWithPageContext(response, message);
        }
        
        // Default voting-related response if no specific match
        return "I understand you have a question about voting. Here are some things I can help with:\n\n" +
               "• How to cast your vote\n" +
               "• Information about candidates\n" +
               "• Election schedule and deadlines\n" +
               "• Vote security and privacy\n" +
               "• Technical voting issues\n\n" +
               "Please ask a more specific question about any of these topics!";
    }
    
    handleContextualQueries(message) {
        const lastContext = this.conversationContext[this.conversationContext.length - 2];
        
        if (!lastContext) return null;
        
        // Handle follow-up questions
        if (message.includes('more') || message.includes('detail') || message.includes('explain')) {
            if (lastContext.user.toLowerCase().includes('secure') || lastContext.user.toLowerCase().includes('privacy')) {
                return "Our voting system uses advanced security measures: 1) Blockchain technology ensures vote integrity and prevents tampering, 2) Your identity is completely separated from your vote choices, 3) All votes are encrypted during transmission, 4) The system is regularly audited for security vulnerabilities. You can vote with complete confidence!";
            }
            if (lastContext.user.toLowerCase().includes('candidate')) {
                return "To learn more about candidates: 1) Click on any candidate card to see their basic information, 2) Click 'View Manifesto' to read their detailed policies and plans, 3) Check their department and background, 4) Compare their platforms before making your decision. Take your time to review all candidates!";
            }
        }
        
        // Handle clarification requests
        if (message.includes('what') && message.includes('mean')) {
            return "Let me clarify that for you. Could you be more specific about which part you'd like me to explain further? I can provide more details about any aspect of the voting process.";
        }
        
        return null;
    }
    
    enhanceWithPageContext(response, userMessage) {
        // Check if election is active and enhance response accordingly
        const countdownTimer = document.getElementById('election-countdown');
        const votingForm = document.getElementById('votingForm');
        
        if (userMessage.includes('when') || userMessage.includes('time') || userMessage.includes('deadline')) {
            if (countdownTimer) {
                const daysEl = document.getElementById('days');
                const hoursEl = document.getElementById('hours');
                if (daysEl && hoursEl) {
                    const days = daysEl.textContent;
                    const hours = hoursEl.textContent;
                    return response + `\n\nBased on the current countdown, you have ${days} days and ${hours} hours remaining to vote. Don't wait until the last minute!`;
                }
            }
        }
        
        if (userMessage.includes('candidate') && userMessage.includes('how many')) {
            const candidateCards = document.querySelectorAll('.candidate-card');
            if (candidateCards.length > 0) {
                return response + `\n\nThere are currently ${candidateCards.length} candidates running in this election across all positions.`;
            }
        }
        
        if (userMessage.includes('vote') && userMessage.includes('already')) {
            const votedBadge = document.querySelector('.voted-badge');
            if (votedBadge) {
                return "I can see you've already voted in this election! Thank you for participating. Your vote has been securely recorded and cannot be changed. You can view the live results to see how the election is progressing.";
            }
        }
        
        return response;
    }
    
    isVotingRelated(message) {
        const votingKeywords = [
            'vote', 'voting', 'election', 'candidate', 'ballot', 'cast', 'poll', 'polls',
            'manifesto', 'platform', 'campaign', 'result', 'results', 'winner', 'tally',
            'count', 'deadline', 'schedule', 'when', 'time', 'date', 'end', 'start',
            'secure', 'anonymous', 'privacy', 'safe', 'confidential', 'blockchain',
            'position', 'office', 'role', 'leadership', 'student government',
            'submit', 'confirm', 'change', 'modify', 'undo', 'cancel',
            'problem', 'issue', 'error', 'help', 'support', 'technical',
            'browser', 'device', 'mobile', 'computer', 'internet'
        ];
        
        return votingKeywords.some(keyword => message.includes(keyword));
    }
    
    calculateMatchScore(message, keywords) {
        let score = 0;
        keywords.forEach(keyword => {
            if (message.includes(keyword.toLowerCase())) {
                score += 1;
            }
        });
        return score / keywords.length; // Normalize score
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Small delay to ensure all other scripts are loaded
    setTimeout(() => {
        window.aiVotingChat = new AIVotingChat();
    }, 500);
});

// Export for potential external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AIVotingChat;
}
