const CLOCK = /^([01][0-9]|2[0-3]):([0-5][0-9])$/;

export type ShopHours = {
  open: string;
  close: string;
  cafeClose: string;
};

export type ShopStatusState = 'soon' | 'open' | 'closed' | 'holiday';

export type ShopStatus = {
  state: ShopStatusState;
  label: string;
  sub: string;
};

export type BakeSlot = {
  time: string;
  items: string[];
};

export type BakeSlotPlan = BakeSlot & {
  min: number;
};

export type BakePlan = {
  slots: BakeSlotPlan[];
  next: number;
  isPast: (slot: { min: number }) => boolean;
  status: ShopStatus;
};

export function parseClock(value: string): number | null {
  const match = CLOCK.exec(value.trim());
  if (!match) return null;
  return Number(match[1]) * 60 + Number(match[2]);
}

/** day は Date#getDay()。0 が日曜。 */
export function isClosedWeekday(day: number, closedWeekdays: readonly number[]): boolean {
  return closedWeekdays.includes(day);
}

function hoursLabel(shop: ShopHours): string {
  if (parseClock(shop.open) === null || parseClock(shop.close) === null) return '';
  return `営業時間 ${shop.open}〜${shop.close}`;
}

export function getShopStatus(
  nowMinutes: number,
  shop: ShopHours,
  closedDay = false
): ShopStatus {
  if (closedDay) {
    return {
      state: 'holiday',
      label: '本日は定休日です',
      sub: hoursLabel(shop),
    };
  }

  const open = parseClock(shop.open);
  const close = parseClock(shop.close);
  const cafeClose = parseClock(shop.cafeClose);
  if (open === null || close === null || cafeClose === null || close <= open) {
    return { state: 'closed', label: '営業時間を確認してください', sub: '' };
  }

  if (nowMinutes < open) {
    return {
      state: 'soon',
      label: `開店前・${shop.open}から`,
      sub: `営業時間 ${shop.open}〜${shop.close}`,
    };
  }
  if (nowMinutes >= close) {
    return {
      state: 'closed',
      label: '本日の営業は終了しました',
      sub: `営業時間 ${shop.open}〜${shop.close}`,
    };
  }

  return {
    state: 'open',
    label: `営業中・${shop.close}まで`,
    sub:
      nowMinutes < cafeClose
        ? `カフェのご利用は${shop.cafeClose}まで`
        : `カフェのご利用は終了しました（パンの販売は${shop.close}まで）`,
  };
}

export function getBakePlan(
  nowMinutes: number,
  shop: ShopHours,
  schedule: readonly BakeSlot[],
  closedDay = false
): BakePlan {
  const status = getShopStatus(nowMinutes, shop, closedDay);
  const slots = schedule.flatMap((slot) => {
    const min = parseClock(slot.time);
    if (min === null) return [];
    return [{ time: slot.time, items: [...slot.items], min }];
  });

  let next = -1;
  if (status.state === 'soon' && slots.length > 0) next = 0;
  else if (status.state === 'open') next = slots.findIndex((slot) => slot.min > nowMinutes);

  const isPast = (slot: { min: number }) => {
    if (status.state === 'holiday') return true;
    if (status.state === 'soon') return false;
    return slot.min <= nowMinutes;
  };

  return { slots, next, isPast, status };
}

export function waitText(mins: number): string {
  const safe = mins < 0 ? 0 : mins;
  if (safe < 60) return `あと${safe}分`;
  const hours = Math.floor(safe / 60);
  const minutes = safe % 60;
  return minutes ? `あと${hours}時間${minutes}分` : `あと${hours}時間`;
}
