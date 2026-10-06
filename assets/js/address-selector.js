(function () {
  const API = 'https://psgc.cloud/api/v2';
  const selectors = document.querySelectorAll('[data-address-selector]');
  if (!selectors.length) return;

  const esc = encodeURIComponent;
  const getItems = async (url) => {
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error('Address service unavailable');
    const data = await res.json();
    return Array.isArray(data) ? data : (data.data || []);
  };
  const setOptions = (select, items, placeholder, selected) => {
    select.innerHTML = `<option value="">${placeholder}</option>`;
    items.sort((a,b) => a.name.localeCompare(b.name)).forEach(item => {
      const o = document.createElement('option');
      o.value = item.code;
      o.textContent = item.name;
      o.dataset.name = item.name;
      if (String(item.code) === String(selected || '')) o.selected = true;
      select.appendChild(o);
    });
    select.disabled = false;
  };
  const setName = (select, hidden) => {
    const o = select.options[select.selectedIndex];
    hidden.value = o && o.value ? o.dataset.name || o.textContent : '';
  };

  selectors.forEach(async root => {
    const province = root.querySelector('[data-province]');
    const city = root.querySelector('[data-city]');
    const barangay = root.querySelector('[data-barangay]');
    const provinceName = root.querySelector('[data-province-name]');
    const cityName = root.querySelector('[data-city-name]');
    const barangayName = root.querySelector('[data-barangay-name]');
    const status = root.querySelector('[data-address-status]');
    const savedProvince = root.dataset.provinceCode || '';
    const savedCity = root.dataset.cityCode || '';
    const savedBarangay = root.dataset.barangayCode || '';

    async function loadCities(selected='') {
      city.disabled = true; barangay.disabled = true;
      city.innerHTML = '<option>Loading cities / municipalities…</option>';
      barangay.innerHTML = '<option value="">Select barangay</option>';
      if (!province.value) { city.innerHTML='<option value="">Select city / municipality</option>'; return; }
      const items = await getItems(`${API}/provinces/${esc(province.value)}/cities-municipalities`);
      setOptions(city, items, 'Select city / municipality', selected);
      setName(city, cityName);
    }
    async function loadBarangays(selected='') {
      barangay.disabled = true;
      barangay.innerHTML = '<option>Loading barangays…</option>';
      if (!city.value) { barangay.innerHTML='<option value="">Select barangay</option>'; return; }
      const items = await getItems(`${API}/cities-municipalities/${esc(city.value)}/barangays`);
      setOptions(barangay, items, 'Select barangay', selected);
      setName(barangay, barangayName);
    }

    province.addEventListener('change', async () => {
      setName(province, provinceName); cityName.value=''; barangayName.value='';
      try { status.textContent=''; await loadCities(); }
      catch(e) { status.textContent='Could not load cities. Check your internet connection and try again.'; }
    });
    city.addEventListener('change', async () => {
      setName(city, cityName); barangayName.value='';
      try { status.textContent=''; await loadBarangays(); }
      catch(e) { status.textContent='Could not load barangays. Check your internet connection and try again.'; }
    });
    barangay.addEventListener('change', () => setName(barangay, barangayName));

    try {
      status.textContent='Loading Philippine address list…';
      const provinces = await getItems(`${API}/provinces`);
      setOptions(province, provinces, 'Select province', savedProvince);
      setName(province, provinceName);
      if (savedProvince) { await loadCities(savedCity); if (savedCity) await loadBarangays(savedBarangay); }
      status.textContent='';
    } catch(e) {
      status.textContent='Unable to load the Philippine address list. Please check your internet connection and reload the page.';
      province.innerHTML='<option value="">Address list unavailable</option>';
    }
  });
})();
