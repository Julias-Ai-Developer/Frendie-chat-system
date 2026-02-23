<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Chat · members & invite</title>
  <!-- Tailwind + icons -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .msg-scroll { scroll-behavior: smooth; }
    .msg-scroll::-webkit-scrollbar { width: 6px; }
    .msg-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .msg-scroll::-webkit-scrollbar-thumb { background: #c0c0c0; border-radius: 10px; }
    .suggest-enter { animation: softGlow 0.4s ease; }
    @keyframes softGlow { 0% { opacity: 0.6; transform: scale(0.98); } 100% { opacity: 1; transform: scale(1); } }
    .typing-dot { animation: typingPulse 1.4s infinite; }
    @keyframes typingPulse { 0%,60%,100% { opacity: 0.4; transform: scale(1); } 30% { opacity: 1; transform: scale(1.2); } }
    #suggestionStrip::-webkit-scrollbar { height: 4px; background: #f0f0f0; border-radius: 4px; }
    #suggestionStrip::-webkit-scrollbar-thumb { background: #c0c0c0; border-radius: 4px; }
    .member-online { position: relative; }
    .member-online::after { content: ''; position: absolute; bottom: 2px; right: 2px; width: 10px; height: 10px; background-color: #22c55e; border: 2px solid white; border-radius: 50%; }
  </style>
</head>
<body class="bg-gray-100 antialiased font-sans flex items-center justify-center min-h-screen p-3">

  <!-- main chat card – wider to accommodate member sidebar -->
  <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-200 flex flex-row h-[90vh]">

    <!-- LEFT SIDEBAR – members & invite panel (whatsapp style) -->
    <div class="w-1/4 bg-gray-50 border-r border-gray-200 flex flex-col">
      <!-- sidebar header -->
      <div class="bg-teal-600 px-4 py-5 text-white flex items-center justify-between">
        <h3 class="font-semibold text-sm flex items-center gap-2"><i class="fa-solid fa-users"></i> Members · online</h3>
        <i class="fa-solid fa-user-plus cursor-pointer hover:text-teal-100 transition text-lg" id="openInviteModal" title="Invite people"></i>
      </div>
      <!-- members list (dynamic) -->
      <div id="membersList" class="flex-1 overflow-y-auto p-3 space-y-2">
        <!-- filled via JS -->
      </div>
      <!-- invite box (simple input + send invite) -->
      <div class="p-3 border-t border-gray-200 bg-white">
        <div class="flex items-center gap-2">
          <input type="email" id="inviteEmail" placeholder="Email to invite..." class="w-full text-sm bg-gray-100 rounded-full px-4 py-2 focus:outline-none focus:ring-1 focus:ring-teal-300">
          <button id="inviteBtn" class="bg-teal-500 hover:bg-teal-600 text-white rounded-full p-2 w-9 h-9 flex items-center justify-center shadow-sm transition" title="send invite">
            <i class="fa-regular fa-paper-plane text-sm"></i>
          </button>
        </div>
        <p id="inviteFeedback" class="text-xs text-teal-600 mt-1 italic h-4"></p>
      </div>
    </div>

    <!-- RIGHT PANEL – chat area (same as before, enhanced) -->
    <div class="w-3/4 flex flex-col">
      <!-- header with room name and online count -->
      <div class="bg-gradient-to-r from-teal-600 to-teal-500 px-5 py-4 flex items-center justify-between text-white shadow-sm">
        <div class="flex items-center gap-3">
          <div class="bg-white/20 p-2 rounded-2xl backdrop-blur-sm">
            <i class="fa-brands fa-whatsapp text-xl"></i>
          </div>
          <div>
            <h2 class="font-semibold text-lg tracking-tight">AI Chat · smart replies</h2>
            <p class="text-xs text-teal-50 flex items-center gap-1" id="onlineStatus"><span class="w-2 h-2 bg-green-300 rounded-full"></span> <span id="onlineCount">3</span> online</p>
          </div>
        </div>
        <div class="flex gap-2 text-white/80">
          <i class="fa-solid fa-phone text-sm bg-white/20 p-2 rounded-full cursor-pointer hover:bg-white/30 transition"></i>
          <i class="fa-solid fa-video text-sm bg-white/20 p-2 rounded-full cursor-pointer hover:bg-white/30 transition"></i>
          <i class="fa-solid fa-ellipsis-vertical text-sm bg-white/20 p-2 rounded-full cursor-pointer hover:bg-white/30 transition"></i>
        </div>
      </div>

      <!-- messages container -->
      <div id="chatMessages" class="flex-1 overflow-y-auto p-5 space-y-3 msg-scroll bg-[#e5ded8] bg-opacity-40" style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,240,0.3) 0%, transparent 30%);">
        <!-- messages appear via JS -->
      </div>

      <!-- AI suggestion strip -->
      <div id="suggestionStrip" class="bg-white border-t border-gray-200 px-4 py-3 flex items-center gap-2 overflow-x-auto whitespace-nowrap">
        <!-- chips injected -->
      </div>

      <!-- compose area -->
      <div class="bg-white border-t border-gray-200 px-4 py-3 flex items-center gap-2">
        <button class="text-teal-500 hover:text-teal-700 transition text-2xl leading-none"><i class="fa-regular fa-face-smile"></i></button>
        <button class="text-teal-500 hover:text-teal-700 transition text-2xl leading-none"><i class="fa-regular fa-paper-plane"></i></button>
        <input type="text" id="messageInput" placeholder="Type a message..." class="flex-1 bg-gray-100 rounded-full px-5 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300 border border-transparent">
        <button id="sendButton" class="bg-teal-500 hover:bg-teal-600 text-white rounded-full p-3 w-12 h-12 flex items-center justify-center shadow-md transition-all active:scale-95">
          <i class="fa-regular fa-paper-plane"></i>
        </button>
      </div>
    </div>
  </div>

  <script>
    (function() {
      // ---------- DOM elements ----------
      const chatContainer = document.getElementById('chatMessages');
      const messageInput = document.getElementById('messageInput');
      const sendButton = document.getElementById('sendButton');
      const suggestionStrip = document.getElementById('suggestionStrip');
      const membersListDiv = document.getElementById('membersList');
      const onlineCountSpan = document.getElementById('onlineCount');
      const inviteEmail = document.getElementById('inviteEmail');
      const inviteBtn = document.getElementById('inviteBtn');
      const inviteFeedback = document.getElementById('inviteFeedback');

      // ---------- Members data (other users) ----------
      let members = [
        { id: 'm1', name: 'Alex Chen', avatar: 'A', online: true, email: 'alex@example.com' },
        { id: 'm2', name: 'Jamie Smith', avatar: 'J', online: true, email: 'jamie@example.com' },
        { id: 'm3', name: 'Taylor Kim', avatar: 'T', online: false, email: 'taylor@example.com' },
        { id: 'm4', name: 'Sam Rivera', avatar: 'S', online: true, email: 'sam@example.com' },
        { id: 'm5', name: 'Casey Lo', avatar: 'C', online: false, email: 'casey@example.com' },
      ];

      // current user (you) is not in this list – but we add a special "You" entry later.

      // ---------- Chat messages (include sender info) ----------
      let messages = [
        { id: '1', senderId: 'ai', senderName: 'AI Assistant', text: 'Hey! I\'m your smart helper. You can invite friends using the left panel.', time: 'now' },
        { id: '2', senderId: 'm1', senderName: 'Alex Chen', text: 'Just joined, hi everyone!', time: 'now' },
        { id: '3', senderId: 'you', senderName: 'You', text: 'Welcome Alex 👋', time: 'now' },
        { id: '4', senderId: 'm2', senderName: 'Jamie Smith', text: 'This AI suggestion feature is cool!', time: 'now' },
      ];

      // Helper to get member name by id (with fallback)
      function getMemberName(senderId) {
        if (senderId === 'ai') return 'AI Assistant';
        if (senderId === 'you') return 'You';
        const member = members.find(m => m.id === senderId);
        return member ? member.name : 'Unknown';
      }

      // Render members list (left sidebar)
      function renderMembers() {
        let html = '';
        // current user at top (always you)
        html += `<div class="flex items-center gap-3 p-2 rounded-xl bg-teal-50 border border-teal-100 shadow-sm">
          <div class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold text-sm">You</div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-800">You <span class="text-xs text-teal-600 ml-1">(online)</span></p>
            <p class="text-xs text-gray-400">online now</p>
          </div>
          <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
        </div>`;

        members.forEach(m => {
          const onlineClass = m.online ? 'bg-green-500' : 'bg-gray-300';
          const statusText = m.online ? 'online' : 'offline';
          html += `<div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-100 transition">
            <div class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center font-bold text-sm">${m.avatar}</div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-800">${m.name}</p>
              <p class="text-xs text-gray-400">${statusText}</p>
            </div>
            <span class="w-2.5 h-2.5 ${onlineClass} rounded-full"></span>
          </div>`;
        });
        membersListDiv.innerHTML = html;
        // update online count
        const onlineMembers = members.filter(m => m.online).length + 1; // + you (always online)
        onlineCountSpan.innerText = onlineMembers;
      }

      // Render chat messages (improved with sender labels)
      function renderMessages() {
        chatContainer.innerHTML = '';
        messages.forEach(msg => {
          const isYou = msg.senderId === 'you';
          const isAi = msg.senderId === 'ai';
          let senderDisplayName = msg.senderName || getMemberName(msg.senderId);
          let avatarLetter = senderDisplayName.charAt(0).toUpperCase();

          if (isYou) {
            // your message on right
            const div = document.createElement('div');
            div.className = 'flex justify-end';
            div.innerHTML = `
              <div class="max-w-[80%] bg-teal-50 rounded-2xl rounded-tr-none px-4 py-2 shadow-sm border border-teal-100">
                <p class="text-xs text-teal-700 flex justify-end items-center gap-1">You · now <i class="fa-regular fa-circle-check text-teal-500"></i></p>
                <p class="text-sm text-gray-800">${escapeHtml(msg.text)}</p>
              </div>
            `;
            chatContainer.appendChild(div);
          } else {
            // left side: ai or other member
            const div = document.createElement('div');
            div.className = 'flex justify-start';
            let extraClass = isAi ? 'bg-white' : 'bg-white/90';
            let nameColor = isAi ? 'text-teal-600' : 'text-indigo-600';
            let icon = isAi ? '<i class="fa-regular fa-circle-check"></i>' : '<i class="fa-regular fa-user"></i>';
            div.innerHTML = `
              <div class="max-w-[80%] ${extraClass} rounded-2xl rounded-tl-none px-4 py-2 shadow-sm border border-gray-100">
                <p class="text-xs ${nameColor} font-medium flex items-center gap-1">${icon} ${escapeHtml(senderDisplayName)}</p>
                <p class="text-sm text-gray-800">${escapeHtml(msg.text)}</p>
              </div>
            `;
            chatContainer.appendChild(div);
          }
        });
        chatContainer.scrollTop = chatContainer.scrollHeight;
      }

      function escapeHtml(unsafe) {
        return unsafe.replace(/[&<>"']/g, function(m) {
          if(m === '&') return '&amp;'; if(m === '<') return '&lt;'; if(m === '>') return '&gt;'; if(m === '"') return '&quot;'; if(m === "'") return '&#039;';
          return m;
        });
      }

      // ---------- AI suggestion engine (based on last user message, but can include context) ----------
      function generateSmartReplies(lastUserMessage) {
        if (!lastUserMessage || lastUserMessage.trim() === '') {
          return ['Hello!', 'How are you?', 'Sounds good 👍'];
        }
        const lower = lastUserMessage.toLowerCase();
        if (lower.includes('hi') || lower.includes('hello') || lower.includes('hey')) return ['Hi there!', 'How can I help?', 'Hey, what\'s up?'];
        if (lower.includes('how are you')) return ['I\'m fine, thanks!', 'Doing well, you?', 'All good 👍'];
        if (lower.includes('thanks')) return ['You\'re welcome!', 'Happy to help 🙂', 'Anytime!'];
        if (lower.includes('bye')) return ['Goodbye!', 'See you later 👋', 'Take care!'];
        if (lower.includes('meeting')) return ['When shall we schedule?', 'I\'m available', 'Let\'s plan it'];
        if (lower.includes('joke')) return ['Why so serious? 😄', 'Knock knock!', 'I love jokes!'];
        if (lower.includes('weather')) return ['Sunny all day ☀️', 'Might rain 🌧️', 'Check forecast'];
        if (lower.includes('coffee')) return ['Coffee sounds good', 'Pizza maybe? 🍕', 'I\'d love some'];
        const words = lower.split(' ').filter(w => w.length > 3);
        if (words.length > 0) {
          const randomWord = words[Math.floor(Math.random() * words.length)];
          return [`Tell me more about ${randomWord}`, 'Interesting!', 'Go on...'];
        }
        return ['Okay', 'I see', 'That\'s cool'];
      }

      // Update suggestion chips based on last USER message (or fallback)
      function updateSuggestions() {
        let lastUserMsg = '';
        for (let i = messages.length - 1; i >= 0; i--) {
          if (messages[i].senderId === 'you') {
            lastUserMsg = messages[i].text;
            break;
          }
        }
        const suggestions = generateSmartReplies(lastUserMsg);
        suggestionStrip.innerHTML = '';
        const labelSpan = document.createElement('span');
        labelSpan.className = 'text-xs font-medium text-teal-600 mr-1 flex items-center gap-1';
        labelSpan.innerHTML = '<i class="fa-regular fa-lightbulb"></i> AI suggest:';
        suggestionStrip.appendChild(labelSpan);

        suggestions.forEach(text => {
          const chip = document.createElement('button');
          chip.className = 'bg-teal-50 hover:bg-teal-100 text-teal-800 text-sm px-4 py-2 rounded-full border border-teal-200 transition whitespace-nowrap shadow-sm flex items-center gap-1 suggest-enter';
          chip.innerHTML = `${escapeHtml(text)} <i class="fa-regular fa-arrow-right text-xs opacity-60"></i>`;
          chip.addEventListener('click', () => { sendMessage(text); });
          suggestionStrip.appendChild(chip);
        });
      }

      // Send a message (from you)
      function sendMessage(text) {
        if (!text || text.trim() === '') return;
        const trimmed = text.trim();
        // push your message
        messages.push({ id: 'msg' + Date.now(), senderId: 'you', senderName: 'You', text: trimmed, time: 'now' });
        renderMessages();
        updateSuggestions();

        // Simulate AI reply after short delay
        simulateTypingAndAIReply(trimmed);
        // Also simulate a random member reply occasionally (makes it lively)
        if (Math.random() > 0.5) simulateMemberReply(trimmed);

        messageInput.value = '';
      }

      // Simulate AI typing & reply
      function simulateTypingAndAIReply(userMsg) {
        const typingId = 'typing-ai';
        const existing = document.getElementById(typingId);
        if (existing) existing.remove();

        const typingDiv = document.createElement('div');
        typingDiv.id = typingId;
        typingDiv.className = 'flex justify-start';
        typingDiv.innerHTML = `
          <div class="bg-white rounded-2xl rounded-tl-none px-5 py-3 shadow-sm border border-gray-100 flex items-center gap-1">
            <span class="w-2 h-2 bg-teal-400 rounded-full typing-dot" style="animation-delay:0s"></span>
            <span class="w-2 h-2 bg-teal-400 rounded-full typing-dot" style="animation-delay:0.2s"></span>
            <span class="w-2 h-2 bg-teal-400 rounded-full typing-dot" style="animation-delay:0.4s"></span>
            <span class="text-xs text-teal-500 ml-1">AI is typing</span>
          </div>
        `;
        chatContainer.appendChild(typingDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;

        setTimeout(() => {
          document.getElementById(typingId)?.remove();
          const suggestions = generateSmartReplies(userMsg);
          let aiReply = suggestions[0];
          if (aiReply.toLowerCase() === userMsg.toLowerCase() && suggestions.length > 1) aiReply = suggestions[1];
          messages.push({ id: 'ai' + Date.now(), senderId: 'ai', senderName: 'AI Assistant', text: aiReply, time: 'now' });
          renderMessages();
          // suggestions remain based on last user message, but we can keep as is.
        }, 1200);
      }

      // Simulate random member reply (to feel like other users)
      function simulateMemberReply(userMsg) {
        const onlineMembers = members.filter(m => m.online === true);
        if (onlineMembers.length === 0) return;
        const randomMember = onlineMembers[Math.floor(Math.random() * onlineMembers.length)];
        const answers = ['Cool!', 'Nice', 'I agree', '👍', 'Exactly', 'Tell me more', 'Haha', 'Interesting'];
        const replyText = answers[Math.floor(Math.random() * answers.length)];
        setTimeout(() => {
          messages.push({ id: 'mem' + Date.now(), senderId: randomMember.id, senderName: randomMember.name, text: replyText, time: 'now' });
          renderMessages();
        }, 2000 + Math.random() * 2000);
      }

      // Invite function
      function sendInvite(email) {
        if (!email || !email.includes('@')) {
          inviteFeedback.innerText = '❌ valid email required';
          return false;
        }
        // add as new member (offline at first)
        const newId = 'inv' + Date.now();
        const nameParts = email.split('@')[0];
        const newName = nameParts.length > 0 ? nameParts.charAt(0).toUpperCase() + nameParts.slice(1, 8) : 'Friend';
        members.push({ id: newId, name: newName, avatar: newName.charAt(0).toUpperCase(), online: false, email: email });
        renderMembers();
        inviteFeedback.innerText = `✅ Invitation sent to ${email}`;
        inviteEmail.value = '';
        // system message about invite
        messages.push({ id: 'sys' + Date.now(), senderId: 'ai', senderName: 'AI Assistant', text: `${newName} has been invited. They'll appear when online.`, time: 'now' });
        renderMessages();
        return true;
      }

      // Event listeners
      sendButton.addEventListener('click', () => sendMessage(messageInput.value));
      messageInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') { e.preventDefault(); sendMessage(messageInput.value); } });
      inviteBtn.addEventListener('click', () => { sendInvite(inviteEmail.value.trim()); });
      inviteEmail.addEventListener('keypress', (e) => { if (e.key === 'Enter') { e.preventDefault(); sendInvite(inviteEmail.value.trim()); } });

      // Toggle online/offline randomly for demo (simulate members coming online)
      setInterval(() => {
        members.forEach(m => { if (Math.random() > 0.7) m.online = !m.online; });
        renderMembers();
      }, 15000);

      // initial render
      renderMembers();
      renderMessages();
      updateSuggestions();
    })();
  </script>
</body>
</html>