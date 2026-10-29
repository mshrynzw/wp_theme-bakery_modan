import {
  getBakePlan,
  getShopStatus,
  isClosedWeekday,
  waitText,
  type BakePlan,
  type BakeSlot,
  type ShopHours,
} from './shop-status';

/** 次の分の変わり目まで待ち、50ms 余白を足す。 */
export function statusRefreshDelay(nowMs: number): number {
  return 60000 - (nowMs % 60000) + 50;
}

export function shopHoursOf(theme: BkeryThemeData): ShopHours {
  const shop = theme.shop;
  return {
    open: typeof shop?.open === 'string' ? shop.open : '',
    close: typeof shop?.close === 'string' ? shop.close : '',
    cafeClose: typeof shop?.cafeClose === 'string' ? shop.cafeClose : '',
  };
}

export function closedWeekdayList(value: unknown): number[] {
  if (!Array.isArray(value)) return [];
  return value.flatMap((day) => {
    if (typeof day === 'number' && Number.isInteger(day) && day >= 0 && day <= 6) return [day];
    if (typeof day === 'string' && /^[0-6]$/.test(day)) return [Number(day)];
    return [];
  });
}

export function bakeScheduleOf(theme: BkeryThemeData): BakeSlot[] {
  if (!Array.isArray(theme.bakeSchedule)) return [];
  return theme.bakeSchedule.flatMap((slot) => {
    if (!slot || typeof slot.time !== 'string' || !Array.isArray(slot.items)) return [];
    const items = slot.items.filter((item): item is string => typeof item === 'string' && item !== '');
    return [{ time: slot.time, items }];
  });
}

export function productName(id: string, root: ParentNode): string {
  const nodes = Array.from(root.querySelectorAll<HTMLElement>('[data-product]')).filter((el) => el.dataset.product === id);
  for (const node of nodes) {
    const named = node.querySelector('.season-card__name, .menu-card__name');
    const text = named?.textContent?.trim();
    if (text) return text;
  }
  for (const node of nodes) {
    if (node.matches('.season-card, .menu-card')) continue;
    const text = node.textContent?.replace(/\s+/g, ' ').trim();
    if (text) return text;
  }
  return '';
}

function slotElement(time: string, root: ParentNode): HTMLElement | null {
  return Array.from(root.querySelectorAll<HTMLElement>('[data-bake-slot]')).find((item) => item.dataset.bakeSlot?.trim() === time) ?? null;
}

function isSeasonal(id: string, root: ParentNode): boolean {
  return Array.from(root.querySelectorAll<HTMLElement>('[data-product]')).some((el) => {
    if (el.dataset.product !== id) return false;
    if (el.dataset.line === 'seasonal') return true;
    if (el.classList.contains('season-card')) return true;
    if (el.querySelector('.tag--season')) return true;
    return Boolean(el.closest('#season'));
  });
}

function cloneChips(time: string, root: ParentNode): HTMLElement | null {
  const item = slotElement(time, root);
  if (!item) return null;
  const chips = item.querySelector('.chips');
  if (chips instanceof HTMLElement && chips.childElementCount > 0) {
    return chips.cloneNode(true) as HTMLElement;
  }
  const buttons = Array.from(item.querySelectorAll<HTMLElement>('[data-product]'));
  if (!buttons.length) return null;
  const wrap = document.createElement('div');
  wrap.className = 'chips';
  buttons.forEach((button) => wrap.append(button.cloneNode(true)));
  return wrap;
}

function buildChips(items: readonly string[], root: ParentNode): HTMLElement | null {
  const wrap = document.createElement('div');
  wrap.className = 'chips';
  items.forEach((id) => {
    const name = productName(id, root);
    if (!name) return;
    const button = document.createElement('button');
    button.type = 'button';
    button.dataset.product = id;
    button.setAttribute('aria-haspopup', 'dialog');
    if (isSeasonal(id, root)) {
      button.setAttribute('aria-label', `期間限定 ${name}`);
      const dot = document.createElement('span');
      dot.className = 'dot-season';
      dot.setAttribute('aria-hidden', 'true');
      button.append(dot);
    }
    button.append(document.createTextNode(name));
    wrap.append(button);
  });
  return wrap.childElementCount > 0 ? wrap : null;
}

