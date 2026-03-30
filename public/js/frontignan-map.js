(function () {
  const appRoot = document.querySelector('[data-frontignan-app]');
  const configElement = document.getElementById('frontignan-config');

  if (!appRoot || !configElement || typeof window.L === 'undefined') {
    return;
  }

  const config = JSON.parse(configElement.textContent);
  const defaultLatitude = config.defaultLatitude;
  const defaultLongitude = config.defaultLongitude;

  const typeButtons = Array.from(appRoot.querySelectorAll('[data-type-button]'));
  const errorMessage = appRoot.querySelector('[data-error-message]');
  const successToast = appRoot.querySelector('[data-success-toast]');
  const activeName = appRoot.querySelector('[data-active-name]');
  const activeType = appRoot.querySelector('[data-active-type]');
  const activeSummary = appRoot.querySelector('[data-active-summary]');
  const googleMapsLink = appRoot.querySelector('[data-google-maps-link]');
  const showAllPointsButton = appRoot.querySelector('[data-action="show-all-points"]');
  const saveChatButton = appRoot.querySelector('[data-action="save-chat"]');
  const deleteChatSheetButton = appRoot.querySelector('[data-action="delete-chat-sheet"]');
  const saveMaisonButton = appRoot.querySelector('[data-action="save-maison"]');
  const deleteSelectedButton = appRoot.querySelector('[data-action="delete-selected"]');
  const selectedSection = appRoot.querySelector('[data-selected-section]');
  const chatFormSection = appRoot.querySelector('[data-chat-form-section]');
  const chatForm = appRoot.querySelector('[data-chat-form]');
  const maisonForm = appRoot.querySelector('[data-maison-form]');
  const mapElement = document.getElementById('leaflet-map');

  const state = {
    typeMarqueurActif: 'chat',
    lieuActifKey: null,
    lieux: [],
    popupNom: '',
    popupDossierNumero: '',
    popupAdresse: '',
    pendingCoordinates: null,
    leafletMap: null,
    markersLayer: null,
    temporaryMarker: null
  };

  let successToastTimeout = null;

  function createIcon(type, extraClass = '') {
    const baseClass = type === 'maison' ? 'house-marker' : 'cat-marker';
    const html = type === 'maison' ? '<div class="house-pin"></div>' : '<div class="cat-pin"></div>';

    return L.divIcon({
      className: `${baseClass}${extraClass ? ` ${extraClass}` : ''}`,
      html: html,
      iconSize: [42, 42],
      iconAnchor: [21, 34],
      popupAnchor: [0, -24]
    });
  }

  function createEmptyChatForm() {
    return {
      dossierNumero: '',
      trappageDate: '',
      trappageHeure: '',
      adressePrecise: '',
      commune: '',
      typeLieu: '',
      autreTypeLieu: '',
      nomEntrepriseParticulier: '',
      trappageTelephone: '',
      colonieSite: '',
      signalementNom: '',
      signalementTelephone: '',
      signalementEmail: '',
      statutChat: '',
      proprietaireNom: '',
      proprietaireAdresse: '',
      proprietaireTelephone: '',
      chatNourri: '',
      nourrissageType: '',
      nourrisseurNom: '',
      nourrisseurTelephone: '',
      sterilise: '',
      dateSterilisation: '',
      identificationType: '',
      identificationNumero: '',
      veterinaireNom: '',
      clinique: '',
      financementType: '',
      financementAutre: '',
      nomAttribue: '',
      sexe: '',
      ageApprox: '',
      couleurRobe: '',
      typePelage: '',
      couleurYeux: '',
      signesParticuliers: '',
      photo: '',
      photoReference: '',
      etatGeneral: '',
      comportement: '',
      observations: '',
      orientation: '',
      lieuRelachement: '',
      dateRelachement: '',
      etatAvancement: [],
      nomTrappeur: '',
      associationCollectif: ''
    };
  }

  async function requestJson(url, options) {
    const response = await fetch(url, {
      headers: {
        'Content-Type': 'application/json'
      },
      ...options
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data.message || 'Une erreur est survenue.');
    }

    return data;
  }

  function getLieuKey(lieu) {
    return `${lieu.type}:${lieu.id}`;
  }

  function getLieuActif() {
    return state.lieux.find((lieu) => getLieuKey(lieu) === state.lieuActifKey) || state.lieux[0] || null;
  }

  function getTypeLabel(type) {
    return type === 'maison' ? 'Maison' : 'Chat';
  }

  function getLieuTitle(lieu) {
    if (!lieu) {
      return '';
    }

    if (lieu.type === 'chat') {
      return lieu.details?.dossierNumero || lieu.label || 'Dossier chat';
    }

    return lieu.nom || lieu.label || 'Maison';
  }

  function getLieuSummary(lieu) {
    if (!lieu) {
      return '';
    }

    if (lieu.type === 'chat') {
      return lieu.details?.adressePrecise || 'Aucune adresse';
    }

    return lieu.details?.adresse || 'Aucune adresse';
  }

  function getGoogleMapsUrl() {
    const lieu = getLieuActif();
    const latitude = lieu ? lieu.latitude : defaultLatitude;
    const longitude = lieu ? lieu.longitude : defaultLongitude;

    return `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`;
  }

  function setErrorMessage(message) {
    errorMessage.hidden = !message;
    errorMessage.textContent = message;
  }

  function showSuccessPopup(message) {
    if (!successToast) {
      return;
    }

    if (successToastTimeout) {
      window.clearTimeout(successToastTimeout);
    }

    successToast.textContent = message;
    successToast.hidden = false;
    successToast.classList.add('is-visible');

    successToastTimeout = window.setTimeout(() => {
      successToast.classList.remove('is-visible');
      successToast.hidden = true;
    }, 2600);
  }

  function getChatFormData() {
    const formData = new FormData(chatForm);
    const values = createEmptyChatForm();

    for (const [key, value] of formData.entries()) {
      if (key === 'etatAvancement') {
        values.etatAvancement.push(value);
        continue;
      }

      values[key] = value;
    }

    return values;
  }

  function fillChatForm(details) {
    const values = { ...createEmptyChatForm(), ...(details || {}) };

    Array.from(chatForm.elements).forEach((element) => {
      if (!element.name) {
        return;
      }

      if (element.name === 'etatAvancement' && element.type === 'checkbox') {
        element.checked = Array.isArray(values.etatAvancement) && values.etatAvancement.includes(element.value);
        return;
      }

      element.value = values[element.name] || '';
    });
  }

  function fillMaisonForm(lieu) {
    if (!lieu || lieu.type !== 'maison') {
      maisonForm.reset();
      return;
    }

    maisonForm.elements.nom.value = lieu.nom || '';
    maisonForm.elements.adresse.value = lieu.details?.adresse || '';
    maisonForm.elements.commentaire.value = lieu.details?.commentaire || '';
    maisonForm.elements.latitude.value = lieu.latitude ?? '';
    maisonForm.elements.longitude.value = lieu.longitude ?? '';
  }

  function getMaisonFormData() {
    return {
      nom: maisonForm.elements.nom.value.trim(),
      adresse: maisonForm.elements.adresse.value.trim(),
      commentaire: maisonForm.elements.commentaire.value.trim(),
      latitude: Number(maisonForm.elements.latitude.value),
      longitude: Number(maisonForm.elements.longitude.value)
    };
  }

  function clearPopupInput() {
    state.popupNom = '';
    state.popupDossierNumero = '';
    state.popupAdresse = '';
    state.pendingCoordinates = null;
  }

  function removeTemporaryMarker() {
    if (state.temporaryMarker) {
      state.temporaryMarker.remove();
      state.temporaryMarker = null;
    }

    clearPopupInput();
  }

  function centerOnActiveLieu() {
    const lieu = getLieuActif();

    if (!state.leafletMap || !lieu) {
      return;
    }

    state.leafletMap.panTo([lieu.latitude, lieu.longitude], {
      animate: true,
      duration: 0.35
    });
  }

  function showAllPoints() {
    if (!state.leafletMap || state.lieux.length === 0) {
      return;
    }

    const points = state.lieux.map((lieu) => [lieu.latitude, lieu.longitude]);

    if (state.temporaryMarker && state.pendingCoordinates) {
      points.push([state.pendingCoordinates.latitude, state.pendingCoordinates.longitude]);
    }

    if (points.length === 1) {
      state.leafletMap.setView(points[0], 14);
      return;
    }

    state.leafletMap.fitBounds(points, {
      padding: [40, 40]
    });
  }

  function syncExternalTypeButtons() {
    typeButtons.forEach((button) => {
      button.classList.toggle('active', button.dataset.type === state.typeMarqueurActif);
    });
  }

  function createPopupContent(latitude, longitude) {
    const container = L.DomUtil.create('div', 'map-popup');
    container.style.touchAction = 'auto'; // important pour mobile

    const title = L.DomUtil.create('strong', '', container);
    title.textContent = 'Nouveau point';

    const typeInfo = L.DomUtil.create('div', 'popup-type', container);
    typeInfo.textContent = `Mode actif : ${getTypeLabel(state.typeMarqueurActif)}`;

    const coordinates = L.DomUtil.create('div', 'popup-coordinates', container);
    coordinates.textContent = `${latitude}, ${longitude}`;

    const primaryLabel = L.DomUtil.create('label', 'popup-label', container);

    if (state.typeMarqueurActif === 'chat') {
        primaryLabel.textContent = 'Numero de dossier';
        const dossierInput = L.DomUtil.create('input', '', container);
        dossierInput.type = 'text';
        dossierInput.placeholder = 'Numero de dossier';
        dossierInput.value = state.popupDossierNumero;

        L.DomEvent.on(dossierInput, 'input', (event) => {
            state.popupDossierNumero = event.target.value;
        });
    } else {
        primaryLabel.textContent = 'Nom de la maison';
        const nomInput = L.DomUtil.create('input', '', container);
        nomInput.type = 'text';
        nomInput.placeholder = 'Nom de la maison';
        nomInput.value = state.popupNom;

        L.DomEvent.on(nomInput, 'input', (event) => {
            state.popupNom = event.target.value;
        });
    }

    const adresseLabel = L.DomUtil.create('label', 'popup-label', container);
    adresseLabel.textContent = 'Adresse';

    const adresseInput = L.DomUtil.create('input', '', container);
    adresseInput.type = 'text';
    adresseInput.placeholder = 'Adresse';
    adresseInput.value = state.popupAdresse;

    L.DomEvent.on(adresseInput, 'input', (event) => {
        state.popupAdresse = event.target.value;
    });

    const button = L.DomUtil.create('button', '', container);
    button.type = 'button';
    button.textContent = 'Ajouter ce point';

    // ✅ corrige le blocage mobile
    L.DomEvent.disableClickPropagation(container);
    L.DomEvent.disableScrollPropagation(container);

    L.DomEvent.on(button, 'click', (e) => {
        L.DomEvent.stop(e); // stop propagation mobile
        addLieuFromPopup();

        // 🔥 ferme le popup et libère la carte
        if (state.leafletMap) {
            state.leafletMap.closePopup();
        }
    });

    return container;
  }

  function showTemporaryMarker(latitude, longitude) {
    if (!state.leafletMap) {
      return;
    }

    removeTemporaryMarker();

    state.temporaryMarker = L.marker([latitude, longitude], {
      icon: createIcon(state.typeMarqueurActif, 'is-temporary'),
      opacity: 0.88
    }).addTo(state.leafletMap);

    state.pendingCoordinates = { latitude, longitude };
    state.temporaryMarker.bindPopup(createPopupContent(latitude, longitude), {
      closeButton: true,
      maxWidth: 460
    }).openPopup();
  }

  async function refreshLieux() {
    const data = await requestJson('/api/lieux', {
      method: 'GET'
    });

    state.lieux = Array.isArray(data.lieux) ? data.lieux : [];

    if (!state.lieux.some((lieu) => getLieuKey(lieu) === state.lieuActifKey)) {
      state.lieuActifKey = state.lieux[0] ? getLieuKey(state.lieux[0]) : null;
    }
  }

  async function addLieuFromPopup() {
    if (!state.pendingCoordinates) {
      setErrorMessage('Cliquez d abord sur la carte pour choisir une position.');
      return;
    }

    try {
      setErrorMessage('');
      const adresse = state.popupAdresse.trim();
      const payload = {
        type: state.typeMarqueurActif,
        latitude: state.pendingCoordinates.latitude,
        longitude: state.pendingCoordinates.longitude
      };

      if (state.typeMarqueurActif === 'chat') {
        const dossierNumero = state.popupDossierNumero.trim();
        const emptyChatDetails = createEmptyChatForm();
        payload.nom = dossierNumero ? `Dossier ${dossierNumero}` : '';
        payload.details = {
          ...emptyChatDetails,
          dossierNumero,
          adressePrecise: adresse
        };
      } else {
        payload.nom = state.popupNom.trim() || `Maison ${state.lieux.length + 1}`;
        payload.adresse = adresse;
        payload.commentaire = '';
        payload.details = {};
      }

      const data = await requestJson('/api/lieux', {
        method: 'POST',
        body: JSON.stringify(payload)
      });

      state.lieux.push(data.lieu);
      state.lieuActifKey = getLieuKey(data.lieu);
      removeTemporaryMarker();
      updateUI();
      centerOnActiveLieu();
      setErrorMessage('');
    } catch (error) {
      setErrorMessage(error.message);
    }
  }

  function getDeleteUrl(lieu) {
    return lieu.type === 'chat' ? `/api/lieux/chat/${lieu.id}` : `/api/lieux/maison/${lieu.id}`;
  }

  function getUpdateChatUrl(lieu) {
    return `/api/lieux/chat/${lieu.id}/fiche`;
  }

  function getUpdateMaisonUrl(lieu) {
    return `/api/lieux/maison/${lieu.id}`;
  }

  async function deleteSelectedLieu() {
    const lieu = getLieuActif();

    if (!lieu) {
      return;
    }

    if (lieu.type === 'maison') {
      const confirmed = window.confirm('Voulez-vous vraiment supprimer cette maison ?');

      if (!confirmed) {
        return;
      }
    }

    try {
      await requestJson(getDeleteUrl(lieu), {
        method: 'DELETE'
      });

      state.lieux = state.lieux.filter((item) => getLieuKey(item) !== getLieuKey(lieu));
      state.lieuActifKey = state.lieux[0] ? getLieuKey(state.lieux[0]) : null;
      updateUI();
      setErrorMessage('');
    } catch (error) {
      setErrorMessage(error.message);
    }
  }

  async function deleteChatSheet() {
    const lieu = getLieuActif();

    if (!lieu || lieu.type !== 'chat') {
      return;
    }

    const confirmed = window.confirm('Voulez-vous vraiment supprimer cette fiche chat ?');

    if (!confirmed) {
      return;
    }

    await deleteSelectedLieu();
  }

  async function saveChatSheet() {
    const lieu = getLieuActif();

    if (!lieu || lieu.type !== 'chat') {
      return;
    }

    try {
      const chatDetails = getChatFormData();
      const data = await requestJson(getUpdateChatUrl(lieu), {
        method: 'PUT',
        body: JSON.stringify({
          latitude: lieu.latitude,
          longitude: lieu.longitude,
          details: chatDetails
        })
      });

      state.lieux = state.lieux.map((item) => (getLieuKey(item) === getLieuKey(lieu) ? data.lieu : item));
      state.lieuActifKey = getLieuKey(data.lieu);
      updateUI();
      setErrorMessage('');
      showSuccessPopup('La fiche chat a bien ete enregistree.');
    } catch (error) {
      setErrorMessage(error.message);
    }
  }

  async function saveMaisonSheet() {
    const lieu = getLieuActif();

    if (!lieu || lieu.type !== 'maison') {
      return;
    }

    try {
      const maisonData = getMaisonFormData();
      const data = await requestJson(getUpdateMaisonUrl(lieu), {
        method: 'PUT',
        body: JSON.stringify(maisonData)
      });

      state.lieux = state.lieux.map((item) => (getLieuKey(item) === getLieuKey(lieu) ? data.lieu : item));
      state.lieuActifKey = getLieuKey(data.lieu);
      updateUI();
      setErrorMessage('');
      showSuccessPopup('La fiche maison a bien ete enregistree.');
    } catch (error) {
      setErrorMessage(error.message);
    }
  }

  function updateMarkers() {
    if (!state.markersLayer) {
      return;
    }

    state.markersLayer.clearLayers();

    state.lieux.forEach((lieu) => {
      const isSelected = getLieuKey(lieu) === state.lieuActifKey;
      const marker = L.marker([lieu.latitude, lieu.longitude], {
        icon: createIcon(lieu.type, isSelected ? 'is-selected' : '')
      }).addTo(state.markersLayer);

      marker.on('click', () => {
        state.lieuActifKey = getLieuKey(lieu);
        setErrorMessage('');
        updateUI();
      });
    });
  }

  function updateActiveInfo() {
    const lieu = getLieuActif();

    if (!lieu) {
      activeName.textContent = '';
      activeType.textContent = '';
      activeSummary.textContent = '';
      googleMapsLink.href = getGoogleMapsUrl();
      selectedSection.hidden = true;
      chatFormSection.hidden = true;
      maisonForm.hidden = true;
      deleteSelectedButton.hidden = true;
      return;
    }

    activeName.textContent = getLieuTitle(lieu);
    activeType.textContent = getTypeLabel(lieu.type);
    activeSummary.textContent = getLieuSummary(lieu);
    googleMapsLink.href = getGoogleMapsUrl();
    deleteSelectedButton.hidden = false;

    if (lieu.type === 'chat') {
      selectedSection.hidden = true;
      chatFormSection.hidden = false;
      maisonForm.hidden = true;
      fillChatForm(lieu.details || {});
    } else {
      selectedSection.hidden = false;
      chatFormSection.hidden = true;
      maisonForm.hidden = false;
      fillMaisonForm(lieu);
    }
  }

  function updateUI() {
    updateActiveInfo();
    updateMarkers();
    syncExternalTypeButtons();
  }

  function initMap() {
    state.leafletMap = L.map(mapElement).setView([defaultLatitude, defaultLongitude], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(state.leafletMap);

    state.markersLayer = L.layerGroup().addTo(state.leafletMap);

    state.leafletMap.on('click', (event) => {
      const latitude = Number(event.latlng.lat.toFixed(6));
      const longitude = Number(event.latlng.lng.toFixed(6));

      setErrorMessage('');
      state.popupNom = '';
      state.popupDossierNumero = '';
      state.popupAdresse = '';
      showTemporaryMarker(latitude, longitude);
    });

    state.leafletMap.on('popupclose', () => {
      if (state.temporaryMarker && state.pendingCoordinates) {
        removeTemporaryMarker();
      }
    });
  }

  function bindEvents() {
    typeButtons.forEach((button) => {
      button.addEventListener('click', () => {
        state.typeMarqueurActif = button.dataset.type;
        syncExternalTypeButtons();
      });
    });

    saveChatButton.addEventListener('click', () => {
      saveChatSheet();
    });

    deleteChatSheetButton.addEventListener('click', () => {
      deleteChatSheet();
    });

    saveMaisonButton.addEventListener('click', () => {
      saveMaisonSheet();
    });

    deleteSelectedButton.addEventListener('click', () => {
      deleteSelectedLieu();
    });

    showAllPointsButton.addEventListener('click', () => {
      showAllPoints();
    });
  }

  async function init() {
    try {
      initMap();
      bindEvents();
      await refreshLieux();
      updateUI();
      setErrorMessage('');
    } catch (error) {
      setErrorMessage(error.message || 'La carte interactive n a pas pu etre chargee.');
    }
  }

  init();
})();
