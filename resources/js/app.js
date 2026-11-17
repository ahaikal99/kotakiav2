// eCard Kotakia — skrip kecil tanpa framework

// Salin teks ke papan klip: <button data-copy="#target"> atau data-copy-text="..."
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-copy], [data-copy-text]');
    if (!btn) return;
    e.preventDefault();
    let text = btn.dataset.copyText;
    if (!text) {
        const el = document.querySelector(btn.dataset.copy);
        text = el ? (el.value ?? el.textContent) : '';
    }
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        ta.remove();
    }
    const original = btn.innerHTML;
    btn.innerHTML = '✓ Disalin';
    setTimeout(() => (btn.innerHTML = original), 1600);
});

// Pengesahan sebelum hantar borang: <form data-confirm="Anda pasti?">
document.addEventListener('submit', (e) => {
    const msg = e.target.dataset.confirm;
    if (msg && !confirm(msg)) e.preventDefault();
});

// Togol sidebar mudah alih
document.addEventListener('click', (e) => {
    const t = e.target.closest('[data-toggle]');
    if (!t) return;
    document.querySelector(t.dataset.toggle)?.classList.toggle('hidden');
});

// Baris berulang (aturcara / hubungi): <div data-repeater> <template> ... __i__ ... </template> <div data-rows></div> <button data-add-row>
document.querySelectorAll('[data-repeater]').forEach((wrap) => {
    const tpl = wrap.querySelector('template');
    const rows = wrap.querySelector('[data-rows]');
    const max = parseInt(wrap.dataset.max || '20', 10);
    let i = rows.children.length + 100;
    wrap.querySelector('[data-add-row]')?.addEventListener('click', () => {
        if (rows.children.length >= max) return;
        rows.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', i++));
    });
    rows.addEventListener('click', (e) => {
        if (e.target.closest('[data-remove-row]')) e.target.closest('[data-row]').remove();
    });
});

// Pratonton nama fail
document.querySelectorAll('input[type=file][data-preview]').forEach((input) => {
    input.addEventListener('change', () => {
        const target = document.querySelector(input.dataset.preview);
        if (!target) return;
        const files = [...input.files];
        target.textContent = files.length ? files.map((f) => f.name).join(', ') : '';
    });
});

// Penjana pautan tetamu peribadi (halaman urus kad)
const guestInput = document.querySelector('[data-guest-name]');
if (guestInput) {
    const template = document.querySelector('#share-template').value;
    const baseMessage = document.querySelector('#share-message');
    const original = baseMessage.value;
    const wa = document.querySelector('[data-wa-share]');
    const update = () => {
        const name = guestInput.value.trim();
        const msg = name
            ? template.replaceAll('?to=__GUEST__', '?to=' + encodeURIComponent(name)).replaceAll('__GUEST__', name)
            : original;
        baseMessage.value = msg;
        if (wa) wa.href = 'https://wa.me/?text=' + encodeURIComponent(msg);
    };
    guestInput.addEventListener('input', update);
}
