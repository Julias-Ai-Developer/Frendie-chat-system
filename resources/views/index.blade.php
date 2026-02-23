@php
    $user = auth()->user();
    $userName = $user?->name ?? 'User';
    $userEmail = $user?->email ?? 'user@example.com';
    $userInitial = strtoupper(substr($userName, 0, 1));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Frendie Chat</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .msg-scroll {
            scroll-behavior: smooth;
        }

        .msg-scroll::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        .msg-scroll::-webkit-scrollbar-track {
            background: #eef2f7;
            border-radius: 999px;
        }

        .msg-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .typing-dot {
            animation: typingPulse 1.1s infinite;
        }

        @keyframes typingPulse {
            0%, 60%, 100% {
                opacity: 0.35;
                transform: scale(1);
            }

            30% {
                opacity: 1;
                transform: scale(1.15);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 antialiased">
    <div class="min-h-screen p-3 sm:p-5">
        <div class="mx-auto flex h-[95vh] w-full max-w-7xl overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl">
            <aside
                id="membersPanel"
                class="fixed inset-y-0 left-0 z-40 flex w-[300px] -translate-x-full flex-col border-r border-gray-200 bg-gray-50 transition-transform duration-200 lg:static lg:z-auto lg:w-[300px] lg:translate-x-0"
            >
                <div class="bg-gradient-to-r from-teal-600 to-teal-500 px-4 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold tracking-wide">
                            <i class="fa-solid fa-users mr-2"></i>
                            Members
                        </h2>
                        <button
                            id="closeMembersPanel"
                            class="rounded-md p-1 text-teal-100 hover:bg-white/15 lg:hidden"
                            type="button"
                            aria-label="Close members panel"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div class="border-b border-gray-200 bg-white p-4">
                    <div class="rounded-2xl border border-teal-100 bg-teal-50 p-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-600 font-semibold text-white">
                                {{ $userInitial }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-800">{{ $userName }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $userEmail }}</p>
                            </div>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500" title="Online"></span>
                        </div>
                    </div>
                </div>

                <div id="membersList" class="msg-scroll flex-1 overflow-y-auto p-3"></div>

                <div class="border-t border-gray-200 bg-white p-4">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">Invite by email</label>
                    <div class="flex items-center gap-2">
                        <input
                            id="inviteEmail"
                            type="email"
                            placeholder="person@example.com"
                            class="w-full rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700 focus:border-teal-300 focus:bg-white focus:outline-none"
                        >
                        <button
                            id="inviteBtn"
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-500 text-white transition hover:bg-teal-600"
                            title="Send invite"
                        >
                            <i class="fa-regular fa-paper-plane text-sm"></i>
                        </button>
                    </div>
                    <p id="inviteFeedback" class="mt-2 min-h-5 text-xs text-teal-700"></p>
                </div>
            </aside>

            <div id="membersBackdrop" class="fixed inset-0 z-30 hidden bg-black/30 lg:hidden"></div>

            <main class="flex min-w-0 flex-1 flex-col">
                <header class="border-b border-gray-200 bg-white px-4 py-3 sm:px-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <button
                                id="openMembersPanel"
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 lg:hidden"
                                aria-label="Open members panel"
                            >
                                <i class="fa-solid fa-bars"></i>
                            </button>

                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-100 text-teal-700">
                                <i class="fa-solid fa-comments"></i>
                            </div>

                            <div>
                                <h1 class="text-base font-semibold text-gray-900 sm:text-lg">Frendie Chat Room</h1>
                                <p class="text-xs text-gray-500">
                                    <span id="onlineCount">0</span> online now
                                </p>
                            </div>
                        </div>

                        <div class="relative">
                            <button
                                id="accountMenuButton"
                                type="button"
                                class="flex items-center gap-2 rounded-full border border-gray-200 bg-white px-2 py-1.5 text-left shadow-sm transition hover:bg-gray-50"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-600 text-xs font-semibold text-white">{{ $userInitial }}</span>
                                <span class="hidden max-w-[150px] truncate text-sm font-medium text-gray-700 sm:block">{{ $userName }}</span>
                                <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                            </button>

                            <div
                                id="accountMenu"
                                class="absolute right-0 z-20 mt-2 hidden w-52 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
                                role="menu"
                            >
                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 transition hover:bg-gray-50"
                                    role="menuitem"
                                >
                                    <i class="fa-regular fa-user text-gray-500"></i>
                                    View profile
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-2 px-4 py-3 text-sm text-red-600 transition hover:bg-red-50"
                                        role="menuitem"
                                    >
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <section
                    id="chatMessages"
                    class="msg-scroll flex-1 space-y-3 overflow-y-auto bg-[radial-gradient(circle_at_top,_rgba(20,184,166,0.12),transparent_38%),linear-gradient(180deg,#f8fafc_0%,#f1f5f9_100%)] px-4 py-4 sm:px-6"
                ></section>

                <section id="suggestionStrip" class="msg-scroll flex items-center gap-2 overflow-x-auto border-t border-gray-200 bg-white px-4 py-3 sm:px-6"></section>

                <footer class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                    <div class="flex items-center gap-2">
                        <button type="button" class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-teal-600 hover:bg-gray-200">
                            <i class="fa-regular fa-face-smile"></i>
                        </button>
                        <input
                            id="messageInput"
                            type="text"
                            placeholder="Type your message..."
                            class="h-11 w-full rounded-full border border-gray-200 bg-gray-50 px-4 text-sm text-gray-700 focus:border-teal-300 focus:bg-white focus:outline-none"
                        >
                        <button
                            id="sendButton"
                            type="button"
                            class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-500 text-white shadow-sm transition hover:bg-teal-600 active:scale-95"
                        >
                            <i class="fa-regular fa-paper-plane"></i>
                        </button>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <script>
        (function() {
            const currentUser = {
                id: 'you',
                name: @json($userName),
                email: @json($userEmail),
                avatar: @json($userInitial),
            };

            const chatContainer = document.getElementById('chatMessages');
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            const suggestionStrip = document.getElementById('suggestionStrip');
            const membersListDiv = document.getElementById('membersList');
            const onlineCountSpan = document.getElementById('onlineCount');
            const inviteEmail = document.getElementById('inviteEmail');
            const inviteBtn = document.getElementById('inviteBtn');
            const inviteFeedback = document.getElementById('inviteFeedback');

            const accountMenuButton = document.getElementById('accountMenuButton');
            const accountMenu = document.getElementById('accountMenu');

            const membersPanel = document.getElementById('membersPanel');
            const membersBackdrop = document.getElementById('membersBackdrop');
            const openMembersPanel = document.getElementById('openMembersPanel');
            const closeMembersPanel = document.getElementById('closeMembersPanel');

            const members = [
                { id: 'm1', name: 'Alex Chen', avatar: 'A', online: true, email: 'alex@example.com' },
                { id: 'm2', name: 'Jamie Smith', avatar: 'J', online: true, email: 'jamie@example.com' },
                { id: 'm3', name: 'Taylor Kim', avatar: 'T', online: false, email: 'taylor@example.com' },
                { id: 'm4', name: 'Sam Rivera', avatar: 'S', online: true, email: 'sam@example.com' },
                { id: 'm5', name: 'Casey Lo', avatar: 'C', online: false, email: 'casey@example.com' },
            ];

            const messages = [
                { id: '1', senderId: 'ai', senderName: 'AI Assistant', text: 'Welcome to Frendie Chat. Use the suggestion chips to reply quickly.' },
                { id: '2', senderId: 'm1', senderName: 'Alex Chen', text: 'I just joined the room. Hello everyone.' },
                { id: '3', senderId: currentUser.id, senderName: currentUser.name, text: 'Welcome Alex, glad you are here.' },
                { id: '4', senderId: 'm2', senderName: 'Jamie Smith', text: 'The new layout looks great.' },
            ];

            function escapeHtml(unsafe) {
                return unsafe.replace(/[&<>"']/g, function(m) {
                    if (m === '&') return '&amp;';
                    if (m === '<') return '&lt;';
                    if (m === '>') return '&gt;';
                    if (m === '"') return '&quot;';
                    if (m === "'") return '&#039;';
                    return m;
                });
            }

            function toggleAccountMenu(forceOpen) {
                const shouldOpen = typeof forceOpen === 'boolean'
                    ? forceOpen
                    : accountMenu.classList.contains('hidden');

                accountMenu.classList.toggle('hidden', !shouldOpen);
                accountMenuButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            }

            function openSidePanel() {
                membersPanel.classList.remove('-translate-x-full');
                membersBackdrop.classList.remove('hidden');
            }

            function closeSidePanel() {
                membersPanel.classList.add('-translate-x-full');
                membersBackdrop.classList.add('hidden');
            }

            function renderMembers() {
                let html = '';

                html += `<div class="mb-2 flex items-center gap-3 rounded-xl border border-teal-100 bg-teal-50 p-2 shadow-sm">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-600 text-sm font-semibold text-white">${escapeHtml(currentUser.avatar)}</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-800">${escapeHtml(currentUser.name)} <span class="ml-1 text-xs text-teal-700">(you)</span></p>
                        <p class="truncate text-xs text-gray-500">${escapeHtml(currentUser.email)}</p>
                    </div>
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                </div>`;

                members.forEach(member => {
                    html += `<div class="mb-2 flex items-center gap-3 rounded-xl p-2 transition hover:bg-gray-100">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-500 text-sm font-semibold text-white">${escapeHtml(member.avatar)}</div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-800">${escapeHtml(member.name)}</p>
                            <p class="truncate text-xs text-gray-500">${member.online ? 'online' : 'offline'}</p>
                        </div>
                        <span class="h-2.5 w-2.5 rounded-full ${member.online ? 'bg-emerald-500' : 'bg-gray-300'}"></span>
                    </div>`;
                });

                membersListDiv.innerHTML = html;
                onlineCountSpan.innerText = String(members.filter(member => member.online).length + 1);
            }

            function renderMessages() {
                chatContainer.innerHTML = '';

                messages.forEach(message => {
                    const isCurrentUser = message.senderId === currentUser.id;
                    const isAssistant = message.senderId === 'ai';
                    const wrapper = document.createElement('div');
                    wrapper.className = isCurrentUser ? 'flex justify-end' : 'flex justify-start';

                    if (isCurrentUser) {
                        wrapper.innerHTML = `
                            <div class="max-w-[82%] rounded-2xl rounded-tr-none border border-teal-100 bg-teal-50 px-4 py-2 shadow-sm">
                                <p class="mb-1 text-xs font-medium text-teal-700">You - now</p>
                                <p class="text-sm text-gray-800">${escapeHtml(message.text)}</p>
                            </div>
                        `;
                    } else {
                        wrapper.innerHTML = `
                            <div class="max-w-[82%] rounded-2xl rounded-tl-none border border-gray-200 ${isAssistant ? 'bg-white' : 'bg-white/95'} px-4 py-2 shadow-sm">
                                <p class="mb-1 text-xs font-medium ${isAssistant ? 'text-teal-700' : 'text-indigo-600'}">${escapeHtml(message.senderName)}</p>
                                <p class="text-sm text-gray-800">${escapeHtml(message.text)}</p>
                            </div>
                        `;
                    }

                    chatContainer.appendChild(wrapper);
                });

                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            function generateSmartReplies(lastUserMessage) {
                if (!lastUserMessage || lastUserMessage.trim() === '') {
                    return ['Hello there', 'Can you help me', 'Sounds good'];
                }

                const lower = lastUserMessage.toLowerCase();

                if (lower.includes('hello') || lower.includes('hi') || lower.includes('hey')) {
                    return ['Hello', 'Nice to meet you', 'How are you'];
                }

                if (lower.includes('thanks') || lower.includes('thank you')) {
                    return ['You are welcome', 'Happy to help', 'Any time'];
                }

                if (lower.includes('meeting')) {
                    return ['Let us schedule it', 'What time works best', 'I am available'];
                }

                if (lower.includes('update') || lower.includes('status')) {
                    return ['Here is a quick update', 'I will share progress soon', 'Working on it now'];
                }

                return ['Interesting', 'Tell me more', 'I understand'];
            }

            function updateSuggestions() {
                let lastUserText = '';

                for (let i = messages.length - 1; i >= 0; i -= 1) {
                    if (messages[i].senderId === currentUser.id) {
                        lastUserText = messages[i].text;
                        break;
                    }
                }

                const suggestions = generateSmartReplies(lastUserText);
                suggestionStrip.innerHTML = '';

                const title = document.createElement('span');
                title.className = 'mr-1 text-xs font-semibold uppercase tracking-wide text-teal-700';
                title.innerText = 'AI suggestions';
                suggestionStrip.appendChild(title);

                suggestions.forEach(suggestion => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'whitespace-nowrap rounded-full border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-medium text-teal-800 transition hover:bg-teal-100';
                    button.textContent = suggestion;
                    button.addEventListener('click', function() {
                        sendMessage(suggestion);
                    });
                    suggestionStrip.appendChild(button);
                });
            }

            function sendMessage(text) {
                if (!text || text.trim() === '') return;

                const clean = text.trim();

                messages.push({
                    id: 'msg_' + Date.now(),
                    senderId: currentUser.id,
                    senderName: currentUser.name,
                    text: clean,
                });

                renderMessages();
                updateSuggestions();

                messageInput.value = '';
                simulateTypingAndReply(clean);

                if (Math.random() > 0.5) {
                    simulateMemberReply();
                }
            }

            function simulateTypingAndReply(userMessage) {
                const typingId = 'typing_assistant';
                document.getElementById(typingId)?.remove();

                const typing = document.createElement('div');
                typing.id = typingId;
                typing.className = 'flex justify-start';
                typing.innerHTML = `
                    <div class="flex items-center gap-1 rounded-2xl rounded-tl-none border border-gray-200 bg-white px-4 py-3 shadow-sm">
                        <span class="typing-dot h-2 w-2 rounded-full bg-teal-400"></span>
                        <span class="typing-dot h-2 w-2 rounded-full bg-teal-400" style="animation-delay:0.2s"></span>
                        <span class="typing-dot h-2 w-2 rounded-full bg-teal-400" style="animation-delay:0.4s"></span>
                        <span class="ml-1 text-xs text-gray-500">AI is typing</span>
                    </div>
                `;
                chatContainer.appendChild(typing);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                window.setTimeout(function() {
                    document.getElementById(typingId)?.remove();
                    const suggestions = generateSmartReplies(userMessage);
                    messages.push({
                        id: 'ai_' + Date.now(),
                        senderId: 'ai',
                        senderName: 'AI Assistant',
                        text: suggestions[0],
                    });
                    renderMessages();
                }, 1100);
            }

            function simulateMemberReply() {
                const onlineMembers = members.filter(member => member.online);
                if (onlineMembers.length === 0) return;

                const replies = ['Good point', 'I agree', 'Looks good', 'Let us do it', 'Thanks for the update'];
                const randomMember = onlineMembers[Math.floor(Math.random() * onlineMembers.length)];
                const randomReply = replies[Math.floor(Math.random() * replies.length)];

                window.setTimeout(function() {
                    messages.push({
                        id: 'member_' + Date.now(),
                        senderId: randomMember.id,
                        senderName: randomMember.name,
                        text: randomReply,
                    });
                    renderMessages();
                }, 1300 + Math.floor(Math.random() * 1400));
            }

            function sendInvite(email) {
                if (!email || !email.includes('@')) {
                    inviteFeedback.innerText = 'Please provide a valid email address.';
                    return;
                }

                const localName = email.split('@')[0];
                const display = localName ? localName[0].toUpperCase() + localName.slice(1, 10) : 'Friend';
                members.push({
                    id: 'inv_' + Date.now(),
                    name: display,
                    avatar: display.slice(0, 1).toUpperCase(),
                    online: false,
                    email: email,
                });

                inviteFeedback.innerText = `Invite sent to ${email}`;
                inviteEmail.value = '';

                messages.push({
                    id: 'invite_' + Date.now(),
                    senderId: 'ai',
                    senderName: 'AI Assistant',
                    text: `${display} was invited and will appear when they come online.`,
                });

                renderMembers();
                renderMessages();
            }

            accountMenuButton.addEventListener('click', function(event) {
                event.stopPropagation();
                toggleAccountMenu();
            });

            document.addEventListener('click', function(event) {
                if (!accountMenu.contains(event.target) && !accountMenuButton.contains(event.target)) {
                    toggleAccountMenu(false);
                }
            });

            openMembersPanel.addEventListener('click', openSidePanel);
            closeMembersPanel.addEventListener('click', closeSidePanel);
            membersBackdrop.addEventListener('click', closeSidePanel);

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    membersBackdrop.classList.add('hidden');
                    membersPanel.classList.remove('-translate-x-full');
                } else {
                    membersPanel.classList.add('-translate-x-full');
                }
            });

            sendButton.addEventListener('click', function() {
                sendMessage(messageInput.value);
            });

            messageInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendMessage(messageInput.value);
                }
            });

            inviteBtn.addEventListener('click', function() {
                sendInvite(inviteEmail.value.trim());
            });

            inviteEmail.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendInvite(inviteEmail.value.trim());
                }
            });

            window.setInterval(function() {
                members.forEach(function(member) {
                    if (Math.random() > 0.7) {
                        member.online = !member.online;
                    }
                });
                renderMembers();
            }, 14000);

            if (window.innerWidth >= 1024) {
                membersPanel.classList.remove('-translate-x-full');
            }

            renderMembers();
            renderMessages();
            updateSuggestions();
        })();
    </script>
</body>
</html>
