/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    // Only needed because resources/views/dev/design-system.blade.php builds
    // class names dynamically (bg-ink-{{ $shade }}) to render every swatch
    // from one loop instead of hand-writing 11 lines. Nothing else in this
    // codebase should rely on dynamic class construction — see
    // DESIGN_SYSTEM.md "Do not interpolate utility classes" note.
    safelist: [
        { pattern: /bg-ink-(50|100|200|300|400|500|600|700|800|900|950)/ },
        { pattern: /bg-ember-(50|100|200|300|400|500|600|700|800|900)/ },
    ],
    theme: {
        extend: {
            // --- Colors ---------------------------------------------------
            // Two-color system on purpose: 'ink' (neutral, does almost all
            // the work) + 'ember' (a single restrained accent). No blue/
            // purple AI-gradient palette, no rainbow of semantic tints.
            // See DESIGN_SYSTEM.md for the rationale and usage rules.
            colors: {
                ink: {
                    50: '#F7F7F5',
                    100: '#EEEEEA',
                    200: '#D9D9D2',
                    300: '#B7B7AC',
                    400: '#8E8E80',
                    500: '#6B6B5E',
                    600: '#52524A',
                    700: '#3D3D38',
                    800: '#26261F',
                    900: '#17170F',
                    950: '#0D0D08',
                },
                ember: {
                    50: '#FDF6EC',
                    100: '#FAE9CC',
                    200: '#F3D093',
                    300: '#E9B15A',
                    400: '#DC9530',
                    500: '#C07A1D',
                    600: '#9C6118',
                    700: '#7A4B15',
                    800: '#5C3812',
                    900: '#402710',
                },
                // Semantic colors are deliberately muted, not saturated
                // "alert" primaries — they should read as calm and
                // professional even in an error state.
                success: {
                    50: '#F1F7F2',
                    500: '#3F7A52',
                    700: '#2C5A3B',
                },
                warning: {
                    50: '#FBF4E8',
                    500: '#B8791E',
                    700: '#8A5A16',
                },
                danger: {
                    50: '#F9F0EE',
                    500: '#A5432E',
                    700: '#7C3221',
                },
                info: {
                    50: '#EEF3F3',
                    500: '#3A6E6E',
                    700: '#2A5252',
                },
            },

            // --- Typography -------------------------------------------------
            // Editorial serif for display/headings, a plain-spoken sans for
            // UI and body copy. This pairing is the single biggest lever
            // against the "generic AI SaaS" look — see DESIGN_SYSTEM.md.
            fontFamily: {
                display: ['"Fraunces"', 'Georgia', 'serif'],
                sans: ['"Public Sans"', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
            },
            fontSize: {
                'display-xl': ['3.5rem', { lineHeight: '1.05', letterSpacing: '-0.01em' }],
                'display-lg': ['2.75rem', { lineHeight: '1.08', letterSpacing: '-0.01em' }],
                'display-md': ['2.125rem', { lineHeight: '1.15' }],
                'display-sm': ['1.625rem', { lineHeight: '1.25' }],
            },

            // --- Radius --------------------------------------------------
            // Restrained on purpose — no 24px "everything is a blob" cards.
            borderRadius: {
                sm: '6px',
                DEFAULT: '8px',
                md: '10px',
                lg: '14px',
                pill: '999px',
            },

            // --- Shadows ---------------------------------------------------
            // Low-elevation, low-opacity. No glow, no glassmorphism.
            boxShadow: {
                'resting': '0 1px 2px 0 rgb(13 13 8 / 0.06), 0 1px 1px 0 rgb(13 13 8 / 0.04)',
                'raised': '0 4px 10px -2px rgb(13 13 8 / 0.10), 0 2px 4px -2px rgb(13 13 8 / 0.06)',
                'floating': '0 12px 28px -6px rgb(13 13 8 / 0.16), 0 4px 10px -4px rgb(13 13 8 / 0.08)',
            },

            // --- Spacing rhythm (documented convention, not new tokens) ---
            // Section vertical padding uses space-32/space-24 (see
            // DESIGN_SYSTEM.md "Spacing" table) rather than one-off values.
            maxWidth: {
                prose: '68ch',
                content: '1180px',
            },
        },
    },
    plugins: [],
};
