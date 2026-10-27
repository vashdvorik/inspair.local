/* Lightbox for photo galleries: any <a href="full.webp"> inside [data-gallery] opens in a viewer (keys, swipe, preloading). */
(function () {
  var box = document.createElement('div');
  box.className = 'lightbox'; box.setAttribute('role', 'dialog'); box.setAttribute('aria-modal', 'true');
  box.innerHTML = '<button class="lb-close" aria-label="Close">&times;</button><button class="lb-prev" aria-label="Previous">&#8249;</button>' +
                  '<img alt=""><button class="lb-next" aria-label="Next">&#8250;</button><div class="lb-count"></div>';
  document.body.appendChild(box);
  var img = box.querySelector('img'), count = box.querySelector('.lb-count');
  var list = [], cur = 0, touchX = null;

  function show(i) {
    cur = (i + list.length) % list.length;
    img.src = list[cur].href;
    count.textContent = (cur + 1) + ' / ' + list.length;
    [cur + 1, cur - 1].forEach(function (k) { var p = new Image(); p.src = list[(k + list.length) % list.length].href; });
  }
  function open(group, a) {
    list = Array.prototype.slice.call(group.querySelectorAll('a[href]')).filter(function (x) { return x.offsetParent !== null; });
    show(Math.max(0, list.indexOf(a)));
    box.classList.add('is-open'); document.body.style.overflow = 'hidden';
  }
  function close() { box.classList.remove('is-open'); document.body.style.overflow = ''; img.removeAttribute('src'); }

  document.addEventListener('click', function (e) {
    var a = e.target.closest('[data-gallery] a[href]');
    if (!a) return;
    e.preventDefault();
    open(a.closest('[data-gallery]'), a);
  });
  box.addEventListener('click', function (e) {
    if (e.target === box || e.target.classList.contains('lb-close')) close();
    else if (e.target.classList.contains('lb-prev')) show(cur - 1);
    else if (e.target.classList.contains('lb-next')) show(cur + 1);
  });
  document.addEventListener('keydown', function (e) {
    if (!box.classList.contains('is-open')) return;
    if (e.key === 'Escape') close(); else if (e.key === 'ArrowLeft') show(cur - 1); else if (e.key === 'ArrowRight') show(cur + 1);
  });
  box.addEventListener('touchstart', function (e) { touchX = e.touches[0].clientX; }, { passive: true });
  box.addEventListener('touchend', function (e) {
    if (touchX === null) return;
    var dx = e.changedTouches[0].clientX - touchX; touchX = null;
    if (Math.abs(dx) > 50) show(cur + (dx < 0 ? 1 : -1));
  });
})();
