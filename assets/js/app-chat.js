/**
 * App Chat
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  // DOM Elements
  const elements = {
    chatContactsBody: document.querySelector('.app-chat-contacts .sidebar-body'),
    chatHistoryBody: document.querySelector('.chat-history-body'),
    chatSidebarLeftBody: document.querySelector('.app-chat-sidebar-left .sidebar-body'),
    chatSidebarRightBody: document.querySelector('.app-chat-sidebar-right .sidebar-body'),
    chatUserStatus: [...document.querySelectorAll(".form-check-input[name='chat-user-status']")],
    chatSidebarLeftUserAbout: document.getElementById('chat-sidebar-left-user-about'),
    formSendMessage: document.querySelector('.form-send-message'),
    messageInput: document.querySelector('.message-input'),
    searchInput: document.querySelector('.chat-search-input'),
    chatContactListItems: [...document.querySelectorAll('.chat-contact-list-item:not(.chat-contact-list-item-title)')],
    textareaInfo: document.getElementById('textarea-maxlength-info'),
    conversationButton: document.getElementById('app-chat-conversation-btn'),
    chatHistoryHeader: document.querySelector(".chat-history-header [data-target='#app-chat-contacts']"),
    speechToText: $('.speech-to-text'),
    appChatConversation: document.getElementById('app-chat-conversation'),
    appChatHistory: document.getElementById('app-chat-history')
  };

  const userStatusClasses = {
    active: 'avatar-online',
    offline: 'avatar-offline',
    away: 'avatar-away',
    busy: 'avatar-busy'
  };

  /**
   * Initialize PerfectScrollbar on provided elements.
   * @param {HTMLElement[]} elements - List of elements to initialize.
   */
  const initPerfectScrollbar = elements => {
    elements.forEach(el => {
      if (el) {
        new PerfectScrollbar(el, {
          wheelPropagation: false,
          suppressScrollX: true
        });
      }
    });
  };

  /**
   * Scroll chat history to the bottom.
   */
  const scrollToBottom = () => elements.chatHistoryBody?.scrollTo(0, elements.chatHistoryBody.scrollHeight);

  /**
   * Update user status avatar classes.
   * @param {string} status - Status key from userStatusClasses.
   */
  const updateUserStatus = status => {
    const leftSidebarAvatar = document.querySelector('.chat-sidebar-left-user .avatar');
    const contactsAvatar = document.querySelector('.app-chat-contacts .avatar');

    [leftSidebarAvatar, contactsAvatar].forEach(avatar => {
      if (avatar) avatar.className = avatar.className.replace(/avatar-\w+/, userStatusClasses[status]);
    });
  };

  // Handle textarea max length count.
  function handleMaxLengthCount(inputElement, infoElement, maxLength) {
    const currentLength = inputElement.value.length;
    const remaining = maxLength - currentLength;

    infoElement.className = 'maxLength label-success';

    if (remaining >= 0) {
      infoElement.textContent = `${currentLength}/${maxLength}`;
    }
    if (remaining <= 0) {
      infoElement.textContent = `${currentLength}/${maxLength}`;
      infoElement.classList.remove('label-success');
      infoElement.classList.add('label-danger');
    }
  }

  /**
   * Switch to chat conversation view.
   */
  const switchToChatConversation = () => {
    elements.appChatConversation.classList.replace('d-flex', 'd-none');
    elements.appChatHistory.classList.replace('d-none', 'd-block');
  };

  /**
   * Filter chat contacts by search input.
   * @param {string} selector - CSS selector for chat/contact list items.
   * @param {string} searchValue - Search input value.
   * @param {string} placeholderSelector - Selector for placeholder element.
   */
  const filterChatContacts = (selector, searchValue, placeholderSelector) => {
    const items = document.querySelectorAll(`${selector}:not(.chat-contact-list-item-title)`);
    let visibleCount = 0;

    items.forEach(item => {
      const isVisible = item.textContent.toLowerCase().includes(searchValue);
      item.classList.toggle('d-flex', isVisible);
      item.classList.toggle('d-none', !isVisible);
      if (isVisible) visibleCount++;
    });

    document.querySelector(placeholderSelector)?.classList.toggle('d-none', visibleCount > 0);
  };

  /**
   * Initialize speech-to-text functionality.
   */
  const initSpeechToText = () => {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition || elements.speechToText.length === 0) return;

    const recognition = new SpeechRecognition();
    let listening = false;

    elements.speechToText.on('click', function () {
      if (!listening) recognition.start();
      recognition.onspeechstart = () => (listening = true);
      recognition.onresult = event => {
        $(this).closest('.form-send-message').find('.message-input').val(event.results[0][0].transcript);
      };
      recognition.onspeechend = () => (listening = false);
      recognition.onerror = () => (listening = false);
    });
  };

  // Initialize PerfectScrollbar
  initPerfectScrollbar([
    elements.chatContactsBody,
    elements.chatHistoryBody,
    elements.chatSidebarLeftBody,
    elements.chatSidebarRightBody
  ]);

  // Scroll to the bottom of the chat history
  scrollToBottom();

  // Attach user status change event
  elements.chatUserStatus.forEach(statusInput => {
    statusInput.addEventListener('click', () => updateUserStatus(statusInput.value));
  });

  // Handle max length for textarea
  const maxLength = parseInt(elements.chatSidebarLeftUserAbout.getAttribute('maxlength'), 10);
  handleMaxLengthCount(elements.chatSidebarLeftUserAbout, elements.textareaInfo, maxLength);

  elements.chatSidebarLeftUserAbout.addEventListener('input', () => {
    handleMaxLengthCount(elements.chatSidebarLeftUserAbout, elements.textareaInfo, maxLength);
  });

  // Attach chat conversation switch event
  elements.conversationButton?.addEventListener('click', switchToChatConversation);

  // Attach chat contact selection event
  elements.chatContactListItems.forEach(item => {
    item.addEventListener('click', () => {
      elements.chatContactListItems.forEach(contact => contact.classList.remove('active'));
      item.classList.add('active');
      switchToChatConversation();
    });
  });

  // Attach chat search filter event
  elements.searchInput?.addEventListener(
    'keyup',
    debounce(e => {
      const searchValue = e.target.value.toLowerCase();
      filterChatContacts('#chat-list li', searchValue, '.chat-list-item-0');
      filterChatContacts('#contact-list li', searchValue, '.contact-list-item-0');
    }, 300)
  );

  // Attach message send event
  elements.formSendMessage?.addEventListener('submit', e => {
    e.preventDefault();
    const message = elements.messageInput.value.trim();
    if (message) {
      const messageDiv = document.createElement('div');
      messageDiv.className = 'chat-message-text mt-2';
      messageDiv.innerHTML = `<p class="mb-0 text-break">${message}</p>`;
      document.querySelector('li:last-child .chat-message-wrapper')?.appendChild(messageDiv);
      elements.messageInput.value = '';
      scrollToBottom();
    }
  });

  // ! Fix overlay issue for chat sidebar
  elements.chatHistoryHeader?.addEventListener('click', () => {
    document.querySelector('.app-chat-sidebar-left .close-sidebar')?.removeAttribute('data-overlay');
  });

  // Initialize speech-to-text
  initSpeechToText();
});

/**
 * Debounce utility function.
 * @param {Function} func - Function to debounce.
 * @param {number} wait - Delay in milliseconds.
 */
function debounce(func, wait) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}
