(function() {
  // Generate or retrieve session ID
  function getSessionId() {
    let sessionId = localStorage.getItem('ujm_session_id');
    if (!sessionId) {
      sessionId = 'ujm_' + Math.random().toString(36).substr(2, 9);
      localStorage.setItem('ujm_session_id', sessionId);
    }
    return sessionId;
  }

  const sessionId = getSessionId();
  const pageUrl = window.location.href;
  const referrer = document.referrer;
  const startTime = Date.now();

  // Send data when user leaves the page
  window.addEventListener('beforeunload', function() {
    const duration = Math.round((Date.now() - startTime) / 1000); // in seconds

    const data = {
      action: 'ujm_track_visit',
      session_id: sessionId,
      page_url: pageUrl,
      referrer: referrer,
      duration: duration,
    };

    navigator.sendBeacon(ujm_ajax.ajax_url, new URLSearchParams(data));
  });
})();
