import { useEffect, useRef, useState } from 'react';
import { Loader2, LocateFixed, MapPin, Search, X } from 'lucide-react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { useI18n } from '../context/I18nContext';

// Iraq geographic bounding box (WGS-84) — must match backend CheckoutRequest
export const IRAQ_LAT_MIN = 29.06;
export const IRAQ_LAT_MAX = 37.38;
export const IRAQ_LNG_MIN = 38.79;
export const IRAQ_LNG_MAX = 48.76;

export const isInsideIraq = (lat, lng) =>
  lat >= IRAQ_LAT_MIN && lat <= IRAQ_LAT_MAX &&
  lng >= IRAQ_LNG_MIN && lng <= IRAQ_LNG_MAX;

const BAGHDAD_CENTER = [33.3128, 44.3615];

const pinIcon = L.divIcon({
  html: `<div style="
    width:22px;height:34px;
    background:#ef4444;
    border:2.5px solid #fff;
    border-radius:50% 50% 50% 0;
    transform:rotate(-45deg);
    box-shadow:0 2px 8px rgba(0,0,0,0.5);
  "></div>`,
  className: '',
  iconSize: [22, 34],
  iconAnchor: [11, 34],
});

// ── Inner Leaflet map ─────────────────────────────────────────────────────
const LeafletMap = ({ value, onChange, flyToTarget }) => {
  const containerRef    = useRef(null);
  const mapRef          = useRef(null);
  const markerRef       = useRef(null);
  const onChangeRef     = useRef(onChange);
  const setMarkerPosRef = useRef(null);

  useEffect(() => { onChangeRef.current = onChange; });

  useEffect(() => {
    if (!containerRef.current || mapRef.current) return;

    const map = L.map(containerRef.current, {
      center: value ? [value.lat, value.lng] : BAGHDAD_CENTER,
      zoom: value ? 13 : 6,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      maxZoom: 18,
    }).addTo(map);

    const setMarkerPos = (lat, lng) => {
      if (markerRef.current) {
        markerRef.current.setLatLng([lat, lng]);
      } else {
        const m = L.marker([lat, lng], { icon: pinIcon, draggable: true }).addTo(map);
        m.on('dragend', () => {
          const { lat: mlat, lng: mlng } = m.getLatLng();
          onChangeRef.current({ lat: mlat, lng: mlng });
        });
        markerRef.current = m;
      }
    };

    setMarkerPosRef.current = setMarkerPos;

    if (value) setMarkerPos(value.lat, value.lng);

    map.on('click', (e) => {
      setMarkerPos(e.latlng.lat, e.latlng.lng);
      onChangeRef.current({ lat: e.latlng.lat, lng: e.latlng.lng });
    });

    mapRef.current = map;
    return () => {
      map.remove();
      mapRef.current  = null;
      markerRef.current = null;
    };
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    if (!flyToTarget || !mapRef.current || !setMarkerPosRef.current) return;
    mapRef.current.flyTo([flyToTarget.lat, flyToTarget.lng], 15, {
      animate: true,
      duration: 0.7,
    });
    setMarkerPosRef.current(flyToTarget.lat, flyToTarget.lng);
  }, [flyToTarget]);

  return <div ref={containerRef} style={{ height: '320px', width: '100%' }} />;
};

// ── Nominatim helpers ─────────────────────────────────────────────────────
const NOM_HEADERS = { 'User-Agent': 'KurdistanStoreDemo/1.0' };

const searchNominatim = (q) =>
  fetch(
    `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=5&countrycodes=iq&accept-language=en`,
    { headers: NOM_HEADERS }
  ).then((r) => r.json());

const reverseNominatim = (lat, lng) =>
  fetch(
    `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&accept-language=en`,
    { headers: NOM_HEADERS }
  ).then((r) => r.json());

