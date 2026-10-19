/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary: 'rgb(var(--site-primary, 15 139 131) / <alpha-value>)',
                'primary-dark': 'rgb(var(--site-primary-dark, 8 106 100) / <alpha-value>)',
                accent: 'rgb(var(--site-accent, 244 185 66) / <alpha-value>)',
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
