import { describe, expect, it } from 'vitest';
import {
  getBakePlan,
  getShopStatus,
  isClosedWeekday,
  parseClock,
  waitText,
  type ShopHours,
} from '../../../src/shop-status';

const shop: ShopHours = {
  open: '10:00',
  close: '18:00',
  cafeClose: '17:00',
};

const schedule = [
  { time: '10:00', items: ['croissant', 'shokupan'] },
  { time: '11:00', items: ['focaccia'] },
  { time: '15:00', items: ['apple'] },
];

describe('parseClock', () => {
  it('reads HH:MM as minutes from midnight', () => {
    expect(parseClock('10:00')).toBe(600);
    expect(parseClock('09:05')).toBe(545);
  });

  it('rejects a single-digit hour', () => {
    expect(parseClock('9:05')).toBeNull();
  });
});

describe('isClosedWeekday', () => {
  it('uses Date#getDay numbers, with Sunday as 0', () => {
    expect(isClosedWeekday(0, [0, 3])).toBe(true);
    expect(isClosedWeekday(1, [0, 3])).toBe(false);
  });
});

describe('getShopStatus', () => {
  it('is before open', () => {
    expect(getShopStatus(9 * 60, shop)).toMatchObject({
      state: 'soon',
      label: '開店前・10:00から',
    });
  });

  it('is open and the cafe is still open at the opening minute', () => {
    expect(getShopStatus(10 * 60, shop)).toMatchObject({
      state: 'open',
      label: '営業中・18:00まで',
      sub: 'カフェのご利用は17:00まで',
    });
  });

  it('keeps bread sales open after the cafe closes', () => {
    expect(getShopStatus(17 * 60, shop).sub).toBe(
      'カフェのご利用は終了しました（パンの販売は18:00まで）'
    );
  });

  it('is closed at the closing minute', () => {
    expect(getShopStatus(18 * 60, shop).state).toBe('closed');
  });

  it('treats a closed weekday before checking the clock', () => {
    expect(getShopStatus(12 * 60, shop, true)).toEqual({
      state: 'holiday',
      label: '本日は定休日です',
      sub: '営業時間 10:00〜18:00',
    });
  });

  it('asks for a check when the hours are inverted', () => {
    expect(getShopStatus(12 * 60, { ...shop, close: '09:00' }).label).toBe(
      '営業時間を確認してください'
    );
  });
});

describe('getBakePlan', () => {
  it('points at the first slot before opening', () => {
    const plan = getBakePlan(9 * 60, shop, schedule);
    expect(plan.next).toBe(0);
    expect(plan.isPast(plan.slots[0])).toBe(false);
  });

  it('skips slots that have already passed while open', () => {
    const plan = getBakePlan(10 * 60 + 30, shop, schedule);
    expect(plan.next).toBe(1);
    expect(plan.slots[plan.next].time).toBe('11:00');
    expect(plan.isPast(plan.slots[0])).toBe(true);
  });

  it('has no next slot after the last bake while still open', () => {
    const plan = getBakePlan(16 * 60, shop, schedule);
    expect(plan.next).toBe(-1);
  });

  it('marks every slot as past on a closed day', () => {
    const plan = getBakePlan(9 * 60, shop, schedule, true);
    expect(plan.next).toBe(-1);
    expect(plan.slots.every((slot) => plan.isPast(slot))).toBe(true);
  });

  it('drops a slot with an invalid time', () => {
    const plan = getBakePlan(9 * 60, shop, [{ time: '9:00', items: ['x'] }]);
    expect(plan.slots).toEqual([]);
    expect(plan.next).toBe(-1);
  });
});

describe('waitText', () => {
  it('formats minutes and hours', () => {
    expect(waitText(15)).toBe('あと15分');
    expect(waitText(60)).toBe('あと1時間');
    expect(waitText(61)).toBe('あと1時間1分');
  });

  it('does not show a negative wait', () => {
    expect(waitText(-3)).toBe('あと0分');
  });
});
