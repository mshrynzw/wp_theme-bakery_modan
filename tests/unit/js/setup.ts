import { afterEach, vi } from 'vitest';
import { resetDialogState } from '../../../src/dialog';

afterEach(() => {
  resetDialogState();
  document.body.replaceChildren();
  document.documentElement.className = '';
  for (const key of Object.keys(document.documentElement.dataset)) {
    delete document.documentElement.dataset[key];
  }
  window.bkeryTheme = undefined;
  vi.useRealTimers();
  vi.unstubAllGlobals();
  vi.restoreAllMocks();
});
