import { beforeEach, describe, expect, it, vi } from 'vitest';
import { initVideo, renderVideoFrame } from '../../../src/video';
import { installMatchMedia, sampleTheme } from './helpers';

function videoDom(): HTMLElement {
  document.body.innerHTML = `
    <button type="button" data-video-open>再生</button>
    <dialog id="video-dialog">
      <button type="button" data-close>閉じる</button>
      <div data-video-frame></div>
    </dialog>
  `;
  const frame = document.querySelector<HTMLElement>('[data-video-frame]');
  if (!frame) throw new Error('missing frame');
  return frame;
}

describe('renderVideoFrame', () => {
  it('shows the placeholder and does not create a video when the file is missing', () => {
    const frame = videoDom();
    const video = renderVideoFrame(frame, { video: '  ', videoPoster: 'https://example.com/poster.webp' });
    expect(video).toBeNull();
    expect(frame.querySelector('video')).toBeNull();
    expect(frame.textContent).toContain('動画素材は準備中です');
    expect(frame.querySelector<HTMLElement>('.video-frame__empty')?.style.getPropertyValue('--poster')).toContain('poster.webp');
  });

  it('creates the video element only for the frame and removes the placeholder after data loads', () => {
    const frame = videoDom();
    const video = renderVideoFrame(frame, {
      video: 'https://example.com/bake.mp4',
      videoPoster: 'https://example.com/poster.webp',
    });
    expect(video?.getAttribute('src')).toBe('https://example.com/bake.mp4');
    expect(video?.getAttribute('aria-label')).toBe('パンができるまでの動画');
    expect(video?.controls).toBe(true);
    expect(frame.querySelector('.video-frame__empty')).not.toBeNull();
    video?.dispatchEvent(new Event('loadeddata'));
    expect(frame.querySelector('.video-frame__empty')).toBeNull();
  });

  it('drops the video element when the file fails, and keeps the placeholder', () => {
    const frame = videoDom();
    const video = renderVideoFrame(frame, { video: 'https://example.com/missing.mp4', videoPoster: '' });
    video?.dispatchEvent(new Event('error'));
    expect(frame.querySelector('video')).toBeNull();
    expect(frame.textContent).toContain('動画素材は準備中です');
  });
});

describe('initVideo', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    window.bkeryTheme = sampleTheme();
    installMatchMedia(() => false);
  });

  it('does not build a video until the opener is used, then pauses it on close', async () => {
    const frame = videoDom();
    initVideo();
    expect(frame.querySelector('video')).toBeNull();
    document.querySelector<HTMLButtonElement>('[data-video-open]')?.click();
    const video = frame.querySelector('video');
    expect(video).not.toBeNull();
    expect(document.querySelector<HTMLDialogElement>('#video-dialog')?.open).toBe(true);
    if (!video) return;
    Object.defineProperty(video, 'paused', { configurable: true, get: () => false });
    const pause = vi.spyOn(video, 'pause').mockImplementation(() => {});
    document.querySelector<HTMLButtonElement>('[data-close]')?.click();
    await vi.advanceTimersByTimeAsync(380);
    expect(pause).toHaveBeenCalled();
  });

  it('returns without a theme', () => {
    window.bkeryTheme = undefined;
    videoDom();
    initVideo();
    document.querySelector<HTMLButtonElement>('[data-video-open]')?.click();
    expect(document.querySelector('video')).toBeNull();
  });
});
