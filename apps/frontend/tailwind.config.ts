import type { Config } from "tailwindcss";

export default {
  darkMode: "class",
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: [
          '"IBM Plex Sans Arabic"',
          '"Inter"',
          "-apple-system",
          "BlinkMacSystemFont",
          '"Segoe UI"',
          "Roboto",
          "sans-serif",
        ],
        mono: [
          "ui-monospace",
          "SFMono-Regular",
          "Menlo",
          "Monaco",
          "Consolas",
          '"Liberation Mono"',
          '"Courier New"',
          "monospace",
        ],
      },
      colors: {
        notion: {
          bg: {
            DEFAULT: "#FFFFFF",
            dark: "#191919",
          },
          sidebar: {
            DEFAULT: "#F7F6F3",
            dark: "#202020",
          },
          card: {
            DEFAULT: "#FFFFFF",
            dark: "#242424",
          },
          hover: {
            DEFAULT: "#EFEFED",
            dark: "#2C2C2C",
          },
          active: {
            DEFAULT: "#E3E2DE",
            dark: "#353535",
          },
          border: {
            DEFAULT: "#E9E9E7",
            dark: "#2E2E2E",
          },
          text: {
            DEFAULT: "#37352F",
            dark: "#E3E3E0",
          },
          muted: {
            DEFAULT: "#787774",
            dark: "#9B9A97",
          },
          subtle: {
            DEFAULT: "#9B9A97",
            dark: "#5A5A58",
          },
          accent: {
            DEFAULT: "#2EAADC",
            dark: "#2383E2",
          },
          // Notion Tag & Highlight Palette
          tag: {
            gray: { bg: "#EBECED", text: "#37352F", darkBg: "#2A2A2A", darkText: "#9B9A97" },
            brown: { bg: "#E9E5E3", text: "#64473A", darkBg: "#3A2E2B", darkText: "#BCA39B" },
            orange: { bg: "#FAEBDD", text: "#D9730D", darkBg: "#432E1D", darkText: "#F0A664" },
            yellow: { bg: "#FBF3DB", text: "#DFAB01", darkBg: "#453819", darkText: "#F2CE6B" },
            green: { bg: "#EDF3EC", text: "#0F7B6C", darkBg: "#1C3729", darkText: "#68BC9E" },
            blue: { bg: "#E7F3F8", text: "#0B6E99", darkBg: "#1B364A", darkText: "#6FB3DC" },
            purple: { bg: "#F4F0F7", text: "#6940A5", darkBg: "#312544", darkText: "#B89EE8" },
            pink: { bg: "#F9EFF4", text: "#AD1A72", darkBg: "#401F32", darkText: "#E57FB7" },
            red: { bg: "#FDEBEC", text: "#E03E3E", darkBg: "#462020", darkText: "#EB8484" },
          },
        },
      },
      boxShadow: {
        notion: "rgba(15, 15, 15, 0.05) 0px 0px 0px 1px, rgba(15, 15, 15, 0.1) 0px 3px 6px, rgba(15, 15, 15, 0.2) 0px 9px 24px",
        "notion-subtle": "rgba(15, 15, 15, 0.05) 0px 0px 0px 1px, rgba(15, 15, 15, 0.05) 0px 2px 4px",
        "notion-menu": "rgba(15, 15, 15, 0.05) 0px 0px 0px 1px, rgba(15, 15, 15, 0.1) 0px 5px 10px, rgba(15, 15, 15, 0.2) 0px 15px 40px",
        "notion-dark": "rgba(0, 0, 0, 0.4) 0px 0px 0px 1px, rgba(0, 0, 0, 0.4) 0px 6px 16px, rgba(0, 0, 0, 0.6) 0px 12px 32px",
      },
    },
  },
  plugins: [],
} satisfies Config;
