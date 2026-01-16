<?php
declare(strict_types=1);

/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice 6
 * File: usersc/includes/m1-location-selection.php
 * Date: 2026-01-16
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

if (!function_exists('m1_location_selection')) {
  /**
   * Echoes the Location Selection modal + JS handlers.
   *
   * Requirements:
   * - Bootstrap 5 CSS + bootstrap.bundle.js already present on page.
   * - UserSpice init.php already loaded on the calling page (for Token/DB/user session).
   *
   * @param int $this_user_id
   * @return void
   */
  function m1_location_selection(int $this_user_id): void
  {
    if ($this_user_id < 1) {
      return;
    }

    // Pull active locations
    $db = DB::getInstance();

    $locations = [];
    $locQ = $db->query(
      "SELECT id, name, type, address, city_state, postal_code
       FROM m1_locations
       WHERE active = 1
       ORDER BY type ASC, name ASC"
    );
    if ($locQ && $locQ->count() > 0) {
      $locations = $locQ->results();
    }

    // Current selection (best-effort)
    $current_location_id = null;
    $curQ = $db->query(
      "SELECT location_id
       FROM users_dashboard_location
       WHERE user_id = ?
       LIMIT 1",
      [$this_user_id]
    );
    if ($curQ && $curQ->count() === 1) {
      $current_location_id = (int)$curQ->first()->location_id;
    }

    $csrf = Token::generate();

    // Build list HTML (server-side for simplicity/perf)
    $listItemsHtml = '';
    foreach ($locations as $row) {
      $id = (int)$row->id;

      $name = (string)($row->name ?? '');
      $type = (string)($row->type ?? '');
      $address = (string)($row->address ?? '');
      $city_state = (string)($row->city_state ?? '');
      $postal_code = (string)($row->postal_code ?? '');

      $labelParts = [];
      if ($type !== '') { $labelParts[] = $type; }
      if ($address !== '') { $labelParts[] = $address; }
      $cityLine = trim($city_state . ' ' . $postal_code);
      if ($cityLine !== '') { $labelParts[] = $cityLine; }

      $sub = implode(' • ', $labelParts);

      $isActive = ($current_location_id !== null && $current_location_id === $id);
      $activeBadge = $isActive ? '<span class="badge bg-success ms-2" id="m1LocSelActiveBadge">Current</span>' : '';

      $dataSearch = strtolower(trim($name . ' ' . $sub));

      $listItemsHtml .= '
        <button
          type="button"
          class="list-group-item list-group-item-action d-flex justify-content-between align-items-start"
          data-m1-locsel-location-id="' . $id . '"
          data-m1-locsel-search="' . htmlspecialchars($dataSearch, ENT_QUOTES, 'UTF-8') . '"
        >
          <div class="me-2">
            <div class="fw-semibold">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . $activeBadge . '</div>
            <div class="small text-muted">' . htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') . '</div>
          </div>
          <div class="ms-auto">
            <span class="btn btn-sm btn-outline-light">Select</span>
          </div>
        </button>
      ';
    }

    echo '
<!-- M1 Location Selector Modal (Bootstrap 5) -->
<div class="modal fade" id="m1LocSelModal" tabindex="-1" aria-labelledby="m1LocSelModalLabel" aria-hidden="true" data-m1-locsel="1">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title" id="m1LocSelModalLabel">Select Location</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="m1LocSelUserId" value="' . (int)$this_user_id . '">
        <input type="hidden" id="m1LocSelCsrf" value="' . htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') . '">

        <div class="mb-3">
          <input type="text" class="form-control form-control-lg bg-black text-light border-secondary" id="m1LocSelSearch"
            placeholder="Search locations (name, address, city)...">
          <div class="form-text text-muted mt-2">
            This selection updates your dashboard context (maps, widgets, alerts).
          </div>
        </div>

        <div class="list-group" id="m1LocSelList">
          ' . $listItemsHtml . '
        </div>

        <div class="mt-3">
          <div class="alert alert-secondary d-none" id="m1LocSelMsg" role="alert"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  "use strict";

  // Unique namespaced state
    var m1LocSel_state = {
    modal: null,
    modalEl: null,
    listEl: null,
    searchEl: null,
    msgEl: null,
    userIdEl: null,
    csrfEl: null,
    reloadAfterClose: false
  };

  function m1LocSel_reloadParentPageClean() {
    // Remove any #hash without changing path/query
    if (window.history && window.history.replaceState) {
      window.history.replaceState(
        null,
        document.title,
        window.location.pathname + window.location.search
      );
    }

    // Force full refresh of whatever page we are on
    window.location.href = window.location.pathname + window.location.search;
  }


  function m1LocSel_getEl(id) {
    return document.getElementById(id);
  }

  function m1LocSel_showMessage(text, isOk) {
    if (!m1LocSel_state.msgEl) return;
    m1LocSel_state.msgEl.classList.remove("d-none");
    m1LocSel_state.msgEl.classList.remove("alert-success", "alert-danger", "alert-secondary");
    m1LocSel_state.msgEl.classList.add(isOk ? "alert-success" : "alert-danger");
    m1LocSel_state.msgEl.textContent = text;
  }

  function m1LocSel_clearMessage() {
    if (!m1LocSel_state.msgEl) return;
    m1LocSel_state.msgEl.classList.add("d-none");
    m1LocSel_state.msgEl.textContent = "";
  }

  function m1LocSel_openModal() {
    if (!m1LocSel_state.modalEl) return;
    if (!m1LocSel_state.modal) {
      if (!window.bootstrap || !window.bootstrap.Modal) return;
      m1LocSel_state.modal = new window.bootstrap.Modal(m1LocSel_state.modalEl, { backdrop: true, keyboard: true });
    }
    m1LocSel_clearMessage();
    m1LocSel_state.modal.show();
    window.setTimeout(function () {
      if (m1LocSel_state.searchEl) m1LocSel_state.searchEl.focus();
    }, 250);
  }

  function m1LocSel_matchesLocationsHash(href) {
    if (!href) return false;
    return (href === "#locations" || href === "/#locations");
  }

  function m1LocSel_bindMenuTriggers() {
    // 1) Click interception for any anchor that points to #locations (including Ultra Menu output)
    document.addEventListener("click", function (e) {
      var a = e.target && e.target.closest ? e.target.closest("a") : null;
      if (!a) return;

      var href = a.getAttribute("href");
      if (!m1LocSel_matchesLocationsHash(href)) return;

      // Prevent hash navigation and open modal
      e.preventDefault();
      m1LocSel_openModal();
    }, true);

    // 2) Also respond to direct hash navigation (manual entry or other scripts)
    window.addEventListener("hashchange", function () {
      if (window.location && window.location.hash === "#locations") {
        m1LocSel_openModal();
      }
    });
  }

  function m1LocSel_filterList(query) {
    if (!m1LocSel_state.listEl) return;

    var q = (query || "").trim().toLowerCase();
    var items = m1LocSel_state.listEl.querySelectorAll("[data-m1-locsel-location-id]");
    items.forEach(function (btn) {
      var hay = (btn.getAttribute("data-m1-locsel-search") || "");
      var show = (q === "" || hay.indexOf(q) !== -1);
      btn.style.display = show ? "" : "none";
    });
  }

  async function m1LocSel_saveLocation(locationId) {
    var userId = m1LocSel_state.userIdEl ? m1LocSel_state.userIdEl.value : "";
    var csrf = m1LocSel_state.csrfEl ? m1LocSel_state.csrfEl.value : "";

    if (!userId || !csrf || !locationId) {
      m1LocSel_showMessage("Cannot save: missing required parameters.", false);
      return;
    }

    m1LocSel_clearMessage();

    try {
      var form = new URLSearchParams();
      form.set("user_id", userId);
      form.set("location_id", String(locationId));
      form.set("csrf", csrf);

        var resp = await fetch("/assets/ajax/m1-location-save.php", {

        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: form.toString(),
        credentials: "same-origin"
      });

      var data = null;
      try {
        data = await resp.json();
      } catch (jsonErr) {
        data = null;
      }

      if (!resp.ok || !data || !data.success) {
        var msg = (data && data.message) ? data.message : "Save failed.";
        m1LocSel_showMessage(msg, false);
        return;
      }

      // Rotate CSRF token if server returned a new one
      if (data.csrf && m1LocSel_state.csrfEl) {
        m1LocSel_state.csrfEl.value = data.csrf;
      }

      m1LocSel_showMessage("Location updated.", true);

      // Optional: update hashless UX; allow other scripts to react
      window.dispatchEvent(new CustomEvent("m1_location_changed", {
        detail: { location_id: data.location_id }
      }));

      // Close modal first; reload only after Bootstrap finishes closing it
      m1LocSel_state.reloadAfterClose = true;

      if (m1LocSel_state.modal) {
        m1LocSel_state.modal.hide();
      } else {
        // Fallback if modal instance is unavailable
        m1LocSel_reloadParentPageClean();
      }



    } catch (err) {
      m1LocSel_showMessage("Save failed (network error).", false);
    }
  }

  function m1LocSel_bindListClicks() {
    if (!m1LocSel_state.listEl) return;

    m1LocSel_state.listEl.addEventListener("click", function (e) {
      var btn = e.target && e.target.closest ? e.target.closest("[data-m1-locsel-location-id]") : null;
      if (!btn) return;

      var id = btn.getAttribute("data-m1-locsel-location-id");
      if (!id) return;

      e.preventDefault();
      m1LocSel_saveLocation(id);
    });
  }

  function m1LocSel_init() {
    m1LocSel_state.modalEl = m1LocSel_getEl("m1LocSelModal");
    m1LocSel_state.listEl  = m1LocSel_getEl("m1LocSelList");
    m1LocSel_state.searchEl = m1LocSel_getEl("m1LocSelSearch");
    m1LocSel_state.msgEl = m1LocSel_getEl("m1LocSelMsg");
    m1LocSel_state.userIdEl = m1LocSel_getEl("m1LocSelUserId");
    m1LocSel_state.csrfEl = m1LocSel_getEl("m1LocSelCsrf");

    // Reload only after the modal is fully closed (Bootstrap transition complete)
    if (m1LocSel_state.modalEl) {
      m1LocSel_state.modalEl.addEventListener("hidden.bs.modal", function () {
        if (m1LocSel_state.reloadAfterClose) {
          m1LocSel_state.reloadAfterClose = false;
          m1LocSel_reloadParentPageClean();
        }
      });
    }

    if (m1LocSel_state.searchEl) {
      m1LocSel_state.searchEl.addEventListener("input", function () {
        m1LocSel_filterList(m1LocSel_state.searchEl.value);
      });
    }

    m1LocSel_bindMenuTriggers();
    m1LocSel_bindListClicks();

    // Auto-open if page loads already at #locations
    if (window.location && window.location.hash === "#locations") {
      m1LocSel_openModal();
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", m1LocSel_init);
  } else {
    m1LocSel_init();
  }
})();
</script>
';
  }
}
