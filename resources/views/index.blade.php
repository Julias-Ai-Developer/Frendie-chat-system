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
    <style>
        .msg-scroll { scroll-behavior: smooth; }
        .msg-scroll::-webkit-scrollbar { width: 7px; height: 7px; }
        .msg-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 999px; }
        .msg-scroll::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 999px; }

        .typing-stars {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .typing-stars .typing-text {
            color: #0f766e;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: lowercase;
        }

        .typing-stars .star {
            color: #f59e0b;
            font-size: 10px;
            line-height: 1;
            opacity: 0.2;
            transform: translateY(0) scale(0.8);
            animation: typing-star 1.15s infinite ease-in-out;
        }

        .typing-stars .s2 {
            animation-delay: 0.2s;
        }

        .typing-stars .s3 {
            animation-delay: 0.4s;
        }

        @keyframes typing-star {
            0%, 100% {
                opacity: 0.2;
                transform: translateY(0) scale(0.8);
            }
            50% {
                opacity: 1;
                transform: translateY(-2px) scale(1.2);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 antialiased">
    <div class="min-h-screen p-3 sm:p-5">
        <div class="mx-auto flex h-[95vh] w-full max-w-7xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
            <aside id="usersPanel" class="fixed inset-y-0 left-0 z-40 flex w-[330px] -translate-x-full flex-col border-r border-slate-200 bg-slate-50 transition-transform duration-200 lg:static lg:z-auto lg:w-[330px] lg:translate-x-0">
                <div class="bg-gradient-to-r from-teal-700 to-teal-500 px-4 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold tracking-wide">Frendie Chat</h2>
                        <button id="closeUsersPanel" type="button" class="rounded-md px-2 py-1 text-teal-100 hover:bg-white/15 lg:hidden" aria-label="Close users panel">&times;</button>
                    </div>
                </div>
                <div class="border-b border-slate-200 bg-white p-4">
                    <div class="flex items-center gap-3 rounded-xl border border-teal-100 bg-teal-50 p-3">
                        <div id="profileBadgeSidebar" class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-600 text-sm font-semibold text-white">{{ $userInitial }}</div>
                        <div class="min-w-0 flex-1">
                            <p id="profileNameSidebar" class="truncate text-sm font-semibold text-slate-800">{{ $userName }}</p>
                            <p id="profileEmailSidebar" class="truncate text-xs text-slate-500">{{ $userEmail }}</p>
                        </div>
                    </div>
                </div>
                <div id="usersList" class="msg-scroll flex-1 overflow-y-auto p-3"></div>
            </aside>

            <div id="usersBackdrop" class="fixed inset-0 z-30 hidden bg-black/30 lg:hidden"></div>

            <main class="flex min-w-0 flex-1 flex-col bg-slate-100">
                <header class="border-b border-slate-200 bg-white px-4 py-3 sm:px-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <button id="openUsersPanel" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200 lg:hidden" aria-label="Open users panel">&#9776;</button>
                            <div id="activeChatAvatar" class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700">C</div>
                            <div class="min-w-0">
                                <h1 id="activeChatName" class="truncate text-base font-semibold text-slate-900 sm:text-lg">Select a user</h1>
                                <p id="activeChatMeta" class="truncate text-xs text-slate-500">Choose a contact to start chatting.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button id="audioCallButton" type="button" class="inline-flex h-10 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" disabled>Audio</button>
                            <button id="videoCallButton" type="button" class="inline-flex h-10 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" disabled>Video</button>

                            <div class="relative">
                                <button id="accountMenuButton" type="button" class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2 py-1.5 text-left shadow-sm transition hover:bg-slate-50" aria-expanded="false" aria-haspopup="true">
                                    <span id="profileBadgeHeader" class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-600 text-xs font-semibold text-white">{{ $userInitial }}</span>
                                    <span id="profileNameHeader" class="hidden max-w-[140px] truncate text-sm font-medium text-slate-700 sm:block">{{ $userName }}</span>
                                </button>
                                <div id="accountMenu" class="absolute right-0 z-20 mt-2 hidden w-52 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl" role="menu">
                                    <button id="openProfileDrawer" type="button" class="block w-full px-4 py-3 text-left text-sm text-slate-700 transition hover:bg-slate-50" role="menuitem">Profile</button>
                                    <div class="border-t border-slate-100"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full px-4 py-3 text-left text-sm text-red-600 transition hover:bg-red-50" role="menuitem">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <section id="chatMessages" class="msg-scroll flex-1 overflow-y-auto bg-[linear-gradient(180deg,#f8fafc_0%,#edf6f3_100%)] px-4 py-4 sm:px-6"></section>

                <footer class="border-t border-slate-200 bg-white px-4 py-3 sm:px-6">
                    <div id="attachmentPreview" class="mb-2 hidden rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2"></div>
                    <div id="typingStars" class="typing-stars mb-1 hidden pl-2" aria-live="polite">
                        <span class="typing-text">typing</span>
                        <span class="star s1">&#10022;</span>
                        <span class="star s2">&#10022;</span>
                        <span class="star s3">&#10022;</span>
                    </div>
                    <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                            <div class="flex h-12 items-center rounded-full border border-slate-200 bg-slate-50 px-2">
                                <button id="composeMenuButton" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-lg font-semibold text-slate-600 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:text-slate-400" aria-label="Open compose menu" disabled>+</button>
                                <div class="mx-2 h-5 w-px bg-slate-300"></div>
                                <button id="emojiButton" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-base text-slate-600 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:text-slate-400" aria-label="Open emoji picker" disabled>&#9786;</button>
                                <input id="messageInput" type="text" placeholder="Select a user first..." class="h-full w-full bg-transparent px-2 text-sm text-slate-700 focus:outline-none" disabled>
                            </div>

                            <div id="composeMenu" class="absolute bottom-14 left-0 z-20 hidden w-52 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                <button id="attachFileButton" type="button" class="mb-1 w-full rounded-xl px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400" disabled>Attach file</button>
                                <button id="attachAudioButton" type="button" class="mb-1 w-full rounded-xl px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400" disabled>Attach audio</button>
                                <button id="attachVideoButton" type="button" class="mb-1 w-full rounded-xl px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400" disabled>Attach video</button>
                                <button id="toggleViewOnceButton" type="button" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400" disabled>
                                    View once: <span id="viewOnceMenuState">Off</span>
                                </button>
                            </div>

                            <div id="emojiPanel" class="absolute bottom-14 left-12 z-20 hidden max-w-[220px] rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                <button type="button" data-emoji="&#128512;" class="rounded-lg px-2 py-1 text-lg hover:bg-slate-100">&#128512;</button>
                                <button type="button" data-emoji="&#128514;" class="rounded-lg px-2 py-1 text-lg hover:bg-slate-100">&#128514;</button>
                                <button type="button" data-emoji="&#128525;" class="rounded-lg px-2 py-1 text-lg hover:bg-slate-100">&#128525;</button>
                                <button type="button" data-emoji="&#128077;" class="rounded-lg px-2 py-1 text-lg hover:bg-slate-100">&#128077;</button>
                                <button type="button" data-emoji="&#128293;" class="rounded-lg px-2 py-1 text-lg hover:bg-slate-100">&#128293;</button>
                                <button type="button" data-emoji="&#128591;" class="rounded-lg px-2 py-1 text-lg hover:bg-slate-100">&#128591;</button>
                            </div>
                        </div>

                        <button id="sendButton" type="button" class="h-12 rounded-full bg-teal-500 px-5 text-sm font-semibold text-white transition hover:bg-teal-600 disabled:cursor-not-allowed disabled:bg-slate-300" disabled>Send</button>
                    </div>
                    <input id="viewOnceToggle" type="checkbox" class="hidden" disabled>
                    <p id="chatStatus" class="mt-2 text-right text-xs text-slate-500"></p>
                    <input id="attachmentInput" type="file" class="hidden">
                </footer>
            </main>
        </div>
    </div>

    <div id="profileDrawerBackdrop" class="fixed inset-0 z-40 hidden bg-black/30"></div>
    <aside id="profileDrawer" class="fixed right-0 top-0 z-50 flex h-full w-full max-w-md translate-x-full flex-col border-l border-slate-200 bg-white shadow-2xl transition-transform duration-200">
        <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-teal-700 to-teal-500 px-5 py-4 text-white">
            <div>
                <h2 class="text-base font-semibold">Profile</h2>
                <p class="text-xs text-teal-100">Edit your details without leaving chat</p>
            </div>
            <button id="closeProfileDrawer" type="button" class="rounded-md px-2 py-1 text-teal-100 hover:bg-white/15" aria-label="Close profile panel">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            <div class="rounded-2xl border border-teal-100 bg-teal-50 p-4">
                <div class="flex items-center gap-3">
                    <div id="profileBadgeDrawer" class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-600 text-base font-semibold text-white">{{ $userInitial }}</div>
                    <div class="min-w-0">
                        <p id="profileNameDrawer" class="truncate text-sm font-semibold text-slate-800">{{ $userName }}</p>
                        <p class="text-xs text-slate-500">Keep your chat profile up to date</p>
                    </div>
                </div>
            </div>
            <form id="profileForm" class="mt-4 space-y-4">
                <div>
                    <label for="profileNameInput" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-teal-700">Name</label>
                    <input id="profileNameInput" type="text" value="{{ $userName }}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-teal-300 focus:bg-white focus:outline-none" required>
                </div>
                <div>
                    <label for="profileEmailInput" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-teal-700">Email</label>
                    <input id="profileEmailInput" type="email" value="{{ $userEmail }}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-teal-300 focus:bg-white focus:outline-none" required>
                </div>
                <p id="profileFormError" class="hidden rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700"></p>
                <p id="profileFormSuccess" class="hidden rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"></p>
                <button id="profileSaveButton" type="submit" class="h-11 w-full rounded-full bg-teal-500 px-4 text-sm font-semibold text-white transition hover:bg-teal-600">Save changes</button>
            </form>
        </div>
    </aside>

    <div id="viewOnceBackdrop" class="fixed inset-0 z-[60] hidden bg-black/70"></div>
    <div id="viewOnceModal" class="fixed left-1/2 top-1/2 z-[61] hidden w-[92vw] max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">View once</h3>
            <button id="closeViewOnceButton" type="button" class="rounded-md px-2 py-1 text-slate-500 hover:bg-slate-100" aria-label="Close view once">&times;</button>
        </div>
        <div id="viewOnceContent" class="max-h-[70vh] overflow-auto p-4"></div>
    </div>

    <div id="callBackdrop" class="fixed inset-0 z-[72] hidden bg-slate-950/75"></div>
    <div id="callModal" class="fixed inset-x-2 top-3 bottom-3 z-[73] hidden overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-2xl sm:inset-x-8 sm:top-8 sm:bottom-8">
        <div class="flex items-center justify-between border-b border-slate-700 bg-slate-900 px-4 py-3 text-white">
            <div class="min-w-0">
                <p id="callTitle" class="truncate text-sm font-semibold">Call</p>
                <p id="callHint" class="text-xs text-slate-300">In-app call</p>
            </div>
            <button id="closeCallButton" type="button" class="rounded-full border border-slate-500 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-700">
                End call
            </button>
        </div>
        <iframe
            id="callFrame"
            title="Frendie in-app call"
            class="h-[calc(100%-56px)] w-full bg-black"
            allow="camera; microphone; autoplay; fullscreen; display-capture"
            referrerpolicy="no-referrer"
        ></iframe>
    </div>

    <script>
        (function () {
            const endpoints = {
                users: @json(route('chat.users')),
                conversationBase: @json(url('/chat/conversations')),
                send: @json(route('chat.send')),
                profileUpdate: @json(route('chat.profile.update')),
                typingUpdate: @json(route('chat.typing.update')),
                typingStatusBase: @json(url('/chat/typing')),
                callStart: @json(route('chat.call.start')),
                messagesBase: @json(url('/chat/messages')),
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const usersList = document.getElementById('usersList');
            const chatMessages = document.getElementById('chatMessages');
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            const chatStatus = document.getElementById('chatStatus');
            const activeChatName = document.getElementById('activeChatName');
            const activeChatMeta = document.getElementById('activeChatMeta');
            const typingStars = document.getElementById('typingStars');
            const activeChatAvatar = document.getElementById('activeChatAvatar');
            const usersPanel = document.getElementById('usersPanel');
            const usersBackdrop = document.getElementById('usersBackdrop');
            const openUsersPanel = document.getElementById('openUsersPanel');
            const closeUsersPanel = document.getElementById('closeUsersPanel');
            const audioCallButton = document.getElementById('audioCallButton');
            const videoCallButton = document.getElementById('videoCallButton');
            const attachFileButton = document.getElementById('attachFileButton');
            const attachAudioButton = document.getElementById('attachAudioButton');
            const attachVideoButton = document.getElementById('attachVideoButton');
            const composeMenuButton = document.getElementById('composeMenuButton');
            const composeMenu = document.getElementById('composeMenu');
            const emojiButton = document.getElementById('emojiButton');
            const emojiPanel = document.getElementById('emojiPanel');
            const toggleViewOnceButton = document.getElementById('toggleViewOnceButton');
            const viewOnceMenuState = document.getElementById('viewOnceMenuState');
            const attachmentInput = document.getElementById('attachmentInput');
            const attachmentPreview = document.getElementById('attachmentPreview');
            const viewOnceToggle = document.getElementById('viewOnceToggle');
            const accountMenuButton = document.getElementById('accountMenuButton');
            const accountMenu = document.getElementById('accountMenu');
            const openProfileDrawerButton = document.getElementById('openProfileDrawer');
            const closeProfileDrawerButton = document.getElementById('closeProfileDrawer');
            const profileDrawer = document.getElementById('profileDrawer');
            const profileDrawerBackdrop = document.getElementById('profileDrawerBackdrop');
            const profileForm = document.getElementById('profileForm');
            const profileNameInput = document.getElementById('profileNameInput');
            const profileEmailInput = document.getElementById('profileEmailInput');
            const profileSaveButton = document.getElementById('profileSaveButton');
            const profileFormError = document.getElementById('profileFormError');
            const profileFormSuccess = document.getElementById('profileFormSuccess');
            const profileNameSidebar = document.getElementById('profileNameSidebar');
            const profileEmailSidebar = document.getElementById('profileEmailSidebar');
            const profileNameHeader = document.getElementById('profileNameHeader');
            const profileBadgeSidebar = document.getElementById('profileBadgeSidebar');
            const profileBadgeHeader = document.getElementById('profileBadgeHeader');
            const profileBadgeDrawer = document.getElementById('profileBadgeDrawer');
            const profileNameDrawer = document.getElementById('profileNameDrawer');
            const viewOnceBackdrop = document.getElementById('viewOnceBackdrop');
            const viewOnceModal = document.getElementById('viewOnceModal');
            const viewOnceContent = document.getElementById('viewOnceContent');
            const closeViewOnceButton = document.getElementById('closeViewOnceButton');
            const callBackdrop = document.getElementById('callBackdrop');
            const callModal = document.getElementById('callModal');
            const callFrame = document.getElementById('callFrame');
            const callTitle = document.getElementById('callTitle');
            const callHint = document.getElementById('callHint');
            const closeCallButton = document.getElementById('closeCallButton');

            const horseSound = new Audio('https://actions.google.com/sounds/v1/animals/horse.ogg');
            horseSound.preload = 'auto';
            horseSound.volume = 0.45;

            let users = [];
            let selectedUserId = null;
            let selectedUser = null;
            let messages = [];
            let lastMessageId = 0;
            let loadingConversation = false;
            let selectedUserTyping = false;
            let unreadSnapshot = new Map();
            let pendingAttachment = null;
            let pendingAttachmentKind = 'file';
            let pendingViewOnceConsumeId = null;
            let typingTimer = null;
            let typingSent = false;
            let initialized = false;
            let activeCall = null;

            let profileData = {
                name: @json($userName),
                email: @json($userEmail),
                initials: @json($userInitial),
            };
            function escapeHtml(value) {
                return String(value).replace(/[&<>"']/g, function (char) {
                    if (char === '&') return '&amp;';
                    if (char === '<') return '&lt;';
                    if (char === '>') return '&gt;';
                    if (char === '"') return '&quot;';
                    if (char === "'") return '&#039;';
                    return char;
                });
            }

            function nl2br(text) {
                return escapeHtml(text).replace(/\n/g, '<br>');
            }

            function formatTime(isoDate) {
                if (!isoDate) return '';
                const date = new Date(isoDate);
                if (Number.isNaN(date.getTime())) return '';
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            function formatBytes(bytes) {
                if (!bytes || bytes <= 0) return '';
                const units = ['B', 'KB', 'MB', 'GB'];
                let size = bytes;
                let i = 0;
                while (size >= 1024 && i < units.length - 1) {
                    size /= 1024;
                    i += 1;
                }
                const rounded = size >= 10 || i === 0 ? Math.round(size) : Math.round(size * 10) / 10;
                return `${rounded} ${units[i]}`;
            }

            function getInitials(name) {
                if (!name) return 'U';
                return name.trim().split(/\s+/).slice(0, 2).map(function (part) {
                    return part.charAt(0).toUpperCase();
                }).join('') || 'U';
            }

            function playIncomingSound() {
                horseSound.currentTime = 0;
                horseSound.play().catch(function () {
                    // Browser may block autoplay until user interaction.
                });
            }

            function closeUsersSidebar() {
                usersPanel.classList.add('-translate-x-full');
                usersBackdrop.classList.add('hidden');
            }

            function openUsersSidebar() {
                usersPanel.classList.remove('-translate-x-full');
                usersBackdrop.classList.remove('hidden');
            }

            function toggleAccountMenu(forceOpen) {
                const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : accountMenu.classList.contains('hidden');
                accountMenu.classList.toggle('hidden', !shouldOpen);
                accountMenuButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            }

            function openProfileDrawer() {
                profileDrawer.classList.remove('translate-x-full');
                profileDrawerBackdrop.classList.remove('hidden');
                profileNameInput.focus();
            }

            function closeProfileDrawer() {
                profileDrawer.classList.add('translate-x-full');
                profileDrawerBackdrop.classList.add('hidden');
            }

            function toggleComposeMenu(forceOpen) {
                const shouldOpen = typeof forceOpen === 'boolean'
                    ? forceOpen
                    : composeMenu.classList.contains('hidden');

                composeMenu.classList.toggle('hidden', !shouldOpen);
                if (shouldOpen) {
                    emojiPanel.classList.add('hidden');
                }
            }

            function toggleEmojiPanel(forceOpen) {
                const shouldOpen = typeof forceOpen === 'boolean'
                    ? forceOpen
                    : emojiPanel.classList.contains('hidden');

                emojiPanel.classList.toggle('hidden', !shouldOpen);
                if (shouldOpen) {
                    composeMenu.classList.add('hidden');
                }
            }

            function renderViewOnceState() {
                const enabled = viewOnceToggle.checked;
                viewOnceMenuState.textContent = enabled ? 'On' : 'Off';
                toggleViewOnceButton.classList.toggle('border-teal-300', enabled);
                toggleViewOnceButton.classList.toggle('bg-teal-50', enabled);
            }

            function setProfileUi(user) {
                const initials = (user.initials || getInitials(user.name)).slice(0, 2).toUpperCase();
                profileData = { name: user.name, email: user.email, initials: initials };
                profileNameSidebar.textContent = user.name;
                profileEmailSidebar.textContent = user.email;
                profileNameHeader.textContent = user.name;
                profileNameDrawer.textContent = user.name;
                profileBadgeSidebar.textContent = initials;
                profileBadgeHeader.textContent = initials;
                profileBadgeDrawer.textContent = initials;
            }

            function clearProfileFeedback() {
                profileFormError.classList.add('hidden');
                profileFormSuccess.classList.add('hidden');
                profileFormError.textContent = '';
                profileFormSuccess.textContent = '';
            }

            function setProfileError(message) {
                profileFormSuccess.classList.add('hidden');
                profileFormError.classList.remove('hidden');
                profileFormError.textContent = message;
            }

            function renderUsers() {
                if (users.length === 0) {
                    usersList.innerHTML = '<div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600">No other users found.</div>';
                    return;
                }

                usersList.innerHTML = users.map(function (user) {
                    const active = selectedUserId === user.id;
                    const lastMessage = user.last_message
                        ? `${user.last_message_is_mine ? 'You: ' : ''}${escapeHtml(String(user.last_message).slice(0, 42))}`
                        : 'No messages yet';

                    return `
                        <button type="button" data-user-id="${user.id}" class="mb-2 flex w-full items-center gap-3 rounded-xl border px-3 py-2 text-left transition ${active ? 'border-teal-200 bg-teal-50' : 'border-transparent bg-white hover:border-slate-200 hover:bg-slate-50'}">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-semibold text-white">${escapeHtml(user.initials || getInitials(user.name))}</div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm font-semibold text-slate-800">${escapeHtml(user.name)}</p>
                                    <p class="text-[11px] text-slate-500">${formatTime(user.last_message_at)}</p>
                                </div>
                                <div class="mt-0.5 flex items-center justify-between gap-2">
                                    <p class="truncate text-xs text-slate-500">${lastMessage}</p>
                                    ${user.unread_count > 0 ? `<span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-teal-500 px-1 text-[10px] font-semibold text-white">${user.unread_count}</span>` : ''}
                                </div>
                            </div>
                        </button>
                    `;
                }).join('');
            }

            function renderActiveChatHeader() {
                if (!selectedUser) {
                    activeChatName.textContent = 'Select a user';
                    activeChatMeta.textContent = 'Choose a contact to start chatting.';
                    typingStars.classList.add('hidden');
                    activeChatAvatar.textContent = 'C';
                    audioCallButton.disabled = true;
                    videoCallButton.disabled = true;
                    return;
                }

                activeChatName.textContent = selectedUser.name;
                activeChatMeta.textContent = selectedUser.email || 'Direct message';
                typingStars.classList.toggle('hidden', !selectedUserTyping);
                activeChatAvatar.textContent = (selectedUser.initials || getInitials(selectedUser.name)).slice(0, 2).toUpperCase();
                audioCallButton.disabled = false;
                videoCallButton.disabled = false;
            }

            function renderComposerState() {
                const enabled = Boolean(selectedUserId);
                messageInput.disabled = !enabled;
                sendButton.disabled = !enabled;
                viewOnceToggle.disabled = !enabled;
                composeMenuButton.disabled = !enabled;
                emojiButton.disabled = !enabled;
                attachFileButton.disabled = !enabled;
                attachAudioButton.disabled = !enabled;
                attachVideoButton.disabled = !enabled;
                toggleViewOnceButton.disabled = !enabled;
                messageInput.placeholder = enabled ? 'Type your message...' : 'Select a user first...';
                chatStatus.textContent = enabled ? '' : 'Open a conversation to send messages.';
                if (!enabled) {
                    pendingAttachment = null;
                    attachmentInput.value = '';
                    viewOnceToggle.checked = false;
                    toggleComposeMenu(false);
                    toggleEmojiPanel(false);
                }
                renderViewOnceState();
                renderAttachmentPreview();
            }

            function renderAttachmentPreview() {
                if (!pendingAttachment) {
                    attachmentPreview.classList.add('hidden');
                    attachmentPreview.innerHTML = '';
                    return;
                }

                attachmentPreview.innerHTML = `
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold text-slate-700">${pendingAttachmentKind.toUpperCase()} selected: ${escapeHtml(pendingAttachment.name)}</p>
                            <p class="text-[11px] text-slate-500">${escapeHtml(pendingAttachment.type || 'application/octet-stream')} ${formatBytes(pendingAttachment.size) ? `- ${formatBytes(pendingAttachment.size)}` : ''}</p>
                        </div>
                        <button type="button" data-action="remove-attachment" class="rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-600 hover:bg-white">Remove</button>
                    </div>
                `;
                attachmentPreview.classList.remove('hidden');
            }

            function renderMessageContent(message) {
                if (message.message_type === 'call') {
                    const mode = message.call_mode === 'video' ? 'Video call' : 'Audio call';
                    return `
                        <div class="rounded-xl border border-teal-200 bg-teal-50 px-3 py-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">${mode} invite</p>
                            <p class="mt-1 text-sm text-slate-700">${escapeHtml(message.body || `${mode} request`)}</p>
                            ${message.call_url ? `<button data-action="join-call" data-url="${escapeHtml(message.call_url)}" data-mode="${escapeHtml(message.call_mode || 'video')}" class="mt-2 inline-flex rounded-full bg-teal-600 px-3 py-1 text-xs font-semibold text-white hover:bg-teal-700">Join in app</button>` : ''}
                        </div>
                    `;
                }

                if (message.view_once) {
                    if (message.is_view_once_consumed) {
                        return '<div class="rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-500">View-once message expired.</div>';
                    }

                    if (message.can_consume_view_once) {
                        return `<button type="button" data-action="open-view-once" data-message-id="${message.id}" class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100">Open view-once message</button>`;
                    }

                    return '<div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">View-once message sent.</div>';
                }

                const bodyHtml = message.body && String(message.body).trim() !== ''
                    ? `<p class="text-sm text-slate-800">${nl2br(String(message.body))}</p>`
                    : '';

                if (!message.attachment_url) {
                    return bodyHtml || '<p class="text-sm text-slate-500">No content</p>';
                }

                const url = escapeHtml(message.attachment_url);
                const name = escapeHtml(message.attachment_name || 'attachment');
                const size = formatBytes(message.attachment_size);

                if (message.message_type === 'image') {
                    return `${bodyHtml}<a href="${url}" target="_blank" rel="noopener" class="mt-2 block overflow-hidden rounded-xl border border-slate-200 bg-white"><img src="${url}" alt="Image" class="max-h-64 w-full object-cover"></a>`;
                }

                if (message.message_type === 'video') {
                    return `${bodyHtml}<video controls class="mt-2 max-h-72 w-full rounded-xl border border-slate-200 bg-black"><source src="${url}"></video>`;
                }

                if (message.message_type === 'audio') {
                    return `${bodyHtml}<audio controls class="mt-2 w-full"><source src="${url}"></audio>`;
                }

                return `${bodyHtml}<a href="${url}" target="_blank" rel="noopener" class="mt-2 inline-flex max-w-full items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"><span class="truncate">${name}</span>${size ? `<span class="shrink-0 text-slate-500">${escapeHtml(size)}</span>` : ''}</a>`;
            }

            function renderMessages(shouldScroll) {
                if (!selectedUserId) {
                    chatMessages.innerHTML = '<div class="mx-auto mt-20 max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm"><h3 class="text-sm font-semibold text-slate-800">Start a conversation</h3><p class="mt-2 text-xs text-slate-500">Click a user on the left to open your direct chat.</p></div>';
                    return;
                }

                if (messages.length === 0) {
                    chatMessages.innerHTML = `<div class="mx-auto mt-20 max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm"><h3 class="text-sm font-semibold text-slate-800">No messages yet</h3><p class="mt-2 text-xs text-slate-500">Send the first message to ${escapeHtml(selectedUser ? selectedUser.name : 'this user')}.</p></div>`;
                    return;
                }

                chatMessages.innerHTML = messages.map(function (message) {
                    const mine = Boolean(message.is_mine);
                    const align = mine ? 'justify-end' : 'justify-start';
                    const bubble = mine ? 'border-teal-200 bg-teal-50 rounded-br-sm' : 'border-slate-200 bg-white rounded-bl-sm';
                    const status = mine ? (message.read_at ? '<span class="text-teal-700">Seen</span>' : '<span class="text-slate-400">Sent</span>') : '';

                    return `
                        <div class="mb-3 flex ${align}">
                            <div class="max-w-[88%] rounded-2xl border px-3 py-2 shadow-sm ${bubble}">
                                ${renderMessageContent(message)}
                                <div class="mt-1 flex items-center justify-end gap-2 text-[11px]">
                                    <span class="text-slate-500">${formatTime(message.created_at)}</span>
                                    ${status}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                if (shouldScroll) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }

            async function requestJson(url, options) {
                const response = await fetch(url, options);
                const data = await response.json().catch(function () { return {}; });

                if (!response.ok) {
                    if (data.errors && typeof data.errors === 'object') {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && Array.isArray(data.errors[firstKey]) && data.errors[firstKey].length > 0) {
                            throw new Error(data.errors[firstKey][0]);
                        }
                    }
                    throw new Error(data.message || 'Request failed');
                }

                return data;
            }

            async function loadUsers(options) {
                const preserveSelection = options && options.preserveSelection === true;
                const data = await requestJson(endpoints.users, { headers: { Accept: 'application/json' } });
                const incomingUsers = data.users || [];
                const nextUnread = new Map();

                incomingUsers.forEach(function (user) {
                    nextUnread.set(user.id, Number(user.unread_count || 0));
                });

                if (initialized && preserveSelection) {
                    let hasNewUnread = false;
                    nextUnread.forEach(function (count, userId) {
                        if (count > (unreadSnapshot.get(userId) || 0)) {
                            hasNewUnread = true;
                        }
                    });
                    if (hasNewUnread) {
                        playIncomingSound();
                    }
                }

                unreadSnapshot = nextUnread;
                users = incomingUsers;

                if (!preserveSelection || !users.some(function (user) { return user.id === selectedUserId; })) {
                    selectedUserId = users.length > 0 ? users[0].id : null;
                    selectedUserTyping = false;
                    messages = [];
                    lastMessageId = 0;
                }

                selectedUser = users.find(function (user) { return user.id === selectedUserId; }) || null;

                renderUsers();
                renderActiveChatHeader();
                renderComposerState();
            }

            async function loadConversation(options) {
                const incremental = options && options.incremental === true;
                if (!selectedUserId || loadingConversation) return;
                loadingConversation = true;

                try {
                    const url = new URL(`${endpoints.conversationBase}/${selectedUserId}`, window.location.origin);
                    if (incremental && lastMessageId > 0) url.searchParams.set('after', String(lastMessageId));

                    const data = await requestJson(url.toString(), { headers: { Accept: 'application/json' } });
                    selectedUser = data.user || selectedUser;

                    const incoming = data.messages || [];
                    let hasIncomingFromOther = false;

                    if (incremental) {
                        const known = new Set(messages.map(function (m) { return m.id; }));
                        incoming.forEach(function (message) {
                            if (!known.has(message.id)) {
                                messages.push(message);
                                if (!message.is_mine) hasIncomingFromOther = true;
                            }
                        });
                    } else {
                        messages = incoming;
                    }

                    if (hasIncomingFromOther) playIncomingSound();

                    lastMessageId = messages.length > 0 ? messages[messages.length - 1].id : 0;
                    renderActiveChatHeader();
                    renderMessages(true);
                } finally {
                    loadingConversation = false;
                }
            }
            async function loadTypingStatus() {
                if (!selectedUserId) {
                    selectedUserTyping = false;
                    renderActiveChatHeader();
                    return;
                }

                try {
                    const data = await requestJson(`${endpoints.typingStatusBase}/${selectedUserId}`, {
                        headers: { Accept: 'application/json' },
                    });
                    selectedUserTyping = Boolean(data.typing);
                    renderActiveChatHeader();
                } catch (_) {
                    // Ignore typing poll failures.
                }
            }

            async function sendTypingStatus(typing, overrideRecipientId) {
                const recipientId = overrideRecipientId || selectedUserId;
                if (!recipientId) return;

                if (!overrideRecipientId && typingSent === typing) return;
                if (!overrideRecipientId) typingSent = typing;

                try {
                    await requestJson(endpoints.typingUpdate, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ recipient_id: recipientId, typing: Boolean(typing) }),
                    });
                } catch (_) {
                    // Ignore typing send failures.
                }
            }

            function queueTypingPulse() {
                const hasDraft = messageInput.value.trim() !== '' || Boolean(pendingAttachment);

                if (hasDraft) {
                    sendTypingStatus(true);
                    if (typingTimer) clearTimeout(typingTimer);
                    typingTimer = setTimeout(function () {
                        sendTypingStatus(false);
                    }, 1500);
                } else {
                    if (typingTimer) {
                        clearTimeout(typingTimer);
                        typingTimer = null;
                    }
                    sendTypingStatus(false);
                }
            }

            async function sendMessage() {
                const text = messageInput.value.trim();
                if (!selectedUserId || (text === '' && !pendingAttachment)) return;

                sendButton.disabled = true;
                try {
                    const formData = new FormData();
                    formData.append('recipient_id', String(selectedUserId));
                    if (text !== '') formData.append('body', text);
                    if (pendingAttachment) formData.append('attachment', pendingAttachment);
                    if (viewOnceToggle.checked) formData.append('view_once', '1');

                    const data = await requestJson(endpoints.send, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    if (data.message) {
                        messages.push(data.message);
                        lastMessageId = data.message.id;
                    }

                    messageInput.value = '';
                    pendingAttachment = null;
                    attachmentInput.value = '';
                    viewOnceToggle.checked = false;
                    renderViewOnceState();
                    renderAttachmentPreview();
                    renderMessages(true);
                    await sendTypingStatus(false);
                    await loadUsers({ preserveSelection: true });
                } catch (error) {
                    chatStatus.textContent = error.message;
                } finally {
                    sendButton.disabled = false;
                    renderComposerState();
                }
            }

            function openInAppCall(callUrl, mode, partnerName) {
                if (!callUrl) return;

                const modeLabel = mode === 'audio' ? 'Audio call' : 'Video call';
                const userLabel = partnerName || (selectedUser ? selectedUser.name : 'contact');

                callTitle.textContent = `${modeLabel} with ${userLabel}`;
                callHint.textContent = mode === 'audio'
                    ? 'Mic-only mode. You can still enable video in meeting controls.'
                    : 'Camera and microphone enabled inside the app.';

                activeCall = {
                    url: callUrl,
                    mode: mode,
                    partner: userLabel,
                };

                callFrame.src = callUrl;
                callBackdrop.classList.remove('hidden');
                callModal.classList.remove('hidden');
            }

            function closeInAppCall() {
                if (activeCall === null && callModal.classList.contains('hidden')) {
                    return;
                }

                activeCall = null;
                callFrame.src = 'about:blank';
                callBackdrop.classList.add('hidden');
                callModal.classList.add('hidden');
            }

            async function startCall(mode) {
                if (!selectedUserId) return;

                try {
                    const data = await requestJson(endpoints.callStart, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ recipient_id: selectedUserId, mode: mode }),
                    });

                    if (data.message) {
                        messages.push(data.message);
                        lastMessageId = data.message.id;
                        renderMessages(true);
                        if (data.message.call_url) {
                            openInAppCall(
                                data.message.call_url,
                                data.message.call_mode || mode,
                                selectedUser ? selectedUser.name : ''
                            );
                        }
                    }

                    await loadUsers({ preserveSelection: true });
                } catch (error) {
                    chatStatus.textContent = error.message;
                }
            }

            function openViewOnceModal(contentHtml, messageId) {
                pendingViewOnceConsumeId = messageId;
                viewOnceContent.innerHTML = contentHtml;
                viewOnceBackdrop.classList.remove('hidden');
                viewOnceModal.classList.remove('hidden');
            }

            async function consumePendingViewOnceMessage() {
                if (!pendingViewOnceConsumeId) return;
                const messageId = pendingViewOnceConsumeId;
                pendingViewOnceConsumeId = null;

                try {
                    await requestJson(`${endpoints.messagesBase}/${messageId}/consume-view-once`, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                    await loadConversation({ incremental: false });
                    await loadUsers({ preserveSelection: true });
                } catch (_) {
                    // Ignore view-once consume failures.
                }
            }

            function openViewOnceMessage(messageId) {
                const message = messages.find(function (item) { return item.id === messageId; });
                if (!message || !message.can_consume_view_once) return;

                const bodyText = message.body && String(message.body).trim() !== ''
                    ? `<p class="mb-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">${nl2br(String(message.body))}</p>`
                    : '';

                let contentHtml = '';

                if (message.attachment_url) {
                    const url = escapeHtml(message.attachment_url);
                    if (message.message_type === 'image') {
                        contentHtml = `${bodyText}<img src="${url}" alt="View once image" class="max-h-[55vh] w-full rounded-xl border border-slate-200 object-contain"><p class="mt-2 text-xs text-slate-500">Close this dialog after viewing. It can be opened only once.</p>`;
                    } else if (message.message_type === 'video') {
                        contentHtml = `${bodyText}<video controls class="max-h-[55vh] w-full rounded-xl border border-slate-200 bg-black"><source src="${url}"></video><p class="mt-2 text-xs text-slate-500">Close this dialog after viewing. It can be opened only once.</p>`;
                    } else if (message.message_type === 'audio') {
                        contentHtml = `${bodyText}<audio controls class="w-full"><source src="${url}"></audio><p class="mt-2 text-xs text-slate-500">Close this dialog after listening. It can be opened only once.</p>`;
                    } else {
                        const name = escapeHtml(message.attachment_name || 'attachment');
                        contentHtml = `${bodyText}<a href="${url}" target="_blank" rel="noopener" class="inline-flex rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Open ${name}</a><p class="mt-2 text-xs text-slate-500">Close this dialog after opening. It can be opened only once.</p>`;
                    }
                } else {
                    contentHtml = `<p class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">${nl2br(String(message.body || 'View-once message'))}</p><p class="mt-2 text-xs text-slate-500">Close this dialog after viewing. It can be opened only once.</p>`;
                }

                openViewOnceModal(contentHtml, message.id);
            }

            async function closeViewOnceModal() {
                viewOnceBackdrop.classList.add('hidden');
                viewOnceModal.classList.add('hidden');
                viewOnceContent.innerHTML = '';
                await consumePendingViewOnceMessage();
            }

            async function updateProfile(event) {
                event.preventDefault();
                clearProfileFeedback();

                const payload = {
                    name: profileNameInput.value.trim(),
                    email: profileEmailInput.value.trim(),
                };

                if (payload.name === '' || payload.email === '') {
                    setProfileError('Name and email are required.');
                    return;
                }

                profileSaveButton.disabled = true;
                profileSaveButton.classList.add('opacity-75');

                try {
                    const data = await requestJson(endpoints.profileUpdate, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    if (data.user) {
                        setProfileUi(data.user);
                    }

                    profileFormSuccess.classList.remove('hidden');
                    profileFormSuccess.textContent = data.message || 'Profile updated successfully.';
                } catch (error) {
                    setProfileError(error.message);
                } finally {
                    profileSaveButton.disabled = false;
                    profileSaveButton.classList.remove('opacity-75');
                }
            }

            usersList.addEventListener('click', async function (event) {
                const button = event.target.closest('[data-user-id]');
                if (!button) return;

                const nextId = Number(button.getAttribute('data-user-id'));
                if (!nextId || nextId === selectedUserId) return;

                const previousUserId = selectedUserId;
                selectedUserId = nextId;
                selectedUserTyping = false;
                typingSent = false;

                if (typingTimer) {
                    clearTimeout(typingTimer);
                    typingTimer = null;
                }

                if (previousUserId) {
                    sendTypingStatus(false, previousUserId);
                }

                selectedUser = users.find(function (user) { return user.id === selectedUserId; }) || null;
                messages = [];
                lastMessageId = 0;

                renderUsers();
                renderActiveChatHeader();
                renderComposerState();
                renderMessages(false);

                await loadConversation({ incremental: false });
                await loadTypingStatus();
                closeUsersSidebar();
            });

            composeMenuButton.addEventListener('click', function (event) {
                event.stopPropagation();
                if (composeMenuButton.disabled) return;
                toggleComposeMenu();
            });

            emojiButton.addEventListener('click', function (event) {
                event.stopPropagation();
                if (emojiButton.disabled) return;
                toggleEmojiPanel();
            });

            emojiPanel.addEventListener('click', function (event) {
                const emojiButtonEl = event.target.closest('[data-emoji]');
                if (!emojiButtonEl || messageInput.disabled) return;
                const emoji = emojiButtonEl.getAttribute('data-emoji') || '';
                messageInput.value += emoji;
                messageInput.focus();
                queueTypingPulse();
            });

            toggleViewOnceButton.addEventListener('click', function () {
                if (toggleViewOnceButton.disabled) return;
                viewOnceToggle.checked = !viewOnceToggle.checked;
                renderViewOnceState();
            });

            sendButton.addEventListener('click', sendMessage);
            messageInput.addEventListener('keypress', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendMessage();
                }
            });
            messageInput.addEventListener('input', queueTypingPulse);
            attachFileButton.addEventListener('click', function () {
                toggleComposeMenu(false);
                pendingAttachmentKind = 'file';
                attachmentInput.accept = '*/*';
                attachmentInput.removeAttribute('capture');
                attachmentInput.click();
            });

            attachAudioButton.addEventListener('click', function () {
                toggleComposeMenu(false);
                pendingAttachmentKind = 'audio';
                attachmentInput.accept = 'audio/*';
                attachmentInput.setAttribute('capture', 'microphone');
                attachmentInput.click();
            });

            attachVideoButton.addEventListener('click', function () {
                toggleComposeMenu(false);
                pendingAttachmentKind = 'video';
                attachmentInput.accept = 'video/*';
                attachmentInput.setAttribute('capture', 'user');
                attachmentInput.click();
            });

            attachmentInput.addEventListener('change', function () {
                pendingAttachment = attachmentInput.files && attachmentInput.files.length > 0
                    ? attachmentInput.files[0]
                    : null;
                renderAttachmentPreview();
                queueTypingPulse();
            });

            attachmentPreview.addEventListener('click', function (event) {
                const button = event.target.closest('[data-action="remove-attachment"]');
                if (!button) return;
                pendingAttachment = null;
                attachmentInput.value = '';
                renderAttachmentPreview();
                queueTypingPulse();
            });

            chatMessages.addEventListener('click', function (event) {
                const openOnceButton = event.target.closest('[data-action="open-view-once"]');
                if (openOnceButton) {
                    const messageId = Number(openOnceButton.getAttribute('data-message-id'));
                    if (messageId) openViewOnceMessage(messageId);
                    return;
                }

                const joinCallButton = event.target.closest('[data-action="join-call"]');
                if (joinCallButton) {
                    const url = joinCallButton.getAttribute('data-url');
                    const mode = joinCallButton.getAttribute('data-mode') || 'video';
                    if (url) {
                        openInAppCall(url, mode, selectedUser ? selectedUser.name : '');
                    }
                }
            });

            audioCallButton.addEventListener('click', function () {
                startCall('audio');
            });

            videoCallButton.addEventListener('click', function () {
                startCall('video');
            });

            openUsersPanel.addEventListener('click', openUsersSidebar);
            closeUsersPanel.addEventListener('click', closeUsersSidebar);
            usersBackdrop.addEventListener('click', closeUsersSidebar);

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    usersPanel.classList.remove('-translate-x-full');
                    usersBackdrop.classList.add('hidden');
                } else {
                    usersPanel.classList.add('-translate-x-full');
                }
            });

            accountMenuButton.addEventListener('click', function (event) {
                event.stopPropagation();
                toggleAccountMenu();
            });

            openProfileDrawerButton.addEventListener('click', function () {
                toggleAccountMenu(false);
                clearProfileFeedback();
                profileNameInput.value = profileData.name;
                profileEmailInput.value = profileData.email;
                openProfileDrawer();
            });

            closeProfileDrawerButton.addEventListener('click', closeProfileDrawer);
            profileDrawerBackdrop.addEventListener('click', closeProfileDrawer);
            profileForm.addEventListener('submit', updateProfile);

            closeViewOnceButton.addEventListener('click', closeViewOnceModal);
            viewOnceBackdrop.addEventListener('click', closeViewOnceModal);
            closeCallButton.addEventListener('click', closeInAppCall);
            callBackdrop.addEventListener('click', closeInAppCall);

            document.addEventListener('click', function (event) {
                if (!accountMenu.contains(event.target) && !accountMenuButton.contains(event.target)) {
                    toggleAccountMenu(false);
                }

                if (!composeMenu.contains(event.target) && !composeMenuButton.contains(event.target)) {
                    toggleComposeMenu(false);
                }

                if (!emojiPanel.contains(event.target) && !emojiButton.contains(event.target)) {
                    toggleEmojiPanel(false);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeProfileDrawer();
                    toggleAccountMenu(false);
                    toggleComposeMenu(false);
                    toggleEmojiPanel(false);
                    if (!viewOnceModal.classList.contains('hidden')) {
                        closeViewOnceModal();
                    }
                    if (!callModal.classList.contains('hidden')) {
                        closeInAppCall();
                    }
                }
            });

            (async function init() {
                setProfileUi(profileData);
                clearProfileFeedback();
                renderViewOnceState();

                try {
                    await loadUsers({ preserveSelection: false });
                    renderMessages(false);

                    if (selectedUserId) {
                        await loadConversation({ incremental: false });
                        await loadTypingStatus();
                    }
                } catch (error) {
                    chatStatus.textContent = error.message;
                }

                initialized = true;

                window.setInterval(async function () {
                    try {
                        await loadUsers({ preserveSelection: true });
                        await loadConversation({ incremental: true });
                        await loadTypingStatus();
                    } catch (_) {
                        // Keep current UI state when polling fails.
                    }
                }, 2500);
            })();
        })();
    </script>
</body>
</html>

