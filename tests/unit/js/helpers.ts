import { vi } from 'vitest';

export function sampleTheme(): BkeryThemeData {
  return {
    shop: {
      open: '10:00',
      close: '18:00',
      cafeClose: '17:00',
      closedWeekdays: [],
      addressText: '〒123-4567 北海道札幌市',
    },
    links: {
      instagram: 'https://www.instagram.com/bakery_s/',
      instagramHandle: '@bakery_s',
      map: 'https://maps.example/bakery',
      calendar: 'https://calendar.example/bake',
    },
    bakeSchedule: [
      { time: '10:00', items: ['croissant'] },
      { time: '11:00', items: ['focaccia', 'croque'] },
      { time: '15:00', items: ['apple', 'muffin'] },
    ],
    media: {
      video: 'https://example.com/bake.mp4',
      videoPoster: 'https://example.com/poster.webp',
    },
    steam: {
      enabled: 1,
      density: 50,
      speed: 50,
      column: 100,
      background: 100,
    },
    scenes: {
      hero: {
        image: ['https://example.com/hero.webp'],
        section: 'Bakery S',
        title: '日常に、\n焼きたて。',
        text: '本文',
      },
      gallery: {
        image: ['https://example.com/a.webp', 'https://example.com/b.webp'],
        section: '店内のようす',
        title: '店内の\nようす',
        text: '20席',
      },
      instagram: {
        image: [],
        section: 'Instagram',
        title: '季節の新作を、\nInstagramで。',
        text: '',
      },
    },
  };
}

export function installMatchMedia(matchesFor: (query: string) => boolean): { emit: (query: string) => void } {
  const listeners = new Map<string, Set<() => void>>();
  window.matchMedia = ((query: string) => {
    let group = listeners.get(query);
    if (!group) {
      group = new Set();
      listeners.set(query, group);
    }
    const stored = group;
    return {
      media: query,
      get matches() {
        return matchesFor(query);
      },
      onchange: null,
      addListener(listener: () => void) {
        stored.add(listener);
      },
      removeListener(listener: () => void) {
        stored.delete(listener);
      },
      addEventListener(_type: string, listener: EventListener) {
        stored.add(() => listener({} as Event));
      },
      removeEventListener() {},
      dispatchEvent() {
        return true;
      },
    } as MediaQueryList;
  }) as typeof window.matchMedia;

  return {
    emit(query: string) {
      listeners.get(query)?.forEach((listener) => listener());
    },
  };
}

export type RecordedObserver = {
  callback: IntersectionObserverCallback;
  options?: IntersectionObserverInit;
  elements: Element[];
};

export function installIntersectionObserver(): {
  observers: RecordedObserver[];
  intersect: (index: number, target: Element, extra?: Partial<IntersectionObserverEntry>) => void;
} {
  const observers: RecordedObserver[] = [];

  class FakeObserver implements IntersectionObserver {
    readonly root = null;
    readonly rootMargin: string;
    readonly thresholds: readonly number[];
    private readonly record: RecordedObserver;

    constructor(callback: IntersectionObserverCallback, options?: IntersectionObserverInit) {
      this.rootMargin = options?.rootMargin ?? '';
      const threshold = options?.threshold ?? 0;
      this.thresholds = Array.isArray(threshold) ? threshold : [threshold];
      this.record = { callback, options, elements: [] };
      observers.push(this.record);
    }

    observe(target: Element): void {
      this.record.elements.push(target);
    }

    unobserve(): void {}

    disconnect(): void {}

    takeRecords(): IntersectionObserverEntry[] {
      return [];
    }
  }

  vi.stubGlobal('IntersectionObserver', FakeObserver);
  return {
    observers,
    intersect(index, target, extra = {}) {
      const observer = observers[index];
      observer.callback([
        {
          isIntersecting: true,
          intersectionRatio: 1,
          target,
          boundingClientRect: new DOMRect(),
          intersectionRect: new DOMRect(),
          rootBounds: null,
          time: 0,
          ...extra,
        },
      ], {} as IntersectionObserver);
    },
  };
}

export function defineBox(element: HTMLElement, name: 'offsetLeft' | 'scrollWidth' | 'clientWidth', value: number): void {
  Object.defineProperty(element, name, { configurable: true, value });
}
