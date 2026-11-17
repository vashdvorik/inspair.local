/* INSPIRE design system — tiny behaviour layer (no dependencies).
   - marks the page as JS-enabled (reveal animations only hide content when JS can show it)
   - reveals elements on scroll        : add class "i-reveal" (optional style="--d:.1s")
   - nav shadow after scrolling         : <nav class="i-nav">
   - pauses decorative animation off-screen, and drops it on low-power devices          */
(function () {
  var d = document.documentElement;
  d.classList.add('i-js');
  try {
    var n = navigator, c = n.connection || {};
    var low = (n.hardwareConcurrency || 8) <= 4 || (n.deviceMemory || 8) <= 4 || c.saveData;
    if (low) d.classList.add('i-lite');
  } catch (e) {}

  function ready(fn) { document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn); }
  ready(function () {
    var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

    // reveal on scroll
    var items = [].slice.call(document.querySelectorAll('.i-reveal'));
    if (!('IntersectionObserver' in window) || reduce) { items.forEach(function (el) { el.classList.add('is-visible'); }); }
    else {
      var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
      }, { threshold: .12, rootMargin: '0px 0px -40px 0px' });
      items.forEach(function (el) { io.observe(el); });
    }

    // nav shadow
    var nav = document.querySelector('.i-nav');
    if (nav) {
      var on = function () { nav.classList.toggle('is-scrolled', (window.scrollY || d.scrollTop) > 8); };
      addEventListener('scroll', on, { passive: true }); on();
    }

    // topics strip: all words are always on; every ~2 s one of them swaps for another topic at random
    (function () {
      var row = document.querySelector('#topics .ticker__row'), pool = document.querySelectorAll('#topics .tk-pool > span');
      if (!row || pool.length < 7 || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      var slots = [].slice.call(row.querySelectorAll('.tk-slot')), last = -1, shown = pool.length;
      var wordsOn = function () { return slots.map(function (s) { return +s.getAttribute('data-i'); }); };
      slots.forEach(function (s, i) { s.setAttribute('data-i', i); });
      var swap = function () {
        if (document.hidden || d.classList.contains('i-idle')) return;
        var visible = slots.filter(function (s) { return s.offsetParent !== null; });
        if (!visible.length) return;
        var slot; do { slot = visible[Math.floor(Math.random() * visible.length)]; } while (visible.length > 1 && slots.indexOf(slot) === last);
        last = slots.indexOf(slot);
        var on = wordsOn(), next; do { next = Math.floor(Math.random() * pool.length); } while (on.indexOf(next) !== -1);
        var stage = slot.querySelector('.tk-stage'), old = stage.querySelector('.tk-word:not(.is-out)'), el = document.createElement('span');
        el.className = 'tk-word is-in'; el.innerHTML = pool[next].innerHTML;
        slot.setAttribute('data-i', next); stage.appendChild(el);
        if (old) { old.classList.add('is-out'); setTimeout(function () { old.remove(); }, 800); }
        setTimeout(function () { el.classList.remove('is-in'); }, 760);
      };
      var tick = function () { swap(); setTimeout(tick, 1400 + Math.random() * 1800); };
      setTimeout(tick, 2200);
    })();

    // idle: after 20 s without interaction (or when the tab is hidden) decorative loops are paused
    var idleTimer, setIdle = function (on) { d.classList.toggle('i-idle', on); };
    var wake = function () { setIdle(false); clearTimeout(idleTimer); idleTimer = setTimeout(function () { setIdle(true); }, 20000); };
    ['pointermove', 'pointerdown', 'keydown', 'touchstart', 'wheel'].forEach(function (ev) { addEventListener(ev, wake, { passive: true }); });
    addEventListener('scroll', wake, { passive: true });
    document.addEventListener('visibilitychange', function () { document.hidden ? setIdle(true) : wake(); });
    wake();

    // pause decorative animation when its block is off-screen
    if ('IntersectionObserver' in window) {
      var po = new IntersectionObserver(function (es) { es.forEach(function (e) { e.target.classList.toggle('i-paused', !e.isIntersecting); }); });
      [].slice.call(document.querySelectorAll('.i-airy, .i-nodes, .ticker')).forEach(function (el) { po.observe(el); });
    }
  });
})();
