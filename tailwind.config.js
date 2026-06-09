import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // Tajawal carries Arabic beautifully and includes Latin glyphs.
                sans: ['Tajawal', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Premium deep-indigo brand scale.
                brand: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
                accent: {
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                },
            },
            boxShadow: {
                card: '0 1px 3px 0 rgba(17, 24, 39, 0.06), 0 1px 2px -1px rgba(17, 24, 39, 0.08)',
                'card-hover': '0 12px 28px -8px rgba(49, 46, 129, 0.22)',
            },
            borderRadius: {
                xl: '0.875rem',
                '2xl': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
