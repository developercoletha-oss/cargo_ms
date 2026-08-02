@extends('layouts.app')

@section('title', 'Live Cargo Transit Progress Tracking')

@push('critical-head')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['Inter', 'system-ui', 'sans-serif'],
            },
          },
        },
      }
    </script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
@endpush

@section('content')
<div className="bg-slate-50 min-h-screen py-10 px-4 sm:px-6 lg:px-8">
    <div id="live-transit-tracking-root" data-tracking-number="{{ $trackingNumber ?? '' }}">
        {{-- Fallback SSR layout while client JS initializes --}}
        <div className="max-w-5xl mx-auto bg-white rounded-3xl p-8 shadow-xl border border-slate-100 text-center animate-pulse">
            <div className="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mx-auto mb-4"></div>
            <p className="text-slate-600 font-semibold text-sm">Loading Live Cargo Transit Progress...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script type="text/babel">
  const { useState, useEffect } = React;

  function LiveTransitTracking({ initialTrackingNumber }) {
    const [cargoData, setCargoData] = useState(null);
    const [activeCargoes, setActiveCargoes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedTrackingNumber, setSelectedTrackingNumber] = useState(initialTrackingNumber);

    useEffect(() => {
      const urlParams = new URLSearchParams(window.location.search);
      let trackingFromUrl = urlParams.get('trackingNumber') || urlParams.get('tracking_number');

      if (!trackingFromUrl) {
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        if (pathParts.length > 1 && (pathParts[0] === 'on-track' || pathParts[0] === 'track')) {
          trackingFromUrl = pathParts[1];
        }
      }

      const targetTrackingNumber = trackingFromUrl || initialTrackingNumber;
      if (targetTrackingNumber) {
        setSelectedTrackingNumber(targetTrackingNumber);
      }

      fetchActiveCargoes();
    }, [initialTrackingNumber]);

    useEffect(() => {
      fetchTrackingData(selectedTrackingNumber);
    }, [selectedTrackingNumber]);

    const fetchActiveCargoes = async () => {
      try {
        const res = await fetch('/api/v1/customer/active-cargoes');
        if (res.ok) {
          const json = await res.json();
          if (json.success && Array.isArray(json.activeCargoes)) {
            setActiveCargoes(json.activeCargoes);
            if (!selectedTrackingNumber && json.activeCargoes.length > 0) {
              setSelectedTrackingNumber(json.activeCargoes[0].trackingNumber);
            }
          }
        }
      } catch (e) {
        console.warn('Could not fetch active cargoes:', e);
      }
    };

    const fetchTrackingData = async (trackingNum) => {
      setLoading(true);
      setError(null);
      try {
        const endpoint = trackingNum 
          ? `/api/v1/track/${encodeURIComponent(trackingNum)}`
          : '/api/v1/track';
        
        const response = await fetch(endpoint);
        const data = await response.json();

        if (data.success) {
          setCargoData(data);
        } else {
          setError(data.message || 'Tracking details unavailable.');
        }
      } catch (err) {
        setError('Unable to fetch live tracking progress.');
      } finally {
        setLoading(false);
      }
    };

    if (loading) {
      return (
        <div className="flex flex-col items-center justify-center p-12 bg-white rounded-3xl shadow-xl border border-slate-100 min-h-[380px] max-w-5xl mx-auto">
          <div className="relative flex items-center justify-center">
            <div className="w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
            <div className="absolute w-8 h-8 bg-indigo-600 rounded-full animate-ping opacity-30"></div>
          </div>
          <p className="mt-4 text-slate-600 font-semibold text-sm animate-pulse">
            Auto-detecting & Loading Live Transit Progress...
          </p>
        </div>
      );
    }

    if (error || !cargoData) {
      return (
        <div className="p-8 bg-red-50/80 border border-red-200 rounded-3xl text-center max-w-xl mx-auto shadow-sm">
          <div className="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 className="text-lg font-bold text-red-800 mb-1">Live Tracking Unavailable</h3>
          <p className="text-sm text-red-600 mb-4">{error || 'No active cargo found.'}</p>
          <button
            onClick={() => fetchTrackingData(selectedTrackingNumber)}
            className="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition shadow-md"
          >
            Retry Load
          </button>
        </div>
      );
    }

    const { trackingNumber, status, cargo, route, checkpoints } = cargoData;
    const progressPct = route?.progressPercentage ?? 0;

    const formatEta = (isoString) => {
      if (!isoString) return 'Calculating...';
      try {
        const date = new Date(isoString);
        return date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch {
        return isoString;
      }
    };

    return (
      <div className="w-full max-w-5xl mx-auto space-y-6 font-sans text-slate-800">
        {/* Multiple Shipments Support Tabs */}
        {activeCargoes.length > 1 && (
          <div className="bg-slate-900/90 backdrop-blur p-2.5 rounded-2xl shadow-lg border border-slate-800 flex items-center gap-3 overflow-x-auto">
            <span className="text-xs font-bold uppercase tracking-wider text-slate-400 px-3 flex items-center gap-1.5 shrink-0">
              <svg className="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
              Your Active Cargoes:
            </span>
            <div className="flex gap-2">
              {activeCargoes.map((item) => {
                const isSelected = item.trackingNumber === trackingNumber;
                return (
                  <button
                    key={item.trackingNumber}
                    onClick={() => setSelectedTrackingNumber(item.trackingNumber)}
                    className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 flex items-center gap-2 whitespace-nowrap ${
                      isSelected
                        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40 ring-2 ring-indigo-400'
                        : 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                    }`}
                  >
                    <span className={`w-2 h-2 rounded-full ${isSelected ? 'bg-emerald-400 animate-ping' : 'bg-slate-500'}`}></span>
                    {item.trackingNumber}
                  </button>
                );
              })}
            </div>
          </div>
        )}

        {/* Header Summary Card */}
        <div className="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 md:p-8 shadow-2xl border border-indigo-900/40 relative overflow-hidden">
          <div className="absolute -top-24 -right-24 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
          <div className="absolute -bottom-24 -left-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

          <div className="relative z-10 space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-4 border-b border-indigo-800/40 pb-5">
              <div>
                <span className="text-xs uppercase font-semibold text-indigo-300 tracking-wider">Automated Live Transit Progress</span>
                <h2 className="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                  {trackingNumber}
                </h2>
              </div>
              
              <div className="flex items-center gap-2 bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 px-4 py-2 rounded-full shadow-lg shadow-emerald-950/50">
                <span className="relative flex h-3 w-3">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span className="text-xs font-bold uppercase tracking-wider">{status || 'ON_TRANSIT'}</span>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              {/* Cargo Info */}
              <div className="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur">
                <div className="text-xs text-indigo-200 font-medium mb-1">Cargo Details</div>
                <div className="text-sm font-bold text-white truncate">{cargo?.description || 'N/A'}</div>
                <div className="text-xs text-slate-300 mt-1">Weight: <span className="font-semibold text-white">{cargo?.weight || 'N/A'}</span></div>
                <div className="text-[11px] text-slate-400 mt-0.5 truncate">Sender: {cargo?.sender}</div>
              </div>

              {/* Carrier Vehicle */}
              <div className="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur">
                <div className="text-xs text-indigo-200 font-medium mb-1">Carrier Vehicle</div>
                <div className="text-sm font-bold text-white flex items-center gap-1.5 truncate">
                  <svg className="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                  </svg>
                  {route?.currentVehicle || 'Scania Truck - T 382 EDX'}
                </div>
                <div className="text-xs text-slate-300 mt-1">Assigned Transporter</div>
              </div>

              {/* Driver Name */}
              <div className="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur">
                <div className="text-xs text-indigo-200 font-medium mb-1">Driver Name</div>
                <div className="text-sm font-bold text-white flex items-center gap-1.5 truncate">
                  <svg className="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {route?.driverName || 'Juma Hassan'}
                </div>
                <div className="text-xs text-slate-300 mt-1">Verified Driver Staff</div>
              </div>

              {/* Estimated Arrival (ETA) */}
              <div className="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur">
                <div className="text-xs text-indigo-200 font-medium mb-1">Estimated Arrival (ETA)</div>
                <div className="text-sm font-bold text-emerald-400 flex items-center gap-1.5">
                  <svg className="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {formatEta(route?.estimatedDelivery)}
                </div>
                <div className="text-xs text-slate-300 mt-1 truncate">Destination: {route?.destination}</div>
              </div>
            </div>

            {/* GPS & Distance Analytics Row */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
              <div className="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur">
                <div className="text-xs text-indigo-200 font-medium mb-1">Current Geolocation</div>
                <div className="text-sm font-bold text-white truncate">{route?.currentLocationName || 'Dar es Salaam'}</div>
                <div className="text-xs text-slate-300 mt-1">Live reverse-geocoded region</div>
              </div>
              <div className="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur">
                <div className="text-xs text-indigo-200 font-medium mb-1">Distance Covered</div>
                <div className="text-sm font-bold text-indigo-300">{route?.distanceCoveredKm !== undefined ? `${route.distanceCoveredKm} km` : '0.00 km'}</div>
                <div className="text-xs text-slate-300 mt-1">Distance remaining: <span className="font-semibold text-white">{route?.distanceRemainingKm !== undefined ? `${route.distanceRemainingKm} km` : '0.00 km'}</span></div>
              </div>
              <div className="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur">
                <div className="text-xs text-indigo-200 font-medium mb-1">Calculated Transit Time</div>
                <div className="text-sm font-bold text-amber-400">{route?.etaFormatted || 'Calculating...'}</div>
                <div className="text-xs text-slate-300 mt-1">Dynamic time based on speed</div>
              </div>
            </div>
          </div>
        </div>

        {/* Visual Route Progress Timeline Card */}
        <div className="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-100 space-y-8">
          <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
              <h3 className="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <svg className="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Step-by-Step Route Progress Map
              </h3>
              <p className="text-xs text-slate-500 mt-0.5">
                Route: <span className="font-semibold text-slate-700">{route?.origin}</span> → <span className="font-semibold text-slate-700">{route?.destination}</span>
              </p>
            </div>

            <div className="flex items-center gap-3">
              <span className="text-xs font-bold text-slate-600 uppercase tracking-wider">Progress Rate:</span>
              <div className="flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 px-3.5 py-1.5 rounded-full">
                <span className="text-base font-extrabold text-indigo-700">{progressPct}%</span>
              </div>
            </div>
          </div>

          {/* Dynamic Progress Bar Line */}
          <div className="relative pt-2">
            <div className="w-full bg-slate-100 h-3.5 rounded-full overflow-hidden relative shadow-inner">
              <div
                className="bg-gradient-to-r from-indigo-600 via-indigo-500 to-emerald-500 h-full rounded-full transition-all duration-700 ease-out relative"
                style={{ width: `${progressPct}%` }}
              >
                <div className="absolute top-0 right-0 bottom-0 w-12 bg-white/30 animate-pulse"></div>
              </div>
            </div>
          </div>

          {/* Desktop/Tablet Horizontal Timeline */}
          <div className="hidden md:flex items-start justify-between relative pt-2">
            <div className="absolute top-8 left-8 right-8 h-1 bg-slate-200 -z-0"></div>

            {checkpoints?.map((cp, idx) => {
              const isCompleted = cp.status === 'COMPLETED';
              const isActive = cp.status === 'ACTIVE_CURRENT';

              return (
                <div key={cp.id || idx} className="relative z-10 flex flex-col items-center text-center max-w-[150px] px-1 group">
                  <div
                    className={`w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 ${
                      isCompleted
                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 ring-4 ring-indigo-100'
                        : isActive
                        ? 'bg-emerald-500 text-white shadow-xl shadow-emerald-500/50 ring-4 ring-emerald-300 animate-bounce'
                        : 'bg-slate-100 text-slate-400 border-2 border-slate-200'
                    }`}
                  >
                    {isCompleted ? (
                      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                      </svg>
                    ) : isActive ? (
                      <svg className="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                    ) : (
                      <span className="text-sm font-bold">{idx + 1}</span>
                    )}
                  </div>

                  {isActive && (
                    <span className="mt-2 px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase rounded-full tracking-wider border border-emerald-300 shadow-sm animate-pulse">
                      Current Vehicle Location
                    </span>
                  )}

                  <h4 className={`mt-2 text-xs font-extrabold ${isActive ? 'text-emerald-700 text-sm' : isCompleted ? 'text-slate-800' : 'text-slate-400'}`}>
                    {cp.name}
                  </h4>

                  {cp.timestamp ? (
                    <span className="text-[11px] text-slate-500 mt-1 font-medium bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100">
                      {cp.timestamp}
                    </span>
                  ) : (
                    <span className="text-[11px] text-slate-400 italic mt-1">Pending</span>
                  )}
                </div>
              );
            })}
          </div>

          {/* Mobile Vertical Timeline */}
          <div className="md:hidden space-y-6 relative pl-6 border-l-2 border-slate-200 ml-4 pt-2">
            {checkpoints?.map((cp, idx) => {
              const isCompleted = cp.status === 'COMPLETED';
              const isActive = cp.status === 'ACTIVE_CURRENT';

              return (
                <div key={cp.id || idx} className="relative flex items-start gap-4">
                  <div
                    className={`absolute -left-[35px] w-9 h-9 rounded-full flex items-center justify-center transition-all duration-300 ${
                      isCompleted
                        ? 'bg-indigo-600 text-white shadow-md'
                        : isActive
                        ? 'bg-emerald-500 text-white shadow-lg ring-4 ring-emerald-300 animate-pulse'
                        : 'bg-slate-100 text-slate-400 border-2 border-slate-200'
                    }`}
                  >
                    {isCompleted ? (
                      <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                      </svg>
                    ) : isActive ? (
                      <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                    ) : (
                      <span className="text-xs font-bold">{idx + 1}</span>
                    )}
                  </div>

                  <div className="bg-slate-50 border border-slate-100 rounded-2xl p-4 w-full">
                    <div className="flex items-center justify-between gap-2">
                      <h4 className={`text-sm font-extrabold ${isActive ? 'text-emerald-700' : isCompleted ? 'text-slate-800' : 'text-slate-400'}`}>
                        {cp.name}
                      </h4>
                      {isActive && (
                        <span className="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase rounded-full border border-emerald-300">
                          Active Vehicle Node
                        </span>
                      )}
                    </div>
                    <div className="text-xs text-slate-500 mt-1">
                      {cp.timestamp ? (
                        <span>Logged: <strong className="text-slate-700">{cp.timestamp}</strong></span>
                      ) : (
                        <span className="italic text-slate-400">Scheduled Checkpoint</span>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    );
  }

  const rootEl = document.getElementById('live-transit-tracking-root');
  if (rootEl) {
    const initialTracking = rootEl.getAttribute('data-tracking-number') || null;
    const root = ReactDOM.createRoot(rootEl);
    root.render(<LiveTransitTracking initialTrackingNumber={initialTracking} />);
  }
</script>
@endverbatim
@endpush
