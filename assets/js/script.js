document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('[data-nav-toggle]').forEach(b=>b.addEventListener('click',()=>document.body.classList.toggle('nav-open')));document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm||'Are you sure?'))e.preventDefault()}));});


// Reusable instant search/filter controls. No submit or Enter key is required.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-live-list]').forEach(list => {
    const scope = list.closest('[data-live-scope]') || document;
    const search = scope.querySelector('[data-live-search]');
    const filters = [...scope.querySelectorAll('[data-live-filter]')];
    const items = [...list.querySelectorAll('[data-live-item]')];
    const empty = scope.querySelector('[data-live-empty]');

    const apply = () => {
      const q = (search?.value || '').trim().toLocaleLowerCase();
      let shown = 0;
      items.forEach(item => {
        const haystack = (item.dataset.search || item.textContent || '').toLocaleLowerCase();
        const searchOK = !q || haystack.includes(q);
        const filtersOK = filters.every(control => {
          const wanted = (control.value || '').toLocaleLowerCase();
          if (!wanted) return true;
          const field = control.dataset.liveFilter;
          const actual = (item.dataset[field] || '').toLocaleLowerCase();
          return actual === wanted;
        });
        const visible = searchOK && filtersOK;
        item.hidden = !visible;
        if (visible) shown++;
      });
      if (empty) empty.hidden = shown !== 0;
      const count = scope.querySelector('[data-live-count]');
      if (count) count.textContent = `${shown} result${shown === 1 ? '' : 's'}`;
    };
    search?.addEventListener('input', apply);
    filters.forEach(f => f.addEventListener('change', apply));
    scope.querySelector('[data-live-clear]')?.addEventListener('click', () => {
      if (search) search.value = '';
      filters.forEach(f => f.value = '');
      apply();
      search?.focus();
    });
    apply();
  });
});

// Mobile parishioner/admin drawer: close after navigation, outside tap, or Escape.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.sidebar .nav a').forEach(link => link.addEventListener('click', () => document.body.classList.remove('nav-open')));
  document.addEventListener('click', (event) => {
    if (!document.body.classList.contains('nav-open')) return;
    const sidebar = document.querySelector('.sidebar');
    const toggle = event.target.closest('[data-nav-toggle]');
    if (sidebar && !sidebar.contains(event.target) && !toggle) document.body.classList.remove('nav-open');
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') document.body.classList.remove('nav-open');
  });
});


// Booking success confirmation overlay.
document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.querySelector('[data-submission-success]');
  if (!overlay) return;
  const confirmSuccess = () => {
    const destination = overlay.dataset.successRedirect || 'my-appointments.php';
    overlay.style.opacity = '0';
    overlay.style.transition = 'opacity .18s ease';
    window.setTimeout(() => { window.location.href = destination; }, 180);
  };
  overlay.querySelector('[data-submission-success-close]')?.addEventListener('click', confirmSuccess);
});


// Normalize person-name fields after entry. This is intentionally limited to names;
// emails, addresses, notes, passwords, and reference values are never changed.
document.addEventListener('DOMContentLoaded', () => {
  const nameFieldPattern = /(^|_)(first_name|middle_name|last_name|maiden_last_name)$|^(principal_(male|female)_godparent|additional_godparents\[\])$/;
  const capitalizeWord = word => {
    if (!word) return word;
    const hasLower = /\p{Ll}/u.test(word);
    const hasUpper = /\p{Lu}/u.test(word);
    // Keep deliberate mixed-case names such as McDonald exactly as entered.
    if (hasLower && hasUpper && !/^\p{Lu}\p{Ll}+$/u.test(word)) return word;
    const lower = word.toLocaleLowerCase();
    return lower.charAt(0).toLocaleUpperCase() + lower.slice(1);
  };
  const normalizeName = value => value.trim().replace(/\s+/g, ' ').split(/([\s\-'’]+)/u).map(part => /^[\s\-'’]+$/u.test(part) ? part : capitalizeWord(part)).join('');
  document.addEventListener('blur', event => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || !nameFieldPattern.test(input.name || '')) return;
    input.value = normalizeName(input.value);
  }, true);
});