function markBakeSlots(plan: BakePlan, root: ParentNode): void {
  const nextTime = plan.next >= 0 ? plan.slots[plan.next]?.time : undefined;
  root.querySelectorAll<HTMLElement>('[data-bake-slot]').forEach((item) => {
    item.classList.remove('is-next', 'is-past');
    const time = item.dataset.bakeSlot?.trim() ?? '';
    const slot = plan.slots.find((entry) => entry.time === time);
    if (!slot) return;
    if (time === nextTime) item.classList.add('is-next');
    if (plan.isPast(slot)) item.classList.add('is-past');
  });
}

function renderBakeNext(plan: BakePlan, nowMinutes: number, root: ParentNode): void {
  const slot = plan.next >= 0 ? plan.slots[plan.next] : undefined;
  root.querySelectorAll<HTMLElement>('[data-bake-next]').forEach((box) => {
    box.replaceChildren();
    if (!slot) {
      const done = document.createElement('p');
      done.className = 'bake__next-done';
      done.textContent = plan.status.state === 'holiday'
        ? '本日は定休日です'
        : '本日の焼き上がりはすべて終了しました';
      box.append(done);
      return;
    }

    const label = document.createElement('p');
    label.className = 'bake__next-label';
    label.textContent = '次の焼き上がり';
    const time = document.createElement('p');
    time.className = 'bake__next-time';
    time.append(document.createTextNode(slot.time));
    const small = document.createElement('small');
    small.textContent = plan.status.state === 'soon' ? '開店と同時に' : waitText(slot.min - nowMinutes);
    time.append(small);
    box.append(label, time);
    const chips = cloneChips(slot.time, root) ?? buildChips(slot.items, root);
    if (chips) box.append(chips);
  });
}

function renderBakeShort(plan: BakePlan, root: ParentNode): void {
  const slot = plan.next >= 0 ? plan.slots[plan.next] : undefined;
  const firstId = slot?.items[0] ?? '';
  const name = firstId ? productName(firstId, root) : '';
  const short = slot && name
    ? `次の焼き上がり ${slot.time}\u3000${name}${slot.items.length > 1 ? ' ほか' : ''}`
    : '';
  const quick = slot ? `次は${slot.time}` : '今日の時間を見る';
  root.querySelectorAll<HTMLElement>('[data-next-bake-short]').forEach((el) => {
    el.textContent = short;
  });
  root.querySelectorAll<HTMLElement>('[data-next-bake-quick]').forEach((el) => {
    el.textContent = quick;
  });
}

/** 分と曜日を引数で受け、端末時刻そのものはここでは読まない。 */
export function applyShopStatus(nowMinutes: number, day: number, theme: BkeryThemeData, root: ParentNode = document): void {
  if (!theme.shop) return;
  const hours = shopHoursOf(theme);
  const closed = isClosedWeekday(day, closedWeekdayList(theme.shop.closedWeekdays));
  const status = getShopStatus(nowMinutes, hours, closed);
  root.querySelectorAll<HTMLElement>('[data-status-label]').forEach((el) => {
    el.textContent = status.label;
  });
  root.querySelectorAll<HTMLElement>('[data-status-sub]').forEach((el) => {
    el.textContent = status.sub;
  });
  root.querySelectorAll<HTMLElement>('[data-status-dot]').forEach((el) => {
    el.dataset.state = status.state;
  });

  const plan = getBakePlan(nowMinutes, hours, bakeScheduleOf(theme), closed);
  markBakeSlots(plan, root);
  renderBakeNext(plan, nowMinutes, root);
  renderBakeShort(plan, root);
}

let statusTimer = 0;
let statusListening = false;

function paint(): void {
  const theme = window.bkeryTheme;
  if (!theme?.shop) return;
  const now = new Date();
  applyShopStatus(now.getHours() * 60 + now.getMinutes(), now.getDay(), theme, document);
}

function arm(): void {
  window.clearTimeout(statusTimer);
  statusTimer = window.setTimeout(() => {
    paint();
    arm();
  }, statusRefreshDelay(Date.now()));
}

export function initStatus(): void {
  if (!window.bkeryTheme?.shop) return;
  paint();
  if (statusListening) return;
  statusListening = true;
  arm();
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) paint();
  });
}
