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

    // *** CRITICAL FIX ***
    // Safelisting classes forces Tailwind to include them in the final CSS build,
    // ensuring your dynamic gradients and colors appear correctly.
    safelist: [
        // Layout & Grid
        'grid', 'grid-cols-1', 'sm:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-5', 
        'gap-6', 'gap-8',
        
        // Font Sizes
        'text-xs', 'text-sm', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl', 
        
        // Gradients (Backgrounds)
        'bg-gradient-to-br', 'bg-gradient-to-r',
        
        // Gradient Colors (From & To)
        'from-indigo-600', 'to-blue-700',
        'from-amber-400', 'to-orange-500',
        'from-rose-500', 'to-pink-600',
        'from-emerald-400', 'to-teal-600',
        'from-violet-500', 'to-purple-600',
        'from-blue-400', 'to-cyan-500',
        
        // Text Colors (Visibility)
        'text-white', 
        'text-gray-900', 'text-gray-800', 'text-gray-500', 'text-gray-600',
        'text-yellow-600', 'text-yellow-500',
        'text-red-600', 'text-red-500',
        'text-green-600', 'text-green-500',
        'text-purple-600', 'text-purple-500',
        'text-blue-600', 'text-blue-500',
        
        // Effects (Shadow, Hover, Transform)
        'shadow-lg', 'shadow-xl', 
        'hover:shadow-xl', 'hover:scale-[1.02]', 
        'transform', 'transition-all',
        
        // Font Awesome & Icon Helpers
        'fa-3x',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Custom Brand Colors
            colors: {
                'brand-blue': '#2563eb',
                'brand-indigo': '#4f46e5',
            },
        },
    },

    plugins: [forms],
};