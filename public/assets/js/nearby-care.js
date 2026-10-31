(function () {
  const statusEl = document.getElementById('facility-status');
  const listEl   = document.getElementById('facility-list');
  const mapEl    = document.getElementById('map');

  const FACILITY_LABELS = {
    hospital: 'Hospitali',
    clinic: 'Zahanati',
    doctors: 'Daktari',
    pharmacy: 'Duka la dawa',
  };

  function distanceKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLon / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  function renderList(facilities, userLat, userLon) {
    listEl.innerHTML = '';
    if (facilities.length === 0) {
      listEl.innerHTML = '<p class="muted">Hakuna vituo vilivyopatikana karibu (ndani ya kilomita 5). Jaribu eneo lingine.</p>';
      return;
    }

    facilities
      .map((f) => ({ ...f, dist: distanceKm(userLat, userLon, f.lat, f.lon) }))
      .sort((a, b) => a.dist - b.dist)
      .slice(0, 20)
      .forEach((f) => {
        const name = f.tags.name || FACILITY_LABELS[f.type] || 'Kituo cha afya';
        const label = FACILITY_LABELS[f.type] || 'Kituo';
        const div = document.createElement('a');
        div.href = `https://www.google.com/maps/dir/?api=1&destination=${f.lat},${f.lon}`;
        div.target = '_blank';
        div.rel = 'noopener';
        div.className = 'list-card';
        div.innerHTML = `
          <div>
            <strong>${name}</strong>
            <div class="muted">${label} &middot; kilomita ${f.dist.toFixed(1)}</div>
          </div>
          <span>&#8594;</span>
        `;
        listEl.appendChild(div);
      });
  }

  async function fetchNearbyFacilities(lat, lon) {
    const radius = 5000; // mita 5000 = km 5
    const query = `
      [out:json][timeout:25];
      (
        node["amenity"="hospital"](around:${radius},${lat},${lon});
        node["amenity"="clinic"](around:${radius},${lat},${lon});
        node["amenity"="doctors"](around:${radius},${lat},${lon});
        node["amenity"="pharmacy"](around:${radius},${lat},${lon});
      );
      out body;
    `;

    const res = await fetch('https://overpass-api.de/api/interpreter', {
      method: 'POST',
      body: query,
    });

    if (!res.ok) throw new Error('Overpass API error: ' + res.status);
    const data = await res.json();

    return (data.elements || []).map((el) => ({
      lat: el.lat,
      lon: el.lon,
      type: el.tags?.amenity || 'clinic',
      tags: el.tags || {},
    }));
  }

  function initMap(lat, lon, facilities) {
    const map = L.map('map').setView([lat, lon], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(map);

    L.marker([lat, lon]).addTo(map).bindPopup('Wewe upo hapa').openPopup();

    facilities.forEach((f) => {
      const name = f.tags.name || FACILITY_LABELS[f.type] || 'Kituo cha afya';
      L.marker([f.lat, f.lon]).addTo(map).bindPopup(name);
    });
  }

  function start() {
    if (!navigator.geolocation) {
      statusEl.textContent = 'Kifaa/browser yako haiwezi kutoa mahali ulipo.';
      mapEl.textContent = 'Ramani haipatikani.';
      return;
    }

    statusEl.textContent = 'Inatafuta mahali ulipo...';

    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const { latitude, longitude } = pos.coords;
        statusEl.textContent = 'Inatafuta vituo vya afya karibu nawe...';

        try {
          const facilities = await fetchNearbyFacilities(latitude, longitude);
          mapEl.innerHTML = '';
          initMap(latitude, longitude, facilities);
          statusEl.textContent = `Vituo vilivyopatikana: ${facilities.length}`;
          renderList(facilities, latitude, longitude);
        } catch (err) {
          statusEl.textContent = 'Imeshindwa kupata taarifa za vituo vya afya. Jaribu tena baadaye.';
        }
      },
      () => {
        statusEl.textContent = 'Umekataa ruhusa ya mahali ulipo, au haipatikani. Wezesha "Location" kwenye mipangilio ya browser yako kisha upakie ukurasa upya.';
        mapEl.textContent = 'Ramani haipatikani bila ruhusa ya mahali.';
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }

  start();
})();
