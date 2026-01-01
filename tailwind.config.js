/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './Modules/**/*.blade.php',
        './Modules/**/*.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