// ── Outer MapPickerField ───────────────────────────────────────────────────
const MapPickerField = ({ value, onChange, error, flyTo = null }) => {
  const { t } = useI18n();
  const [query,          setQuery]          = useState('');
  const [suggestions,    setSuggestions]    = useState([]);
  const [searchLoading,  setSearchLoading]  = useState(false);
  const [showDropdown,   setShowDropdown]   = useState(false);
  const [flyToTarget,    setFlyToTarget]    = useState(null);
  const [reverseAddress, setReverseAddress] = useState('');
  const [reverseLoading, setReverseLoading] = useState(false);
  const [gpsLoading,     setGpsLoading]     = useState(false);
  const [gpsError,       setGpsError]       = useState('');
  const debounceRef    = useRef(null);
  const lastReverseKey = useRef('');
  const onChangeRef    = useRef(onChange);
  useEffect(() => { onChangeRef.current = onChange; });

  // External flyTo — lets parent (e.g. saved-address selection) move the map
  useEffect(() => {
    if (!flyTo) return;
    setFlyToTarget({ lat: flyTo.lat, lng: flyTo.lng });
  }, [flyTo]);

  const handleGps = () => {
    if (!navigator.geolocation) {
      setGpsError(t('map.gpsUnsupported'));
      return;
    }
    setGpsLoading(true);
    setGpsError('');
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        setGpsLoading(false);
        onChange({ lat, lng });
        setFlyToTarget({ lat, lng });
      },
      (err) => {
        setGpsLoading(false);
        setGpsError(
          err.code === 1
            ? t('map.gpsDenied')
            : t('map.gpsUnavailable')
        );
      },
      { timeout: 8000, enableHighAccuracy: true }
    );
  };

  // Reverse geocode when pin moves
  useEffect(() => {
    if (!value) {
      setReverseAddress('');
      lastReverseKey.current = '';
      return;
    }
    const { lat, lng } = value;
    const key = `${lat.toFixed(5)},${lng.toFixed(5)}`;
    if (key === lastReverseKey.current) return;
    lastReverseKey.current = key;

    setReverseLoading(true);
    reverseNominatim(lat, lng)
      .then((d) => {
        const addr = d.display_name || '';
        setReverseAddress(addr);
        onChangeRef.current({ lat, lng, address: addr });
      })
      .catch(() => setReverseAddress(''))
      .finally(() => setReverseLoading(false));
  }, [value]);

  // Address search — debounced 500 ms
  const handleQueryChange = (e) => {
    const q = e.target.value;
    setQuery(q);
    clearTimeout(debounceRef.current);

    if (q.length < 3) {
      setSuggestions([]);
      setShowDropdown(false);
      return;
    }

    debounceRef.current = setTimeout(() => {
      setSearchLoading(true);
      searchNominatim(q)
        .then((d) => { setSuggestions(d); setShowDropdown(d.length > 0); })
        .catch(() => setSuggestions([]))
        .finally(() => setSearchLoading(false));
    }, 500);
  };

  const handleSelect = (s) => {
    const lat = parseFloat(s.lat);
    const lng = parseFloat(s.lon);
    setQuery(s.display_name);
    setShowDropdown(false);
    setSuggestions([]);
    onChange({ lat, lng });
    setFlyToTarget({ lat, lng });
  };

  const handleClear = () => {
    onChange(null);
    setReverseAddress('');
    lastReverseKey.current = '';
    setQuery('');
    setSuggestions([]);
    setShowDropdown(false);
  };

  const valid     = value !== null && isInsideIraq(value.lat, value.lng);
  const outOfIraq = value !== null && !valid;

  const borderClass = outOfIraq
    ? 'border-red-500'
    : valid
    ? 'border-green-500/60'
    : error
    ? 'border-red-500'
    : 'border-neutral-700';

  return (
    <div className="space-y-3">

      {/* Search bar + GPS button */}
      <div className="flex gap-2">
        <div className="relative flex-1">
          <div className="flex items-center gap-3 rounded-2xl border border-neutral-800 bg-black px-4 py-3 transition-colors focus-within:border-neutral-600">
            {searchLoading
              ? <Loader2 size={17} className="shrink-0 animate-spin text-neutral-500" />
              : <Search   size={17} className="shrink-0 text-neutral-500" />}
            <input
              type="text"
              value={query}
              onChange={handleQueryChange}
              onFocus={() => suggestions.length > 0 && setShowDropdown(true)}
              onBlur={() => setTimeout(() => setShowDropdown(false), 160)}
              placeholder={t('map.searchPlaceholder')}
              className="w-full bg-transparent text-sm text-white outline-none placeholder:text-neutral-600"
            />
            {query && (
              <button
                type="button"
                onClick={() => { setQuery(''); setSuggestions([]); setShowDropdown(false); }}
                className="shrink-0 text-neutral-500 transition-colors hover:text-white"
              >
                <X size={15} />
              </button>
            )}
          </div>

          {/* Suggestions dropdown */}
          {showDropdown && suggestions.length > 0 && (
            <div className="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-950 shadow-2xl">
              {suggestions.map((s, i) => (
                <button
                  key={i}
                  type="button"
                  onMouseDown={() => handleSelect(s)}
                  className="flex w-full items-start gap-3 border-b border-neutral-800/50 px-4 py-3 text-start transition-colors last:border-0 hover:bg-neutral-800/70"
                >
                  <MapPin size={13} className="mt-0.5 shrink-0 text-red-400" />
                  <span className="line-clamp-2 text-sm text-neutral-300">{s.display_name}</span>
                </button>
              ))}
            </div>
          )}
        </div>

        {/* GPS button */}
        <button
          type="button"
          onClick={handleGps}
          disabled={gpsLoading}
          title={t('map.myLocation')}
          className="flex shrink-0 items-center justify-center gap-2 rounded-2xl border border-neutral-800 bg-black px-4 py-3 text-sm text-neutral-400 transition-colors hover:border-neutral-600 hover:text-white disabled:opacity-50"
        >
          {gpsLoading
            ? <Loader2 size={17} className="animate-spin" />
            : <LocateFixed size={17} />}
          <span className="hidden sm:inline">{t('map.myLocation')}</span>
        </button>
      </div>

      {/* GPS error */}
      {gpsError && (
        <div className="flex items-center justify-between rounded-xl border border-neutral-800 bg-neutral-950 px-4 py-2.5">
          <p className="text-xs text-neutral-400">{gpsError}</p>
          <button type="button" onClick={() => setGpsError('')} className="ms-3 shrink-0 text-neutral-600 hover:text-white transition-colors">
            <X size={13} />
          </button>
        </div>
      )}

      {/* Leaflet map */}
      <div className={`overflow-hidden rounded-xl border-2 transition-colors ${borderClass}`}>
        <LeafletMap value={value} onChange={onChange} flyToTarget={flyToTarget} />
      </div>

      {/* Confirmation panel (valid Iraqi location) */}
      {valid && (
        <div className="rounded-2xl border border-green-500/25 bg-green-500/5 px-4 py-4">
          <div className="flex items-start justify-between gap-4">
            <div className="flex min-w-0 items-start gap-3">
              <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-500/15">
                <MapPin size={15} className="text-green-400" />
              </div>
              <div className="min-w-0 space-y-1">
                <p className="text-xs font-semibold uppercase tracking-widest text-green-400">
                  {t('map.confirmed')}
                </p>
                {reverseLoading ? (
                  <div className="flex items-center gap-2 text-sm text-neutral-400">
                    <Loader2 size={13} className="animate-spin" />
                    <span>{t('map.findingAddress')}</span>
                  </div>
                ) : reverseAddress ? (
                  <p className="text-sm leading-snug text-neutral-300">{reverseAddress}</p>
                ) : null}
                <p className="font-mono text-xs text-neutral-500">
                  {value.lat.toFixed(6)}°&nbsp;N,&nbsp;{value.lng.toFixed(6)}°&nbsp;E
                </p>
              </div>
            </div>
            <button
              type="button"
              onClick={handleClear}
              className="shrink-0 rounded-full border border-neutral-700 px-3 py-1.5 text-xs text-neutral-400 transition-colors hover:border-neutral-500 hover:text-white"
            >
              {t('map.change')}
            </button>
          </div>
        </div>
      )}

      {/* Out-of-Iraq error */}
      {outOfIraq && (
        <div className="flex items-center justify-between rounded-2xl border border-red-500/30 bg-red-500/5 px-4 py-3">
          <p className="text-sm text-red-400">
            {t('map.outOfIraq')}
          </p>
          <button
            type="button"
            onClick={handleClear}
            className="ms-4 shrink-0 text-xs text-neutral-400 underline hover:text-white"
          >
            {t('map.reset')}
          </button>
        </div>
      )}

      {/* No pin states */}
      {!value && error && (
        <p className="text-xs text-red-500">{error}</p>
      )}
      {!value && !error && (
        <p className="text-xs text-neutral-500">
          {t('map.hint')}
        </p>
      )}
    </div>
  );
};

export default MapPickerField;
