/**
 * カスタマイザー: テーマ同梱の配色を 11 色へ適用し、標準の色へ戻す。
 */
(function () {
  'use strict';

  function colorConfig() {
    return window.bkeryColorCustomize || null;
  }

  function applyColors(colors) {
    var data = colorConfig();
    if (!data || !colors) {
      return;
    }

    Object.keys(data.roles).forEach(function (role) {
      if (!Object.prototype.hasOwnProperty.call(colors, role)) {
        return;
      }
      var setting = wp.customize(data.roles[role].setting);
      if (setting) {
        setting.set(colors[role]);
      }
    });
  }

  function bindPresets(root) {
    var data = colorConfig();
    if (!data) {
      return;
    }

    var select = root.querySelector('.bkery-color-presets__select');
    var applyButton = root.querySelector('.bkery-color-presets__apply');
    var resetButton = root.querySelector('.bkery-color-presets__reset');
    if (!select || !applyButton || !resetButton) {
      return;
    }

    applyButton.addEventListener('click', function (event) {
      event.preventDefault();
      var presetId = select.value;
      if (!presetId || !data.presets[presetId]) {
        window.alert(data.i18n.choose);
        return;
      }
      applyColors(data.presets[presetId].colors);
    });

    resetButton.addEventListener('click', function (event) {
      event.preventDefault();
      if (!window.confirm(data.i18n.confirmReset)) {
        return;
      }
      var defaults = {};
      Object.keys(data.roles).forEach(function (role) {
        defaults[role] = data.roles[role].default;
      });
      applyColors(defaults);
      select.value = 'standard';
    });
  }

  wp.customize.bind('ready', function () {
    document.querySelectorAll('.bkery-color-presets').forEach(bindPresets);
  });
})();
