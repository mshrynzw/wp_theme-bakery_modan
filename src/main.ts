import './styles/theme.css';
import { initCarousel } from './carousel';
import { initHero } from './hero';
import { initLightbox } from './lightbox';
import { initProductSheet } from './product-sheet';
import { initReveal } from './reveal';
import { initShopUi } from './shop-ui';
import { initStage } from './stage';
import { initStatus } from './status';
import { initSteam } from './steam';
import { initVideo } from './video';

const ready = (cb: () => void) => {
  if (document.readyState !== 'loading') cb();
  else document.addEventListener('DOMContentLoaded', cb);
};

ready(() => {
  initStatus();
  initHero();
  initCarousel();
  initProductSheet();
  initLightbox();
  initVideo();
  initStage();
  initReveal();
  initShopUi();
  initSteam();
});
