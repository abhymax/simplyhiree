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

    // *** THIS IS THE CRITICAL FIX ***
    // This forces Tailwind to include all the classes our dashboard needs.
    safelist: [
        'grid',
        'grid-cols-1',
        'sm:grid-cols-2',
        'lg:grid-cols-3',
        'gap-6',
        'text-yellow-600',
        'text-yellow-500',
        'text-red-600',
        'text-red-500',
        'text-green-600',
        'text-green-500',
        'text-purple-600',
        'text-purple-500',
        'text-blue-600',
        'text-blue-500',
        'text-4xl',
        'fa-3x', // Font-awesome size class
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