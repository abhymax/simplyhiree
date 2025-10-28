import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    // We are removing 'dark' from the darkMode class to disable it.
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // You can add your brand colors here for easy use throughout the app
            colors: {
                'brand-blue': '#2563eb',
                'brand-indigo': '#4f46e5',
            },
        },
    },

    plugins: [forms],
};
    
