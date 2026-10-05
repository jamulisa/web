// Jamulisa channel attribution for the website itself.
// When a page is opened with ?ref=<channel> (or ?utm_source=<channel>), the
// source is remembered for the session and appended to every WhatsApp CTA on
// the site, so a web visitor's enquiry arrives tagged with the channel that
// sent them - not just a generic "(dari laman ...)".
//
// Social short links (/fb, /ig, /th) go straight to wa.me and never reach the
// site, so this only matters once a post or ad points at a page like
// https://jamulisa.com/?ref=fb
(function () {
  var NAMES = {
    fb: 'Facebook', ig: 'Instagram', th: 'Threads', status: 'WhatsApp Status',
    tt: 'TikTok', yt: 'YouTube', gbp: 'Google', web: 'Laman Web'
  };
  try {
    var m = location.search.match(/[?&](?:ref|utm_source)=([^&]*)/i);
    if (m && m[1]) {
      sessionStorage.setItem('jamu_src', decodeURIComponent(m[1]));
    }
    var src = sessionStorage.getItem('jamu_src');
    if (!src) return;

    src = src.replace(/[^A-Za-z0-9 _-]/g, '').replace(/\s+/g, ' ').trim().slice(0, 24);
    if (!src) return;
    var label = NAMES[src.toLowerCase()] || src;

    var links = document.querySelectorAll('a[href*="wa.me/60108666700"]');
    for (var i = 0; i < links.length; i++) {
      var u;
      try { u = new URL(links[i].href); } catch (e) { continue; }
      var t = u.searchParams.get('text') || 'Hi Lisa, saya nak order Jamu Lisa!';
      // drop any existing source tag, whichever page wrote it
      t = t.replace(/\s*\(dari [^)]*\)\s*/gi, ' ').trim();
      u.searchParams.set('text', t + ' (dari ' + label + ')');
      links[i].setAttribute('href', u.href);
    }
  } catch (e) { /* never let attribution break the page */ }
})();
