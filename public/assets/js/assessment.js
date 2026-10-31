(function () {
  const chatLog   = document.getElementById('chat-log');
  const chatForm  = document.getElementById('chat-form');
  const chatInput = document.getElementById('chat-input');
  const sendBtn   = document.getElementById('send-btn');

  let assessmentId = window.AFYA_ASSESSMENT_ID || null;
  const initialConversation = window.AFYA_INITIAL_CONVERSATION || [];
  const dependentId = window.AFYA_DEPENDENT_ID || null;
  const forLabel = window.AFYA_FOR_LABEL || 'Mwenyewe';

  function renderMessage(role, text) {
    const div = document.createElement('div');
    div.className = 'msg ' + (role === 'user' ? 'msg-user' : 'msg-assistant');
    div.textContent = text;
    chatLog.appendChild(div);
    chatLog.scrollTop = chatLog.scrollHeight;
    return div;
  }

  function showTyping() {
    const div = document.createElement('div');
    div.className = 'msg msg-assistant msg-typing';
    div.id = 'typing-indicator';
    div.innerHTML = '<span></span><span></span><span></span>';
    chatLog.appendChild(div);
    chatLog.scrollTop = chatLog.scrollHeight;
  }

  function hideTyping() {
    const el = document.getElementById('typing-indicator');
    if (el) el.remove();
  }

  // Onyesha mazungumzo yaliyopo (kama assessment tayari ipo)
  if (initialConversation.length > 0) {
    initialConversation.forEach((m) => renderMessage(m.role, m.content));
  } else {
    renderMessage('assistant', "Habari! Mimi ni msaidizi wa AfyaCheck. Niambie dalili unazojisikia sasa hivi, na nitakuuliza maswali machache ili nikusaidie vizuri.");
  }

  async function sendMessage(text) {
    renderMessage('user', text);
    chatInput.value = '';
    sendBtn.disabled = true;
    showTyping();

    try {
      const res = await fetch('api/chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          assessment_id: assessmentId,
          message: text,
          dependent_id: dependentId,
          for_label: forLabel,
        }),
      });

      const data = await res.json();
      hideTyping();

      if (res.status === 429) {
        renderMessage('assistant', data.error || 'Umefikia kikomo cha ujumbe kwa leo. Jaribu tena kesho.');
        chatInput.disabled = true;
        sendBtn.disabled = true;
        return;
      }

      if (!res.ok || data.error) {
        renderMessage('assistant', 'Samahani, kuna hitilafu: ' + (data.error || 'Tatizo lisilojulikana.') + '\n\nJaribu tena baada ya muda.');
        return;
      }

      assessmentId = data.assessment_id;
      renderMessage('assistant', data.reply);
      if (data.source === 'local' && !window.__afyaLocalNote) {
        window.__afyaLocalNote = true;
        const note = document.createElement('div');
        note.className = 'muted';
        note.style.cssText = 'text-align:center;font-size:12px;margin:6px 0;';
        note.textContent = 'AI ya mtandaoni haipatikani sasa; unatumia msaidizi wa msingi.';
        chatLog.appendChild(note);
      }

      if (data.completed) {
        chatInput.placeholder = 'Tathmini imekamilika';
        chatInput.disabled = true;
        sendBtn.disabled = true;
        const reportLink = document.createElement('a');
        reportLink.href = 'assessment-report.php?id=' + assessmentId;
        reportLink.className = 'btn-primary btn-block';
        reportLink.style.marginTop = '14px';
        reportLink.textContent = 'Ona/Chapisha Ripoti Kamili';
        chatLog.appendChild(reportLink);
        return;
      }
    } catch (err) {
      hideTyping();
      renderMessage('assistant', 'Samahani, imeshindikana kuunganisha na seva. Angalia mtandao wako na ujaribu tena.');
    } finally {
      sendBtn.disabled = false;
    }
  }

  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = chatInput.value.trim();
    if (!text) return;
    sendMessage(text);
  });
})();
