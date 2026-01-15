/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice 6
 * File: public_html/assets/js/widgets.js
 * Date: 2026-01-12
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 *
 * Purpose:
 * - Handles per-widget 3-dot menu actions (Settings/Refresh)
 * - Loads widget settings UI into the shared Bootstrap modal (#m1WidgetModal)
 * - Saves settings to DB via /dashboard/api/widget_settings.php (AJAX)
 *
 * Assumptions:
 * - Widget root: <section class="m1-widget" data-m1-widget-uid="..." data-m1-widget-key="...">
 * - Menu items exist (Settings/Refresh) OR you call openWidgetModal directly elsewhere.
 * - Shared modal exists: #m1WidgetModal (Bootstrap 5)
 * - CSRF meta exists: <meta name="m1-csrf" content="...">
 */

(function () {
  "use strict";

  function q(sel, root) { return (root || document).querySelector(sel); }
  function qa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  function closestWidget(el) {
    return el ? el.closest(".m1-widget") : null;
  }

  function getWidgetContext(widgetEl) {
    if (!widgetEl) return { uid: "", key: "" };
    const uid = widgetEl.getAttribute("data-m1-widget-uid") || "";
    const key = widgetEl.getAttribute("data-m1-widget-key") || "";
    return { uid, key };
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function getCsrf() {
    const meta = document.querySelector('meta[name="m1-csrf"]');
    return meta ? (meta.getAttribute("content") || "") : "";
  }

  function setModalStatus(txt) {
    const statusEl = document.getElementById("m1WidgetModalStatus");
    if (statusEl) statusEl.textContent = txt || "";
  }

  function setSaveVisible(visible) {
    const btn = document.getElementById("m1WidgetModalSaveBtn");
    if (!btn) return;
    btn.style.display = visible ? "" : "none";
  }

  function setModalContext(ctx) {
    const uidEl = document.getElementById("m1WidgetModalUid");
    const keyEl = document.getElementById("m1WidgetModalKey");
    if (uidEl) uidEl.value = ctx.uid || "";
    if (keyEl) keyEl.value = ctx.key || "";
  }

  function setModalTitle(title, subtitle) {
    const titleEl = document.getElementById("m1WidgetModalLabel");
    const subTitleEl = document.getElementById("m1WidgetModalSubTitle");
    if (titleEl) titleEl.textContent = title || "Widget Settings";
    if (subTitleEl) subTitleEl.textContent = subtitle || "";
  }

  function setModalBodyHtml(html) {
    const bodyEl = document.getElementById("m1WidgetModalBody");
    if (!bodyEl) return;
    bodyEl.innerHTML = html || "";
  }

  function openBootstrapModal() {
    const modalEl = document.getElementById("m1WidgetModal");
    if (!modalEl || typeof bootstrap === "undefined" || !bootstrap.Modal) return null;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    return modal;
  }

  async function fetchWidgetSettingsUI(ctx) {
    const url =
      "/dashboard/api/widget_settings.php" +
      "?action=ui" +
      "&widget_key=" + encodeURIComponent(ctx.key || "") +
      "&uid=" + encodeURIComponent(ctx.uid || "");

    const resp = await fetch(url, { credentials: "same-origin" });
    const data = await resp.json();
    return data;
  }

  function collectSettingsFromModal(widget_key) {
    // For now: collect generic [data-m1-setting] inputs into an object.
    // Widget-specific UI can still use this contract.
    const out = {};

    qa("[data-m1-setting]").forEach(function (el) {
      const k = el.getAttribute("data-m1-setting");
      if (!k) return;

      if (el.type === "checkbox") {
        out[k] = !!el.checked;
        return;
      }

      out[k] = el.value;
    });

    // widget-specific: you can harden/normalize here if needed
    if (widget_key === "weather_radar") {
      out.radar_url = String(out.radar_url || "");
    }

    return out;
  }

  async function saveWidgetSettings(ctx) {
    const csrf = getCsrf();
    if (!csrf) {
      setModalStatus("Missing CSRF token (m1-csrf).");
      return;
    }

    const settings = collectSettingsFromModal(ctx.key);
    const body = new URLSearchParams();
    body.set("action", "save");
    body.set("csrf", csrf);
    body.set("widget_key", ctx.key || "");
    body.set("uid", ctx.uid || "");
    body.set("settings_json", JSON.stringify(settings));

    setModalStatus("Saving…");

    const resp = await fetch("/dashboard/api/widget_settings.php", {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
      body: body.toString()
    });

    const data = await resp.json();

    if (!resp.ok || data.error) {
      setModalStatus(data && data.message ? data.message : "Save failed.");
      return;
    }

    setModalStatus("Saved.");
  }

  async function openWidgetModal(widgetEl, opts) {
    opts = opts || {};
    const ctx = getWidgetContext(widgetEl);

    if (!ctx.key) {
      // Safe default; do not throw
      setModalTitle("Widget Settings", "Missing widget_key");
      setModalBodyHtml('<div class="p-2 text-white-50 small">This widget has no <span class="text-white">data-m1-widget-key</span>.</div>');
      setSaveVisible(false);
      openBootstrapModal();
      return;
    }

    setModalContext(ctx);

    const titleEl = widgetEl ? q(".m1-widget-title", widgetEl) : null;
    const title = opts.title || (titleEl ? titleEl.textContent.trim() : "Widget Settings");
    setModalTitle(title, opts.subtitle || "Settings");

    setModalBodyHtml(
      '<div class="p-2 text-white-50 small">Loading settings…</div>'
    );
    setSaveVisible(false);
    setModalStatus("");

    openBootstrapModal();

    try {
      const data = await fetchWidgetSettingsUI(ctx);

      if (data.error) {
        setModalBodyHtml('<div class="p-2 text-white-50 small">' + escapeHtml(data.message || "Failed to load settings.") + '</div>');
        setSaveVisible(false);
        return;
      }

      setModalBodyHtml(data.html || "");
      setSaveVisible(!!data.can_save);

    } catch (e) {
      setModalBodyHtml('<div class="p-2 text-white-50 small">Error loading settings.</div>');
      setSaveVisible(false);
    }
  }

  // Save button handler (single global modal)
  function bindModalSave() {
    const btn = document.getElementById("m1WidgetModalSaveBtn");
    if (!btn) return;

    btn.addEventListener("click", function () {
      const uidEl = document.getElementById("m1WidgetModalUid");
      const keyEl = document.getElementById("m1WidgetModalKey");

      const ctx = {
        uid: uidEl ? uidEl.value : "",
        key: keyEl ? keyEl.value : ""
      };

      if (!ctx.key) {
        setModalStatus("Missing widget_key.");
        return;
      }

      saveWidgetSettings(ctx);
    });
  }

  // Existing “Settings” menu items (if you still have panels)
  function menuActionFromItem(itemEl) {
    const explicit = itemEl.getAttribute("data-m1-action");
    if (explicit) return explicit.trim().toLowerCase();

    const txt = (itemEl.textContent || "").trim().toLowerCase();
    if (txt.includes("setting")) return "settings";
    if (txt.includes("refresh")) return "refresh";
    return txt || "unknown";
  }

  function onMenuItemClick(e) {
    const item = e.currentTarget;
    const widgetEl = closestWidget(item);
    const action = menuActionFromItem(item);

    if (action === "settings") {
      openWidgetModal(widgetEl, { subtitle: "Settings" });
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    if (action === "refresh") {
      if (widgetEl) {
        const ctx = getWidgetContext(widgetEl);
        const ev = new CustomEvent("m1:widget:refresh", {
          bubbles: true,
          detail: { uid: ctx.uid, widget_key: ctx.key }
        });
        widgetEl.dispatchEvent(ev);
      }
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    e.preventDefault();
    e.stopPropagation();
  }

  function bindWidgetMenus() {
    qa(".m1-widget-menu-item").forEach(function (item) {
      item.removeEventListener("click", onMenuItemClick);
      item.addEventListener("click", onMenuItemClick);
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    bindWidgetMenus();
    bindModalSave();
  });

  window.m1BindWidgetMenus = bindWidgetMenus;

})();

function openWidgetModal(widgetEl, opts) {
  opts = opts || {};
  const ctx = getWidgetContext(widgetEl);

  const modalEl = document.getElementById("m1WidgetModal");
  if (!modalEl || typeof bootstrap === "undefined" || !bootstrap.Modal) return;

  const uidEl = document.getElementById("m1WidgetModalUid");
  const keyEl = document.getElementById("m1WidgetModalKey");
  if (uidEl) uidEl.value = ctx.uid;
  if (keyEl) keyEl.value = ctx.key;

  const titleEl = document.getElementById("m1WidgetModalLabel");
  const subTitleEl = document.getElementById("m1WidgetModalSubTitle");
  if (titleEl && opts.title) titleEl.textContent = opts.title;
  if (subTitleEl) subTitleEl.textContent = (opts.subtitle || ctx.key || ctx.uid || "");

  const bodyEl = document.getElementById("m1WidgetModalBody");
  const statusEl = document.getElementById("m1WidgetModalStatus");
  const saveBtn = document.getElementById("m1WidgetModalSaveBtn");

  if (statusEl) statusEl.textContent = "";
  if (saveBtn) saveBtn.style.display = "none";

  // Default placeholder
  if (bodyEl) {
    bodyEl.innerHTML =
      '<div class="text-white-50 small">' +
        'Loading settings for <span class="text-white">' + escapeHtml(ctx.key || "(no widget_key)") + '</span>...' +
      '</div>';
  }

  // Show modal immediately
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();

  // Only implement settings UI for weather_radar in this step
  if (ctx.key !== "weather_radar") {
    if (bodyEl) {
      bodyEl.innerHTML =
        '<div class="text-white-50 small">' +
          'No settings UI yet for <span class="text-white">' + escapeHtml(ctx.key || "") + '</span>.' +
        '</div>';
    }
    return;
  }

  // Weather radar options (expand later / tie to location table later)
  const radarOptions = [
    { label: "Exeter Branch (default KDIX)", value: "https://radar.weather.gov/ridge/standard/KDIX_loop.gif" },
    { label: "KDOX (Delmarva)", value: "https://radar.weather.gov/ridge/standard/KDOX_loop.gif" },
    { label: "KDIX (Philadelphia/PA/NJ)", value: "https://radar.weather.gov/ridge/standard/KDIX_loop.gif" },
    { label: "KCCX (Central PA)", value: "https://radar.weather.gov/ridge/standard/KCCX_loop.gif" },
  ];

  const endpoint = window.m1WidgetSettingsEndpoint || "";
  if (!endpoint) {
    if (bodyEl) bodyEl.innerHTML = '<div class="text-danger small">Missing settings endpoint.</div>';
    return;
  }

  // Load current saved setting
  const url = endpoint + "?action=load&widget_key=" + encodeURIComponent(ctx.key);
  fetch(url, { credentials: "same-origin" })
    .then(r => r.json())
    .then(data => {
      const current = (data && data.settings && data.settings.radar_url) ? String(data.settings.radar_url) : "";

      if (bodyEl) {
        const selectId = "m1WeatherRadarSelect";
        bodyEl.innerHTML =
          '<div class="p-2">' +
            '<div class="small text-white-50 mb-2">Choose radar loop for this location.</div>' +
            '<select class="form-select form-select-sm" id="' + selectId + '"></select>' +
            '<div class="small text-white-50 mt-2">Applies to: location_id <span class="text-white">' + escapeHtml(String(data.location_id || "")) + '</span></div>' +
          '</div>';

        const sel = document.getElementById(selectId);
        if (sel) {
          radarOptions.forEach(opt => {
            const o = document.createElement("option");
            o.value = opt.value;
            o.textContent = opt.label;
            sel.appendChild(o);
          });

          // Default to current saved value if it matches; else first option.
          const match = radarOptions.some(o => o.value === current);
          sel.value = match ? current : radarOptions[0].value;
        }
      }

      // Enable Save
      if (saveBtn) {
        saveBtn.style.display = "inline-block";
        saveBtn.onclick = function () {
          const csrfEl = document.getElementById("m1WidgetModalCsrf");
          const csrf = csrfEl ? csrfEl.value : "";

          const sel = document.getElementById("m1WeatherRadarSelect");
          const chosen = sel ? String(sel.value || "") : "";

          if (statusEl) statusEl.textContent = "Saving...";

          const form = new FormData();
          form.append("action", "save");
          form.append("csrf", csrf);
          form.append("widget_key", ctx.key);
          form.append("settings_json", JSON.stringify({ radar_url: chosen }));

          fetch(endpoint, { method: "POST", body: form, credentials: "same-origin" })
            .then(r => r.json())
            .then(resp => {
              if (resp && resp.ok) {
                if (statusEl) statusEl.textContent = "Saved.";

                // Optional immediate UI update: swap image in the widget if present
                const img = widgetEl ? widgetEl.querySelector("img") : null;
                if (img && chosen) img.src = chosen;
              } else {
                if (statusEl) statusEl.textContent = "Save failed.";
              }
            })
            .catch(() => {
              if (statusEl) statusEl.textContent = "Save failed.";
            });
        };
      }
    })
    .catch(() => {
      if (bodyEl) bodyEl.innerHTML = '<div class="text-danger small">Failed to load settings.</div>';
    });
}

