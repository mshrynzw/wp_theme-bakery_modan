/// <reference types="vite/client" />

declare global {
  interface BkeryThemeData {
    shop: {
      open: string;
      close: string;
      cafeClose: string;
      closedWeekdays: number[];
      addressText: string;
    };
    links: {
      instagram: string;
      instagramHandle: string;
      map: string;
      calendar: string;
    };
    bakeSchedule: Array<{ time: string; items: string[] }>;
    media: {
      video: string;
      videoPoster: string;
    };
    scenes: Record<string, { image: string[]; section: string; title: string; text: string }>;
    steam?: {
      enabled: boolean | number;
      density: number;
      speed: number;
      column: number;
      background: number;
    };
  }

  interface Window {
    bkeryTheme?: BkeryThemeData;
  }
}

export {};
