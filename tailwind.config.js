/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#0F8B83',
                'primary-dark': '#086A64',
                accent: '#F4B942',
                canvas: '#F8FAFA',
                ink: '#1F2937',
            },
            boxShadow: {
                soft: '0 18px 50px rgba(8, 106, 100, 0.12)',
            },
        },
    },
    plugins: [],
};
