import { icon } from './dom';
import { openDialog } from './dialog';

export function renderVideoFrame(
  frame: HTMLElement,
  media: { video: string; videoPoster: string },
): HTMLVideoElement | null {
  frame.replaceChildren();
  const empty = document.createElement('div');
  empty.className = 'video-frame__empty';
  const poster = media.videoPoster.trim();
  if (poster) {
    empty.style.setProperty('--poster', `url("${poster.replace(/["'\\\n\r]/g, '')}")`);
  }
  const title = document.createElement('p');
  title.textContent = '動画素材は準備中です';
  const note = document.createElement('small');
  const code = document.createElement('code');
  code.textContent = media.video;
  note.append(code, ' を配置すると', document.createElement('br'), 'ここで再生されます（モック用の表示）');
  empty.append(icon('i-play'), title, note);
  frame.append(empty);

  const src = media.video.trim();
  if (!src) return null;

  const video = document.createElement('video');
  video.controls = true;
  video.playsInline = true;
  video.preload = 'metadata';
  if (poster) video.poster = poster;
  video.setAttribute('playsinline', '');
  video.setAttribute('aria-label', 'パンができるまでの動画');
  video.addEventListener('loadeddata', () => {
    frame.querySelector('.video-frame__empty')?.remove();
  }, { once: true });
  video.addEventListener('error', () => {
    video.remove();
  }, { once: true });
  video.src = src;
  frame.prepend(video);
  return video;
}

const bound = new WeakSet<HTMLDialogElement>();
const built = new WeakSet<HTMLElement>();

export function initVideo(): void {
  if (!window.bkeryTheme) return;
  const dlg = document.querySelector('#video-dialog');
  const opener = document.querySelector('[data-video-open]');
  if (!(dlg instanceof HTMLDialogElement) || !(opener instanceof HTMLElement) || bound.has(dlg)) return;
  const frame = dlg.querySelector('[data-video-frame]');
  if (!(frame instanceof HTMLElement)) return;
  bound.add(dlg);

  opener.addEventListener('click', () => {
    if (!built.has(frame)) {
      built.add(frame);
      const media = window.bkeryTheme?.media;
      renderVideoFrame(frame, {
        video: media?.video ?? '',
        videoPoster: media?.videoPoster ?? '',
      });
    }
    openDialog(dlg, opener);
  });

  dlg.addEventListener('modal:closed', () => {
    const video = frame.querySelector('video');
    if (video && !video.paused) video.pause();
  });
}
