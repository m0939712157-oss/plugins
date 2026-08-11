(function () {
  'use strict';

  var root = document.documentElement;
  var body = document.body;
  var defaultTheme = body ? body.getAttribute('data-bmd-default-theme') || 'dark' : 'dark';
  var storedTheme = null;
  try { storedTheme = window.localStorage.getItem('bmd-theme'); } catch (e) {}
  var theme = storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : defaultTheme;
  root.setAttribute('data-bmd-theme', theme);
  var headerBreakpoint = window.bmdQuickSettings ? Number(bmdQuickSettings.headerBreakpoint || 900) : 900;
  var initialBodyOverflow = document.body.style.overflow || '';

  function toggleHidden(element, force) {
    if (!element) return false;
    var next = typeof force === 'boolean' ? force : element.hasAttribute('hidden');
    if (next) element.removeAttribute('hidden');
    else element.setAttribute('hidden', 'hidden');
    return next;
  }

  function closeMenus() {
    var searchPanel = document.getElementById('bmd-search-panel');
    var mobileMenu = document.getElementById('bmd-mobile-menu');
    if (searchPanel) searchPanel.setAttribute('hidden', 'hidden');
    if (mobileMenu) mobileMenu.setAttribute('hidden', 'hidden');
    var menuButton = document.querySelector('.bmd-menu-toggle');
    if (menuButton) menuButton.setAttribute('aria-expanded', 'false');
    var searchButton = document.querySelector('.bmd-search-toggle');
    if (searchButton) searchButton.setAttribute('aria-expanded', 'false');
  }

  function handleResponsiveLayout() {
    if (window.innerWidth > headerBreakpoint) {
      closeMenus();
    }
  }

  function setHeaderBreakpoint(value) {
    headerBreakpoint = Number(value) || 900;
    handleResponsiveLayout();
  }

  window.addEventListener('resize', handleResponsiveLayout);
  handleResponsiveLayout();

  function initSlider(slider) {
    var track = slider.querySelector('[data-bmd-slider-track]');
    if (!track) return;
    var section = slider.closest('.bmd-featured-section');
    var previous = section ? section.querySelector('[data-bmd-slider-prev]') : null;
    var next = section ? section.querySelector('[data-bmd-slider-next]') : null;
    var pause = section ? section.querySelector('[data-bmd-slider-pause]') : null;
    var progress = slider.querySelector('[data-bmd-slider-progress]');
    var autoplay = !window.matchMedia('(prefers-reduced-motion: reduce)').matches && (!window.bmdQuickSettings || bmdQuickSettings.autoplay !== false);
    var interval = window.bmdQuickSettings ? Number(bmdQuickSettings.interval || 4000) : 4000;
    var timer = null;
    var paused = !autoplay;

    function cardStep() {
      var card = track.querySelector('.bmd-video-card');
      if (!card) return track.clientWidth;
      var gap = parseFloat(window.getComputedStyle(track).columnGap || window.getComputedStyle(track).gap || '0') || 0;
      return card.getBoundingClientRect().width + gap;
    }

    function updateProgress() {
      if (!progress) return;
      var max = Math.max(0, track.scrollWidth - track.clientWidth);
      var percentage = max ? Math.min(100, Math.max(12, ((track.scrollLeft / max) * 88) + 12)) : 100;
      progress.style.width = percentage + '%';
    }

    function go(direction) {
      var step = cardStep();
      var max = Math.max(0, track.scrollWidth - track.clientWidth);
      var target = track.scrollLeft + (direction * step);
      if (direction > 0 && target >= max - 4) target = 0;
      if (direction < 0 && target <= 4) target = max;
      track.scrollTo({ left: target, behavior: 'smooth' });
    }

    function stopTimer() {
      if (timer) window.clearInterval(timer);
      timer = null;
    }

    function startTimer() {
      stopTimer();
      if (!paused && maxScrollable()) timer = window.setInterval(function () { go(1); }, Math.max(1500, interval));
    }

    function maxScrollable() {
      return track.scrollWidth > track.clientWidth + 4;
    }

    if (previous) previous.addEventListener('click', function () { go(-1); startTimer(); });
    if (next) next.addEventListener('click', function () { go(1); startTimer(); });
    if (pause) {
      pause.addEventListener('click', function () {
        paused = !paused;
        pause.textContent = paused ? '▶' : 'Ⅱ';
        pause.setAttribute('aria-label', paused ? 'เริ่มสไลด์' : 'หยุดสไลด์');
        startTimer();
      });
    }
    track.addEventListener('scroll', updateProgress, { passive: true });
    track.addEventListener('mouseenter', stopTimer);
    track.addEventListener('mouseleave', startTimer);
    window.addEventListener('resize', updateProgress);
    updateProgress();
    startTimer();
  }

  document.querySelectorAll('[data-bmd-slider]').forEach(initSlider);

  // Hide browser broken-image UI and reveal the component's built-in artwork.
  document.addEventListener('error', function (event) {
    var image = event.target;
    if (!image || !image.matches || !image.matches('img[data-bmd-fallback-image]')) return;
    var media = image.closest('.bmd-card-poster, .bmd-term-cover');
    if (!media) return;
    image.remove();
    media.classList.add('is-placeholder', 'has-image-error');
  }, true);

  function panelElements() {
    return {
      panel: document.getElementById('bmd-quick-settings'),
      backdrop: document.querySelector('[data-bmd-settings-backdrop]'),
      opener: document.querySelector('[data-bmd-settings-open]'),
      form: document.querySelector('[data-bmd-settings-form]'),
      status: document.querySelector('[data-bmd-settings-status]')
    };
  }

  function setPanelOpen(open) {
    var parts = panelElements();
    if (!parts.panel) return;
    parts.panel.classList.toggle('is-open', open);
    parts.panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (parts.backdrop) {
      if (open) parts.backdrop.removeAttribute('hidden');
      else parts.backdrop.setAttribute('hidden', 'hidden');
    }
    if (parts.opener) parts.opener.setAttribute('aria-expanded', open ? 'true' : 'false');
    body.classList.toggle('bmd-settings-open', open);
  }

  function selectedValue(form, name, fallback) {
    var input = form.querySelector('input[name="' + name + '"]:checked');
    return input ? input.value : fallback;
  }

  function checkboxValue(form, name) {
    var input = form.querySelector('input[name="' + name + '"]');
    return !!(input && input.checked);
  }

  function numberValue(form, name, fallback) {
    var input = form.querySelector('input[name="' + name + '"]');
    return input ? input.value : fallback;
  }

  function textValue(form, name, fallback) {
    var input = form.querySelector('input[name="' + name + '"]');
    return input ? input.value : fallback;
  }

  function switchClass(prefix, value) {
    Array.prototype.slice.call(body.classList).forEach(function (className) {
      if (className.indexOf(prefix) === 0) body.classList.remove(className);
    });
    body.classList.add(prefix + value);
  }

  function applyRandomLatestOrientationPreview(form) {
    var cards = document.querySelectorAll('.bmd-random-latest-section .bmd-video-card');
    if (!cards.length) return;
    var enabled = checkboxValue(form, 'random_latest_natural');
    var orientation = enabled ? 'natural' : (body.classList.contains('bmd-cards-portrait') ? 'vertical' : 'horizontal');
    Array.prototype.slice.call(cards).forEach(function (card) {
      card.setAttribute('data-bmd-card-orientation', orientation);
    });
  }

  function applyQuickPreview(form) {
    if (!form) return;
    switchClass('bmd-preset-', selectedValue(form, 'color_preset', 'neon'));
    switchClass('bmd-cards-', selectedValue(form, 'card_ratio', 'landscape'));
    switchClass('bmd-density-', selectedValue(form, 'density', 'compact'));

    function toggleHeaderElement(selector, visible) {
      var element = document.querySelector(selector);
      if (element) {
        element.hidden = !visible;
      }
    }

    function setHeaderSticky(enabled) {
      var header = document.getElementById('bmd-header');
      if (header) {
        header.dataset.bmdSticky = enabled ? 'true' : 'false';
      }
    }

    function setHeaderHeight(value) {
      var headerInner = document.querySelector('.bmd-header-inner');
      if (headerInner) {
        headerInner.style.setProperty('--bmd-header-height', (Number(value) || 76) + 'px');
      }
    }

    function setHeaderBreakpointValue(value) {
      setHeaderBreakpoint(value);
    }

    function setTopNotice(visible) {
      var notice = document.querySelector('.bmd-top-notice');
      if (notice) {
        notice.hidden = !visible;
      }
    }

    toggleHeaderElement('.bmd-theme-toggle', checkboxValue(form, 'show_header_theme_toggle'));
    toggleHeaderElement('.bmd-search-toggle', checkboxValue(form, 'show_header_search'));
    toggleHeaderElement('.bmd-language-toggle', checkboxValue(form, 'show_header_language'));
    setHeaderSticky(checkboxValue(form, 'header_sticky'));
    setTopNotice(checkboxValue(form, 'show_top_notice'));
    setHeaderHeight(numberValue(form, 'header_height', '76'));
    setHeaderBreakpointValue(numberValue(form, 'header_breakpoint', headerBreakpoint));
    applyRandomLatestOrientationPreview(form);

    // Random Latest preview vars
    var randSection = document.querySelector('[data-bmd-home-block="random_latest"], .bmd-random-latest-section');
    if (randSection) {
      randSection.style.setProperty('--bmd-random-latest-cols-desktop', numberValue(form, 'random_latest_columns_desktop', 5));
      randSection.style.setProperty('--bmd-random-latest-cols-tablet', numberValue(form, 'random_latest_columns_tablet', 4));
      randSection.style.setProperty('--bmd-random-latest-cols-mobile', numberValue(form, 'random_latest_columns_mobile', 3));
      randSection.style.setProperty('--bmd-random-latest-gap', (numberValue(form, 'random_latest_gap', 16) || 16) + 'px');
      randSection.style.setProperty('--bmd-random-latest-rows-desktop', numberValue(form, 'random_latest_rows_desktop', 2));
      randSection.style.setProperty('--bmd-random-latest-rows-tablet', numberValue(form, 'random_latest_rows_tablet', 2));
      randSection.style.setProperty('--bmd-random-latest-rows-mobile', numberValue(form, 'random_latest_rows_mobile', 2));
      var heading = randSection.querySelector('.bmd-section-heading h2');
      if (heading) heading.textContent = textValue(form, 'random_latest_title', heading.textContent || '');
      var viewAll = randSection.querySelector('.bmd-section-heading a');
      if (viewAll) viewAll.textContent = textValue(form, 'random_latest_view_all', viewAll.textContent || '');
    }

    // Explore preview vars
    var exploreSection = document.querySelector('[data-bmd-home-block="directories"], .bmd-explore-section');
    if (exploreSection) {
      exploreSection.style.setProperty('--bmd-explore-cols-desktop', numberValue(form, 'explore_columns_desktop', 3));
      exploreSection.style.setProperty('--bmd-explore-cols-tablet', numberValue(form, 'explore_columns_tablet', 2));
      exploreSection.style.setProperty('--bmd-explore-cols-mobile', numberValue(form, 'explore_columns_mobile', 1));
      exploreSection.style.setProperty('--bmd-explore-gap', (numberValue(form, 'explore_gap', 12) || 12) + 'px');
    }

    // Single gallery preview vars (if present on page)
    var singleGallery = document.querySelector('[data-bmd-single-gallery]');
    if (singleGallery) {
      var wrapper = singleGallery.closest('.bmd-gallery-section') || singleGallery;
      wrapper.style.setProperty('--bmd-single-gallery-cols', numberValue(form, 'single_gallery_columns', 4));
      wrapper.style.setProperty('--bmd-single-gallery-cols-tablet', numberValue(form, 'single_gallery_columns_tablet', 3));
      wrapper.style.setProperty('--bmd-single-gallery-cols-mobile', numberValue(form, 'single_gallery_columns_mobile', 2));
      wrapper.style.setProperty('--bmd-single-gallery-gap', (numberValue(form, 'single_gallery_gap', 10) || 10) + 'px');
      wrapper.style.setProperty('--bmd-single-gallery-rows', numberValue(form, 'single_gallery_rows', 2));
      var ratio = selectedValue(form, 'single_gallery_ratio', 'landscape');
      wrapper.style.setProperty('--bmd-single-gallery-ratio', ratio === 'portrait' ? '2/3' : '16/9');
      wrapper.hidden = !checkboxValue(form, 'single_show_gallery');
    }

    var mapping = {
      show_hero: 'hero',
      show_featured: 'featured',
      show_latest: 'latest',
      show_random_latest: 'random_latest',
      show_directories: 'directories'
    };
    Object.keys(mapping).forEach(function (name) {
      var block = document.querySelector('[data-bmd-home-block="' + mapping[name] + '"]');
      if (block) block.hidden = !checkboxValue(form, name);
    });
  }

  function settingsPayload(form) {
    return {
      color_preset: selectedValue(form, 'color_preset', 'neon'),
      card_ratio: selectedValue(form, 'card_ratio', 'landscape'),
      density: selectedValue(form, 'density', 'compact'),
      show_header_theme_toggle: checkboxValue(form, 'show_header_theme_toggle') ? '1' : '0',
      show_header_search: checkboxValue(form, 'show_header_search') ? '1' : '0',
      show_header_language: checkboxValue(form, 'show_header_language') ? '1' : '0',
      header_sticky: checkboxValue(form, 'header_sticky') ? '1' : '0',
      header_height: numberValue(form, 'header_height', '76'),
      header_breakpoint: numberValue(form, 'header_breakpoint', headerBreakpoint),
      show_top_notice: checkboxValue(form, 'show_top_notice') ? '1' : '0',
      random_latest_natural: checkboxValue(form, 'random_latest_natural') ? '1' : '0',
      random_latest_rows_desktop: numberValue(form, 'random_latest_rows_desktop', 2),
      random_latest_rows_tablet: numberValue(form, 'random_latest_rows_tablet', 2),
      random_latest_rows_mobile: numberValue(form, 'random_latest_rows_mobile', 2),
      random_latest_gap: numberValue(form, 'random_latest_gap', 16),
      random_latest_title: textValue(form, 'random_latest_title', ''),
      random_latest_view_all: textValue(form, 'random_latest_view_all', ''),
      show_hero: checkboxValue(form, 'show_hero') ? '1' : '0',
      show_featured: checkboxValue(form, 'show_featured') ? '1' : '0',
      show_latest: checkboxValue(form, 'show_latest') ? '1' : '0',
      show_random_latest: checkboxValue(form, 'show_random_latest') ? '1' : '0',
      random_latest_count: numberValue(form, 'random_latest_count', 15),
      random_latest_columns_desktop: numberValue(form, 'random_latest_columns_desktop', 5),
      random_latest_columns_tablet: numberValue(form, 'random_latest_columns_tablet', 4),
      random_latest_columns_mobile: numberValue(form, 'random_latest_columns_mobile', 3),
      show_directories: checkboxValue(form, 'show_directories') ? '1' : '0',
      explore_count: numberValue(form, 'explore_count', 5),
      explore_columns_desktop: numberValue(form, 'explore_columns_desktop', 3),
      explore_columns_tablet: numberValue(form, 'explore_columns_tablet', 2),
      explore_columns_mobile: numberValue(form, 'explore_columns_mobile', 1),
      explore_gap: numberValue(form, 'explore_gap', 12),
      single_show_gallery: checkboxValue(form, 'single_show_gallery') ? '1' : '0',
      single_gallery_columns: numberValue(form, 'single_gallery_columns', 4),
      single_gallery_columns_tablet: numberValue(form, 'single_gallery_columns_tablet', 3),
      single_gallery_columns_mobile: numberValue(form, 'single_gallery_columns_mobile', 2),
      single_gallery_rows: numberValue(form, 'single_gallery_rows', 2),
      single_gallery_max_items: numberValue(form, 'single_gallery_max_items', 8),
      single_gallery_gap: numberValue(form, 'single_gallery_gap', 10),
      single_gallery_ratio: selectedValue(form, 'single_gallery_ratio', 'landscape')
      
    };
  }

  var quickParts = panelElements();
  if (quickParts.form) {
    quickParts.form.addEventListener('change', function () { applyQuickPreview(quickParts.form); });
    quickParts.form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (!window.bmdQuickSettings || !bmdQuickSettings.canSave) return;
      var saveButton = quickParts.form.querySelector('.bmd-settings-save');
      if (saveButton) saveButton.disabled = true;
      if (quickParts.status) quickParts.status.textContent = bmdQuickSettings.saving || 'กำลังบันทึก…';
      var data = new window.FormData();
      data.append('action', 'bmd_save_quick_settings');
      data.append('nonce', bmdQuickSettings.nonce || '');
      var payload = settingsPayload(quickParts.form);
      Object.keys(payload).forEach(function (key) { data.append('settings[' + key + ']', payload[key]); });
      window.fetch(bmdQuickSettings.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data })
        .then(function (response) { return response.json(); })
        .then(function (result) {
          if (!result || !result.success) throw new Error('save_failed');
          if (quickParts.status) quickParts.status.textContent = bmdQuickSettings.saved || 'บันทึกแล้ว';
        })
        .catch(function () {
          if (quickParts.status) quickParts.status.textContent = bmdQuickSettings.error || 'บันทึกไม่สำเร็จ';
        })
        .finally(function () { if (saveButton) saveButton.disabled = false; });
    });
  }

  document.querySelectorAll('[data-bmd-like],[data-bmd-save]').forEach(function (button) {
    var isLike = button.hasAttribute('data-bmd-like');
    var key = (isLike ? 'bmd-liked-' : 'bmd-saved-') + (button.getAttribute('data-post-id') || '0');
    var active = false;
    try { active = window.localStorage.getItem(key) === '1'; } catch (e) {}
    button.classList.toggle('is-active', active);
    button.setAttribute('aria-pressed', active ? 'true' : 'false');
    var label = button.querySelector('.bmd-action-label');
    if (label) label.textContent = active ? (isLike ? 'ถูกใจแล้ว' : 'บันทึกแล้ว') : (isLike ? 'ถูกใจ' : 'บันทึก');
  });

  document.addEventListener('click', function (event) {
    var settingsOpen = event.target.closest('[data-bmd-settings-open]');
    if (settingsOpen) {
      setPanelOpen(true);
      return;
    }
    if (event.target.closest('[data-bmd-settings-close]') || event.target.closest('[data-bmd-settings-backdrop]')) {
      setPanelOpen(false);
      return;
    }

    var themeButton = event.target.closest('.bmd-theme-toggle');
    if (themeButton) {
      var current = root.getAttribute('data-bmd-theme') === 'light' ? 'light' : 'dark';
      var nextTheme = current === 'light' ? 'dark' : 'light';
      root.setAttribute('data-bmd-theme', nextTheme);
      try { window.localStorage.setItem('bmd-theme', nextTheme); } catch (e) {}
      return;
    }

    var searchButton = event.target.closest('.bmd-search-toggle');
    if (searchButton) {
      var searchPanel = document.getElementById('bmd-search-panel');
      var searchOpen = toggleHidden(searchPanel);
      searchButton.setAttribute('aria-expanded', searchOpen ? 'true' : 'false');
      var mobilePanel = document.getElementById('bmd-mobile-menu');
      if (mobilePanel) mobilePanel.setAttribute('hidden', 'hidden');
      var menuButton = document.querySelector('.bmd-menu-toggle');
      if (menuButton) menuButton.setAttribute('aria-expanded', 'false');
      if (searchOpen) {
        var input = searchPanel.querySelector('input[type="search"]');
        if (input) window.setTimeout(function () { input.focus(); }, 50);
      }
      return;
    }

    var menuToggle = event.target.closest('.bmd-menu-toggle');
    if (menuToggle) {
      var menuPanel = document.getElementById('bmd-mobile-menu');
      var menuOpen = toggleHidden(menuPanel);
      menuToggle.setAttribute('aria-expanded', menuOpen ? 'true' : 'false');
      var panel = document.getElementById('bmd-search-panel');
      if (panel) panel.setAttribute('hidden', 'hidden');
      var searchButton = document.querySelector('.bmd-search-toggle');
      if (searchButton) searchButton.setAttribute('aria-expanded', 'false');
      return;
    }

    var copyButton = event.target.closest('[data-bmd-copy-link]');
    if (copyButton) {
      var url = window.location.href;
      var label = copyButton.querySelector('.bmd-action-label');
      var original = label ? label.textContent : copyButton.textContent;
      var done = function () {
        if (label) label.textContent = 'คัดลอกแล้ว';
        window.setTimeout(function () { if (label) label.textContent = original; }, 1600);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(done).catch(function () {});
      } else {
        var field = document.createElement('textarea');
        field.value = url;
        document.body.appendChild(field);
        field.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        field.remove();
      }
      return;
    }

    var likeButton = event.target.closest('[data-bmd-like]');
    if (likeButton) {
      var likeKey = 'bmd-liked-' + (likeButton.getAttribute('data-post-id') || '0');
      var liked = likeButton.classList.toggle('is-active');
      likeButton.setAttribute('aria-pressed', liked ? 'true' : 'false');
      var likeLabel = likeButton.querySelector('.bmd-action-label');
      if (likeLabel) likeLabel.textContent = liked ? 'ถูกใจแล้ว' : 'ถูกใจ';
      try { window.localStorage.setItem(likeKey, liked ? '1' : '0'); } catch (e) {}
      return;
    }

    var savePostButton = event.target.closest('[data-bmd-save]');
    if (savePostButton) {
      var saveKey = 'bmd-saved-' + (savePostButton.getAttribute('data-post-id') || '0');
      var saved = savePostButton.classList.toggle('is-active');
      savePostButton.setAttribute('aria-pressed', saved ? 'true' : 'false');
      var saveLabel = savePostButton.querySelector('.bmd-action-label');
      if (saveLabel) saveLabel.textContent = saved ? 'บันทึกแล้ว' : 'บันทึก';
      try { window.localStorage.setItem(saveKey, saved ? '1' : '0'); } catch (e) {}
      return;
    }

    var lightboxLink = event.target.closest('[data-bmd-lightbox]');
    if (lightboxLink) {
      event.preventDefault();
      var galleryLinks = Array.prototype.slice.call(document.querySelectorAll('[data-bmd-lightbox]'));
      var currentIndex = galleryLinks.indexOf(lightboxLink);
      if (currentIndex < 0) currentIndex = 0;
      var previousActive = document.activeElement;
      var previousBodyOverflow = document.body.style.overflow || '';
      var touchStartX = 0;
      var touchStartY = 0;
      var overlay = document.createElement('div');
      overlay.className = 'bmd-lightbox-overlay';
      overlay.setAttribute('role', 'dialog');
      overlay.setAttribute('aria-modal', 'true');
      var shell = document.createElement('div');
      shell.className = 'bmd-lightbox-shell';
      var image = document.createElement('img');
      var updateImage = function (index) {
        currentIndex = (index + galleryLinks.length) % galleryLinks.length;
        var item = galleryLinks[currentIndex];
        image.src = item.href;
        image.alt = item.querySelector('img') ? item.querySelector('img').alt : '';
      };
      var closeOverlay = function () {
        overlay.remove();
        document.body.style.overflow = previousBodyOverflow;
        if (previousActive && typeof previousActive.focus === 'function') previousActive.focus();
      };
      var closeButton = document.createElement('button');
      closeButton.type = 'button';
      closeButton.className = 'bmd-lightbox-close';
      closeButton.setAttribute('aria-label', 'Close');
      closeButton.textContent = '×';
      closeButton.addEventListener('click', closeOverlay);
      var prevButton = document.createElement('button');
      prevButton.type = 'button';
      prevButton.className = 'bmd-lightbox-prev';
      prevButton.setAttribute('aria-label', 'Previous');
      prevButton.textContent = '‹';
      prevButton.addEventListener('click', function () { updateImage(currentIndex - 1); });
      var nextButton = document.createElement('button');
      nextButton.type = 'button';
      nextButton.className = 'bmd-lightbox-next';
      nextButton.setAttribute('aria-label', 'Next');
      nextButton.textContent = '›';
      nextButton.addEventListener('click', function () { updateImage(currentIndex + 1); });
      shell.appendChild(image);
      shell.appendChild(prevButton);
      shell.appendChild(nextButton);
      shell.appendChild(closeButton);
      overlay.appendChild(shell);
      overlay.addEventListener('click', function (event) { if (event.target === overlay) closeOverlay(); });
      shell.addEventListener('touchstart', function (event) {
        if (event.touches.length === 1) {
          touchStartX = event.touches[0].clientX;
          touchStartY = event.touches[0].clientY;
        }
      }, { passive: true });
      shell.addEventListener('touchend', function (event) {
        if (event.changedTouches.length !== 1) {
          return;
        }
        var dx = event.changedTouches[0].clientX - touchStartX;
        var dy = event.changedTouches[0].clientY - touchStartY;
        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
          event.preventDefault();
          updateImage(currentIndex + (dx < 0 ? 1 : -1));
        }
      }, { passive: true });
      document.body.appendChild(overlay);
      document.body.style.overflow = 'hidden';
      updateImage(currentIndex);
      closeButton.focus();
      overlay.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') { event.preventDefault(); updateImage(currentIndex - 1); }
        if (event.key === 'ArrowRight') { event.preventDefault(); updateImage(currentIndex + 1); }
        if (event.key === 'Escape') { event.preventDefault(); closeOverlay(); }
      });
      overlay.tabIndex = -1;
      overlay.focus();
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      if (document.querySelector('.bmd-lightbox-overlay')) {
        document.querySelectorAll('.bmd-lightbox-overlay').forEach(function (item) { item.remove(); });
        document.body.style.overflow = initialBodyOverflow;
      }
      closeMenus();
      setPanelOpen(false);
    }
  });

  document.querySelectorAll('[data-bmd-term-search]').forEach(function (input) {
    input.addEventListener('input', function () {
      var term = input.value.trim().toLocaleLowerCase();
      var container = input.closest('.bmd-container');
      var grid = container ? container.querySelector('[data-bmd-term-grid]') : null;
      if (!grid) return;
      grid.querySelectorAll('.bmd-term-card,.bmd-tag-chip').forEach(function (card) {
        var visible = !term || card.textContent.toLocaleLowerCase().indexOf(term) !== -1;
        card.classList.toggle('is-filtered-out', !visible);
      });
    });
  });
})();
