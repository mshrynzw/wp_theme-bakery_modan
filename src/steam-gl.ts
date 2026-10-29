import {
  Mesh,
  OrthographicCamera,
  PlaneGeometry,
  Scene,
  ShaderMaterial,
  Vector2,
  WebGLRenderer,
} from 'three';
import { STEAM_FRAGMENT, STEAM_VERTEX, steamColumn, type SteamMotion } from './steam-field';

/** Three.js の全画面板に湯気のシェーダーを載せる。失敗したら呼び出し側が canvas を外す。 */
export function mountSteam(canvas: HTMLCanvasElement, motion: SteamMotion): () => void {
  const renderer = new WebGLRenderer({
    canvas,
    alpha: true,
    antialias: false,
    depth: false,
    stencil: false,
    powerPreference: 'low-power',
  });
  renderer.setClearColor(0x000000, 0);
  const scene = new Scene();
  const camera = new OrthographicCamera(-1, 1, 1, -1, 0, 1);
  const uniforms = {
    uTime: { value: 0 },
    uColumn: { value: new Vector2(0, 1) },
    uDensity: { value: motion.density },
    uSpeed: { value: motion.speed },
    uColumnStrength: { value: motion.column },
    uBackgroundStrength: { value: motion.background },
  };
  const material = new ShaderMaterial({
    uniforms,
    vertexShader: STEAM_VERTEX,
    fragmentShader: STEAM_FRAGMENT,
    transparent: true,
    depthTest: false,
    depthWrite: false,
  });
  const geometry = new PlaneGeometry(2, 2);
  scene.add(new Mesh(geometry, material));

  const resize = () => {
    const width = Math.max(window.innerWidth, 1);
    const height = Math.max(window.innerHeight, 1);
    const ratio = Math.min(window.devicePixelRatio || 1, width < 768 ? 1 : 1.25);
    renderer.setPixelRatio(ratio);
    renderer.setSize(width, height, false);
    const site = canvas.ownerDocument.querySelector('.site');
    const band = steamColumn(site instanceof HTMLElement ? site.getBoundingClientRect() : null, width);
    uniforms.uColumn.value.set(band.x, band.width);
  };

  const draw = (time: number) => {
    uniforms.uTime.value = time * 0.001;
    renderer.render(scene, camera);
  };

  const onVisibility = () => {
    renderer.setAnimationLoop(document.hidden ? null : draw);
  };

  resize();
  window.addEventListener('resize', resize);
  document.addEventListener('visibilitychange', onVisibility);
  renderer.setAnimationLoop(document.hidden ? null : draw);

  return () => {
    renderer.setAnimationLoop(null);
    window.removeEventListener('resize', resize);
    document.removeEventListener('visibilitychange', onVisibility);
    geometry.dispose();
    material.dispose();
    renderer.dispose();
    renderer.forceContextLoss();
    canvas.remove();
  };
}
