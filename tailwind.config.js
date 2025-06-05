import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/andrewdwallo/filament-companies/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                danger: '#e63c25',
                primary: {
                    50: '#fdf5f3',
                    100: '#fce8e3',
                    200: '#f9d4cc',
                    300: '#f5a8aa',
                    400: '#f38279',
                    500: '#e63c25',
                    600: '#d32f22',
                    700: '#b92222',
                    800: '#981e20',
                    900: '#7e1c1f',
                    950: '#450c0e',
                },
                warning: {
                    400: '#f5c04a',
                    500: '#edae0a',
                },
            },
        },
    },

    plugins: [forms, typography],
};
